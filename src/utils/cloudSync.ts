import { User } from '../types';
import { initialUsers } from '../data/mockConfig';
import { SEED_CREDENTIALS } from './cryptoUtils';

const STORAGE_USERS_KEY = 'ggh_platform_users';

/**
 * Fetch synchronized staff and administrator accounts from the central server / edge endpoint.
 */
export const fetchRemoteUsers = async (): Promise<User[] | null> => {
  try {
    const res = await fetch('/api/users', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Cache-Control': 'no-cache'
      }
    });

    if (!res.ok) {
      return null;
    }

    const data = await res.json();
    if (Array.isArray(data) && data.length > 0) {
      return sanitizeUsersList(data);
    }
    return null;
  } catch (err) {
    // Local / offline fallback - silent warning
    return null;
  }
};

/**
 * Push updated staff accounts and credentials to the central backend.
 */
export const pushRemoteUsers = async (users: User[]): Promise<boolean> => {
  try {
    const sanitized = sanitizeUsersList(users);
    const res = await fetch('/api/users', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(sanitized)
    });
    return res.ok;
  } catch (err) {
    return false;
  }
};

/**
 * Ensures all user objects have proper PBKDF2 hashes, salts, and removes legacy plaintext fields.
 */
export const sanitizeUsersList = (usersList: any[]): User[] => {
  if (!Array.isArray(usersList)) return [];
  return usersList.map((u) => {
    const seed =
      u.role === 'customer'
        ? SEED_CREDENTIALS.shopper
        : u.role === 'vendor'
        ? SEED_CREDENTIALS.seller
        : SEED_CREDENTIALS.admin;

    const sanitized: User = {
      ...u,
      passwordHash: u.passwordHash || seed.hashHex,
      passwordSalt: u.passwordSalt || seed.saltHex
    };
    delete (sanitized as any).password;
    return sanitized;
  });
};

/**
 * Merges two lists of users (local and remote), deduping by ID, normalized username, and email.
 * Ensures accounts created on Desktop are merged into Mobile and vice-versa.
 */
export const mergeUsers = (localUsers: User[], remoteUsers: User[]): User[] => {
  const mergedMap = new Map<string, User>();

  // Helper key generator
  const makeKey = (u: User): string => {
    if (u.id) return `id:${u.id}`;
    if (u.username) return `usr:${u.username.trim().toLowerCase()}`;
    return `email:${u.email.trim().toLowerCase()}`;
  };

  // Add initial users as baseline
  initialUsers.forEach((u) => {
    mergedMap.set(makeKey(u), u);
  });

  // Add remote users
  remoteUsers.forEach((u) => {
    const key = makeKey(u);
    const existing = mergedMap.get(key);
    if (!existing || (u.passwordHash && !existing.passwordHash)) {
      mergedMap.set(key, u);
    } else {
      mergedMap.set(key, { ...existing, ...u });
    }
  });

  // Add local users
  localUsers.forEach((u) => {
    const key = makeKey(u);
    const existing = mergedMap.get(key);
    if (!existing) {
      mergedMap.set(key, u);
    } else {
      mergedMap.set(key, { ...existing, ...u });
    }
  });

  return Array.from(mergedMap.values());
};

/**
 * Generate a compressed URL-safe token representing all administrative and staff accounts
 * for instant device-to-device pairing via QR code or direct link.
 */
export const generatePairToken = (users: User[]): string => {
  try {
    // Only package relevant staff & vendor accounts to keep payload small
    const staffAccounts = users.filter((u) =>
      ['super_admin', 'admin', 'marketer', 'content_writer', 'support', 'vendor'].includes(u.role)
    );

    const payload = staffAccounts.map((u) => ({
      id: u.id,
      n: u.name,
      u: u.username || u.name.toLowerCase().replace(/[^a-z0-9_]/g, '_'),
      e: u.email,
      p: u.phone,
      r: u.role,
      d: u.department || 'Operations',
      h: u.passwordHash,
      s: u.passwordSalt
    }));

    const jsonStr = JSON.stringify(payload);
    // URL-safe Base64
    const base64 = btoa(encodeURIComponent(jsonStr).replace(/%([0-9A-F]{2})/g, (_, p1) =>
      String.fromCharCode(parseInt(p1, 16))
    ));
    return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
  } catch (err) {
    console.error('Failed to generate pair token:', err);
    return '';
  }
};

/**
 * Decode and validate a device pair token.
 */
