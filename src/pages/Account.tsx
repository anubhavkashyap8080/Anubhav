import React, { useState, useEffect } from 'react';
import {
  User as UserIcon,
  Package,
  MapPin,
  Heart,
  ShieldCheck,
  ExternalLink,
  ChevronRight,
  LogOut,
  Edit3,
  Save,
  CheckCircle2,
  AlertCircle,
  Eye,
  EyeOff,
  KeyRound,
  Store,
  Truck,
  FileText,
  Clock,
  Sparkles,
  ArrowRight,
  Plus,
  Trash2
} from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { usePlatform } from '../context/PlatformContext';
import { useCartWishlist } from '../context/CartWishlistContext';
import { Link } from 'react-router-dom';
import { hashPassword, verifyPassword } from '../utils/cryptoUtils';

interface OrderItem {
  name: string;
  qty: number;
  price: number;
  seller: string;
}

interface CustomerOrder {
  id: string;
  date: string;
  total: number;
  status: 'Order Placed' | 'In Transit' | 'Out for Delivery' | 'Delivered';
  items: OrderItem[];
  trackingNo: string;
}

interface SavedAddress {
  id: string;
  title: string;
  fullName: string;
  phone: string;
  street: string;
  villageOrCity: string;
  district: string;
  state: string;
  pincode: string;
  isDefault: boolean;
}

