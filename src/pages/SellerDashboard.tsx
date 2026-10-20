import React, { useState } from 'react';
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
  X
} from 'lucide-react';
import { mockProducts } from '../data/mockProducts';

export const SellerDashboard: React.FC = () => {
  const [notification, setNotification] = useState<string | null>(null);

  const notify = (msg: string) => {
    setNotification(msg);
    setTimeout(() => setNotification(null), 3500);
  };

  const sampleOrders = [
    { id: 'ORD-9821', item: 'Forged Carbon Steel Hansiya Sickle (Hardwood Handle)', customer: 'Vikram Joshi (Bengaluru)', date: 'Today, 10:30 AM', amount: 360, status: 'Ready for Dispatch' },
    { id: 'ORD-9818', item: 'Hand-Embroidered Jute Market Bag (Pack of 2)', customer: 'Pooja Kashyap (Mumbai)', date: 'Yesterday', amount: 520, status: 'In Transit (India Post)' },
    { id: 'ORD-9804', item: 'Handmade Wooden Village Puppet Toy Set', customer: 'Deepak Sharma (Delhi)', date: 'Oct 18, 2026', amount: 450, status: 'Delivered' },
  ];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Toast Notification */}
      {notification && (
        <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-3 text-xs text-emerald-900 shadow-sm animate-in fade-in duration-200">
          <div className="flex items-center gap-2 font-semibold">
            <CheckCircle2 className="w-5 h-5 text-brand-green shrink-0" />
            <span>{notification}</span>
          </div>
          <button
            onClick={() => setNotification(null)}
            className="p-1 hover:bg-emerald-100 rounded-lg text-emerald-700"
          >
            <X className="w-4 h-4" />
          </button>
        </div>
      )}

      {/* Official Status Header */}
      <div className="p-4 bg-white border border-gray-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs shadow-xs">
        <div className="flex items-center gap-3">
          <ShieldCheck className="w-5 h-5 text-brand-green shrink-0" />
          <div>
            <span className="font-bold text-gray-900">Artisan & Foundry Partner Operations Portal:</span>
            <span className="text-gray-600 ml-1">
              Live inventory synchronization, postal dispatch tracking, and direct bank payout settlement.
            </span>
          </div>
        </div>
        <span className="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg shrink-0">
          KYC Verified Partner
        </span>
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
            onClick={() => notify('Payout request of ₹14,500 submitted to accounts desk. Transfer via NEFT scheduled within 24 hours.')}
            className="px-4 py-2.5 bg-white border border-gray-300 hover:border-brand-green text-gray-800 text-xs font-semibold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Banknote className="w-4 h-4 text-brand-green" />
            <span>Request Payout</span>
          </button>

          <button
            onClick={() => notify('Product creation modal opened. Ready to list rural inventory.')}
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
          <p className="text-[11px] text-gray-500 font-medium">Scheduled for direct account credit</p>
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
            onClick={() => notify('Displaying all historical order manifests.')}
            className="text-xs text-brand-green font-semibold hover:underline cursor-pointer"
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
                      onClick={() => {
                        notify(`Generated India Post dispatch manifest for ${ord.id}. Ready for printer.`);
                        setTimeout(() => window.print(), 500);
                      }}
                      className="text-xs font-semibold text-brand-green hover:underline cursor-pointer flex items-center gap-1 ml-auto"
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

      {/* Listed Products Status */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
        <div className="flex items-center justify-between border-b border-gray-100 pb-4">
          <h2 className="font-bold text-base text-gray-900 font-display">Active Product Listings (Saraswati SHG)</h2>
          <button
            onClick={() => notify('Stock levels updated and synchronized with Cloudflare KV edge store.')}
            className="text-xs text-brand-green font-semibold hover:underline cursor-pointer"
          >
            Bulk Stock Sync
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
                onClick={() => notify(`Opening product editor for ${p.name}`)}
                className="text-xs text-gray-500 hover:text-brand-green p-1 cursor-pointer"
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
