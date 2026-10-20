import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import {
  Store,
  Users,
  ShieldCheck,
  CheckCircle2,
  Building,
  CreditCard,
  ArrowRight,
  Info
} from 'lucide-react';
import { useDemoModal } from '../context/DemoModalContext';

export const SellerRegister: React.FC = () => {
  const { showDemoNotice } = useDemoModal();

  const [formData, setFormData] = useState({
    artisanName: 'Shanti Devi',
    groupName: 'Kalyani Mahila Bachat Gat',
    sellerType: 'SHG',
    phone: '9876501234',
    village: 'Village Madhupur',
    district: 'Deoghar',
    state: 'Jharkhand',
    category: 'Handmade Apparel & Bangles',
    memberCount: '15',
    bankAccount: '918237461298',
    ifsc: 'SBIN0001234',
    udyamNumber: 'UDYAM-JH-04-0019283',
    story: 'We are a group of 15 tribal women artisans producing hand-rolled lac bangles and handloom stoles.'
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    showDemoNotice(
      'Seller Registration Submission',
      'This feature is currently available for preview only. Backend functionality and artisan verification will be implemented in a future phase.'
    );
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3 text-xs text-amber-900">
        <Info className="w-5 h-5 text-brand-orange shrink-0 mt-0.5" />
        <div>
          <span className="font-bold">Artisan Onboarding Preview:</span> This form simulates the multi-step verification process for rural SHGs, individual artisans, and farmer cooperatives. Submissions do not create live accounts or store KYC documents in Phase 1.
        </div>
      </div>

      <div className="text-center space-y-2">
        <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
          Rural Seller Onboarding Application
        </h1>
        <p className="text-xs sm:text-sm text-gray-600">
          Join India's dedicated direct-to-consumer rural marketplace.
        </p>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-3xl border border-gray-100 shadow-md p-6 sm:p-10 space-y-8">
        
        {/* Section 1: Basic Artisan & Group Profile */}
        <div className="space-y-4">
          <div className="flex items-center gap-2 pb-2 border-b border-gray-100 font-bold text-base text-gray-900 font-display">
            <Users className="w-5 h-5 text-brand-green" />
            <span>1. Organization & Contact Details</span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Leader / Artisan Full Name *
              </label>
              <input
                type="text"
                value={formData.artisanName}
                onChange={(e) => setFormData({ ...formData, artisanName: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                SHG / Cooperative / Business Name *
              </label>
              <input
                type="text"
                value={formData.groupName}
                onChange={(e) => setFormData({ ...formData, groupName: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Seller Type *
              </label>
              <select
                value={formData.sellerType}
                onChange={(e) => setFormData({ ...formData, sellerType: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
              >
                <option value="SHG">Women Self-Help Group (SHG)</option>
                <option value="Artisan">Individual Master Artisan</option>
                <option value="Farmer">Farmer Producer Organization (FPO)</option>
                <option value="Cottage Industry">Rural Cottage Unit</option>
              </select>
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Active Contact Mobile Number *
              </label>
              <input
                type="text"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Number of Member Artisans / Homemakers
              </label>
              <input
                type="number"
                value={formData.memberCount}
                onChange={(e) => setFormData({ ...formData, memberCount: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Primary Craft / Harvest Category *
              </label>
              <select
                value={formData.category}
                onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
              >
                <option value="Farming & Traditional Tools">Farming & Traditional Tools</option>
                <option value="Jute Bags & Cloth Jholas">Jute Bags & Cloth Jholas</option>
                <option value="Handmade Apparel & Bangles">Handmade Apparel & Bangles</option>
                <option value="Village Home Decor & Toys">Village Home Decor & Toys</option>
                <option value="Clay Pottery & Terracotta">Clay Pottery & Terracotta</option>
                <option value="Handloom & Dhurrie Weaving">Handloom & Dhurrie Weaving</option>
              </select>
            </div>
          </div>
        </div>

        {/* Section 2: Rural Location */}
        <div className="space-y-4">
          <div className="flex items-center gap-2 pb-2 border-b border-gray-100 font-bold text-base text-gray-900 font-display">
            <Store className="w-5 h-5 text-brand-orange" />
            <span>2. Village & Workshop Location</span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Village / Gram Panchayat *</label>
              <input
                type="text"
                value={formData.village}
                onChange={(e) => setFormData({ ...formData, village: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">District *</label>
              <input
                type="text"
                value={formData.district}
                onChange={(e) => setFormData({ ...formData, district: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">State *</label>
              <input
                type="text"
                value={formData.state}
                onChange={(e) => setFormData({ ...formData, state: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>
          </div>

          <div className="text-xs">
            <label className="block font-semibold text-gray-700 mb-1">
              Brief Story of Your Craft & Artisan Collective
            </label>
            <textarea
              rows={3}
              value={formData.story}
              onChange={(e) => setFormData({ ...formData, story: e.target.value })}
              className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
            />
          </div>
        </div>

        {/* Section 3: Bank Payout & KYC Details */}
        <div className="space-y-4">
          <div className="flex items-center gap-2 pb-2 border-b border-gray-100 font-bold text-base text-gray-900 font-display">
            <CreditCard className="w-5 h-5 text-brand-green" />
            <span>3. Bank Payout & Verification Preview</span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Bank Account Number *</label>
              <input
                type="text"
                value={formData.bankAccount}
                onChange={(e) => setFormData({ ...formData, bankAccount: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg font-mono text-xs"
                required
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Bank IFSC Code *</label>
              <input
                type="text"
                value={formData.ifsc}
                onChange={(e) => setFormData({ ...formData, ifsc: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg font-mono text-xs uppercase"
                required
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Udyam / SHG Reg. Number</label>
              <input
                type="text"
                value={formData.udyamNumber}
                onChange={(e) => setFormData({ ...formData, udyamNumber: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg font-mono text-xs uppercase"
              />
            </div>
          </div>
        </div>

        {/* Submit Button */}
        <div className="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-xs text-gray-500">
            By submitting, you agree to GanvGaliHut's Artisan Purity Code and fair compensation standards.
          </p>

          <button
            type="submit"
            className="w-full sm:w-auto px-8 py-3.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-md transition-all cursor-pointer active:scale-95"
          >
            Submit Application (Demo)
          </button>
        </div>

      </form>
    </div>
  );
};
