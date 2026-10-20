import React from 'react';
import { Link } from 'react-router-dom';
import {
  ArrowRight,
  Sparkles,
  Heart,
  ShieldCheck,
  Truck,
  Leaf,
  Users,
  Award,
  ChevronRight,
  Star,
  CheckCircle2,
  MapPin,
  Clock,
  ArrowUpRight,
  TrendingUp,
  Flame,
  HeartHandshake
} from 'lucide-react';
import { mockProducts } from '../data/mockProducts';
import { mockCategories } from '../data/mockCategories';
import { mockSellers } from '../data/mockSellers';
import { mockBlogPosts } from '../data/mockBlogPosts';
import { ProductCard } from '../components/common/ProductCard';
import { useDemoModal } from '../context/DemoModalContext';

export const Home: React.FC = () => {
  const { showDemoNotice } = useDemoModal();

  // Filter specific product sets
  const bestsellers = mockProducts.filter(p => p.isBestSeller).slice(0, 8);
  const homemadeFoods = mockProducts.filter(p => p.categorySlug === 'papad-pickles-snacks' || p.categorySlug === 'dairy-ghee-grains').slice(0, 4);
  const womenHandmade = mockProducts.filter(p => p.isRuralWomenMade).slice(0, 4);
  const farmingTools = mockProducts.filter(p => p.categorySlug === 'farming-traditional-tools').slice(0, 4);
  const recentArticles = mockBlogPosts.slice(0, 3);

  return (
    <div className="space-y-16 pb-16">
      
      {/* 7. HERO BANNER */}
      <section className="relative overflow-hidden bg-gradient-to-br from-emerald-900 via-brand-green to-emerald-950 text-white">
        {/* Decorative background elements */}
        <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none" />
        <div className="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-brand-orange/20 blur-3xl pointer-events-none" />
        <div className="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none" />

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 relative z-10">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            {/* Text & CTAs */}
            <div className="lg:col-span-7 space-y-6 text-center lg:text-left">
              <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-800/80 border border-emerald-600/60 text-amber-300 text-xs font-semibold shadow-xs">
                <Sparkles className="w-3.5 h-3.5" />
                <span>Empowering 5,000+ Rural Women Artisans & Farmers</span>
              </div>

              <h1 className="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight font-display leading-tight sm:leading-none">
                Gaon ke hunar, <br />
                <span className="text-amber-300 drop-shadow-sm">ab har ghar tak</span>
              </h1>

              <p className="text-base sm:text-lg text-emerald-100/90 max-w-2xl leading-relaxed mx-auto lg:mx-0">
                Taste the untamed purity of Vedic Bilona A2 ghee, heirloom sun-dried pickles, 
                and stone-pounded spices. Discover hand-forged tools and artisanal craft 
                created by rural women self-help groups across Bharat.
              </p>

              {/* Action Buttons */}
              <div className="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-2">
                <Link
                  to="/products"
                  className="px-6 py-3.5 rounded-xl bg-brand-orange hover:bg-brand-orange-dark text-white font-semibold text-sm shadow-lg hover:shadow-orange-600/30 transition-all flex items-center gap-2 active:scale-95"
                >
                  <span>Explore Village Marketplace</span>
                  <ArrowRight className="w-4 h-4" />
                </Link>

                <Link
                  to="/category/papad-pickles-snacks"
                  className="px-6 py-3.5 rounded-xl bg-emerald-800/80 hover:bg-emerald-700/80 border border-emerald-600/80 text-white font-semibold text-sm transition-all flex items-center gap-2"
                >
                  <span>Shop Desi Foods</span>
                </Link>

                <Link
                  to="/become-seller"
                  className="px-4 py-3.5 text-xs text-amber-200 hover:text-white font-medium underline underline-offset-4 flex items-center gap-1"
                >
                  <span>Join as an Artisan</span>
                  <ArrowUpRight className="w-3.5 h-3.5" />
                </Link>
              </div>

              {/* Trust badges */}
              <div className="grid grid-cols-3 gap-4 pt-6 border-t border-emerald-800/60 text-left">
                <div>
                  <div className="text-xl sm:text-2xl font-bold text-amber-300 font-display">100%</div>
                  <div className="text-[11px] text-emerald-200/80">Authentic & Pure Desi</div>
                </div>
                <div>
                  <div className="text-xl sm:text-2xl font-bold text-amber-300 font-display">40+</div>
                  <div className="text-[11px] text-emerald-200/80">Rural Clusters</div>
                </div>
                <div>
                  <div className="text-xl sm:text-2xl font-bold text-amber-300 font-display">0%</div>
                  <div className="text-[11px] text-emerald-200/80">Middleman Commission</div>
                </div>
              </div>
            </div>

            {/* Hero Visual Card Showcase */}
            <div className="lg:col-span-5 relative">
              <div className="relative mx-auto max-w-sm rounded-3xl bg-white/10 backdrop-blur-md p-4 border border-white/20 shadow-2xl">
                <img
                  src="https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=800&q=80"
                  alt="Rural Indian women artisan"
                  className="w-full h-80 object-cover rounded-2xl shadow-md"
                />

                {/* Floating pill 1 */}
                <div className="absolute -top-4 -left-4 bg-white text-gray-900 rounded-xl p-3 shadow-xl border border-gray-100 flex items-center gap-3 animate-soft-pulse">
                  <div className="w-10 h-10 rounded-lg bg-orange-100 text-brand-orange flex items-center justify-center font-bold">
                    🌾
                  </div>
                  <div>
                    <p className="text-[10px] text-gray-500 font-medium">Grandmother's Recipe</p>
                    <p className="text-xs font-bold text-gray-800">Moong Dal Papad</p>
                  </div>
                </div>

                {/* Floating pill 2 */}
                <div className="absolute -bottom-4 -right-4 bg-brand-green text-white rounded-xl p-3 shadow-xl border border-emerald-500 flex items-center gap-3">
                  <div className="w-10 h-10 rounded-lg bg-emerald-800 flex items-center justify-center text-amber-300 font-bold">
                    ★ 4.98
                  </div>
                  <div>
                    <p className="text-[10px] text-emerald-200 font-medium">Gir Cow Vedic Bilona</p>
                    <p className="text-xs font-bold text-white">Pure Desi Ghee</p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>

      {/* 8. SHOP BY CATEGORY */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
          <div>
            <div className="flex items-center gap-1.5 text-xs font-bold text-brand-orange uppercase tracking-wider mb-1">
              <Sparkles className="w-4 h-4" />
              <span>Explore Village Craft & Harvest</span>
            </div>
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
              Shop by Category
            </h2>
          </div>
          <Link
            to="/products"
            className="mt-2 sm:mt-0 text-xs font-semibold text-brand-green hover:text-brand-green-dark flex items-center gap-1 group"
          >
            <span>View All Categories</span>
            <ChevronRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
          {mockCategories.map((cat) => (
            <Link
              key={cat.id}
              to={`/category/${cat.slug}`}
              className="group relative rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col"
            >
              <div className="aspect-4/3 w-full overflow-hidden bg-gray-100">
                <img
                  src={cat.image}
                  alt={cat.name}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
              </div>
              <div className="p-4 flex-1 flex flex-col justify-between">
                <div>
                  <h3 className="font-bold text-sm text-gray-900 group-hover:text-brand-green transition-colors font-display">
                    {cat.name}
                  </h3>
                  <p className="text-xs text-gray-500 line-clamp-2 mt-1">
                    {cat.description}
                  </p>
                </div>
                <div className="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                  <span className="text-gray-400 font-medium">{cat.productCount} Items</span>
                  <span className="text-brand-green font-semibold group-hover:translate-x-1 transition-transform inline-flex items-center">
                    Browse &rarr;
                  </span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      </section>

      {/* 9. BESTSELLING PRODUCTS */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
          <div>
            <div className="flex items-center gap-1.5 text-xs font-bold text-brand-orange uppercase tracking-wider mb-1">
              <Flame className="w-4 h-4" />
              <span>Beloved Across Bharat</span>
            </div>
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
              Bestselling Products
            </h2>
          </div>
          <Link
            to="/products"
            className="mt-2 sm:mt-0 text-xs font-semibold text-brand-green hover:text-brand-green-dark flex items-center gap-1 group"
          >
            <span>See All Bestsellers</span>
            <ChevronRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {bestsellers.map(product => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      </section>

      {/* PROMO BANNER: Women SHG & Bilona Ghee Special */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="rounded-3xl bg-gradient-to-r from-amber-700 via-brand-orange to-amber-800 text-white p-8 sm:p-12 relative overflow-hidden shadow-xl">
          <div className="max-w-xl relative z-10 space-y-4">
            <span className="px-3 py-1 rounded-full bg-white/20 backdrop-blur-xs text-xs font-semibold uppercase tracking-wider">
              Special Artisan Direct Initiative
            </span>
            <h3 className="text-2xl sm:text-4xl font-extrabold font-display leading-tight">
              100% Sun-Dried Pickles & Authentic Wood-Pressed Oils
            </h3>
            <p className="text-sm text-amber-100 leading-relaxed">
              Every jar bought directly finances daughters' education and healthcare in Barabanki and Malwa villages. Experience the unadulterated taste of grandfather’s era.
            </p>
            <div className="pt-2">
              <Link
                to="/category/papad-pickles-snacks"
                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-gray-900 font-bold text-xs hover:bg-amber-50 shadow-md transition-all active:scale-95"
              >
                <span>Shop Traditional Harvest</span>
                <ArrowRight className="w-4 h-4" />
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* 10. HOMEMADE & TRADITIONAL FOODS */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
          <div>
            <div className="flex items-center gap-1.5 text-xs font-bold text-brand-green uppercase tracking-wider mb-1">
              <Leaf className="w-4 h-4" />
              <span>Pure & Organic Country Pantry</span>
            </div>
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
              Homemade & Traditional Foods
            </h2>
            <p className="text-xs text-gray-500 mt-1">Sun-dried papad, slow-matured pickles, stone-ground flours, and pure gur.</p>
          </div>
          <Link
            to="/category/papad-pickles-snacks"
            className="mt-2 sm:mt-0 text-xs font-semibold text-brand-green hover:text-brand-green-dark flex items-center gap-1 group"
          >
            <span>Explore Foods</span>
            <ChevronRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {homemadeFoods.map(product => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      </section>

      {/* 11. HANDMADE BY RURAL WOMEN */}
      <section className="bg-brand-sand/60 py-16 border-y border-gray-200/70">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
              <div className="flex items-center gap-1.5 text-xs font-bold text-brand-orange uppercase tracking-wider mb-1">
                <Heart className="w-4 h-4 fill-brand-orange" />
                <span>Self-Help Group (SHG) Creations</span>
              </div>
              <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
                Handmade by Rural Women
              </h2>
              <p className="text-xs text-gray-600 mt-1">Golden jute bags, lac bangles, kantha embroidered pouches, and terracotta crafts.</p>
            </div>
            <Link
              to="/category/handmade-by-rural-women"
              className="mt-2 sm:mt-0 text-xs font-semibold text-brand-green hover:text-brand-green-dark flex items-center gap-1 group"
            >
              <span>Explore SHG Products</span>
              <ChevronRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
            </Link>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {womenHandmade.map(product => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        </div>
      </section>

      {/* 12. FARMING & HOUSEHOLD TOOLS */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
          <div>
            <div className="flex items-center gap-1.5 text-xs font-bold text-brand-green uppercase tracking-wider mb-1">
              <Award className="w-4 h-4" />
              <span>Hand-Forged Iron & High-Carbon Steel</span>
            </div>
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
              Farming & Household Tools
            </h2>
            <p className="text-xs text-gray-500 mt-1">Built to endure decades. Desi hansiya, garden khurpis, fawda, and hammers.</p>
          </div>
          <Link
            to="/category/farming-traditional-tools"
            className="mt-2 sm:mt-0 text-xs font-semibold text-brand-green hover:text-brand-green-dark flex items-center gap-1 group"
          >
            <span>Explore Tools</span>
            <ChevronRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {farmingTools.map(product => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      </section>

      {/* 13. MEET OUR SELLERS */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-10">
          <span className="text-xs font-bold text-brand-orange uppercase tracking-wider">
            Faces of Rural Resilience
          </span>
          <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display mt-1">
            Meet Our Rural Sellers & SHG Leaders
          </h2>
          <p className="text-xs sm:text-sm text-gray-600 mt-2">
            Every product on GanvGaliHut comes with a name, a village, and a livelihood story.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {mockSellers.slice(0, 4).map((seller) => (
            <div
              key={seller.id}
              className="bg-white rounded-2xl border border-gray-100 shadow-xs hover:shadow-lg transition-all p-5 flex flex-col justify-between"
            >
              <div>
                <div className="flex items-center gap-3 mb-3">
                  <img
                    src={seller.avatar}
                    alt={seller.name}
                    className="w-12 h-12 rounded-full object-cover border-2 border-emerald-100"
                  />
                  <div>
                    <h3 className="font-bold text-sm text-gray-900 font-display line-clamp-1">
                      {seller.name}
                    </h3>
                    <div className="flex items-center gap-1 text-[11px] text-gray-500">
                      <MapPin className="w-3 h-3 text-brand-orange" />
                      <span className="truncate">{seller.location}</span>
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-2 mb-3">
                  <span className="px-2 py-0.5 rounded bg-emerald-50 text-brand-green font-semibold text-[10px]">
                    {seller.type} ({seller.memberCount} Artisans)
                  </span>
                  <div className="flex items-center gap-0.5 text-xs font-bold text-amber-600">
                    <Star className="w-3 h-3 fill-amber-400 text-amber-400" />
                    <span>{seller.rating}</span>
                  </div>
                </div>

                <p className="text-xs text-gray-600 line-clamp-3 leading-relaxed">
                  {seller.story}
                </p>
              </div>

              <div className="pt-4 mt-4 border-t border-gray-100">
                <Link
                  to={`/seller/${seller.slug}`}
                  className="w-full py-2 bg-brand-cream hover:bg-emerald-50 text-brand-green font-semibold text-xs rounded-lg border border-emerald-200/60 flex items-center justify-center gap-1 transition-colors"
                >
                  <span>Visit Artisan Shop</span>
                  <ChevronRight className="w-3.5 h-3.5" />
                </Link>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* 14. WHY GANVGALIHUT? */}
      <section className="bg-brand-cream py-16 border-y border-amber-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-12">
            <span className="text-xs font-bold text-brand-green uppercase tracking-wider">
              The GanvGaliHut Difference
            </span>
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display mt-1">
              Why Shop on GanvGaliHut?
            </h2>
            <p className="text-xs sm:text-sm text-gray-600 mt-2">
              We bridge the gap between rural craftsmanship and modern urban kitchens with complete honesty.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="bg-white p-6 rounded-2xl shadow-xs border border-gray-100 space-y-3">
              <div className="w-12 h-12 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center">
                <HeartHandshake className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-base text-gray-900 font-display">
                100% Direct Village Sourcing
              </h3>
              <p className="text-xs text-gray-600 leading-relaxed">
                No corporate wholesalers. Every item is packed at the artisan or SHG village unit, ensuring maximum profits stay in rural pockets.
              </p>
            </div>

            <div className="bg-white p-6 rounded-2xl shadow-xs border border-gray-100 space-y-3">
              <div className="w-12 h-12 rounded-xl bg-orange-50 text-brand-orange flex items-center justify-center">
                <Leaf className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-base text-gray-900 font-display">
                Traditional Heirloom Purity
              </h3>
              <p className="text-xs text-gray-600 leading-relaxed">
                Zero artificial coloring, chemical bleaching, or industrial adulteration. Cold-pressed oils, unpolished pulses, and sun-dried spices.
              </p>
            </div>

            <div className="bg-white p-6 rounded-2xl shadow-xs border border-gray-100 space-y-3">
              <div className="w-12 h-12 rounded-xl bg-emerald-50 text-brand-green flex items-center justify-center">
                <Users className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-base text-gray-900 font-display">
                Women SHG Empowerment
              </h3>
              <p className="text-xs text-gray-600 leading-relaxed">
                Over 70% of our marketplace catalog is produced by women-led self-help groups, driving dignified financial independence.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* 15. HOW IT WORKS */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-12">
          <span className="text-xs font-bold text-brand-orange uppercase tracking-wider">
            Transparent Supply Flow
          </span>
          <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display mt-1">
            How GanvGaliHut Works
          </h2>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative">
          <div className="p-6 rounded-2xl bg-white border border-gray-100 shadow-xs relative">
            <span className="text-4xl font-extrabold text-emerald-100 font-display absolute top-4 right-4">
              01
            </span>
            <div className="w-10 h-10 rounded-full bg-emerald-100 text-brand-green font-bold flex items-center justify-center text-sm mb-4">
              1
            </div>
            <h3 className="font-bold text-sm text-gray-900 font-display mb-2">Artisan Handcrafts</h3>
            <p className="text-xs text-gray-600 leading-relaxed">
              Rural women, ironsmiths, and organic farmers harvest and handcraft goods using generations-old wisdom.
            </p>
          </div>

          <div className="p-6 rounded-2xl bg-white border border-gray-100 shadow-xs relative">
            <span className="text-4xl font-extrabold text-emerald-100 font-display absolute top-4 right-4">
              02
            </span>
            <div className="w-10 h-10 rounded-full bg-orange-100 text-brand-orange font-bold flex items-center justify-center text-sm mb-4">
              2
            </div>
            <h3 className="font-bold text-sm text-gray-900 font-display mb-2">Purity Verification</h3>
            <p className="text-xs text-gray-600 leading-relaxed">
              Batches are tested for zero adulteration and sustainably packaged in eco-friendly burlap and cardboard.
            </p>
          </div>

          <div className="p-6 rounded-2xl bg-white border border-gray-100 shadow-xs relative">
            <span className="text-4xl font-extrabold text-emerald-100 font-display absolute top-4 right-4">
              03
            </span>
            <div className="w-10 h-10 rounded-full bg-emerald-100 text-brand-green font-bold flex items-center justify-center text-sm mb-4">
              3
            </div>
            <h3 className="font-bold text-sm text-gray-900 font-display mb-2">Village to Doorstep</h3>
            <p className="text-xs text-gray-600 leading-relaxed">
              Dispatched straight from the rural center to your home with real-time tracking across all Indian pincodes.
            </p>
          </div>

          <div className="p-6 rounded-2xl bg-white border border-gray-100 shadow-xs relative">
            <span className="text-4xl font-extrabold text-emerald-100 font-display absolute top-4 right-4">
              04
            </span>
            <div className="w-10 h-10 rounded-full bg-orange-100 text-brand-orange font-bold flex items-center justify-center text-sm mb-4">
              4
            </div>
            <h3 className="font-bold text-sm text-gray-900 font-display mb-2">Fair Direct Earnings</h3>
            <p className="text-xs text-gray-600 leading-relaxed">
              Payment is credited directly to the artisan's bank account, transforming village economies permanently.
            </p>
          </div>
        </div>
      </section>

      {/* 16. DEMO TESTIMONIALS */}
      <section className="bg-brand-green text-white py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-10">
            <span className="text-xs font-bold text-amber-300 uppercase tracking-wider">
              Customer Love
            </span>
            <h2 className="text-2xl sm:text-3xl font-bold text-white font-display mt-1">
              What Conscious Buyers Say
            </h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-emerald-900/60 p-6 rounded-2xl border border-emerald-700/60 space-y-4">
              <div className="flex text-amber-400 gap-1">
                {'★★★★★'.split('').map((s, i) => (
                  <span key={i}>{s}</span>
                ))}
              </div>
              <p className="text-xs text-emerald-100 leading-relaxed italic">
                "The Vedic A2 Gir Cow Ghee brought tears to my mother’s eyes. It smelled exactly like the ghee her grandmother used to churn in our ancestral village 40 years ago."
              </p>
              <div className="pt-2 border-t border-emerald-800">
                <p className="font-bold text-xs text-white">Ananya Mukherjee</p>
                <p className="text-[10px] text-emerald-300">Bangalore, Verified Buyer</p>
              </div>
            </div>

            <div className="bg-emerald-900/60 p-6 rounded-2xl border border-emerald-700/60 space-y-4">
              <div className="flex text-amber-400 gap-1">
                {'★★★★★'.split('').map((s, i) => (
                  <span key={i}>{s}</span>
                ))}
              </div>
              <p className="text-xs text-emerald-100 leading-relaxed italic">
                "Ramkishan’s hand-forged Desi Hansiya is razor sharp and sturdy. I use it for my organic terrace farm in Pune. Commercial garden tools don’t stand a chance against it."
              </p>
              <div className="pt-2 border-t border-emerald-800">
                <p className="font-bold text-xs text-white">Dr. Rajesh Patel</p>
                <p className="text-[10px] text-emerald-300">Pune, Urban Farmer</p>
              </div>
            </div>

            <div className="bg-emerald-900/60 p-6 rounded-2xl border border-emerald-700/60 space-y-4">
              <div className="flex text-amber-400 gap-1">
                {'★★★★★'.split('').map((s, i) => (
                  <span key={i}>{s}</span>
                ))}
              </div>
              <p className="text-xs text-emerald-100 leading-relaxed italic">
                "We ordered 50 heavy-duty jute bags as wedding return gifts from the Sundarban women’s cooperative. The craftsmanship, stitching, and feel were world-class."
              </p>
              <div className="pt-2 border-t border-emerald-800">
                <p className="font-bold text-xs text-white">Meenakshi & Siddharth</p>
                <p className="text-[10px] text-emerald-300">Delhi NCR</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 17. BLOG HIGHLIGHTS */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
          <div>
            <div className="flex items-center gap-1.5 text-xs font-bold text-brand-orange uppercase tracking-wider mb-1">
              <Sparkles className="w-4 h-4" />
              <span>Village Stories & Insights</span>
            </div>
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
              Latest from the Village Journal
            </h2>
          </div>
          <Link
            to="/blog"
            className="mt-2 sm:mt-0 text-xs font-semibold text-brand-green hover:text-brand-green-dark flex items-center gap-1 group"
          >
            <span>Read All Articles</span>
            <ChevronRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {recentArticles.map(post => (
            <Link
              key={post.id}
              to={`/blog/${post.slug}`}
              className="group bg-white rounded-2xl border border-gray-100 shadow-xs hover:shadow-lg transition-all overflow-hidden flex flex-col"
            >
              <div className="aspect-16/9 w-full overflow-hidden bg-gray-100">
                <img
                  src={post.image}
                  alt={post.title}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
              </div>
              <div className="p-5 flex-1 flex flex-col justify-between">
                <div>
                  <div className="flex items-center justify-between text-[11px] text-gray-500 mb-2">
                    <span className="font-semibold text-brand-orange">{post.category}</span>
                    <span className="flex items-center gap-1">
                      <Clock className="w-3 h-3" />
                      {post.readTime}
                    </span>
                  </div>
                  <h3 className="font-bold text-base text-gray-900 group-hover:text-brand-green transition-colors font-display line-clamp-2 mb-2">
                    {post.title}
                  </h3>
                  <p className="text-xs text-gray-600 line-clamp-3 leading-relaxed">
                    {post.excerpt}
                  </p>
                </div>

                <div className="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <img
                      src={post.author.avatar}
                      alt={post.author.name}
                      className="w-6 h-6 rounded-full object-cover"
                    />
                    <span className="text-xs font-medium text-gray-700">{post.author.name}</span>
                  </div>
                  <span className="text-xs font-bold text-brand-green group-hover:translate-x-1 transition-transform">
                    Read &rarr;
                  </span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      </section>

    </div>
  );
};
