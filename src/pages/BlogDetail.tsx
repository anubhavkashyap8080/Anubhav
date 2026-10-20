import React from 'react';
import { useParams, Link } from 'react-router-dom';
import {
  Clock,
  Calendar,
  Share2,
  ChevronLeft,
  ChevronRight,
  BookOpen,
  Sparkles,
  Tag,
  ArrowLeft,
  Facebook,
  Twitter,
  Linkedin
} from 'lucide-react';
import { mockBlogPosts } from '../data/mockBlogPosts';
import { useDemoModal } from '../context/DemoModalContext';

export const BlogDetail: React.FC = () => {
  const { slug } = useParams<{ slug: string }>();
  const { showDemoNotice } = useDemoModal();

  const currentIndex = mockBlogPosts.findIndex(p => p.slug === slug);
  const post = currentIndex !== -1 ? mockBlogPosts[currentIndex] : mockBlogPosts[0];

  const prevPost = currentIndex > 0 ? mockBlogPosts[currentIndex - 1] : null;
  const nextPost = currentIndex < mockBlogPosts.length - 1 ? mockBlogPosts[currentIndex + 1] : null;

  const relatedPosts = mockBlogPosts.filter(p => p.id !== post.id).slice(0, 2);

  const handleShare = (network: string) => {
    showDemoNotice(
      `Share on ${network}`,
      'Social share triggers will broadcast canonical article URLs in production.'
    );
  };

  return (
    <article className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Breadcrumb Navigation */}
      <nav className="text-xs text-gray-500 flex items-center gap-1.5 flex-wrap">
        <Link to="/" className="hover:text-brand-green">Home</Link>
        <span>/</span>
        <Link to="/blog" className="hover:text-brand-green">Blog</Link>
        <span>/</span>
        <span className="text-brand-orange font-semibold">{post.category}</span>
        <span>/</span>
        <span className="text-gray-900 font-semibold truncate max-w-xs">{post.title}</span>
      </nav>

      {/* Article Header */}
      <header className="space-y-4">
        <div className="flex items-center gap-2">
          <span className="px-3 py-1 rounded-full bg-orange-100 text-brand-orange text-xs font-bold uppercase tracking-wider">
            {post.category}
          </span>
          <span className="text-xs text-gray-500 flex items-center gap-1">
            <Clock className="w-3.5 h-3.5" />
            <span>{post.readTime}</span>
          </span>
        </div>

        <h1 className="text-2xl sm:text-4xl font-extrabold text-gray-900 font-display leading-tight sm:leading-snug">
          {post.title}
        </h1>

        <p className="text-sm sm:text-base text-gray-600 leading-relaxed font-serif italic border-l-4 border-brand-green pl-4">
          {post.excerpt}
        </p>

        {/* Author Bio Bar & Share Buttons */}
        <div className="py-4 border-y border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <img
              src={post.author.avatar}
              alt={post.author.name}
              className="w-12 h-12 rounded-full object-cover border-2 border-brand-green"
            />
            <div>
              <p className="font-bold text-sm text-gray-900">{post.author.name}</p>
              <p className="text-xs text-gray-500">{post.author.role} • {post.publishedAt}</p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <span className="text-xs text-gray-500 mr-1">Share:</span>
            <button
              onClick={() => handleShare('WhatsApp')}
              className="px-2.5 py-1.5 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-lg hover:bg-emerald-100 transition-colors"
            >
              WhatsApp
            </button>
            <button
              onClick={() => handleShare('Twitter')}
              className="px-2.5 py-1.5 bg-sky-50 text-sky-800 text-xs font-semibold rounded-lg hover:bg-sky-100 transition-colors"
            >
              Twitter
            </button>
            <button
              onClick={() => handleShare('Clipboard')}
              className="p-1.5 text-gray-500 hover:text-gray-900 rounded-lg border border-gray-200"
              title="Copy link"
            >
              <Share2 className="w-4 h-4" />
            </button>
          </div>
        </div>
      </header>

      {/* Featured Banner Image */}
      <div className="aspect-16/9 rounded-3xl overflow-hidden bg-gray-100 shadow-md">
        <img
          src={post.image}
          alt={post.title}
          className="w-full h-full object-cover"
        />
      </div>

      {/* Table of Contents UI Box */}
      <div className="bg-brand-cream rounded-2xl p-5 border border-amber-200/80 space-y-2">
        <div className="flex items-center gap-2 text-xs font-bold text-brand-green uppercase tracking-wider">
          <BookOpen className="w-4 h-4 text-brand-orange" />
          <span>Table of Contents</span>
        </div>
        <ul className="text-xs text-gray-700 space-y-1.5 list-disc list-inside">
          <li>Understanding the Ancient Technique & Provenance</li>
          <li>The Five Pillars of Traditional Craftsmanship</li>
          <li>Impact on Rural Women Self-Help Groups and Local Incomes</li>
          <li>How to Identify Pure Authentic Quality at Home</li>
        </ul>
      </div>

      {/* Main Safe HTML / Formatted Body */}
      <div
        className="prose prose-sm sm:prose-base max-w-none text-gray-800 leading-relaxed space-y-4 font-sans [&>h2]:text-xl [&>h2]:font-bold [&>h2]:font-display [&>h2]:text-gray-900 [&>h2]:pt-4 [&>h3]:text-base [&>h3]:font-bold [&>h3]:text-brand-green [&>blockquote]:border-l-4 [&>blockquote]:border-brand-orange [&>blockquote]:pl-4 [&>blockquote]:italic [&>blockquote]:text-gray-700 [&>ul]:list-disc [&>ul]:list-inside [&>ul]:space-y-1"
        dangerouslySetInnerHTML={{ __html: post.content }}
      />

      {/* Tags */}
      <div className="pt-6 border-t border-gray-100 flex items-center gap-2 flex-wrap">
        <Tag className="w-4 h-4 text-gray-400" />
        <span className="text-xs text-gray-500 font-medium">Filed under:</span>
        {post.tags.map(t => (
          <span
            key={t}
            className="px-2.5 py-1 bg-gray-100 hover:bg-emerald-50 text-gray-700 hover:text-brand-green text-xs rounded-full font-medium transition-colors"
          >
            #{t}
          </span>
        ))}
      </div>

      {/* Previous / Next Article Navigation */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-gray-200">
        {prevPost ? (
          <Link
            to={`/blog/${prevPost.slug}`}
            className="p-4 rounded-xl border border-gray-200 hover:border-brand-green hover:bg-emerald-50/40 transition-all text-left flex items-center gap-3"
          >
            <ChevronLeft className="w-5 h-5 text-brand-green shrink-0" />
            <div className="truncate">
              <span className="text-[10px] uppercase font-bold text-gray-400 block">Previous Story</span>
              <span className="text-xs font-bold text-gray-800 truncate block">{prevPost.title}</span>
            </div>
          </Link>
        ) : <div />}

        {nextPost && (
          <Link
            to={`/blog/${nextPost.slug}`}
            className="p-4 rounded-xl border border-gray-200 hover:border-brand-green hover:bg-emerald-50/40 transition-all text-right flex items-center justify-end gap-3"
          >
            <div className="truncate">
              <span className="text-[10px] uppercase font-bold text-gray-400 block">Next Story</span>
              <span className="text-xs font-bold text-gray-800 truncate block">{nextPost.title}</span>
            </div>
            <ChevronRight className="w-5 h-5 text-brand-green shrink-0" />
          </Link>
        )}
      </div>

      {/* Related Stories */}
      {relatedPosts.length > 0 && (
        <section className="pt-8 border-t border-gray-200 space-y-6">
          <h2 className="text-xl font-bold text-gray-900 font-display">
            More Stories from the Village
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {relatedPosts.map(p => (
              <Link
                key={p.id}
                to={`/blog/${p.slug}`}
                className="group bg-white rounded-2xl border border-gray-100 p-4 shadow-xs hover:shadow-md transition-all flex gap-4 items-center"
              >
                <img
                  src={p.image}
                  alt={p.title}
                  className="w-20 h-20 rounded-xl object-cover shrink-0"
                />
                <div className="min-w-0">
                  <span className="text-[10px] font-bold text-brand-orange">{p.category}</span>
                  <h3 className="font-bold text-xs text-gray-900 group-hover:text-brand-green line-clamp-2 mt-0.5">
                    {p.title}
                  </h3>
                  <span className="text-[11px] text-gray-400 mt-1 block">{p.readTime}</span>
                </div>
              </Link>
            ))}
          </div>
        </section>
      )}

    </article>
  );
};
