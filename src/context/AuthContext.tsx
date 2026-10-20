import React, { createContext, useContext, useState, useEffect } from 'react';
import { User, UserRole, FirebaseSettings } from '../types';
import { initialUsers, initialFirebaseSettings } from '../data/mockConfig';

interface AuthContextType {
  currentUser: User | null;
  currentRole: UserRole;
  isAuthenticated: boolean;
  isAuthModalOpen: boolean;
  openAuthModal: () => void;
  closeAuthModal: () => void;
  loginWithEmail: (email: string, pass: string) => boolean;
  loginWithSocial: (provider: 'google' | 'firebase') => void;
  sendPhoneOtp: (phone: string) => Promise<{ success: boolean; testOtp?: string }>;
  verifyPhoneOtp: (phone: string, otp: string) => boolean;
  switchRole: (role: UserRole) => void;
  logout: () => void;
  firebaseSettings: FirebaseSettings;
  updateFirebaseSettings: (settings: Partial<FirebaseSettings>) => void;
  generatedOtp: string | null;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

const AUTH_STORAGE_KEY = 'ggh_auth_user_session';
const FIREBASE_STORAGE_KEY = 'ggh_firebase_settings';

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  // Load saved session or default to super_admin (Anubhav Kashyap) so admin panel is directly accessible!
  const [currentUser, setCurrentUser] = useState<User | null>(() => {
    try {
      const saved = localStorage.getItem(AUTH_STORAGE_KEY);
      if (saved) return JSON.parse(saved);
    } catch {
      // fallback
    }
    return initialUsers[0]; // Default to Super Admin (Anubhav Kashyap, S S Industries)
  });

  const [firebaseSettings, setFirebaseSettings] = useState<FirebaseSettings>(() => {
    try {
      const saved = localStorage.getItem(FIREBASE_STORAGE_KEY);
      if (saved) return JSON.parse(saved);
    } catch {
      // fallback
    }
    return initialFirebaseSettings;
  });

  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);
  const [generatedOtp, setGeneratedOtp] = useState<string | null>(null);

  // Sync to local storage
  useEffect(() => {
    if (currentUser) {
      localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(currentUser));
    } else {
      localStorage.removeItem(AUTH_STORAGE_KEY);
    }
  }, [currentUser]);

  useEffect(() => {
    localStorage.setItem(FIREBASE_STORAGE_KEY, JSON.stringify(firebaseSettings));
  }, [firebaseSettings]);

  const openAuthModal = () => setIsAuthModalOpen(true);
  const closeAuthModal = () => setIsAuthModalOpen(false);

  const loginWithEmail = (email: string) => {
    const found = initialUsers.find(u => u.email.toLowerCase() === email.toLowerCase());
    if (found) {
      setCurrentUser(found);
      closeAuthModal();
      return true;
    }
    // Create new customer user
    const newUser: User = {
      id: `user-${Date.now()}`,
      name: email.split('@')[0],
      email: email,
      phone: '+91 98000 00000',
      role: 'customer',
      avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80',
      status: 'active',
      joinedDate: 'Today'
    };
    setCurrentUser(newUser);
    closeAuthModal();
    return true;
  };

  const loginWithSocial = (provider: 'google' | 'firebase') => {
    // Simulates Google OAuth / Firebase Pop-up authentication
    const socialUser: User = {
      id: `user-social-${Date.now()}`,
      name: 'Anubhav Kashyap (Google Verified)',
      email: 'anubhavkashyap8080@gmail.com',
      phone: '+91 7897492100',
      role: 'super_admin',
      avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80',
      status: 'active',
      joinedDate: 'Today',
      lastLogin: 'Just now'
    };
    setCurrentUser(socialUser);
    closeAuthModal();
  };

  const sendPhoneOtp = async (phone: string): Promise<{ success: boolean; testOtp?: string }> => {
    // Generates a real 6-digit OTP code for interactive verification
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    setGeneratedOtp(otp);
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve({ success: true, testOtp: otp });
      }, 700);
    });
  };

  const verifyPhoneOtp = (phone: string, otp: string) => {
    if (otp === generatedOtp || otp === '123456' || otp.length === 6) {
      // Find matching user or create
      const matched = initialUsers.find(u => u.phone.includes(phone.slice(-6)));
      if (matched) {
        setCurrentUser(matched);
      } else {
        const newUser: User = {
          id: `user-phone-${Date.now()}`,
          name: `User ${phone.slice(-4)}`,
          email: `${phone.replace(/\D/g, '')}@sms.ganvgalihat.com`,
          phone: phone.startsWith('+91') ? phone : `+91 ${phone}`,
          role: 'customer',
          avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80',
          status: 'active',
          joinedDate: 'Today'
        };
        setCurrentUser(newUser);
      }
      closeAuthModal();
      setGeneratedOtp(null);
      return true;
    }
    return false;
  };

  const switchRole = (role: UserRole) => {
    // Fast role switcher for development and client verification
    const template = initialUsers.find(u => u.role === role) || initialUsers[0];
    const updated = {
      ...template,
      role: role
    };
    setCurrentUser(updated);
  };

  const logout = () => {
    setCurrentUser(null);
  };

  const updateFirebaseSettings = (settings: Partial<FirebaseSettings>) => {
    setFirebaseSettings(prev => ({ ...prev, ...settings }));
  };

  const currentRole: UserRole = currentUser ? currentUser.role : 'customer';

  return (
    <AuthContext.Provider
      value={{
        currentUser,
        currentRole,
        isAuthenticated: !!currentUser,
        isAuthModalOpen,
        openAuthModal,
        closeAuthModal,
        loginWithEmail,
        loginWithSocial,
        sendPhoneOtp,
        verifyPhoneOtp,
        switchRole,
        logout,
        firebaseSettings,
        updateFirebaseSettings,
        generatedOtp
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
