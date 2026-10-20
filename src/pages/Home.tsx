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

  // Filter specific product sets (100% Non-food catalog)
  const bestsellers = mockProducts.filter(p => p.isBestSeller).slice(0, 8);
  const ecoJuteAndDecor = mockProducts.filter(p => p.categorySlug === 'jute-bags-cloth-jholas' || p.categorySlug === 'village-home-decor-toys').slice(0, 4);
  const womenHandmade = mockProducts.filter(p => p.isRuralWomenMade).slice(0, 4);
  const farmingTools = mockProducts.filter(p => p.categorySlug === 'farming-traditional-tools').slice(0, 4);
  const recentArticles = mockBlogPosts.slice(0, 3);

  return (
    <div className="space-y-16 pb-16 w-full max-w-full overflow-x-hidden min-w-0">
      
      {/* 7. HERO BANNER */}
      <section className="relative overflow-hidden bg-gradient-to-br from-emerald-900 via-brand-green to-emerald-950 text-white w-full max-w-full">
        {/* Decorative background elements */}
        <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none" />
        <div className="absolute -top-24 -right-24 w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-brand-orange/20 blur-3xl pointer-events-none" />
        <div className="absolute -bottom-24 -left-24 w-72 sm:w-96 h-72 sm:h-96 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none" />

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-24 relative z-10 w-full min-w-0">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            {/* Text & CTAs */}
            <div className="lg:col-span-7 space-y-6 text-center lg:text-left min-w-0">
              <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-800/80 border border-emerald-600/60 text-amber-300 text-xs font-semibold shadow-xs max-w-full">
                <Sparkles className="w-3.5 h-3.5 shrink-0" />
                <span className="truncate">S S Industries Enterprise • Empowering 5,000+ Rural Artisans & Smiths</span>
              </div>

              <h1 className="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight font-display leading-tight sm:leading-none break-words">
                Gaon ke hunar, <br />
                <span className="text-amber-300 drop-shadow-sm">ab har ghar tak</span>
              </h1>

              <p className="text-sm sm:text-lg text-emerald-100/90 max-w-2xl leading-relaxed mx-auto lg:mx-0">
                Discover high-durability hand-forged agricultural tools, high-carbon steel sickles, 
                heavy garden khurpis, eco golden jute bags, GI Channapatna wooden toys, and handloom 
                textiles manufactured by rural foundries and women self-help groups across Bharat.
              </p>

              {/* Action Buttons */}
              <div className="flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 pt-2">
                <Link
                  to="/products"
                  className="px-5 sm:px-6 py-3 sm:py-3.5 rounded-xl bg-brand-orange hover:bg-brand-orange-dark text-white font-semibold text-xs sm:text-sm shadow-lg hover:shadow-orange-600/30 transition-all flex items-center gap-2 active:scale-95"
                >
                  <span>Explore Marketplace</span>
                  <ArrowRight className="w-4 h-4" />
                </Link>

                <Link
                  to="/category/farming-traditional-tools"
                  className="px-5 sm:px-6 py-3 sm:py-3.5 rounded-xl bg-emerald-800/80 hover:bg-emerald-700/80 border border-emerald-600/80 text-white font-semibold text-xs sm:text-sm transition-all flex items-center gap-2"
                >
                  <span>Farming & Garden Tools</span>
                </Link>

                <Link
                  to="/nearby-artisans"
                  className="px-5 sm:px-6 py-3 sm:py-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold text-xs sm:text-sm transition-all flex items-center gap-1.5 shadow-md"
                >
                  <MapPin className="w-4 h-4 text-emerald-950" />
                  <span>Nearby Artisans (Map)</span>
                </Link>
              </div>

              {/* Trust badges */}
              <div className="grid grid-cols-3 gap-2 sm:gap-4 pt-6 border-t border-emerald-800/60 text-left">
                <div>
                  <div className="text-lg sm:text-2xl font-bold text-amber-300 font-display">100%</div>
                  <div className="text-[10px] sm:text-[11px] text-emerald-200/80">Forged Iron & Eco Jute</div>
                </div>
                <div>
                  <div className="text-lg sm:text-2xl font-bold text-amber-300 font-display">40+</div>
                  <div className="text-[10px] sm:text-[11px] text-emerald-200/80">Artisan & Foundry Hubs</div>
                </div>
                <div>
                  <div className="text-lg sm:text-2xl font-bold text-amber-300 font-display">0%</div>
                  <div className="text-[10px] sm:text-[11px] text-emerald-200/80">Middleman Exploitation</div>
                </div>
              </div>
            </div>

            {/* Hero Visual Card Showcase */}
            <div className="lg:col-span-5 relative mt-6 lg:mt-0 px-2 sm:px-0">
              <div className="relative mx-auto max-w-sm rounded-3xl bg-white/10 backdrop-blur-md p-3 sm:p-4 border border-white/20 shadow-2xl">
                <img
                  src="https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80"
                  alt="Rural Indian blacksmith forging agriculture tools"
                  className="w-full h-72 sm:h-80 object-cover rounded-2xl shadow-md"
                />

                {/* Floating pill 1 */}
                <div className="absolute -top-3 left-2 sm:-top-4 sm:-left-4 bg-white text-gray-900 rounded-xl p-2.5 sm:p-3 shadow-xl border border-gray-100 flex items-center gap-2.5 animate-soft-pulse max-w-[90%] sm:max-w-none">
                  <div className="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-orange-100 text-brand-orange flex items-center justify-center font-bold text-sm shrink-0">
                    ⚒️
                  </div>
                  <div className="min-w-0">
                    <p className="text-[9px] sm:text-[10px] text-gray-500 font-medium truncate">High-Carbon Steel</p>
                    <p className="text-xs font-bold text-gray-800 truncate">Forged Desi Hansiya (Sickle)</p>
                  </div>
                </div>

                {/* Floating pill 2 */}
                <div className="absolute -bottom-3 right-2 sm:-bottom-4 sm:-right-4 bg-brand-green text-white rounded-xl p-2.5 sm:p-3 shadow-xl border border-emerald-500 flex items-center gap-2.5 max-w-[90%] sm:max-w-none">
                  <div className="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-emerald-800 flex items-center justify-center text-amber-300 font-bold text-xs sm:text-sm shrink-0">
                    ★ 4.96
                  </div>
                  <div className="min-w-0">
                    <p className="text-[9px] sm:text-[10px] text-emerald-200 font-medium truncate">100% Eco Biodegradable</p>
                    <p className="text-xs font-bold text-white truncate">Golden Jute Shopping Jhola</p>
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

      {/* PROMO BANNER: S S Industries Heavy Farming & Workshop Implements */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="rounded-3xl bg-gradient-to-r from-emerald-900 via-brand-green to-emerald-950 text-white p-8 sm:p-12 relative overflow-hidden shadow-xl border-2 border-amber-400/40">
          <div className="max-w-xl relative z-10 space-y-4">
            <span className="px-3 py-1 rounded-full bg-white/20 backdrop-blur-xs text-xs font-semibold uppercase tracking-wider text-amber-300">
              S S Industries • Hardoi & Sandila Foundry
            </span>
            <h3 className="text-2xl sm:text-4xl font-extrabold font-display leading-tight">
              High-Carbon Forged Tools Engineered to Last Decades
            </h3>
            <p className="text-sm text-emerald-100 leading-relaxed">
              Precision-forged agricultural sickles (Desi Hansiya), hardened carbon steel garden khurpis, heavy fawdas, and chaff cutter toka blades. No brittle scrap metal. Pure generational smith metallurgy.
            </p>
            <div className="pt-2 flex flex-wrap gap-3">
              <Link
                to="/category/farming-traditional-tools"
                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brand-orange hover:bg-brand-orange-dark text-white font-bold text-xs shadow-md transition-all active:scale-95"
              >
                <span>Shop Farming Tools</span>
                <ArrowRight className="w-4 h-4" />
              </Link>
              <Link
                to="/nearby-artisans"
                className="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/30 text-white font-semibold text-xs transition-all"
              >
                <span>View Foundry on Map</span>
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* 10. ECO JUTE BAGS & VILLAGE HANDCRAFTS */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
          <div>
            <div className="flex items-center gap-1.5 text-xs font-bold text-brand-green uppercase tracking-wider mb-1">
              <Leaf className="w-4 h-4" />
              <span>100% Biodegradable & Heritage Crafts</span>
            </div>
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 font-display">
              Eco Jute Bags & Village Handcrafts
            </h2>
            <p className="text-xs text-gray-500 mt-1">Heavy golden jute totes, Sabai grass organizers, Channapatna wooden toys, and brass handicrafts.</p>
          </div>
          <Link
            to="/category/jute-bags-cloth-jholas"
            className="mt-2 sm:mt-0 text-xs font-semibold text-brand-green hover:text-brand-green-dark flex items-center gap-1 group"
          >
            <span>Explore Jute & Crafts</span>
            <ChevronRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {ecoJuteAndDecor.map(product => (
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
              We bridge the gap between rural blacksmiths, women weavers, SHG artisans, and modern Indian households with complete honesty.
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
                No corporate wholesalers. Every tool and craft item is dispatched from the artisan or SHG village unit, ensuring maximum earnings stay in rural pockets.
              </p>
            </div>

            <div className="bg-white p-6 rounded-2xl shadow-xs border border-gray-100 space-y-3">
              <div className="w-12 h-12 rounded-xl bg-orange-50 text-brand-orange flex items-center justify-center">
                <Leaf className="w-6 h-6" />
              </div>
              <h3 className="font-bold text-base text-gray-900 font-display">
                Pure Natural & Forged Materials
              </h3>
              <p className="text-xs text-gray-600 leading-relaxed">
                Zero cheap scrap steel, brittle castings, or toxic plastics. 100% natural golden jute, leaf-spring forged tool steel, and GI Channapatna organic lac wood.
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

      {/* INTERACTIVE MAP TEASER: Nearby Artisans & Foundries */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl p-8 sm:p-12 border border-amber-200/80 shadow-md">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div className="lg:col-span-7 space-y-4">
              <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-brand-orange text-xs font-bold">
                <MapPin className="w-3.5 h-3.5" />
                <span>Live OpenStreetMap Integration</span>
              </div>
              <h2 className="text-2xl sm:text-4xl font-extrabold text-gray-900 font-display">
                Locate Artisans, Foundries & SHGs Near You
              </h2>
              <p className="text-xs sm:text-sm text-gray-700 leading-relaxed">
                Use our interactive village directory map to discover blacksmith workshops in Hardoi, brass craft foundries in Moradabad, and women jute cooperatives in the Terai belt. Directly call artisans, check workshop addresses, and arrange bulk farm tool pickups.
              </p>
              <div className="pt-2 flex flex-wrap gap-3">
                <Link
                  to="/nearby-artisans"
                  className="px-6 py-3 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-2"
                >
                  <MapPin className="w-4 h-4 text-amber-300" />
                  <span>Open Interactive Map Directory</span>
                  <ArrowRight className="w-4 h-4" />
                </Link>
                <a
                  href="tel:+917897492100"
                  className="px-5 py-3 bg-white border border-gray-300 text-gray-800 text-xs font-semibold rounded-xl hover:bg-gray-50 transition-all flex items-center gap-1.5"
                >
                  <span>📞 Call S S Industries Works</span>
                </a>
              </div>
            </div>

            <div className="lg:col-span-5 bg-white p-5 rounded-2xl border border-amber-200 shadow-sm space-y-3">
              <div className="flex items-center justify-between pb-2 border-b border-gray-100">
                <span className="text-xs font-bold text-gray-900">Foundry & Works Coordinates</span>
                <span className="text-[10px] bg-emerald-100 text-brand-green px-2 py-0.5 rounded font-bold">Active Geolocation</span>
              </div>
              <div className="text-xs space-y-2 text-gray-600">
                <p><strong>Works:</strong> Madhoganj Baghauli Road, Newada, Hardoi 241126</p>
                <p><strong>Head Office:</strong> G145 Phase 2 Industrial Area, UPSIDA Sandila</p>
                <p><strong>GPS Reference:</strong> 27.3986° N, 80.1319° E</p>
                <p><strong>Dispatch Scope:</strong> Pan-Bharat (India Post & Cargo)</p>
              </div>
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
              Rural women, ironsmiths, and craft collectives forge tools and stitch bags using generational wisdom.
            </p>
          </div>

          <div className="p-6 rounded-2xl bg-white border border-gray-100 shadow-xs relative">
            <span className="text-4xl font-extrabold text-emerald-100 font-display absolute top-4 right-4">
              02
            </span>
            <div className="w-10 h-10 rounded-full bg-orange-100 text-brand-orange font-bold flex items-center justify-center text-sm mb-4">
              2
            </div>
            <h3 className="font-bold text-sm text-gray-900 font-display mb-2">Quality & Metal Verification</h3>
            <p className="text-xs text-gray-600 leading-relaxed">
              Tool blades and fibers are tested for steel hardness, stitch tensile strength, and zero synthetic shortcuts.
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
                "The S S Industries garden khurpi and fawda are built like tanks. The hand-forged high-carbon steel edge cuts clean through heavy soil and keeps sharp month after month without bending."
              </p>
              <div className="pt-2 border-t border-emerald-800">
                <p className="font-bold text-xs text-white">Rameshwar Tripathi</p>
                <p className="text-[10px] text-emerald-300">Kanpur, Orchard Grower</p>
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
