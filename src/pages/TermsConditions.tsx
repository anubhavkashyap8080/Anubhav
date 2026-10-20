import React from 'react';
import { FileText, ShieldCheck, CheckCircle2, Info } from 'lucide-react';

export const TermsConditions: React.FC = () => {
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-900">
        <FileText className="w-5 h-5 text-brand-green shrink-0" />
        <div>
          <span className="font-bold">Legal Governance:</span> Official Terms & Conditions governing marketplace transactions on GaunGaliHat.
        </div>
      </div>

      <div className="space-y-2">
        <h1 className="text-3xl font-extrabold text-gray-900 font-display">
          Terms & Conditions
        </h1>
        <p className="text-xs text-gray-500">Effective Date: October 2026 • Version 1.0 (Production)</p>
      </div>

      <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 sm:p-10 space-y-6 text-xs text-gray-700 leading-relaxed">
        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">1. Marketplace Platform Structure</h2>
          <p>
            GaunGaliHat operates as an enablement bridge connecting rural artisan collectives, women self-help groups, and farmers directly with consumers. Product descriptions, specifications, and regional origins are provided by verified producers.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">2. Artisan Craft Variations</h2>
          <p>
            Because authentic village handicrafts, terracotta kulhads, hand-forged iron tools, and hand-woven jute items are created manually without industrial automation, slight natural variations in color, texture, shape, and weight celebrate their genuine handmade heritage.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">3. Pricing and Fair Compensation</h2>
          <p>
            Prices are displayed in Indian Rupees (INR) inclusive of applicable taxes. In alignment with our ethical charter, artisan earnings are protected from arbitrary discounting or exploitative commission cuts.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">4. Prototype Demonstration Disclaimer</h2>
          <p>
            During Phase 1, all interface controls, cart checkouts, and account management actions are functional in a demonstration mode only. No actual monetary debits or legally binding sales contracts are initiated.
          </p>
        </section>
      </div>

    </div>
  );
};
