import React, { useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import {
  Star,
  Heart,
  ShoppingBag,
  ShieldCheck,
  Truck,
  RotateCcw,
  MapPin,
  CheckCircle2,
  Share2,
  Sparkles,
  ArrowLeft,
  Users,
  Award,
  ChevronRight,
  Info
} from 'lucide-react';
import { usePlatform } from '../context/PlatformContext';
import { ProductCard } from '../components/common/ProductCard';
import { useCartWishlist } from '../context/CartWishlistContext';
import { useDemoModal } from '../context/DemoModalContext';

export const ProductDetail: React.FC = () => {
  const { slug } = useParams<{ slug: string }>();
  const navigate = useNavigate();
  const { addToCart, toggleWishlist, isInWishlist } = useCartWishlist();
  const { showDemoNotice } = useDemoModal();
  const { products } = usePlatform();

  const allProducts = products && products.length > 0 ? products : [];
  const product = allProducts.find(p => p.slug === slug) || allProducts[0];
  const [selectedImage, setSelectedImage] = useState<string>(product ? product.image : '');
  const [quantity, setQuantity] = useState<number>(1);
  const [activeTab, setActiveTab] = useState<'desc' | 'story' | 'reviews'>('desc');

  React.useEffect(() => {
    if (product) {
      setSelectedImage(product.image);
    }
  }, [product]);

  if (!product) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-16 text-center">
        <h2 className="text-xl font-bold text-gray-800">Product Not Found</h2>
        <Link to="/products" className="mt-4 inline-block px-4 py-2 bg-brand-green text-white rounded-lg">
          Browse Marketplace
        </Link>
      </div>
    );
  }

  const inWishlist = isInWishlist(product.id);

  // Related products from same category
  const relatedProducts = allProducts
    .filter(p => p.categorySlug === product.categorySlug && p.id !== product.id)
    .slice(0, 4);

  const handleAddToCart = () => {
    addToCart(product, quantity);
    showDemoNotice(
      'Add to Cart',
      `Added ${quantity} unit(s) of "${product.name}" to your demo cart. Checkout and real payments will be active in Phase 2.`
    );
  };

  const handleBuyNow = () => {
    showDemoNotice(
      'Buy Now',
      `Direct purchase for "${product.name}" (₹${product.price * quantity}) is available for preview only. No charges will be processed.`
    );
  };

  const handleWishlistClick = () => {
    toggleWishlist(product);
    showDemoNotice(
      inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist',
      `"${product.name}" has been ${inWishlist ? 'removed from' : 'saved to'} your preview wishlist.`
    );
  };

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({
        title: product.name,
        url: window.location.href,
      }).catch(() => {});
    } else {
      showDemoNotice('Share Product', 'Link copied to clipboard! (Demo preview mode).');
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
      
      {/* Breadcrumb Navigation */}
      <nav className="text-xs text-gray-500 flex items-center gap-1.5 flex-wrap">
        <Link to="/" className="hover:text-brand-green">Home</Link>
        <span>/</span>
        <Link to="/products" className="hover:text-brand-green">Shop</Link>
        <span>/</span>
        <Link to={`/category/${product.categorySlug}`} className="hover:text-brand-green">
          {product.category}
        </Link>
        <span>/</span>
        <span className="text-gray-900 font-semibold truncate max-w-xs">{product.name}</span>
      </nav>

      {/* Main Product Presentation Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        {/* Left: Gallery Showcase */}
        <div className="lg:col-span-6 space-y-4">
          <div className="aspect-4/3 rounded-3xl overflow-hidden bg-brand-cream border border-gray-100 shadow-md relative group">
            <img
              src={selectedImage}
              alt={product.name}
              className="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
            />
            
            {/* Badges */}
            <div className="absolute top-4 left-4 flex flex-col gap-1.5 z-10 pointer-events-none">
              {product.isBestSeller && (
                <span className="px-2.5 py-1 bg-brand-orange text-white text-[11px] font-bold uppercase rounded-md shadow-sm">
                  Bestseller
                </span>
              )}
              {product.isRuralWomenMade && (
                <span className="px-2.5 py-1 bg-brand-green text-white text-[11px] font-semibold rounded-md shadow-sm">
                  🌾 Rural Women SHG
                </span>
              )}
            </div>

            <button
              onClick={handleShare}
              className="absolute top-4 right-4 p-2.5 bg-white/90 rounded-full shadow-md text-gray-600 hover:text-brand-green transition-all"
              title="Share Product"
              aria-label="Share"
            >
              <Share2 className="w-4 h-4" />
            </button>
          </div>

          {/* Thumbnails */}
          {product.gallery.length > 1 && (
            <div className="flex items-center gap-3 overflow-x-auto pb-2">
              <button
                onClick={() => setSelectedImage(product.image)}
                className={`w-18 h-18 rounded-xl overflow-hidden border-2 shrink-0 transition-all ${
                  selectedImage === product.image ? 'border-brand-green shadow-sm' : 'border-gray-200 opacity-70'
                }`}
              >
                <img src={product.image} alt="Thumbnail main" className="w-full h-full object-cover" />
              </button>
              {product.gallery.map((img, idx) => (
                <button
                  key={idx}
                  onClick={() => setSelectedImage(img)}
                  className={`w-18 h-18 rounded-xl overflow-hidden border-2 shrink-0 transition-all ${
                    selectedImage === img ? 'border-brand-green shadow-sm' : 'border-gray-200 opacity-70'
                  }`}
                >
                  <img src={img} alt={`Thumbnail ${idx + 1}`} className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          )}

          {/* Demo Callout Notice */}
          <div className="p-3.5 bg-amber-50 rounded-xl border border-amber-200/80 text-xs text-amber-900 flex items-start gap-2.5">
            <Info className="w-4 h-4 text-brand-orange shrink-0 mt-0.5" />
            <div>
              <span className="font-bold">Prototype Demonstration Data:</span> Realistic pricing, artisan bios, and stock statuses shown for UI validation. No financial or order commitments exist on this screen.
            </div>
          </div>
        </div>

        {/* Right: Product Details & Actions */}
        <div className="lg:col-span-6 space-y-6">
          <div>
            <div className="flex items-center gap-2 mb-2">
              <span className="px-2.5 py-0.5 rounded-full bg-emerald-100 text-brand-green text-xs font-semibold">
                {product.category}
              </span>
              <span className="text-xs text-emerald-800 font-bold bg-emerald-50 px-2 py-0.5 rounded">
                {product.stockStatus}
              </span>
            </div>

            <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display leading-tight">
              {product.name}
            </h1>

            {/* Ratings & Reviews summary */}
            <div className="flex items-center gap-3 mt-3">
              <div className="flex items-center gap-1 bg-amber-50 px-2.5 py-1 rounded-md text-amber-900 font-bold text-xs border border-amber-200">
                <Star className="w-3.5 h-3.5 text-amber-500 fill-current" />
                <span>{product.rating}</span>
                <span className="text-gray-400 font-normal">/ 5.0</span>
              </div>
              <span className="text-xs text-gray-500">
                {product.reviewCount} Verified Buyer Reviews
              </span>
            </div>
          </div>

          {/* Seller Attribution Card */}
          <div className="p-4 rounded-2xl bg-brand-sand border border-gray-200/80 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <img
                src={product.seller.avatar}
                alt={product.seller.name}
                className="w-12 h-12 rounded-full object-cover border-2 border-brand-green"
              />
              <div>
                <p className="text-[11px] text-gray-500 font-medium">Handcrafted By</p>
                <div className="flex items-center gap-1.5">
                  <h3 className="font-bold text-sm text-gray-900 font-display">{product.seller.name}</h3>
                  {product.seller.verified && (
                    <CheckCircle2 className="w-3.5 h-3.5 text-brand-green shrink-0" />
                  )}
                </div>
                <div className="flex items-center gap-2 text-[11px] text-gray-600">
                  <span className="flex items-center gap-0.5">
                    <MapPin className="w-3 h-3 text-brand-orange" />
                    {product.seller.location}, {product.seller.state}
                  </span>
                  <span>•</span>
                  <span className="text-brand-green font-semibold">{product.seller.type}</span>
                </div>
              </div>
            </div>

            <Link
              to={`/seller/${product.seller.slug}`}
              className="px-3 py-1.5 text-xs font-semibold text-brand-green bg-white rounded-lg border border-gray-200 hover:border-brand-green transition-all shadow-2xs shrink-0"
            >
              Artisan Profile &rarr;
            </Link>
          </div>

          {/* Price Box */}
          <div className="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs space-y-2">
            <div className="flex items-baseline gap-3">
              <span className="text-3xl font-extrabold text-gray-900">₹{product.price}</span>
              {product.originalPrice > product.price && (
                <>
                  <span className="text-sm text-gray-400 line-through">₹{product.originalPrice}</span>
                  <span className="px-2 py-0.5 bg-orange-100 text-brand-orange text-xs font-bold rounded-md">
                    Save {product.discount}%
                  </span>
                </>
              )}
            </div>
            <p className="text-[11px] text-gray-500">
              Inclusive of all taxes. Free village dispatch shipping on orders above ₹499.
            </p>
          </div>

          {/* Quantity Selector & Action Buttons */}
          <div className="space-y-3 pt-2">
            <div className="flex items-center gap-4">
              <div className="flex items-center border border-gray-300 rounded-xl bg-white p-1">
                <button
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  className="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-gray-600 hover:bg-gray-100"
                  aria-label="Decrease quantity"
                >
                  -
                </button>
                <span className="w-10 text-center font-bold text-sm text-gray-900">
                  {quantity}
                </span>
                <button
                  onClick={() => setQuantity(quantity + 1)}
                  className="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-gray-600 hover:bg-gray-100"
                  aria-label="Increase quantity"
                >
                  +
                </button>
              </div>

              <button
                onClick={handleWishlistClick}
                className={`p-3 rounded-xl border transition-all flex items-center gap-2 text-xs font-semibold ${
                  inWishlist
                    ? 'border-red-200 bg-red-50 text-red-600'
                    : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                }`}
              >
                <Heart className={`w-4 h-4 ${inWishlist ? 'fill-current' : ''}`} />
                <span>{inWishlist ? 'Wishlisted' : 'Save to Wishlist'}</span>
              </button>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              <button
                onClick={handleAddToCart}
                className="w-full py-3.5 px-4 bg-white border-2 border-brand-green text-brand-green hover:bg-emerald-50 font-bold text-sm rounded-xl flex items-center justify-center gap-2 shadow-xs transition-all cursor-pointer"
              >
                <ShoppingBag className="w-4 h-4" />
                <span>Add to Cart</span>
              </button>

              <button
                onClick={handleBuyNow}
                className="w-full py-3.5 px-4 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-lg hover:shadow-emerald-900/20 transition-all cursor-pointer active:scale-95"
              >
                Buy Now
              </button>
            </div>
          </div>

          {/* Delivery & Assurance checklist */}
          <div className="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100 text-xs text-gray-600">
            <div className="flex items-center gap-2">
              <Truck className="w-4 h-4 text-brand-orange shrink-0" />
              <span>4-7 Days Village Dispatch</span>
            </div>
            <div className="flex items-center gap-2">
              <RotateCcw className="w-4 h-4 text-brand-orange shrink-0" />
              <span>7 Days Easy Return Policy</span>
            </div>
            <div className="flex items-center gap-2">
              <ShieldCheck className="w-4 h-4 text-brand-green shrink-0" />
              <span>100% Purity Guarantee</span>
            </div>
            <div className="flex items-center gap-2">
              <Award className="w-4 h-4 text-brand-green shrink-0" />
              <span>Handcrafted by Rural Women</span>
            </div>
          </div>
        </div>
      </div>

      {/* Tabs: Specifications, Artisan Story, Customer Reviews */}
      <div className="bg-white rounded-3xl border border-gray-100 shadow-xs overflow-hidden">
        <div className="flex border-b border-gray-200">
          <button
            onClick={() => setActiveTab('desc')}
            className={`px-6 py-4 text-xs sm:text-sm font-bold border-b-2 transition-all cursor-pointer ${
              activeTab === 'desc'
                ? 'border-brand-green text-brand-green bg-emerald-50/40'
                : 'border-transparent text-gray-500 hover:text-gray-800'
            }`}
          >
            Product Specifications
          </button>
          <button
            onClick={() => setActiveTab('story')}
            className={`px-6 py-4 text-xs sm:text-sm font-bold border-b-2 transition-all cursor-pointer ${
              activeTab === 'story'
                ? 'border-brand-green text-brand-green bg-emerald-50/40'
                : 'border-transparent text-gray-500 hover:text-gray-800'
            }`}
          >
            Artisan & Village Story
          </button>
          <button
            onClick={() => setActiveTab('reviews')}
            className={`px-6 py-4 text-xs sm:text-sm font-bold border-b-2 transition-all cursor-pointer ${
              activeTab === 'reviews'
                ? 'border-brand-green text-brand-green bg-emerald-50/40'
                : 'border-transparent text-gray-500 hover:text-gray-800'
            }`}
          >
            Buyer Reviews ({product.reviewCount})
          </button>
        </div>

        <div className="p-6 sm:p-8">
          {activeTab === 'desc' && (
            <div className="space-y-6">
              <div>
                <h3 className="font-bold text-base text-gray-900 font-display mb-2">Description</h3>
                <p className="text-sm text-gray-600 leading-relaxed">{product.description}</p>
              </div>

              <div>
                <h3 className="font-bold text-sm text-gray-900 font-display mb-3">Attributes & Details</h3>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {Object.entries(product.attributes).map(([key, val]) => (
                    <div key={key} className="p-3 bg-gray-50 rounded-xl flex justify-between text-xs">
                      <span className="font-semibold text-gray-600">{key}</span>
                      <span className="text-gray-900 font-medium text-right max-w-xs">{val}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {activeTab === 'story' && (
            <div className="space-y-4 max-w-3xl">
              <div className="p-4 bg-emerald-50/60 rounded-2xl border border-emerald-100 flex items-start gap-4">
                <Users className="w-6 h-6 text-brand-green shrink-0 mt-1" />
                <div>
                  <h3 className="font-bold text-base text-brand-green font-display">
                    Crafted with Pride in {product.seller.location}
                  </h3>
                  <p className="text-xs text-gray-600 mt-1 leading-relaxed">
                    {product.story}
                  </p>
                </div>
              </div>

              <p className="text-sm text-gray-700 leading-relaxed">
                By purchasing this item through GaunGaliHat, you are directly sustaining generational craftsmanship and rural livelihood autonomy. The entire value chain is free of exploitative middle-agents, ensuring dignified earnings returned to village households.
              </p>
            </div>
          )}

          {activeTab === 'reviews' && (
            <div className="space-y-6">
              <div className="flex flex-col sm:flex-row items-center gap-6 p-6 bg-brand-cream rounded-2xl border border-amber-100">
                <div className="text-center sm:text-left">
                  <div className="text-4xl font-extrabold text-gray-900 font-display">{product.rating}</div>
                  <div className="flex text-amber-400 mt-1">
                    {'★★★★★'.split('').map((s, i) => (
                      <span key={i}>{s}</span>
                    ))}
                  </div>
                  <p className="text-xs text-gray-500 mt-1">Based on {product.reviewCount} customer reviews</p>
                </div>
                <div className="flex-1 w-full text-xs space-y-1.5">
                  <div className="flex items-center gap-2">
                    <span className="w-12 text-gray-500">5 Star</span>
                    <div className="flex-1 bg-gray-200 h-2 rounded-full overflow-hidden">
                      <div className="bg-emerald-600 h-full w-[85%]" />
                    </div>
                    <span className="text-gray-500 w-8">85%</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <span className="w-12 text-gray-500">4 Star</span>
                    <div className="flex-1 bg-gray-200 h-2 rounded-full overflow-hidden">
                      <div className="bg-emerald-600 h-full w-[12%]" />
                    </div>
                    <span className="text-gray-500 w-8">12%</span>
                  </div>
                </div>
              </div>

              {/* Sample Reviews */}
              <div className="divide-y divide-gray-100 space-y-4 pt-2">
                <div className="pt-4 space-y-1.5">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className="font-bold text-xs text-gray-900">Kavita Sharma</span>
                      <span className="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded font-semibold">
                        Verified Purchase
                      </span>
                    </div>
                    <span className="text-[10px] text-gray-400">2 weeks ago</span>
                  </div>
                  <div className="flex text-amber-400 text-xs">★★★★★</div>
                  <p className="text-xs text-gray-600 leading-relaxed">
                    "Exceptional authentic quality! You can immediately smell and feel the purity compared to commercial market brands. Will definitely order regularly."
                  </p>
                </div>

                <div className="pt-4 space-y-1.5">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className="font-bold text-xs text-gray-900">Amitabh Sengupta</span>
                      <span className="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded font-semibold">
                        Verified Purchase
                      </span>
                    </div>
                    <span className="text-[10px] text-gray-400">1 month ago</span>
                  </div>
                  <div className="flex text-amber-400 text-xs">★★★★★</div>
                  <p className="text-xs text-gray-600 leading-relaxed">
                    "Delivered well packed with eco-friendly jute cushions. Supporting rural artisans makes this purchase even more worthwhile."
                  </p>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Related Products Section */}
      {relatedProducts.length > 0 && (
        <section className="space-y-6">
          <div className="flex items-center justify-between">
            <h2 className="text-xl sm:text-2xl font-bold text-gray-900 font-display">
              You May Also Like
            </h2>
            <Link
              to={`/category/${product.categorySlug}`}
              className="text-xs font-semibold text-brand-green hover:underline"
            >
              View More in {product.category} &rarr;
            </Link>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {relatedProducts.map(p => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        </section>
      )}

    </div>
  );
};
