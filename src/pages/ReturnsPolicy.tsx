import React from 'react';
import { RotateCcw, ShieldCheck, CheckCircle2, Info } from 'lucide-react';

export const ReturnsPolicy: React.FC = () => {
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Official Policy Header */}
      <div className="p-4 bg-white border border-gray-200 rounded-2xl flex items-center gap-3 text-xs text-gray-800 shadow-xs">
        <ShieldCheck className="w-5 h-5 text-brand-green shrink-0" />
        <div>
          <span className="font-bold text-gray-900">Official Artisan Protection & Consumer Policy:</span> Fair-trade guarantees and transparent return process for handcrafted items and agricultural tools.
        </div>
      </div>

      <div className="space-y-2">
        <h1 className="text-3xl font-extrabold text-gray-900 font-display">
          Return and Refund Policy
        </h1>
        <p className="text-xs text-gray-500">Effective Date: October 2026 • Official Guidelines</p>
      </div>

      <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 sm:p-10 space-y-6 text-xs text-gray-700 leading-relaxed">
        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">1. 7-Day Easy Return Window</h2>
          <p>
            We stand firmly behind the quality and purity of our rural artisans' work. If you receive a damaged, leaked, defective, or incorrect product, you may request a return or replacement within 7 days of package delivery.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">2. Clay Pottery & Fragile Handcrafted Items</h2>
          <p>
            Due to the delicate nature of handmade terracotta and clay pottery, we pack each item with multi-layer recycled honeycomb cushioning. In the rare event of transit breakage or damage, simply upload a quick photo within 48 hours of delivery for an <strong>instant 100% refund or free priority replacement</strong> without requiring reverse pickup.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">3. Handicrafts, Jute & Farming Tools</h2>
          <p>
            Hardware items such as hand-forged sickles, tools, wooden toys, jute bags, and lac bangles are eligible for return if unused, in original condition, and with original tags intact.
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
