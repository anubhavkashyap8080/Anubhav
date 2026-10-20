import React from 'react';
import { ArtisanMap } from '../components/map/ArtisanMap';
import { MapPin, Navigation, Compass, Sparkles, Building2, ShieldCheck } from 'lucide-react';

export const NearbyArtisans: React.FC = () => {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10 w-full max-w-full overflow-x-hidden min-w-0">
      
      {/* Page Header */}
      <div className="text-center max-w-3xl mx-auto space-y-3">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-brand-green text-xs font-semibold">
          <Compass className="w-3.5 h-3.5" />
          <span>Interactive Geolocation Directory</span>
        </div>
        <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 font-display">
          Find Nearby Artisans, Foundries & SHG Clusters
        </h1>
        <p className="text-xs sm:text-sm text-gray-600 leading-relaxed">
          Discover local toolmaking foundries, women-led jute stitching cooperatives, and hereditary woodcraft guilds across Uttar Pradesh & Bharat. Powered by 100% free OpenStreetMap & Google Maps API compatibility.
        </p>
      </div>

      {/* S S Industries Hubs Banner */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div className="p-5 rounded-2xl bg-white border border-gray-200 shadow-xs flex items-start gap-4">
          <div className="w-12 h-12 rounded-xl bg-orange-100 text-brand-orange flex items-center justify-center font-bold shrink-0 text-xl">
            🏭
          </div>
          <div>
            <span className="text-[10px] font-bold text-brand-orange uppercase tracking-wider block">Production Foundry</span>
            <h3 className="font-bold text-sm text-gray-900">S S Industries Works Unit</h3>
            <p className="text-xs text-gray-500 mt-0.5">Madhoganj Baghauli Road, Newada, Hardoi 241126</p>
            <a href="tel:+917897492100" className="inline-block mt-2 text-xs font-semibold text-brand-green hover:underline">
              📞 +91 7897492100
            </a>
          </div>
        </div>

        <div className="p-5 rounded-2xl bg-white border border-gray-200 shadow-xs flex items-start gap-4">
          <div className="w-12 h-12 rounded-xl bg-emerald-100 text-brand-green flex items-center justify-center font-bold shrink-0 text-xl">
            🏢
          </div>
          <div>
            <span className="text-[10px] font-bold text-brand-green uppercase tracking-wider block">Central Dispatch</span>
            <h3 className="font-bold text-sm text-gray-900">S S Industries Head Office & UPSIDA Hub</h3>
            <p className="text-xs text-gray-500 mt-0.5">G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi</p>
            <a href="tel:+919005756321" className="inline-block mt-2 text-xs font-semibold text-brand-green hover:underline">
              📞 +91 9005756321
            </a>
          </div>
        </div>
      </div>

      {/* Interactive Map */}
      <ArtisanMap />

    </div>
  );
};
