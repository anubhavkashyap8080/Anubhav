import React, { useState } from 'react';
import { usePlatform } from '../../../context/PlatformContext';
import { Category } from '../../../types';
import {
  Layers,
  Plus,
  Search,
  Edit3,
  Trash2,
  CheckCircle2,
  XCircle,
  Tag,
  Package,
  Sparkles,
  ExternalLink
} from 'lucide-react';

export const AdminCategoriesTab: React.FC = () => {
  const { categories, addCategory, updateCategory, deleteCategory, products } = usePlatform();

  const [searchTerm, setSearchTerm] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);

  // Form State
  const [formName, setFormName] = useState('');
  const [formHindiName, setFormHindiName] = useState('');
  const [formSlug, setFormSlug] = useState('');
  const [formImage, setFormImage] = useState('');
  const [formDescription, setFormDescription] = useState('');
  const [formStatus, setFormStatus] = useState<'active' | 'inactive'>('active');

  const filteredCategories = categories.filter((c) => {
    return (
      c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      c.slug.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (c.hindiName && c.hindiName.includes(searchTerm)) ||
      (c.description && c.description.toLowerCase().includes(searchTerm.toLowerCase()))
    );
  });

  const getProductCount = (categorySlug: string) => {
    return products.filter((p) => p.category === categorySlug).length;
  };

  const handleOpenAdd = () => {
    setEditingCategory(null);
    setFormName('');
    setFormHindiName('');
    setFormSlug('');
    setFormImage('https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=600&q=80');
    setFormDescription('');
    setFormStatus('active');
    setShowModal(true);
  };

  const handleOpenEdit = (c: Category) => {
    setEditingCategory(c);
    setFormName(c.name);
    setFormHindiName(c.hindiName || '');
    setFormSlug(c.slug);
    setFormImage(c.image);
    setFormDescription(c.description || '');
    setFormStatus(c.status || 'active');
    setShowModal(true);
  };

  const handleNameChange = (val: string) => {
    setFormName(val);
    if (!editingCategory) {
      setFormSlug(
        val
          .toLowerCase()
          .replace(/[^\w\s-]/g, '')
          .replace(/\s+/g, '-')
      );
    }
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formName.trim() || !formSlug.trim()) return;

    const catData: Omit<Category, 'id'> = {
      name: formName.trim(),
      hindiName: formHindiName.trim() || undefined,
      slug: formSlug.trim(),
      image: formImage.trim() || 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=600&q=80',
      description: formDescription.trim(),
      productCount: editingCategory ? editingCategory.productCount : getProductCount(formSlug.trim()),
      itemCount: editingCategory ? editingCategory.itemCount : getProductCount(formSlug.trim()),
      iconName: 'Layers',
      status: formStatus
    };

    if (editingCategory) {
      updateCategory(editingCategory.id, catData);
    } else {
      addCategory(catData);
    }
    setShowModal(false);
  };

  const handleDelete = (id: string, name: string) => {
    if (window.confirm(`Are you sure you want to delete category "${name}"? Products in this category will remain in catalog.`)) {
      deleteCategory(id);
    }
  };

  const handleToggleStatus = (category: Category) => {
    const nextStatus = category.status === 'inactive' ? 'active' : 'inactive';
    updateCategory(category.id, { status: nextStatus });
  };

  return (
    <div className="space-y-4">
      {/* Metrics Row */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Total Categories</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-gray-900">{categories.length}</span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
              Active Taxonomy
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Live On Storefront</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-brand-green">
              {categories.filter((c) => c.status !== 'inactive').length} Active
            </span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-brand-green/10 text-brand-green">
              Published
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Mapped Catalog SKUs</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-blue-600">{products.length} Items</span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
              Assigned
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Top Volume Segment</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-amber-700">Farming Tools</span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
              44% GMV
            </span>
          </div>
        </div>
      </div>

      {/* Categories Card */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-4 sm:p-5 space-y-4">
        {/* Controls */}
        <div className="flex flex-col sm:flex-row items-center justify-between gap-3 pb-3 border-b border-gray-100">
          <div className="relative w-full sm:w-72">
            <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Search category name or slug..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:border-brand-green outline-hidden"
            />
          </div>

          <div className="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button
              onClick={handleOpenAdd}
              className="px-3.5 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap"
            >
              <Plus className="w-4 h-4" />
              <span>Add New Category</span>
            </button>
          </div>
        </div>

        {/* Categories Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredCategories.map((c) => {
            const count = getProductCount(c.slug);
            const isActive = c.status !== 'inactive';
            return (
              <div
                key={c.id}
                className="p-3.5 rounded-xl border border-gray-200 hover:border-brand-green/40 bg-white transition-all shadow-2xs flex flex-col justify-between gap-3"
              >
                <div className="flex items-start gap-3">
                  <img
                    src={c.image}
                    alt={c.name}
                    className="w-14 h-14 rounded-lg object-cover border border-gray-200 shrink-0"
                  />
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center justify-between gap-1">
                      <h4 className="font-bold text-gray-900 text-sm truncate">{c.name}</h4>
                      <button
                        onClick={() => handleToggleStatus(c)}
                        title="Click to toggle active/inactive"
                        className={`px-1.5 py-0.5 rounded text-[9px] font-bold cursor-pointer ${
                          isActive
                            ? 'bg-emerald-100 text-emerald-800'
                            : 'bg-gray-100 text-gray-600'
                        }`}
                      >
                        {isActive ? 'Active' : 'Inactive'}
                      </button>
                    </div>

                    {c.hindiName && (
                      <p className="text-[11px] text-brand-green font-medium">{c.hindiName}</p>
                    )}

                    <div className="flex items-center gap-2 mt-1">
                      <span className="font-mono text-[10px] text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">
                        /{c.slug}
                      </span>
                      <span className="text-[10px] font-bold text-gray-600">
                        {count} Products
                      </span>
                    </div>
                  </div>
                </div>

                <p className="text-xs text-gray-500 line-clamp-2">
                  {c.description || 'Authentic rural artisan products curated for GaunGaliHat marketplace.'}
                </p>

                <div className="flex items-center justify-between pt-2 border-t border-gray-100 text-xs">
                  <a
                    href={`/category/${c.slug}`}
                    target="_blank"
                    rel="noreferrer"
                    className="text-gray-500 hover:text-brand-green text-[11px] font-semibold flex items-center gap-1"
                  >
                    <span>View Category</span>
                    <ExternalLink className="w-3 h-3" />
                  </a>

                  <div className="flex items-center gap-1">
                    <button
                      onClick={() => handleOpenEdit(c)}
                      className="p-1.5 text-gray-500 hover:text-brand-green hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                      title="Edit Category"
                    >
                      <Edit3 className="w-4 h-4" />
                    </button>
                    <button
                      onClick={() => handleDelete(c.id, c.name)}
                      className="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                      title="Delete Category"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Add / Edit Category Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-md w-full space-y-4 shadow-2xl animate-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between pb-3 border-b border-gray-100">
              <h4 className="font-bold text-base text-gray-900 font-display">
                {editingCategory ? 'Edit Category' : 'Create New Category'}
              </h4>
              <button
                onClick={() => setShowModal(false)}
                className="text-gray-400 hover:text-gray-600 font-bold cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleSave} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">
                  Category Name (English) <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  required
                  value={formName}
                  onChange={(e) => handleNameChange(e.target.value)}
                  placeholder="e.g. Traditional Handlooms"
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">
                  Local / Hindi Name
                </label>
                <input
                  type="text"
                  value={formHindiName}
                  onChange={(e) => setFormHindiName(e.target.value)}
                  placeholder="e.g. हथकरघा वस्त्र व साड़ियाँ"
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">
                  URL Slug <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  required
                  value={formSlug}
                  onChange={(e) => setFormSlug(e.target.value)}
                  placeholder="e.g. traditional-handlooms"
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-xs"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">
                  Category Image URL
                </label>
                <input
                  type="url"
                  value={formImage}
                  onChange={(e) => setFormImage(e.target.value)}
                  placeholder="https://images.unsplash.com/..."
                  className="w-full p-2.5 border border-gray-300 rounded-xl text-xs"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Description</label>
                <textarea
                  rows={2}
                  value={formDescription}
                  onChange={(e) => setFormDescription(e.target.value)}
                  placeholder="Brief description of the crafts and goods under this category..."
                  className="w-full p-2.5 border border-gray-300 rounded-xl text-xs"
                ></textarea>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Storefront Visibility</label>
                <select
                  value={formStatus}
                  onChange={(e) => setFormStatus(e.target.value as any)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl bg-white"
                >
                  <option value="active">Active (Visible in Navigation & Filters)</option>
                  <option value="inactive">Inactive (Hidden from Customers)</option>
                </select>
              </div>

              <div className="flex gap-2 pt-2 border-t border-gray-100">
                <button
                  type="submit"
                  className="flex-1 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                >
                  {editingCategory ? 'Save Changes' : 'Create Category'}
                </button>
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2.5 border border-gray-300 rounded-xl text-gray-700 cursor-pointer"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
