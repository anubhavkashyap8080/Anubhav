import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import {
  Phone,
  Mail,
  MapPin,
  Send,
  Facebook,
  Instagram,
  Twitter,
  Youtube,
  ShieldCheck,
  Truck,
  HeartHandshake,
  Sparkles
} from 'lucide-react';
import { useSiteConfig } from '../../context/SiteConfigContext';
import { useDemoModal } from '../../context/DemoModalContext';

export const Footer: React.FC = () => {
  const { footerConfig } = useSiteConfig();
  const { showDemoNotice } = useDemoModal();
  const [newsletterEmail, setNewsletterEmail] = useState('');

  const handleNewsletterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newsletterEmail.trim() || !newsletterEmail.includes('@')) {
      showDemoNotice('Newsletter Validation', 'Please enter a valid email address.');
      return;
    }
    showDemoNotice('Newsletter Subscription', 'Thank you for your interest! Newsletter dispatch functionality will be connected to our transactional email service in Phase 2.');
    setNewsletterEmail('');
  };

  return (
    <footer className="bg-brand-green-dark text-white pt-14 pb-8 border-t-4 border-brand-orange">
      {/* Trust Badges Banner */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 border-b border-emerald-800/60">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div className="flex items-start gap-3.5 p-4 rounded-xl bg-emerald-900/40 border border-emerald-800/40">
            <div className="p-2.5 rounded-lg bg-emerald-800/80 text-amber-300 shrink-0">
              <HeartHandshake className="w-5 h-5" />
            </div>
            <div>
              <h4 className="font-semibold text-sm text-emerald-100">100% Direct from Rural Artisans</h4>
              <p className="text-xs text-emerald-300/80 mt-1">Zero middlemen exploitation. Fair earnings to women SHGs & farmers.</p>
            </div>
          </div>

          <div className="flex items-start gap-3.5 p-4 rounded-xl bg-emerald-900/40 border border-emerald-800/40">
            <div className="p-2.5 rounded-lg bg-emerald-800/80 text-amber-300 shrink-0">
              <Sparkles className="w-5 h-5" />
            </div>
            <div>
              <h4 className="font-semibold text-sm text-emerald-100">Desi Authenticity & Purity</h4>
              <p className="text-xs text-emerald-300/80 mt-1">Hand-forged agricultural tools, golden jute bags, handloom textiles & artisan toys.</p>
            </div>
          </div>

          <div className="flex items-start gap-3.5 p-4 rounded-xl bg-emerald-900/40 border border-emerald-800/40">
            <div className="p-2.5 rounded-lg bg-emerald-800/80 text-amber-300 shrink-0">
              <Truck className="w-5 h-5" />
            </div>
            <div>
              <h4 className="font-semibold text-sm text-emerald-100">Pan-India Village Delivery</h4>
              <p className="text-xs text-emerald-300/80 mt-1">Sustainably packed in eco jute & cardboard straight to your doorstep.</p>
            </div>
          </div>

          <div className="flex items-start gap-3.5 p-4 rounded-xl bg-emerald-900/40 border border-emerald-800/40">
            <div className="p-2.5 rounded-lg bg-emerald-800/80 text-amber-300 shrink-0">
              <ShieldCheck className="w-5 h-5" />
            </div>
            <div>
              <h4 className="font-semibold text-sm text-emerald-100">Safe Demo Environment</h4>
              <p className="text-xs text-emerald-300/80 mt-1">Phase 1 prototype. Transparent mock actions with no fake charges.</p>
            </div>
          </div>
        </div>
      </div>

      {/* Main Footer Links & Newsletter */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
          
            {/* Brand Info & Contact */}
            <div className="lg:col-span-2 space-y-4">
              <Link to="/" className="flex items-center gap-3">
                <img
                  src="/logo.png"
                  alt="GaunGaliHat Logo"
                  className="h-12 w-auto bg-white p-1 rounded-lg"
                />
                <div>
                  <span className="font-display font-bold text-xl text-white tracking-wide block">
                    GaunGaliHat
                  </span>
                  <span className="text-[11px] text-amber-300 font-semibold tracking-wider block">
                    A Brand by S S Industries
                  </span>
                  <span className="text-[10px] text-brand-orange-light font-medium tracking-wider uppercase block">
                    Deshi ki mitti ka swad • Ganv se ghar tak
                  </span>
                </div>
              </Link>

              <p className="text-xs text-emerald-200/80 leading-relaxed pr-4">
                {footerConfig.description}
              </p>

              <div className="space-y-3 text-xs text-emerald-200/90 pt-2 border-t border-emerald-800/60">
                <div className="flex items-start gap-2.5">
                  <MapPin className="w-4 h-4 text-amber-300 shrink-0 mt-0.5" />
                  <div>
                    <span className="font-semibold text-white block">Works / Factory Unit:</span>
                    <span className="text-emerald-100/90">Madhoganj Baghauli Road, Newada, Hardoi 241126</span>
                  </div>
                </div>

                <div className="flex items-start gap-2.5">
                  <MapPin className="w-4 h-4 text-brand-orange-light shrink-0 mt-0.5" />
                  <div>
                    <span className="font-semibold text-white block">Head Office:</span>
                    <span className="text-emerald-100/90">G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi</span>
                  </div>
                </div>

                <div className="flex items-start gap-2.5">
                  <Phone className="w-4 h-4 text-amber-300 shrink-0 mt-0.5" />
                  <div>
                    <span className="font-semibold text-white block mb-0.5">Helpline & Orders:</span>
                    <div className="flex flex-wrap gap-x-3 gap-y-1 text-emerald-100 font-medium">
                      <a href="tel:+917897492100" className="hover:text-amber-300 transition-colors underline underline-offset-2">
                        +91 7897492100
                      </a>
                      <a href="tel:+919005756321" className="hover:text-amber-300 transition-colors underline underline-offset-2">
                        +91 9005756321
                      </a>
                      <a href="tel:+918303578806" className="hover:text-amber-300 transition-colors underline underline-offset-2">
                        +91 8303578806
                      </a>
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-2.5">
                  <Mail className="w-4 h-4 text-emerald-300 shrink-0" />
                  <a href={`mailto:${footerConfig.contactEmail}`} className="hover:text-amber-300 transition-colors">
                    {footerConfig.contactEmail}
                  </a>
                </div>
              </div>

            {/* Social Links */}
            <div className="flex items-center gap-3 pt-3">
              <a
                href={footerConfig.socialLinks.facebook}
                target="_blank"
                rel="noopener noreferrer"
                onClick={(e) => {
                  e.preventDefault();
                  showDemoNotice('Social Media Link', 'External social profiles will be activated with verified accounts.');
                }}
                className="w-8 h-8 rounded-full bg-emerald-900 flex items-center justify-center text-emerald-200 hover:bg-brand-orange hover:text-white transition-colors"
                aria-label="Facebook"
              >
                <Facebook className="w-4 h-4" />
              </a>
              <a
                href={footerConfig.socialLinks.instagram}
                target="_blank"
                rel="noopener noreferrer"
                onClick={(e) => {
                  e.preventDefault();
                  showDemoNotice('Social Media Link', 'External social profiles will be activated with verified accounts.');
                }}
                className="w-8 h-8 rounded-full bg-emerald-900 flex items-center justify-center text-emerald-200 hover:bg-brand-orange hover:text-white transition-colors"
                aria-label="Instagram"
              >
                <Instagram className="w-4 h-4" />
              </a>
              <a
                href={footerConfig.socialLinks.twitter}
                target="_blank"
                rel="noopener noreferrer"
                onClick={(e) => {
                  e.preventDefault();
                  showDemoNotice('Social Media Link', 'External social profiles will be activated with verified accounts.');
                }}
                className="w-8 h-8 rounded-full bg-emerald-900 flex items-center justify-center text-emerald-200 hover:bg-brand-orange hover:text-white transition-colors"
                aria-label="Twitter"
              >
                <Twitter className="w-4 h-4" />
              </a>
              <a
                href={footerConfig.socialLinks.youtube}
                target="_blank"
                rel="noopener noreferrer"
                onClick={(e) => {
                  e.preventDefault();
                  showDemoNotice('Social Media Link', 'External social profiles will be activated with verified accounts.');
                }}
                className="w-8 h-8 rounded-full bg-emerald-900 flex items-center justify-center text-emerald-200 hover:bg-brand-orange hover:text-white transition-colors"
                aria-label="YouTube"
              >
                <Youtube className="w-4 h-4" />
              </a>
            </div>
          </div>

          {/* Configurable Footer Nav Columns */}
          {footerConfig.columns.slice(0, 2).map((col, idx) => (
            <div key={idx} className="space-y-3">
              <h4 className="font-semibold text-sm text-amber-200 uppercase tracking-wider font-display">
                {col.title}
              </h4>
              <ul className="space-y-2 text-xs">
                {col.links.map((link, lIdx) => (
                  <li key={lIdx}>
                    <Link
                      to={link.href}
                      className="text-emerald-200/80 hover:text-white hover:underline transition-colors block"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}

          {/* Customer Support & Policies */}
          <div className="space-y-3">
            <h4 className="font-semibold text-sm text-amber-200 uppercase tracking-wider font-display">
              {footerConfig.columns[2]?.title || 'Customer Support'}
            </h4>
            <ul className="space-y-2 text-xs">
              {footerConfig.columns[2]?.links.map((link, lIdx) => (
                <li key={lIdx}>
                  <Link
                    to={link.href}
                    className="text-emerald-200/80 hover:text-white hover:underline transition-colors block"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Newsletter Section */}
        {footerConfig.showNewsletter && (
          <div className="mt-12 p-6 rounded-2xl bg-gradient-to-r from-emerald-900/70 to-emerald-950 border border-emerald-800/80 flex flex-col md:flex-row items-center justify-between gap-6">
            <div className="max-w-md">
              <h4 className="font-bold text-base text-white font-display">
                Subscribe to "Gaon Ki Baat" Newsletter
              </h4>
              <p className="text-xs text-emerald-200/80 mt-1">
                Receive weekly stories of rural women entrepreneurs, seasonal harvest alerts, and special artisan discounts.
              </p>
            </div>

            <form onSubmit={handleNewsletterSubmit} className="w-full md:w-auto flex-1 max-w-md flex gap-2">
              <input
                type="email"
                placeholder="Enter your email address..."
                value={newsletterEmail}
                onChange={(e) => setNewsletterEmail(e.target.value)}
                className="flex-1 px-4 py-2.5 rounded-lg text-xs bg-emerald-950/80 border border-emerald-700 text-white placeholder-emerald-400 focus:outline-none focus:ring-2 focus:ring-brand-orange"
              />
              <button
                type="submit"
                className="px-5 py-2.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-semibold rounded-lg shadow-sm flex items-center gap-1.5 transition-all shrink-0"
              >
                <span>Join</span>
                <Send className="w-3.5 h-3.5" />
              </button>
            </form>
          </div>
        )}
      </div>

      {/* Bottom Bar: Copyright & Demo Notice */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 border-t border-emerald-900 text-center sm:flex sm:justify-between sm:items-center text-xs text-emerald-300/70">
        <p>{footerConfig.copyrightText}</p>
        <div className="mt-3 sm:mt-0 flex items-center justify-center gap-4 text-[11px]">
          <Link to="/admin/site-customization" className="text-amber-300 hover:underline">
            Header/Footer Editor
          </Link>
          <span>•</span>
          <Link to="/admin/security-settings" className="text-amber-300 hover:underline">
            Security Controls
          </Link>
          <span>•</span>
          <Link to="/admin/seo-settings" className="text-amber-300 hover:underline">
            SEO & Sitemap
          </Link>
        </div>
      </div>
    </footer>
  );
};
