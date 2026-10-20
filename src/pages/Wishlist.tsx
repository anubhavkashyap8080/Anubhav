import React from 'react';
import { Link } from 'react-router-dom';
import { Heart, Trash2, ShoppingBag, ArrowRight } from 'lucide-react';
import { useCartWishlist } from '../context/CartWishlistContext';
import { useDemoModal } from '../context/DemoModalContext';

export const Wishlist: React.FC = () => {
  const { wishlist, toggleWishlist, addToCart } = useCartWishlist();
  const { showDemoNotice } = useDemoModal();

  const handleMoveToCart = (product: any) => {
    addToCart(product, 1);
    toggleWishlist(product);
    showDemoNotice(
      'Move to Cart',
      `"${product.name}" moved from wishlist to your preview cart.`
    );
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      <div>
        <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
          My Saved Village Crafts & Tools ({wishlist.length})
        </h1>
        <p className="text-xs text-gray-600 mt-1">
          Items you've bookmarked to support rural self-help groups and artisans.
        </p>
      </div>

      {wishlist.length > 0 ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {wishlist.map(product => (
            <div
              key={product.id}
              className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden flex flex-col justify-between group"
            >
              <div className="relative aspect-4/3 w-full overflow-hidden bg-gray-50">
                <Link to={`/product/${product.slug}`}>
                  <img
                    src={product.image}
                    alt={product.name}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                </Link>
                <button
                  onClick={() => {
                    toggleWishlist(product);
                    showDemoNotice('Remove from Wishlist', `"${product.name}" removed from wishlist.`);
                  }}
                  className="absolute top-2.5 right-2.5 p-2 bg-white/90 rounded-full text-red-500 hover:bg-white shadow-sm transition-all"
                  title="Remove from wishlist"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>

              <div className="p-4 flex-1 flex flex-col justify-between">
                <div>
                  <span className="text-[10px] text-brand-orange font-semibold uppercase">
                    {product.category}
                  </span>
                  <Link
                    to={`/product/${product.slug}`}
                    className="block font-semibold text-sm text-gray-900 hover:text-brand-green line-clamp-2 mt-1 font-display"
                  >
                    {product.name}
                  </Link>
                  <p className="text-xs text-gray-500 mt-1">By {product.seller.name}</p>
                </div>

                <div className="pt-3 mt-3 border-t border-gray-100">
                  <div className="flex items-baseline gap-2 mb-3">
                    <span className="text-base font-bold text-gray-900">₹{product.price}</span>
                    {product.originalPrice > product.price && (
                      <span className="text-xs text-gray-400 line-through">₹{product.originalPrice}</span>
                    )}
                  </div>

                  <button
                    onClick={() => handleMoveToCart(product)}
                    className="w-full py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                  >
                    <ShoppingBag className="w-3.5 h-3.5" />
                    <span>Move to Cart (Demo)</span>
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      ) : (
        <div className="bg-white rounded-3xl border border-gray-100 p-12 text-center max-w-md mx-auto my-12 shadow-xs">
          <div className="w-16 h-16 rounded-full bg-red-50 text-red-400 flex items-center justify-center mx-auto mb-4">
            <Heart className="w-8 h-8" />
          </div>
          <h2 className="text-xl font-bold text-gray-900 font-display">Your Wishlist is Empty</h2>
          <p className="text-xs text-gray-600 mt-2 leading-relaxed">
            Discover forged farming tools, durable jute sacks, pottery, and handloom handicrafts and save your favorites here.
          </p>
          <Link
            to="/products"
            className="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-semibold rounded-xl shadow-md transition-all"
          >
            <span>Explore All Products</span>
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>
      )}
    </div>
  );
};
