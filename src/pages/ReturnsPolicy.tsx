import React from 'react';
import { RotateCcw, ShieldCheck, CheckCircle2, Info } from 'lucide-react';

export const ReturnsPolicy: React.FC = () => {
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3 text-xs text-amber-900">
        <Info className="w-5 h-5 text-brand-orange shrink-0" />
        <div>
          <span className="font-bold">Policy Document Preview:</span> Demonstration return and refund guidelines for GanvGaliHut Phase 1.
        </div>
      </div>

      <div className="space-y-2">
        <h1 className="text-3xl font-extrabold text-gray-900 font-display">
          Return and Refund Policy
        </h1>
        <p className="text-xs text-gray-500">Effective Date: October 2026 • Version 1.0 (Demo)</p>
      </div>

      <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 sm:p-10 space-y-6 text-xs text-gray-700 leading-relaxed">
        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">1. 7-Day Easy Return Window</h2>
          <p>
            We stand firmly behind the quality and purity of our rural artisans' work. If you receive a damaged, leaked, defective, or incorrect product, you may request a return or replacement within 7 days of package delivery.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">2. Perishable Foods & Dairy Products</h2>
          <p>
            Due to strict hygiene standards, open jars of ghee, pickles, flours, and spices cannot be physically returned once the tamper-evident foil is broken. However, if any food item arrives spoiled, broken in transit, or rancid, we issue a <strong>100% full refund or free replacement</strong> immediately without requiring return shipping.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">3. Handicrafts, Jute & Farming Tools</h2>
          <p>
            Non-perishable items such as hand-forged tools, wooden toys, jute bags, and lac bangles are eligible for return if unused, in original condition, and with original tags intact.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">4. Refund Processing</h2>
          <p>
            Approved refunds are credited directly back to the original payment source (UPI, Card, Netbanking) within 5 to 7 business days. For Cash on Delivery orders, refunds are transferred via direct NEFT / IMPS to the customer’s verified bank account.
          </p>
        </section>
      </div>

    </div>
  );
};
