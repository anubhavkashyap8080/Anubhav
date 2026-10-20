import React from 'react';
import { Truck, ShieldCheck, Clock, MapPin, Info } from 'lucide-react';

export const ShippingPolicy: React.FC = () => {
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3 text-xs text-amber-900">
        <Info className="w-5 h-5 text-brand-orange shrink-0" />
        <div>
          <span className="font-bold">Policy Document Preview:</span> This is a demonstration shipping policy for GaunGaliHat Phase 1. Final legal terms and courier API SLA metrics will be formalized prior to commercial launch.
        </div>
      </div>

      <div className="space-y-2">
        <h1 className="text-3xl font-extrabold text-gray-900 font-display">
          Shipping & Delivery Policy
        </h1>
        <p className="text-xs text-gray-500">Effective Date: October 2026 • Version 1.0 (Demo)</p>
      </div>

      <div className="bg-white rounded-3xl border border-gray-100 shadow-xs p-6 sm:p-10 space-y-6 text-xs text-gray-700 leading-relaxed">
        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">1. Direct-from-Village Dispatch</h2>
          <p>
            Unlike conventional e-commerce warehouses where mass inventory is stored for months, every item on GaunGaliHat is packed and sealed directly at the artisan workshop, gaushala, or SHG community center upon order receipt to guarantee utmost freshness.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">2. Delivery Timelines</h2>
          <ul className="list-disc list-inside space-y-1 text-gray-600">
            <li><strong>Metro Cities:</strong> 4 to 6 business days from dispatch.</li>
            <li><strong>Tier 2 & Tier 3 Towns:</strong> 5 to 7 business days.</li>
            <li><strong>Remote & Hilly Regions:</strong> 7 to 10 business days via India Post Speed Post.</li>
          </ul>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">3. Shipping Charges</h2>
          <p>
            Orders with a cart total of <strong>₹499 or higher qualify for FREE Delivery</strong> nationwide. For orders below ₹499, a nominal flat delivery charge of ₹60 is applied to offset rural parcel pickup expenses.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">4. Eco-Friendly Sustainable Packaging</h2>
          <p>
            In alignment with our environmental mission, parcels are packed using biodegradable jute padding, shredded kraft paper, and paper adhesive tape, avoiding non-recyclable bubble wrap and thermocol wherever feasible.
          </p>
        </section>

        <section className="space-y-2">
          <h2 className="text-base font-bold text-gray-900 font-display">5. Tracking Your Shipment</h2>
          <p>
            Upon dispatch from the village postal facility, customers receive an automated SMS and email notification containing the carrier consignment number and live tracking link.
          </p>
        </section>
      </div>

    </div>
  );
};
