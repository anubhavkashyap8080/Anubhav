import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth, AuthTabType } from '../../context/AuthContext';
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
  Building2,
  Store,
  UserCheck,
  AlertCircle
} from 'lucide-react';

export const AuthModal: React.FC = () => {
  const {
    isAuthModalOpen,
    closeAuthModal,
    authModalTab,
    loginWithSocial,
    loginWithEmail,
    loginAsSeller,
    loginAsAdmin,
    sendPhoneOtp,
    verifyPhoneOtp,
    generatedOtp
  } = useAuth();

  const navigate = useNavigate();

  // Tab State: 'customer' | 'seller' | 'admin'
  const [activeTab, setActiveTab] = useState<AuthTabType>(authModalTab || 'customer');

  useEffect(() => {
    if (authModalTab) {
      setActiveTab(authModalTab);
    }
  }, [authModalTab]);

  // Customer Form State
  const [customerMode, setCustomerMode] = useState<'otp' | 'email'>('otp');
  const [customerPhone, setCustomerPhone] = useState('');
  const [customerOtp, setCustomerOtp] = useState('');
  const [customerEmail, setCustomerEmail] = useState('');
  const [customerPassword, setCustomerPassword] = useState('');
  const [otpSent, setOtpSent] = useState(false);
  const [countdown, setCountdown] = useState(30);

  // Seller Form State
  const [sellerIdentifier, setSellerIdentifier] = useState('');
  const [sellerPassOrOtp, setSellerPassOrOtp] = useState('');

  // Admin Form State (Cleaned: No pre-filled credentials for visitors)
  const [adminIdentifier, setAdminIdentifier] = useState('');
  const [adminPasscode, setAdminPasscode] = useState('');

  // Status & Errors
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  // OTP Countdown
  useEffect(() => {
    let timer: any;
    if (otpSent && countdown > 0) {
      timer = setTimeout(() => setCountdown(c => c - 1), 1000);
    }
    return () => clearTimeout(timer);
  }, [otpSent, countdown]);

  if (!isAuthModalOpen) return null;

  // Handle Send OTP for Customer
  const handleSendOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    const clean = customerPhone.replace(/\D/g, '');
    if (clean.length < 10) {
      setErrorMsg('Please enter a valid 10-digit Indian mobile number');
      return;
    }

    setIsLoading(true);
    const res = await sendPhoneOtp(clean);
    setIsLoading(false);

    if (res.success) {
      setOtpSent(true);
      setCountdown(30);
      if (res.testOtp) {
        setCustomerOtp(res.testOtp);
      }
    }
  };

  // Handle Verify Customer OTP
  const handleVerifyOtp = (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    if (customerOtp.length < 6) {
      setErrorMsg('Please enter the 6-digit OTP code');
      return;
    }

    const verified = verifyPhoneOtp(customerPhone, customerOtp, 'customer');
    if (!verified) {
      setErrorMsg('Invalid OTP. Please check the code or resend.');
    }
  };

  // Handle Customer Email Login
  const handleCustomerEmailLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    if (!customerEmail.trim()) {
      setErrorMsg('Please enter your email address');
      return;
    }
    setIsLoading(true);
    const res = await loginWithEmail(customerEmail, customerPassword);
    setIsLoading(false);
    if (!res.success) {
      setErrorMsg(res.message || 'Invalid login credentials');
    }
  };

  // Handle Seller Login
  const handleSellerLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    if (!sellerIdentifier.trim()) {
      setErrorMsg('Please enter registered artisan phone or business email');
      return;
    }
    if (!sellerPassOrOtp.trim()) {
      setErrorMsg('Please enter your password or verification OTP');
      return;
    }
    setIsLoading(true);
    const res = await loginAsSeller(sellerIdentifier, sellerPassOrOtp);
    setIsLoading(false);
    if (res.success) {
      navigate('/seller/dashboard');
    } else {
      setErrorMsg(res.message || 'Invalid credentials');
    }
  };

  // Handle Admin Login
  const handleAdminLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    if (!adminIdentifier.trim()) {
      setErrorMsg('Please enter administrator username or email');
      return;
    }
    if (!adminPasscode.trim()) {
      setErrorMsg('Please enter your security passcode');
      return;
    }
    setIsLoading(true);
    const res = await loginAsAdmin(adminIdentifier, adminPasscode);
    setIsLoading(false);
    if (res.success) {
      navigate('/admin');
    } else {
      setErrorMsg(res.message || 'Access Denied: Invalid administrator passcode');
    }
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        
        {/* Header */}
        <div className="bg-gradient-to-r from-emerald-950 via-slate-900 to-emerald-900 p-5 text-white relative">
          <button
            onClick={closeAuthModal}
            className="absolute right-4 top-4 p-1.5 rounded-full text-emerald-200 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>

          <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-800/80 text-amber-300 text-[10px] font-semibold mb-1 border border-amber-300/20">
            <Sparkles className="w-3 h-3 text-amber-300" />
            <span>GaunGaliHat • Official Portal Access</span>
          </div>

          <h3 className="text-xl font-bold font-display">
            {activeTab === 'customer'
              ? 'Customer Sign In'
              : activeTab === 'seller'
              ? 'Artisan & Seller Portal'
              : 'Central Admin Clearance'}
          </h3>
          <p className="text-xs text-emerald-100/80 mt-0.5">
            {activeTab === 'customer'
              ? 'Order rural village staples & handcrafted tools'
              : activeTab === 'seller'
              ? 'Manage artisan workshop, village products & settlements'
              : 'Restricted access for administrative governance & operations'}
          </p>
        </div>

        {/* Portal Selection Tabs */}
        <div className="flex border-b border-gray-200 bg-gray-50/90 p-1.5 text-xs font-semibold gap-1">
          <button
            onClick={() => { setActiveTab('customer'); setErrorMsg(''); }}
            className={`flex-1 py-2 rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
              activeTab === 'customer'
                ? 'bg-white text-brand-green shadow-xs font-bold'
                : 'text-gray-600 hover:text-gray-900'
            }`}
          >
            <Phone className="w-3.5 h-3.5" />
            <span>Shopper Login</span>
          </button>

          <button
            onClick={() => { setActiveTab('seller'); setErrorMsg(''); }}
            className={`flex-1 py-2 rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
              activeTab === 'seller'
                ? 'bg-brand-orange text-white shadow-xs font-bold'
                : 'text-gray-600 hover:text-gray-900'
            }`}
          >
            <Store className="w-3.5 h-3.5" />
            <span>Artisan & Seller</span>
          </button>

          <button
            onClick={() => { setActiveTab('admin'); setErrorMsg(''); }}
            className={`flex-1 py-2 rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
              activeTab === 'admin'
                ? 'bg-slate-900 text-white shadow-xs font-bold'
                : 'text-gray-600 hover:text-gray-900'
            }`}
          >
            <ShieldCheck className="w-3.5 h-3.5" />
            <span>Staff & Admin</span>
          </button>
        </div>

        {/* Modal Body */}
        <div className="p-5 sm:p-6 space-y-4">
          
          {errorMsg && (
            <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center gap-2">
              <AlertCircle className="w-4 h-4 text-red-600 shrink-0" />
              <span>{errorMsg}</span>
            </div>
          )}

          {/* 1. CUSTOMER LOGIN */}
          {activeTab === 'customer' && (
            <div className="space-y-4">
              <div className="flex gap-2 p-1 bg-gray-100 rounded-xl text-xs">
                <button
                  type="button"
                  onClick={() => { setCustomerMode('otp'); setErrorMsg(''); }}
                  className={`flex-1 py-1.5 rounded-lg font-semibold transition-all cursor-pointer ${
                    customerMode === 'otp' ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'text-gray-500'
                  }`}
                >
                  Mobile OTP
                </button>
                <button
                  type="button"
                  onClick={() => { setCustomerMode('email'); setErrorMsg(''); }}
                  className={`flex-1 py-1.5 rounded-lg font-semibold transition-all cursor-pointer ${
                    customerMode === 'email' ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'text-gray-500'
                  }`}
                >
                  Email & Password
                </button>
              </div>

              {customerMode === 'otp' ? (
                <div>
                  {!otpSent ? (
                    <form onSubmit={handleSendOtp} className="space-y-3 text-xs">
                      <div>
                        <label className="block font-semibold text-gray-700 mb-1">
                          Enter Indian Mobile Number
                        </label>
                        <div className="flex">
                          <span className="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-300 bg-gray-100 text-gray-600 font-semibold text-xs">
                            +91
                          </span>
                          <input
                            type="tel"
                            maxLength={10}
                            placeholder="98765 43210"
                            value={customerPhone}
                            onChange={(e) => setCustomerPhone(e.target.value.replace(/\D/g, ''))}
                            className="w-full px-3 py-2.5 border border-gray-300 rounded-r-xl text-sm font-medium focus:ring-2 focus:ring-brand-green"
                            autoFocus
                          />
                        </div>
                      </div>

                      <button
                        type="submit"
                        disabled={isLoading || customerPhone.length < 10}
                        className="w-full py-2.5 bg-brand-green hover:bg-brand-green-dark disabled:bg-gray-300 text-white font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer"
                      >
                        {isLoading ? <RotateCw className="w-4 h-4 animate-spin" /> : 'Get 6-Digit Verification OTP'}
                      </button>
                    </form>
                  ) : (
                    <form onSubmit={handleVerifyOtp} className="space-y-3 text-xs">
                      <div>
                        <div className="flex justify-between items-center mb-1">
                          <label className="font-semibold text-gray-700">Enter OTP Code</label>
                          <span className="text-[11px] text-brand-green font-bold">+91 {customerPhone}</span>
                        </div>
                        <input
                          type="text"
                          maxLength={6}
                          placeholder="••••••"
                          value={customerOtp}
                          onChange={(e) => setCustomerOtp(e.target.value.replace(/\D/g, ''))}
                          className="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-center text-lg tracking-widest font-mono font-bold"
                          autoFocus
                        />
                      </div>

                      <button
                        type="submit"
                        className="w-full py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-xs cursor-pointer"
                      >
                        Verify & Sign In
                      </button>

                      <div className="flex items-center justify-between text-[11px] text-gray-500 pt-1">
                        <span>Didn't receive code?</span>
                        {countdown > 0 ? (
                          <span className="font-mono">Resend in {countdown}s</span>
                        ) : (
                          <button
                            type="button"
                            onClick={handleSendOtp}
                            className="text-brand-green font-bold hover:underline cursor-pointer"
                          >
                            Resend OTP
                          </button>
                        )}
                      </div>
                    </form>
                  )}
                </div>
              ) : (
                <form onSubmit={handleCustomerEmailLogin} className="space-y-3 text-xs">
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Email Address or Username</label>
                    <input
                      type="text"
                      required
                      placeholder="e.g. aarav_shopper or you@example.com"
                      value={customerEmail}
                      onChange={(e) => setCustomerEmail(e.target.value)}
                      className="w-full p-2.5 border border-gray-300 rounded-xl"
                    />
                  </div>
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Password</label>
                    <input
                      type="password"
                      placeholder="Enter account password"
                      value={customerPassword}
                      onChange={(e) => setCustomerPassword(e.target.value)}
                      className="w-full p-2.5 border border-gray-300 rounded-xl"
                    />
                  </div>
                  <button
                    type="submit"
                    className="w-full py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-xs cursor-pointer"
                  >
                    Sign In to Account
                  </button>
                </form>
              )}

              {/* Social Login Button */}
              <div className="pt-2 border-t border-gray-100">
                <button
                  type="button"
                  onClick={() => loginWithSocial('google')}
                  className="w-full py-2 border border-gray-300 hover:bg-gray-50 rounded-xl text-xs font-semibold text-gray-700 flex items-center justify-center gap-2 transition-colors cursor-pointer"
                >
                  <svg className="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                  </svg>
                  <span>Continue with Google</span>
                </button>
              </div>
            </div>
          )}

          {/* 2. SELLER / ARTISAN LOGIN */}
          {activeTab === 'seller' && (
            <div className="space-y-4">
              <div className="p-3 bg-orange-50 rounded-xl border border-orange-200 text-xs text-orange-950 flex items-center gap-2">
                <Store className="w-4 h-4 text-brand-orange shrink-0" />
                <span>Dedicated portal for rural blacksmiths, weavers, potters and registered SHGs.</span>
              </div>

              <form onSubmit={handleSellerLogin} className="space-y-3 text-xs">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Artisan Username, Seller Email, or Mobile
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. ramkishan_artisan, +91 83035 78806, or email"
                    value={sellerIdentifier}
                    onChange={(e) => setSellerIdentifier(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                    autoFocus
                  />
                </div>

                <div>
                  <div className="flex justify-between items-center mb-1">
                    <label className="font-semibold text-gray-700">Password / Access Key</label>
                    <span className="text-[10px] text-gray-400 font-mono">PBKDF2 Encrypted</span>
                  </div>
                  <input
                    type="password"
                    placeholder="Enter seller password"
                    value={sellerPassOrOtp}
                    onChange={(e) => setSellerPassOrOtp(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>

                <button
                  type="submit"
                  className="w-full py-2.5 bg-brand-orange hover:bg-orange-600 text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5"
                >
                  <Store className="w-4 h-4" />
                  <span>Access Seller Workshop Dashboard</span>
                </button>
              </form>

              <div className="p-3 bg-gray-50 rounded-xl border border-gray-200 text-center text-xs">
                <span className="text-gray-500">Not registered as a seller yet?</span>
                <Link
                  to="/seller-register"
                  onClick={closeAuthModal}
                  className="ml-1 text-brand-green font-bold hover:underline"
                >
                  Register Artisan Workshop →
                </Link>
              </div>
            </div>
          )}

          {/* 3. ADMIN PORTAL LOGIN */}
          {activeTab === 'admin' && (
            <div className="space-y-4">
              <div className="p-3 bg-slate-900 text-white rounded-xl text-xs space-y-1">
                <div className="flex items-center gap-1.5 font-bold text-amber-300">
                  <ShieldCheck className="w-4 h-4 text-amber-400" />
                  <span>Enterprise Security Gateway</span>
                </div>
                <p className="text-[11px] text-slate-300">
                  Authorized access for Super Admin, Operations, Marketing, and Content Team.
                </p>
              </div>

              <form onSubmit={handleAdminLogin} className="space-y-3 text-xs">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Admin Username or Official Email</label>
                  <input
                    type="text"
                    required
                    placeholder="Username (e.g. anubhav, sunil_ops) or email"
                    value={adminIdentifier}
                    onChange={(e) => setAdminIdentifier(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-medium"
                    autoFocus
                  />
                </div>

                <div>
                  <div className="flex justify-between items-center mb-1">
                    <label className="font-semibold text-gray-700">Security Passcode / Password</label>
                    <span className="text-[10px] text-gray-400 font-mono">PBKDF2 Encrypted</span>
                  </div>
                  <input
                    type="password"
                    required
                    placeholder="Enter authorized security password"
                    value={adminPasscode}
                    onChange={(e) => setAdminPasscode(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-sm"
                  />
                </div>

                <button
                  type="submit"
                  className="w-full py-2.5 bg-slate-900 hover:bg-black text-amber-300 font-bold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5"
                >
                  <Lock className="w-4 h-4 text-amber-400" />
                  <span>Authenticate & Open Admin Console</span>
                </button>
              </form>
            </div>
          )}

        </div>

        {/* Footer */}
        <div className="bg-gray-50 px-5 py-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
          <span>GaunGaliHat Security System</span>
          <span className="font-mono text-emerald-700 font-semibold">256-bit Edge Encrypted</span>
        </div>
      </div>
    </div>
  );
};
