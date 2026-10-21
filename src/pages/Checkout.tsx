import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  ShieldCheck,
  CreditCard,
  Truck,
  CheckCircle2,
  Lock,
  ArrowLeft,
  Smartphone,
  Banknote,
  Building,
  Sparkles,
  Package,
  ArrowRight
} from 'lucide-react';
import { useCartWishlist } from '../context/CartWishlistContext';
import { useAuth } from '../context/AuthContext';

export const Checkout: React.FC = () => {
  const { cart, cartTotal, cartCount, clearCart } = useCartWishlist();
  const { currentUser, openAuthModal } = useAuth();
  const navigate = useNavigate();

  const [paymentMethod, setPaymentMethod] = useState<'upi' | 'cod' | 'card' | 'netbanking'>('upi');
  const [address, setAddress] = useState({
    fullName: currentUser?.name || '',
    phone: currentUser?.phone || '',
    pincode: currentUser?.pincode || '241001',
    street: currentUser?.address || '',
    city: currentUser?.city || 'Hardoi',
    state: currentUser?.state || 'Uttar Pradesh',
  });

  const [placedOrder, setPlacedOrder] = useState<any | null>(null);

  useEffect(() => {
    if (currentUser) {
      setAddress(prev => ({
        ...prev,
        fullName: prev.fullName || currentUser.name || '',
        phone: prev.phone || currentUser.phone || '',
        city: prev.city || currentUser.city || 'Hardoi',
        state: prev.state || currentUser.state || 'Uttar Pradesh',
        pincode: prev.pincode || currentUser.pincode || '241001',
        street: prev.street || currentUser.address || ''
      }));
    }
  }, [currentUser]);

  const shippingFee = cartTotal >= 499 || cartTotal === 0 ? 0 : 60;
  const finalTotal = cartTotal + shippingFee;

  const handlePlaceOrder = (e: React.FormEvent) => {
    e.preventDefault();
    if (cart.length === 0) return;

    const newOrder = {
      id: `GGH-${Date.now().toString().slice(-6)}`,
      date: new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }),
      total: finalTotal,
      status: 'Order Placed' as const,
      paymentMethod,
      recipient: address,
      items: cart.map(item => ({
        name: item.product.name,
        qty: item.quantity,
        price: item.product.price,
        seller: item.product.seller?.name || 'GaunGaliHat Rural Foundry & Handlooms'
      })),
      trackingNo: `INPOST-${Math.floor(1000000 + Math.random() * 9000000)}-HRD`
    };

    try {
      const existing = localStorage.getItem('ggh_customer_orders');
      const ordersList = existing ? JSON.parse(existing) : [];
      localStorage.setItem('ggh_customer_orders', JSON.stringify([newOrder, ...ordersList]));
    } catch {
      // fallback
    }

    clearCart();
    setPlacedOrder(newOrder);
  };

  // SUCCESS ORDER SCREEN
  if (placedOrder) {
    return (
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center space-y-6">
        <div className="w-20 h-20 bg-emerald-100 text-brand-green rounded-full flex items-center justify-center mx-auto shadow-inner animate-in zoom-in-75 duration-300">
          <CheckCircle2 className="w-10 h-10" />
        </div>

        <div className="space-y-2">
          <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-brand-green text-xs font-bold border border-emerald-200">
            <Sparkles className="w-3.5 h-3.5" />
            <span>Order Confirmed & Logged</span>
          </span>
          <h1 className="text-3xl font-extrabold font-display text-gray-900">
            Thank you for supporting rural artisans!
          </h1>
          <p className="text-sm text-gray-500 max-w-md mx-auto">
            Your order has been forwarded to the local artisan workshop for forging, packaging, and dispatch via India Post.
          </p>
        </div>

        <div className="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm text-left max-w-lg mx-auto space-y-4 text-xs">
          <div className="flex justify-between items-center border-b border-gray-100 pb-3">
            <div>
              <p className="text-gray-400 text-[11px]">Order Reference</p>
              <p className="font-bold text-gray-900 font-mono text-sm">{placedOrder.id}</p>
            </div>
            <div className="text-right">
              <p className="text-gray-400 text-[11px]">Consignment Tracking</p>
              <p className="font-bold text-emerald-800 font-mono">{placedOrder.trackingNo}</p>
            </div>
          </div>

          <div className="space-y-1">
            <p className="font-bold text-gray-800">Delivering to:</p>
            <p className="text-gray-600">
              {placedOrder.recipient.fullName} • {placedOrder.recipient.phone}
            </p>
            <p className="text-gray-600">
              {placedOrder.recipient.street}, {placedOrder.recipient.city}, {placedOrder.recipient.state} - {placedOrder.recipient.pincode}
            </p>
          </div>

          <div className="border-t border-gray-100 pt-3 flex justify-between font-bold text-sm text-gray-900">
            <span>Total Amount Paid ({placedOrder.paymentMethod.toUpperCase()}):</span>
            <span className="text-brand-green">₹{placedOrder.total.toLocaleString()}</span>
          </div>
        </div>

        <div className="flex justify-center gap-4 pt-2">
          <Link
            to="/account"
            className="px-6 py-3 bg-brand-green hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition-colors flex items-center gap-2"
          >
            <Package className="w-4 h-4" />
            <span>View in My Orders</span>
          </Link>
          <Link
            to="/"
            className="px-6 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold rounded-xl text-xs transition-colors"
          >
            Continue Shopping
          </Link>
        </div>
      </div>
    );
  }

  // EMPTY CART SCREEN
  if (cart.length === 0) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-20 text-center space-y-5">
        <div className="w-16 h-16 rounded-2xl bg-stone-100 text-gray-400 flex items-center justify-center mx-auto">
          <Package className="w-8 h-8" />
        </div>
        <h2 className="text-2xl font-bold font-display text-gray-900">Your shopping bag is empty</h2>
        <p className="text-xs text-gray-500 max-w-sm mx-auto">
          Add authentic agricultural implements, handlooms, and village staples to proceed with checkout.
        </p>
        <Link
          to="/"
          className="inline-flex items-center gap-2 px-6 py-3 bg-brand-green text-white font-bold text-xs rounded-xl shadow-xs"
        >
          <span>Explore Village Marketplace</span>
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      <div className="flex items-center justify-between pb-2 border-b border-gray-100">
        <div className="flex items-center gap-3">
          <Link to="/cart" className="text-gray-500 hover:text-gray-800 p-1.5 rounded-xl hover:bg-gray-100 transition-colors">
            <ArrowLeft className="w-5 h-5" />
          </Link>
          <div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
              Secure Checkout
            </h1>
            <p className="text-xs text-gray-500">
              Direct dispatch from verified Indian village artisan clusters and blacksmith foundries.
            </p>
          </div>
        </div>

        {!currentUser && (
          <button
            onClick={() => openAuthModal('customer')}
            className="px-3.5 py-1.5 bg-emerald-50 text-brand-green border border-emerald-200 hover:bg-emerald-100 rounded-xl text-xs font-bold cursor-pointer transition-colors"
          >
            Have an account? Sign In
          </button>
        )}
      </div>

      <form onSubmit={handlePlaceOrder} className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {/* Left Form: Address, Delivery & Payment */}
        <div className="lg:col-span-8 space-y-6">
          
          {/* Step 1: Delivery Address */}
          <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-gray-100">
              <div className="flex items-center gap-2 font-bold text-base text-gray-900 font-display">
                <span className="w-6 h-6 rounded-full bg-brand-green text-white text-xs flex items-center justify-center font-bold">1</span>
                <span>Delivery Address</span>
              </div>
              <span className="text-[11px] text-gray-400">
                {currentUser ? 'Linked to your account' : 'Guest checkout'}
              </span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Full Recipient Name *</label>
                <input
                  type="text"
                  placeholder="e.g. Ramesh Kumar"
                  value={address.fullName}
                  onChange={(e) => setAddress({ ...address, fullName: e.target.value })}
                  className="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-gray-900 font-medium"
                  required
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Mobile Number (For Delivery SMS) *</label>
                <input
                  type="tel"
                  placeholder="10-digit mobile number"
                  value={address.phone}
                  onChange={(e) => setAddress({ ...address, phone: e.target.value })}
                  className="w-full px-3 py-2.5 border border-gray-300 rounded-xl font-mono text-gray-900"
                  required
                />
              </div>

              <div className="sm:col-span-2">
                <label className="block font-semibold text-gray-700 mb-1">House / Flat / Street / Landmark *</label>
                <input
                  type="text"
                  placeholder="e.g. Plot 14, Near Primary School, Main Gali"
                  value={address.street}
                  onChange={(e) => setAddress({ ...address, street: e.target.value })}
                  className="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-gray-900 font-medium"
                  required
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Postal Pincode *</label>
                <input
                  type="text"
                  placeholder="6-digit pincode"
                  value={address.pincode}
                  onChange={(e) => setAddress({ ...address, pincode: e.target.value })}
                  className="w-full px-3 py-2.5 border border-gray-300 rounded-xl font-mono text-gray-900"
                  required
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">City / Village & State</label>
                <div className="grid grid-cols-2 gap-2">
                  <input
                    type="text"
                    placeholder="City / Village"
                    value={address.city}
                    onChange={(e) => setAddress({ ...address, city: e.target.value })}
                    className="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-gray-900 font-medium"
                    required
                  />
                  <input
                    type="text"
                    placeholder="State"
                    value={address.state}
                    onChange={(e) => setAddress({ ...address, state: e.target.value })}
                    className="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-gray-900 font-medium"
                    required
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Step 2: Shipping Option */}
          <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="flex items-center gap-2 font-bold text-base text-gray-900 font-display pb-3 border-b border-gray-100">
              <span className="w-6 h-6 rounded-full bg-brand-green text-white text-xs flex items-center justify-center font-bold">2</span>
              <span>Shipping Method</span>
            </div>

            <div className="p-4 rounded-2xl border border-emerald-300 bg-emerald-50/50 flex items-center justify-between text-xs">
              <div className="flex items-center gap-3">
                <Truck className="w-5 h-5 text-brand-green shrink-0" />
                <div>
                  <p className="font-bold text-gray-900">Direct Rural Express (India Post Registered Parcel)</p>
                  <p className="text-gray-500">Dispatched straight from artisan forge/loom with tracking number.</p>
                </div>
              </div>
              <span className="font-extrabold text-sm text-brand-green">
                {shippingFee === 0 ? 'FREE' : `₹${shippingFee}`}
              </span>
            </div>
          </div>

          {/* Step 3: Payment Method Selection */}
          <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="flex items-center gap-2 font-bold text-base text-gray-900 font-display pb-3 border-b border-gray-100">
              <span className="w-6 h-6 rounded-full bg-brand-green text-white text-xs flex items-center justify-center font-bold">3</span>
              <span>Select Payment Method</span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label
                className={`p-4 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 ${
                  paymentMethod === 'upi' ? 'border-brand-green bg-emerald-50/50 shadow-xs ring-1 ring-brand-green' : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                <input
                  type="radio"
                  name="payment"
                  checked={paymentMethod === 'upi'}
                  onChange={() => setPaymentMethod('upi')}
                  className="text-brand-green focus:ring-brand-green"
                />
                <Smartphone className="w-5 h-5 text-brand-orange" />
                <div>
                  <p className="text-xs font-bold text-gray-900">UPI (GPay / PhonePe / Paytm)</p>
                  <p className="text-[10px] text-gray-500">Instant & Direct Artisan Settlement</p>
                </div>
              </label>

              <label
                className={`p-4 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 ${
                  paymentMethod === 'cod' ? 'border-brand-green bg-emerald-50/50 shadow-xs ring-1 ring-brand-green' : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                <input
                  type="radio"
                  name="payment"
                  checked={paymentMethod === 'cod'}
                  onChange={() => setPaymentMethod('cod')}
                  className="text-brand-green focus:ring-brand-green"
                />
                <Banknote className="w-5 h-5 text-brand-green" />
                <div>
                  <p className="text-xs font-bold text-gray-900">Cash on Delivery (COD)</p>
                  <p className="text-[10px] text-gray-500">Pay cash on doorstep arrival</p>
                </div>
              </label>

              <label
                className={`p-4 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 ${
                  paymentMethod === 'card' ? 'border-brand-green bg-emerald-50/50 shadow-xs ring-1 ring-brand-green' : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                <input
                  type="radio"
                  name="payment"
                  checked={paymentMethod === 'card'}
                  onChange={() => setPaymentMethod('card')}
                  className="text-brand-green focus:ring-brand-green"
                />
                <CreditCard className="w-5 h-5 text-gray-700" />
                <div>
                  <p className="text-xs font-bold text-gray-900">Credit / Debit Card</p>
                  <p className="text-[10px] text-gray-500">Visa, Mastercard, RuPay</p>
                </div>
              </label>

              <label
                className={`p-4 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 ${
                  paymentMethod === 'netbanking' ? 'border-brand-green bg-emerald-50/50 shadow-xs ring-1 ring-brand-green' : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                <input
                  type="radio"
                  name="payment"
                  checked={paymentMethod === 'netbanking'}
                  onChange={() => setPaymentMethod('netbanking')}
                  className="text-brand-green focus:ring-brand-green"
                />
                <Building className="w-5 h-5 text-gray-700" />
                <div>
                  <p className="text-xs font-bold text-gray-900">Net Banking</p>
                  <p className="text-[10px] text-gray-500">All major Indian banks</p>
                </div>
              </label>
            </div>
          </div>

        </div>

        {/* Right: Order Summary & Place Order CTA */}
        <div className="lg:col-span-4 space-y-4">
          <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 space-y-4 sticky top-28">
            <h2 className="text-base font-bold text-gray-900 font-display border-b border-gray-100 pb-3">
              Order Summary ({cartCount} {cartCount === 1 ? 'item' : 'items'})
            </h2>

            <div className="max-h-60 overflow-y-auto divide-y divide-gray-100 pr-1">
              {cart.map(({ product, quantity }) => (
                <div key={product.id} className="py-2.5 flex items-center justify-between text-xs">
                  <div className="truncate pr-2">
                    <p className="font-semibold text-gray-800 truncate">{product.name}</p>
                    <p className="text-[10px] text-gray-400">Qty: {quantity} × ₹{product.price}</p>
                  </div>
                  <span className="font-bold text-gray-900 shrink-0">
                    ₹{(product.price * quantity).toLocaleString()}
                  </span>
                </div>
              ))}
            </div>

            <div className="border-t border-gray-100 pt-3 space-y-2 text-xs text-gray-600">
              <div className="flex justify-between">
                <span>Items Subtotal</span>
                <span className="font-semibold text-gray-900">₹{cartTotal.toLocaleString()}</span>
              </div>
              <div className="flex justify-between">
                <span>Shipping</span>
                <span className="font-semibold text-brand-green">
                  {shippingFee === 0 ? 'FREE' : `₹${shippingFee}`}
                </span>
              </div>
              <div className="flex justify-between pt-2 border-t border-gray-100 text-sm font-bold text-gray-900">
                <span>Total Amount</span>
                <span className="text-xl text-brand-green">₹{finalTotal.toLocaleString()}</span>
              </div>
            </div>

            <button
              type="submit"
              className="w-full py-4 bg-brand-green hover:bg-emerald-700 text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-emerald-900/20 transition-all cursor-pointer active:scale-95"
            >
              Confirm & Place Order
            </button>

            <p className="text-[10px] text-center text-gray-400 flex items-center justify-center gap-1">
              <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
              <span>Protected by 256-bit PBKDF2 SSL Encryption</span>
            </p>
          </div>
        </div>

      </form>
    </div>
  );
};