import React, { useState, useMemo } from 'react';
import { useParams, useSearchParams, Link } from 'react-router-dom';
import {
  Filter,
  SlidersHorizontal,
  X,
  ChevronDown,
  Sparkles,
  RotateCcw,
  Store,
  Grid,
  Check
} from 'lucide-react';
import { mockProducts } from '../data/mockProducts';
import { mockCategories } from '../data/mockCategories';
import { ProductCard } from '../components/common/ProductCard';

export const Products: React.FC = () => {
  const { categorySlug } = useParams<{ categorySlug?: string }>();
  const [searchParams] = useSearchParams();
  const searchQuery = searchParams.get('q') || '';

  // Local filter states
  const [selectedCategory, setSelectedCategory] = useState<string>(categorySlug || 'all');
  const [selectedSellerType, setSelectedSellerType] = useState<string>('all');
  const [priceRange, setPriceRange] = useState<string>('all');
  const [inStockOnly, setInStockOnly] = useState<boolean>(false);
  const [womenShgOnly, setWomenShgOnly] = useState<boolean>(false);
  const [sortBy, setSortBy] = useState<string>('featured');
  const [mobileFiltersOpen, setMobileFiltersOpen] = useState<boolean>(false);

  // Keep category in sync if URL param changes
  React.useEffect(() => {
    if (categorySlug) {
      setSelectedCategory(categorySlug);
    }
  }, [categorySlug]);

  const currentCategoryObj = useMemo(() => {
    return mockCategories.find(c => c.slug === selectedCategory);
  }, [selectedCategory]);

  // Filtered and sorted products
  const filteredProducts = useMemo(() => {
    return mockProducts.filter(product => {
      // Category filter
      if (selectedCategory !== 'all' && product.categorySlug !== selectedCategory) {
        return false;
      }
      // Search query filter
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase();
        const matchesName = product.name.toLowerCase().includes(q);
        const matchesCategory = product.category.toLowerCase().includes(q);
        const matchesTags = product.tags.some(t => t.toLowerCase().includes(q));
        const matchesSeller = product.seller.name.toLowerCase().includes(q);
        if (!matchesName && !matchesCategory && !matchesTags && !matchesSeller) {
          return false;
        }
      }
      // Seller type filter
      if (selectedSellerType !== 'all' && product.seller.type !== selectedSellerType) {
        return false;
      }
      // Price range
      if (priceRange === 'under-250' && product.price > 250) return false;
      if (priceRange === '250-500' && (product.price < 250 || product.price > 500)) return false;
      if (priceRange === '500-1000' && (product.price < 500 || product.price > 1000)) return false;
      if (priceRange === 'over-1000' && product.price < 1000) return false;

      // In stock only
      if (inStockOnly && product.stockStatus === 'Out of Stock') return false;

      // Women SHG only
      if (womenShgOnly && !product.isRuralWomenMade) return false;

      return true;
    }).sort((a, b) => {
      if (sortBy === 'price-asc') return a.price - b.price;
      if (sortBy === 'price-desc') return b.price - a.price;
      if (sortBy === 'rating') return b.rating - a.rating;
      if (sortBy === 'discount') return b.discount - a.discount;
      // Default: featured (bestseller first)
      return (b.isBestSeller ? 1 : 0) - (a.isBestSeller ? 1 : 0);
    });
  }, [selectedCategory, searchQuery, selectedSellerType, priceRange, inStockOnly, womenShgOnly, sortBy]);

  const handleResetFilters = () => {
    setSelectedCategory('all');
    setSelectedSellerType('all');
    setPriceRange('all');
    setInStockOnly(false);
    setWomenShgOnly(false);
    setSortBy('featured');
  };

  const hasActiveFilters =
    selectedCategory !== 'all' ||
    selectedSellerType !== 'all' ||
    priceRange !== 'all' ||
    inStockOnly ||
    womenShgOnly;

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      {/* Breadcrumb & Header */}
      <div className="mb-6">
        <nav className="text-xs text-gray-500 mb-2 flex items-center gap-1.5">
          <Link to="/" className="hover:text-brand-green">Home</Link>
          <span>/</span>
          <Link to="/products" className="hover:text-brand-green">Shop</Link>
          {currentCategoryObj && (
            <>
              <span>/</span>
              <span className="text-gray-900 font-semibold">{currentCategoryObj.name}</span>
            </>
          )}
          {searchQuery && (
            <>
              <span>/</span>
              <span className="text-gray-900 font-semibold">Search: "{searchQuery}"</span>
            </>
          )}
        </nav>

        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
          <div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
              {searchQuery
                ? `Search results for "${searchQuery}"`
                : currentCategoryObj
                ? currentCategoryObj.name
                : 'All Village Products'}
            </h1>
            <p className="text-xs text-gray-600 mt-1">
              {currentCategoryObj
                ? currentCategoryObj.description
                : `Showing ${filteredProducts.length} authentic handcrafted items straight from rural clusters.`}
            </p>
          </div>

          {/* Sort & Mobile Filter Toggle */}
          <div className="flex items-center gap-3">
            <button
              onClick={() => setMobileFiltersOpen(true)}
              className="lg:hidden flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50"
            >
              <Filter className="w-4 h-4 text-brand-orange" />
              <span>Filters {hasActiveFilters && '(Active)'}</span>
            </button>

            {/* Sort Dropdown */}
            <div className="flex items-center gap-2 text-xs">
              <span className="text-gray-500 hidden sm:inline">Sort by:</span>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-green cursor-pointer"
              >
                <option value="featured">Featured / Bestsellers</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="rating">Highest Customer Rating</option>
                <option value="discount">Biggest Discount</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content Layout (Sidebar Filters + Product Grid) */}
      <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        {/* Desktop Sidebar Filters */}
        <aside className="hidden lg:block space-y-6 bg-white p-5 rounded-2xl border border-gray-100 shadow-xs h-fit sticky top-28">
          <div className="flex items-center justify-between pb-3 border-b border-gray-100">
            <div className="flex items-center gap-2 text-sm font-bold text-gray-900 font-display">
              <SlidersHorizontal className="w-4 h-4 text-brand-orange" />
              <span>Refine Products</span>
            </div>
            {hasActiveFilters && (
              <button
                onClick={handleResetFilters}
                className="text-xs text-brand-orange hover:underline font-medium flex items-center gap-1"
              >
                <RotateCcw className="w-3 h-3" />
                Reset
              </button>
            )}
          </div>

          {/* Category Filter */}
          <div>
            <h4 className="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2.5">
              Category
            </h4>
            <div className="space-y-1.5 text-xs">
              <button
                onClick={() => setSelectedCategory('all')}
                className={`w-full text-left px-2.5 py-1.5 rounded-md transition-colors flex justify-between items-center ${
                  selectedCategory === 'all'
                    ? 'bg-brand-green text-white font-semibold'
                    : 'text-gray-700 hover:bg-gray-100'
                }`}
              >
                <span>All Categories</span>
                <span className="text-[10px] opacity-80">{mockProducts.length}</span>
              </button>

              {mockCategories.map(cat => (
                <button
                  key={cat.id}
                  onClick={() => setSelectedCategory(cat.slug)}
                  className={`w-full text-left px-2.5 py-1.5 rounded-md transition-colors flex justify-between items-center ${
                    selectedCategory === cat.slug
                      ? 'bg-brand-green text-white font-semibold'
                      : 'text-gray-700 hover:bg-gray-100'
                  }`}
                >
                  <span className="truncate pr-2">{cat.name}</span>
                  <span className="text-[10px] opacity-80 shrink-0">{cat.productCount}</span>
                </button>
              ))}
            </div>
          </div>

          {/* Quick Special Toggles */}
          <div className="pt-4 border-t border-gray-100 space-y-2.5">
            <h4 className="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">
              Empowerment
            </h4>
            <label className="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
              <input
                type="checkbox"
                checked={womenShgOnly}
                onChange={(e) => setWomenShgOnly(e.target.checked)}
                className="rounded text-brand-green focus:ring-brand-green w-4 h-4"
              />
              <span className="font-medium">Handmade by Women SHGs only</span>
            </label>
            <label className="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
              <input
                type="checkbox"
                checked={inStockOnly}
                onChange={(e) => setInStockOnly(e.target.checked)}
                className="rounded text-brand-green focus:ring-brand-green w-4 h-4"
              />
              <span>In Stock Items Only</span>
            </label>
          </div>

          {/* Price Range Filter */}
          <div className="pt-4 border-t border-gray-100">
            <h4 className="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2.5">
              Price Range
            </h4>
            <div className="space-y-1.5 text-xs text-gray-700">
              {[
                { label: 'All Prices', val: 'all' },
                { label: 'Under ₹250', val: 'under-250' },
                { label: '₹250 to ₹500', val: '250-500' },
                { label: '₹500 to ₹1,000', val: '500-1000' },
                { label: 'Above ₹1,000', val: 'over-1000' },
              ].map(p => (
                <label key={p.val} className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="priceRangeDesktop"
                    checked={priceRange === p.val}
                    onChange={() => setPriceRange(p.val)}
                    className="text-brand-green focus:ring-brand-green"
                  />
                  <span>{p.label}</span>
                </label>
              ))}
            </div>
          </div>

          {/* Artisan Type Filter */}
          <div className="pt-4 border-t border-gray-100">
            <h4 className="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2.5">
              Producer Type
            </h4>
            <div className="space-y-1.5 text-xs text-gray-700">
              {[
                { label: 'All Producers', val: 'all' },
                { label: 'Women Self-Help Groups (SHG)', val: 'SHG' },
                { label: 'Hereditary Artisans', val: 'Artisan' },
                { label: 'Farmer Cooperatives', val: 'Farmer' },
                { label: 'Cottage Industries', val: 'Cottage Industry' },
              ].map(t => (
                <label key={t.val} className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="sellerTypeDesktop"
                    checked={selectedSellerType === t.val}
                    onChange={() => setSelectedSellerType(t.val)}
                    className="text-brand-green focus:ring-brand-green"
                  />
                  <span>{t.label}</span>
                </label>
              ))}
            </div>
          </div>
        </aside>

        {/* Product Grid Area */}
        <main className="lg:col-span-3">
          {filteredProducts.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredProducts.map(product => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          ) : (
            <div className="bg-white rounded-2xl border border-gray-200 p-12 text-center max-w-md mx-auto my-8">
              <div className="w-14 h-14 rounded-full bg-amber-50 text-brand-orange flex items-center justify-center mx-auto mb-4">
                <Store className="w-7 h-7" />
              </div>
              <h3 className="font-bold text-lg text-gray-900 font-display">No matching products</h3>
              <p className="text-xs text-gray-600 mt-2">
                We couldn't find items matching your active filter criteria. Try clearing some filters or searching for another term.
              </p>
              <button
                onClick={handleResetFilters}
                className="mt-5 px-5 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-lg shadow-sm"
              >
                Reset All Filters
              </button>
            </div>
          )}
        </main>
      </div>

      {/* Mobile Filters Drawer */}
      {mobileFiltersOpen && (
        <div className="lg:hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex justify-end">
          <div className="w-4/5 max-w-sm bg-white h-full shadow-2xl flex flex-col overflow-y-auto animate-in slide-in-from-right duration-200 p-6">
            <div className="flex items-center justify-between pb-4 border-b border-gray-100">
              <h3 className="font-bold text-base text-gray-900 font-display">Filter Catalog</h3>
              <button
                onClick={() => setMobileFiltersOpen(false)}
                className="p-1 rounded-full text-gray-500 hover:bg-gray-100"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="py-4 space-y-6 flex-1">
              <div>
                <h4 className="text-xs font-bold text-gray-900 uppercase mb-2">Category</h4>
                <select
                  value={selectedCategory}
                  onChange={(e) => setSelectedCategory(e.target.value)}
                  className="w-full text-xs p-2 border border-gray-300 rounded-lg"
                >
                  <option value="all">All Categories</option>
                  {mockCategories.map(c => (
                    <option key={c.id} value={c.slug}>{c.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-900 uppercase mb-2">Price Range</h4>
                <select
                  value={priceRange}
                  onChange={(e) => setPriceRange(e.target.value)}
                  className="w-full text-xs p-2 border border-gray-300 rounded-lg"
                >
                  <option value="all">All Prices</option>
                  <option value="under-250">Under ₹250</option>
                  <option value="250-500">₹250 - ₹500</option>
                  <option value="500-1000">₹500 - ₹1,000</option>
                  <option value="over-1000">Above ₹1,000</option>
                </select>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-900 uppercase mb-2">Producer Type</h4>
                <select
                  value={selectedSellerType}
                  onChange={(e) => setSelectedSellerType(e.target.value)}
                  className="w-full text-xs p-2 border border-gray-300 rounded-lg"
                >
                  <option value="all">All Producers</option>
                  <option value="SHG">Women Self-Help Groups (SHG)</option>
                  <option value="Artisan">Hereditary Artisans</option>
                  <option value="Farmer">Farmer Cooperatives</option>
                  <option value="Cottage Industry">Cottage Industries</option>
                </select>
              </div>

              <div className="space-y-2">
                <label className="flex items-center gap-2 text-xs text-gray-700">
                  <input
                    type="checkbox"
                    checked={womenShgOnly}
                    onChange={(e) => setWomenShgOnly(e.target.checked)}
                    className="rounded text-brand-green"
                  />
                  <span>Women SHG Only</span>
                </label>
                <label className="flex items-center gap-2 text-xs text-gray-700">
                  <input
                    type="checkbox"
                    checked={inStockOnly}
                    onChange={(e) => setInStockOnly(e.target.checked)}
                    className="rounded text-brand-green"
                  />
                  <span>In Stock Only</span>
                </label>
              </div>
            </div>

            <div className="pt-4 border-t border-gray-100 flex gap-2">
              <button
                onClick={() => setMobileFiltersOpen(false)}
                className="flex-1 py-2.5 bg-brand-green text-white text-xs font-semibold rounded-lg"
              >
                Apply Filters ({filteredProducts.length})
              </button>
              <button
                onClick={handleResetFilters}
                className="px-4 py-2.5 border border-gray-300 text-gray-700 text-xs rounded-lg"
              >
                Reset
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};
