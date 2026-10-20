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
  X,
  Upload,
  Globe,
  Share2,
  Code2,
  Sparkles,
  Image as ImageIcon
} from 'lucide-react';
import { useSiteConfig } from '../../context/SiteConfigContext';
import { Header } from '../../components/layout/Header';
import { AnnouncementBar } from '../../components/layout/AnnouncementBar';
import { Footer } from '../../components/layout/Footer';

export const SiteCustomization: React.FC = () => {
  const {
    headerConfig,
    setHeaderConfig,
    footerConfig,
    setFooterConfig,
    updateSocialLinks,
    resetConfigs
  } = useSiteConfig();

  const [activeTab, setActiveTab] = useState<'header' | 'footer' | 'code'>('header');
  const [previewDevice, setPreviewDevice] = useState<'desktop' | 'mobile'>('desktop');
  const [showLivePreviewModal, setShowLivePreviewModal] = useState<boolean>(false);
  const [saveToast, setSaveToast] = useState<boolean>(false);

  const handleSaveChanges = () => {
    setSaveToast(true);
    setTimeout(() => setSaveToast(false), 3500);
  };

  const handleReset = () => {
    resetConfigs();
    setSaveToast(true);
    setTimeout(() => setSaveToast(false), 3500);
  };

  const handleLogoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        if (typeof reader.result === 'string') {
          setHeaderConfig(prev => ({ ...prev, logoUrl: reader.result as string }));
          setSaveToast(true);
          setTimeout(() => setSaveToast(false), 3000);
        }
      };
      reader.readAsDataURL(file);
    }
  };

  const handleFaviconUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        if (typeof reader.result === 'string') {
          setHeaderConfig(prev => ({ ...prev, faviconUrl: reader.result as string }));
          setSaveToast(true);
          setTimeout(() => setSaveToast(false), 3000);
        }
      };
      reader.readAsDataURL(file);
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      {/* Floating Save Success Toast */}
      {saveToast && (
        <div className="fixed bottom-6 right-6 z-50 bg-emerald-800 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 animate-in fade-in slide-in-from-bottom duration-200 text-xs font-semibold">
          <CheckCircle2 className="w-5 h-5 text-emerald-300" />
          <span>Branding & Chrome settings successfully published to live storefront!</span>
        </div>
      )}

      {/* Production Top Status Header (Zero Demo Tags) */}
      <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs text-emerald-950 shadow-xs">
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
            <Sliders className="w-4 h-4" />
          </div>
          <div>
            <span className="font-bold text-sm block text-emerald-900">Site Chrome & Branding Studio</span>
            <p className="text-emerald-800 mt-0.5">Real-time configuration of Logo, Favicon, Menus, Social Channels, and Custom Scripts.</p>
          </div>
        </div>
        <span className="px-3 py-1 bg-emerald-200 text-emerald-900 font-bold rounded-full shrink-0 text-[11px] flex items-center gap-1.5">
          <span className="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
          <span>Live Edge Synchronized</span>
        </span>
      </div>

      {/* Header and Controls */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
            Header, Footer & Branding Customizer
          </h1>
          <p className="text-xs text-gray-500 mt-1">
            Update your corporate identity, official logo, favicon, category menus, social links, and custom tracking codes.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <button
            onClick={handleReset}
            className="px-3.5 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-xs"
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
            <span>Save & Publish Live</span>
          </button>
        </div>
      </div>

      {/* Main Studio: Form (Left) + Device Preview (Right) */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {/* Editor Controls Pane */}
        <div className="lg:col-span-6 space-y-6">
          {/* Tab Selector */}
          <div className="flex bg-gray-200/80 p-1 rounded-xl text-xs font-bold gap-1">
            <button
              onClick={() => setActiveTab('header')}
              className={`flex-1 py-2.5 rounded-lg transition-all ${
                activeTab === 'header' ? 'bg-white text-brand-green shadow-sm' : 'text-gray-700 hover:text-gray-900'
              }`}
            >
              Header, Logo & Favicon
            </button>
            <button
              onClick={() => setActiveTab('footer')}
              className={`flex-1 py-2.5 rounded-lg transition-all ${
                activeTab === 'footer' ? 'bg-white text-brand-green shadow-sm' : 'text-gray-700 hover:text-gray-900'
              }`}
            >
              Footer & Social Links
            </button>
            <button
              onClick={() => setActiveTab('code')}
              className={`flex-1 py-2.5 rounded-lg transition-all ${
                activeTab === 'code' ? 'bg-white text-brand-green shadow-sm' : 'text-gray-700 hover:text-gray-900'
              }`}
            >
              Custom Code / Scripts
            </button>
          </div>

          {/* TAB 1: HEADER & LOGO SETTINGS */}
          {activeTab === 'header' && (
            <div className="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-6 text-xs text-gray-800">
              <h2 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2 flex items-center gap-2">
                <ImageIcon className="w-4 h-4 text-brand-green" />
                <span>Logo & Favicon Identity</span>
              </h2>

              {/* 1. Logo Management */}
              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                <label className="block font-bold text-gray-900">Brand Logo Image</label>
                <div className="flex items-center gap-4">
                  <div className="w-24 h-16 rounded-xl bg-white border border-gray-300 p-2 flex items-center justify-center shadow-xs overflow-hidden">
                    <img
                      src={headerConfig.logoUrl || '/logo.png'}
                      alt="Logo Preview"
                      className="max-h-full max-w-full object-contain"
                    />
                  </div>
                  <div className="flex-1 space-y-2">
                    <div className="flex items-center gap-2">
                      <label className="px-3 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white rounded-lg text-xs font-semibold cursor-pointer shadow-xs flex items-center gap-1.5">
                        <Upload className="w-3.5 h-3.5" />
                        <span>Upload Logo File</span>
                        <input
                          type="file"
                          accept="image/*"
                          onChange={handleLogoUpload}
                          className="hidden"
                        />
                      </label>
                      <button
                        type="button"
                        onClick={() => setHeaderConfig({ ...headerConfig, logoUrl: '/logo.png' })}
                        className="px-3 py-1.5 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 rounded-lg text-xs font-medium"
                      >
                        Reset Default
                      </button>
                    </div>
                    <div>
                      <input
                        type="text"
                        placeholder="Or enter direct Logo Image URL..."
                        value={headerConfig.logoUrl}
                        onChange={(e) => setHeaderConfig({ ...headerConfig, logoUrl: e.target.value })}
                        className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs font-mono"
                      />
                    </div>
                  </div>
                </div>
              </div>

              {/* 2. Favicon Management */}
              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                <label className="block font-bold text-gray-900">Website Favicon (.ico / .png)</label>
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 rounded-xl bg-white border border-gray-300 p-2 flex items-center justify-center shadow-xs">
                    <img
                      src={headerConfig.faviconUrl || '/logo.png'}
                      alt="Favicon Preview"
                      className="w-6 h-6 object-contain"
                    />
                  </div>
                  <div className="flex-1 space-y-2">
                    <div className="flex items-center gap-2">
                      <label className="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold cursor-pointer shadow-xs flex items-center gap-1.5">
                        <Upload className="w-3.5 h-3.5" />
                        <span>Upload Favicon</span>
                        <input
                          type="file"
                          accept="image/png, image/x-icon, image/svg+xml"
                          onChange={handleFaviconUpload}
                          className="hidden"
                        />
                      </label>
                      <button
                        type="button"
                        onClick={() => setHeaderConfig({ ...headerConfig, faviconUrl: '/logo.png' })}
                        className="px-3 py-1.5 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 rounded-lg text-xs font-medium"
                      >
                        Reset Default
                      </button>
                    </div>
                    <input
                      type="text"
                      placeholder="Favicon Image URL..."
                      value={headerConfig.faviconUrl || ''}
                      onChange={(e) => setHeaderConfig({ ...headerConfig, faviconUrl: e.target.value })}
                      className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs font-mono"
                    />
                  </div>
                </div>
              </div>

              {/* 3. Top Announcement Bar */}
              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                <div className="flex items-center justify-between">
                  <div>
                    <span className="font-bold text-gray-900 block">Top Announcement Bar Ticker</span>
                    <span className="text-[11px] text-gray-500">Show promotion, festival offers or shipping notices at very top</span>
                  </div>
                  <input
                    type="checkbox"
                    checked={headerConfig.showAnnouncement}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, showAnnouncement: e.target.checked })}
                    className="w-4 h-4 text-brand-green rounded cursor-pointer"
                  />
                </div>
                {headerConfig.showAnnouncement && (
                  <div>
                    <label className="block text-[11px] font-semibold text-gray-700 mb-1">Announcement Message Text</label>
                    <textarea
                      rows={2}
                      value={headerConfig.announcementText}
                      onChange={(e) => setHeaderConfig({ ...headerConfig, announcementText: e.target.value })}
                      className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs"
                    />
                  </div>
                )}
              </div>

              {/* 4. Navigation Menu Items */}
              <div>
                <label className="block font-bold text-gray-900 mb-2">Category Navigation Items</label>
                <div className="space-y-2 max-h-56 overflow-y-auto pr-1">
                  {headerConfig.navItems.map((item, idx) => (
                    <div key={item.id || idx} className="flex items-center gap-2 p-2.5 bg-gray-50 rounded-xl border border-gray-200">
                      <input
                        type="checkbox"
                        checked={item.enabled}
                        onChange={(e) => {
                          const updated = [...headerConfig.navItems];
                          updated[idx] = { ...item, enabled: e.target.checked };
                          setHeaderConfig({ ...headerConfig, navItems: updated });
                        }}
                        className="rounded text-brand-green"
                      />
                      <input
                        type="text"
                        value={item.label}
                        onChange={(e) => {
                          const updated = [...headerConfig.navItems];
                          updated[idx] = { ...item, label: e.target.value };
                          setHeaderConfig({ ...headerConfig, navItems: updated });
                        }}
                        className="flex-1 p-1.5 border border-gray-300 rounded-lg bg-white text-xs font-medium"
                      />
                      <input
                        type="text"
                        value={item.href}
                        onChange={(e) => {
                          const updated = [...headerConfig.navItems];
                          updated[idx] = { ...item, href: e.target.value };
                          setHeaderConfig({ ...headerConfig, navItems: updated });
                        }}
                        className="w-36 p-1.5 border border-gray-300 rounded-lg bg-white text-xs font-mono"
                      />
                    </div>
                  ))}
                </div>
              </div>

              {/* 5. Header Visibility Toggles */}
              <div className="space-y-2.5 pt-2 border-t border-gray-100">
                <span className="font-bold text-gray-900 block">Header Elements Visibility</span>
                
                <label className="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                  <span>Show Delivery Location Selector</span>
                  <input
                    type="checkbox"
                    checked={headerConfig.showLocationSelector}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, showLocationSelector: e.target.checked })}
                    className="w-4 h-4 text-brand-green rounded"
                  />
                </label>

                <label className="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                  <span>Show Search Bar with Autocomplete Suggestions</span>
                  <input
                    type="checkbox"
                    checked={headerConfig.showSearch}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, showSearch: e.target.checked })}
                    className="w-4 h-4 text-brand-green rounded"
                  />
                </label>

                <label className="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                  <span>Show Account, Wishlist & Cart Icons</span>
                  <input
                    type="checkbox"
                    checked={headerConfig.showIcons}
                    onChange={(e) => setHeaderConfig({ ...headerConfig, showIcons: e.target.checked })}
                    className="w-4 h-4 text-brand-green rounded"
                  />
                </label>
              </div>
            </div>
          )}

          {/* TAB 2: FOOTER & SOCIAL LINKS */}
          {activeTab === 'footer' && (
            <div className="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-6 text-xs text-gray-800">
              <h2 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2 flex items-center gap-2">
                <Share2 className="w-4 h-4 text-brand-orange" />
                <span>Footer Content & Social Channels</span>
              </h2>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Company / Initiative Name</label>
                <input
                  type="text"
                  value={footerConfig.companyName}
                  onChange={(e) => setFooterConfig({ ...footerConfig, companyName: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-bold"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Footer Brand Mission / Description</label>
                <textarea
                  rows={3}
                  value={footerConfig.description}
                  onChange={(e) => setFooterConfig({ ...footerConfig, description: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg text-xs"
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Works / Factory Address</label>
                  <textarea
                    rows={2}
                    value={footerConfig.contactAddress}
                    onChange={(e) => setFooterConfig({ ...footerConfig, contactAddress: e.target.value })}
                    className="w-full p-2 bg-gray-50 border border-gray-300 rounded-lg text-xs"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Head Office Address</label>
                  <textarea
                    rows={2}
                    value={footerConfig.headOfficeAddress}
                    onChange={(e) => setFooterConfig({ ...footerConfig, headOfficeAddress: e.target.value })}
                    className="w-full p-2 bg-gray-50 border border-gray-300 rounded-lg text-xs"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Contact Phone Numbers</label>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                  <input
                    type="text"
                    value={footerConfig.contactPhone}
                    onChange={(e) => setFooterConfig({ ...footerConfig, contactPhone: e.target.value })}
                    className="p-2 border border-gray-300 rounded-lg text-xs font-mono"
                    placeholder="Primary"
                  />
                  <input
                    type="text"
                    value={footerConfig.altPhone1 || ''}
                    onChange={(e) => setFooterConfig({ ...footerConfig, altPhone1: e.target.value })}
                    className="p-2 border border-gray-300 rounded-lg text-xs font-mono"
                    placeholder="Alt Phone 1"
                  />
                  <input
                    type="text"
                    value={footerConfig.altPhone2 || ''}
                    onChange={(e) => setFooterConfig({ ...footerConfig, altPhone2: e.target.value })}
                    className="p-2 border border-gray-300 rounded-lg text-xs font-mono"
                    placeholder="Alt Phone 2"
                  />
                </div>
              </div>

              {/* Social Channels Manager */}
              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                <span className="font-bold text-gray-900 block border-b border-gray-200 pb-2">
                  Official Social Media Channels (with Toggle)
                </span>

                <div className="space-y-2.5">
                  <div className="flex items-center gap-2">
                    <input
                      type="checkbox"
                      checked={footerConfig.socialLinks.showWhatsapp}
                      onChange={(e) => updateSocialLinks({ showWhatsapp: e.target.checked })}
                      className="w-4 h-4 text-emerald-600 rounded"
                    />
                    <span className="w-20 font-bold text-emerald-700">WhatsApp:</span>
                    <input
                      type="text"
                      value={footerConfig.socialLinks.whatsapp}
                      onChange={(e) => updateSocialLinks({ whatsapp: e.target.value })}
                      className="flex-1 p-2 bg-white border border-gray-300 rounded-lg font-mono text-[11px]"
                    />
                  </div>

                  <div className="flex items-center gap-2">
                    <input
                      type="checkbox"
                      checked={footerConfig.socialLinks.showInstagram}
                      onChange={(e) => updateSocialLinks({ showInstagram: e.target.checked })}
                      className="w-4 h-4 text-pink-600 rounded"
                    />
                    <span className="w-20 font-bold text-pink-600">Instagram:</span>
                    <input
                      type="text"
                      value={footerConfig.socialLinks.instagram}
                      onChange={(e) => updateSocialLinks({ instagram: e.target.value })}
                      className="flex-1 p-2 bg-white border border-gray-300 rounded-lg font-mono text-[11px]"
                    />
                  </div>

                  <div className="flex items-center gap-2">
                    <input
                      type="checkbox"
                      checked={footerConfig.socialLinks.showFacebook}
                      onChange={(e) => updateSocialLinks({ showFacebook: e.target.checked })}
                      className="w-4 h-4 text-blue-600 rounded"
                    />
                    <span className="w-20 font-bold text-blue-600">Facebook:</span>
                    <input
                      type="text"
                      value={footerConfig.socialLinks.facebook}
                      onChange={(e) => updateSocialLinks({ facebook: e.target.value })}
                      className="flex-1 p-2 bg-white border border-gray-300 rounded-lg font-mono text-[11px]"
                    />
                  </div>

                  <div className="flex items-center gap-2">
                    <input
                      type="checkbox"
                      checked={footerConfig.socialLinks.showYoutube}
                      onChange={(e) => updateSocialLinks({ showYoutube: e.target.checked })}
                      className="w-4 h-4 text-red-600 rounded"
                    />
                    <span className="w-20 font-bold text-red-600">YouTube:</span>
                    <input
                      type="text"
                      value={footerConfig.socialLinks.youtube}
                      onChange={(e) => updateSocialLinks({ youtube: e.target.value })}
                      className="flex-1 p-2 bg-white border border-gray-300 rounded-lg font-mono text-[11px]"
                    />
                  </div>

                  <div className="flex items-center gap-2">
                    <input
                      type="checkbox"
                      checked={footerConfig.socialLinks.showTwitter}
                      onChange={(e) => updateSocialLinks({ showTwitter: e.target.checked })}
                      className="w-4 h-4 text-gray-800 rounded"
                    />
                    <span className="w-20 font-bold text-gray-800">Twitter / X:</span>
                    <input
                      type="text"
                      value={footerConfig.socialLinks.twitter}
                      onChange={(e) => updateSocialLinks({ twitter: e.target.value })}
                      className="flex-1 p-2 bg-white border border-gray-300 rounded-lg font-mono text-[11px]"
                    />
                  </div>

                  <div className="flex items-center gap-2">
                    <input
                      type="checkbox"
                      checked={footerConfig.socialLinks.showLinkedin}
                      onChange={(e) => updateSocialLinks({ showLinkedin: e.target.checked })}
                      className="w-4 h-4 text-blue-700 rounded"
                    />
                    <span className="w-20 font-bold text-blue-700">LinkedIn:</span>
                    <input
                      type="text"
                      value={footerConfig.socialLinks.linkedin}
                      onChange={(e) => updateSocialLinks({ linkedin: e.target.value })}
                      className="flex-1 p-2 bg-white border border-gray-300 rounded-lg font-mono text-[11px]"
                    />
                  </div>
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Copyright Legal Notice</label>
                <input
                  type="text"
                  value={footerConfig.copyrightText}
                  onChange={(e) => setFooterConfig({ ...footerConfig, copyrightText: e.target.value })}
                  className="w-full p-2 bg-gray-50 border border-gray-300 rounded-lg text-xs"
                />
              </div>
            </div>
          )}

          {/* TAB 3: CUSTOM CODE / SCRIPTS INSERTER */}
          {activeTab === 'code' && (
            <div className="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-6 text-xs text-gray-800">
              <h2 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2 flex items-center gap-2">
                <Code2 className="w-4 h-4 text-brand-green" />
                <span>Custom Header & Footer Code Inserter</span>
              </h2>

              <p className="text-gray-500">
                Insert Google Tag Manager, Meta Pixel, custom web analytics, live chat widgets, or custom CSS stylesheet overrides.
              </p>

              <div>
                <label className="block font-bold text-gray-900 mb-1">Custom Header Code (&lt;head&gt; / &lt;header&gt; Scripts)</label>
                <p className="text-[11px] text-gray-500 mb-1">Scripts inserted here are executed in the page head (e.g. GTM container, Google Site Verification, Meta Pixel):</p>
                <textarea
                  rows={5}
                  value={headerConfig.customHeadCode || ''}
                  onChange={(e) => setHeaderConfig({ ...headerConfig, customHeadCode: e.target.value })}
                  placeholder="<!-- Google Tag Manager / Analytics Code -->&#10;<script>...</script>"
                  className="w-full p-3 font-mono text-[11px] bg-gray-950 text-emerald-300 rounded-xl focus:outline-none"
                />
              </div>

              <div>
                <label className="block font-bold text-gray-900 mb-1">Custom Footer Code (&lt;body&gt; End Scripts)</label>
                <p className="text-[11px] text-gray-500 mb-1">Code executed before the closing &lt;/body&gt; tag (e.g. Chat widgets, badges, tracking pixels):</p>
                <textarea
                  rows={5}
                  value={footerConfig.customFooterCode || ''}
                  onChange={(e) => setFooterConfig({ ...footerConfig, customFooterCode: e.target.value })}
                  placeholder="<!-- Live Chat / Trust Seal Script -->&#10;<script>...</script>"
                  className="w-full p-3 font-mono text-[11px] bg-gray-950 text-amber-200 rounded-xl focus:outline-none"
                />
              </div>

              <div>
                <label className="block font-bold text-gray-900 mb-1">Custom CSS Overrides</label>
                <p className="text-[11px] text-gray-500 mb-1">Custom stylesheet rules applied globally to the public marketplace:</p>
                <textarea
                  rows={4}
                  value={footerConfig.customCss || ''}
                  onChange={(e) => setFooterConfig({ ...footerConfig, customCss: e.target.value })}
                  placeholder="/* Custom CSS */&#10;.custom-banner { font-weight: 700; }"
                  className="w-full p-3 font-mono text-[11px] bg-gray-950 text-cyan-300 rounded-xl focus:outline-none"
                />
              </div>
            </div>
          )}

        </div>

        {/* Live Device Preview Pane (Right) */}
        <div className="lg:col-span-6 space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="font-bold text-sm text-gray-900 font-display flex items-center gap-2">
              <Eye className="w-4 h-4 text-brand-green" />
              <span>Real-Time Reactive Storefront Preview</span>
            </h2>

            <div className="flex items-center bg-gray-200 p-1 rounded-xl text-xs">
              <button
                onClick={() => setPreviewDevice('desktop')}
                className={`p-1.5 rounded-lg flex items-center gap-1 cursor-pointer transition-all ${
                  previewDevice === 'desktop' ? 'bg-white shadow-xs font-bold text-brand-green' : 'text-gray-600'
                }`}
                title="Desktop View"
              >
                <Monitor className="w-3.5 h-3.5" />
                <span className="hidden sm:inline">Desktop</span>
              </button>
              <button
                onClick={() => setPreviewDevice('mobile')}
                className={`p-1.5 rounded-lg flex items-center gap-1 cursor-pointer transition-all ${
                  previewDevice === 'mobile' ? 'bg-white shadow-xs font-bold text-brand-green' : 'text-gray-600'
                }`}
                title="Mobile View"
              >
                <Smartphone className="w-3.5 h-3.5" />
                <span className="hidden sm:inline">Mobile</span>
              </button>
            </div>
          </div>

          <div
            className={`border border-gray-300 rounded-3xl bg-white shadow-xl overflow-hidden transition-all mx-auto ${
              previewDevice === 'mobile' ? 'max-w-xs' : 'w-full'
            }`}
          >
            <div className="bg-slate-900 text-white text-[10px] px-4 py-2 flex items-center justify-between">
              <span className="font-mono">Preview: {previewDevice.toUpperCase()}</span>
              <span className="text-emerald-400 flex items-center gap-1">
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Reactive Storefront Live</span>
              </span>
            </div>

            {/* Embedded Live Preview */}
            <div className="overflow-y-auto max-h-[560px] divide-y divide-gray-100">
              <AnnouncementBar />
              <Header />
              <div className="p-8 text-center text-xs text-gray-400 italic bg-gray-50/70">
                [ Marketplace Product Catalog & Hero Area ]
              </div>
              <Footer />
            </div>
          </div>
        </div>

      </div>

      {/* Full Screen Live Preview Modal */}
      {showLivePreviewModal && (
        <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex flex-col p-4 sm:p-8 animate-in fade-in duration-150">
          <div className="bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col flex-1 max-w-6xl mx-auto w-full">
            <div className="bg-slate-950 text-white p-4 flex items-center justify-between border-b border-slate-800">
              <div className="flex items-center gap-2">
                <span className="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <span className="font-bold text-sm">Full Marketplace Chrome & Footer Inspection</span>
              </div>
              <button
                onClick={() => setShowLivePreviewModal(false)}
                className="p-1.5 rounded-full bg-slate-800 text-gray-300 hover:text-white"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
            <div className="flex-1 overflow-y-auto">
              <AnnouncementBar />
              <Header />
              <div className="p-16 text-center text-sm text-gray-500 bg-gray-50">
                Viewing customized logo, announcement ticker, category navigation, corporate addresses, and social channels.
              </div>
              <Footer />
            </div>
          </div>
        </div>
      )}

    </div>
  );
};
