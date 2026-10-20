import React from 'react';
import { Link } from 'react-router-dom';
import {
  Store,
  Users,
  Banknote,
  Truck,
  CheckCircle2,
  ArrowRight,
  ShieldCheck,
  Sparkles,
  Heart
} from 'lucide-react';
import { useDemoModal } from '../context/DemoModalContext';

export const BecomeSeller: React.FC = () => {
  const { showDemoNotice } = useDemoModal();

  return (
    <div className="space-y-16 py-10">
      
      {/* Hero Section */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-gradient-to-br from-emerald-900 to-brand-green text-white rounded-3xl p-8 sm:p-14 relative overflow-hidden shadow-xl">
          <div className="max-w-2xl space-y-6 relative z-10">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800 text-amber-300 text-xs font-semibold">
              <Sparkles className="w-3.5 h-3.5" />
              <span>Direct Rural Artisan Partnership</span>
            </div>

            <h1 className="text-3xl sm:text-5xl font-extrabold font-display leading-tight">
              Sell Directly from Your Village to All of India
            </h1>

            <p className="text-sm sm:text-base text-emerald-100/90 leading-relaxed">
              Are you an artisan, a women self-help group (SHG), an organic farmer, or a cottage unit? 
              Join GanvGaliHut and reach lakhs of conscious urban families with zero middleman exploitation.
            </p>

            <div className="flex flex-wrap gap-4 pt-2">
              <Link
                to="/seller-register"
                className="px-6 py-3.5 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold text-sm rounded-xl shadow-lg transition-all flex items-center gap-2"
              >
                <span>Start Free Registration</span>
                <ArrowRight className="w-4 h-4" />
              </Link>

              <button
                onClick={() => showDemoNotice('Artisan Helpline', 'Call our rural support coordinator at 1800-GANV-HUT (Demo preview).')}
                className="px-6 py-3.5 bg-emerald-800 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl border border-emerald-600 transition-all cursor-pointer"
              >
                Call Rural Support
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* Benefits Grid */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-xl mx-auto mb-10">
          <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
            Why Sell on GanvGaliHut?
          </h2>
          <p className="text-xs sm:text-sm text-gray-600 mt-2">
            Tailored specifically for rural creators, self-help groups, and farmer cooperatives.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <div className="w-12 h-12 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center">
              <Banknote className="w-6 h-6" />
            </div>
            <h3 className="font-bold text-base text-gray-900 font-display">0% Exploitative Cut</h3>
            <p className="text-xs text-gray-600 leading-relaxed">
              You fix your artisan price. We ensure the full product value is deposited directly into your bank or SHG account every Tuesday.
            </p>
          </div>

          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <div className="w-12 h-12 rounded-xl bg-orange-50 text-brand-orange flex items-center justify-center">
              <Truck className="w-6 h-6" />
            </div>
            <h3 className="font-bold text-base text-gray-900 font-display">Village Doorstep Pickup</h3>
            <p className="text-xs text-gray-600 leading-relaxed">
              No need to travel to city transport hubs. Our logistics partners collect packed orders directly from your village premises.
            </p>
          </div>

          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <div className="w-12 h-12 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center">
              <Users className="w-6 h-6" />
            </div>
            <h3 className="font-bold text-base text-gray-900 font-display">Dedicated Village Mitra</h3>
            <p className="text-xs text-gray-600 leading-relaxed">
              Assistance with digital photography, barcode printing, and packaging material in your local regional language.
            </p>
          </div>
        </div>
      </section>

      {/* 3 Step Registration Flow */}
      <section className="bg-brand-cream py-14 border-y border-amber-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-xl mx-auto mb-10">
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
              Simple 3-Step Onboarding
            </h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs">
              <div className="text-3xl font-extrabold text-brand-green font-display mb-2">1</div>
              <h3 className="font-bold text-sm text-gray-900 mb-1">Fill Basic Profile</h3>
              <p className="text-xs text-gray-600">Provide your artisan cluster details, village location, and craft category.</p>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs">
              <div className="text-3xl font-extrabold text-brand-orange font-display mb-2">2</div>
              <h3 className="font-bold text-sm text-gray-900 mb-1">Catalog Listing</h3>
              <p className="text-xs text-gray-600">Our Mitra assists in taking clean product photos and setting fair prices.</p>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs">
              <div className="text-3xl font-extrabold text-brand-green font-display mb-2">3</div>
              <h3 className="font-bold text-sm text-gray-900 mb-1">Start Shipping</h3>
              <p className="text-xs text-gray-600">Receive orders via SMS, pack items, and hand them over to the postal agent.</p>
            </div>
          </div>

          <div className="text-center pt-8">
            <Link
              to="/seller-register"
              className="inline-flex items-center gap-2 px-8 py-3.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-md transition-all"
            >
              <span>Open Seller Registration Form</span>
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </div>
      </section>

    </div>
  );
};