export const unpackPairToken = (token: string): User[] | null => {
  try {
    let base64 = token.replace(/-/g, '+').replace(/_/g, '/');
    while (base64.length % 4) {
      base64 += '=';
    }
    const decodedBinary = atob(base64);
    const jsonStr = decodeURIComponent(
      Array.from(decodedBinary)
        .map((c) => '%' + c.charCodeAt(0).toString(16).padStart(2, '0'))
        .join('')
    );

    const parsed = JSON.parse(jsonStr);
    if (!Array.isArray(parsed)) return null;

    return parsed.map((item: any) => ({
      id: item.id || `user-paired-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      name: item.n || item.name || 'Admin Staff',
      username: item.u || item.username,
      email: item.e || item.email,
      phone: item.p || item.phone || '+91 7897492100',
      role: item.r || item.role || 'admin',
      department: item.d || item.department || 'Operations',
      passwordHash: item.h || item.passwordHash,
      passwordSalt: item.s || item.passwordSalt,
      status: 'active',
      joinedDate: 'Linked Device'
    }));
  } catch (err) {
    console.error('Failed to unpack pair token:', err);
    return null;
  }
};

/**
 * Startup synchronization routine:
 * 1. Checks if current URL has ?ggh_sync=<token> or ?pair_code=<token>
 * 2. Fetches /api/users to sync with desktop/central server
 * 3. Dispatches 'ggh_users_synced' so React state updates seamlessly
 */
export const syncOnStartup = async (): Promise<{ synced: boolean; count: number; source?: string }> => {
  let hasUpdated = false;
  let source = 'none';

  // 1. Check URL pairing parameter (e.g. user scanned QR code on mobile or opened sync link)
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const syncParam = urlParams.get('ggh_sync') || urlParams.get('pair_token');

    if (syncParam) {
      const unpacked = unpackPairToken(syncParam);
      if (unpacked && unpacked.length > 0) {
        const rawLocal = localStorage.getItem(STORAGE_USERS_KEY);
        const localList = rawLocal ? JSON.parse(rawLocal) : initialUsers;
        const merged = mergeUsers(localList, unpacked);
        localStorage.setItem(STORAGE_USERS_KEY, JSON.stringify(merged));
        hasUpdated = true;
        source = 'qr_pair_link';

        // Clean the URL parameter without page reload
        urlParams.delete('ggh_sync');
        urlParams.delete('pair_token');
        const newSearch = urlParams.toString() ? `?${urlParams.toString()}` : '';
        window.history.replaceState(
          {},
          document.title,
          window.location.pathname + newSearch + window.location.hash
        );

        // Push merged state to server
        pushRemoteUsers(merged).catch(() => {});
      }
    }
  } catch (err) {
    console.warn('URL pair sync error:', err);
  }

  // 2. Query Central API (/api/users)
  try {
    const remoteUsers = await fetchRemoteUsers();
    if (remoteUsers && remoteUsers.length > 0) {
      const rawLocal = localStorage.getItem(STORAGE_USERS_KEY);
      const localList = rawLocal ? JSON.parse(rawLocal) : initialUsers;
      const merged = mergeUsers(localList, remoteUsers);

      // Only write if new users or changes were discovered
      if (merged.length >= localList.length) {
        localStorage.setItem(STORAGE_USERS_KEY, JSON.stringify(merged));
        hasUpdated = true;
        source = source === 'qr_pair_link' ? 'qr_pair_and_api' : 'central_api';
      }

      // If local had users not present on remote, push merged back to remote!
      if (localList.length > remoteUsers.length) {
        pushRemoteUsers(merged).catch(() => {});
      }
    } else {
      // Remote was empty or server newly initialized: push local initial users
      const rawLocal = localStorage.getItem(STORAGE_USERS_KEY);
      if (rawLocal) {
        const localList = JSON.parse(rawLocal);
        if (Array.isArray(localList) && localList.length > 0) {
          pushRemoteUsers(localList).catch(() => {});
        }
      }
    }
  } catch (err) {
    // Offline / fallback
  }

  // 3. Notify listeners (AuthContext, PlatformContext, AdminLayout)
  if (hasUpdated) {
    window.dispatchEvent(new CustomEvent('ggh_users_synced', { detail: { source } }));
  }

  const currentLocal = localStorage.getItem(STORAGE_USERS_KEY);
  const currentCount = currentLocal ? JSON.parse(currentLocal).length : initialUsers.length;

  return { synced: hasUpdated, count: currentCount, source };
};
