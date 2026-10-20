import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import {
  ShieldCheck,
  CreditCard,
  Truck,
  CheckCircle2,
  Lock,
  ArrowLeft,
  Smartphone,
  Banknote,
  Building
} from 'lucide-react';
import { useCartWishlist } from '../context/CartWishlistContext';
import { useDemoModal } from '../context/DemoModalContext';

export const Checkout: React.FC = () => {
  const { cart, cartTotal, cartCount } = useCartWishlist();
  const { showDemoNotice } = useDemoModal();

  const [paymentMethod, setPaymentMethod] = useState<'upi' | 'cod' | 'card' | 'netbanking'>('upi');
  const [address, setAddress] = useState({
    fullName: 'Pooja Kashyap',
    phone: '9876543210',
    pincode: '400001',
    street: 'Flat 402, Green Orchid Apartments, Fort Road',
    city: 'Mumbai',
    state: 'Maharashtra',
  });

  const shippingFee = cartTotal >= 499 || cartTotal === 0 ? 0 : 60;
  const finalTotal = cartTotal + shippingFee;

  const handlePlaceOrder = (e: React.FormEvent) => {
    e.preventDefault();
    // Section 12 requirement: mandatory demo notice on order & payment
    showDemoNotice(
      'Place Order & Payment Gateway',
      'This feature is currently available for preview only. Backend functionality and payment gateway processing will be implemented in a future phase.'
    );
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3 text-xs text-amber-900">
        <Lock className="w-5 h-5 text-brand-orange shrink-0" />
        <div>
          <span className="font-bold">Prototype Checkout Experience:</span> This screen demonstrates the multi-step checkout workflow. Clicking "Place Order & Pay" will display the required demo notice without executing real charges.
        </div>
      </div>

      <div className="flex items-center gap-2">
        <Link to="/cart" className="text-gray-500 hover:text-gray-800 p-1 rounded-lg hover:bg-gray-100">
          <ArrowLeft className="w-5 h-5" />
        </Link>
        <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
          Checkout Preview
        </h1>
      </div>

      <form onSubmit={handlePlaceOrder} className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {/* Left Form: Address, Delivery & Payment */}
        <div className="lg:col-span-8 space-y-6">
          
          {/* Step 1: Delivery Address */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-gray-100">
              <div className="flex items-center gap-2 font-bold text-base text-gray-900 font-display">
                <span className="w-6 h-6 rounded-full bg-brand-green text-white text-xs flex items-center justify-center">1</span>
                <span>Delivery Address (Sample Buyer)</span>
              </div>
              <span className="text-xs text-brand-green font-semibold">Demo autofilled</span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Full Recipient Name</label>
                <input
                  type="text"
                  value={address.fullName}
                  onChange={(e) => setAddress({ ...address, fullName: e.target.value })}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg"
                  required
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Mobile Number (For Delivery SMS)</label>
                <input
                  type="text"
                  value={address.phone}
                  onChange={(e) => setAddress({ ...address, phone: e.target.value })}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg"
                  required
                />
              </div>

              <div className="sm:col-span-2">
                <label className="block font-semibold text-gray-700 mb-1">House / Flat / Street Address</label>
                <input
                  type="text"
                  value={address.street}
                  onChange={(e) => setAddress({ ...address, street: e.target.value })}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg"
                  required
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Postal Pincode</label>
                <input
                  type="text"
                  value={address.pincode}
                  onChange={(e) => setAddress({ ...address, pincode: e.target.value })}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg"
                  required
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">City & State</label>
                <input
                  type="text"
                  value={`${address.city}, ${address.state}`}
                  onChange={() => {}}
                  className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg"
                  disabled
                />
              </div>
            </div>
          </div>

          {/* Step 2: Shipping Option */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="flex items-center gap-2 font-bold text-base text-gray-900 font-display pb-3 border-b border-gray-100">
              <span className="w-6 h-6 rounded-full bg-brand-green text-white text-xs flex items-center justify-center">2</span>
              <span>Shipping Method</span>
            </div>

            <div className="p-3.5 rounded-xl border border-emerald-300 bg-emerald-50/50 flex items-center justify-between text-xs">
              <div className="flex items-center gap-3">
                <Truck className="w-5 h-5 text-brand-green" />
                <div>
                  <p className="font-bold text-gray-900">Direct Rural Express (India Post & Registered Carrier)</p>
                  <p className="text-gray-500">Delivers in 4 to 7 working days straight from artisan cluster.</p>
                </div>
              </div>
              <span className="font-bold text-brand-green">
                {shippingFee === 0 ? 'FREE' : `₹${shippingFee}`}
              </span>
            </div>
          </div>

          {/* Step 3: Payment Method Selection */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="flex items-center gap-2 font-bold text-base text-gray-900 font-display pb-3 border-b border-gray-100">
              <span className="w-6 h-6 rounded-full bg-brand-green text-white text-xs flex items-center justify-center">3</span>
              <span>Select Payment Method (Preview)</span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label
                className={`p-4 rounded-xl border cursor-pointer transition-all flex items-center gap-3 ${
                  paymentMethod === 'upi' ? 'border-brand-green bg-emerald-50/50 shadow-xs' : 'border-gray-200 hover:border-gray-300'
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
                  <p className="text-[10px] text-gray-500">Fast & Zero transaction fee</p>
                </div>
              </label>

              <label
                className={`p-4 rounded-xl border cursor-pointer transition-all flex items-center gap-3 ${
                  paymentMethod === 'cod' ? 'border-brand-green bg-emerald-50/50 shadow-xs' : 'border-gray-200 hover:border-gray-300'
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
                  <p className="text-[10px] text-gray-500">Pay cash upon village package arrival</p>
                </div>
              </label>

              <label
                className={`p-4 rounded-xl border cursor-pointer transition-all flex items-center gap-3 ${
                  paymentMethod === 'card' ? 'border-brand-green bg-emerald-50/50 shadow-xs' : 'border-gray-200 hover:border-gray-300'
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
                className={`p-4 rounded-xl border cursor-pointer transition-all flex items-center gap-3 ${
                  paymentMethod === 'netbanking' ? 'border-brand-green bg-emerald-50/50 shadow-xs' : 'border-gray-200 hover:border-gray-300'
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
                  <p className="text-[10px] text-gray-500">All major Indian banks supported</p>
                </div>
              </label>
            </div>
          </div>

        </div>

        {/* Right: Order Summary & Place Order CTA */}
        <div className="lg:col-span-4 space-y-4">
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4 sticky top-28">
            <h2 className="text-base font-bold text-gray-900 font-display border-b border-gray-100 pb-3">
              Order Items ({cartCount})
            </h2>

            <div className="max-h-60 overflow-y-auto divide-y divide-gray-100 pr-1">
              {cart.map(({ product, quantity }) => (
                <div key={product.id} className="py-2.5 flex items-center justify-between text-xs">
                  <div className="truncate pr-2">
                    <p className="font-semibold text-gray-800 truncate">{product.name}</p>
                    <p className="text-[10px] text-gray-400">Qty: {quantity} × ₹{product.price}</p>
                  </div>
                  <span className="font-bold text-gray-900 shrink-0">
                    ₹{product.price * quantity}
                  </span>
                </div>
              ))}
            </div>

            <div className="border-t border-gray-100 pt-3 space-y-2 text-xs text-gray-600">
              <div className="flex justify-between">
                <span>Items Subtotal</span>
                <span className="font-semibold text-gray-900">₹{cartTotal}</span>
              </div>
              <div className="flex justify-between">
                <span>Shipping</span>
                <span className="font-semibold text-brand-green">
                  {shippingFee === 0 ? 'FREE' : `₹${shippingFee}`}
                </span>
              </div>
              <div className="flex justify-between pt-2 border-t border-gray-100 text-sm font-bold text-gray-900">
                <span>Total Amount</span>
                <span className="text-xl text-brand-green">₹{finalTotal}</span>
              </div>
            </div>

            <button
              type="submit"
              className="w-full py-4 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-lg hover:shadow-emerald-900/20 transition-all cursor-pointer active:scale-95"
            >
              Place Order & Pay
            </button>

            <p className="text-[10px] text-center text-gray-400">
              Guaranteed safe & secure checkout protected by 256-bit encryption.
            </p>
          </div>
        </div>

      </form>
    </div>
  );
};
