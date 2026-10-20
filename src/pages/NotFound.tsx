import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { MapPin, Search, ArrowLeft, Home, Sparkles } from 'lucide-react';

export const NotFound: React.FC = () => {
  const [query, setQuery] = useState('');
  const navigate = useNavigate();

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (query.trim()) {
      navigate(`/search?q=${encodeURIComponent(query.trim())}`);
    }
  };

  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center space-y-8">
      
      <div className="w-20 h-20 rounded-3xl bg-amber-100 text-brand-orange flex items-center justify-center mx-auto shadow-sm">
        <MapPin className="w-10 h-10 animate-bounce" />
      </div>

      <div className="space-y-3">
        <span className="text-xs font-bold text-brand-orange uppercase tracking-wider">
          Error 404 • Lost in the Village Lanes
        </span>
        <h1 className="text-3xl sm:text-5xl font-extrabold text-gray-900 font-display">
          Looks Like You've Strayed Off the Path
        </h1>
        <p className="text-sm text-gray-600 max-w-md mx-auto leading-relaxed">
          The page or product you are looking for has either moved to another artisan cluster or does not exist.
        </p>
      </div>

      {/* Search Input */}
      <form onSubmit={handleSearch} className="max-w-md mx-auto relative">
        <input
          type="text"
          placeholder="Search for hand-forged tools, sickles, jute bags, pottery..."
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          className="w-full pl-10 pr-24 py-3 bg-white border border-gray-300 rounded-full text-xs shadow-xs focus:ring-2 focus:ring-brand-green"
        />
        <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <button
          type="submit"
          className="absolute right-1.5 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-brand-green text-white text-xs font-semibold rounded-full hover:bg-brand-green-dark"
        >
          Search
        </button>
      </form>

      {/* Popular Links */}
      <div className="pt-4 space-y-3">
        <p className="text-xs font-bold text-gray-500 uppercase tracking-wider">Popular Village Categories</p>
        <div className="flex flex-wrap justify-center gap-2 text-xs">
          <Link to="/category/farming-traditional-tools" className="px-3 py-1.5 bg-white border border-gray-200 rounded-lg hover:border-brand-green">
            Farming & Sickles
          </Link>
          <Link to="/category/jute-bags-cloth-jholas" className="px-3 py-1.5 bg-white border border-gray-200 rounded-lg hover:border-brand-green">
            Jute Bags & Sacks
          </Link>
          <Link to="/category/handmade-apparel-bangles" className="px-3 py-1.5 bg-white border border-gray-200 rounded-lg hover:border-brand-green">
            Lac Bangles & Apparel
          </Link>
          <Link to="/category/village-home-decor-toys" className="px-3 py-1.5 bg-white border border-gray-200 rounded-lg hover:border-brand-green">
            Home Decor & Toys
          </Link>
        </div>
      </div>

      <div className="pt-6">
        <Link
          to="/"
          className="inline-flex items-center gap-2 px-6 py-3 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-md transition-all"
        >
          <Home className="w-4 h-4" />
          <span>Return to GaunGaliHat Homepage</span>
        </Link>
      </div>

    </div>
  );
};
