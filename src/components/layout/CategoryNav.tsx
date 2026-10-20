import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import {
  Cookie,
  Milk,
  Sparkles,
  ShoppingBag,
  Shirt,
  Home,
  Wrench,
  Heart,
  Grid,
  Flame,
  MapPin
} from 'lucide-react';
import { mockCategories } from '../../data/mockCategories';

const iconMap: Record<string, React.ReactNode> = {
  Cookie: <Cookie className="w-4 h-4" />,
  Milk: <Milk className="w-4 h-4" />,
  Sparkles: <Sparkles className="w-4 h-4" />,
  ShoppingBag: <ShoppingBag className="w-4 h-4" />,
  Shirt: <Shirt className="w-4 h-4" />,
  Home: <Home className="w-4 h-4" />,
  Wrench: <Wrench className="w-4 h-4" />,
  Heart: <Heart className="w-4 h-4" />,
};

export const CategoryNav: React.FC = () => {
  const location = useLocation();

  return (
    <nav className="bg-brand-sand border-b border-gray-200/80 shadow-xs w-full max-w-full overflow-hidden">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full min-w-0">
        <div className="flex items-center space-x-1 sm:space-x-4 overflow-x-auto py-2.5 scrollbar-none text-xs font-medium text-gray-700 whitespace-nowrap w-full min-w-0">
          
          {/* All Products pill */}
          <Link
            to="/products"
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full transition-all shrink-0 ${
              location.pathname === '/products'
                ? 'bg-brand-green text-white font-semibold shadow-xs'
                : 'hover:bg-brand-cream hover:text-brand-green text-gray-800'
            }`}
          >
            <Grid className="w-3.5 h-3.5" />
            <span>All Products</span>
          </Link>

          {/* Category Links */}
          {mockCategories.map((cat) => {
            const isActive = location.pathname === `/category/${cat.slug}`;
            return (
              <Link
                key={cat.id}
                to={`/category/${cat.slug}`}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full transition-all shrink-0 ${
                  isActive
                    ? 'bg-brand-green text-white font-semibold shadow-xs'
                    : 'hover:bg-brand-cream hover:text-brand-green text-gray-800'
                }`}
              >
                <span className={isActive ? 'text-amber-200' : 'text-brand-orange'}>
                  {iconMap[cat.iconName] || <Sparkles className="w-3.5 h-3.5" />}
                </span>
                <span>{cat.name}</span>
                {cat.slug === 'handmade-by-rural-women' && (
                  <span className="ml-0.5 px-1.5 py-0.2 bg-red-100 text-red-700 text-[10px] font-bold rounded-full">
                    SHG
                  </span>
                )}
              </Link>
            );
          })}

          {/* Nearby Artisans link */}
          <Link
            to="/nearby-artisans"
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full transition-all shrink-0 ${
              location.pathname === '/nearby-artisans'
                ? 'bg-brand-green text-white font-semibold shadow-xs'
                : 'hover:bg-emerald-100/70 text-brand-green font-semibold border border-emerald-300/60'
            }`}
          >
            <MapPin className="w-3.5 h-3.5 text-brand-orange" />
            <span>Nearby Artisans (Map)</span>
          </Link>

          {/* Blog stories link */}
          <Link
            to="/blog"
            className={`flex items-center gap-1 px-3 py-1.5 rounded-full transition-all shrink-0 ${
              location.pathname.startsWith('/blog')
                ? 'bg-brand-orange text-white font-semibold shadow-xs'
                : 'hover:bg-orange-100/60 text-brand-orange font-semibold'
            }`}
          >
            <Flame className="w-3.5 h-3.5" />
            <span>Village Stories</span>
          </Link>
        </div>
      </div>
    </nav>
  );
};
