import React, { useState } from 'react';
import {
  Sliders,
  Smartphone,
  Monitor,
  RotateCcw,
  Save,
  CheckCircle2,
  Eye,
  Info,
  Layout as LayoutIcon,
  Plus,
  Trash2,
  X
} from 'lucide-react';
import { useSiteConfig } from '../../context/SiteConfigContext';
import { useDemoModal } from '../../context/DemoModalContext';
import { Header } from '../../components/layout/Header';
import { AnnouncementBar } from '../../components/layout/AnnouncementBar';
import { Footer } from '../../components/layout/Footer';

export const SiteCustomization: React.FC = () => {
  const { headerConfig, setHeaderConfig, footerConfig, setFooterConfig, resetConfigs } = useSiteConfig();
  const { showDemoNotice } = useDemoModal();

  const [activeTab, setActiveTab] = useState<'header' | 'footer'>('header');
  const [previewDevice, setPreviewDevice] = useState<'desktop' | 'mobile'>('desktop');
  const [showLivePreviewModal, setShowLivePreviewModal] = useState<boolean>(false);

  const handleSaveChanges = () => {
    // Section 6 requirement: Clicking Save Changes must display the demo popup!
    showDemoNotice(
      'Save Header / Footer Customization',
      'This feature is currently available for preview only. Backend functionality will be implemented in a future phase. Your edits are currently active in temporary local memory.'
    );
  };

  const handleReset = () => {
    resetConfigs();
    showDemoNotice('Reset Defaults', 'Configuration reset to default GanvGaliHut settings.');
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between gap-4 text-xs text-amber-900">
        <div className="flex items-center gap-3">
          <Info className="w-5 h-5 text-brand-orange shrink-0" />
          <div>
            <span className="font-bold">CMS Header & Footer Preview Studio:</span> Real-time reactive editing of announcement text, menus, branding, and contact parameters. Changes persist in local frontend state during this session.
          </div>
        </div>
        <span className="px-2.5 py-1 bg-amber-200/80 font-bold rounded-lg shrink-0">Demo Configuration</span>
      </div>

      {/* Header and Controls */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
            Header & Footer Customization Studio
          </h1>
          <p className="text-xs text-gray-500 mt-1">
            Configure site chrome, navigation taxonomy, announcements, and policy links.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={handleReset}
            className="px-3.5 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold rounded-xl flex items-center gap-1.5 cursor-pointer"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>Reset Defaults</span>
          </button>

          <button
            onClick={() => setShowLivePreviewModal(true)}
            className="px-4 py-2 bg-emerald-50 text-brand-green border border-emerald-300 hover:bg-emerald-100 text-xs font-semibold rounded-xl flex items-center gap-1.5 cursor-pointer"
          >
            <Eye className="w-3.5 h-3.5" />
            <span>Full Preview</span>
          </button>

          <button
            onClick={handleSaveChanges}
            className="px-5 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Save className="w-3.5 h-3.5" />
            <span>Save Changes (Demo)</span>
          </button>
        </div>
      </div>

      {/* Main Studio: Form (Left) + Device Preview (Right) */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {/* Editor Controls Pane */}
        <div className="lg:col-span-6 space-y-6">
          {/* Tab Selector */}
          <div className="flex bg-gray-100 p-1 rounded-xl text-xs font-bold">
            <button
              onClick={() => setActiveTab('header')}
              className={`flex-1 py-2 rounded-lg transition-all ${
                activeTab === 'header' ? 'bg-white text-brand-green shadow-xs' : 'text-gray-600 hover:text-gray-900'
              }`}
            >
              Header Settings
            </button>
            <button
              onClick={() => setActiveTab('footer')}
              className={`flex-1 py-2 rounded-lg transition-all ${
                activeTab === 'footer' ? 'bg-white text-brand-green shadow-xs' : 'text-gray-600 hover:text-gray-900'
              }`}
            >
              Footer Settings
            </button>
          </div>

          {/* HEADER SETTINGS TAB */}
          {activeTab === 'header' && (
            <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-5 text-xs">
              <h2 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2">
                Brand & Announcement Options
              </h2>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Brand Name</label>
                <input
                  type="text"
                  value={headerConfig.brandName}
                  onChange={(e) => setHeaderConfig({ ...headerConfig, brandName: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Tagline</label>
                <input
                  type="text"
                  value={headerConfig.tagline}
                  onChange={(e) => setHeaderConfig({ ...headerConfig, tagline: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                />
              </div>

              {/* Announcement Bar Toggle & Text */}
              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                <div className="flex items-center justify-between">
                  <span className="font-bold text-gray-800">Show Announcement Bar</span>
                  <input
                    type="checkbox"
                    checked={headerConfig.showAnnouncement}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, showAnnouncement: e.target.checked })}
                    className="w-4 h-4 text-brand-green rounded"
                  />
                </div>
                {headerConfig.showAnnouncement && (
                  <div>
                    <label className="block text-[11px] font-semibold text-gray-600 mb-1">Announcement Message</label>
                    <textarea
                      rows={2}
                      value={headerConfig.announcementText}
                      onChange={(e) => setHeaderConfig({ ...headerConfig, announcementText: e.target.value })}
                      className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs"
                    />
                  </div>
                )}
              </div>

              {/* Toggles */}
              <div className="space-y-3 pt-2">
                <h3 className="font-bold text-gray-900">Visibility Controls</h3>
                
                <label className="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer">
                  <span>Show Delivery Location Selector</span>
                  <input
                    type="checkbox"
                    checked={headerConfig.showLocationSelector}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, showLocationSelector: e.target.checked })}
                    className="w-4 h-4 text-brand-green rounded"
                  />
                </label>

                <label className="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer">
                  <span>Show Search Bar with Suggestions</span>
                  <input
                    type="checkbox"
                    checked={headerConfig.showSearch}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, showSearch: e.target.checked })}
                    className="w-4 h-4 text-brand-green rounded"
                  />
                </label>

                <label className="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer">
                  <span>Show Account, Wishlist & Cart Icons</span>
                  <input
                    type="checkbox"
                    checked={headerConfig.showIcons}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, showIcons: e.target.checked })}
                    className="w-4 h-4 text-brand-green rounded"
                  />
                </label>
              </div>

              {/* Call to Action configuration */}
              <div className="grid grid-cols-2 gap-3 pt-2">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">CTA Button Label</label>
                  <input
                    type="text"
                    value={headerConfig.ctaLabel}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, ctaLabel: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">CTA Target Destination</label>
                  <input
                    type="text"
                    value={headerConfig.ctaHref}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, ctaHref: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                  />
                </div>
              </div>
            </div>
          )}

          {/* FOOTER SETTINGS TAB */}
          {activeTab === 'footer' && (
            <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-5 text-xs">
              <h2 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2">
                Footer Content & Social Channels
              </h2>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Footer Brand Mission / Description</label>
                <textarea
                  rows={3}
                  value={footerConfig.description}
                  onChange={(e) => setFooterConfig({ ...footerConfig, description: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Support Phone</label>
                  <input
                    type="text"
                    value={footerConfig.contactPhone}
                    onChange={(e) => setFooterConfig({ ...footerConfig, contactPhone: e.target.value })}
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Support Email</label>
                  <input
                    type="text"
                    value={footerConfig.contactEmail}
                    onChange={(e) => setFooterConfig({ ...footerConfig, contactEmail: e.target.value })}
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Office / Hub Address</label>
                <input
                  type="text"
                  value={footerConfig.contactAddress}
                  onChange={(e) => setFooterConfig({ ...footerConfig, contactAddress: e.target.value })}
                  className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Copyright Disclaimer Text</label>
                <input
                  type="text"
                  value={footerConfig.copyrightText}
                  onChange={(e) => setFooterConfig({ ...footerConfig, copyrightText: e.target.value })}
                  className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                />
              </div>

              {/* Newsletter Toggle */}
              <label className="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer">
                <span>Display "Gaon Ki Baat" Newsletter Subscription Section</span>
                <input
                  type="checkbox"
                  checked={footerConfig.showNewsletter}
                  onChange={(e) => setFooterConfig({ ...footerConfig, showNewsletter: e.target.checked })}
                  className="w-4 h-4 text-brand-green rounded"
                />
              </label>
            </div>
          )}
        </div>

        {/* Live Device Preview Pane (Right) */}
        <div className="lg:col-span-6 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="font-bold text-sm text-gray-900 font-display flex items-center gap-2">
              <Eye className="w-4 h-4 text-brand-green" />
              <span>Real-Time Reactive Preview</span>
            </h2>

            <div className="flex items-center bg-gray-100 p-1 rounded-lg text-xs">
              <button
                onClick={() => setPreviewDevice('desktop')}
                className={`p-1.5 rounded flex items-center gap-1 cursor-pointer ${
                  previewDevice === 'desktop' ? 'bg-white shadow-xs font-bold text-brand-green' : 'text-gray-500'
                }`}
                title="Desktop View"
              >
                <Monitor className="w-3.5 h-3.5" />
                <span className="hidden sm:inline">Desktop</span>
              </button>
              <button
                onClick={() => setPreviewDevice('mobile')}
                className={`p-1.5 rounded flex items-center gap-1 cursor-pointer ${
                  previewDevice === 'mobile' ? 'bg-white shadow-xs font-bold text-brand-green' : 'text-gray-500'
                }`}
                title="Mobile View"
              >
                <Smartphone className="w-3.5 h-3.5" />
                <span className="hidden sm:inline">Mobile</span>
              </button>
            </div>
          </div>

          <div
            className={`border border-gray-300 rounded-2xl bg-white shadow-lg overflow-hidden transition-all mx-auto ${
              previewDevice === 'mobile' ? 'max-w-xs' : 'w-full'
            }`}
          >
            <div className="bg-gray-800 text-white text-[10px] px-4 py-1.5 flex items-center justify-between">
              <span>Preview Screen: {previewDevice.toUpperCase()}</span>
              <span className="text-emerald-400">● Live Preview Active</span>
            </div>

            {/* Embedded Live Preview */}
            <div className="overflow-y-auto max-h-[520px] divide-y divide-gray-100">
              <AnnouncementBar />
              <Header />
              <div className="p-8 text-center text-xs text-gray-400 italic bg-gray-50">
                [ Marketplace Content Area ]
              </div>
              <Footer />
            </div>
          </div>
        </div>

      </div>

      {/* Full Screen Live Preview Modal */}
      {showLivePreviewModal && (
        <div className="fixed inset-0 z-50 bg-black/80 flex flex-col p-4 sm:p-8">
          <div className="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col flex-1 max-w-6xl mx-auto w-full">
            <div className="bg-gray-900 text-white p-4 flex items-center justify-between">
              <span className="font-bold text-sm">Full Marketplace Chrome Preview</span>
              <button
                onClick={() => setShowLivePreviewModal(false)}
                className="p-1 rounded-full text-gray-400 hover:text-white"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
            <div className="flex-1 overflow-y-auto">
              <AnnouncementBar />
              <Header />
              <div className="p-16 text-center text-sm text-gray-500">
                Previewing customized header and footer in relation to full viewport width.
              </div>
              <Footer />
            </div>
          </div>
        </div>
      )}

    </div>
  );
};
