with open('includes/functions.php', 'r', encoding='utf-8') as f:
    content = f.read()

needle = """function is_retailer() {
    $u = get_logged_in_user();
    return $u && in_array($u['role'], ['retailer', 'super_admin', 'admin'], true);
}"""

replacement = """function is_retailer() {
    $u = get_logged_in_user();
    if ($u) {
        return in_array($u['role'], ['retailer', 'super_admin', 'admin'], true);
    }
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['retailer', 'super_admin', 'admin'], true);
}"""

if needle in content:
    content = content.replace(needle, replacement)
else:
    needle_crlf = needle.replace('\n', '\r\n')
    replacement_crlf = replacement.replace('\n', '\r\n')
    content = content.replace(needle_crlf, replacement_crlf)

with open('includes/functions.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated is_retailer() successfully")
