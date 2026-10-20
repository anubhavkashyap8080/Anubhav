import React from 'react';
import { Link } from 'react-router-dom';
import {
  TrendingUp,
  Users,
  Package,
  Layers,
  Settings,
  ShieldCheck,
  FileText,
  Globe,
  Sliders,
  Store,
  ChevronRight,
  Sparkles,
  Info
} from 'lucide-react';
import { useDemoModal } from '../../context/DemoModalContext';

export const AdminDashboard: React.FC = () => {
  const { showDemoNotice } = useDemoModal();

  const handleAction = (name: string) => {
    showDemoNotice(
      name,
      'This feature is currently available for preview only. Backend functionality will be implemented in a future phase.'
    );
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between gap-4 text-xs text-amber-900">
        <div className="flex items-center gap-3">
          <Info className="w-5 h-5 text-brand-orange shrink-0" />
          <div>
            <span className="font-bold">Admin Central Demonstration:</span> This dashboard aggregates marketplace metrics, artisan onboarding queues, and system configuration modules.
          </div>
        </div>
        <span className="px-2.5 py-1 bg-amber-200/80 font-bold rounded-lg shrink-0">Admin Preview</span>
      </div>

      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
            GanvGaliHut Central Operations Portal
          </h1>
          <p className="text-xs text-gray-500 mt-1">
            Marketplace metrics, artisan clusters, editorial controls, and technical governance.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => handleAction('Export Financial Ledger')}
            className="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold rounded-xl cursor-pointer"
          >
            Export Ledger (Demo)
          </button>
          <button
            onClick={() => handleAction('System Health Diagnostics')}
            className="px-4 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer"
          >
            Run System Health
          </button>
        </div>
      </div>

      {/* Primary Metrics Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Gross Merchandise (GMV)</span>
            <span className="p-2 bg-emerald-50 text-brand-green rounded-lg">
              <TrendingUp className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">₹1,24,500</div>
          <p className="text-[11px] text-emerald-600 font-medium">↑ 24% month-over-month</p>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Active Rural Artisans</span>
            <span className="p-2 bg-orange-50 text-brand-orange rounded-lg">
              <Users className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">184 Creators</div>
          <p className="text-[11px] text-gray-500 font-medium">Across 12 Indian states</p>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>Orders Dispatched</span>
            <span className="p-2 bg-blue-50 text-blue-600 rounded-lg">
              <Package className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">492 Shipments</div>
          <p className="text-[11px] text-gray-500 font-medium">Average delivery: 5.2 days</p>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-1">
          <div className="flex justify-between items-center text-xs text-gray-500">
            <span>SHGs Empowered</span>
            <span className="p-2 bg-purple-50 text-purple-600 rounded-lg">
              <Sparkles className="w-4 h-4" />
            </span>
          </div>
          <div className="text-2xl font-extrabold text-gray-900 font-display">28 Groups</div>
          <p className="text-[11px] text-purple-600 font-medium">100% direct bank payout</p>
        </div>
      </div>

      {/* Admin Modules Hub (Quick Access Cards) */}
      <div className="space-y-4">
        <h2 className="text-lg font-bold text-gray-900 font-display">Management Modules</h2>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Link
            to="/admin/site-customization"
            className="group bg-white p-6 rounded-2xl border border-gray-100 shadow-xs hover:shadow-lg hover:border-emerald-200 transition-all space-y-3 flex flex-col justify-between"
          >
            <div>
              <div className="w-12 h-12 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                <Sliders className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-base text-gray-900 font-display">Site Customization</h3>
              <p className="text-xs text-gray-600 leading-relaxed mt-1">
                Configure announcement banner, header navigation, location selectors, and footer columns.
              </p>
            </div>
            <span className="text-xs font-bold text-brand-green group-hover:translate-x-1 transition-transform flex items-center gap-1 pt-2">
              <span>Open Customizer</span>
              <ChevronRight className="w-3.5 h-3.5" />
            </span>
          </Link>

          <Link
            to="/admin/blog"
            className="group bg-white p-6 rounded-2xl border border-gray-100 shadow-xs hover:shadow-lg hover:border-emerald-200 transition-all space-y-3 flex flex-col justify-between"
          >
            <div>
              <div className="w-12 h-12 rounded-xl bg-orange-50 text-brand-orange flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                <FileText className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-base text-gray-900 font-display">Village Journal CMS</h3>
              <p className="text-xs text-gray-600 leading-relaxed mt-1">
                Author new village stories, manage drafts, edit categories, and configure Open Graph meta tags.
              </p>
            </div>
            <span className="text-xs font-bold text-brand-orange group-hover:translate-x-1 transition-transform flex items-center gap-1 pt-2">
              <span>Manage Blog</span>
              <ChevronRight className="w-3.5 h-3.5" />
            </span>
          </Link>

          <Link
            to="/admin/seo-settings"
            className="group bg-white p-6 rounded-2xl border border-gray-100 shadow-xs hover:shadow-lg hover:border-emerald-200 transition-all space-y-3 flex flex-col justify-between"
          >
            <div>
              <div className="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                <Globe className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-base text-gray-900 font-display">SEO & Sitemaps</h3>
              <p className="text-xs text-gray-600 leading-relaxed mt-1">
                Manage canonical domains, XML sitemap generation previews, robots.txt, and Schema.org markup.
              </p>
            </div>
            <span className="text-xs font-bold text-blue-600 group-hover:translate-x-1 transition-transform flex items-center gap-1 pt-2">
              <span>SEO Settings</span>
              <ChevronRight className="w-3.5 h-3.5" />
            </span>
          </Link>

          <Link
            to="/admin/security-settings"
            className="group bg-white p-6 rounded-2xl border border-gray-100 shadow-xs hover:shadow-lg hover:border-emerald-200 transition-all space-y-3 flex flex-col justify-between"
          >
            <div>
              <div className="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                <ShieldCheck className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-base text-gray-900 font-display">Security & Privacy</h3>
              <p className="text-xs text-gray-600 leading-relaxed mt-1">
                Audit HTTPS headers, CSP directives, DPDP Act data handling, and server hardening guidelines.
              </p>
            </div>
            <span className="text-xs font-bold text-purple-600 group-hover:translate-x-1 transition-transform flex items-center gap-1 pt-2">
              <span>Security Controls</span>
              <ChevronRight className="w-3.5 h-3.5" />
            </span>
          </Link>
        </div>
      </div>

      {/* Artisan Verification Queue */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div className="p-6 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 className="font-bold text-base text-gray-900 font-display">
              Artisan & SHG Verification Queue
            </h2>
            <p className="text-xs text-gray-500">New seller registrations pending village Mitra verification</p>
          </div>
          <button
            onClick={() => handleAction('Review All Pending Applications')}
            className="text-xs text-brand-green font-semibold hover:underline"
          >
            View All Applications &rarr;
          </button>
        </div>

        <div className="overflow-x-auto text-xs">
          <table className="w-full text-left">
            <thead className="bg-gray-50 text-gray-500 uppercase tracking-wider font-semibold">
              <tr>
                <th className="p-4">Applicant</th>
                <th className="p-4">Type</th>
                <th className="p-4">Location</th>
                <th className="p-4">Craft</th>
                <th className="p-4">Submitted</th>
                <th className="p-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              <tr className="hover:bg-gray-50/60">
                <td className="p-4 font-bold text-gray-900">Kalyani Mahila Bachat Gat (15 women)</td>
                <td className="p-4"><span className="px-2 py-0.5 rounded bg-emerald-50 text-brand-green font-semibold">SHG</span></td>
                <td className="p-4 text-gray-600">Madhupur, Deoghar, JH</td>
                <td className="p-4 text-gray-700">Lac Bangles & Handloom</td>
                <td className="p-4 text-gray-500">Today, 09:15 AM</td>
                <td className="p-4 text-right space-x-2">
                  <button
                    onClick={() => handleAction('Approve Seller Application')}
                    className="px-2.5 py-1 bg-brand-green text-white rounded font-semibold text-xs"
                  >
                    Verify & Onboard
                  </button>
                </td>
              </tr>
              <tr className="hover:bg-gray-50/60">
                <td className="p-4 font-bold text-gray-900">Babu Ram Cane & Bamboo Works</td>
                <td className="p-4"><span className="px-2 py-0.5 rounded bg-orange-50 text-brand-orange font-semibold">Artisan</span></td>
                <td className="p-4 text-gray-600">Kokrajhar, Assam</td>
                <td className="p-4 text-gray-700">Bamboo Baskets & Furniture</td>
                <td className="p-4 text-gray-500">Yesterday</td>
                <td className="p-4 text-right space-x-2">
                  <button
                    onClick={() => handleAction('Approve Seller Application')}
                    className="px-2.5 py-1 bg-brand-green text-white rounded font-semibold text-xs"
                  >
                    Verify & Onboard
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  );
};
