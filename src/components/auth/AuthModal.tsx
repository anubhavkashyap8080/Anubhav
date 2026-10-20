import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import {
  X,
  Phone,
  Mail,
  ShieldCheck,
  Sparkles,
  ArrowRight,
  RotateCw,
  CheckCircle2,
  Lock,
  UserCheck,
  Building2
} from 'lucide-react';

export const AuthModal: React.FC = () => {
  const {
    isAuthModalOpen,
    closeAuthModal,
    loginWithSocial,
    loginWithEmail,
    sendPhoneOtp,
    verifyPhoneOtp,
    switchRole,
    currentUser,
    currentRole,
    generatedOtp,
    firebaseSettings
  } = useAuth();

  const [authMode, setAuthMode] = useState<'otp' | 'google' | 'email'>('otp');
  const [phoneNumber, setPhoneNumber] = useState('');
  const [otpCode, setOtpCode] = useState('');
  const [otpSent, setOtpSent] = useState(false);
  const [countdown, setCountdown] = useState(30);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [emailInput, setEmailInput] = useState('');
  const [passwordInput, setPasswordInput] = useState('');

  // Countdown timer for OTP
  useEffect(() => {
    let timer: any;
    if (otpSent && countdown > 0) {
      timer = setTimeout(() => setCountdown(c => c - 1), 1000);
    }
    return () => clearTimeout(timer);
  }, [otpSent, countdown]);

  if (!isAuthModalOpen) return null;

  const handleSendOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    const cleaned = phoneNumber.replace(/\D/g, '');
    if (cleaned.length < 10) {
      setErrorMsg('Please enter a valid 10-digit Indian mobile number');
      return;
    }

    setIsLoading(true);
    const res = await sendPhoneOtp(cleaned);
    setIsLoading(false);

    if (res.success) {
      setOtpSent(true);
      setCountdown(30);
      if (res.testOtp) {
        setOtpCode(res.testOtp); // Auto-fill for seamless user testing
      }
    }
  };

  const handleVerifyOtp = (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    if (otpCode.length < 6) {
      setErrorMsg('Please enter the 6-digit OTP code');
      return;
    }

    const verified = verifyPhoneOtp(phoneNumber, otpCode);
    if (!verified) {
      setErrorMsg('Invalid OTP. Please check the code or click Resend.');
    }
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        
        {/* Header */}
        <div className="bg-gradient-to-r from-emerald-900 to-brand-green p-6 text-white relative">
          <button
            onClick={closeAuthModal}
            className="absolute right-4 top-4 p-1.5 rounded-full text-emerald-200 hover:text-white hover:bg-emerald-800/60 transition-colors"
          >
            <X className="w-5 h-5" />
          </button>

          <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-800/80 text-amber-300 text-[10px] font-semibold mb-2">
            <Sparkles className="w-3 h-3" />
            <span>S S Industries Unified Auth</span>
          </div>

          <h3 className="text-xl font-bold font-display">
            Welcome to GaunGaliHat
          </h3>
          <p className="text-xs text-emerald-100/90 mt-1">
            Sign in with Mobile OTP, Google, or explore multiple user permission roles.
          </p>
        </div>

        {/* Tab Switcher */}
        <div className="flex border-b border-gray-100 bg-gray-50/70 p-1 text-xs font-semibold">
          <button
            onClick={() => { setAuthMode('otp'); setErrorMsg(''); }}
            className={`flex-1 py-2 rounded-lg flex items-center justify-center gap-1.5 transition-all ${
              authMode === 'otp'
                ? 'bg-white text-brand-green shadow-xs'
                : 'text-gray-500 hover:text-gray-900'
            }`}
          >
            <Phone className="w-3.5 h-3.5" />
            <span>Mobile OTP</span>
          </button>
          <button
            onClick={() => { setAuthMode('google'); setErrorMsg(''); }}
            className={`flex-1 py-2 rounded-lg flex items-center justify-center gap-1.5 transition-all ${
              authMode === 'google'
                ? 'bg-white text-brand-green shadow-xs'
                : 'text-gray-500 hover:text-gray-900'
            }`}
          >
            <svg className="w-3.5 h-3.5" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Google One-Tap</span>
          </button>
          <button
            onClick={() => { setAuthMode('email'); setErrorMsg(''); }}
            className={`flex-1 py-2 rounded-lg flex items-center justify-center gap-1.5 transition-all ${
              authMode === 'email'
                ? 'bg-white text-brand-green shadow-xs'
                : 'text-gray-500 hover:text-gray-900'
            }`}
          >
            <Mail className="w-3.5 h-3.5" />
            <span>Email</span>
          </button>
        </div>

        {/* Modal Body */}
        <div className="p-6 space-y-4">
          
          {errorMsg && (
            <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center gap-2">
              <span className="font-bold">Error:</span> {errorMsg}
            </div>
          )}

          {/* 1. Mobile OTP Mode */}
          {authMode === 'otp' && (
            <div>
              {!otpSent ? (
                <form onSubmit={handleSendOtp} className="space-y-4 text-xs">
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1.5">
                      Enter Mobile Number
                    </label>
                    <div className="flex">
                      <span className="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-300 bg-gray-100 text-gray-600 font-semibold text-xs">
                        +91
                      </span>
                      <input
                        type="tel"
                        maxLength={10}
                        placeholder="78974 92100"
                        value={phoneNumber}
                        onChange={(e) => setPhoneNumber(e.target.value.replace(/\D/g, ''))}
                        className="w-full px-3 py-2.5 border border-gray-300 rounded-r-xl text-sm font-medium focus:ring-2 focus:ring-brand-green focus:border-brand-green"
                        autoFocus
                      />
                    </div>
                    <p className="text-[11px] text-gray-500 mt-1">
                      A 6-digit OTP will be generated via our DLT SMS Gateway.
                    </p>
                  </div>

                  <button
                    type="submit"
                    disabled={isLoading || phoneNumber.length < 10}
                    className="w-full py-3 bg-brand-green hover:bg-brand-green-dark disabled:bg-gray-300 text-white font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
                  >
                    {isLoading ? (
                      <RotateCw className="w-4 h-4 animate-spin" />
                    ) : (
                      <>
                        <span>Get Verification OTP</span>
                        <ArrowRight className="w-4 h-4" />
                      </>
                    )}
                  </button>
                </form>
              ) : (
                <form onSubmit={handleVerifyOtp} className="space-y-4 text-xs">
                  <div className="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-emerald-900">
                    <p className="font-semibold text-xs">OTP Sent to +91 {phoneNumber}</p>
                    {generatedOtp && (
                      <p className="text-[11px] text-brand-green mt-0.5">
                        Test code generated: <strong className="font-mono bg-white px-1.5 py-0.5 rounded border border-emerald-200">{generatedOtp}</strong>
                      </p>
                    )}
                  </div>

                  <div>
                    <label className="block font-semibold text-gray-700 mb-1.5">
                      Enter 6-Digit OTP Code
                    </label>
                    <input
                      type="text"
                      maxLength={6}
                      value={otpCode}
                      onChange={(e) => setOtpCode(e.target.value.replace(/\D/g, ''))}
                      placeholder="e.g. 482910"
                      className="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-center text-lg font-mono font-bold tracking-widest focus:ring-2 focus:ring-brand-green"
                      autoFocus
                    />
                  </div>

                  <div className="flex items-center justify-between text-xs text-gray-500">
                    <span>
                      {countdown > 0 ? `Resend in ${countdown}s` : 'Did not receive code?'}
                    </span>
                    {countdown === 0 && (
                      <button
                        type="button"
                        onClick={handleSendOtp}
                        className="text-brand-orange font-bold hover:underline"
                      >
                        Resend OTP
                      </button>
                    )}
                  </div>

                  <button
                    type="submit"
                    className="w-full py-3 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-md transition-all"
                  >
                    Verify & Enter Marketplace
                  </button>
                </form>
              )}
            </div>
          )}

          {/* 2. Google / Social Sign-In Mode */}
          {authMode === 'google' && (
            <div className="space-y-4 text-center">
              <p className="text-xs text-gray-600">
                Authenticate with one click using your Google or Firebase credentials:
              </p>

              <button
                type="button"
                onClick={() => loginWithSocial('google')}
                className="w-full py-3 px-4 border border-gray-300 rounded-xl hover:bg-gray-50 shadow-xs flex items-center justify-center gap-3 transition-all cursor-pointer font-semibold text-xs text-gray-700"
              >
                <svg className="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                  <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                  <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                  <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                  <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Continue with Google (Anubhav Kashyap)</span>
              </button>

              <div className="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-[11px] text-left">
                <span className="font-bold block">Firebase Project Connected:</span>
                <code>{firebaseSettings.projectId}</code> (Auth domain: {firebaseSettings.authDomain})
              </div>
            </div>
          )}

          {/* 3. Email Mode */}
          {authMode === 'email' && (
            <form onSubmit={(e) => { e.preventDefault(); loginWithEmail(emailInput, passwordInput); }} className="space-y-4 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Email Address</label>
                <input
                  type="email"
                  required
                  placeholder="anubhav@ssindustries.in"
                  value={emailInput}
                  onChange={(e) => setEmailInput(e.target.value)}
                  className="w-full px-3 py-2.5 border border-gray-300 rounded-xl"
                  autoFocus
                />
              </div>
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Password</label>
                <input
                  type="password"
                  placeholder="••••••••"
                  value={passwordInput}
                  onChange={(e) => setPasswordInput(e.target.value)}
                  className="w-full px-3 py-2.5 border border-gray-300 rounded-xl"
                />
              </div>
              <button
                type="submit"
                className="w-full py-3 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-md transition-all"
              >
                Sign In with Email
              </button>
            </form>
          )}

          {/* Multi-User Role Simulator Box */}
          <div className="pt-4 border-t border-gray-100">
            <p className="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center justify-between">
              <span>Multiple User Level Access</span>
              <span className="text-brand-orange lowercase font-normal">switch & test</span>
            </p>

            <div className="grid grid-cols-2 gap-2 text-[11px]">
              <button
                type="button"
                onClick={() => { switchRole('super_admin'); closeAuthModal(); }}
                className={`p-2 rounded-lg border text-left flex items-center gap-2 transition-all ${
                  currentRole === 'super_admin'
                    ? 'border-brand-green bg-emerald-50 text-brand-green font-bold'
                    : 'border-gray-200 hover:bg-gray-50 text-gray-700'
                }`}
              >
                <ShieldCheck className="w-3.5 h-3.5 shrink-0 text-brand-green" />
                <div className="truncate">
                  <span className="block font-bold">Super Admin</span>
                  <span className="text-[10px] text-gray-500">Full Controls</span>
                </div>
              </button>

              <button
                type="button"
                onClick={() => { switchRole('vendor'); closeAuthModal(); }}
                className={`p-2 rounded-lg border text-left flex items-center gap-2 transition-all ${
                  currentRole === 'vendor'
                    ? 'border-brand-orange bg-orange-50 text-brand-orange font-bold'
                    : 'border-gray-200 hover:bg-gray-50 text-gray-700'
                }`}
              >
                <Building2 className="w-3.5 h-3.5 shrink-0 text-brand-orange" />
                <div className="truncate">
                  <span className="block font-bold">Vendor / Seller</span>
                  <span className="text-[10px] text-gray-500">Products & Orders</span>
                </div>
              </button>

              <button
                type="button"
                onClick={() => { switchRole('support'); closeAuthModal(); }}
                className={`p-2 rounded-lg border text-left flex items-center gap-2 transition-all ${
                  currentRole === 'support'
                    ? 'border-blue-500 bg-blue-50 text-blue-700 font-bold'
                    : 'border-gray-200 hover:bg-gray-50 text-gray-700'
                }`}
              >
                <UserCheck className="w-3.5 h-3.5 shrink-0 text-blue-600" />
                <div className="truncate">
                  <span className="block font-bold">Support Agent</span>
                  <span className="text-[10px] text-gray-500">Ticket Triage</span>
                </div>
              </button>

              <button
                type="button"
                onClick={() => { switchRole('customer'); closeAuthModal(); }}
                className={`p-2 rounded-lg border text-left flex items-center gap-2 transition-all ${
                  currentRole === 'customer'
                    ? 'border-purple-500 bg-purple-50 text-purple-700 font-bold'
                    : 'border-gray-200 hover:bg-gray-50 text-gray-700'
                }`}
              >
                <Lock className="w-3.5 h-3.5 shrink-0 text-purple-600" />
                <div className="truncate">
                  <span className="block font-bold">Customer</span>
                  <span className="text-[10px] text-gray-500">Buyer View</span>
                </div>
              </button>
            </div>
          </div>

        </div>

        {/* Footer */}
        <div className="bg-gray-50 p-3 border-t border-gray-100 text-center text-[10px] text-gray-500">
          S S Industries • Secured with DLT SMS Gateway & Firebase Auth Architecture
        </div>
      </div>
    </div>
  );
};
