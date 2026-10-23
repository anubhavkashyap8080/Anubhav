<?php
/**
 * GaunGaliHat Real-time Live Chat API Endpoint
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$action = clean_input($_REQUEST['action'] ?? '');
$curr_user = is_logged_in() ? get_logged_in_user() : null;

// Helper: Get or create session for customer
function get_or_create_customer_session() {
    global $pdo, $curr_user;
    if (empty($_SESSION['chat_token'])) {
        $_SESSION['chat_token'] = bin2hex(random_bytes(16));
    }
    $token = $_SESSION['chat_token'];

    $stmt = $pdo->prepare("SELECT * FROM chat_sessions WHERE session_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $session = $stmt->fetch();

    if (!$session) {
        $user_name = $curr_user['name'] ?? ($_SESSION['chat_guest_name'] ?? 'Guest Customer');
        $user_email = $curr_user['email'] ?? ($_SESSION['chat_guest_email'] ?? null);
        $user_phone = $curr_user['phone'] ?? null;
        $user_id = $curr_user['id'] ?? null;

        $stmt_ins = $pdo->prepare("
            INSERT INTO chat_sessions (session_token, user_id, user_name, user_email, user_phone, status, last_message, unread_admin, unread_user, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'active', 'Chat session started', 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt_ins->execute([$token, $user_id, $user_name, $user_email, $user_phone]);
        $session_id = $pdo->lastInsertId();

        $stmt->execute([$token]);
        $session = $stmt->fetch();

        // Send automated welcome message from support in English
        $stmt_wel = $pdo->prepare("
            INSERT INTO chat_messages (session_id, sender_type, sender_name, message, is_read, created_at)
            VALUES (?, 'support', 'GaunGaliHat Support Desk', 'Hello! Welcome to GaunGaliHat Live Support. How may we assist you today?', 0, CURRENT_TIMESTAMP)
        ");
        $stmt_wel->execute([$session['id']]);
    }

    return $session;
}

// 1. Customer: Init or Poll Messages
if ($action === 'customer_poll') {
    $session = get_or_create_customer_session();
    $last_id = intval($_GET['last_id'] ?? 0);

    // Fetch new messages
    $stmt_msg = $pdo->prepare("
        SELECT id, sender_type, sender_name, message, is_read, created_at
        FROM chat_messages 
        WHERE session_id = ? AND id > ? 
        ORDER BY id ASC
    ");
    $stmt_msg->execute([$session['id'], $last_id]);
    $messages = $stmt_msg->fetchAll();

    // Mark support messages as read
    $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender_type != 'user'")->execute([$session['id']]);
    $pdo->prepare("UPDATE chat_sessions SET unread_user = 0 WHERE id = ?")->execute([$session['id']]);

    echo json_encode([
        'success'    => true,
        'session_id' => $session['id'],
        'user_name'  => $session['user_name'],
        'messages'   => $messages
    ]);
    exit;
}

// 2. Customer: Send Message (With Double-Submit & Duplicate Spam Debounce)
if ($action === 'customer_send') {
    $session = get_or_create_customer_session();
    $message = clean_input($_POST['message'] ?? '');
    $guest_name = clean_input($_POST['guest_name'] ?? '');
    $guest_phone = clean_input($_POST['guest_phone'] ?? '');

    if (!empty($guest_name)) {
        $_SESSION['chat_guest_name'] = $guest_name;
        $pdo->prepare("UPDATE chat_sessions SET user_name = ? WHERE id = ?")->execute([$guest_name, $session['id']]);
    }
    if (!empty($guest_phone)) {
        $_SESSION['chat_guest_phone'] = $guest_phone;
        $pdo->prepare("UPDATE chat_sessions SET user_phone = ? WHERE id = ?")->execute([$guest_phone, $session['id']]);
    }

    if (!empty($message)) {
        // 1. Session-level immediate debounce (immune to DB timezone variations)
        if (!empty($_SESSION['last_sent_msg']) && 
            $_SESSION['last_sent_msg'] === $message && 
            !empty($_SESSION['last_sent_time']) && 
            (time() - $_SESSION['last_sent_time']) <= 4) {
            echo json_encode(['success' => true, 'debounced' => true, 'message_id' => $_SESSION['last_sent_id'] ?? 0]);
            exit;
        }

        // 2. Database-level fallback debounce
        $stmt_chk = $pdo->prepare("
            SELECT id, message, created_at 
            FROM chat_messages 
            WHERE session_id = ? AND sender_type = 'user' 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt_chk->execute([$session['id']]);
        $last_msg = $stmt_chk->fetch();
        if ($last_msg && trim($last_msg['message']) === $message) {
            $diff = abs(time() - strtotime($last_msg['created_at']));
            // If created within 4 seconds (accounting for timezone drift)
            if ($diff <= 4 || ($diff >= 19790 && $diff <= 19810)) {
                echo json_encode(['success' => true, 'debounced' => true, 'message_id' => $last_msg['id']]);
                exit;
            }
        }

        $now = date('Y-m-d H:i:s');
        $sender_name = $guest_name ?: $session['user_name'];
        $stmt_ins = $pdo->prepare("
            INSERT INTO chat_messages (session_id, sender_type, sender_name, message, is_read, created_at)
            VALUES (?, 'user', ?, ?, 0, ?)
        ");
        $stmt_ins->execute([$session['id'], $sender_name, $message, $now]);
        $new_msg_id = $pdo->lastInsertId();

        // Record in session
        $_SESSION['last_sent_msg'] = $message;
        $_SESSION['last_sent_time'] = time();
        $_SESSION['last_sent_id'] = $new_msg_id;

        // Update session
        $pdo->prepare("
            UPDATE chat_sessions 
            SET last_message = ?, unread_admin = unread_admin + 1, updated_at = ? 
            WHERE id = ?
        ")->execute([$message, $now, $session['id']]);

        echo json_encode(['success' => true, 'message_id' => $new_msg_id]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Empty message']);
    exit;
}

// 2b. Customer: End / Cancel Chat Session
if ($action === 'customer_end_chat') {
    if (!empty($_SESSION['chat_token'])) {
        $token = $_SESSION['chat_token'];
        $stmt = $pdo->prepare("SELECT id FROM chat_sessions WHERE session_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $session = $stmt->fetch();
        if ($session) {
            $pdo->prepare("UPDATE chat_sessions SET status = 'resolved', updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$session['id']]);
            $pdo->prepare("
                INSERT INTO chat_messages (session_id, sender_type, sender_name, message, is_read, created_at)
                VALUES (?, 'system', 'System', 'Customer has ended this chat session.', 1, CURRENT_TIMESTAMP)
            ")->execute([$session['id']]);
        }
        unset($_SESSION['chat_token']);
    }
    echo json_encode(['success' => true]);
    exit;
}

// ==========================================
// ADMIN & SUPPORT TEAM ENDPOINTS
// ==========================================
$is_support_authorized = false;
if ($curr_user && (
    in_array($curr_user['role'], ['super_admin', 'admin', 'support']) ||
    strpos(strtolower($curr_user['department'] ?? ''), 'support') !== false
)) {
    $is_support_authorized = true;
}

if (!$is_support_authorized) {
    echo json_encode(['success' => false, 'error' => 'अनधिकृत पहुंच (Support clearance required)']);
    exit;
}

// 3. Admin: List All Sessions
if ($action === 'admin_sessions') {
    $filter = clean_input($_GET['filter'] ?? 'all');
    $query = "SELECT * FROM chat_sessions ";
    if ($filter === 'unread') {
        $query .= "WHERE unread_admin > 0 ";
    } elseif ($filter === 'active') {
        $query .= "WHERE status = 'active' ";
    } elseif ($filter === 'resolved') {
        $query .= "WHERE status = 'resolved' ";
    }
    $query .= "ORDER BY updated_at DESC LIMIT 50";

    $sessions = $pdo->query($query)->fetchAll();
    $total_unread = $pdo->query("SELECT SUM(unread_admin) FROM chat_sessions")->fetchColumn() ?: 0;

    echo json_encode([
        'success'      => true,
        'sessions'     => $sessions,
        'total_unread' => (int)$total_unread
    ]);
    exit;
}

// 4. Admin: Get Thread & Mark Read
if ($action === 'admin_get_thread') {
    $session_id = intval($_GET['session_id'] ?? 0);
    $last_id = intval($_GET['last_id'] ?? 0);

    $stmt_sess = $pdo->prepare("SELECT * FROM chat_sessions WHERE id = ?");
    $stmt_sess->execute([$session_id]);
    $session = $stmt_sess->fetch();

    if ($session) {
        $stmt_msg = $pdo->prepare("
            SELECT id, sender_type, sender_name, message, is_read, created_at
            FROM chat_messages 
            WHERE session_id = ? AND id > ? 
            ORDER BY id ASC
        ");
        $stmt_msg->execute([$session_id, $last_id]);
        $messages = $stmt_msg->fetchAll();

        // Mark user messages as read by admin
        $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender_type = 'user'")->execute([$session_id]);
        $pdo->prepare("UPDATE chat_sessions SET unread_admin = 0 WHERE id = ?")->execute([$session_id]);

        echo json_encode([
            'success'  => true,
            'session'  => $session,
            'messages' => $messages
        ]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'सत्र नहीं मिला']);
    exit;
}

// 5. Admin: Send Reply (With Debounce Check)
if ($action === 'admin_reply') {
    $session_id = intval($_POST['session_id'] ?? 0);
    $reply_msg = clean_input($_POST['message'] ?? '');

    if ($session_id > 0 && !empty($reply_msg)) {
        // Prevent duplicate spam within 2 seconds
        $stmt_chk = $pdo->prepare("
            SELECT id, message, created_at 
            FROM chat_messages 
            WHERE session_id = ? AND sender_type != 'user' 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt_chk->execute([$session_id]);
        $last_msg = $stmt_chk->fetch();
        if ($last_msg && trim($last_msg['message']) === $reply_msg) {
            $time_diff = time() - strtotime($last_msg['created_at']);
            if ($time_diff <= 2) {
                echo json_encode(['success' => true, 'debounced' => true]);
                exit;
            }
        }

        $agent_name = $curr_user['name'] . ' (Support)';
        $stmt_ins = $pdo->prepare("
            INSERT INTO chat_messages (session_id, sender_type, sender_name, message, is_read, created_at)
            VALUES (?, 'support', ?, ?, 0, CURRENT_TIMESTAMP)
        ");
        $stmt_ins->execute([$session_id, $agent_name, $reply_msg]);

        $pdo->prepare("
            UPDATE chat_sessions 
            SET last_message = ?, unread_user = unread_user + 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ")->execute([$reply_msg, $session_id]);

        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'अमान्य डेटा']);
    exit;
}

// 6. Admin: Toggle Resolved / Active Status
if ($action === 'admin_toggle_status') {
    $session_id = intval($_POST['session_id'] ?? 0);
    $new_status = clean_input($_POST['status'] ?? 'resolved');
    $pdo->prepare("UPDATE chat_sessions SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$new_status, $session_id]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'अमान्य कार्रवाई']);
exit;
