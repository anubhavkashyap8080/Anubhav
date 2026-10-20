import React from 'react';
import { ShieldCheck, Lock, Eye, CheckCircle2, Info } from 'lucide-react';

export const PrivacyPolicy: React.FC = () => {
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-900">
        <ShieldCheck className="w-5 h-5 text-brand-green shrink-0" />
        <div>
          <span className="font-bold">Privacy Governance:</span> This policy outlines compliance commitments with India's Digital Personal Data Protection (DPDP) Act.
        </div>
      </div>

      <div className="space-y-2">
        <h1 className="text-3xl font-extrabold text-gray-900 font-display">
          Privacy Policy
        </h1>
        <p className="text-xs text-gray-500">Effective Date: October 2026 • Version 1.0 (Production)</p>
      </div>

      <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 sm:p-10 space-y-6 text-xs text-gray-700 leading-relaxed">
        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">1. Information We Collect</h2>
          <p>
            We collect personal information necessary to fulfill delivery orders, including recipient name, mobile number for dispatch alerts, postal delivery address, and communication preferences. We do not store sensitive payment card numbers or bank credentials on our systems.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">2. Use of Personal Data</h2>
          <p>
            Your information is utilized solely to facilitate package dispatch from rural workshops, provide live order tracking, process refunds, and (with explicit opt-in) share our weekly "Gaon Ki Baat" newsletter. We strictly do not sell, rent, or monetize customer data with third-party advertisers.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">3. Digital Personal Data Protection (DPDP) Rights</h2>
          <p>
            In accordance with Indian data privacy statutes, users retain the right to access, rectify, or request permanent deletion of their personal identity records and address history from our active databases.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">4. Cookies and Local Storage</h2>
          <p>
            We employ essential session cookies for cart state preservation and authentication security. No invasive cross-site tracking beacons are embedded.
          </p>
        </section>
      </div>

    </div>
  );
};
