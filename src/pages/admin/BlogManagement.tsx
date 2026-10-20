import React, { useState } from 'react';
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
  Info
} from 'lucide-react';
import { mockBlogPosts, mockBlogCategories } from '../../data/mockBlogPosts';
import { BlogPost } from '../../types';
import { useDemoModal } from '../../context/DemoModalContext';

export const BlogManagement: React.FC = () => {
  const { showDemoNotice } = useDemoModal();
  const [posts, setPosts] = useState<BlogPost[]>(mockBlogPosts);
  const [editingPost, setEditingPost] = useState<BlogPost | null>(null);
  const [isCreatingNew, setIsCreatingNew] = useState(false);
  const [searchFilter, setSearchFilter] = useState('');

  // Form State for Editing/Creating
  const [title, setTitle] = useState('');
  const [slug, setSlug] = useState('');
  const [excerpt, setExcerpt] = useState('');
  const [content, setContent] = useState('');
  const [category, setCategory] = useState(mockBlogCategories[1]);
  const [authorName, setAuthorName] = useState('Ananya Verma');
  const [status, setStatus] = useState<'Published' | 'Draft' | 'Scheduled'>('Draft');
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
    setCategory(mockBlogCategories[1]);
    setAuthorName('Editorial Contributor');
    setStatus('Draft');
    setFeaturedImage('https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=800&q=80');
    setSeoTitle('');
    setMetaDescription('');
    setCanonicalUrl('https://ganvgalihat.com/blog/sample-draft');
    setIndexRobots(true);
  };

  const handleAction = (actionName: string) => {
    // Section 8 requirement: All CMS actions must show demo popup
    showDemoNotice(
      actionName,
      'This feature is currently available for preview only. Backend functionality will be implemented in a future phase. Articles are not saved or published permanently.'
    );
  };

  const filteredList = posts.filter(p =>
    p.title.toLowerCase().includes(searchFilter.toLowerCase()) ||
    p.category.toLowerCase().includes(searchFilter.toLowerCase())
  );

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between gap-4 text-xs text-amber-900">
        <div className="flex items-center gap-3">
          <Info className="w-5 h-5 text-brand-orange shrink-0" />
          <div>
            <span className="font-bold">Editorial CMS Demonstration:</span> Simulated blog post management, rich-text styling toolbar, and SEO Open Graph metadata inspector.
          </div>
        </div>
        <span className="px-2.5 py-1 bg-amber-200/80 font-bold rounded-lg shrink-0">Demo CMS Studio</span>
      </div>

      {/* Editor Modal / View */}
      {(editingPost || isCreatingNew) ? (
        <div className="bg-white rounded-3xl border border-gray-200 shadow-xl p-6 sm:p-10 space-y-8 animate-in fade-in duration-200">
          
          <div className="flex items-center justify-between pb-4 border-b border-gray-100">
            <div>
              <span className="text-xs font-bold text-brand-orange uppercase tracking-wider">
                {isCreatingNew ? 'Create New Post' : `Editing: ${editingPost?.title}`}
              </span>
              <h2 className="text-xl sm:text-2xl font-bold text-gray-900 font-display mt-0.5">
                Article Editor & SEO Publishing Preview
              </h2>
            </div>

            <button
              onClick={() => {
                setEditingPost(null);
                setIsCreatingNew(false);
              }}
              className="p-2 text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-100"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {/* Left: Article Details & Rich Content Area */}
            <div className="lg:col-span-8 space-y-5 text-xs">
              <div>
                <label className="block font-bold text-gray-700 mb-1">Article Headline *</label>
                <input
                  type="text"
                  placeholder="e.g. Traditional Sun-Drying Techniques in Eastern UP"
                  value={title}
                  onChange={(e) => {
                    setTitle(e.target.value);
                    if (isCreatingNew) {
                      setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').slice(0, 50));
                      setSeoTitle(e.target.value);
                    }
                  }}
                  className="w-full text-sm font-semibold p-3 bg-gray-50 border border-gray-200 rounded-xl"
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

              {/* Rich-Text Toolbar Simulation */}
              <div>
                <label className="block font-bold text-gray-700 mb-1">Article Body Content</label>
                <div className="border border-gray-200 rounded-xl overflow-hidden bg-white">
                  <div className="p-2 bg-gray-100 border-b border-gray-200 flex flex-wrap items-center gap-1.5 text-gray-700">
                    <button type="button" onClick={() => handleAction('Format Bold')} className="p-1.5 hover:bg-white rounded" title="Bold"><Bold className="w-3.5 h-3.5" /></button>
                    <button type="button" onClick={() => handleAction('Format Italic')} className="p-1.5 hover:bg-white rounded" title="Italic"><Italic className="w-3.5 h-3.5" /></button>
                    <span className="text-gray-300">|</span>
                    <button type="button" onClick={() => handleAction('Heading 2')} className="p-1.5 hover:bg-white rounded" title="H2"><Heading2 className="w-3.5 h-3.5" /></button>
                    <button type="button" onClick={() => handleAction('Heading 3')} className="p-1.5 hover:bg-white rounded" title="H3"><Heading3 className="w-3.5 h-3.5" /></button>
                    <span className="text-gray-300">|</span>
                    <button type="button" onClick={() => handleAction('Insert Quote')} className="p-1.5 hover:bg-white rounded" title="Blockquote"><Quote className="w-3.5 h-3.5" /></button>
                    <button type="button" onClick={() => handleAction('Bullet List')} className="p-1.5 hover:bg-white rounded" title="Bullet List"><List className="w-3.5 h-3.5" /></button>
                    <button type="button" onClick={() => handleAction('Insert Hyperlink')} className="p-1.5 hover:bg-white rounded" title="Link"><LinkIcon className="w-3.5 h-3.5" /></button>
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
                      className="rounded text-brand-green"
                    />
                    <span className="font-semibold text-gray-800">Allow search engine crawlers to index this story (index, follow)</span>
                  </label>
                </div>
              </div>

            </div>

            {/* Right: Sidebar Options, Image Selector, Actions */}
            <div className="lg:col-span-4 space-y-6 text-xs">
              
              {/* Publication Status & Actions */}
              <div className="p-5 rounded-2xl bg-white border border-gray-200 shadow-xs space-y-4">
                <h3 className="font-bold text-gray-900 border-b border-gray-100 pb-2">Publishing Actions</h3>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Status</label>
                  <select
                    value={status}
                    onChange={(e: any) => setStatus(e.target.value)}
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg font-semibold"
                  >
                    <option value="Draft">Draft</option>
                    <option value="Published">Published</option>
                    <option value="Scheduled">Scheduled</option>
                  </select>
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Editorial Category</label>
                  <select
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg"
                  >
                    {mockBlogCategories.filter(c => c !== 'All').map(cat => (
                      <option key={cat} value={cat}>{cat}</option>
                    ))}
                  </select>
                </div>

                <div className="pt-2 space-y-2">
                  <button
                    type="button"
                    onClick={() => handleAction('Publish Article')}
                    className="w-full py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer"
                  >
                    <Send className="w-3.5 h-3.5" />
                    <span>Publish Story (Demo)</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => handleAction('Save Draft')}
                    className="w-full py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                  >
                    <Save className="w-3.5 h-3.5" />
                    <span>Save Draft (Demo)</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => handleAction('Schedule Publication')}
                    className="w-full py-2 bg-white border border-gray-300 hover:bg-gray-50 text-brand-orange font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                  >
                    <Calendar className="w-3.5 h-3.5" />
                    <span>Schedule Release (Demo)</span>
                  </button>
                </div>
              </div>

              {/* Featured Image Preview & Selector */}
              <div className="p-5 rounded-2xl bg-white border border-gray-200 shadow-xs space-y-3">
                <h3 className="font-bold text-gray-900 border-b border-gray-100 pb-2">Featured Image & Open Graph</h3>
                <div className="aspect-16/9 rounded-xl overflow-hidden bg-gray-100 border border-gray-200">
                  <img
                    src={featuredImage}
                    alt="Featured preview"
                    className="w-full h-full object-cover"
                  />
                </div>
                <div>
                  <label className="block text-[11px] text-gray-600 mb-1">Image URL Preview</label>
                  <input
                    type="text"
                    value={featuredImage}
                    onChange={(e) => setFeaturedImage(e.target.value)}
                    className="w-full p-2 bg-gray-50 border border-gray-200 rounded-lg text-[11px]"
                  />
                </div>
                <button
                  type="button"
                  onClick={() => handleAction('Upload / Select Image')}
                  className="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-xs"
                >
                  Choose Media Library Asset
                </button>
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
                Manage articles, author assignments, categories, and SEO search tags.
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
                          onClick={() => handleAction(`Delete Article: "${post.title}"`)}
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

    </div>
  );
};
