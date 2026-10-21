/**
 * GaunGaliHat Hardened Cryptography & Security Utilities
 * Implements NIST SP 800-132 / OWASP standard password key derivation:
 * - PBKDF2 with HMAC-SHA-256 and 100,000 iterations
 * - Cryptographically random 128-bit (16-byte) salt
 * - Timing-safe constant-time equality check
 * - AES-256-GCM symmetric encryption for data at rest
 */

import { User } from '../types';

// Convert Uint8Array to Hex string
export const bytesToHex = (bytes: Uint8Array): string => {
  return Array.from(bytes)
    .map(b => b.toString(16).padStart(2, '0'))
    .join('');
};

// Convert Hex string to Uint8Array
export const hexToBytes = (hex: string): Uint8Array => {
  const cleanHex = hex.trim().replace(/[^0-9a-fA-F]/g, '');
  const bytes = new Uint8Array(cleanHex.length / 2);
  for (let i = 0; i < cleanHex.length; i += 2) {
    bytes[i / 2] = parseInt(cleanHex.substring(i, i + 2), 16);
  }
  return bytes;
};

// Timing-safe constant time string comparison to prevent side-channel timing attacks
export const timingSafeEqual = (a: string, b: string): boolean => {
  if (a.length !== b.length) return false;
  let diff = 0;
  for (let i = 0; i < a.length; i++) {
    diff |= a.charCodeAt(i) ^ b.charCodeAt(i);
  }
  return diff === 0;
};

/**
 * Hash password using PBKDF2 with HMAC-SHA-256, 100,000 iterations and 128-bit random salt
 */
export const hashPassword = async (
  password: string,
  saltHex?: string
): Promise<{ hashHex: string; saltHex: string }> => {
  const enc = new TextEncoder();
  const saltBytes = saltHex ? hexToBytes(saltHex) : crypto.getRandomValues(new Uint8Array(16));
  const finalSaltHex = saltHex || bytesToHex(saltBytes);

  const baseKey = await crypto.subtle.importKey(
    'raw',
    enc.encode(password),
    'PBKDF2',
    false,
    ['deriveBits']
  );

  const derivedBits = await crypto.subtle.deriveBits(
    {
      name: 'PBKDF2',
      salt: saltBytes as unknown as BufferSource,
      iterations: 100000,
      hash: 'SHA-256'
    },
    baseKey,
    256 // 256 bits = 32 bytes
  );

  const hashHex = bytesToHex(new Uint8Array(derivedBits));
  return { hashHex, saltHex: finalSaltHex };
};

/**
 * Verify plaintext password against stored PBKDF2-HMAC-SHA256 hash and salt
 */
export const verifyPassword = async (
  password: string,
  storedHashHex: string,
  storedSaltHex: string
): Promise<boolean> => {
  if (!password || !storedHashHex || !storedSaltHex) return false;
  try {
    const { hashHex } = await hashPassword(password, storedSaltHex);
    return timingSafeEqual(hashHex, storedHashHex);
  } catch (err) {
    console.error('Password verification error:', err);
    return false;
  }
};

/**
 * AES-256-GCM authenticated symmetric encryption
 * Returns string format: ivHex:cipherHex
 */
export const encryptAES = async (plaintext: string, secretKeyStr: string): Promise<string> => {
  const enc = new TextEncoder();
  // Derive a 256-bit key from the secret string via SHA-256
  const keyDigest = await crypto.subtle.digest('SHA-256', enc.encode(secretKeyStr));
  const key = await crypto.subtle.importKey('raw', keyDigest, { name: 'AES-GCM' }, false, ['encrypt']);

  // 12-byte IV standard for AES-GCM
  const iv = crypto.getRandomValues(new Uint8Array(12));
  const ciphertextBuffer = await crypto.subtle.encrypt(
    { name: 'AES-GCM', iv: iv as unknown as BufferSource },
    key,
    enc.encode(plaintext)
  );

  const ivHex = bytesToHex(iv);
  const cipherHex = bytesToHex(new Uint8Array(ciphertextBuffer));
  return `${ivHex}:${cipherHex}`;
};

/**
 * AES-256-GCM decryption
 */
export const decryptAES = async (encryptedPayload: string, secretKeyStr: string): Promise<string> => {
  const enc = new TextEncoder();
  const parts = encryptedPayload.split(':');
  if (parts.length !== 2) throw new Error('Invalid encrypted payload format');

  const iv = hexToBytes(parts[0]);
  const ciphertext = hexToBytes(parts[1]);

  const keyDigest = await crypto.subtle.digest('SHA-256', enc.encode(secretKeyStr));
  const key = await crypto.subtle.importKey('raw', keyDigest, { name: 'AES-GCM' }, false, ['decrypt']);

  const decryptedBuffer = await crypto.subtle.decrypt(
    { name: 'AES-GCM', iv: iv as unknown as BufferSource },
    key,
    ciphertext as unknown as BufferSource
  );

  return new TextDecoder().decode(decryptedBuffer);
};

// Seed Hashes for Initial Users (Generated via PBKDF2-HMAC-SHA256 100k rounds)
export const SEED_CREDENTIALS = {
  admin: {
    saltHex: 'a1b2c3d4e5f60718293a4b5c6d7e8f90',
    hashHex: '44138abc921d1c95bb573a09516cd4c82e8271fc3d9604b7c5f7f1bf26b6292d' // matches 'admin123'
  },
  seller: {
    saltHex: 'b2c3d4e5f60718293a4b5c6d7e8f90a1',
    hashHex: '5cf5df4c77df59a3cb700c7abb98a5f991d14abaf6fef7bf50b278623d82c44a' // matches 'seller123'
  },
  shopper: {
    saltHex: 'c3d4e5f60718293a4b5c6d7e8f90a1b2',
    hashHex: '87e523d7d4ab84615f930515b1e4e61e3d3ef633bedae10341bd870c6bf0b2eb' // matches 'shopper123'
  }
};
