import React from 'react';
import { Link } from 'react-router-dom';
import {
  HeartHandshake,
  Sparkles,
  Users,
  Target,
  Award,
  ArrowRight,
  ShieldCheck,
  CheckCircle2
} from 'lucide-react';
import { useDemoModal } from '../context/DemoModalContext';

export const About: React.FC = () => {
  const { showDemoNotice } = useDemoModal();

  return (
    <div className="space-y-16 py-10 w-full max-w-full overflow-x-hidden min-w-0">
      
      {/* Hero Section */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full min-w-0">
        <div className="bg-gradient-to-br from-emerald-950 via-brand-green to-emerald-900 text-white rounded-3xl p-6 sm:p-14 text-center max-w-4xl mx-auto space-y-4 shadow-xl">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-800 text-amber-300 text-xs font-semibold">
            <Sparkles className="w-3.5 h-3.5" />
            <span>An Initiative by S S Industries</span>
          </div>

          <h1 className="text-3xl sm:text-5xl font-extrabold font-display leading-tight">
            Deshi ki mitti ka swad, <br />
            <span className="text-amber-300">Ganv se ghar tak</span>
          </h1>

          <p className="text-sm sm:text-base text-emerald-100/90 leading-relaxed max-w-2xl mx-auto">
            GanvGaliHut was founded by S S Industries to reverse the commercial exploitation of rural Indian artisans, women self-help groups (SHGs), and small organic farmers by building a direct digital bridge to conscious urban homes.
          </p>
        </div>
      </section>

      {/* Mission & Vision */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-8 space-y-3">
            <div className="w-12 h-12 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center">
              <Target className="w-6 h-6" />
            </div>
            <h2 className="text-xl font-bold text-gray-900 font-display">Our Mission</h2>
            <p className="text-xs sm:text-sm text-gray-600 leading-relaxed">
              To eliminate the layers of predatory middlemen who buy village-produced goods for pennies and resell them in metro cities at 400% markups. We ensure that 100% of fair artisan earnings reach rural bank accounts with complete transparency.
            </p>
          </div>

          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-8 space-y-3">
            <div className="w-12 h-12 rounded-xl bg-orange-50 text-brand-orange flex items-center justify-center">
              <Users className="w-6 h-6" />
            </div>
            <h2 className="text-xl font-bold text-gray-900 font-display">Our Vision</h2>
            <p className="text-xs sm:text-sm text-gray-600 leading-relaxed">
              A self-reliant rural India where village women entrepreneurs and traditional hereditary craftspeople command dignified, sustainable livelihoods, keeping ancient culinary recipes, natural fibers, and blacksmith crafts thriving for future generations.
            </p>
          </div>
        </div>
      </section>

      {/* The 4 Core Values */}
      <section className="bg-brand-sand/60 py-16 border-y border-gray-200/70">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
          <div className="text-center max-w-xl mx-auto">
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
              The Principles That Guide Us
            </h2>
            <p className="text-xs sm:text-sm text-gray-600 mt-2">
              Every package dispatched from our artisan clusters upholds these sacred promises.
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-2">
              <div className="font-bold text-brand-green text-sm">1. Zero Adulteration</div>
              <p className="text-xs text-gray-600 leading-relaxed">
                No palm oil, synthetic chemical bleaching, or artificial coloring. Real food from real village soil.
              </p>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-2">
              <div className="font-bold text-brand-orange text-sm">2. Women SHG First</div>
              <p className="text-xs text-gray-600 leading-relaxed">
                70%+ of our sellers are women self-help collectives, directly funding children’s schooling and nutrition.
              </p>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-2">
              <div className="font-bold text-brand-green text-sm">3. Heritage Preservation</div>
              <p className="text-xs text-gray-600 leading-relaxed">
                Protecting GI-tagged crafts, Channapatna wood toys, leaf-spring blacksmithing, and Vedic Bilona churning.
              </p>
            </div>

            <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-2">
              <div className="font-bold text-brand-orange text-sm">4. Eco-Conscious Packaging</div>
              <p className="text-xs text-gray-600 leading-relaxed">
                Packaged using biodegradable golden jute sacks, recycled paper cushioning, and compostable tape.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Corporate Entity & Facilities */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-10 max-w-4xl mx-auto space-y-6">
          <div className="text-center max-w-xl mx-auto">
            <span className="text-xs font-bold text-brand-orange uppercase tracking-wider">
              Corporate Governance
            </span>
            <h2 className="text-xl sm:text-2xl font-bold text-gray-900 font-display mt-1">
              S S Industries
            </h2>
            <p className="text-xs text-gray-500 mt-1">
              Pioneering rural processing units, artisan coordination, and direct-to-consumer marketplace logistics in Uttar Pradesh.
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
            <div className="p-4 rounded-xl bg-gray-50 border border-gray-100">
              <span className="font-bold text-gray-900 block mb-1">🏢 Works / Factory Unit</span>
              <p className="text-gray-600 leading-relaxed">
                Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126
              </p>
            </div>
            <div className="p-4 rounded-xl bg-gray-50 border border-gray-100">
              <span className="font-bold text-gray-900 block mb-1">📍 Head Office</span>
              <p className="text-gray-600 leading-relaxed">
                G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, Uttar Pradesh
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Box */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div className="bg-brand-cream border border-amber-200 rounded-3xl p-8 sm:p-12 max-w-2xl mx-auto space-y-4">
          <h2 className="text-xl sm:text-2xl font-bold text-gray-900 font-display">
            Join the Movement to Empower Rural Bharat
          </h2>
          <p className="text-xs text-gray-600">
            Whether you are a conscious customer looking for pure food, or an artisan seeking a direct marketplace.
          </p>
          <div className="flex justify-center gap-4 pt-2">
            <Link
              to="/products"
              className="px-6 py-3 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-xl shadow-md transition-all"
            >
              Shop Village Marketplace
            </Link>
            <Link
              to="/become-seller"
              className="px-6 py-3 bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 text-xs font-semibold rounded-xl"
            >
              Become a Seller
            </Link>
          </div>
        </div>
      </section>

    </div>
  );
};
