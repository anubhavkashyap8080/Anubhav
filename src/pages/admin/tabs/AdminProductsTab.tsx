import React, { useState } from 'react';
import { usePlatform } from '../../../context/PlatformContext';
import { Product } from '../../../types';
import {
  Package,
  Plus,
  Search,
  Filter,
  Edit3,
  Trash2,
  Copy,
  Check,
  Building2,
  MapPin,
  Phone,
  ShieldCheck,
  ExternalLink,
  AlertCircle,
  Tag,
  CheckCircle2
} from 'lucide-react';

export const AdminProductsTab: React.FC = () => {
  const { products, addProduct, updateProduct, deleteProduct, categories, vendors } = usePlatform();

  // Filters
  const [searchTerm, setSearchTerm] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('all');
  const [stockFilter, setStockFilter] = useState('all');

  // Copy SKU state
  const [copiedSku, setCopiedSku] = useState<string | null>(null);

  // Modal State
  const [showModal, setShowModal] = useState(false);
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);

  // Form State
  const [formSku, setFormSku] = useState('');
  const [formName, setFormName] = useState('');
  const [formHindiName, setFormHindiName] = useState('');
  const [formCategory, setFormCategory] = useState(categories[0]?.id || 'tools');
  const [formPrice, setFormPrice] = useState(499);
  const [formOriginalPrice, setFormOriginalPrice] = useState(799);
  const [formStock, setFormStock] = useState(100);
  const [formUnit, setFormUnit] = useState('Piece');
  const [formImage, setFormImage] = useState('');
  const [formDescription, setFormDescription] = useState('');
  const [selectedVendorId, setSelectedVendorId] = useState(vendors[0]?.id || '');
  const [formIsVillageStaple, setFormIsVillageStaple] = useState(true);

  // Filter products
  const filteredProducts = products.filter((p) => {
    const code = p.code || `GGH-${p.id.slice(0, 4).toUpperCase()}`;
    const matchesSearch =
      p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      code.toLowerCase().includes(searchTerm.toLowerCase()) ||
      p.seller?.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      p.seller?.location.toLowerCase().includes(searchTerm.toLowerCase()) ||
      p.seller?.district?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      p.category.toLowerCase().includes(searchTerm.toLowerCase());

    const pStock = p.stock ?? 50;
    const matchesCategory = categoryFilter === 'all' || p.category === categoryFilter;
    const matchesStock =
      stockFilter === 'all' ||
      (stockFilter === 'in_stock' && pStock > 10) ||
      (stockFilter === 'low_stock' && pStock > 0 && pStock <= 10) ||
      (stockFilter === 'out_of_stock' && pStock === 0);

    return matchesSearch && matchesCategory && matchesStock;
  });

  const handleCopySku = (sku: string) => {
    navigator.clipboard.writeText(sku);
    setCopiedSku(sku);
    setTimeout(() => setCopiedSku(null), 2000);
  };

  const handleOpenAdd = () => {
    setEditingProduct(null);
    const nextNum = products.length + 101;
    setFormSku(`GGH-PR-${nextNum}`);
    setFormName('');
    setFormHindiName('');
    setFormCategory(categories[0]?.id || 'tools');
    setFormPrice(499);
    setFormOriginalPrice(799);
    setFormStock(100);
    setFormUnit('Piece');
    setFormImage('https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=600&q=80');
    setFormDescription('Authentic rural staple handcrafted by regional artisans for village utility.');
    setSelectedVendorId(vendors[0]?.id || '');
    setFormIsVillageStaple(true);
    setShowModal(true);
  };

  const handleOpenEdit = (p: Product) => {
    setEditingProduct(p);
    setFormSku(p.code || `GGH-PR-${p.id.slice(0, 4).toUpperCase()}`);
    setFormName(p.name);
    setFormHindiName(p.name.includes('(') ? p.name.split('(')[1]?.replace(')', '') : '');
    setFormCategory(p.category);
    setFormPrice(p.price);
    setFormOriginalPrice(p.originalPrice || Math.round(p.price * 1.3));
    setFormStock(p.stock ?? 100);
    setFormUnit(p.unit || 'Piece');
    setFormImage(p.image || p.gallery?.[0] || '');
    setFormDescription(p.description || '');
    const matchedVendor = vendors.find(v => v.name.toLowerCase() === p.seller?.name?.toLowerCase());
    setSelectedVendorId(matchedVendor?.id || vendors[0]?.id || '');
    setFormIsVillageStaple(true);
    setShowModal(true);
  };

  const handleSaveProduct = (e: React.FormEvent) => {
    e.preventDefault();
    const vendor = vendors.find(v => v.id === selectedVendorId) || vendors[0];

    const productData: Omit<Product, 'id'> = {
      name: formHindiName ? `${formName} (${formHindiName})` : formName,
      hindiName: formHindiName || undefined,
      code: formSku.trim() || `GGH-PR-${Date.now().toString().slice(-4)}`,
      slug: formName.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-'),
      category: formCategory,
      categorySlug: formCategory,
      subCategory: 'Village Essentials',
      price: Number(formPrice),
      originalPrice: Number(formOriginalPrice),
      discount: Math.round(((Number(formOriginalPrice) - Number(formPrice)) / Number(formOriginalPrice)) * 100),
      rating: editingProduct?.rating || 4.8,
      reviewCount: editingProduct?.reviewCount || 24,
      stock: Number(formStock),
      unit: formUnit,
      image: formImage || 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=600&q=80',
      gallery: [formImage || 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=600&q=80'],
      description: formDescription,
      story: formDescription,
      attributes: {
        'Material': 'High-Carbon Steel / Natural Craft',
        'Origin': vendor?.location || 'Hardoi, Uttar Pradesh'
      },
      stockStatus: (Number(formStock) > 10 ? 'In Stock' : Number(formStock) > 0 ? 'Only Few Left' : 'Out of Stock'),
      seller: {
        id: vendor?.id || 'v-hardoi-01',
        name: vendor?.name || 'S S Industries Works Unit',
        slug: vendor?.slug || 's-s-industries',
        type: (vendor?.type as any) || 'Cottage Industry',
        location: vendor?.location || 'Madhoganj, Hardoi 241126',
        district: vendor?.district || 'Hardoi',
        state: vendor?.state || 'Uttar Pradesh',
        verified: true,
        rating: vendor?.rating || 4.9,
        avatar: vendor?.avatar || 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=150&q=80',
        phone: vendor?.phone || '+91 7897492100'
      },
      tags: ['Village Essential', 'Rural Need', formCategory]
    };

    if (editingProduct) {
      updateProduct(editingProduct.id, productData);
    } else {
      addProduct(productData);
    }
    setShowModal(false);
  };

  const handleDelete = (id: string, name: string) => {
    if (window.confirm(`Are you sure you want to delete "${name}" from the GaunGaliHat catalog?`)) {
      deleteProduct(id);
    }
  };

  // Village staples list indicator check
  const isVillageNeed = (name: string) => {
    const lower = name.toLowerCase();
    return (
      lower.includes('khurpi') ||
      lower.includes('kulhadi') ||
      lower.includes('fawda') ||
      lower.includes('fadawa') ||
      lower.includes('chikan') ||
      lower.includes('mat') ||
      lower.includes('chatai') ||
      lower.includes('net') ||
      lower.includes('sickle') ||
      lower.includes('hansiya') ||
      lower.includes('handi') ||
      lower.includes('bori') ||
      lower.includes('tokri')
    );
  };

  return (
    <div className="space-y-4">
      {/* Top High-Density Metric Strip */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Total Products Listed</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-gray-900">{products.length} Items</span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
              100% Verified
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Village Staples Mapped</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-brand-green">
              {products.filter(p => isVillageNeed(p.name)).length} Essentials
            </span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
              Khurpi, Net, Suit
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Active Artisans & Vendors</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-blue-600">{vendors.length} Foundries</span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
              Direct Source
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">In-Stock Fulfillment</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-emerald-700">
              {products.filter(p => (p.stock ?? 50) > 0).length} Ready
            </span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
              Sandila Hub
            </span>
          </div>
        </div>
      </div>

      {/* Main Catalog Card */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-4 sm:p-5 space-y-4">
        {/* Search, Category, Stock Filters & Add Button */}
        <div className="flex flex-col md:flex-row items-center justify-between gap-3 pb-3 border-b border-gray-100">
          <div className="flex flex-wrap items-center gap-2 w-full md:w-auto flex-1">
            <div className="relative flex-1 min-w-[220px]">
              <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                placeholder="Search SKU code, product name, vendor, district..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:border-brand-green outline-hidden"
              />
            </div>

            <select
              value={categoryFilter}
              onChange={(e) => setCategoryFilter(e.target.value)}
              className="px-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50 text-gray-700 outline-hidden font-medium"
            >
              <option value="all">All Categories ({categories.length})</option>
              {categories.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.name}
                </option>
              ))}
            </select>

            <select
              value={stockFilter}
              onChange={(e) => setStockFilter(e.target.value)}
              className="px-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50 text-gray-700 outline-hidden font-medium"
            >
              <option value="all">All Stock Status</option>
              <option value="in_stock">In Stock (&gt;10)</option>
              <option value="low_stock">Low Stock (1-10)</option>
              <option value="out_of_stock">Out of Stock (0)</option>
            </select>
          </div>

          <div className="flex items-center gap-2 w-full md:w-auto justify-end">
            <span className="text-xs text-gray-500 font-semibold hidden lg:inline">
              Showing {filteredProducts.length} of {products.length} products
            </span>
            <button
              onClick={handleOpenAdd}
              className="px-3.5 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap"
            >
              <Plus className="w-4 h-4" />
              <span>Add New Product</span>
            </button>
          </div>
        </div>

        {/* Product Table */}
        <div className="overflow-x-auto text-xs">
          <table className="w-full text-left border-collapse">
            <thead className="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] tracking-wider">
              <tr>
                <th className="p-3">Unique Code (SKU)</th>
                <th className="p-3">Product Name & Category</th>
                <th className="p-3">Vendor / Artisan Supply Hub</th>
                <th className="p-3 text-right">Price / MRP</th>
                <th className="p-3 text-center">Stock</th>
                <th className="p-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {filteredProducts.map((p) => {
                const sku = p.code || `GGH-${p.id.slice(0, 4).toUpperCase()}`;
                const staple = isVillageNeed(p.name);
                return (
                  <tr key={p.id} className="hover:bg-gray-50/80 transition-colors">
                    {/* SKU Column */}
                    <td className="p-3 whitespace-nowrap">
                      <div className="flex items-center gap-1.5">
                        <span className="font-mono text-xs font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded-md border border-gray-200">
                          {sku}
                        </span>
                        <button
                          onClick={() => handleCopySku(sku)}
                          title="Copy Unique Code"
                          className="p-1 text-gray-400 hover:text-gray-700 rounded transition-colors cursor-pointer"
                        >
                          {copiedSku === sku ? (
                            <Check className="w-3.5 h-3.5 text-emerald-600" />
                          ) : (
                            <Copy className="w-3.5 h-3.5" />
                          )}
                        </button>
                      </div>
                      {staple && (
                        <span className="inline-block mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-900">
                          Village Staple
                        </span>
                      )}
                    </td>

                    {/* Product Name & Category */}
                    <td className="p-3">
                      <div className="flex items-center gap-3">
                        <img
                          src={p.image || p.gallery?.[0] || 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=150&q=80'}
                          alt={p.name}
                          className="w-11 h-11 rounded-lg object-cover border border-gray-200 shrink-0"
                        />
                        <div className="min-w-0">
                          <p className="font-bold text-gray-900 leading-snug line-clamp-1">{p.name}</p>
                          <div className="flex items-center gap-2 mt-0.5">
                            <span className="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 capitalize">
                              {p.category}
                            </span>
                            <span className="text-[10px] text-gray-400">★ {p.rating} ({p.reviewCount})</span>
                          </div>
                        </div>
                      </div>
                    </td>

                    {/* Vendor Details */}
                    <td className="p-3">
                      <div className="space-y-0.5">
                        <div className="flex items-center gap-1 font-bold text-gray-900">
                          <Building2 className="w-3 h-3 text-brand-green" />
                          <span>{p.seller?.name || 'S S Industries Works Unit'}</span>
                        </div>
                        <div className="flex items-center gap-1 text-[11px] text-gray-500">
                          <MapPin className="w-3 h-3 text-gray-400" />
                          <span>{p.seller?.location || 'Madhoganj, Hardoi'} ({p.seller?.district || 'Hardoi'})</span>
                        </div>
                        <div className="flex items-center gap-1 text-[11px] text-brand-green font-medium">
                          <Phone className="w-3 h-3 text-gray-400" />
                          <a href={`tel:${p.seller?.phone || '+917897492100'}`} className="hover:underline font-mono">
                            {p.seller?.phone || '+91 7897492100'}
                          </a>
                          <span className="text-[9px] bg-emerald-50 text-emerald-700 px-1 rounded font-bold ml-1">
                            KYC Verified
                          </span>
                        </div>
                      </div>
                    </td>

                    {/* Price / MRP */}
                    <td className="p-3 text-right whitespace-nowrap">
                      <div className="font-bold text-gray-900 text-sm">₹{p.price.toLocaleString()}</div>
                      {p.originalPrice && (
                        <div className="text-[11px] text-gray-400 line-through">
                          ₹{p.originalPrice.toLocaleString()}
                        </div>
                      )}
                      {p.discount && (
                        <div className="text-[10px] font-bold text-emerald-600">{p.discount}% OFF</div>
                      )}
                    </td>

                    {/* Stock */}
                    <td className="p-3 text-center whitespace-nowrap">
                      {(() => {
                        const st = p.stock ?? 50;
                        return (
                          <span
                            className={`inline-block px-2 py-0.5 rounded-full text-[10px] font-bold ${
                              st > 10
                                ? 'bg-emerald-100 text-emerald-800'
                                : st > 0
                                ? 'bg-amber-100 text-amber-800'
                                : 'bg-red-100 text-red-800'
                            }`}
                          >
                            {st > 0 ? `${st} ${p.unit || 'units'}` : 'Out of stock'}
                          </span>
                        );
                      })()}
                    </td>

                    {/* Actions */}
                    <td className="p-3 text-right whitespace-nowrap">
                      <div className="flex items-center justify-end gap-1">
                        <button
                          onClick={() => handleOpenEdit(p)}
                          className="p-1.5 text-gray-500 hover:text-brand-green hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                          title="Edit Product"
                        >
                          <Edit3 className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => handleDelete(p.id, p.name)}
                          className="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                          title="Delete Product"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* Add / Edit Product Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-white rounded-3xl p-6 max-w-2xl w-full space-y-4 shadow-2xl animate-in zoom-in-95 duration-150 my-8">
            <div className="flex items-center justify-between pb-3 border-b border-gray-100">
              <div>
                <h4 className="font-bold text-lg text-gray-900 font-display">
                  {editingProduct ? 'Edit Product SKU' : 'Add New Village Staple / Product'}
                </h4>
                <p className="text-xs text-gray-500">
                  Manage product catalog with unique code, vendor provenance and stock levels.
                </p>
              </div>
              <button
                onClick={() => setShowModal(false)}
                className="text-gray-400 hover:text-gray-600 text-sm font-bold cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleSaveProduct} className="space-y-4 text-xs">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {/* SKU Code */}
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Unique Product ID / SKU <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={formSku}
                    onChange={(e) => setFormSku(e.target.value)}
                    placeholder="e.g. GGH-TL-105"
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-mono font-bold"
                  />
                  <span className="text-[10px] text-gray-400 mt-0.5 block">Every item must have a unique identifier.</span>
                </div>

                {/* Category */}
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Category <span className="text-red-500">*</span>
                  </label>
                  <select
                    value={formCategory}
                    onChange={(e) => setFormCategory(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl bg-white"
                  >
                    {categories.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              {/* Product Names */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Product Title (English) <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={formName}
                    onChange={(e) => setFormName(e.target.value)}
                    placeholder="e.g. Forged Iron Woodcutting Kulhadi"
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Local / Hindi Name (खुरपी, कुल्हाड़ी, फावड़ा, चिकन सूट)
                  </label>
                  <input
                    type="text"
                    value={formHindiName}
                    onChange={(e) => setFormHindiName(e.target.value)}
                    placeholder="e.g. कुल्हाड़ी / Kulhadi"
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>
              </div>

              {/* Vendor Supply Hub */}
              <div>
                <label className="block font-semibold text-gray-700 mb-1">
                  Source Vendor / Foundry Hub <span className="text-red-500">*</span>
                </label>
                <select
                  value={selectedVendorId}
                  onChange={(e) => setSelectedVendorId(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl bg-white font-medium"
                >
                  {vendors.map((v) => (
                    <option key={v.id} value={v.id}>
                      {v.name} — {v.location} ({v.type})
                    </option>
                  ))}
                </select>
                <span className="text-[10px] text-gray-500 mt-1 block">
                  Assigns direct vendor traceability, KYC status and workshop contact details.
                </span>
              </div>

              {/* Price & Stock */}
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Selling Price (₹) <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    min={1}
                    required
                    value={formPrice}
                    onChange={(e) => setFormPrice(Number(e.target.value))}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Original MRP (₹)</label>
                  <input
                    type="number"
                    min={1}
                    value={formOriginalPrice}
                    onChange={(e) => setFormOriginalPrice(Number(e.target.value))}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Stock Quantity</label>
                  <input
                    type="number"
                    min={0}
                    value={formStock}
                    onChange={(e) => setFormStock(Number(e.target.value))}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Unit</label>
                  <input
                    type="text"
                    value={formUnit}
                    onChange={(e) => setFormUnit(e.target.value)}
                    placeholder="Piece / Pair / Set"
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>
              </div>

              {/* Image URL & Description */}
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Image CDN / URL</label>
                <input
                  type="url"
                  value={formImage}
                  onChange={(e) => setFormImage(e.target.value)}
                  placeholder="https://..."
                  className="w-full p-2.5 border border-gray-300 rounded-xl text-xs"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Product Description</label>
                <textarea
                  rows={2}
                  value={formDescription}
                  onChange={(e) => setFormDescription(e.target.value)}
                  placeholder="Details on material, gauge, craft tradition, and agricultural or household usage..."
                  className="w-full p-2.5 border border-gray-300 rounded-xl text-xs"
                ></textarea>
              </div>

              <div className="flex gap-2 pt-2 border-t border-gray-100">
                <button
                  type="submit"
                  className="flex-1 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                >
                  {editingProduct ? 'Update Product SKU' : 'Add to Catalog'}
                </button>
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-5 py-2.5 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 font-semibold cursor-pointer"
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
