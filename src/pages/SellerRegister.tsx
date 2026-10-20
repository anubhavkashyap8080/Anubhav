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
  Upload,
  FileCheck,
  FileText,
  AlertCircle,
  HelpCircle,
  ChevronRight,
  Phone,
  Check
} from 'lucide-react';

export const SellerRegister: React.FC = () => {
  // Submission success state
  const [submittedRef, setSubmittedRef] = useState<string | null>(null);

  // Form Data State
  const [formData, setFormData] = useState({
    artisanName: '',
    groupName: '',
    sellerType: 'SHG',
    phone: '',
    email: '',
    village: '',
    district: '',
    state: 'Uttar Pradesh',
    pincode: '',
    category: 'Farming & Traditional Tools',
    memberCount: '12',
    story: '',
    // Tax & GST Flow
    hasGst: 'no', // 'yes' | 'no'
    gstin: '',
    gstCertificateName: '',
    gstCertificateFile: '',
    // Alternative KYC Documents
    altDocType: 'Aadhaar Card', // 'Aadhaar Card' | 'PAN Card' | 'Udyam Registration' | 'Gram Panchayat Certificate' | 'SHG Bank Passbook'
    altDocNumber: '',
    altDocFileName: '',
    altDocFile: '',
    // Bank Payout Details
    bankBeneficiaryName: '',
    bankName: '',
    bankAccount: '',
    confirmBankAccount: '',
    ifsc: '',
    bankDocFileName: '',
    bankDocFile: ''
  });

  const [formError, setFormError] = useState<string | null>(null);

  // File Upload Helper
  const handleFileUpload = (
    e: React.ChangeEvent<HTMLInputElement>,
    fileField: 'gstCertificateFile' | 'altDocFile' | 'bankDocFile',
    nameField: 'gstCertificateName' | 'altDocFileName' | 'bankDocFileName'
  ) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setFormData(prev => ({
          ...prev,
          [fileField]: reader.result as string,
          [nameField]: file.name
        }));
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setFormError(null);

    // Validate Account Numbers match
    if (formData.bankAccount !== formData.confirmBankAccount) {
      setFormError('Bank Account Numbers do not match. Please verify your details.');
      return;
    }

    // Validate GST or Alternative Document
    if (formData.hasGst === 'yes') {
      if (!formData.gstin.trim() || formData.gstin.trim().length < 15) {
        setFormError('Please enter a valid 15-character GSTIN Number.');
        return;
      }
    } else {
      if (!formData.altDocNumber.trim()) {
        setFormError(`Please enter your valid ${formData.altDocType} Document Number.`);
        return;
      }
    }

    // Generate unique verification tracking reference code
    const generatedRef = `GGH-KYC-2026-${Math.floor(1000 + Math.random() * 9000)}`;

    const applicationRecord = {
      ...formData,
      refId: generatedRef,
      submittedAt: new Date().toISOString(),
      status: 'Under Verification',
      verificationStage: 'Document Authenticity Check'
    };

    // Save to LocalStorage
    try {
      const existing = JSON.parse(localStorage.getItem('ggh_pending_sellers') || '[]');
      existing.push(applicationRecord);
      localStorage.setItem('ggh_pending_sellers', JSON.stringify(existing));
    } catch (err) {
      console.warn('Failed to save to local storage:', err);
    }

    setSubmittedRef(generatedRef);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // If application was submitted, show confirmation dashboard
  if (submittedRef) {
    return (
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-8 animate-in fade-in duration-300">
        <div className="bg-white rounded-3xl border border-gray-100 shadow-xl p-8 sm:p-12 text-center space-y-6">
          <div className="w-16 h-16 rounded-2xl bg-emerald-100 text-brand-green flex items-center justify-center mx-auto shadow-inner">
            <CheckCircle2 className="w-10 h-10" />
          </div>

          <div className="space-y-2">
            <span className="px-3 py-1 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-full">
              Application Submitted & Under Verification
            </span>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
              Artisan Onboarding Submitted!
            </h1>
            <p className="text-xs sm:text-sm text-gray-600 max-w-lg mx-auto">
              Your partner registration and KYC documentation have been submitted to the GanvGaliHut artisan verification desk.
            </p>
          </div>

          {/* Reference Card */}
          <div className="bg-gray-50 border border-gray-200 rounded-2xl p-5 text-left max-w-md mx-auto space-y-3 text-xs">
            <div className="flex justify-between items-center pb-2 border-b border-gray-200">
              <span className="text-gray-500 font-medium">Tracking Reference:</span>
              <span className="font-mono font-bold text-brand-green text-sm">{submittedRef}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-500">Artisan / Group Name:</span>
              <span className="font-bold text-gray-800">{formData.groupName || formData.artisanName}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-500">KYC Verification Method:</span>
              <span className="font-bold text-gray-800">
                {formData.hasGst === 'yes' ? `GSTIN: ${formData.gstin}` : `Exempt: ${formData.altDocType}`}
              </span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-500">Bank Settlement:</span>
              <span className="font-mono text-gray-800">****{formData.bankAccount.slice(-4) || '9283'}</span>
            </div>
            <div className="flex justify-between items-center pt-2 border-t border-gray-200">
              <span className="text-gray-500">Estimated Verification:</span>
              <span className="font-bold text-amber-600">24-48 Hours</span>
            </div>
          </div>

          <div className="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 text-xs text-emerald-900 max-w-md mx-auto space-y-1">
            <p className="font-bold">Need Fast-Track Dispatch Approval?</p>
            <p className="text-[11px] text-emerald-800">
              Contact S S Industries Artisan Coordination Cell: <br />
              <span className="font-mono font-bold">+91 7897492100</span> / <span className="font-mono font-bold">+91 9005756321</span>
            </p>
          </div>

          <div className="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
            <button
              onClick={() => {
                setSubmittedRef(null);
                setFormData({
                  artisanName: '',
                  groupName: '',
                  sellerType: 'SHG',
                  phone: '',
                  email: '',
                  village: '',
                  district: '',
                  state: 'Uttar Pradesh',
                  pincode: '',
                  category: 'Farming & Traditional Tools',
                  memberCount: '12',
                  story: '',
                  hasGst: 'no',
                  gstin: '',
                  gstCertificateName: '',
                  gstCertificateFile: '',
                  altDocType: 'Aadhaar Card',
                  altDocNumber: '',
                  altDocFileName: '',
                  altDocFile: '',
                  bankBeneficiaryName: '',
                  bankName: '',
                  bankAccount: '',
                  confirmBankAccount: '',
                  ifsc: '',
                  bankDocFileName: '',
                  bankDocFile: ''
                });
              }}
              className="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-colors"
            >
              Register Another Unit
            </button>
            <Link
              to="/seller-dashboard"
              className="px-6 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
            >
              <span>Go to Seller Dashboard</span>
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Official Status Header */}
      <div className="p-4 bg-white border border-gray-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs shadow-xs">
        <div className="flex items-center gap-3">
          <ShieldCheck className="w-5 h-5 text-brand-green shrink-0" />
          <div>
            <span className="font-bold text-gray-900">Official Rural Artisan & Foundry Registration:</span>
            <span className="text-gray-600 ml-1">
              Direct marketplace access for rural self-help groups (SHG), blacksmith foundries, and artisans.
            </span>
          </div>
        </div>
        <span className="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg shrink-0">
          Fast-Track KYC Active
        </span>
      </div>

      <div className="text-center space-y-2">
        <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
          Rural Seller & Foundry Onboarding Application
        </h1>
        <p className="text-xs sm:text-sm text-gray-600">
          Join India's dedicated direct-to-consumer rural manufacturing marketplace.
        </p>
      </div>

      {formError && (
        <div className="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-xs text-red-800">
          <AlertCircle className="w-5 h-5 text-red-600 shrink-0" />
          <span>{formError}</span>
        </div>
      )}

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
                placeholder="e.g. Rameshwar Vishwakarma / Shanti Devi"
                value={formData.artisanName}
                onChange={(e) => setFormData({ ...formData, artisanName: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                SHG / Foundry / Workshop Name *
              </label>
              <input
                type="text"
                placeholder="e.g. S S Industries Foundry / Saraswati SHG"
                value={formData.groupName}
                onChange={(e) => setFormData({ ...formData, groupName: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Seller Enterprise Type *
              </label>
              <select
                value={formData.sellerType}
                onChange={(e) => setFormData({ ...formData, sellerType: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold"
              >
                <option value="SHG">Women Self-Help Group (SHG)</option>
                <option value="Blacksmith Foundry">Rural Foundry & Blacksmithing Unit</option>
                <option value="Individual Artisan">Individual Master Craftsman / Artisan</option>
                <option value="Farmer Cooperative">Farmer Producer Organization (FPO)</option>
                <option value="Cottage Industry">Rural Cottage Unit</option>
              </select>
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Active Contact Mobile Number *
              </label>
              <input
                type="tel"
                placeholder="10-digit mobile number"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Number of Associated Artisans / Workers
              </label>
              <input
                type="number"
                min="1"
                placeholder="12"
                value={formData.memberCount}
                onChange={(e) => setFormData({ ...formData, memberCount: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">
                Primary Craft / Production Category *
              </label>
              <select
                value={formData.category}
                onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
              >
                <option value="Farming & Traditional Tools">Farming & Traditional Tools (Sickles, Khurpas, Spades)</option>
                <option value="Jute Bags & Cloth Jholas">Jute Bags & Cloth Jholas</option>
                <option value="Handmade Apparel & Bangles">Handmade Apparel & Bangles</option>
                <option value="Village Home Decor & Toys">Village Home Decor & Toys</option>
                <option value="Clay Pottery & Terracotta">Clay Pottery & Terracotta Cookware</option>
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

          <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
            <div className="sm:col-span-2">
              <label className="block font-semibold text-gray-700 mb-1">Village / Gram Panchayat / Industrial Area *</label>
              <input
                type="text"
                placeholder="e.g. Madhoganj Baghauli Road, Newada / Sandila"
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
                placeholder="e.g. Hardoi"
                value={formData.district}
                onChange={(e) => setFormData({ ...formData, district: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">PIN Code *</label>
              <input
                type="text"
                placeholder="e.g. 241126"
                value={formData.pincode}
                onChange={(e) => setFormData({ ...formData, pincode: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono"
                required
              />
            </div>
          </div>

          <div className="text-xs">
            <label className="block font-semibold text-gray-700 mb-1">
              Brief Description of Craft, Workshop, or Collective
            </label>
            <textarea
              rows={2}
              placeholder="e.g. We operate a coal hearth workshop forging agricultural sickles and khurpas with high-carbon tempered iron..."
              value={formData.story}
              onChange={(e) => setFormData({ ...formData, story: e.target.value })}
              className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
            />
          </div>
        </div>

        {/* Section 3: Tax Identification & Alternative KYC Verification */}
        <div className="space-y-5">
          <div className="flex items-center gap-2 pb-2 border-b border-gray-100 font-bold text-base text-gray-900 font-display">
            <ShieldCheck className="w-5 h-5 text-brand-green" />
            <span>3. Mandatory Tax Registration & KYC Verification</span>
          </div>

          {/* Primary Question: GST Availability */}
          <div className="p-4 bg-gray-50 rounded-2xl border border-gray-200 space-y-3 text-xs">
            <label className="block font-bold text-gray-900 text-sm">
              Do you or your enterprise have an active GST Registration (GSTIN)? *
            </label>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label
                className={`p-3.5 rounded-xl border flex items-start gap-3 cursor-pointer transition-all ${
                  formData.hasGst === 'yes'
                    ? 'border-brand-green bg-emerald-50/50 ring-2 ring-brand-green/20'
                    : 'border-gray-200 bg-white hover:border-gray-300'
                }`}
              >
                <input
                  type="radio"
                  name="hasGst"
                  value="yes"
                  checked={formData.hasGst === 'yes'}
                  onChange={() => setFormData({ ...formData, hasGst: 'yes' })}
                  className="mt-0.5 text-brand-green focus:ring-brand-green"
                />
                <div>
                  <span className="font-bold text-gray-900 block">Yes, I have an active GSTIN</span>
                  <span className="text-[11px] text-gray-500">
                    Recommended for registered foundries, companies, and turnover &gt; ₹20-40 Lakhs.
                  </span>
                </div>
              </label>

              <label
                className={`p-3.5 rounded-xl border flex items-start gap-3 cursor-pointer transition-all ${
                  formData.hasGst === 'no'
                    ? 'border-brand-green bg-emerald-50/50 ring-2 ring-brand-green/20'
                    : 'border-gray-200 bg-white hover:border-gray-300'
                }`}
              >
                <input
                  type="radio"
                  name="hasGst"
                  value="no"
                  checked={formData.hasGst === 'no'}
                  onChange={() => setFormData({ ...formData, hasGst: 'no' })}
                  className="mt-0.5 text-brand-green focus:ring-brand-green"
                />
                <div>
                  <span className="font-bold text-gray-900 block">No, I am an Exempt Artisan / SHG</span>
                  <span className="text-[11px] text-gray-500">
                    As per CBIC rules, small rural artisans with turnover &lt; ₹20-40 Lakhs can register using alternative documents.
                  </span>
                </div>
              </label>
            </div>
          </div>

          {/* Conditional Branch A: GST Registered Flow */}
          {formData.hasGst === 'yes' ? (
            <div className="p-4 bg-emerald-50/40 rounded-2xl border border-emerald-200 space-y-4 text-xs animate-in fade-in">
              <span className="font-bold text-emerald-900 block text-sm">
                Enter GSTIN & Upload Certificate
              </span>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    15-Character GSTIN Number *
                  </label>
                  <input
                    type="text"
                    maxLength={15}
                    placeholder="e.g. 09ABCDE1234F1Z5"
                    value={formData.gstin}
                    onChange={(e) => setFormData({ ...formData, gstin: e.target.value.toUpperCase() })}
                    className="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-mono uppercase font-bold"
                    required
                  />
                  <p className="text-[10px] text-gray-500 mt-1">First 2 digits represent State Code (e.g. 09 for UP).</p>
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Upload GST Registration Certificate (PDF or Image)
                  </label>
                  <label className="w-full py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                    <Upload className="w-3.5 h-3.5 text-brand-green" />
                    <span>{formData.gstCertificateName || 'Choose GST Certificate File'}</span>
                    <input
                      type="file"
                      accept=".pdf,image/*"
                      onChange={(e) => handleFileUpload(e, 'gstCertificateFile', 'gstCertificateName')}
                      className="hidden"
                    />
                  </label>
                  {formData.gstCertificateName && (
                    <span className="text-[10px] text-emerald-700 font-semibold block mt-1">
                      ✓ Attached: {formData.gstCertificateName}
                    </span>
                  )}
                </div>
              </div>
            </div>
          ) : (
            /* Conditional Branch B: Alternative KYC Documents Flow */
            <div className="p-4 bg-amber-50/40 rounded-2xl border border-amber-200 space-y-4 text-xs animate-in fade-in">
              <div>
                <span className="font-bold text-amber-900 block text-sm">
                  Alternative Government KYC Document Submission
                </span>
                <p className="text-[11px] text-amber-800 mt-0.5">
                  You are registering under the rural artisan turnover exemption. Please select and provide one of the approved identity documents:
                </p>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Select Identity / Registration Document *
                  </label>
                  <select
                    value={formData.altDocType}
                    onChange={(e) => setFormData({ ...formData, altDocType: e.target.value })}
                    className="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold"
                  >
                    <option value="Aadhaar Card">Aadhaar Card (12-Digit)</option>
                    <option value="PAN Card">Income Tax PAN Card</option>
                    <option value="Udyam Registration">Udyam MSME Registration Certificate</option>
                    <option value="Gram Panchayat Certificate">Gram Panchayat / Pradhan Letter</option>
                    <option value="SHG Bank Passbook">SHG Passbook / DRDA Registration</option>
                  </select>
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    {formData.altDocType} Number / ID *
                  </label>
                  <input
                    type="text"
                    placeholder={`Enter ${formData.altDocType} ID`}
                    value={formData.altDocNumber}
                    onChange={(e) => setFormData({ ...formData, altDocNumber: e.target.value.toUpperCase() })}
                    className="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-mono uppercase font-bold"
                    required
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Upload {formData.altDocType} Copy *
                  </label>
                  <label className="w-full py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                    <Upload className="w-3.5 h-3.5 text-brand-orange" />
                    <span>{formData.altDocFileName || 'Upload Document File'}</span>
                    <input
                      type="file"
                      accept=".pdf,image/*"
                      onChange={(e) => handleFileUpload(e, 'altDocFile', 'altDocFileName')}
                      className="hidden"
                    />
                  </label>
                  {formData.altDocFileName && (
                    <span className="text-[10px] text-brand-green font-semibold block mt-1">
                      ✓ Attached: {formData.altDocFileName}
                    </span>
                  )}
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Section 4: Bank Settlement & Direct Payout Details */}
        <div className="space-y-4">
          <div className="flex items-center gap-2 pb-2 border-b border-gray-100 font-bold text-base text-gray-900 font-display">
            <CreditCard className="w-5 h-5 text-brand-green" />
            <span>4. Direct Bank Settlement & Payout Details</span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Beneficiary / Account Holder Name *</label>
              <input
                type="text"
                placeholder="Must match KYC document name"
                value={formData.bankBeneficiaryName}
                onChange={(e) => setFormData({ ...formData, bankBeneficiaryName: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">Bank Name & Branch *</label>
              <input
                type="text"
                placeholder="e.g. State Bank of India, Madhoganj"
                value={formData.bankName}
                onChange={(e) => setFormData({ ...formData, bankName: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">Bank IFSC Code *</label>
              <input
                type="text"
                maxLength={11}
                placeholder="e.g. SBIN0001234"
                value={formData.ifsc}
                onChange={(e) => setFormData({ ...formData, ifsc: e.target.value.toUpperCase() })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg font-mono text-xs uppercase font-bold"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">Bank Account Number *</label>
              <input
                type="text"
                placeholder="Account number"
                value={formData.bankAccount}
                onChange={(e) => setFormData({ ...formData, bankAccount: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg font-mono text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">Confirm Bank Account Number *</label>
              <input
                type="text"
                placeholder="Re-enter account number"
                value={formData.confirmBankAccount}
                onChange={(e) => setFormData({ ...formData, confirmBankAccount: e.target.value })}
                className="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg font-mono text-xs"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">Passbook / Cheque Copy</label>
              <label className="w-full py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                <Upload className="w-3.5 h-3.5 text-brand-green" />
                <span>{formData.bankDocFileName || 'Upload Passbook / Cheque'}</span>
                <input
                  type="file"
                  accept=".pdf,image/*"
                  onChange={(e) => handleFileUpload(e, 'bankDocFile', 'bankDocFileName')}
                  className="hidden"
                />
              </label>
              {formData.bankDocFileName && (
                <span className="text-[10px] text-brand-green font-semibold block mt-1">
                  ✓ Attached: {formData.bankDocFileName}
                </span>
              )}
            </div>
          </div>
        </div>

        {/* Submit Button */}
        <div className="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-xs text-gray-500">
            By submitting, you certify that all KYC documents and GST declarations submitted are genuine and verifiable.
          </p>

          <button
            type="submit"
            className="w-full sm:w-auto px-8 py-3.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-sm rounded-xl shadow-md transition-all cursor-pointer active:scale-95 flex items-center justify-center gap-2"
          >
            <Check className="w-4 h-4" />
            <span>Submit KYC Application</span>
          </button>
        </div>

      </form>
    </div>
  );
};
