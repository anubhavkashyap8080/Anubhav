import React, { useState } from 'react';
import {
  User,
  Package,
  MapPin,
  Heart,
  Bell,
  Lock,
  ExternalLink,
  ChevronRight,
  Info
} from 'lucide-react';
import { useDemoModal } from '../context/DemoModalContext';
import { useCartWishlist } from '../context/CartWishlistContext';
import { Link } from 'react-router-dom';

export const Account: React.FC = () => {
  const { showDemoNotice } = useDemoModal();
  const { wishlistCount } = useCartWishlist();
  const [activeTab, setActiveTab] = useState<'profile' | 'orders' | 'addresses'>('orders');

  const handleAction = (name: string) => {
    showDemoNotice(
      name,
      'This feature is currently available for preview only. Backend functionality will be implemented in a future phase.'
    );
  };

  const mockPastOrders = [
    {
      id: 'GGH-2026-8812',
      date: 'October 14, 2026',
      total: 1450,
      status: 'Delivered',
      items: [
        { name: 'Vedic A2 Gir Cow Bilona Ghee (500ml)', qty: 1, price: 1450, seller: 'Radha Rani Gir Gaushala' }
      ],
      trackingNo: 'INPOST-8812904-BOM'
    },
    {
      id: 'GGH-2026-7910',
      date: 'September 28, 2026',
      total: 500,
      status: 'Delivered',
      items: [
        { name: 'Handmade Sun-Dried Moong Dal Papad', qty: 1, price: 180, seller: 'Saraswati Mahila SHG' },
        { name: 'Traditional Banarasi Stuffed Red Chilli Pickle', qty: 1, price: 260, seller: 'Saraswati Mahila SHG' }
      ],
      trackingNo: 'INPOST-7910442-BOM'
    }
  ];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3 text-xs text-amber-900">
        <Info className="w-5 h-5 text-brand-orange shrink-0" />
        <div>
          <span className="font-bold">Customer Account Preview:</span> This interface simulates buyer profiles, historical shipments, and saved delivery addresses. Profile edits and password changes will persist once backend authentication is integrated.
        </div>
      </div>

      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div className="flex items-center gap-4">
          <div className="w-16 h-16 rounded-2xl bg-emerald-100 text-brand-green flex items-center justify-center font-bold text-2xl font-display">
            PK
          </div>
          <div>
            <h1 className="text-2xl font-bold text-gray-900 font-display">Pooja Kashyap</h1>
            <p className="text-xs text-gray-500">pooja.kashyap@example.com • +91 98765 43210</p>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <Link
            to="/wishlist"
            className="px-4 py-2 bg-white border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 flex items-center gap-1.5 hover:bg-gray-50"
          >
            <Heart className="w-4 h-4 text-brand-orange" />
            <span>Wishlist ({wishlistCount})</span>
          </Link>
          <button
            onClick={() => handleAction('Account Sign Out')}
            className="px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-xl border border-red-200 cursor-pointer"
          >
            Sign Out
          </button>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex border-b border-gray-200 gap-4 text-xs sm:text-sm font-bold">
        <button
          onClick={() => setActiveTab('orders')}
          className={`pb-3 border-b-2 cursor-pointer transition-colors ${
            activeTab === 'orders' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          My Orders & Tracking ({mockPastOrders.length})
        </button>
        <button
          onClick={() => setActiveTab('profile')}
          className={`pb-3 border-b-2 cursor-pointer transition-colors ${
            activeTab === 'profile' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          Profile Details
        </button>
        <button
          onClick={() => setActiveTab('addresses')}
          className={`pb-3 border-b-2 cursor-pointer transition-colors ${
            activeTab === 'addresses' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          Saved Delivery Addresses
        </button>
      </div>

      {/* Tab Contents */}
      {activeTab === 'orders' && (
        <div className="space-y-4">
          {mockPastOrders.map(order => (
            <div key={order.id} className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100 text-xs">
                <div>
                  <span className="font-bold text-gray-900 font-mono">{order.id}</span>
                  <span className="text-gray-400 mx-2">•</span>
                  <span className="text-gray-500">Ordered on {order.date}</span>
                </div>
                <div className="flex items-center gap-3">
                  <span className="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 font-bold text-[10px]">
                    ✓ {order.status}
                  </span>
                  <span className="font-extrabold text-sm text-gray-900">Total: ₹{order.total}</span>
                </div>
              </div>

              <div className="divide-y divide-gray-50">
                {order.items.map((item, idx) => (
                  <div key={idx} className="py-2.5 flex items-center justify-between text-xs">
                    <div>
                      <p className="font-bold text-gray-800">{item.name}</p>
                      <p className="text-[11px] text-gray-500">By {item.seller} • Qty: {item.qty}</p>
                    </div>
                    <span className="font-semibold text-gray-900">₹{item.price}</span>
                  </div>
                ))}
              </div>

              <div className="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <span className="text-gray-500 font-mono text-[11px]">
                  Carrier: India Post Registered Express ({order.trackingNo})
                </span>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => handleAction(`Track Order ${order.id}`)}
                    className="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold rounded-lg border border-gray-200 cursor-pointer"
                  >
                    Track Shipment
                  </button>
                  <button
                    onClick={() => handleAction(`Download Invoice for ${order.id}`)}
                    className="px-3 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white font-semibold rounded-lg cursor-pointer"
                  >
                    Tax Invoice
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {activeTab === 'profile' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 max-w-2xl space-y-4 text-xs">
          <h2 className="font-bold text-base text-gray-900 font-display">Personal Information</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Full Name</label>
              <input type="text" defaultValue="Pooja Kashyap" className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg" />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Email Address</label>
              <input type="email" defaultValue="pooja.kashyap@example.com" className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg" />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Mobile Number</label>
              <input type="tel" defaultValue="+91 98765 43210" className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg" />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Preferred Language</label>
              <select defaultValue="en" className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg">
                <option value="en">English</option>
                <option value="hi">हिन्दी (Hindi)</option>
                <option value="mr">मराठी (Marathi)</option>
              </select>
            </div>
          </div>
          <div className="pt-4">
            <button
              onClick={() => handleAction('Save Profile Updates')}
              className="px-6 py-2.5 bg-brand-green text-white font-semibold rounded-lg cursor-pointer"
            >
              Save Profile Changes (Demo)
            </button>
          </div>
        </div>
      )}

      {activeTab === 'addresses' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="bg-white rounded-2xl border-2 border-brand-green shadow-xs p-6 space-y-3 text-xs relative">
            <span className="absolute top-4 right-4 px-2 py-0.5 bg-emerald-100 text-brand-green font-bold rounded text-[10px]">
              Default
            </span>
            <div className="flex items-center gap-2 font-bold text-sm text-gray-900">
              <MapPin className="w-4 h-4 text-brand-orange" />
              <span>Home (Mumbai)</span>
            </div>
            <p className="text-gray-600 leading-relaxed">
              Flat 402, Green Orchid Apartments, Fort Road, Mumbai, Maharashtra - 400001
            </p>
            <p className="text-gray-500">Contact: +91 98765 43210</p>
            <div className="pt-2 flex gap-2">
              <button
                onClick={() => handleAction('Edit Address')}
                className="text-xs text-brand-green font-semibold hover:underline"
              >
                Edit
              </button>
            </div>
          </div>

          <div className="bg-white rounded-2xl border border-gray-200 border-dashed p-6 flex flex-col items-center justify-center text-center space-y-2">
            <button
              onClick={() => handleAction('Add New Delivery Address')}
              className="px-4 py-2 bg-brand-cream hover:bg-gray-100 text-brand-green text-xs font-bold rounded-xl border border-emerald-200"
            >
              + Add New Address
            </button>
            <p className="text-[11px] text-gray-400">Save office, family, or village ancestral delivery points.</p>
          </div>
        </div>
      )}

    </div>
  );
};
