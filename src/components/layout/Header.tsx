import React, { useState, useRef, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  Search,
  ShoppingCart,
  Heart,
  User,
  MapPin,
  Menu,
  X,
  ChevronDown,
  Store,
  ShieldCheck,
  Package,
  LogOut,
  ExternalLink,
  Sliders,
  Settings
} from 'lucide-react';
import { useSiteConfig } from '../../context/SiteConfigContext';
import { useCartWishlist } from '../../context/CartWishlistContext';
import { useDemoModal } from '../../context/DemoModalContext';
import { useAuth } from '../../context/AuthContext';
import { mockProducts } from '../../data/mockProducts';

export const Header: React.FC = () => {
  const { headerConfig } = useSiteConfig();
  const { cartCount, wishlistCount, cartTotal } = useCartWishlist();
  const { showDemoNotice } = useDemoModal();
  const { currentUser, currentRole, openAuthModal, logout, switchRole } = useAuth();
  const navigate = useNavigate();

  const [searchQuery, setSearchQuery] = useState('');
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [accountMenuOpen, setAccountMenuOpen] = useState(false);
  const [locationModalOpen, setLocationModalOpen] = useState(false);
  const [pincode, setPincode] = useState('400001');
  const [currentLocation, setCurrentLocation] = useState(headerConfig.defaultLocation);

  const searchRef = useRef<HTMLDivElement>(null);
  const accountRef = useRef<HTMLDivElement>(null);

  // Filter search suggestions
  const suggestions = searchQuery.trim()
    ? mockProducts.filter(p =>
        p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.category.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.tags.some(t => t.toLowerCase().includes(searchQuery.toLowerCase()))
      ).slice(0, 5)
    : [];

  // Close menus on outside click
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
        setShowSuggestions(false);
      }
      if (accountRef.current && !accountRef.current.contains(event.target as Node)) {
        setAccountMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      setShowSuggestions(false);
      navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
    }
  };

  const handlePincodeSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (pincode.trim().length === 6) {
      setCurrentLocation(`Deliver to: ${pincode.trim()}`);
      setLocationModalOpen(false);
    } else {
      showDemoNotice('Pincode Validation', 'Please enter a valid 6-digit Indian postal code (e.g. 400001, 110001, 226001).');
    }
  };

  return (
    <header className="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm transition-all w-full max-w-full">
      {/* Main Header Container */}
      <div className="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 w-full min-w-0">
        <div className="flex items-center justify-between h-16 sm:h-20 gap-2 sm:gap-4 w-full min-w-0">
          
          {/* Mobile Menu Button */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="lg:hidden p-2 text-gray-700 hover:text-brand-green hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green"
            aria-label="Toggle Navigation Menu"
          >
            {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>

          {/* Brand Logo Only (No text next to logo) */}
          <Link to="/" className="flex items-center shrink-0 group py-1" title="GanvGaliHut • Home">
            <div className="relative h-12 sm:h-14 w-auto flex items-center">
              <img
                src={headerConfig.logoUrl || '/logo.png'}
                alt="GanvGaliHut Logo"
                className="h-10 sm:h-12 w-auto max-w-[200px] object-contain transition-transform group-hover:scale-105"
                onError={(e) => {
                  (e.target as HTMLElement).style.display = 'none';
                }}
              />
            </div>
          </Link>

          {/* Delivery Location Selector */}
          {headerConfig.showLocationSelector && (
            <div className="hidden md:flex items-center">
              <button
                onClick={() => setLocationModalOpen(true)}
                className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-brand-cream hover:border-brand-green hover:bg-brand-green-50/50 transition-colors text-left group"
              >
                <MapPin className="w-4 h-4 text-brand-orange shrink-0 group-hover:animate-bounce" />
                <div className="text-xs leading-tight">
                  <span className="block text-gray-500 text-[10px]">Express Village Dispatch</span>
                  <span className="font-semibold text-gray-800 truncate max-w-[140px] block">
                    {currentLocation}
                  </span>
                </div>
                <ChevronDown className="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-700" />
              </button>
            </div>
          )}

          {/* Search Bar with Live Autocomplete */}
          {headerConfig.showSearch && (
            <div ref={searchRef} className="flex-1 max-w-xl relative hidden sm:block">
              <form onSubmit={handleSearchSubmit} className="relative">
                <input
                  type="text"
                  placeholder="Search hand-forged tools, sickles, khurpi, jute bags, wooden toys..."
                  value={searchQuery}
                  onChange={(e) => {
                    setQueryAndShow(e.target.value);
                  }}
                  onFocus={() => setShowSuggestions(true)}
                  className="w-full pl-10 pr-24 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-brand-green transition-all"
                />
                <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                
                <button
                  type="submit"
                  className="absolute right-1.5 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-full shadow-sm transition-all"
                >
                  Search
                </button>
              </form>

              {/* Suggestions Dropdown */}
              {showSuggestions && searchQuery.trim() && (
                <div className="absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50">
                  <div className="px-3 py-2 bg-brand-cream/60 border-b border-gray-100 text-xs text-gray-600 font-medium flex justify-between items-center">
                    <span>Products matching "{searchQuery}"</span>
                    <span className="text-[10px] text-brand-green font-semibold">Demo Catalog</span>
                  </div>

                  {suggestions.length > 0 ? (
                    <ul className="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                      {suggestions.map((product) => (
                        <li key={product.id}>
                          <Link
                            to={`/product/${product.slug}`}
                            onClick={() => setShowSuggestions(false)}
                            className="flex items-center gap-3 p-3 hover:bg-emerald-50/50 transition-colors"
                          >
                            <img
                              src={product.image}
                              alt={product.name}
                              className="w-10 h-10 rounded-md object-cover border border-gray-100 shrink-0"
                            />
                            <div className="flex-1 min-w-0">
                              <p className="text-sm font-medium text-gray-800 truncate">
                                {product.name}
                              </p>
                              <div className="flex items-center gap-2 text-xs text-gray-500">
                                <span>{product.category}</span>
                                <span>•</span>
                                <span className="font-semibold text-brand-green">₹{product.price}</span>
                              </div>
                            </div>
                          </Link>
                        </li>
                      ))}
                    </ul>
                  ) : (
                    <div className="p-4 text-center text-sm text-gray-500">
                      No matching rural products found. Try searching for "sickle", "khurpi", "jute", or "hammer".
                    </div>
                  )}

                  <div className="p-2.5 bg-gray-50 border-t border-gray-100 text-center">
                    <button
                      onClick={handleSearchSubmit}
                      className="text-xs font-semibold text-brand-green hover:text-brand-green-dark flex items-center justify-center gap-1 mx-auto"
                    >
                      <span>View all matching results</span>
                      <ExternalLink className="w-3 h-3" />
                    </button>
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Action Icons: Nearby Artisans, Become Seller, Account, Wishlist, Cart */}
          {headerConfig.showIcons && (
            <div className="flex items-center gap-2 sm:gap-3 shrink-0">
              
              {/* Nearby Artisans Map CTA */}
              <Link
                to="/nearby-artisans"
                className="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-600/30 text-brand-green bg-emerald-50 hover:bg-brand-green hover:text-white transition-all text-xs font-semibold"
                title="Locate rural artisans & foundries"
              >
                <MapPin className="w-3.5 h-3.5 text-brand-orange" />
                <span>Nearby Artisans</span>
              </Link>

              {/* Become a Seller CTA (Desktop) */}
              <Link
                to={headerConfig.ctaHref || '/become-seller'}
                className="hidden xl:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-orange/40 text-brand-orange bg-orange-50/60 hover:bg-brand-orange hover:text-white transition-all text-xs font-semibold"
              >
                <Store className="w-3.5 h-3.5" />
                <span>{headerConfig.ctaLabel || 'Become a Seller'}</span>
              </Link>

              {/* Wishlist Icon */}
              <Link
                to="/wishlist"
                className="relative p-2 text-gray-600 hover:text-brand-green hover:bg-gray-100 rounded-full transition-colors"
                title="Your Wishlist"
                aria-label="Wishlist"
              >
                <Heart className="w-5 h-5" />
                {wishlistCount > 0 && (
                  <span className="absolute top-0 right-0 w-4 h-4 bg-brand-orange text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                    {wishlistCount}
                  </span>
                )}
              </Link>

              {/* Cart Icon & Total */}
              <Link
                to="/cart"
                className="flex items-center gap-2 p-1.5 sm:px-3 sm:py-1.5 rounded-full sm:rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-brand-green border border-transparent sm:border-gray-200 transition-all"
                title="Shopping Cart"
                aria-label="Shopping Cart"
              >
                <div className="relative">
                  <ShoppingCart className="w-5 h-5 text-brand-green" />
                  {cartCount > 0 && (
                    <span className="absolute -top-1.5 -right-2 w-4 h-4 bg-brand-orange text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                      {cartCount}
                    </span>
                  )}
                </div>
                <div className="hidden lg:block text-left text-xs leading-none">
                  <span className="text-[10px] text-gray-500 block">Total</span>
                  <span className="font-bold text-gray-900">₹{cartTotal}</span>
                </div>
              </Link>

              {/* Account Dropdown */}
              <div ref={accountRef} className="relative">
                <button
                  onClick={() => setAccountMenuOpen(!accountMenuOpen)}
                  className="flex items-center gap-1.5 p-1.5 sm:p-2 rounded-full hover:bg-gray-100 text-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-green"
                  aria-label="Account Menu"
                >
                  <div className="w-8 h-8 rounded-full bg-emerald-100 text-brand-green flex items-center justify-center font-bold text-xs ring-2 ring-emerald-600/30 overflow-hidden">
                    {currentUser?.avatar ? (
                      <img src={currentUser.avatar} alt={currentUser.name} className="w-full h-full object-cover" />
                    ) : (
                      <User className="w-4 h-4" />
                    )}
                  </div>
                  <div className="hidden md:flex flex-col text-left">
                    <span className="text-xs font-semibold text-gray-800 leading-tight truncate max-w-[90px]">
                      {currentUser ? currentUser.name.split(' ')[0] : 'Sign In'}
                    </span>
                    <span className="text-[10px] text-brand-orange font-bold uppercase leading-none">
                      {currentUser ? currentUser.role.replace('_', ' ') : 'OTP / Login'}
                    </span>
                  </div>
                  <ChevronDown className="w-3.5 h-3.5 text-gray-400 hidden sm:block" />
                </button>

                {/* Account Dropdown Menu */}
                {accountMenuOpen && (
                  <div className="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2.5 z-50 animate-in fade-in zoom-in-95 duration-150">
                    <div className="px-4 py-2 border-b border-gray-100">
                      <p className="text-[11px] text-gray-500">Welcome to GanvGaliHut</p>
                      <p className="text-sm font-bold text-gray-900 truncate">
                        {currentUser ? currentUser.name : 'Namaste, Guest'}
                      </p>
                      <div className="flex items-center gap-1.5 mt-1">
                        <span className="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded uppercase">
                          Role: {currentUser ? currentUser.role.replace('_', ' ') : 'Visitor'}
                        </span>
                        {currentUser?.status === 'active' && (
                          <span className="text-[10px] text-emerald-600 font-semibold flex items-center gap-0.5">
                            ● Active
                          </span>
                        )}
                      </div>
                    </div>

                    {/* Quick Role Switcher (Multi-level user access demonstration) */}
                    <div className="px-3 py-2 bg-gray-50/80 border-b border-gray-100 text-[11px]">
                      <span className="text-gray-500 font-semibold block mb-1.5">Switch Access Role:</span>
                      <div className="grid grid-cols-3 gap-1">
                        <button
                          onClick={() => switchRole('super_admin')}
                          className={`px-1.5 py-1 rounded text-[10px] font-bold transition-all ${
                            currentRole === 'super_admin'
                              ? 'bg-red-600 text-white shadow-xs'
                              : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                          }`}
                        >
                          Super Admin
                        </button>
                        <button
                          onClick={() => switchRole('vendor')}
                          className={`px-1.5 py-1 rounded text-[10px] font-bold transition-all ${
                            currentRole === 'vendor'
                              ? 'bg-brand-orange text-white shadow-xs'
                              : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                          }`}
                        >
                          Vendor
                        </button>
                        <button
                          onClick={() => switchRole('customer')}
                          className={`px-1.5 py-1 rounded text-[10px] font-bold transition-all ${
                            currentRole === 'customer'
                              ? 'bg-brand-green text-white shadow-xs'
                              : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                          }`}
                        >
                          Customer
                        </button>
                      </div>
                    </div>

                    <div className="py-1">
                      <Link
                        to="/admin"
                        onClick={() => setAccountMenuOpen(false)}
                        className="flex items-center justify-between px-4 py-2 text-xs font-bold text-brand-green hover:bg-emerald-50"
                      >
                        <span className="flex items-center gap-2">
                          <Settings className="w-4 h-4 text-brand-green" />
                          Admin Operations Center
                        </span>
                        <span className="px-1.5 py-0.5 bg-emerald-100 text-[9px] font-bold rounded">
                          10 Modules
                        </span>
                      </Link>

                      <Link
                        to="/nearby-artisans"
                        onClick={() => setAccountMenuOpen(false)}
                        className="flex items-center gap-2 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-brand-green"
                      >
                        <MapPin className="w-4 h-4 text-brand-orange" />
                        Nearby Artisan Map Directory
                      </Link>

                      <Link
                        to="/account"
                        onClick={() => setAccountMenuOpen(false)}
                        className="flex items-center gap-2 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-brand-green"
                      >
                        <User className="w-4 h-4 text-gray-400" />
                        Buyer Profile & Addresses
                      </Link>

                      <Link
                        to="/seller/dashboard"
                        onClick={() => setAccountMenuOpen(false)}
                        className="flex items-center gap-2 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-emerald-50 hover:text-brand-green"
                      >
                        <Store className="w-4 h-4 text-brand-orange" />
                        Seller Dashboard
                      </Link>
                    </div>

                    <div className="border-t border-gray-100 pt-1.5 px-3">
                      {!currentUser ? (
                        <button
                          onClick={() => {
                            setAccountMenuOpen(false);
                            openAuthModal();
                          }}
                          className="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold bg-brand-green text-white rounded-lg hover:bg-brand-green-dark shadow-sm transition-all"
                        >
                          <ShieldCheck className="w-4 h-4" />
                          Sign In / Mobile OTP
                        </button>
                      ) : (
                        <button
                          onClick={() => {
                            setAccountMenuOpen(false);
                            logout();
                          }}
                          className="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg text-left"
                        >
                          <LogOut className="w-4 h-4" />
                          Sign Out ({currentUser.name.split(' ')[0]})
                        </button>
                      )}
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>

        {/* Mobile Search Input (Visible on small screens) */}
        {headerConfig.showSearch && (
          <div className="pb-3 sm:hidden w-full min-w-0 max-w-full">
            <form onSubmit={handleSearchSubmit} className="relative w-full">
              <input
                type="text"
                placeholder="Search village items..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-9 pr-20 py-2 text-xs bg-gray-50 border border-gray-200 rounded-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-green"
              />
              <Search className="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <button
                type="submit"
                className="absolute right-1 top-1/2 -translate-y-1/2 px-3 py-1 bg-brand-green text-white text-[11px] font-semibold rounded-full"
              >
                Search
              </button>
            </form>
          </div>
        )}
      </div>

      {/* Mobile Drawer Menu */}
      {mobileMenuOpen && (
        <div className="lg:hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex">
          <div className="w-4/5 max-w-sm bg-white h-full shadow-2xl flex flex-col overflow-y-auto animate-in slide-in-from-left duration-200">
            <div className="p-4 border-b border-gray-100 flex items-center justify-between bg-brand-cream">
              <div className="flex items-center">
                <img src="/logo.png" alt="GanvGaliHut" className="h-8 w-auto object-contain" />
              </div>
              <button
                onClick={() => setMobileMenuOpen(false)}
                className="p-1 rounded-full text-gray-500 hover:bg-gray-200"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-4 flex-1">
              <div className="mb-4 p-3 bg-emerald-50 rounded-lg border border-emerald-100 text-xs">
                <p className="font-semibold text-brand-green">Direct Village Commerce</p>
                <p className="text-gray-600 mt-0.5">Deshi ki mitti ka swad • Ganv se ghar tak</p>
              </div>

              <div className="space-y-1">
                <p className="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-2 mb-1">
                  Shop Categories
                </p>
                {headerConfig.navItems.map((item, idx) => (
                  <Link
                    key={idx}
                    to={item.href}
                    onClick={() => setMobileMenuOpen(false)}
                    className="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-emerald-50 hover:text-brand-green rounded-lg transition-colors"
                  >
                    {item.label}
                  </Link>
                ))}
              </div>

              <div className="border-t border-gray-100 my-4 pt-3 space-y-1">
                <p className="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-2 mb-1">
                  Artisans & Interactive Map
                </p>
                <Link
                  to="/nearby-artisans"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-bold text-brand-green bg-emerald-50 hover:bg-emerald-100 rounded-lg"
                >
                  📍 Nearby Artisans (OpenStreetMap)
                </Link>
                <Link
                  to="/become-seller"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-medium text-brand-orange hover:bg-orange-50 rounded-lg"
                >
                  Become a Village Seller
                </Link>
                <Link
                  to="/seller-register"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-emerald-50 rounded-lg"
                >
                  Seller Registration Form
                </Link>
                <Link
                  to="/seller/dashboard"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-emerald-50 rounded-lg"
                >
                  Seller Dashboard
                </Link>
              </div>

              <div className="border-t border-gray-100 my-4 pt-3 space-y-1">
                <p className="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-2 mb-1">
                  Admin Previews
                </p>
                <Link
                  to="/admin"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-emerald-50 rounded-lg"
                >
                  Admin Central Dashboard
                </Link>
                <Link
                  to="/admin/site-customization"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-emerald-50 rounded-lg"
                >
                  Header / Footer Settings
                </Link>
                <Link
                  to="/admin/blog"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-emerald-50 rounded-lg"
                >
                  Blog & CMS Management
                </Link>
                <Link
                  to="/admin/seo-settings"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-emerald-50 rounded-lg"
                >
                  SEO & Sitemap Settings
                </Link>
                <Link
                  to="/admin/security-settings"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-emerald-50 rounded-lg"
                >
                  Security & Privacy
                </Link>
              </div>

              {/* User / Sign In Button in Mobile Drawer */}
              <div className="border-t border-gray-100 my-4 pt-3">
                {!currentUser ? (
                  <button
                    onClick={() => {
                      setMobileMenuOpen(false);
                      openAuthModal();
                    }}
                    className="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-green text-white font-bold text-xs rounded-xl shadow-sm hover:bg-brand-green-dark"
                  >
                    <ShieldCheck className="w-4 h-4" />
                    Sign In with Google / Phone OTP
                  </button>
                ) : (
                  <div className="p-3 bg-gray-50 rounded-xl space-y-2">
                    <div className="flex items-center justify-between">
                      <span className="text-xs font-bold text-gray-800">{currentUser.name}</span>
                      <span className="text-[10px] bg-emerald-100 text-brand-green px-2 py-0.5 rounded font-bold uppercase">
                        {currentUser.role.replace('_', ' ')}
                      </span>
                    </div>
                    <button
                      onClick={() => {
                        setMobileMenuOpen(false);
                        logout();
                      }}
                      className="w-full text-center text-xs text-red-600 font-semibold hover:underline"
                    >
                      Sign Out
                    </button>
                  </div>
                )}
              </div>
            </div>

            <div className="p-4 bg-gray-50 border-t border-gray-100 text-xs text-gray-500 text-center">
              GanvGaliHut • S S Industries Enterprise
            </div>
          </div>
          <div className="flex-1" onClick={() => setMobileMenuOpen(false)} />
        </div>
      )}

      {/* Pincode / Location Modal */}
      {locationModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 animate-in fade-in zoom-in-95 duration-150">
            <div className="flex justify-between items-center mb-4">
              <div className="flex items-center gap-2">
                <MapPin className="w-5 h-5 text-brand-orange" />
                <h3 className="font-bold text-gray-900 text-base">Select Delivery Location</h3>
              </div>
              <button
                onClick={() => setLocationModalOpen(false)}
                className="p-1 rounded-full text-gray-400 hover:text-gray-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
            
            <p className="text-xs text-gray-600 mb-4">
              Enter your Indian postal pincode to check dispatch timelines directly from rural artisan clusters.
            </p>

            <form onSubmit={handlePincodeSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-gray-700 mb-1">
                  6-Digit Postal Pincode
                </label>
                <input
                  type="text"
                  maxLength={6}
                  value={pincode}
                  onChange={(e) => setPincode(e.target.value.replace(/\D/g, ''))}
                  placeholder="e.g. 400001"
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-green focus:border-brand-green"
                  autoFocus
                />
              </div>

              <div className="flex items-center gap-2 pt-2">
                <button
                  type="submit"
                  className="flex-1 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold py-2.5 rounded-lg shadow-sm transition-all"
                >
                  Set Delivery Location
                </button>
                <button
                  type="button"
                  onClick={() => setLocationModalOpen(false)}
                  className="px-4 py-2.5 border border-gray-200 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-50"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </header>
  );

  function setQueryAndShow(val: string) {
    setSearchQuery(val);
    setShowSuggestions(true);
  }
};
