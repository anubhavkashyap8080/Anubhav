import React, { useState, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
  Clock,
  Sparkles,
  Search,
  ChevronRight,
  BookOpen,
  Share2,
  Calendar,
  Flame,
  ArrowRight
} from 'lucide-react';
import { mockBlogPosts, mockBlogCategories } from '../data/mockBlogPosts';
import { useDemoModal } from '../context/DemoModalContext';

export const BlogList: React.FC = () => {
  const { slug } = useParams<{ slug?: string }>();
  const { showDemoNotice } = useDemoModal();

  const [selectedCategory, setSelectedCategory] = useState<string>(slug || 'All');
  const [searchQuery, setSearchQuery] = useState<string>('');

  React.useEffect(() => {
    if (slug) {
      // Find category title matching slug
      const matched = mockBlogCategories.find(c =>
        c.toLowerCase().replace(/[^a-z0-9]+/g, '-') === slug
      );
      if (matched) setSelectedCategory(matched);
    }
  }, [slug]);

  const filteredPosts = useMemo(() => {
    return mockBlogPosts.filter(post => {
      if (selectedCategory !== 'All' && post.category !== selectedCategory) {
        return false;
      }
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase();
        const matchesTitle = post.title.toLowerCase().includes(q);
        const matchesExcerpt = post.excerpt.toLowerCase().includes(q);
        const matchesTags = post.tags.some(t => t.toLowerCase().includes(q));
        if (!matchesTitle && !matchesExcerpt && !matchesTags) return false;
      }
      return true;
    });
  }, [selectedCategory, searchQuery]);

  const featuredPost = mockBlogPosts.find(p => p.featured) || mockBlogPosts[0];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
      
      {/* Blog Hero Header */}
      <div className="text-center max-w-3xl mx-auto space-y-4">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-100 text-brand-orange text-xs font-bold rounded-full">
          <Sparkles className="w-3.5 h-3.5" />
          <span>Village Chronicles & Heritage Wisdom</span>
        </div>
        <h1 className="text-3xl sm:text-5xl font-extrabold text-gray-900 font-display">
          The GanvGaliHut Journal
        </h1>
        <p className="text-sm sm:text-base text-gray-600 leading-relaxed">
          Stories of women self-help group breakthroughs, handloom traditions, traditional blacksmith metallurgy, and sustainable rural living.
        </p>

        {/* Search Bar */}
        <div className="max-w-md mx-auto relative pt-2">
          <input
            type="text"
            placeholder="Search articles on sickles, jute crafts, handloom, SHGs..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 text-xs bg-white border border-gray-300 rounded-full shadow-xs focus:ring-2 focus:ring-brand-green"
          />
          <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 mt-1" />
        </div>
      </div>

      {/* Category Filter Pills */}
      <div className="flex items-center justify-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-medium">
        {mockBlogCategories.map(cat => (
          <button
            key={cat}
            onClick={() => setSelectedCategory(cat)}
            className={`px-4 py-2 rounded-full shrink-0 transition-all cursor-pointer ${
              selectedCategory === cat
                ? 'bg-brand-green text-white font-bold shadow-xs'
                : 'bg-white text-gray-700 border border-gray-200 hover:bg-emerald-50'
            }`}
          >
            {cat}
          </button>
        ))}
      </div>

      {/* Featured Article Hero (When on 'All' tab and no active search) */}
      {selectedCategory === 'All' && !searchQuery.trim() && (
        <div className="bg-white rounded-3xl border border-gray-100 shadow-md overflow-hidden grid grid-cols-1 lg:grid-cols-12 gap-0 group">
          <div className="lg:col-span-7 aspect-16/9 lg:aspect-auto overflow-hidden bg-gray-100">
            <img
              src={featuredPost.image}
              alt={featuredPost.title}
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />
          </div>

          <div className="lg:col-span-5 p-6 sm:p-10 flex flex-col justify-between space-y-4">
            <div className="space-y-3">
              <div className="flex items-center gap-2">
                <span className="px-2.5 py-0.5 rounded-full bg-brand-orange text-white text-[10px] font-bold uppercase tracking-wider">
                  Featured Story
                </span>
                <span className="text-xs text-gray-400">•</span>
                <span className="text-xs font-semibold text-brand-green">{featuredPost.category}</span>
              </div>

              <Link
                to={`/blog/${featuredPost.slug}`}
                className="block text-xl sm:text-2xl font-bold text-gray-900 group-hover:text-brand-green transition-colors font-display leading-snug"
              >
                {featuredPost.title}
              </Link>

              <p className="text-xs sm:text-sm text-gray-600 line-clamp-3 leading-relaxed">
                {featuredPost.excerpt}
              </p>
            </div>

            <div className="pt-4 border-t border-gray-100 flex items-center justify-between">
              <div className="flex items-center gap-2.5">
                <img
                  src={featuredPost.author.avatar}
                  alt={featuredPost.author.name}
                  className="w-9 h-9 rounded-full object-cover border border-brand-green"
                />
                <div>
                  <p className="text-xs font-bold text-gray-900">{featuredPost.author.name}</p>
                  <p className="text-[10px] text-gray-500">{featuredPost.publishedAt} • {featuredPost.readTime}</p>
                </div>
              </div>

              <Link
                to={`/blog/${featuredPost.slug}`}
                className="px-4 py-2 bg-emerald-50 hover:bg-brand-green hover:text-white text-brand-green font-bold text-xs rounded-xl transition-all flex items-center gap-1"
              >
                <span>Read Story</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </Link>
            </div>
          </div>
        </div>
      )}

      {/* Articles Grid */}
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-bold text-gray-900 font-display">
            {selectedCategory === 'All' ? 'Latest Articles' : `${selectedCategory} Articles`} ({filteredPosts.length})
          </h2>
          <span className="text-xs text-gray-500">Updated weekly with rural dispatches</span>
        </div>

        {filteredPosts.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {filteredPosts.map(post => (
              <article
                key={post.id}
                className="bg-white rounded-2xl border border-gray-100 shadow-xs hover:shadow-lg transition-all overflow-hidden flex flex-col group"
              >
                <div className="aspect-16/9 w-full overflow-hidden bg-gray-100">
                  <Link to={`/blog/${post.slug}`}>
                    <img
                      src={post.image}
                      alt={post.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                  </Link>
                </div>

                <div className="p-5 flex-1 flex flex-col justify-between">
                  <div>
                    <div className="flex items-center justify-between text-[11px] text-gray-500 mb-2">
                      <span className="font-bold text-brand-orange">{post.category}</span>
                      <span className="flex items-center gap-1">
                        <Clock className="w-3 h-3" />
                        {post.readTime}
                      </span>
                    </div>

                    <Link
                      to={`/blog/${post.slug}`}
                      className="block font-bold text-base text-gray-900 group-hover:text-brand-green transition-colors font-display line-clamp-2 mb-2 leading-snug"
                    >
                      {post.title}
                    </Link>

                    <p className="text-xs text-gray-600 line-clamp-3 leading-relaxed">
                      {post.excerpt}
                    </p>
                  </div>

                  <div className="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <img
                        src={post.author.avatar}
                        alt={post.author.name}
                        className="w-7 h-7 rounded-full object-cover"
                      />
                      <span className="text-xs font-medium text-gray-700">{post.author.name}</span>
                    </div>

                    <Link
                      to={`/blog/${post.slug}`}
                      className="text-xs font-bold text-brand-green group-hover:translate-x-1 transition-transform flex items-center gap-1"
                    >
                      <span>Read</span>
                      <ChevronRight className="w-3.5 h-3.5" />
                    </Link>
                  </div>
                </div>
              </article>
            ))}
          </div>
        ) : (
          <div className="bg-white p-12 rounded-3xl border border-gray-100 text-center max-w-md mx-auto">
            <BookOpen className="w-10 h-10 text-gray-400 mx-auto mb-3" />
            <h3 className="font-bold text-base text-gray-800">No articles found</h3>
            <p className="text-xs text-gray-500 mt-1">Try another category filter or search term.</p>
          </div>
        )}

        {/* Load More Preview Button */}
        <div className="text-center pt-8">
          <button
            onClick={() => showDemoNotice('Load More Blog Articles', 'All mock village articles are currently shown in this prototype release.')}
            className="px-6 py-2.5 bg-white border border-gray-300 hover:border-brand-green text-gray-700 text-xs font-semibold rounded-xl shadow-xs cursor-pointer"
          >
            Load Older Stories (Demo)
          </button>
        </div>
      </div>

    </div>
  );
};
