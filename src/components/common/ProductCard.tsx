import React from 'react';
import { Link } from 'react-router-dom';
import { Heart, Star, ShoppingBag, MapPin, CheckCircle2, Eye } from 'lucide-react';
import { Product } from '../../types';
import { useCartWishlist } from '../../context/CartWishlistContext';
import { useDemoModal } from '../../context/DemoModalContext';

interface ProductCardProps {
  product: Product;
}

export const ProductCard: React.FC<ProductCardProps> = ({ product }) => {
  const { isInWishlist, toggleWishlist, addToCart } = useCartWishlist();
  const { showDemoNotice } = useDemoModal();
  const inWishlist = isInWishlist(product.id);

  const handleWishlistClick = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    // Toggle in local state for UI responsiveness, and inform via demo notice
    toggleWishlist(product);
    showDemoNotice(
      inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist',
      `${product.name} ${inWishlist ? 'removed from' : 'saved to'} your preview wishlist.`
    );
  };

  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    // Also add to local cart so user can view it in /cart
    addToCart(product, 1);
    showDemoNotice(
      'Add to Cart',
      `"${product.name}" added to local preview cart. Live order processing and payment gateways will be implemented in Phase 2.`
    );
  };

  const handleBuyNow = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    showDemoNotice(
      'Buy Now / Direct Checkout',
      `Checkout for "${product.name}" (₹${product.price}) is available for preview only. No real payment or charges will occur.`
    );
  };

  return (
    <div className="group bg-white rounded-2xl border border-gray-100/90 hover:border-emerald-200 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden relative">
      
      {/* Product Image Area */}
      <div className="relative aspect-4/3 w-full overflow-hidden bg-brand-cream/50">
        <Link to={`/product/${product.slug}`} className="block w-full h-full">
          <img
            src={product.image}
            alt={product.name}
            className="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
          />
        </Link>

        {/* Top Badges */}
        <div className="absolute top-2.5 left-2.5 flex flex-col gap-1 items-start z-10 pointer-events-none">
          {product.isBestSeller && (
            <span className="px-2 py-0.5 bg-brand-orange text-white text-[10px] font-bold uppercase rounded-md tracking-wider shadow-xs">
              Bestseller
            </span>
          )}
          {product.isRuralWomenMade && (
            <span className="px-2 py-0.5 bg-brand-green text-white text-[10px] font-semibold rounded-md shadow-xs flex items-center gap-1">
              <span>🌾 Women SHG</span>
            </span>
          )}
          {product.discount > 0 && (
            <span className="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-md">
              {product.discount}% OFF
            </span>
          )}
        </div>

        {/* Wishlist Button */}
        <button
          onClick={handleWishlistClick}
          className={`absolute top-2.5 right-2.5 p-2 rounded-full shadow-md transition-all z-10 cursor-pointer ${
            inWishlist
              ? 'bg-red-50 text-red-500 hover:bg-red-100'
              : 'bg-white/90 text-gray-500 hover:text-red-500 hover:bg-white'
          }`}
          aria-label={inWishlist ? "Remove from wishlist" : "Add to wishlist"}
          title="Wishlist"
        >
          <Heart className={`w-4 h-4 ${inWishlist ? 'fill-current' : ''}`} />
        </button>

        {/* Quick View Link on Hover */}
        <Link
          to={`/product/${product.slug}`}
          className="absolute inset-x-4 bottom-2.5 py-1.5 bg-white/95 backdrop-blur-xs text-gray-800 text-xs font-semibold rounded-lg text-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-md flex items-center justify-center gap-1.5 hover:bg-brand-green hover:text-white"
        >
          <Eye className="w-3.5 h-3.5" />
          <span>Quick View</span>
        </Link>
      </div>

      {/* Card Content */}
      <div className="p-4 flex-1 flex flex-col justify-between">
        <div>
          {/* Category & Seller info */}
          <div className="flex items-center justify-between gap-1 text-[11px] text-gray-500 mb-1">
            <span className="truncate text-brand-orange font-medium">{product.category}</span>
            <span className="shrink-0 flex items-center gap-0.5 text-gray-400">
              <MapPin className="w-3 h-3 text-brand-green" />
              <span>{product.seller.state}</span>
            </span>
          </div>

          {/* Product Title */}
          <Link
            to={`/product/${product.slug}`}
            className="block font-semibold text-sm text-gray-900 group-hover:text-brand-green transition-colors line-clamp-2 leading-snug mb-2 font-display"
          >
            {product.name}
          </Link>

          {/* Seller Attribution */}
          <Link
            to={`/seller/${product.seller.slug}`}
            className="inline-flex items-center gap-1 text-xs text-gray-600 hover:text-brand-green mb-2.5"
          >
            <span className="text-[11px] text-gray-400">By</span>
            <span className="font-medium text-gray-800 hover:underline truncate max-w-[170px]">
              {product.seller.name}
            </span>
            {product.seller.verified && (
              <CheckCircle2 className="w-3 h-3 text-brand-green shrink-0" />
            )}
          </Link>

          {/* Ratings & Stock Status */}
          <div className="flex items-center justify-between text-xs mb-3">
            <div className="flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded text-amber-900 font-semibold text-[11px]">
              <Star className="w-3 h-3 text-amber-500 fill-current" />
              <span>{product.rating}</span>
              <span className="text-gray-400 text-[10px]">({product.reviewCount})</span>
            </div>

            <span className="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
              {product.stockStatus}
            </span>
          </div>
        </div>

        {/* Pricing & CTA Actions */}
        <div className="pt-2 border-t border-gray-100">
          <div className="flex items-baseline gap-2 mb-3">
            <span className="text-lg font-bold text-gray-900">
              ₹{product.price}
            </span>
            {product.originalPrice > product.price && (
              <span className="text-xs text-gray-400 line-through">
                ₹{product.originalPrice}
              </span>
            )}
          </div>

          <div className="grid grid-cols-2 gap-2">
            <button
              onClick={handleAddToCart}
              className="w-full py-2 px-2 rounded-lg border border-brand-green text-brand-green hover:bg-emerald-50 text-xs font-semibold flex items-center justify-center gap-1 transition-colors cursor-pointer"
            >
              <ShoppingBag className="w-3.5 h-3.5" />
              <span>Add to Cart</span>
            </button>

            <button
              onClick={handleBuyNow}
              className="w-full py-2 px-2 rounded-lg bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold shadow-xs transition-all cursor-pointer"
            >
              Buy Now
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