export const Account: React.FC = () => {
  const { currentUser, logout, openAuthModal, updateCurrentUserProfile } = useAuth();
  const { updateUserProfile, updateUserPassword } = usePlatform();
  const { wishlistCount } = useCartWishlist();

  const [activeTab, setActiveTab] = useState<'orders' | 'profile' | 'security' | 'addresses'>('orders');

  // Profile Form State
  const [profileName, setProfileName] = useState(currentUser?.name || '');
  const [profileEmail, setProfileEmail] = useState(currentUser?.email || '');
  const [profilePhone, setProfilePhone] = useState(currentUser?.phone || '');
  const [profileCity, setProfileCity] = useState(currentUser?.city || 'Hardoi');
  const [profileState, setProfileState] = useState(currentUser?.state || 'Uttar Pradesh');
  const [profilePincode, setProfilePincode] = useState(currentUser?.pincode || '241001');
  const [profileSavedMsg, setProfileSavedMsg] = useState('');

  // Password Security Form State
  const [currentPass, setCurrentPass] = useState('');
  const [newPass, setNewPass] = useState('');
  const [confirmPass, setConfirmPass] = useState('');
  const [showPass, setShowPass] = useState(false);
  const [passError, setPassError] = useState('');
  const [passSuccess, setPassSuccess] = useState('');
  const [isChangingPass, setIsChangingPass] = useState(false);

  // Synced profile inputs on currentUser change
  useEffect(() => {
    if (currentUser) {
      setProfileName(currentUser.name || '');
      setProfileEmail(currentUser.email || '');
      setProfilePhone(currentUser.phone || '');
      if (currentUser.city) setProfileCity(currentUser.city);
      if (currentUser.state) setProfileState(currentUser.state);
      if (currentUser.pincode) setProfilePincode(currentUser.pincode);
    }
  }, [currentUser]);

  // Orders State (Stored in localStorage or initial sample orders)
  const [orders, setOrders] = useState<CustomerOrder[]>(() => {
    try {
      const saved = localStorage.getItem('ggh_customer_orders');
      if (saved) return JSON.parse(saved);
    } catch {
      // fallback
    }
    return [
      {
        id: 'GGH-2026-8812',
        date: 'October 14, 2026',
        total: 1250,
        status: 'Delivered',
        items: [
          { name: 'S S Industries Forged Desi Hansiya (Sickle)', qty: 2, price: 590, seller: 'S S Industries Hardoi Works' }
        ],
        trackingNo: 'INPOST-8812904-HRD'
      },
      {
        id: 'GGH-2026-7910',
        date: 'September 28, 2026',
        total: 620,
        status: 'Delivered',
        items: [
          { name: 'Heavy Agricultural Steel Fawda / Kudal', qty: 1, price: 340, seller: 'Hardoi Blacksmith Foundry' },
          { name: '100% Biodegradable Golden Jute Jhola', qty: 1, price: 280, seller: 'Saraswati Mahila SHG' }
        ],
        trackingNo: 'INPOST-7910442-HRD'
      }
    ];
  });

  // Saved Addresses State
  const [addresses, setAddresses] = useState<SavedAddress[]>(() => {
    try {
      const saved = localStorage.getItem('ggh_saved_addresses');
      if (saved) return JSON.parse(saved);
    } catch {
      // fallback
    }
    return [
      {
        id: 'addr-1',
        title: 'Home / Primary Residence',
        fullName: currentUser?.name || 'Customer Account',
        phone: currentUser?.phone || '+91 98765 43210',
        street: 'Main Bazaar Road, Near Post Office',
        villageOrCity: 'Hardoi',
        district: 'Hardoi',
        state: 'Uttar Pradesh',
        pincode: '241001',
        isDefault: true
      }
    ];
  });

  const [showAddAddress, setShowAddAddress] = useState(false);
  const [newAddrTitle, setNewAddrTitle] = useState('Farm / Workshop');
  const [newAddrStreet, setNewAddrStreet] = useState('');
  const [newAddrCity, setNewAddrCity] = useState('');
  const [newAddrDistrict, setNewAddrDistrict] = useState('');
  const [newAddrState, setNewAddrState] = useState('Uttar Pradesh');
  const [newAddrPincode, setNewAddrPincode] = useState('');
  const [newAddrPhone, setNewAddrPhone] = useState('');

  // Active tracking modal
  const [selectedTrackingOrder, setSelectedTrackingOrder] = useState<CustomerOrder | null>(null);
  const [selectedInvoiceOrder, setSelectedInvoiceOrder] = useState<CustomerOrder | null>(null);

  // Handle Save Profile
  const handleSaveProfile = (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentUser) return;
    const updates = {
      name: profileName.trim(),
      email: profileEmail.trim(),
      phone: profilePhone.trim(),
      city: profileCity.trim(),
      state: profileState.trim(),
      pincode: profilePincode.trim()
    };
    updateCurrentUserProfile(updates);
    updateUserProfile(currentUser.id, updates);
    setProfileSavedMsg('Profile information updated and saved successfully!');
    setTimeout(() => setProfileSavedMsg(''), 4000);
  };

  // Handle Password Change with PBKDF2
  const handlePasswordChange = async (e: React.FormEvent) => {
    e.preventDefault();
    setPassError('');
    setPassSuccess('');

    if (!newPass.trim()) {
      setPassError('Please enter a new password.');
      return;
    }
    if (newPass.length < 6) {
      setPassError('Password must be at least 6 characters.');
      return;
    }
    if (newPass !== confirmPass) {
      setPassError('New password and confirmation password do not match.');
      return;
    }

    setIsChangingPass(true);

    if (currentUser?.passwordHash && currentUser?.passwordSalt) {
      const match = await verifyPassword(currentPass.trim(), currentUser.passwordHash, currentUser.passwordSalt);
      if (!match) {
        setIsChangingPass(false);
        setPassError('Current password is incorrect.');
        return;
      }
    }

    const { hashHex, saltHex } = await hashPassword(newPass.trim());
    if (currentUser) {
      updateUserPassword(currentUser.id, hashHex, saltHex);
      updateCurrentUserProfile({ passwordHash: hashHex, passwordSalt: saltHex });
    }

    setIsChangingPass(false);
    setCurrentPass('');
    setNewPass('');
    setConfirmPass('');
    setPassSuccess('Account password encrypted with PBKDF2 and saved successfully!');
    setTimeout(() => setPassSuccess(''), 4000);
  };

  // Handle Add Address
  const handleAddAddress = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newAddrStreet.trim() || !newAddrCity.trim() || !newAddrPincode.trim()) return;

    const newAddr: SavedAddress = {
      id: `addr-${Date.now()}`,
      title: newAddrTitle,
      fullName: currentUser?.name || 'Customer',
      phone: newAddrPhone || currentUser?.phone || '',
      street: newAddrStreet,
      villageOrCity: newAddrCity,
      district: newAddrDistrict || newAddrCity,
      state: newAddrState,
      pincode: newAddrPincode,
      isDefault: addresses.length === 0
    };

    const updated = [...addresses, newAddr];
    setAddresses(updated);
    localStorage.setItem('ggh_saved_addresses', JSON.stringify(updated));
    setShowAddAddress(false);
    setNewAddrStreet('');
    setNewAddrCity('');
    setNewAddrDistrict('');
    setNewAddrPincode('');
    setNewAddrPhone('');
  };

  const handleDeleteAddress = (id: string) => {
    const updated = addresses.filter(a => a.id !== id);
    setAddresses(updated);
    localStorage.setItem('ggh_saved_addresses', JSON.stringify(updated));
  };

  // ==========================================
  // 1. UNLOGGED IN STATE: TOOLSVILLA STANDARD SIGN IN
  // ==========================================
  if (!currentUser) {
    return (
      <div className="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-stone-50 via-emerald-50/20 to-stone-50">
        <div className="max-w-md w-full bg-white rounded-3xl p-8 border border-gray-200 shadow-xl text-center space-y-6">
          <div className="w-16 h-16 mx-auto rounded-2xl bg-emerald-100 text-brand-green flex items-center justify-center shadow-inner">
            <UserIcon className="w-8 h-8" />
          </div>

          <div className="space-y-2">
            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-brand-green text-xs font-bold border border-emerald-200">
              <Sparkles className="w-3.5 h-3.5" />
              <span>GaunGaliHat Customer Portal</span>
            </span>
            <h1 className="text-2xl font-bold font-display text-gray-900">
              Sign In to Your Account
            </h1>
            <p className="text-xs text-gray-500 max-w-sm mx-auto leading-relaxed">
              Access your orders, track rural tool dispatches, download GST invoices, and manage your village delivery addresses.
            </p>
          </div>

          <div className="space-y-3 pt-2">
            <button
              onClick={() => openAuthModal('customer')}
              className="w-full py-3 bg-brand-green hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md flex items-center justify-center gap-2 cursor-pointer transition-colors text-sm"
            >
              <UserIcon className="w-4 h-4" />
              <span>Shopper Sign In / Register</span>
              <ArrowRight className="w-4 h-4" />
            </button>

            <button
              onClick={() => openAuthModal('seller')}
              className="w-full py-2.5 bg-brand-orange hover:bg-amber-600 text-white font-bold rounded-xl shadow-xs flex items-center justify-center gap-2 cursor-pointer transition-colors text-xs"
            >
              <Store className="w-4 h-4" />
              <span>Artisan & Seller Portal Login</span>
            </button>
          </div>

          <div className="pt-4 border-t border-gray-100 grid grid-cols-2 gap-3 text-left">
            <div className="p-3 bg-gray-50 rounded-xl">
              <Truck className="w-4 h-4 text-emerald-700 mb-1" />
              <p className="text-[11px] font-bold text-gray-800">Live Dispatches</p>
              <p className="text-[10px] text-gray-500">Track shipments via India Post</p>
            </div>
            <div className="p-3 bg-gray-50 rounded-xl">
              <ShieldCheck className="w-4 h-4 text-indigo-700 mb-1" />
              <p className="text-[11px] font-bold text-gray-800">PBKDF2 Security</p>
              <p className="text-[10px] text-gray-500">Encrypted user credentials</p>
            </div>
          </div>
        </div>
      </div>
    );
  }

  // User initials for avatar
  const initials = currentUser.name
    ? currentUser.name
        .split(' ')
        .map(n => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()
    : 'U';

  const isSellerOrAdmin = ['vendor', 'admin', 'super_admin'].includes(currentUser.role);

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      
      {/* Account Header */}
      <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div className="flex items-center gap-4">
          <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-800 text-white flex items-center justify-center font-bold text-2xl font-display shadow-md shrink-0">
            {currentUser.avatar ? (
              <img
                src={currentUser.avatar}
                alt={currentUser.name}
                className="w-full h-full object-cover rounded-2xl"
                onError={(e) => { (e.target as HTMLElement).style.display = 'none'; }}
              />
            ) : (
              initials
            )}
          </div>
          <div>
            <div className="flex items-center gap-2 flex-wrap">
              <h1 className="text-2xl font-bold text-gray-900 font-display">
                {currentUser.name || 'GaunGaliHat Member'}
              </h1>
              <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${
                currentUser.role === 'customer'
                  ? 'bg-emerald-100 text-emerald-800'
                  : currentUser.role === 'vendor'
                  ? 'bg-orange-100 text-orange-800'
                  : 'bg-indigo-100 text-indigo-900'
              }`}>
                {currentUser.role === 'customer'
                  ? 'Verified Shopper'
                  : currentUser.role === 'vendor'
                  ? 'Artisan Workshop Partner'
                  : 'Staff / Administrator'}
              </span>
            </div>
            <p className="text-xs text-gray-500 mt-1">
              {currentUser.email || 'No email attached'} • {currentUser.phone || 'No phone attached'}
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          {isSellerOrAdmin && (
            <Link
              to="/seller/dashboard"
              className="px-4 py-2 bg-brand-orange hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition-colors"
            >
              <Store className="w-4 h-4" />
              <span>Seller Workshop</span>
            </Link>
          )}

          <Link
            to="/wishlist"
            className="px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl text-xs font-semibold text-gray-700 flex items-center gap-1.5 transition-colors"
          >
            <Heart className="w-4 h-4 text-brand-orange" />
            <span>Wishlist ({wishlistCount})</span>
          </Link>

          <button
            onClick={logout}
            className="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-xl border border-red-200 flex items-center gap-1.5 cursor-pointer transition-colors"
          >
            <LogOut className="w-3.5 h-3.5" />
            <span>Sign Out</span>
          </button>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex border-b border-gray-200 gap-6 text-xs sm:text-sm font-bold overflow-x-auto pb-0.5">
        <button
          onClick={() => setActiveTab('orders')}
          className={`pb-3 border-b-2 cursor-pointer transition-colors flex items-center gap-2 shrink-0 ${
            activeTab === 'orders'
              ? 'border-brand-green text-brand-green'
              : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          <Package className="w-4 h-4" />
          <span>My Orders & Tracking ({orders.length})</span>
        </button>
        <button
          onClick={() => setActiveTab('profile')}
          className={`pb-3 border-b-2 cursor-pointer transition-colors flex items-center gap-2 shrink-0 ${
            activeTab === 'profile'
              ? 'border-brand-green text-brand-green'
              : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          <UserIcon className="w-4 h-4" />
          <span>Profile Details</span>
        </button>
        <button
          onClick={() => setActiveTab('security')}
          className={`pb-3 border-b-2 cursor-pointer transition-colors flex items-center gap-2 shrink-0 ${
            activeTab === 'security'
              ? 'border-brand-green text-brand-green'
              : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          <ShieldCheck className="w-4 h-4" />
          <span>Security & Password</span>
        </button>
        <button
          onClick={() => setActiveTab('addresses')}
          className={`pb-3 border-b-2 cursor-pointer transition-colors flex items-center gap-2 shrink-0 ${
            activeTab === 'addresses'
              ? 'border-brand-green text-brand-green'
              : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          <MapPin className="w-4 h-4" />
          <span>Saved Delivery Addresses ({addresses.length})</span>
        </button>
      </div>

      {/* TAB 1: ORDERS & TRACKING */}
      {activeTab === 'orders' && (
        <div className="space-y-4">
          {orders.length === 0 ? (
            <div className="bg-white rounded-3xl border border-gray-100 p-12 text-center space-y-3">
              <Package className="w-12 h-12 text-gray-300 mx-auto" />
              <h3 className="font-bold text-gray-800 text-base">No orders placed yet</h3>
              <p className="text-xs text-gray-500 max-w-sm mx-auto">
                Explore hand-forged agricultural implements, handlooms, and village staples crafted by rural artisans.
              </p>
              <Link
                to="/"
                className="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-green text-white text-xs font-bold rounded-xl shadow-xs mt-2"
              >
                <span>Start Shopping</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </Link>
            </div>
          ) : (
            orders.map(order => (
              <div key={order.id} className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100 text-xs">
                  <div>
                    <span className="font-bold text-gray-900 font-mono">{order.id}</span>
                    <span className="text-gray-400 mx-2">•</span>
                    <span className="text-gray-500">Ordered on {order.date}</span>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 font-bold text-[10px] border border-emerald-200">
                      ✓ {order.status}
                    </span>
                    <span className="font-extrabold text-sm text-gray-900">Total: ₹{order.total.toLocaleString()}</span>
                  </div>
                </div>

                <div className="divide-y divide-gray-50">
                  {order.items.map((item, idx) => (
                    <div key={idx} className="py-2.5 flex items-center justify-between text-xs">
                      <div>
                        <p className="font-bold text-gray-800">{item.name}</p>
                        <p className="text-[11px] text-gray-500">Artisan / Workshop: {item.seller} • Qty: {item.qty}</p>
                      </div>
                      <span className="font-semibold text-gray-900">₹{item.price.toLocaleString()}</span>
                    </div>
                  ))}
                </div>

                <div className="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                  <span className="text-gray-500 font-mono text-[11px] flex items-center gap-1.5">
                    <Truck className="w-3.5 h-3.5 text-gray-400" />
                    <span>Carrier: India Post Express ({order.trackingNo})</span>
                  </span>
                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => setSelectedTrackingOrder(order)}
                      className="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold rounded-lg border border-gray-200 cursor-pointer flex items-center gap-1 transition-colors"
                    >
                      <Truck className="w-3.5 h-3.5 text-brand-green" />
                      <span>Track Shipment</span>
                    </button>
                    <button
                      onClick={() => setSelectedInvoiceOrder(order)}
                      className="px-3 py-1.5 bg-brand-green hover:bg-emerald-700 text-white font-semibold rounded-lg cursor-pointer flex items-center gap-1 transition-colors"
                    >
                      <FileText className="w-3.5 h-3.5" />
                      <span>Tax Invoice</span>
                    </button>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      )}

      {/* TAB 2: PROFILE DETAILS */}
      {activeTab === 'profile' && (
        <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 max-w-2xl space-y-5 text-xs">
          <div className="border-b border-gray-100 pb-3 flex items-center justify-between">
            <div>
              <h2 className="font-bold text-base text-gray-900 font-display">Personal Profile & Contact</h2>
              <p className="text-xs text-gray-500">Update your name, primary email address, and communication mobile.</p>
            </div>
          </div>

          {profileSavedMsg && (
            <div className="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2">
              <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
              <span>{profileSavedMsg}</span>
            </div>
          )}

          <form onSubmit={handleSaveProfile} className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Full Name *</label>
                <input
                  type="text"
                  required
                  value={profileName}
                  onChange={(e) => setProfileName(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-medium text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Email Address *</label>
                <input
                  type="email"
                  required
                  value={profileEmail}
                  onChange={(e) => setProfileEmail(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-medium text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Mobile Number (Phone OTP) *</label>
                <input
                  type="tel"
                  required
                  value={profilePhone}
                  onChange={(e) => setProfilePhone(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">City / Village Cluster</label>
                <input
                  type="text"
                  value={profileCity}
                  onChange={(e) => setProfileCity(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-medium text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">State / Province</label>
                <input
                  type="text"
                  value={profileState}
                  onChange={(e) => setProfileState(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-medium text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Postal Pincode</label>
                <input
                  type="text"
                  value={profilePincode}
                  onChange={(e) => setProfilePincode(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900"
                />
              </div>
            </div>

            <div className="pt-2">
              <button
                type="submit"
                className="px-6 py-2.5 bg-brand-green hover:bg-emerald-700 text-white font-bold rounded-xl cursor-pointer flex items-center gap-2 shadow-xs transition-colors"
              >
                <Save className="w-4 h-4" />
                <span>Save Profile Changes</span>
              </button>
            </div>
          </form>
        </div>
      )}

      {/* TAB 3: SECURITY & PASSWORD (PBKDF2) */}
      {activeTab === 'security' && (
        <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 max-w-2xl space-y-5 text-xs">
          <div className="border-b border-gray-100 pb-3 flex items-center justify-between">
            <div>
              <h2 className="font-bold text-base text-gray-900 font-display flex items-center gap-2">
                <ShieldCheck className="w-5 h-5 text-indigo-600" />
                <span>Password & Encryption Credentials</span>
              </h2>
              <p className="text-xs text-gray-500">
                Update your account password. Encrypted client-side using PBKDF2-HMAC-SHA256 with 100,000 iterations.
              </p>
            </div>
            <span className="hidden sm:inline-flex items-center gap-1 text-[11px] font-mono px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full font-bold border border-indigo-200">
              PBKDF2 SHA-256
            </span>
          </div>

          {passError && (
            <div className="p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2">
              <AlertCircle className="w-4 h-4 text-red-600 shrink-0" />
              <span>{passError}</span>
            </div>
          )}

          {passSuccess && (
            <div className="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2">
              <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
              <span>{passSuccess}</span>
            </div>
          )}

          <form onSubmit={handlePasswordChange} className="space-y-4">
            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Current Password {currentUser.passwordHash ? '*' : '(Leave empty if first time setting password)'}
              </label>
              <div className="relative">
                <input
                  type={showPass ? 'text' : 'password'}
                  value={currentPass}
                  onChange={(e) => setCurrentPass(e.target.value)}
                  placeholder="Enter current password"
                  autoCapitalize="none"
                  autoCorrect="off"
                  className="w-full p-2.5 pr-10 border border-gray-300 rounded-xl font-mono text-gray-900"
                />
                <button
                  type="button"
                  onClick={() => setShowPass(!showPass)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  {showPass ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">New Password *</label>
                <input
                  type={showPass ? 'text' : 'password'}
                  required
                  value={newPass}
                  onChange={(e) => setNewPass(e.target.value)}
                  placeholder="Minimum 6 characters"
                  autoCapitalize="none"
                  autoCorrect="off"
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Confirm New Password *</label>
                <input
                  type={showPass ? 'text' : 'password'}
                  required
                  value={confirmPass}
                  onChange={(e) => setConfirmPass(e.target.value)}
                  placeholder="Re-enter new password"
                  autoCapitalize="none"
                  autoCorrect="off"
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900"
                />
              </div>
            </div>

            <div className="pt-2 flex items-center gap-3">
              <button
                type="submit"
                disabled={isChangingPass}
                className="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold rounded-xl cursor-pointer flex items-center gap-2 shadow-xs transition-colors"
              >
                <KeyRound className="w-4 h-4" />
                <span>{isChangingPass ? 'Encrypting & Saving...' : 'Update Password'}</span>
              </button>
            </div>
          </form>
        </div>
      )}

      {/* TAB 4: SAVED DELIVERY ADDRESSES */}
      {activeTab === 'addresses' && (
        <div className="space-y-6">
          <div className="flex items-center justify-between">
            <p className="text-xs text-gray-500">Saved home, agricultural workshop, and farm dispatch points.</p>
            <button
              onClick={() => setShowAddAddress(true)}
              className="px-3.5 py-2 bg-brand-green hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 cursor-pointer transition-colors"
            >
              <Plus className="w-4 h-4" />
              <span>Add New Address</span>
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {addresses.map(addr => (
              <div
                key={addr.id}
                className={`bg-white rounded-2xl p-5 space-y-2.5 text-xs relative border ${
                  addr.isDefault ? 'border-brand-green shadow-xs' : 'border-gray-200'
                }`}
              >
                {addr.isDefault && (
                  <span className="absolute top-4 right-4 px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">
                    Default
                  </span>
                )}
                <div className="flex items-center gap-2 font-bold text-sm text-gray-900">
                  <MapPin className="w-4 h-4 text-brand-orange shrink-0" />
                  <span>{addr.title}</span>
                </div>
                <p className="font-semibold text-gray-800">{addr.fullName}</p>
                <p className="text-gray-600 leading-relaxed">
                  {addr.street}, {addr.villageOrCity}, {addr.district}, {addr.state} - {addr.pincode}
                </p>
                <p className="text-gray-500 font-mono text-[11px]">Contact: {addr.phone}</p>
                <div className="pt-2 flex items-center gap-3 border-t border-gray-100">
                  <button
                    onClick={() => handleDeleteAddress(addr.id)}
                    className="text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold cursor-pointer"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                    <span>Delete</span>
                  </button>
                </div>
              </div>
            ))}
          </div>

          {/* Add Address Modal */}
          {showAddAddress && (
            <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
              <div className="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
                <div className="flex items-center justify-between border-b border-gray-100 pb-3">
                  <h3 className="font-bold text-base text-gray-900 font-display">Add Delivery Address</h3>
                  <button
                    onClick={() => setShowAddAddress(false)}
                    className="text-gray-400 hover:text-gray-600 p-1"
                  >
                    ✕
                  </button>
                </div>

                <form onSubmit={handleAddAddress} className="space-y-3 text-xs">
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Address Label</label>
                    <input
                      type="text"
                      value={newAddrTitle}
                      onChange={(e) => setNewAddrTitle(e.target.value)}
                      placeholder="e.g. Home, Village Farmhouse, Workshop"
                      className="w-full p-2.5 border border-gray-300 rounded-xl"
                    />
                  </div>

                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Street / House No. / Landmark *</label>
                    <input
                      type="text"
                      required
                      value={newAddrStreet}
                      onChange={(e) => setNewAddrStreet(e.target.value)}
                      placeholder="e.g. Plot 12, Near Hanuman Temple"
                      className="w-full p-2.5 border border-gray-300 rounded-xl"
                    />
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <label className="block font-semibold text-gray-700 mb-1">Village / City *</label>
                      <input
                        type="text"
                        required
                        value={newAddrCity}
                        onChange={(e) => setNewAddrCity(e.target.value)}
                        placeholder="e.g. Hardoi"
                        className="w-full p-2.5 border border-gray-300 rounded-xl"
                      />
                    </div>
                    <div>
                      <label className="block font-semibold text-gray-700 mb-1">District</label>
                      <input
                        type="text"
                        value={newAddrDistrict}
                        onChange={(e) => setNewAddrDistrict(e.target.value)}
                        placeholder="e.g. Hardoi"
                        className="w-full p-2.5 border border-gray-300 rounded-xl"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <label className="block font-semibold text-gray-700 mb-1">State *</label>
                      <input
                        type="text"
                        required
                        value={newAddrState}
                        onChange={(e) => setNewAddrState(e.target.value)}
                        className="w-full p-2.5 border border-gray-300 rounded-xl"
                      />
                    </div>
                    <div>
                      <label className="block font-semibold text-gray-700 mb-1">Pincode *</label>
                      <input
                        type="text"
                        required
                        value={newAddrPincode}
                        onChange={(e) => setNewAddrPincode(e.target.value)}
                        placeholder="e.g. 241001"
                        className="w-full p-2.5 border border-gray-300 rounded-xl font-mono"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Delivery Contact Phone</label>
                    <input
                      type="tel"
                      value={newAddrPhone}
                      onChange={(e) => setNewAddrPhone(e.target.value)}
                      placeholder="+91 98765 43210"
                      className="w-full p-2.5 border border-gray-300 rounded-xl font-mono"
                    />
                  </div>

                  <div className="pt-2 flex justify-end gap-2">
                    <button
                      type="button"
                      onClick={() => setShowAddAddress(false)}
                      className="px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-xl"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      className="px-5 py-2 bg-brand-green hover:bg-emerald-700 text-white font-bold rounded-xl cursor-pointer"
                    >
                      Save Address
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}
        </div>
      )}

      {/* TRACKING MODAL */}
      {selectedTrackingOrder && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div className="flex items-center justify-between border-b border-gray-100 pb-3">
              <div>
                <h3 className="font-bold text-base text-gray-900 font-display">Live Shipment Tracking</h3>
                <p className="text-xs text-gray-500 font-mono">{selectedTrackingOrder.id}</p>
              </div>
              <button
                onClick={() => setSelectedTrackingOrder(null)}
                className="text-gray-400 hover:text-gray-600 p-1"
              >
                ✕
              </button>
            </div>

            <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs space-y-1">
              <div className="flex justify-between font-semibold text-emerald-900">
                <span>Carrier: India Post Express</span>
                <span className="font-mono">{selectedTrackingOrder.trackingNo}</span>
              </div>
              <p className="text-[11px] text-emerald-700">Dispatched from Hardoi Rural Artisan Cluster Hub</p>
            </div>

            <div className="space-y-3 pt-2 text-xs">
              <div className="flex gap-3">
                <div className="flex flex-col items-center">
                  <div className="w-3 h-3 rounded-full bg-emerald-600 ring-4 ring-emerald-100"></div>
                  <div className="w-0.5 h-10 bg-emerald-300"></div>
                </div>
                <div>
                  <p className="font-bold text-gray-900">Delivered Successfully</p>
                  <p className="text-[11px] text-gray-500">{selectedTrackingOrder.date} • Signed by Consignee</p>
                </div>
              </div>

              <div className="flex gap-3">
                <div className="flex flex-col items-center">
                  <div className="w-3 h-3 rounded-full bg-emerald-500"></div>
                  <div className="w-0.5 h-10 bg-gray-200"></div>
                </div>
                <div>
                  <p className="font-bold text-gray-800">Out for Final Delivery</p>
                  <p className="text-[11px] text-gray-500">Local Delivery Post Office</p>
                </div>
              </div>

              <div className="flex gap-3">
                <div className="flex flex-col items-center">
                  <div className="w-3 h-3 rounded-full bg-gray-400"></div>
                </div>
                <div>
                  <p className="font-bold text-gray-700">Dispatched from Foundry Works</p>
                  <p className="text-[11px] text-gray-500">Hardoi Central Sorting Facility</p>
                </div>
              </div>
            </div>

            <button
              onClick={() => setSelectedTrackingOrder(null)}
              className="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-xl text-xs cursor-pointer"
            >
              Close
            </button>
          </div>
        </div>
      )}

      {/* TAX INVOICE MODAL */}
      {selectedInvoiceOrder && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <div className="flex items-center justify-between border-b border-gray-100 pb-3">
              <div>
                <h3 className="font-bold text-base text-gray-900 font-display">Tax Invoice / Bill of Supply</h3>
                <p className="text-xs text-gray-500">GST Compliant E-Commerce Invoice</p>
              </div>
              <button
                onClick={() => setSelectedInvoiceOrder(null)}
                className="text-gray-400 hover:text-gray-600 p-1"
              >
                ✕
              </button>
            </div>

            <div className="p-4 bg-gray-50 rounded-2xl border border-gray-200 text-xs space-y-3 font-mono">
              <div className="flex justify-between border-b border-gray-200 pb-2">
                <div>
                  <p className="font-bold text-gray-900">GAUNGALIHAT RURAL ENTERPRISES</p>
                  <p className="text-[11px] text-gray-500">GSTIN: 09AABCG1234F1Z8</p>
                </div>
                <div className="text-right">
                  <p className="font-bold text-gray-900">INV-{selectedInvoiceOrder.id}</p>
                  <p className="text-[11px] text-gray-500">{selectedInvoiceOrder.date}</p>
                </div>
              </div>

              <div className="space-y-1 text-[11px]">
                <p><span className="text-gray-500">Billed To:</span> {currentUser.name}</p>
                <p><span className="text-gray-500">Phone:</span> {currentUser.phone}</p>
              </div>

              <div className="divide-y divide-gray-200 pt-1">
                {selectedInvoiceOrder.items.map((it, idx) => (
                  <div key={idx} className="py-1.5 flex justify-between">
                    <span>{it.name} (x{it.qty})</span>
                    <span>₹{it.price}</span>
                  </div>
                ))}
              </div>

              <div className="border-t-2 border-gray-800 pt-2 flex justify-between font-bold text-sm">
                <span>Total Amount Paid (Prepaid):</span>
                <span>₹{selectedInvoiceOrder.total}</span>
              </div>
            </div>

            <div className="flex gap-2">
              <button
                onClick={() => window.print()}
                className="flex-1 py-2.5 bg-brand-green hover:bg-emerald-700 text-white font-bold rounded-xl text-xs cursor-pointer flex items-center justify-center gap-1.5"
              >
                <FileText className="w-4 h-4" />
                <span>Print / Save PDF</span>
              </button>
              <button
                onClick={() => setSelectedInvoiceOrder(null)}
                className="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};