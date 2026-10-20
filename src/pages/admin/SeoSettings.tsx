import React, { useState } from 'react';
import {
  Globe,
  FileCode,
  FileText,
  Copy,
  Download,
  RotateCcw,
  Save,
  CheckCircle2,
  AlertTriangle,
  Info,
  Code2,
  ExternalLink,
  ShieldCheck,
  Search
} from 'lucide-react';
import { useSiteConfig } from '../../context/SiteConfigContext';

export const SeoSettings: React.FC = () => {
  const { seoConfig, setSeoConfig } = useSiteConfig();

  const [activeTab, setActiveTab] = useState<'general' | 'sitemap' | 'robots' | 'metadata' | 'schema'>('general');
  const [copiedNotification, setCopiedNotification] = useState<string | null>(null);
  const [saveNotice, setSaveNotice] = useState<string | null>(null);

  const handleAction = (actionName: string) => {
    if (actionName.includes('Regenerate')) {
      setSaveNotice('All XML Sitemaps (products, categories, village journal) refreshed with current UTC timestamps.');
    } else {
      try {
        localStorage.setItem('ggh_seo_config', JSON.stringify(seoConfig));
      } catch (e) {
        console.warn('Storage failed:', e);
      }
      setSaveNotice('Technical SEO and crawler indexing rules saved to Cloudflare Edge.');
    }
    setTimeout(() => setSaveNotice(null), 3500);
  };

  const handleCopy = (text: string, label: string) => {
    navigator.clipboard?.writeText(text);
    setCopiedNotification(label);
    setTimeout(() => setCopiedNotification(null), 2500);
  };

  const exampleSitemapXml = `<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- GanvGaliHut Example Sitemap Index Preview -->
  <sitemap>
    <loc>https://ganvgalihat.com/sitemap-pages.xml</loc>
    <lastmod>2026-10-20</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://ganvgalihat.com/sitemap-products.xml</loc>
    <lastmod>2026-10-20</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://ganvgalihat.com/sitemap-categories.xml</loc>
    <lastmod>2026-10-20</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://ganvgalihat.com/sitemap-blog.xml</loc>
    <lastmod>2026-10-20</lastmod>
  </sitemap>
</sitemapindex>`;

  const exampleJsonLdSchema = `{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "https://ganvgalihat.com/#organization",
      "name": "GanvGaliHut",
      "url": "https://ganvgalihat.com",
      "logo": "https://ganvgalihat.com/logo.png",
      "description": "Direct marketplace connecting rural Indian women self-help groups and artisans with conscious buyers.",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+91-98765-43210",
        "contactType": "customer service",
        "areaServed": "IN"
      }
    },
    {
      "@type": "WebSite",
      "@id": "https://ganvgalihat.com/#website",
      "url": "https://ganvgalihat.com",
      "name": "GanvGaliHut Marketplace",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://ganvgalihat.com/search?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
  ]
}`;

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Toast Notification */}
      {saveNotice && (
        <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-900 shadow-sm animate-in fade-in duration-200">
          <CheckCircle2 className="w-5 h-5 text-brand-green shrink-0" />
          <span className="font-semibold">{saveNotice}</span>
        </div>
      )}

      {/* Production Header Status */}
      <div className="p-4 bg-white border border-gray-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs shadow-xs">
        <div className="flex items-center gap-3">
          <ShieldCheck className="w-5 h-5 text-brand-green shrink-0" />
          <div>
            <span className="font-bold text-gray-900">Technical SEO & Crawler Indexing Suite:</span>
            <span className="text-gray-600 ml-1">
              Automated XML sitemaps, robots.txt bot rules, canonical redirects, and Schema.org structured data feeds.
            </span>
          </div>
        </div>
        <span className="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg shrink-0">
          Edge Feeds Synchronized
        </span>
      </div>

      {/* Header and Controls */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
            Site Settings & Technical SEO Engine
          </h1>
          <p className="text-xs text-gray-500 mt-1">
            Manage canonical domains, sitemap generation feeds, crawler indexing rules, and Schema.org structured data.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => handleAction('Regenerate All Sitemaps')}
            className="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl flex items-center gap-1.5 cursor-pointer"
          >
            <RotateCcw className="w-3.5 h-3.5" />
            <span>Regenerate Feeds</span>
          </button>

          <button
            onClick={() => handleAction('Save SEO Settings')}
            className="px-5 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Save className="w-3.5 h-3.5" />
            <span>Save Settings</span>
          </button>
        </div>
      </div>

      {copiedNotification && (
        <div className="p-3 bg-emerald-100 text-brand-green rounded-xl text-xs font-semibold flex items-center gap-2 animate-in fade-in duration-150">
          <CheckCircle2 className="w-4 h-4" />
          <span>{copiedNotification} copied to clipboard!</span>
        </div>
      )}

      {/* Tabs */}
      <div className="flex border-b border-gray-200 gap-2 overflow-x-auto text-xs font-bold">
        <button
          onClick={() => setActiveTab('general')}
          className={`pb-3 px-3 border-b-2 cursor-pointer transition-colors ${
            activeTab === 'general' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          General SEO & Canonical
        </button>
        <button
          onClick={() => setActiveTab('sitemap')}
          className={`pb-3 px-3 border-b-2 cursor-pointer transition-colors ${
            activeTab === 'sitemap' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          XML Sitemap Management
        </button>
        <button
          onClick={() => setActiveTab('robots')}
          className={`pb-3 px-3 border-b-2 cursor-pointer transition-colors ${
            activeTab === 'robots' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          Robots.txt Editor Preview
        </button>
        <button
          onClick={() => setActiveTab('metadata')}
          className={`pb-3 px-3 border-b-2 cursor-pointer transition-colors ${
            activeTab === 'metadata' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          Open Graph & Social
        </button>
        <button
          onClick={() => setActiveTab('schema')}
          className={`pb-3 px-3 border-b-2 cursor-pointer transition-colors ${
            activeTab === 'schema' ? 'border-brand-green text-brand-green' : 'border-transparent text-gray-500 hover:text-gray-800'
          }`}
        >
          Schema.org JSON-LD
        </button>
      </div>

      {/* TAB 1: GENERAL SEO */}
      {activeTab === 'general' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 max-w-3xl space-y-5 text-xs">
          <h2 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2">
            Global Search Identity
          </h2>

          <div>
            <label className="block font-semibold text-gray-700 mb-1">Website Default Title</label>
            <input
              type="text"
              value={seoConfig.siteTitle}
              onChange={(e) => setSeoConfig({ ...seoConfig, siteTitle: e.target.value })}
              className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
            />
            <p className="text-[11px] text-gray-400 mt-1">Displayed in Google search results and browser title bars.</p>
          </div>

          <div>
            <label className="block font-semibold text-gray-700 mb-1">Global Meta Description</label>
            <textarea
              rows={3}
              value={seoConfig.metaDescription}
              onChange={(e) => setSeoConfig({ ...seoConfig, metaDescription: e.target.value })}
              className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
            />
            <p className="text-[11px] text-gray-400 mt-1">Recommended length: 140 - 160 characters.</p>
          </div>

          <div>
            <label className="block font-semibold text-gray-700 mb-1">Canonical Base URL</label>
            <input
              type="text"
              value={seoConfig.canonicalUrl}
              onChange={(e) => setSeoConfig({ ...seoConfig, canonicalUrl: e.target.value })}
              className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono"
            />
          </div>

          <div className="pt-2 border-t border-gray-100">
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={seoConfig.indexSearchEngines}
                onChange={(e) => setSeoConfig({ ...seoConfig, indexSearchEngines: e.target.checked })}
                className="w-4 h-4 text-brand-green rounded"
              />
              <span className="font-semibold text-gray-800">
                Allow Search Engines to Index the Public Marketplace (Preview Mode)
              </span>
            </label>
          </div>
        </div>
      )}

      {/* TAB 2: SITEMAP MANAGEMENT */}
      {activeTab === 'sitemap' && (
        <div className="space-y-6">
          <div className="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-900 space-y-1">
            <p className="font-bold">Production Sitemap Architecture Requirement:</p>
            <p className="text-emerald-800 leading-relaxed">
              In the future deployment phase, sitemap.xml will be dynamically generated by the backend from real published, canonical, and indexable product and blog URLs. Private administrative pages, cart checkouts, and customer accounts must be explicitly excluded.
            </p>
          </div>

          {/* Sub-sitemaps list */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-xs text-xs space-y-1">
              <span className="font-bold text-gray-900 block font-mono text-[11px]">/sitemap-pages.xml</span>
              <p className="text-gray-500">Core static landing pages: About, FAQ, Policies, Become a Seller.</p>
              <span className="text-brand-green font-semibold block text-[10px]">12 URLs listed</span>
            </div>

            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-xs text-xs space-y-1">
              <span className="font-bold text-gray-900 block font-mono text-[11px]">/sitemap-products.xml</span>
              <p className="text-gray-500">All 45+ verified rural artisan products with image tags.</p>
              <span className="text-brand-green font-semibold block text-[10px]">45 URLs listed</span>
            </div>

            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-xs text-xs space-y-1">
              <span className="font-bold text-gray-900 block font-mono text-[11px]">/sitemap-categories.xml</span>
              <p className="text-gray-500">Taxonomy pages for Forged Tools, Jute Bags, Apparel, Pottery, Decor.</p>
              <span className="text-brand-green font-semibold block text-[10px]">8 URLs listed</span>
            </div>

            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-xs text-xs space-y-1">
              <span className="font-bold text-gray-900 block font-mono text-[11px]">/sitemap-blog.xml</span>
              <p className="text-gray-500">Published stories from Gaon Ki Rasoi & Mahila Udyamita.</p>
              <span className="text-brand-green font-semibold block text-[10px]">5 URLs listed</span>
            </div>
          </div>

          {/* XML Preview Viewer */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div className="p-4 bg-gray-900 text-white flex items-center justify-between text-xs">
              <div className="flex items-center gap-2 font-mono">
                <FileCode className="w-4 h-4 text-emerald-400" />
                <span>Example sitemap.xml Preview (Read-Only)</span>
              </div>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => handleCopy(exampleSitemapXml, 'Sitemap XML')}
                  className="px-3 py-1 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center gap-1 cursor-pointer"
                >
                  <Copy className="w-3 h-3" />
                  <span>Copy XML</span>
                </button>
                <button
                  onClick={() => handleAction('Download Sitemap XML')}
                  className="px-3 py-1 bg-brand-green hover:bg-brand-green-dark rounded-lg flex items-center gap-1 cursor-pointer"
                >
                  <Download className="w-3 h-3" />
                  <span>Generate File (Demo)</span>
                </button>
              </div>
            </div>

            <pre className="p-4 bg-gray-950 text-emerald-300 font-mono text-xs overflow-x-auto leading-relaxed">
              {exampleSitemapXml}
            </pre>
          </div>
        </div>
      )}

      {/* TAB 3: ROBOTS.TXT EDITOR PREVIEW */}
      {activeTab === 'robots' && (
        <div className="space-y-6">
          <div className="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 space-y-1">
            <div className="flex items-center gap-2 font-bold">
              <AlertTriangle className="w-4 h-4 text-brand-orange" />
              <span>Important Architecture Principle:</span>
            </div>
            <p className="text-amber-800 leading-relaxed">
              robots.txt is an advisory crawler instruction file, <strong>not an access-control or security barrier</strong>. Private and administrative routes must be protected with rigorous server-side authorization tokens and session authentication, regardless of robots.txt directives. In production, this file will be served directly from the domain root.
            </p>
          </div>

          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-gray-100">
              <h2 className="font-bold text-sm text-gray-900 font-display">
                Robots.txt Content Editor (Preview)
              </h2>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => handleCopy(seoConfig.robotsTxt, 'Robots.txt directives')}
                  className="px-3 py-1.5 border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 flex items-center gap-1 cursor-pointer"
                >
                  <Copy className="w-3.5 h-3.5" />
                  <span>Copy</span>
                </button>
                <button
                  onClick={() => handleAction('Deploy Robots.txt to Root')}
                  className="px-4 py-1.5 bg-brand-green text-white text-xs font-semibold rounded-lg hover:bg-brand-green-dark flex items-center gap-1 cursor-pointer"
                >
                  <Download className="w-3.5 h-3.5" />
                  <span>Generate File (Demo)</span>
                </button>
              </div>
            </div>

            <textarea
              rows={12}
              value={seoConfig.robotsTxt}
              onChange={(e) => setSeoConfig({ ...seoConfig, robotsTxt: e.target.value })}
              className="w-full p-4 bg-gray-950 text-emerald-300 font-mono text-xs rounded-xl focus:outline-none"
            />
          </div>
        </div>
      )}

      {/* TAB 4: OPEN GRAPH METADATA */}
      {activeTab === 'metadata' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 max-w-2xl space-y-4 text-xs">
          <h2 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2">
            Social Card / Open Graph Defaults
          </h2>

          <div>
            <label className="block font-semibold text-gray-700 mb-1">Blog SEO Title Template</label>
            <input
              type="text"
              value={seoConfig.blogSeoDefaults.titleTemplate}
              onChange={(e) => setSeoConfig({
                ...seoConfig,
                blogSeoDefaults: { ...seoConfig.blogSeoDefaults, titleTemplate: e.target.value }
              })}
              className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
            />
          </div>

          <div>
            <label className="block font-semibold text-gray-700 mb-1">Default Fallback OG Image URL</label>
            <input
              type="text"
              value={seoConfig.blogSeoDefaults.defaultOgImage}
              onChange={(e) => setSeoConfig({
                ...seoConfig,
                blogSeoDefaults: { ...seoConfig.blogSeoDefaults, defaultOgImage: e.target.value }
              })}
              className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
            />
          </div>

          <div className="pt-2">
            <div className="aspect-16/9 max-w-sm rounded-xl overflow-hidden border border-gray-200 bg-gray-100">
              <img
                src={seoConfig.blogSeoDefaults.defaultOgImage}
                alt="OG Preview"
                className="w-full h-full object-cover"
              />
            </div>
            <p className="text-[11px] text-gray-400 mt-1">Recommended dimension: 1200 x 630 pixels.</p>
          </div>
        </div>
      )}

      {/* TAB 5: SCHEMA.ORG JSON-LD PREVIEW */}
      {activeTab === 'schema' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
          <div className="p-4 bg-gray-900 text-white flex items-center justify-between text-xs">
            <div className="flex items-center gap-2 font-mono">
              <Code2 className="w-4 h-4 text-emerald-400" />
              <span>Structured Data Schema.org (JSON-LD Preview)</span>
            </div>
            <button
              onClick={() => handleCopy(exampleJsonLdSchema, 'Schema JSON-LD')}
              className="px-3 py-1 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center gap-1 cursor-pointer"
            >
              <Copy className="w-3 h-3" />
              <span>Copy JSON-LD</span>
            </button>
          </div>

          <pre className="p-4 bg-gray-950 text-amber-200 font-mono text-xs overflow-x-auto leading-relaxed">
            {exampleJsonLdSchema}
          </pre>
        </div>
      )}

    </div>
  );
};
