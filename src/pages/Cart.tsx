import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  Trash2,
  ShoppingBag,
  ArrowRight,
  ShieldCheck,
  Truck,
  Sparkles,
  Tag,
  ArrowLeft
} from 'lucide-react';
import { useCartWishlist } from '../context/CartWishlistContext';
import { useDemoModal } from '../context/DemoModalContext';

export const Cart: React.FC = () => {
  const { cart, updateQuantity, removeFromCart, clearCart, cartTotal, cartCount } = useCartWishlist();
  const { showDemoNotice } = useDemoModal();
  const navigate = useNavigate();

  const [promoCode, setPromoCode] = useState('');
  const [promoApplied, setPromoApplied] = useState(false);

  const shippingFee = cartTotal >= 499 || cartTotal === 0 ? 0 : 60;
  const discountAmount = promoApplied ? Math.round(cartTotal * 0.1) : 0;
  const finalTotal = cartTotal + shippingFee - discountAmount;

  const handleApplyPromo = (e: React.FormEvent) => {
    e.preventDefault();
    if (!promoCode.trim()) return;
    showDemoNotice(
      'Apply Coupon Code',
      `Coupon code "${promoCode}" validated for demo preview. Real coupon redemptions will be connected to the promotion engine in Phase 2.`
    );
    setPromoApplied(true);
  };

  const handleProceedToCheckout = () => {
    navigate('/checkout');
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      <div>
        <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
          Shopping Cart ({cartCount} {cartCount === 1 ? 'item' : 'items'})
        </h1>
        <p className="text-xs text-gray-600 mt-1">
          Review your village crafts and pantry staples before checkout preview.
        </p>
      </div>

      {cart.length > 0 ? (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
          
          {/* Cart Items List */}
          <div className="lg:col-span-8 space-y-4">
            <div className="bg-white rounded-2xl border border-gray-100 shadow-xs divide-y divide-gray-100 overflow-hidden">
              {cart.map(({ product, quantity }) => (
                <div key={product.id} className="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                  <img
                    src={product.image}
                    alt={product.name}
                    className="w-20 h-20 rounded-xl object-cover border border-gray-100 shrink-0"
                  />

                  <div className="flex-1 min-w-0">
                    <span className="text-[10px] text-brand-orange font-bold uppercase">
                      {product.category}
                    </span>
                    <Link
                      to={`/product/${product.slug}`}
                      className="block text-sm font-bold text-gray-900 hover:text-brand-green truncate font-display"
                    >
                      {product.name}
                    </Link>
                    <p className="text-xs text-gray-500 mt-0.5">By {product.seller.name}</p>
                    <div className="text-xs font-semibold text-gray-800 mt-1">
                      ₹{product.price} each
                    </div>
                  </div>

                  {/* Quantity Stepper */}
                  <div className="flex items-center gap-3">
                    <div className="flex items-center border border-gray-200 rounded-lg bg-gray-50 p-1">
                      <button
                        onClick={() => updateQuantity(product.id, quantity - 1)}
                        className="w-7 h-7 flex items-center justify-center font-bold text-gray-600 hover:bg-white rounded"
                        aria-label="Reduce quantity"
                      >
                        -
                      </button>
                      <span className="w-8 text-center text-xs font-bold text-gray-900">
                        {quantity}
                      </span>
                      <button
                        onClick={() => updateQuantity(product.id, quantity + 1)}
                        className="w-7 h-7 flex items-center justify-center font-bold text-gray-600 hover:bg-white rounded"
                        aria-label="Increase quantity"
                      >
                        +
                      </button>
                    </div>

                    <div className="w-20 text-right font-extrabold text-sm text-gray-900">
                      ₹{product.price * quantity}
                    </div>

                    <button
                      onClick={() => removeFromCart(product.id)}
                      className="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors"
                      title="Remove item"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              ))}
            </div>

            <div className="flex justify-between items-center pt-2">
              <Link
                to="/products"
                className="text-xs font-semibold text-brand-green hover:underline flex items-center gap-1"
              >
                <ArrowLeft className="w-3.5 h-3.5" />
                <span>Continue Browsing Village Goods</span>
              </Link>

              <button
                onClick={clearCart}
                className="text-xs text-gray-500 hover:text-red-600 underline"
              >
                Clear Cart
              </button>
            </div>
          </div>

          {/* Order Summary Box */}
          <div className="lg:col-span-4 space-y-4">
            <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
              <h2 className="text-base font-bold text-gray-900 font-display border-b border-gray-100 pb-3">
                Order Summary
              </h2>

              <div className="space-y-2.5 text-xs text-gray-600">
                <div className="flex justify-between">
                  <span>Subtotal ({cartCount} items)</span>
                  <span className="font-semibold text-gray-900">₹{cartTotal}</span>
                </div>

                <div className="flex justify-between">
                  <span>Village Express Shipping</span>
                  <span className="font-semibold text-gray-900">
                    {shippingFee === 0 ? (
                      <span className="text-brand-green font-bold">FREE</span>
                    ) : (
                      `₹${shippingFee}`
                    )}
                  </span>
                </div>

                {cartTotal < 499 && (
                  <p className="text-[11px] text-amber-700 bg-amber-50 p-2 rounded-lg">
                    Add ₹{499 - cartTotal} more to qualify for <strong>FREE Delivery</strong>.
                  </p>
                )}

                {promoApplied && (
                  <div className="flex justify-between text-brand-green font-semibold">
                    <span>Demo Promo Discount (10%)</span>
                    <span>-₹{discountAmount}</span>
                  </div>
                )}

                <div className="pt-3 border-t border-gray-100 flex justify-between items-baseline">
                  <span className="text-sm font-bold text-gray-900">Total Payable</span>
                  <span className="text-xl font-extrabold text-brand-green">₹{finalTotal}</span>
                </div>
              </div>

              {/* Promo Code Form */}
              <form onSubmit={handleApplyPromo} className="pt-2 flex gap-2">
                <div className="relative flex-1">
                  <input
                    type="text"
                    placeholder="Enter Coupon (e.g. DESI10)"
                    value={promoCode}
                    onChange={(e) => setPromoCode(e.target.value)}
                    className="w-full text-xs pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg uppercase font-mono"
                  />
                  <Tag className="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                </div>
                <button
                  type="submit"
                  className="px-3 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold rounded-lg shrink-0 cursor-pointer"
                >
                  Apply
                </button>
              </form>

              {/* Checkout Button */}
              <div className="pt-2">
                <button
                  onClick={handleProceedToCheckout}
                  className="w-full py-3.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-lg hover:shadow-emerald-900/20 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95"
                >
                  <span>Proceed to Checkout Preview</span>
                  <ArrowRight className="w-4 h-4" />
                </button>
              </div>

              <div className="pt-2 border-t border-gray-100 space-y-1.5 text-[11px] text-gray-500">
                <div className="flex items-center gap-1.5">
                  <ShieldCheck className="w-3.5 h-3.5 text-brand-green shrink-0" />
                  <span>Secure 256-Bit SSL Demo Checkout Flow</span>
                </div>
                <div className="flex items-center gap-1.5">
                  <Truck className="w-3.5 h-3.5 text-brand-orange shrink-0" />
                  <span>Direct Dispatch from Rural Artisan Hubs</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      ) : (
        <div className="bg-white rounded-3xl border border-gray-100 p-12 text-center max-w-md mx-auto my-12 shadow-xs">
          <div className="w-16 h-16 rounded-full bg-emerald-50 text-brand-green flex items-center justify-center mx-auto mb-4">
            <ShoppingBag className="w-8 h-8" />
          </div>
          <h2 className="text-xl font-bold text-gray-900 font-display">Your Cart is Empty</h2>
          <p className="text-xs text-gray-600 mt-2 leading-relaxed">
            Fill your basket with hand-forged agricultural tools, eco golden jute bags, Channapatna wooden toys, or handlooms.
          </p>
          <Link
            to="/products"
            className="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-xl shadow-md transition-all"
          >
            <span>Start Shopping Village Goods</span>
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>
      )}
    </div>
  );
};
