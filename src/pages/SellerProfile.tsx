import React from 'react';
import { useParams, Link } from 'react-router-dom';
import {
  MapPin,
  CheckCircle2,
  Star,
  Users,
  Calendar,
  MessageCircle,
  Share2,
  ShieldCheck,
  Package
} from 'lucide-react';
import { usePlatform } from '../context/PlatformContext';
import { ProductCard } from '../components/common/ProductCard';
import { useDemoModal } from '../context/DemoModalContext';

export const SellerProfile: React.FC = () => {
  const { sellerSlug } = useParams<{ sellerSlug: string }>();
  const { showDemoNotice } = useDemoModal();
  const { vendors, products } = usePlatform();

  const allVendors = vendors && vendors.length > 0 ? vendors : [];
  const allProducts = products && products.length > 0 ? products : [];
  const seller = allVendors.find(s => s.slug === sellerSlug) || allVendors[0];

  if (!seller) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-16 text-center">
        <h2 className="text-xl font-bold text-gray-800">Artisan Not Found</h2>
        <Link to="/products" className="mt-4 inline-block px-4 py-2 bg-brand-green text-white rounded-lg">
          Browse Marketplace
        </Link>
      </div>
    );
  }

  const sellerProducts = allProducts.filter(p => p.seller.id === seller.id);

  return (
    <div className="space-y-8 pb-16">
      
      {/* Cover Image & Header Banner */}
      <div className="relative">
        <div className="h-64 sm:h-80 w-full overflow-hidden bg-emerald-950">
          <img
            src={seller.coverImage}
            alt={seller.name}
            className="w-full h-full object-cover opacity-60"
          />
        </div>

        {/* Profile Card Overlay */}
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24 relative z-10">
          <div className="bg-white rounded-3xl border border-gray-100 shadow-xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            
            <div className="flex flex-col sm:flex-row items-start sm:items-center gap-5">
              <img
                src={seller.avatar}
                alt={seller.name}
                className="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-4 border-white shadow-md bg-white shrink-0"
              />
              <div className="space-y-1.5">
                <div className="flex items-center gap-2 flex-wrap">
                  <h1 className="text-xl sm:text-2xl font-extrabold text-gray-900 font-display">
                    {seller.name}
                  </h1>
                  {seller.verified && (
                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-brand-green text-xs font-semibold">
                      <CheckCircle2 className="w-3.5 h-3.5" />
                      <span>Verified Rural Artisan</span>
                    </span>
                  )}
                </div>

                <div className="flex items-center gap-3 text-xs text-gray-600 flex-wrap">
                  <span className="flex items-center gap-1">
                    <MapPin className="w-3.5 h-3.5 text-brand-orange" />
                    <span>{seller.location}, {seller.state}</span>
                  </span>
                  <span>•</span>
                  <span className="flex items-center gap-1 font-semibold text-brand-green">
                    <Users className="w-3.5 h-3.5" />
                    <span>{seller.memberCount} Artisans ({seller.type})</span>
                  </span>
                  <span>•</span>
                  <span className="flex items-center gap-1">
                    <Calendar className="w-3.5 h-3.5 text-gray-400" />
                    <span>Est. {seller.yearEstablished}</span>
                  </span>
                </div>

                <div className="flex items-center gap-2 pt-1 text-xs">
                  <div className="flex items-center gap-1 font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">
                    <Star className="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                    <span>{seller.rating}</span>
                  </div>
                  <span className="text-gray-500">({seller.reviewsTotal} customer ratings)</span>
                </div>
              </div>
            </div>

            {/* Actions */}
            <div className="flex items-center gap-3 w-full md:w-auto">
              <button
                onClick={() => showDemoNotice('Message Artisan', 'Direct rural messaging interface is in development for Phase 2.')}
                className="flex-1 md:flex-none px-5 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 shadow-sm transition-all"
              >
                <MessageCircle className="w-4 h-4" />
                <span>Contact Artisan</span>
              </button>

              <button
                onClick={() => showDemoNotice('Share Seller Shop', 'Artisan shop link copied to clipboard.')}
                className="p-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 text-gray-600"
                title="Share"
              >
                <Share2 className="w-4 h-4" />
              </button>
            </div>

          </div>
        </div>
      </div>

      {/* Seller Story & Philosophy */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-brand-sand/60 rounded-3xl p-6 sm:p-8 border border-gray-200/60 max-w-4xl space-y-3">
          <h2 className="text-base font-bold text-gray-900 font-display flex items-center gap-2">
            <ShieldCheck className="w-5 h-5 text-brand-green" />
            <span>The Artisan's Story & Heritage</span>
          </h2>
          <p className="text-xs sm:text-sm text-gray-700 leading-relaxed">
            {seller.story}
          </p>
        </div>
      </div>

      {/* Products from this Seller */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div className="flex items-center justify-between pb-3 border-b border-gray-200">
          <div className="flex items-center gap-2">
            <Package className="w-5 h-5 text-brand-orange" />
            <h2 className="text-xl font-bold text-gray-900 font-display">
              Products by {seller.name} ({sellerProducts.length})
            </h2>
          </div>
          <span className="text-xs text-brand-green font-semibold">100% Direct from Workshop</span>
        </div>

        {sellerProducts.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {sellerProducts.map(product => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        ) : (
          <div className="p-8 text-center bg-white rounded-2xl border border-gray-100 text-xs text-gray-500">
            No products listed yet under this artisan profile.
          </div>
        )}
      </div>

    </div>
  );
};
