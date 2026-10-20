import React, { useState, useEffect } from 'react';
import {
  FileText,
  Plus,
  Edit3,
  Trash2,
  Eye,
  CheckCircle2,
  Clock,
  Sparkles,
  Search,
  Bold,
  Italic,
  Heading2,
  Heading3,
  Quote,
  List,
  Link as LinkIcon,
  Globe,
  Save,
  Send,
  Calendar,
  X,
  Upload,
  Image as ImageIcon,
  Check,
  Layers,
  CheckCircle
} from 'lucide-react';
import { mockBlogPosts, mockBlogCategories } from '../../data/mockBlogPosts';
import { BlogPost } from '../../types';

// Curated authentic rural craft photography for the media picker
const MEDIA_LIBRARY_ASSETS = [
  {
    id: 'asset-1',
    title: 'High-Carbon Steel Sickle Forging',
    category: 'Foundry & Tools',
    url: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80'
  },
  {
    id: 'asset-2',
    title: 'Terracotta Clay Handi & Water Pots',
    category: 'Clay Pottery',
    url: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=800&q=80'
  },
  {
    id: 'asset-3',
    title: 'Natural Golden Jute Bags & Stitching',
    category: 'Jute & Fiber',
    url: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80'
  },
  {
    id: 'asset-4',
    title: 'Handloom Khadi Weaver on Traditional Loom',
    category: 'Handloom & Khadi',
    url: 'https://images.unsplash.com/photo-1607613009820-a29f7bb81c04?auto=format&fit=crop&w=800&q=80'
  },
  {
    id: 'asset-5',
    title: 'Blacksmith Coal Furnace & Anvil Working',
    category: 'Foundry & Tools',
    url: 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=800&q=80'
  },
  {
    id: 'asset-6',
    title: 'Handcrafted Brass Kansa Utensils',
    category: 'Metal Crafts',
    url: 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80'
  },
  {
    id: 'asset-7',
    title: 'Hand-Carved Village Wooden Craft',
    category: 'Wooden Craft',
    url: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80'
  },
  {
    id: 'asset-8',
    title: 'Moonj Grass Basket Weaving by Rural SHGs',
    category: 'Rural Weaving',
    url: 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=800&q=80'
  }
];

