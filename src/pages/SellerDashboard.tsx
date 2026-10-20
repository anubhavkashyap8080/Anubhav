import React from 'react';
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
  Info
} from 'lucide-react';
import { useDemoModal } from '../context/DemoModalContext';
import { mockProducts } from '../data/mockProducts';

export const SellerDashboard: React.FC = () => {
  const { showDemoNotice } = useDemoModal();

  const handleAction = (action: string) => {
    showDemoNotice(
      action,
      'This feature is currently available for preview only. Backend functionality will be implemented in a future phase.'
    );
  };

  const sampleOrders = [
    { id: 'ORD-9821', item: 'Handmade Moong Dal Papad (Heeng Jeera)', customer: 'Vikram Joshi (Bengaluru)', date: 'Today, 10:30 AM', amount: 360, status: 'Ready for Dispatch' },
    { id: 'ORD-9818', item: 'Banarasi Stuffed Red Chilli Pickle 500g', customer: 'Pooja Kashyap (Mumbai)', date: 'Yesterday', amount: 520, status: 'In Transit (India Post)' },
    { id: 'ORD-9804', item: 'Desi Masala Mathri 450g', customer: 'Deepak Sharma (Delhi)', date: 'Oct 18, 2026', amount: 195, status: 'Delivered' },
  ];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between gap-4 text-xs text-amber-900">
        <div className="flex items-center gap-3">
          <Info className="w-5 h-5 text-brand-orange shrink-0" />
          <div>
            <span className="font-bold">Seller Dashboard Demonstration:</span> Simulated view of artisan sales, order tracking, and bank settlements. Real-time payouts and order management will launch with the Phase 2 backend.
          </div>
        </div>
        <span className="px-2.5 py-1 bg-amber-200/80 font-bold rounded-lg shrink-0">Demo Portal</span>
      </div>

      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
          <div className="flex items-center gap-2">
            <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
              Artisan Hub: Saraswati Mahila SHG
            </h1>
            <span className="px-2 py-0.5 rounded bg-emerald-100 text-brand-green font-bold text-xs">
              Verified Seller
            </span>
          </div>
          <p className="text-xs text-gray-500 mt-1">Village Dewa, Barabanki, UP • 18 Rural Women Artisans</p>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={() => handleAction('Request Bank Payout')}
            className="px-4 py-2.5 bg-white border border-gray-300 hover:border-brand-green text-gray-800 text-xs font-semibold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Banknote className="w-4 h-4 text-brand-green" />
            <span>Request Payout</span>
          </button>

          <button
            onClick={() => handleAction('Add New Product')}
            className="px-4 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-xl shadow-sm flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Add New Product</span>
          </button>
        </div>
      </div>

      {/* Metric Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Monthly Revenue</span>
            <span className="p-2 bg-emerald-50 text-brand-green rounded-lg">
              <TrendingUp className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">₹38,920</div>
          <p className="text-[11px] text-emerald-600 font-medium">↑ 18% from last month</p>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Orders Dispatched</span>
            <span className="p-2 bg-blue-50 text-blue-600 rounded-lg">
              <Package className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">42 Orders</div>
          <p className="text-[11px] text-gray-500 font-medium">100% on-time postal dispatch</p>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Direct Payout Due</span>
            <span className="p-2 bg-amber-50 text-brand-orange rounded-lg">
              <Banknote className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-brand-green font-display">₹14,500</div>
          <p className="text-[11px] text-gray-500 font-medium">Scheduled for Tuesday credit</p>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Customer Rating</span>
            <span className="p-2 bg-yellow-50 text-amber-500 rounded-lg">
              ★
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">4.9 / 5.0</div>
          <p className="text-[11px] text-gray-500 font-medium">184 Verified reviews</p>
        </div>
      </div>

      {/* Orders Table */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div className="p-6 border-b border-gray-100 flex items-center justify-between">
          <h2 className="font-bold text-base text-gray-900 font-display">Recent Village Orders</h2>
          <button
            onClick={() => handleAction('View All Orders')}
            className="text-xs text-brand-green font-semibold hover:underline"
          >
            View All Orders &rarr;
          </button>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-gray-50 text-gray-500 border-b border-gray-100 uppercase tracking-wider font-semibold">
              <tr>
                <th className="p-4">Order ID</th>
                <th className="p-4">Product</th>
                <th className="p-4">Customer</th>
                <th className="p-4">Date</th>
                <th className="p-4">Amount</th>
                <th className="p-4">Status</th>
                <th className="p-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {sampleOrders.map(ord => (
                <tr key={ord.id} className="hover:bg-gray-50/60">
                  <td className="p-4 font-mono font-bold text-gray-900">{ord.id}</td>
                  <td className="p-4 font-medium text-gray-800">{ord.item}</td>
                  <td className="p-4 text-gray-600">{ord.customer}</td>
                  <td className="p-4 text-gray-500">{ord.date}</td>
                  <td className="p-4 font-bold text-gray-900">₹{ord.amount}</td>
                  <td className="p-4">
                    <span className="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 font-semibold text-[10px]">
                      {ord.status}
                    </span>
                  </td>
                  <td className="p-4 text-right">
                    <button
                      onClick={() => handleAction(`Manage Order ${ord.id}`)}
                      className="text-xs font-semibold text-brand-green hover:underline cursor-pointer"
                    >
                      Print Label
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Listed Products Status */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
        <div className="flex items-center justify-between border-b border-gray-100 pb-4">
          <h2 className="font-bold text-base text-gray-900 font-display">Active Product Listings (Saraswati SHG)</h2>
          <button
            onClick={() => handleAction('Bulk Stock Update')}
            className="text-xs text-brand-green font-semibold hover:underline"
          >
            Bulk Stock Update (Demo)
          </button>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {mockProducts.filter(p => p.seller.id === 'seller-1').slice(0, 3).map(p => (
            <div key={p.id} className="p-4 rounded-xl border border-gray-100 flex items-center justify-between gap-3">
              <img src={p.image} alt={p.name} className="w-12 h-12 rounded-lg object-cover" />
              <div className="flex-1 min-w-0 text-xs">
                <p className="font-bold text-gray-900 truncate">{p.name}</p>
                <p className="text-gray-500">₹{p.price} • Stock: <span className="text-emerald-700 font-bold">{p.stockStatus}</span></p>
              </div>
              <button
                onClick={() => handleAction(`Edit Product: ${p.name}`)}
                className="text-xs text-gray-500 hover:text-brand-green p-1"
                title="Edit"
              >
                Edit
              </button>
            </div>
          ))}
        </div>
      </div>

    </div>
  );
};
