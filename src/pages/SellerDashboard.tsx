import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  TrendingUp,
  Package,
  Clock,
  Banknote,
  Plus,
  Store,
  CheckCircle2,
  AlertCircle,
  ExternalLink,
  ChevronRight,
  ShieldCheck,
  Printer,
  X,
  Search,
  Filter,
  Layers,
  Edit3,
  Trash2,
  Save,
  MapPin,
  Phone,
  Mail,
  Sparkles,
  Building2,
  Check,
  RefreshCw,
  QrCode,
  ArrowRight,
  FileText,
  LogOut,
  Lock,
  KeyRound,
  Eye,
  EyeOff
} from 'lucide-react';
import { usePlatform } from '../context/PlatformContext';
import { useAuth } from '../context/AuthContext';
import { Product, Seller } from '../types';
import { hashPassword, verifyPassword } from '../utils/cryptoUtils';

export const SellerDashboard: React.FC = () => {
  const { products, addProduct, updateProduct, deleteProduct, categories, vendors, updateUserPassword, updateUserProfile } = usePlatform();
  const { currentUser, logout, openAuthModal, loginAsSeller, updateCurrentUserProfile } = useAuth();
  const navigate = useNavigate();

  // Route Clearance Check
  const isAuthorizedSeller =
    currentUser &&
    ['vendor', 'admin', 'super_admin'].includes(currentUser.role);

  // Clearance Gate Form State
  const [gateIdentifier, setGateIdentifier] = useState('');
  const [gatePassOrOtp, setGatePassOrOtp] = useState('');
  const [gateError, setGateError] = useState('');
  const [gateLoading, setGateLoading] = useState(false);

  const handleGateSellerLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setGateError('');
    setGateLoading(true);
    const res = await loginAsSeller(gateIdentifier.trim(), gatePassOrOtp.trim());
    setGateLoading(false);
    if (!res.success) {
      setGateError(res.message || 'Access Denied: Invalid artisan credentials.');
    }
  };

  const handleSellerLogout = () => {
    logout();
    navigate('/');
  };

  // Password Change Form State
  const [currentPassInput, setCurrentPassInput] = useState('');
  const [newPassInput, setNewPassInput] = useState('');
  const [confirmPassInput, setConfirmPassInput] = useState('');
  const [showPass, setShowPass] = useState(false);
  const [passError, setPassError] = useState('');
  const [passSuccess, setPassSuccess] = useState('');
  const [isChangingPass, setIsChangingPass] = useState(false);

  const handlePasswordChange = async (e: React.FormEvent) => {
    e.preventDefault();
    setPassError('');
    setPassSuccess('');
    if (!newPassInput.trim()) {
      setPassError('Please enter a new password.');
      return;
    }
    if (newPassInput.length < 6) {
      setPassError('Password must be at least 6 characters.');
      return;
    }
    if (newPassInput !== confirmPassInput) {
      setPassError('New password and confirmation password do not match.');
      return;
    }

    setIsChangingPass(true);
    if (currentUser?.passwordHash && currentUser?.passwordSalt) {
      const match = await verifyPassword(currentPassInput.trim(), currentUser.passwordHash, currentUser.passwordSalt);
      if (!match) {
        setIsChangingPass(false);
        setPassError('Current password is incorrect.');
        return;
      }
    }

    const { hashHex, saltHex } = await hashPassword(newPassInput.trim());
    if (currentUser) {
      updateUserPassword(currentUser.id, hashHex, saltHex);
      updateCurrentUserProfile({ passwordHash: hashHex, passwordSalt: saltHex });
    }
    setIsChangingPass(false);
    setCurrentPassInput('');
    setNewPassInput('');
    setConfirmPassInput('');
    setPassSuccess('Security password updated and PBKDF2 encrypted successfully!');
    notify('Password changed and encrypted with PBKDF2-HMAC-SHA256!');
  };

  const handleSaveWorkshopProfile = () => {
    localStorage.setItem('ggh_vendor_profile', JSON.stringify(workshopProfile));
    localStorage.setItem('ggh_vendor_bank_details', JSON.stringify(bankDetails));
    if (currentUser) {
      updateUserProfile(currentUser.id, {
        name: workshopProfile.name,
        phone: workshopProfile.contactPhone
      });
      updateCurrentUserProfile({
        name: workshopProfile.name,
        phone: workshopProfile.contactPhone
      });
    }
    notify('Workshop profile and bank details saved securely across marketplace!');
  };

  // Active Main Tab: 'products' | 'orders' | 'payouts' | 'profile'
  const [activeTab, setActiveTab] = useState<'products' | 'orders' | 'payouts' | 'profile'>('products');

  // Toast Notification
  const [notification, setNotification] = useState<string | null>(null);
  const notify = (msg: string) => {
    setNotification(msg);
    setTimeout(() => setNotification(null), 3500);
  };

  // Workshop Profile State (Customizable by Artisan)
  const [workshopProfile, setWorkshopProfile] = useState(() => {
    try {
      const saved = localStorage.getItem('ggh_vendor_profile');
      if (saved) return JSON.parse(saved);
    } catch {
      // fallback
    }
    return {
      name: 'Saraswati Mahila SHG & Lohar Foundry',
      tagline: '18 Rural Women Artisans & Ancestral Coal Blacksmiths',
      village: 'Dewa / Madhoganj',
      panchayat: 'Newada Gram Panchayat',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      pincode: '241126',
      contactPhone: '+91 7897492100',
      whatsapp: '+91 7897492100',
      contactEmail: 'artisan@gaungalihat.com',
      memberCount: 18,
      specialty: 'High-Carbon Forged Farming Tools & Eco Handlooms',
      craftStory: 'Generational coal forge metallurgy and handloom weaving producing unbreakable hansiya sickles, heavy khurpi trowels, and natural jute market bags.'
    };
  });

  // Persist Workshop Profile
  useEffect(() => {
    localStorage.setItem('ggh_vendor_profile', JSON.stringify(workshopProfile));
  }, [workshopProfile]);

  // Bank & UPI Settlement State
  const [bankDetails, setBankDetails] = useState(() => {
    try {
      const saved = localStorage.getItem('ggh_vendor_bank_details');
      if (saved) return JSON.parse(saved);
    } catch {
      // fallback
    }
    return {
      accountHolder: 'Saraswati Mahila SHG',
      bankName: 'State Bank of India',
      accountNumber: '38920194821',
      ifscCode: 'SBIN0001245',
      branch: 'Madhoganj Branch, Hardoi',
      upiId: 'saraswati.shg@sbi'
    };
  });

  useEffect(() => {
    localStorage.setItem('ggh_vendor_bank_details', JSON.stringify(bankDetails));
  }, [bankDetails]);

  // Product Filters
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [productSearch, setProductSearch] = useState<string>('');
  const [stockFilter, setStockFilter] = useState<'all' | 'In Stock' | 'Only Few Left' | 'Out of Stock'>('all');

  // Add Product Modal State
  const [showAddModal, setShowAddModal] = useState(false);
  const [newTitle, setNewTitle] = useState('');
  const [newHindiTitle, setNewHindiTitle] = useState('');
  const [newCategorySlug, setNewCategorySlug] = useState('farming-traditional-tools');
  const [newPrice, setNewPrice] = useState('320');
  const [newOriginalPrice, setNewOriginalPrice] = useState('450');
  const [newStockStatus, setNewStockStatus] = useState<'In Stock' | 'Only Few Left' | 'Out of Stock'>('In Stock');
  const [newStockCount, setNewStockCount] = useState('50');
  const [newDescription, setNewDescription] = useState('');
  const [newImageUrl, setNewImageUrl] = useState('https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=600&q=80');
  const [newIsWomenMade, setNewIsWomenMade] = useState(true);
  const [newIsTool, setNewIsTool] = useState(true);

  // Quick Price Edit Modal State
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);
  const [editPrice, setEditPrice] = useState('');
  const [editOriginalPrice, setEditOriginalPrice] = useState('');

  // Orders State & Filters
  const [orderFilter, setOrderFilter] = useState<'all' | 'Ready for Dispatch' | 'In Transit' | 'Delivered'>('all');
  const [orders, setOrders] = useState([
    {
      id: 'ORD-9821',
      item: 'Forged Carbon Steel Hansiya Sickle (Hardwood Handle)',
      sku: 'GGH-TL-101',
      customer: 'Vikram Joshi',
      address: 'Plot 44, Indiranagar, Bengaluru, Karnataka - 560038',
      date: 'Today, 10:30 AM',
      amount: 360,
      payment: 'Prepaid (UPI)',
      status: 'Ready for Dispatch',
      consignmentNo: 'EU982147890IN'
    },
    {
      id: 'ORD-9818',
      item: 'Hand-Embroidered Jute Market Bag (Pack of 2)',
      sku: 'GGH-JT-301',
      customer: 'Suman Devi',
      address: 'Flat 302, Sea Breeze Apts, Bandra West, Mumbai - 400050',
      date: 'Yesterday, 4:15 PM',
      amount: 520,
      payment: 'Cash on Delivery (COD)',
      status: 'In Transit',
      consignmentNo: 'EU981845120IN'
    },
    {
      id: 'ORD-9804',
      item: 'Handmade Wooden Village Puppet Toy Set',
      sku: 'GGH-DC-401',
      customer: 'Deepak Sharma',
      address: 'B-12, Sector 62, Noida, Uttar Pradesh - 201301',
      date: 'Oct 18, 2026',
      amount: 450,
      payment: 'Prepaid (Card)',
      status: 'Delivered',
      consignmentNo: 'EU980411234IN'
    },
    {
      id: 'ORD-9799',
      item: 'Carbon Steel Garden Khurpi (Forged Blade)',
      sku: 'GGH-TL-102',
      customer: 'Rameshwar Yadav',
      address: 'Village Post Baragaon, Varanasi, UP - 221006',
      date: 'Oct 17, 2026',
      amount: 280,
      payment: 'Prepaid (UPI)',
      status: 'Delivered',
      consignmentNo: 'EU979989012IN'
    }
  ]);

  // Dispatch Label Print Modal State
  const [selectedOrderForLabel, setSelectedOrderForLabel] = useState<typeof orders[0] | null>(null);

  // Settlement & Payout Request State
  const [payoutAmount, setPayoutAmount] = useState('14500');
  const [showPayoutModal, setShowPayoutModal] = useState(false);
  const [payoutHistory, setPayoutHistory] = useState([
    { id: 'PAY-901', date: 'Oct 10, 2026', amount: 24000, fee: 1200, net: 22800, utr: 'SBIN26101089214', status: 'Settled to Bank' },
    { id: 'PAY-882', date: 'Sep 25, 2026', amount: 18500, fee: 925, net: 17575, utr: 'SBIN26092544102', status: 'Settled to Bank' }
  ]);

  // Filtered Products Logic
  const filteredProducts = products.filter((p) => {
    const matchesCategory =
      selectedCategory === 'all' ||
      p.categorySlug === selectedCategory ||
      p.category.toLowerCase().includes(selectedCategory.toLowerCase());

    const matchesSearch =
      p.name.toLowerCase().includes(productSearch.toLowerCase()) ||
      (p.hindiName && p.hindiName.includes(productSearch)) ||
      (p.code && p.code.toLowerCase().includes(productSearch.toLowerCase())) ||
      p.description.toLowerCase().includes(productSearch.toLowerCase());

    const matchesStock = stockFilter === 'all' || p.stockStatus === stockFilter;

    return matchesCategory && matchesSearch && matchesStock;
  });

  // Filtered Orders Logic
  const filteredOrders = orders.filter((o) => {
    if (orderFilter === 'all') return true;
    return o.status === orderFilter;
  });

  // Handle Create Product
  const handleCreateProduct = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTitle.trim()) return;

    const matchedCat = categories.find((c) => c.slug === newCategorySlug) || categories[0] || {
      name: 'Farming & Agricultural Tools',
      slug: 'farming-traditional-tools'
    };

    const priceNum = parseFloat(newPrice) || 299;
    const origPriceNum = parseFloat(newOriginalPrice) || Math.round(priceNum * 1.3);
    const discountPercent = Math.round(((origPriceNum - priceNum) / origPriceNum) * 100);

    const generatedSku = `GGH-${newCategorySlug.slice(0, 2).toUpperCase()}-${Math.floor(100 + Math.random() * 900)}`;

    addProduct({
      code: generatedSku,
      name: newTitle.trim(),
      hindiName: newHindiTitle.trim() || undefined,
      slug: newTitle.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-'),
      category: matchedCat.name,
      categorySlug: matchedCat.slug,
      price: priceNum,
      originalPrice: origPriceNum,
      discount: discountPercent > 0 ? discountPercent : 0,
      rating: 5.0,
      reviewCount: 1,
      image: newImageUrl.trim() || 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=600&q=80',
      gallery: [newImageUrl.trim()],
      stock: parseInt(newStockCount) || 50,
      seller: {
        id: currentUser?.id || 'seller-shg',
        name: workshopProfile.name,
        slug: workshopProfile.name.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
        type: 'SHG',
        location: `${workshopProfile.village}, ${workshopProfile.panchayat}`,
        district: workshopProfile.district,
        state: workshopProfile.state,
        verified: true,
        rating: 4.9,
        memberCount: workshopProfile.memberCount,
        avatar: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=150&q=80',
        phone: workshopProfile.contactPhone
      },
      description: newDescription.trim() || `${newTitle} handcrafted directly by village artisans in Hardoi using authentic rural techniques.`,
      story: workshopProfile.craftStory,
      attributes: {
        Origin: `${workshopProfile.village}, ${workshopProfile.district}, ${workshopProfile.state}`,
        Material: newIsTool ? 'Tempered High-Carbon Steel & Solid Hardwood' : 'Natural Biodegradable Fibre',
        Forging: newIsTool ? 'Ancestral Hand-Beaten Forge' : 'Hand-Spun & Woven',
        Dispatch: 'Direct from Village Workshop'
      },
      stockStatus: newStockStatus,
      isRuralWomenMade: newIsWomenMade,
      isTool: newIsTool,
      isBestSeller: true,
      tags: [matchedCat.name, 'Village Craft', 'Rural Made', 'Hardoi']
    });

    notify(`Listed "${newTitle}" successfully! Synchronized across the GaunGaliHat public marketplace.`);
    setShowAddModal(false);
    setNewTitle('');
    setNewHindiTitle('');
    setNewDescription('');
  };

  // Preset Village Image Options for Quick Listing
  const presetImages = [
    { label: 'Forged Hansiya Sickle', url: 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=600&q=80' },
    { label: 'Iron Khurpi / Fawda', url: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80' },
    { label: 'Jute Market Bag', url: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80' },
    { label: 'Village Decor & Toys', url: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80' },
    { label: 'Chikan Suit / Handloom', url: 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80' },
    { label: 'Earthen Clay Handi', url: 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?auto=format&fit=crop&w=600&q=80' }
  ];

  // Quick Stock Status Update
  const handleUpdateStock = (productId: string, stockStatus: Product['stockStatus']) => {
    updateProduct(productId, { stockStatus });
    notify(`Stock status updated to "${stockStatus}" and saved to Edge KV.`);
  };

  // Quick Price Edit Save
  const handleSavePriceEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingProduct) return;
    const price = parseFloat(editPrice) || editingProduct.price;
    const originalPrice = parseFloat(editOriginalPrice) || editingProduct.originalPrice;
    const discount = Math.round(((originalPrice - price) / originalPrice) * 100);

    updateProduct(editingProduct.id, {
      price,
      originalPrice,
      discount: discount > 0 ? discount : 0
    });

    notify(`Updated price for "${editingProduct.name}" to ₹${price}.`);
    setEditingProduct(null);
  };

  // Handle Order Status Change
  const handleUpdateOrderStatus = (orderId: string, newStatus: string) => {
    setOrders((prev) =>
      prev.map((o) => (o.id === orderId ? { ...o, status: newStatus } : o))
    );
    notify(`Order ${orderId} marked as "${newStatus}". Dispatch records updated.`);
  };

  // Handle Payout Request Submit
  const handleRequestPayout = (e: React.FormEvent) => {
    e.preventDefault();
    const amountNum = parseFloat(payoutAmount);
    if (!amountNum || amountNum <= 0) return;

    const fee = Math.round(amountNum * 0.05);
    const net = amountNum - fee;
    const newRecord = {
      id: `PAY-${Math.floor(900 + Math.random() * 100)}`,
      date: 'Today, Just now',
      amount: amountNum,
      fee: fee,
      net: net,
      utr: `SBIN${Date.now().toString().slice(-8)}`,
      status: 'Processing (NEFT / IMPS)'
    };

    setPayoutHistory([newRecord, ...payoutHistory]);
    setShowPayoutModal(false);
    notify(`Payout request of ₹${net.toLocaleString()} initiated! Crediting to ${bankDetails.accountHolder} (${bankDetails.bankName}).`);
  };

  // 1. Seller Clearance Gate: Unauthorized or Logged-out access is strictly blocked
  if (!isAuthorizedSeller) {
    return (
      <div className="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-950 text-white font-sans">
        <div className="max-w-md w-full bg-slate-900 p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl space-y-5 animate-in zoom-in-95 duration-150">
          <div className="text-center space-y-2">
            <div className="w-14 h-14 rounded-2xl bg-orange-500/20 border border-orange-500/40 text-brand-orange mx-auto flex items-center justify-center shadow-lg">
              <Store className="w-7 h-7" />
            </div>
            <h2 className="text-xl sm:text-2xl font-bold font-display text-white">
              Artisan & Seller Portal
            </h2>
            <p className="text-xs text-slate-400 leading-relaxed">
              Restricted Operations Console: Please sign in with verified artisan partner credentials to access products catalog, order dispatch manifests, and bank settlements.
            </p>
          </div>

          {currentUser?.role === 'customer' && (
            <div className="p-3 bg-amber-950/60 border border-amber-800/80 text-amber-200 text-xs rounded-xl flex items-center gap-2">
              <AlertCircle className="w-4 h-4 text-amber-400 shrink-0" />
              <span>Signed in as "{currentUser.name}" (Customer). You must log in with an Artisan / Seller account to access this workshop console.</span>
            </div>
          )}

          {gateError && (
            <div className="p-3 bg-red-950/60 border border-red-800 text-red-200 text-xs rounded-xl flex items-center gap-2">
              <AlertCircle className="w-4 h-4 text-red-400 shrink-0" />
              <span>{gateError}</span>
            </div>
          )}

          <form onSubmit={handleGateSellerLogin} className="space-y-4 text-xs">
            <div>
              <label className="block font-semibold text-slate-300 mb-1">
                Artisan Username, Registered Mobile (+91), or Email
              </label>
              <input
                type="text"
                required
                autoCapitalize="none"
                autoCorrect="off"
                spellCheck={false}
                autoComplete="username"
                value={gateIdentifier}
                onChange={(e) => setGateIdentifier(e.target.value)}
                placeholder="e.g. ramkishan_artisan, 8303578806, or email"
                className="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-white font-medium focus:border-brand-orange focus:outline-hidden"
              />
            </div>

            <div>
              <div className="flex justify-between items-center mb-1">
                <label className="font-semibold text-slate-300">Password / Access Key or 6-Digit OTP</label>
                <span className="text-[10px] text-slate-400 font-mono">PBKDF2 Encrypted</span>
              </div>
              <input
                type="password"
                required
                autoCapitalize="none"
                autoCorrect="off"
                spellCheck={false}
                autoComplete="current-password"
                value={gatePassOrOtp}
                onChange={(e) => setGatePassOrOtp(e.target.value)}
                placeholder="Enter password or verification code"
                className="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-white font-mono text-sm focus:border-brand-orange focus:outline-hidden"
              />
            </div>

            <button
              type="submit"
              disabled={gateLoading}
              className="w-full py-3 bg-brand-orange hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer text-sm"
            >
              <Lock className="w-4 h-4" />
              <span>{gateLoading ? 'Verifying Artisan Security...' : 'Authenticate & Unlock Workshop Console'}</span>
            </button>
          </form>

          <div className="pt-4 border-t border-slate-800 flex flex-col gap-2.5 text-xs">
            <div className="flex items-center justify-between">
              <span className="text-slate-400">Want to sell on GaunGaliHat?</span>
              <Link to="/seller-register" className="text-brand-orange font-bold hover:underline">
                Register Artisan Workshop →
              </Link>
            </div>
            <div className="flex items-center justify-between pt-2 border-t border-slate-800/80">
              <Link to="/" className="text-slate-400 hover:text-white flex items-center gap-1 transition-colors">
                <span>← Return to Public Marketplace</span>
              </Link>
              <span className="text-[10px] text-emerald-400 font-mono">● Cloudflare Edge Protected</span>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      {/* Toast Notification */}
      {notification && (
        <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-3 text-xs text-emerald-900 shadow-sm animate-in fade-in duration-200">
          <div className="flex items-center gap-2 font-semibold">
            <CheckCircle2 className="w-5 h-5 text-brand-green shrink-0" />
            <span>{notification}</span>
          </div>
          <button
            onClick={() => setNotification(null)}
            className="p-1 hover:bg-emerald-100 rounded-lg text-emerald-700 cursor-pointer"
          >
            <X className="w-4 h-4" />
          </button>
        </div>
      )}

      {/* Official Status Header */}
      <div className="p-4 bg-white border border-gray-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs shadow-xs">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-orange-100 border border-orange-200 flex items-center justify-center shrink-0">
            <Store className="w-5 h-5 text-brand-orange" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <span className="font-bold text-gray-900 text-sm">
                {workshopProfile.name}
              </span>
              <span className="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]">
                KYC Verified Partner
              </span>
              {currentUser && (
                <span className="px-2 py-0.5 bg-gray-100 text-gray-700 font-mono rounded text-[10px]">
                  @{currentUser.username || currentUser.name}
                </span>
              )}
            </div>
            <p className="text-gray-500 text-[11px] mt-0.5">
              {workshopProfile.village}, {workshopProfile.district}, {workshopProfile.state} • Postal Pincode: {workshopProfile.pincode}
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2 shrink-0">
          <Link
            to={`/seller/${currentUser?.username || 'artisan'}`}
            target="_blank"
            rel="noopener noreferrer"
            className="px-3 py-2 bg-white border border-gray-300 hover:border-brand-green text-gray-700 text-xs font-semibold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
            title="View public storefront profile"
          >
            <ExternalLink className="w-3.5 h-3.5 text-brand-green" />
            <span>Public Store</span>
          </Link>

          <button
            onClick={() => setShowPayoutModal(true)}
            className="px-3.5 py-2 bg-white border border-gray-300 hover:border-brand-green text-gray-800 text-xs font-semibold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Banknote className="w-4 h-4 text-brand-green" />
            <span>Request Payout</span>
          </button>

          <button
            onClick={() => setShowAddModal(true)}
            className="px-4 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Add New Product</span>
          </button>

          <button
            onClick={handleSellerLogout}
            className="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-semibold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
            title="Sign out of seller workshop"
          >
            <LogOut className="w-3.5 h-3.5 text-red-600" />
            <span>Sign Out</span>
          </button>
        </div>
      </div>

      {/* KPI Overview Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Monthly Workshop Sales</span>
            <span className="p-2 bg-emerald-50 text-brand-green rounded-lg">
              <TrendingUp className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">₹48,920</div>
          <p className="text-[11px] text-emerald-600 font-medium">↑ 22% direct rural artisan income</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Orders Dispatched</span>
            <span className="p-2 bg-blue-50 text-blue-600 rounded-lg">
              <Package className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">
            {orders.length} Consignments
          </div>
          <p className="text-[11px] text-blue-600 font-medium">India Post Speed Post Integrated</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Disbursable Balance</span>
            <span className="p-2 bg-amber-50 text-brand-orange rounded-lg">
              <Banknote className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-brand-green font-display">₹14,500</div>
          <p className="text-[11px] text-gray-500 font-medium">Ready for direct NEFT account transfer</p>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Active Listed Products</span>
            <span className="p-2 bg-purple-50 text-purple-600 rounded-lg">
              <Layers className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-purple-700 font-display">
            {products.length} Items
          </div>
          <p className="text-[11px] text-gray-500 font-medium">Across {categories.length} rural craft categories</p>
        </div>
      </div>

      {/* Main Tab Switcher */}
      <div className="flex border-b border-gray-200 bg-white p-1.5 rounded-2xl shadow-xs text-xs font-semibold gap-1 overflow-x-auto">
        <button
          onClick={() => setActiveTab('products')}
          className={`flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer ${
            activeTab === 'products'
              ? 'bg-brand-green text-white font-bold shadow-xs'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
          }`}
        >
          <Layers className="w-4 h-4" />
          <span>Category Products & Stock ({filteredProducts.length})</span>
        </button>

        <button
          onClick={() => setActiveTab('orders')}
          className={`flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer ${
            activeTab === 'orders'
              ? 'bg-brand-green text-white font-bold shadow-xs'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
          }`}
        >
          <Package className="w-4 h-4" />
          <span>Orders & Postal Dispatches ({orders.length})</span>
        </button>

        <button
          onClick={() => setActiveTab('payouts')}
          className={`flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer ${
            activeTab === 'payouts'
              ? 'bg-brand-green text-white font-bold shadow-xs'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
          }`}
        >
          <Banknote className="w-4 h-4" />
          <span>Bank Settlements & UPI</span>
        </button>

        <button
          onClick={() => setActiveTab('profile')}
          className={`flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer ${
            activeTab === 'profile'
              ? 'bg-brand-green text-white font-bold shadow-xs'
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
          }`}
        >
          <Store className="w-4 h-4" />
          <span>Workshop Profile Customizer</span>
        </button>
      </div>

      {/* TAB 1: CATEGORY-WISE PRODUCTS & LIVE INVENTORY */}
      {activeTab === 'products' && (
        <div className="space-y-4">
          
          {/* Category Filter Pills */}
          <div className="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                <Filter className="w-3.5 h-3.5 text-brand-green" />
                <span>Filter by Village Category</span>
              </span>
              <span className="text-[11px] text-gray-500 font-medium">
                Showing {filteredProducts.length} of {products.length} products
              </span>
            </div>

            <div className="flex flex-wrap gap-2">
              <button
                onClick={() => setSelectedCategory('all')}
                className={`px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer ${
                  selectedCategory === 'all'
                    ? 'bg-brand-green text-white shadow-xs'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                All Categories ({products.length})
              </button>

              {categories.map((cat) => {
                const count = products.filter(
                  (p) => p.categorySlug === cat.slug || p.category.toLowerCase().includes(cat.name.toLowerCase())
                ).length;
                return (
                  <button
                    key={cat.id}
                    onClick={() => setSelectedCategory(cat.slug)}
                    className={`px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer ${
                      selectedCategory === cat.slug
                        ? 'bg-brand-orange text-white shadow-xs'
                        : 'bg-gray-50 border border-gray-200 text-gray-700 hover:bg-gray-100'
                    }`}
                  >
                    {cat.name} ({count})
                  </button>
                );
              })}
            </div>

            {/* Search & Stock Filter Sub-bar */}
            <div className="pt-2 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
              <div className="relative w-full sm:w-72">
                <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                  type="text"
                  placeholder="Search by title, Hindi name, or SKU code..."
                  value={productSearch}
                  onChange={(e) => setProductSearch(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white"
                />
              </div>

              <div className="flex items-center gap-2 w-full sm:w-auto">
                <span className="text-xs text-gray-500 font-medium">Stock Status:</span>
                <select
                  value={stockFilter}
                  onChange={(e) => setStockFilter(e.target.value as any)}
                  className="px-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50 text-gray-700 font-medium"
                >
                  <option value="all">All Stock Status</option>
                  <option value="In Stock">In Stock</option>
                  <option value="Only Few Left">Only Few Left</option>
                  <option value="Out of Stock">Out of Stock</option>
                </select>
              </div>
            </div>
          </div>

          {/* Product Listing Table */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div className="overflow-x-auto text-xs">
              <table className="w-full text-left border-collapse">
                <thead className="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                  <tr>
                    <th className="p-3.5">Product & SKU</th>
                    <th className="p-3.5">Category</th>
                    <th className="p-3.5">Selling Price</th>
                    <th className="p-3.5">Live Stock Status</th>
                    <th className="p-3.5">Attributes & GI</th>
                    <th className="p-3.5 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {filteredProducts.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="text-center py-12 text-gray-400">
                        No products match the selected category or search filter.
                      </td>
                    </tr>
                  ) : (
                    filteredProducts.map((prod) => (
                      <tr key={prod.id} className="hover:bg-gray-50/60 transition-colors">
                        
                        {/* Title & SKU */}
                        <td className="p-3.5">
                          <div className="flex items-center gap-3">
                            <img
                              src={prod.image}
                              alt={prod.name}
                              className="w-12 h-12 rounded-xl object-cover border border-gray-200 shrink-0"
                            />
                            <div>
                              <div className="flex items-center gap-1.5">
                                <span className="font-bold text-gray-900 line-clamp-1">{prod.name}</span>
                                {prod.hindiName && (
                                  <span className="font-hindi text-[11px] text-amber-800 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200 font-semibold">
                                    {prod.hindiName}
                                  </span>
                                )}
                              </div>
                              <div className="flex items-center gap-2 mt-0.5">
                                <span className="font-mono text-[10px] text-gray-500 font-semibold">
                                  {prod.code || `GGH-${prod.id.slice(-4)}`}
                                </span>
                                <span className="text-[10px] text-gray-400">•</span>
                                <span className="text-[10px] text-gray-500">
                                  {prod.stock ? `${prod.stock} units` : '50 units'}
                                </span>
                              </div>
                            </div>
                          </div>
                        </td>

                        {/* Category */}
                        <td className="p-3.5 whitespace-nowrap">
                          <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-brand-green border border-emerald-200">
                            {prod.category}
                          </span>
                        </td>

                        {/* Price & MRP */}
                        <td className="p-3.5 whitespace-nowrap">
                          <div className="flex items-center gap-1.5">
                            <span className="font-bold text-gray-900 text-sm">₹{prod.price}</span>
                            {prod.originalPrice > prod.price && (
                              <span className="text-gray-400 line-through text-[11px]">
                                ₹{prod.originalPrice}
                              </span>
                            )}
                          </div>
                          {prod.discount > 0 && (
                            <span className="text-[10px] text-emerald-600 font-bold">
                              {prod.discount}% OFF
                            </span>
                          )}
                        </td>

                        {/* Stock Status Selector */}
                        <td className="p-3.5 whitespace-nowrap">
                          <select
                            value={prod.stockStatus}
                            onChange={(e) => handleUpdateStock(prod.id, e.target.value as any)}
                            className={`px-2 py-1 text-xs font-bold rounded-lg border cursor-pointer ${
                              prod.stockStatus === 'In Stock'
                                ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                                : prod.stockStatus === 'Only Few Left'
                                ? 'bg-amber-50 text-amber-800 border-amber-200'
                                : 'bg-red-50 text-red-800 border-red-200'
                            }`}
                          >
                            <option value="In Stock">● In Stock</option>
                            <option value="Only Few Left">▲ Only Few Left</option>
                            <option value="Out of Stock">✕ Out of Stock</option>
                          </select>
                        </td>

                        {/* Attributes Badges */}
                        <td className="p-3.5 whitespace-nowrap">
                          <div className="flex flex-wrap gap-1">
                            {prod.isRuralWomenMade && (
                              <span className="px-1.5 py-0.2 rounded text-[9px] font-bold bg-pink-50 text-pink-700 border border-pink-200">
                                Rural Women
                              </span>
                            )}
                            {prod.isTool && (
                              <span className="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-800">
                                Desi Forge
                              </span>
                            )}
                          </div>
                        </td>

                        {/* Actions */}
                        <td className="p-3.5 text-right whitespace-nowrap">
                          <div className="flex items-center justify-end gap-1.5">
                            <button
                              onClick={() => {
                                setEditingProduct(prod);
                                setEditPrice(prod.price.toString());
                                setEditOriginalPrice(prod.originalPrice.toString());
                              }}
                              className="p-1.5 text-gray-500 hover:text-brand-green hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
                              title="Quick Edit Price"
                            >
                              <Edit3 className="w-3.5 h-3.5" />
                            </button>
                            <button
                              onClick={() => {
                                if (confirm(`Remove "${prod.name}" from marketplace?`)) {
                                  deleteProduct(prod.id);
                                  notify(`Deleted "${prod.name}" from catalog.`);
                                }
                              }}
                              className="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                              title="Delete Product"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          </div>
                        </td>

                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* TAB 2: ORDERS & INDIA POST DISPATCH */}
      {activeTab === 'orders' && (
        <div className="space-y-4">
          <div className="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div className="flex items-center gap-2">
              <span className="font-bold text-gray-800 uppercase tracking-wider">Order Status:</span>
              <div className="flex gap-1.5">
                {(['all', 'Ready for Dispatch', 'In Transit', 'Delivered'] as const).map((st) => (
                  <button
                    key={st}
                    onClick={() => setOrderFilter(st)}
                    className={`px-3 py-1.5 rounded-xl font-semibold transition-all cursor-pointer ${
                      orderFilter === st
                        ? 'bg-brand-green text-white shadow-xs'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`}
                  >
                    {st === 'all' ? 'All Orders' : st}
                  </button>
                ))}
              </div>
            </div>

            <span className="text-gray-500 font-medium">
              India Post Speed Post Direct Rural Pickup Active
            </span>
          </div>

          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div className="overflow-x-auto text-xs">
              <table className="w-full text-left">
                <thead className="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                  <tr>
                    <th className="p-3.5">Order ID</th>
                    <th className="p-3.5">Item & SKU</th>
                    <th className="p-3.5">Customer & Delivery Destination</th>
                    <th className="p-3.5">Amount & Payment</th>
                    <th className="p-3.5">Dispatch Status</th>
                    <th className="p-3.5 text-right">Postal Manifest</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {filteredOrders.map((ord) => (
                    <tr key={ord.id} className="hover:bg-gray-50/60 transition-colors">
                      <td className="p-3.5 font-mono font-bold text-gray-900">{ord.id}</td>
                      <td className="p-3.5">
                        <p className="font-bold text-gray-900 line-clamp-1">{ord.item}</p>
                        <p className="text-[10px] font-mono text-gray-500">{ord.sku} • {ord.date}</p>
                      </td>
                      <td className="p-3.5">
                        <p className="font-bold text-gray-800">{ord.customer}</p>
                        <p className="text-[11px] text-gray-500 line-clamp-1">{ord.address}</p>
                      </td>
                      <td className="p-3.5 whitespace-nowrap">
                        <p className="font-bold text-gray-900 text-sm">₹{ord.amount}</p>
                        <span className={`text-[10px] font-semibold ${ord.payment.includes('Prepaid') ? 'text-emerald-700' : 'text-amber-800'}`}>
                          {ord.payment}
                        </span>
                      </td>
                      <td className="p-3.5 whitespace-nowrap">
                        <select
                          value={ord.status}
                          onChange={(e) => handleUpdateOrderStatus(ord.id, e.target.value)}
                          className="px-2 py-1 text-xs font-semibold rounded-lg border border-gray-200 bg-white"
                        >
                          <option value="Ready for Dispatch">Ready for Dispatch</option>
                          <option value="In Transit">In Transit (India Post)</option>
                          <option value="Delivered">Delivered</option>
                        </select>
                      </td>
                      <td className="p-3.5 text-right whitespace-nowrap">
                        <button
                          onClick={() => setSelectedOrderForLabel(ord)}
                          className="px-3 py-1.5 bg-brand-green/10 hover:bg-brand-green text-brand-green hover:text-white rounded-lg text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-1.5"
                        >
                          <Printer className="w-3.5 h-3.5" />
                          <span>Print Label</span>
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* TAB 3: BANK SETTLEMENTS & UPI */}
      {activeTab === 'payouts' && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
              <span className="text-xs text-gray-500 font-medium">Total Lifetime Earnings</span>
              <div className="text-2xl font-extrabold text-gray-900 font-display">₹91,420</div>
              <p className="text-[11px] text-gray-500">100% direct rural bank transfers</p>
            </div>

            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
              <span className="text-xs text-gray-500 font-medium">Platform Facilitation Fee</span>
              <div className="text-2xl font-extrabold text-gray-900 font-display">5.0%</div>
              <p className="text-[11px] text-emerald-600 font-medium">Subsidized promotional rate</p>
            </div>

            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
              <span className="text-xs text-gray-500 font-medium">Available for Instant Payout</span>
              <div className="text-2xl font-extrabold text-brand-green font-display">₹14,500</div>
              <button
                onClick={() => setShowPayoutModal(true)}
                className="mt-2 w-full py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer"
              >
                Request NEFT / UPI Payout Now
              </button>
            </div>

          </div>

          {/* Bank Account Configurator */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div className="flex items-center justify-between border-b border-gray-100 pb-3">
              <div>
                <h3 className="font-bold text-sm text-gray-900 font-display">
                  Artisan Bank & UPI Settlement Credentials
                </h3>
                <p className="text-xs text-gray-500">
                  Government-linked bank account for automated rural direct benefit transfer (DBT).
                </p>
              </div>
              <span className="px-2.5 py-1 bg-emerald-50 text-brand-green border border-emerald-200 rounded-lg text-xs font-bold">
                ✓ Bank Verified
              </span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Beneficiary Account Name</label>
                <input
                  type="text"
                  value={bankDetails.accountHolder}
                  onChange={(e) => setBankDetails({ ...bankDetails, accountHolder: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-medium"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Bank Name</label>
                <input
                  type="text"
                  value={bankDetails.bankName}
                  onChange={(e) => setBankDetails({ ...bankDetails, bankName: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Account Number</label>
                <input
                  type="text"
                  value={bankDetails.accountNumber}
                  onChange={(e) => setBankDetails({ ...bankDetails, accountNumber: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">IFSC Code</label>
                <input
                  type="text"
                  value={bankDetails.ifscCode}
                  onChange={(e) => setBankDetails({ ...bankDetails, ifscCode: e.target.value.toUpperCase() })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Branch Name</label>
                <input
                  type="text"
                  value={bankDetails.branch}
                  onChange={(e) => setBankDetails({ ...bankDetails, branch: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">UPI VPA ID</label>
                <input
                  type="text"
                  value={bankDetails.upiId}
                  onChange={(e) => setBankDetails({ ...bankDetails, upiId: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono"
                />
              </div>
            </div>

            <div className="flex justify-end pt-2">
              <button
                onClick={() => notify('Bank & UPI settlement details saved successfully!')}
                className="px-4 py-2 bg-brand-green text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer"
              >
                Save Bank Details
              </button>
            </div>
          </div>

          {/* Settlement Records */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div className="p-4 border-b border-gray-100 flex items-center justify-between">
              <h4 className="font-bold text-xs text-gray-900 uppercase tracking-wider">
                Settlement & Payout Disbursement History
              </h4>
              <span className="text-xs text-emerald-700 font-semibold">100% On-Time Record</span>
            </div>

            <div className="overflow-x-auto text-xs">
              <table className="w-full text-left">
                <thead className="bg-gray-50 text-gray-500 uppercase font-bold text-[10px]">
                  <tr>
                    <th className="p-3">Reference ID</th>
                    <th className="p-3">Disbursement Date</th>
                    <th className="p-3">Gross Amount</th>
                    <th className="p-3">Platform Fee (5%)</th>
                    <th className="p-3">Net Transferred</th>
                    <th className="p-3">Bank UTR Number</th>
                    <th className="p-3">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {payoutHistory.map((p) => (
                    <tr key={p.id} className="hover:bg-gray-50/60">
                      <td className="p-3 font-mono font-bold">{p.id}</td>
                      <td className="p-3 text-gray-600">{p.date}</td>
                      <td className="p-3 font-semibold">₹{p.amount.toLocaleString()}</td>
                      <td className="p-3 text-red-600 font-medium">-₹{p.fee.toLocaleString()}</td>
                      <td className="p-3 font-bold text-emerald-800 text-sm">₹{p.net.toLocaleString()}</td>
                      <td className="p-3 font-mono text-[11px] text-gray-500">{p.utr}</td>
                      <td className="p-3">
                        <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                          {p.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* TAB 4: WORKSHOP PROFILE & SECURITY CUSTOMIZER */}
      {activeTab === 'profile' && (
        <div className="space-y-6">
          {/* 1. Artisan Workshop Profile */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-5">
            <div className="border-b border-gray-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <h3 className="font-bold text-base text-gray-900 font-display flex items-center gap-2">
                  <Store className="w-5 h-5 text-brand-green" />
                  <span>Artisan Workshop & Storefront Profile</span>
                </h3>
                <p className="text-xs text-gray-500">
                  Toolsvilla-standard artisan workshop story, village cluster location, and contact information.
                </p>
              </div>
              <button
                type="button"
                onClick={handleSaveWorkshopProfile}
                className="px-4 py-2.5 bg-brand-green hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer flex items-center justify-center gap-1.5 transition-colors"
              >
                <Save className="w-3.5 h-3.5" />
                <span>Save Profile Changes</span>
              </button>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Workshop / SHG Name *</label>
                <input
                  type="text"
                  value={workshopProfile.name}
                  onChange={(e) => setWorkshopProfile({ ...workshopProfile, name: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-bold text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Artisan Tagline</label>
                <input
                  type="text"
                  value={workshopProfile.tagline}
                  onChange={(e) => setWorkshopProfile({ ...workshopProfile, tagline: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Village / Town *</label>
                <input
                  type="text"
                  value={workshopProfile.village}
                  onChange={(e) => setWorkshopProfile({ ...workshopProfile, village: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Gram Panchayat / Industrial Area</label>
                <input
                  type="text"
                  value={workshopProfile.panchayat}
                  onChange={(e) => setWorkshopProfile({ ...workshopProfile, panchayat: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">District</label>
                <input
                  type="text"
                  value={workshopProfile.district}
                  onChange={(e) => setWorkshopProfile({ ...workshopProfile, district: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Postal Pincode</label>
                <input
                  type="text"
                  value={workshopProfile.pincode}
                  onChange={(e) => setWorkshopProfile({ ...workshopProfile, pincode: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Direct Artisan Contact Phone</label>
                <input
                  type="tel"
                  value={workshopProfile.contactPhone}
                  onChange={(e) => setWorkshopProfile({ ...workshopProfile, contactPhone: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">WhatsApp Customer Dispatch Inquiry</label>
                <input
                  type="tel"
                  value={workshopProfile.whatsapp}
                  onChange={(e) => setWorkshopProfile({ ...workshopProfile, whatsapp: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
                />
              </div>
            </div>

            <div className="text-xs">
              <label className="block font-semibold text-gray-700 mb-1">
                Generational Craft Story & Ancestral Heritage
              </label>
              <textarea
                rows={4}
                value={workshopProfile.craftStory}
                onChange={(e) => setWorkshopProfile({ ...workshopProfile, craftStory: e.target.value })}
                className="w-full p-3 border border-gray-300 rounded-xl leading-relaxed text-gray-900 focus:ring-2 focus:ring-brand-green/20 focus:border-brand-green"
              ></textarea>
            </div>
          </div>

          {/* 2. Direct Bank & Settlement Payouts Details */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div className="border-b border-gray-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <h3 className="font-bold text-base text-gray-900 font-display flex items-center gap-2">
                  <Banknote className="w-5 h-5 text-emerald-600" />
                  <span>Settlement Bank Account & UPI ID</span>
                </h3>
                <p className="text-xs text-gray-500">
                  Direct artisan payout accounts for verified NEFT / RTGS / IMPS and UPI settlements.
                </p>
              </div>
              <button
                type="button"
                onClick={handleSaveWorkshopProfile}
                className="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-xl cursor-pointer flex items-center justify-center gap-1.5 transition-colors"
              >
                <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
                <span>Save Bank Details</span>
              </button>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Beneficiary Name (Account Holder) *</label>
                <input
                  type="text"
                  value={bankDetails.accountHolder}
                  onChange={(e) => setBankDetails({ ...bankDetails, accountHolder: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-medium text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Bank Name *</label>
                <input
                  type="text"
                  value={bankDetails.bankName}
                  onChange={(e) => setBankDetails({ ...bankDetails, bankName: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-medium text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Account Number *</label>
                <input
                  type="text"
                  value={bankDetails.accountNumber}
                  onChange={(e) => setBankDetails({ ...bankDetails, accountNumber: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Bank IFSC Code *</label>
                <input
                  type="text"
                  value={bankDetails.ifscCode}
                  onChange={(e) => setBankDetails({ ...bankDetails, ifscCode: e.target.value.toUpperCase() })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono uppercase text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Branch Name</label>
                <input
                  type="text"
                  value={bankDetails.branch}
                  onChange={(e) => setBankDetails({ ...bankDetails, branch: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-medium text-gray-900"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">UPI ID (VPA for Instant Disbursal)</label>
                <input
                  type="text"
                  value={bankDetails.upiId}
                  onChange={(e) => setBankDetails({ ...bankDetails, upiId: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-gray-900"
                  placeholder="artisan@upi"
                />
              </div>
            </div>
          </div>

          {/* 3. Password & Security Key Credentials (PBKDF2 Encrypted) */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div className="border-b border-gray-100 pb-3 flex items-center justify-between">
              <div>
                <h3 className="font-bold text-base text-gray-900 font-display flex items-center gap-2">
                  <ShieldCheck className="w-5 h-5 text-indigo-600" />
                  <span>Seller Security & Password Encryption</span>
                </h3>
                <p className="text-xs text-gray-500">
                  Change your login password. All credentials are protected with PBKDF2-HMAC-SHA256 and unique 16-byte cryptographic salt.
                </p>
              </div>
              <span className="hidden sm:inline-flex items-center gap-1 text-[11px] font-mono px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full font-bold border border-indigo-200">
                <Lock className="w-3 h-3" />
                PBKDF2 Hard Encrypted
              </span>
            </div>

            {passError && (
              <div className="p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs flex items-center gap-2">
                <AlertCircle className="w-4 h-4 shrink-0 text-red-500" />
                <span>{passError}</span>
              </div>
            )}

            {passSuccess && (
              <div className="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 shrink-0 text-emerald-600" />
                <span>{passSuccess}</span>
              </div>
            )}

            <form onSubmit={handlePasswordChange} className="space-y-4 max-w-xl">
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">
                  Current Password {currentUser?.passwordHash ? '*' : '(Leave empty if not set)'}
                </label>
                <div className="relative">
                  <input
                    type={showPass ? 'text' : 'password'}
                    value={currentPassInput}
                    onChange={(e) => setCurrentPassInput(e.target.value)}
                    placeholder="Enter existing password"
                    autoCapitalize="none"
                    autoCorrect="off"
                    className="w-full p-2.5 pr-10 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-200 focus:border-indigo-600 font-mono"
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

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">
                    New Secure Password *
                  </label>
                  <input
                    type={showPass ? 'text' : 'password'}
                    value={newPassInput}
                    onChange={(e) => setNewPassInput(e.target.value)}
                    placeholder="Minimum 6 characters"
                    autoCapitalize="none"
                    autoCorrect="off"
                    className="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-200 focus:border-indigo-600 font-mono"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-gray-700 mb-1">
                    Confirm New Password *
                  </label>
                  <input
                    type={showPass ? 'text' : 'password'}
                    value={confirmPassInput}
                    onChange={(e) => setConfirmPassInput(e.target.value)}
                    placeholder="Re-enter new password"
                    autoCapitalize="none"
                    autoCorrect="off"
                    className="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-200 focus:border-indigo-600 font-mono"
                  />
                </div>
              </div>

              <div className="pt-2 flex items-center gap-3">
                <button
                  type="submit"
                  disabled={isChangingPass}
                  className="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer flex items-center gap-2 transition-colors"
                >
                  <KeyRound className="w-4 h-4" />
                  <span>{isChangingPass ? 'Encrypting & Updating...' : 'Update & Encrypt Password'}</span>
                </button>
                <span className="text-[11px] text-gray-500">
                  Protected with 100,000 PBKDF2 iterations
                </span>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 1: ADD NEW VILLAGE PRODUCT */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-white rounded-3xl p-6 max-w-xl w-full space-y-4 shadow-2xl animate-in zoom-in-95 duration-150 my-6">
            <div className="flex items-center justify-between border-b border-gray-100 pb-3">
              <div>
                <h4 className="font-bold text-base text-gray-900 font-display">
                  List New Village Product in Catalog
                </h4>
                <p className="text-[11px] text-gray-500">
                  Direct listing to GaunGaliHat marketplace with instant KV synchronization.
                </p>
              </div>
              <button
                onClick={() => setShowAddModal(false)}
                className="p-1 text-gray-400 hover:text-gray-700 rounded-lg cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateProduct} className="space-y-3.5 text-xs">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Product Title (English) *</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Forged Carbon Steel Khurpi"
                    value={newTitle}
                    onChange={(e) => setNewTitle(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Product Title (Hindi)</label>
                  <input
                    type="text"
                    placeholder="e.g. देसी लोहे की खुरपी"
                    value={newHindiTitle}
                    onChange={(e) => setNewHindiTitle(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-hindi"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Marketplace Category *</label>
                  <select
                    value={newCategorySlug}
                    onChange={(e) => setNewCategorySlug(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl bg-white font-medium"
                  >
                    {categories.map((c) => (
                      <option key={c.id} value={c.slug}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Stock Status *</label>
                  <select
                    value={newStockStatus}
                    onChange={(e) => setNewStockStatus(e.target.value as any)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl bg-white font-medium"
                  >
                    <option value="In Stock">In Stock</option>
                    <option value="Only Few Left">Only Few Left</option>
                    <option value="Out of Stock">Out of Stock</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Selling Price (₹) *</label>
                  <input
                    type="number"
                    required
                    value={newPrice}
                    onChange={(e) => setNewPrice(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-bold"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">MRP Price (₹)</label>
                  <input
                    type="number"
                    value={newOriginalPrice}
                    onChange={(e) => setNewOriginalPrice(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl text-gray-500"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Stock Units</label>
                  <input
                    type="number"
                    value={newStockCount}
                    onChange={(e) => setNewStockCount(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-mono"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Select Preset Craft Photo or Custom URL</label>
                <div className="grid grid-cols-3 sm:grid-cols-6 gap-2 mb-2">
                  {presetImages.map((img, idx) => (
                    <button
                      key={idx}
                      type="button"
                      onClick={() => setNewImageUrl(img.url)}
                      className={`p-1 rounded-xl border text-center transition-all cursor-pointer ${
                        newImageUrl === img.url
                          ? 'border-brand-green ring-2 ring-brand-green bg-emerald-50'
                          : 'border-gray-200 hover:border-gray-400'
                      }`}
                    >
                      <img src={img.url} alt={img.label} className="w-full h-10 object-cover rounded-lg" />
                      <span className="text-[9px] font-semibold text-gray-700 line-clamp-1 mt-0.5">{img.label}</span>
                    </button>
                  ))}
                </div>
                <input
                  type="url"
                  placeholder="Or paste external image URL..."
                  value={newImageUrl}
                  onChange={(e) => setNewImageUrl(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Description & Craft Specifications</label>
                <textarea
                  rows={2}
                  placeholder="Forged carbon steel, heat-treated durability, hardwood handle grip..."
                  value={newDescription}
                  onChange={(e) => setNewDescription(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                ></textarea>
              </div>

              <div className="flex items-center gap-4 pt-1">
                <label className="flex items-center gap-2 cursor-pointer font-semibold text-gray-700">
                  <input
                    type="checkbox"
                    checked={newIsWomenMade}
                    onChange={(e) => setNewIsWomenMade(e.target.checked)}
                    className="rounded text-brand-green focus:ring-brand-green"
                  />
                  <span>Handcrafted by Rural Women SHG</span>
                </label>

                <label className="flex items-center gap-2 cursor-pointer font-semibold text-gray-700">
                  <input
                    type="checkbox"
                    checked={newIsTool}
                    onChange={(e) => setNewIsTool(e.target.checked)}
                    className="rounded text-brand-green focus:ring-brand-green"
                  />
                  <span>Farming / Village Tool</span>
                </label>
              </div>

              <div className="flex gap-2 pt-3 border-t border-gray-100">
                <button
                  type="submit"
                  className="flex-1 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                >
                  Publish Product to Storefront
                </button>
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 cursor-pointer"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 2: QUICK EDIT PRICE */}
      {editingProduct && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-sm w-full space-y-4 shadow-2xl animate-in zoom-in-95 duration-150">
            <h4 className="font-bold text-base text-gray-900 font-display">
              Update Price: {editingProduct.name}
            </h4>
            <form onSubmit={handleSavePriceEdit} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Selling Price (₹)</label>
                <input
                  type="number"
                  required
                  value={editPrice}
                  onChange={(e) => setEditPrice(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-bold text-sm"
                />
              </div>
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Original MRP Price (₹)</label>
                <input
                  type="number"
                  value={editOriginalPrice}
                  onChange={(e) => setEditOriginalPrice(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>
              <div className="flex gap-2 pt-2">
                <button
                  type="submit"
                  className="flex-1 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-xs cursor-pointer"
                >
                  Save New Price
                </button>
                <button
                  type="button"
                  onClick={() => setEditingProduct(null)}
                  className="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 cursor-pointer"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 3: INDIA POST SPEED POST DISPATCH LABEL */}
      {selectedOrderForLabel && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-lg w-full space-y-4 shadow-2xl border border-gray-200 animate-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between border-b border-gray-200 pb-3">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-lg bg-red-600 text-white font-black flex items-center justify-center text-xs">
                  IP
                </div>
                <div>
                  <h4 className="font-bold text-sm text-gray-900 font-display">
                    India Post • Speed Post Parcel Manifest
                  </h4>
                  <span className="font-mono text-[10px] text-gray-500">
                    Consignment No: {selectedOrderForLabel.consignmentNo}
                  </span>
                </div>
              </div>
              <button
                onClick={() => setSelectedOrderForLabel(null)}
                className="p-1 text-gray-400 hover:text-gray-700 cursor-pointer"
              >
                ✕
              </button>
            </div>

            {/* Printable Parcel Label Box */}
            <div className="border-2 border-dashed border-gray-400 p-4 rounded-2xl bg-amber-50/20 text-xs space-y-3 font-sans">
              <div className="flex items-center justify-between border-b border-gray-300 pb-2">
                <div>
                  <span className="font-black text-red-600 text-base tracking-wide">भारतीय डाक • INDIA POST</span>
                  <span className="block text-[10px] font-bold text-gray-700">SPEED POST DOMESTIC PARCEL</span>
                </div>
                <div className="text-right">
                  <span className="font-mono font-bold text-xs bg-black text-white px-2 py-0.5 rounded">
                    {selectedOrderForLabel.payment.includes('Prepaid') ? 'PREPAID' : 'COD: ₹' + selectedOrderForLabel.amount}
                  </span>
                  <span className="block text-[10px] font-mono text-gray-500 mt-0.5">WT: 1.25 KG</span>
                </div>
              </div>

              {/* Barcode Mock */}
              <div className="text-center py-2 bg-white rounded-lg border border-gray-300">
                <div className="font-mono font-bold tracking-[6px] text-lg select-all">
                  ||| | | |||| || | || |||| |
                </div>
                <span className="font-mono text-xs text-gray-700 font-bold">
                  *{selectedOrderForLabel.consignmentNo}*
                </span>
              </div>

              <div className="grid grid-cols-2 gap-3 pt-1 border-t border-gray-300 text-[11px]">
                <div className="space-y-0.5 border-r border-gray-200 pr-2">
                  <span className="font-bold text-gray-500 uppercase text-[9px] block">CONSIGNOR (SENDER):</span>
                  <p className="font-bold text-gray-900">{workshopProfile.name}</p>
                  <p className="text-gray-600">{workshopProfile.village}, {workshopProfile.district}</p>
                  <p className="text-gray-600">Uttar Pradesh - {workshopProfile.pincode}</p>
                  <p className="font-mono text-gray-500">{workshopProfile.contactPhone}</p>
                </div>

                <div className="space-y-0.5 pl-1">
                  <span className="font-bold text-gray-500 uppercase text-[9px] block">CONSIGNEE (DELIVER TO):</span>
                  <p className="font-bold text-gray-900">{selectedOrderForLabel.customer}</p>
                  <p className="text-gray-700 font-medium leading-tight">{selectedOrderForLabel.address}</p>
                </div>
              </div>

              <div className="p-2 bg-gray-100 rounded-lg text-[10px] text-gray-600 flex justify-between">
                <span>Article: {selectedOrderForLabel.item}</span>
                <span className="font-mono font-bold">Order: {selectedOrderForLabel.id}</span>
              </div>
            </div>

            <div className="flex gap-2 pt-2">
              <button
                onClick={() => {
                  window.print();
                  notify(`Printed dispatch manifest for order ${selectedOrderForLabel.id}`);
                }}
                className="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5"
              >
                <Printer className="w-4 h-4" />
                <span>Send to Thermal Label Printer</span>
              </button>
              <button
                onClick={() => setSelectedOrderForLabel(null)}
                className="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 cursor-pointer"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 4: REQUEST PAYOUT */}
      {showPayoutModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-sm w-full space-y-4 shadow-2xl animate-in zoom-in-95 duration-150">
            <h4 className="font-bold text-base text-gray-900 font-display">
              Request Bank Settlement
            </h4>
            <p className="text-xs text-gray-500">
              Funds are disbursed via automated NEFT / IMPS within 24 hours.
            </p>

            <form onSubmit={handleRequestPayout} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Requested Amount (₹)</label>
                <input
                  type="number"
                  required
                  value={payoutAmount}
                  onChange={(e) => setPayoutAmount(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-bold text-sm"
                />
              </div>

              <div className="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-1 text-[11px]">
                <div className="flex justify-between">
                  <span className="text-gray-500">Gross Settlement:</span>
                  <span className="font-bold">₹{parseFloat(payoutAmount || '0').toLocaleString()}</span>
                </div>
                <div className="flex justify-between text-red-600">
                  <span>Platform Fee (5%):</span>
                  <span>-₹{Math.round((parseFloat(payoutAmount || '0') * 0.05)).toLocaleString()}</span>
                </div>
                <div className="flex justify-between border-t border-gray-200 pt-1 font-bold text-brand-green text-xs">
                  <span>Net Credited to Bank:</span>
                  <span>₹{Math.round((parseFloat(payoutAmount || '0') * 0.95)).toLocaleString()}</span>
                </div>
              </div>

              <div className="text-[11px] text-gray-500">
                Destination: <span className="font-bold text-gray-800">{bankDetails.accountHolder}</span> ({bankDetails.bankName})
              </div>

              <div className="flex gap-2 pt-2">
                <button
                  type="submit"
                  className="flex-1 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-xs cursor-pointer"
                >
                  Confirm Payout Request
                </button>
                <button
                  type="button"
                  onClick={() => setShowPayoutModal(false)}
                  className="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 cursor-pointer"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
};