export const BlogManagement: React.FC = () => {
  const [posts, setPosts] = useState<BlogPost[]>(() => {
    try {
      const saved = localStorage.getItem('ggh_custom_blog_posts');
      if (saved) return JSON.parse(saved);
    } catch (e) {
      console.warn('Failed to parse saved blog posts:', e);
    }
    return mockBlogPosts;
  });

  const [editingPost, setEditingPost] = useState<BlogPost | null>(null);
  const [isCreatingNew, setIsCreatingNew] = useState(false);
  const [searchFilter, setSearchFilter] = useState('');
  const [toastMessage, setToastMessage] = useState<string | null>(null);
  const [showMediaLibrary, setShowMediaLibrary] = useState(false);

  // Form State for Editing/Creating
  const [title, setTitle] = useState('');
  const [slug, setSlug] = useState('');
  const [excerpt, setExcerpt] = useState('');
  const [content, setContent] = useState('');
  const [category, setCategory] = useState(mockBlogCategories[1] || 'Farming & Traditional Tools');
  const [authorName, setAuthorName] = useState('Ananya Verma');
  const [status, setStatus] = useState<'Published' | 'Draft' | 'Scheduled'>('Published');
  const [featuredImage, setFeaturedImage] = useState('https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=800&q=80');
  const [seoTitle, setSeoTitle] = useState('');
  const [metaDescription, setMetaDescription] = useState('');
  const [canonicalUrl, setCanonicalUrl] = useState('https://ganvgalihat.com/blog/new-article');
  const [indexRobots, setIndexRobots] = useState(true);

  const startEdit = (post: BlogPost) => {
    setEditingPost(post);
    setIsCreatingNew(false);
    setTitle(post.title);
    setSlug(post.slug);
    setExcerpt(post.excerpt);
    setContent(post.content);
    setCategory(post.category);
    setAuthorName(post.author.name);
    setStatus(post.status);
    setFeaturedImage(post.image);
    setSeoTitle(post.seo.title);
    setMetaDescription(post.seo.metaDescription);
    setCanonicalUrl(post.seo.canonicalUrl);
    setIndexRobots(post.seo.indexRobots);
  };

  const startCreate = () => {
    setEditingPost(null);
    setIsCreatingNew(true);
    setTitle('');
    setSlug('');
    setExcerpt('');
    setContent('');
    setCategory(mockBlogCategories[1] || 'Farming & Traditional Tools');
    setAuthorName('Editorial Lead');
    setStatus('Published');
    setFeaturedImage('https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80');
    setSeoTitle('');
    setMetaDescription('');
    setCanonicalUrl('https://ganvgalihat.com/blog/new-story');
    setIndexRobots(true);
  };

  // Thumbnail file upload from PC
  const handleThumbnailUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setFeaturedImage(reader.result as string);
        setToastMessage('Thumbnail image uploaded from local storage successfully!');
        setTimeout(() => setToastMessage(null), 3000);
      };
      reader.readAsDataURL(file);
    }
  };

  // Rich Text Formatting Toolbar Helper
  const insertFormat = (tagPrefix: string, tagSuffix: string = '') => {
    setContent(prev => `${prev}\n${tagPrefix}Featured Craft Details${tagSuffix}\n`);
  };

  // Save / Publish Action
  const handleSave = (publishStatus: 'Published' | 'Draft' | 'Scheduled') => {
    if (!title.trim()) {
      alert('Please enter an article headline.');
      return;
    }

    const postData: BlogPost = {
      id: editingPost ? editingPost.id : `post-${Date.now()}`,
      title: title.trim(),
      slug: slug.trim() || title.toLowerCase().replace(/[^a-z0-9]+/g, '-').slice(0, 50),
      excerpt: excerpt.trim() || title,
      content: content || '<p>Article content published via GanvGaliHut CMS Studio.</p>',
      category,
      categorySlug: category.toLowerCase().replace(/[^a-z0-9]+/g, '-'),
      image: featuredImage,
      author: {
        name: authorName || 'Editorial Team',
        role: 'Field Correspondent',
        avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80'
      },
      publishedAt: editingPost?.publishedAt || new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
      readTime: '4 min read',
      tags: [category, 'Rural Crafts', 'Artisans', 'Hardoi'],
      status: publishStatus,
      seo: {
        title: seoTitle || title,
        metaDescription: metaDescription || excerpt,
        canonicalUrl: canonicalUrl || `https://ganvgalihat.pages.dev/blog/${slug}`,
        ogImage: featuredImage,
        indexRobots
      }
    };

    let updatedList: BlogPost[];
    if (editingPost) {
      updatedList = posts.map(p => p.id === editingPost.id ? postData : p);
    } else {
      updatedList = [postData, ...posts];
    }
    setPosts(updatedList);
    try {
      localStorage.setItem('ggh_custom_blog_posts', JSON.stringify(updatedList));
    } catch (err) {
      console.warn('LocalStorage save failed:', err);
    }

    setEditingPost(null);
    setIsCreatingNew(false);
    setToastMessage(`Story "${postData.title}" successfully saved as ${publishStatus}!`);
    setTimeout(() => setToastMessage(null), 4000);
  };

  // Delete Action
  const handleDelete = (postId: string, postTitle: string) => {
    if (window.confirm(`Are you sure you want to permanently delete "${postTitle}"?`)) {
      const updated = posts.filter(p => p.id !== postId);
      setPosts(updated);
      try {
        localStorage.setItem('ggh_custom_blog_posts', JSON.stringify(updated));
      } catch (err) {
        console.warn('LocalStorage save failed:', err);
      }
      setToastMessage(`Article "${postTitle}" deleted successfully.`);
      setTimeout(() => setToastMessage(null), 3000);
    }
  };

  const filteredList = posts.filter(p =>
    p.title.toLowerCase().includes(searchFilter.toLowerCase()) ||
    p.category.toLowerCase().includes(searchFilter.toLowerCase())
  );

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Toast Notification */}
      {toastMessage && (
        <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-3 text-xs text-emerald-900 shadow-sm animate-in fade-in duration-200">
          <div className="flex items-center gap-2 font-semibold">
            <CheckCircle className="w-5 h-5 text-brand-green shrink-0" />
            <span>{toastMessage}</span>
          </div>
          <button
            onClick={() => setToastMessage(null)}
            className="p-1 hover:bg-emerald-100 rounded-lg text-emerald-700"
          >
            <X className="w-4 h-4" />
          </button>
        </div>
      )}

      {/* Production Header Status */}
      <div className="p-4 bg-white border border-gray-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs shadow-xs">
        <div className="flex items-center gap-3">
          <FileText className="w-5 h-5 text-brand-green shrink-0" />
          <div>
            <span className="font-bold text-gray-900">Editorial CMS & Story Studio:</span>
            <span className="text-gray-600 ml-1">
              Documenting artisan blacksmithing, pottery heritage, and rural self-help group updates across Uttar Pradesh.
            </span>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <span className="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg shrink-0">
            {posts.length} Stories In Catalog
          </span>
          <span className="px-2.5 py-1 bg-brand-green/10 text-brand-green font-bold rounded-lg shrink-0">
            SEO Ready
          </span>
        </div>
      </div>

      {/* Editor Modal / View */}
      {(editingPost || isCreatingNew) ? (
        <div className="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-10 space-y-8 animate-in fade-in duration-200">
          
          <div className="flex items-center justify-between pb-4 border-b border-gray-100">
            <div>
              <span className="text-xs font-bold text-brand-green uppercase tracking-wider">
                {isCreatingNew ? 'Create New Article' : `Editing Article`}
              </span>
              <h2 className="text-xl sm:text-2xl font-bold text-gray-900 font-display mt-0.5">
                {title || 'Article Editor & SEO Publishing Studio'}
              </h2>
            </div>

            <button
              onClick={() => {
                setEditingPost(null);
                setIsCreatingNew(false);
              }}
              className="p-2 text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-100 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {/* Left Column: Article Details & Content */}
            <div className="lg:col-span-8 space-y-5 text-xs">
              <div>
                <label className="block font-bold text-gray-700 mb-1">Article Headline *</label>
                <input
                  type="text"
                  placeholder="e.g. Traditional Sun-Drying & Forging Techniques in Eastern UP"
                  value={title}
                  onChange={(e) => {
                    setTitle(e.target.value);
                    if (isCreatingNew) {
                      setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').slice(0, 50));
                      setSeoTitle(e.target.value);
                    }
                  }}
                  className="w-full text-sm font-semibold p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-green"
                  required
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">URL Slug</label>
                  <div className="flex items-center bg-gray-50 border border-gray-200 rounded-lg px-2">
                    <span className="text-gray-400 font-mono text-[11px]">/blog/</span>
                    <input
                      type="text"
                      value={slug}
                      onChange={(e) => setSlug(e.target.value)}
                      className="w-full p-2 bg-transparent font-mono text-xs focus:outline-none"
                    />
                  </div>
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Author Attribution</label>
                  <input
                    type="text"
                    value={authorName}
                    onChange={(e) => setAuthorName(e.target.value)}
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Brief Excerpt / Standfirst</label>
                <textarea
                  rows={2}
                  value={excerpt}
                  onChange={(e) => setExcerpt(e.target.value)}
                  placeholder="1-2 sentences summarizing the village craft story..."
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg"
                />
              </div>

              {/* Rich-Text Formatting Toolbar */}
              <div>
                <label className="block font-bold text-gray-700 mb-1">Article Body Content</label>
                <div className="border border-gray-200 rounded-xl overflow-hidden bg-white">
                  <div className="p-2 bg-gray-100 border-b border-gray-200 flex flex-wrap items-center gap-1.5 text-gray-700">
                    <button
                      type="button"
                      onClick={() => insertFormat('**', '**')}
                      className="p-1.5 hover:bg-white rounded transition-colors"
                      title="Bold"
                    >
                      <Bold className="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      onClick={() => insertFormat('*', '*')}
                      className="p-1.5 hover:bg-white rounded transition-colors"
                      title="Italic"
                    >
                      <Italic className="w-3.5 h-3.5" />
                    </button>
                    <span className="text-gray-300">|</span>
                    <button
                      type="button"
                      onClick={() => insertFormat('## ', '')}
                      className="p-1.5 hover:bg-white rounded transition-colors"
                      title="Heading 2"
                    >
                      <Heading2 className="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      onClick={() => insertFormat('### ', '')}
                      className="p-1.5 hover:bg-white rounded transition-colors"
                      title="Heading 3"
                    >
                      <Heading3 className="w-3.5 h-3.5" />
                    </button>
                    <span className="text-gray-300">|</span>
                    <button
                      type="button"
                      onClick={() => insertFormat('> ', '')}
                      className="p-1.5 hover:bg-white rounded transition-colors"
                      title="Blockquote"
                    >
                      <Quote className="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      onClick={() => insertFormat('- ', '')}
                      className="p-1.5 hover:bg-white rounded transition-colors"
                      title="Bullet List"
                    >
                      <List className="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      onClick={() => insertFormat('[Link Title](', ')')}
                      className="p-1.5 hover:bg-white rounded transition-colors"
                      title="Hyperlink"
                    >
                      <LinkIcon className="w-3.5 h-3.5" />
                    </button>
                  </div>
                  <textarea
                    rows={8}
                    value={content}
                    onChange={(e) => setContent(e.target.value)}
                    placeholder="Write detailed paragraphs, village interview quotes, and craft details..."
                    className="w-full p-4 font-mono text-xs focus:outline-none"
                  />
                </div>
              </div>

              {/* SEO & Social Metadata Box */}
              <div className="p-5 rounded-2xl bg-gray-50 border border-gray-200 space-y-4">
                <div className="flex items-center gap-2 font-bold text-gray-900">
                  <Globe className="w-4 h-4 text-brand-green" />
                  <span>Search Engine Optimization (SEO) & Social Meta</span>
                </div>

                <div className="space-y-3">
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">SEO Title Tag (50-60 characters)</label>
                    <input
                      type="text"
                      value={seoTitle}
                      onChange={(e) => setSeoTitle(e.target.value)}
                      className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs"
                    />
                  </div>

                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Meta Description (150-160 characters)</label>
                    <textarea
                      rows={2}
                      value={metaDescription}
                      onChange={(e) => setMetaDescription(e.target.value)}
                      className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs"
                    />
                  </div>

                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Canonical URL</label>
                    <input
                      type="text"
                      value={canonicalUrl}
                      onChange={(e) => setCanonicalUrl(e.target.value)}
                      className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs font-mono"
                    />
                  </div>

                  <label className="flex items-center gap-2 pt-1 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={indexRobots}
                      onChange={(e) => setIndexRobots(e.target.checked)}
                      className="rounded text-brand-green focus:ring-brand-green"
                    />
                    <span className="font-semibold text-gray-800">
                      Allow search engine crawlers to index this story (index, follow)
                    </span>
                  </label>
                </div>
              </div>

            </div>

            {/* Right Column: Publication Actions & Featured Image */}
            <div className="lg:col-span-4 space-y-6 text-xs">
              
              {/* Publication Actions */}
              <div className="p-5 rounded-2xl bg-white border border-gray-200 shadow-xs space-y-4">
                <h3 className="font-bold text-gray-900 border-b border-gray-100 pb-2">Publishing Status</h3>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Status</label>
                  <select
                    value={status}
                    onChange={(e: any) => setStatus(e.target.value)}
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg font-semibold"
                  >
                    <option value="Published">Published</option>
                    <option value="Draft">Draft</option>
                    <option value="Scheduled">Scheduled</option>
                  </select>
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Editorial Category</label>
                  <select
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg font-medium"
                  >
                    {mockBlogCategories.filter(c => c !== 'All').map(cat => (
                      <option key={cat} value={cat}>{cat}</option>
                    ))}
                  </select>
                </div>

                <div className="pt-2 space-y-2">
                  <button
                    type="button"
                    onClick={() => handleSave('Published')}
                    className="w-full py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer"
                  >
                    <Send className="w-3.5 h-3.5" />
                    <span>Publish Story Immediately</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => handleSave('Draft')}
                    className="w-full py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                  >
                    <Save className="w-3.5 h-3.5" />
                    <span>Save as Draft</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => handleSave('Scheduled')}
                    className="w-full py-2 bg-white border border-gray-300 hover:bg-gray-50 text-brand-orange font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                  >
                    <Calendar className="w-3.5 h-3.5" />
                    <span>Schedule Release</span>
                  </button>
                </div>
              </div>

              {/* Featured Image & Media Asset Upload/Picker */}
              <div className="p-5 rounded-2xl bg-white border border-gray-200 shadow-xs space-y-3">
                <h3 className="font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center justify-between">
                  <span>Featured Image & Thumbnail</span>
                  <span className="text-[10px] text-gray-400 font-normal">Open Graph Ready</span>
                </h3>

                <div className="aspect-16/9 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-inner">
                  {featuredImage ? (
                    <img
                      src={featuredImage}
                      alt="Featured preview"
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-gray-400">
                      No Image Selected
                    </div>
                  )}
                </div>

                {/* Direct Upload from Device */}
                <div className="space-y-2 pt-1">
                  <label className="w-full py-2 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-lg text-xs flex items-center justify-center gap-1.5 cursor-pointer shadow-xs transition-colors">
                    <Upload className="w-3.5 h-3.5" />
                    <span>Upload Thumbnail from Device</span>
                    <input
                      type="file"
                      accept="image/*"
                      onChange={handleThumbnailUpload}
                      className="hidden"
                    />
                  </label>

                  {/* Choose Media Library Asset Button */}
                  <button
                    type="button"
                    onClick={() => setShowMediaLibrary(true)}
                    className="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-lg text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                  >
                    <ImageIcon className="w-3.5 h-3.5 text-brand-orange" />
                    <span>Choose Media Library Asset</span>
                  </button>
                </div>

                <div>
                  <label className="block text-[11px] text-gray-600 mb-1">Or Image URL</label>
                  <input
                    type="text"
                    value={featuredImage}
                    onChange={(e) => setFeaturedImage(e.target.value)}
                    placeholder="https://..."
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg text-[11px] font-mono"
                  />
                </div>
              </div>

            </div>

          </div>

        </div>
      ) : (
        /* Blog Post Listing View */
        <div className="space-y-6">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
              <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
                Village Journal CMS Management
              </h1>
              <p className="text-xs text-gray-500 mt-1">
                Manage artisan stories, tool forging documentation, categories, and SEO tags.
              </p>
            </div>

            <div className="flex items-center gap-3">
              <button
                onClick={startCreate}
                className="px-5 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 transition-all cursor-pointer"
              >
                <Plus className="w-4 h-4" />
                <span>Write New Article</span>
              </button>
            </div>
          </div>

          {/* Search bar */}
          <div className="max-w-md relative">
            <input
              type="text"
              placeholder="Filter articles by title or category..."
              value={searchFilter}
              onChange={(e) => setSearchFilter(e.target.value)}
              className="w-full pl-10 pr-4 py-2 text-xs bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-green"
            />
            <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          </div>

          {/* Table */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-gray-50 text-gray-500 border-b border-gray-100 uppercase tracking-wider font-semibold">
                  <tr>
                    <th className="p-4">Article</th>
                    <th className="p-4">Category</th>
                    <th className="p-4">Author</th>
                    <th className="p-4">Status</th>
                    <th className="p-4">Published Date</th>
                    <th className="p-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {filteredList.map(post => (
                    <tr key={post.id} className="hover:bg-gray-50/70 transition-colors">
                      <td className="p-4">
                        <div className="flex items-center gap-3">
                          <img
                            src={post.image}
                            alt={post.title}
                            className="w-12 h-12 rounded-lg object-cover shrink-0"
                          />
                          <div className="min-w-0 max-w-sm">
                            <span className="font-bold text-gray-900 block truncate font-display">
                              {post.title}
                            </span>
                            <span className="text-[11px] text-gray-400 font-mono">
                              /blog/{post.slug}
                            </span>
                          </div>
                        </div>
                      </td>
                      <td className="p-4 font-semibold text-brand-orange">{post.category}</td>
                      <td className="p-4 text-gray-700">{post.author.name}</td>
                      <td className="p-4">
                        <span
                          className={`px-2.5 py-1 rounded-full text-[10px] font-bold ${
                            post.status === 'Published'
                              ? 'bg-emerald-50 text-emerald-800'
                              : post.status === 'Scheduled'
                              ? 'bg-blue-50 text-blue-800'
                              : 'bg-amber-50 text-amber-800'
                          }`}
                        >
                          {post.status}
                        </span>
                      </td>
                      <td className="p-4 text-gray-500">{post.publishedAt}</td>
                      <td className="p-4 text-right space-x-2">
                        <button
                          onClick={() => startEdit(post)}
                          className="px-2.5 py-1 bg-gray-100 hover:bg-emerald-50 text-gray-700 hover:text-brand-green rounded font-semibold text-xs transition-colors cursor-pointer"
                        >
                          Edit
                        </button>
                        <button
                          onClick={() => handleDelete(post.id, post.title)}
                          className="px-2.5 py-1 text-red-600 hover:bg-red-50 rounded font-semibold text-xs transition-colors cursor-pointer"
                        >
                          Delete
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Media Library Asset Picker Modal */}
      {showMediaLibrary && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in">
          <div className="bg-white rounded-3xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 overflow-hidden">
            <div className="p-5 border-b border-gray-100 flex items-center justify-between">
              <div className="flex items-center gap-2">
                <ImageIcon className="w-5 h-5 text-brand-green" />
                <h3 className="font-bold text-base text-gray-900 font-display">
                  Select Media Library Asset
                </h3>
              </div>
              <button
                onClick={() => setShowMediaLibrary(false)}
                className="p-1.5 hover:bg-gray-100 rounded-full text-gray-400 hover:text-gray-700"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-5 overflow-y-auto flex-1">
              <p className="text-xs text-gray-500 mb-4">
                Click any high-resolution photograph to set it as the article's featured thumbnail:
              </p>
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                {MEDIA_LIBRARY_ASSETS.map(asset => (
                  <div
                    key={asset.id}
                    onClick={() => {
                      setFeaturedImage(asset.url);
                      setShowMediaLibrary(false);
                      setToastMessage(`Selected "${asset.title}" as featured thumbnail.`);
                      setTimeout(() => setToastMessage(null), 3000);
                    }}
                    className={`group cursor-pointer rounded-2xl overflow-hidden border transition-all hover:scale-[1.02] ${
                      featuredImage === asset.url
                        ? 'border-brand-green ring-2 ring-brand-green shadow-md'
                        : 'border-gray-200 hover:border-gray-400 shadow-xs'
                    }`}
                  >
                    <div className="aspect-4/3 bg-gray-100 overflow-hidden relative">
                      <img
                        src={asset.url}
                        alt={asset.title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                      {featuredImage === asset.url && (
                        <div className="absolute top-2 right-2 bg-brand-green text-white p-1 rounded-full shadow-xs">
                          <Check className="w-3 h-3" />
                        </div>
                      )}
                    </div>
                    <div className="p-2.5 bg-white text-xs">
                      <span className="font-bold text-gray-900 block truncate text-[11px]">{asset.title}</span>
                      <span className="text-[10px] text-gray-500 font-semibold">{asset.category}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div className="p-4 bg-gray-50 border-t border-gray-100 flex justify-end">
              <button
                type="button"
                onClick={() => setShowMediaLibrary(false)}
                className="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-xs font-bold rounded-xl"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};
