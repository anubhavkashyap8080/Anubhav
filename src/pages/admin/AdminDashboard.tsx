import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { usePlatform } from '../../context/PlatformContext';
import { useSiteConfig } from '../../context/SiteConfigContext';
import { UserRole, SupportTicket, BlogPost } from '../../types';
import { FESTIVAL_PRESETS, initialAuditLogs } from '../../data/mockConfig';
import {
  Users,
  Building2,
  Ticket,
  Sliders,
  FileText,
  Compass,
  Flame,
  Globe,
  Mail,
  MessageSquare,
  ShieldCheck,
  TrendingUp,
  Search,
  Plus,
  Trash2,
  Edit3,
  CheckCircle2,
  XCircle,
  Download,
  Copy,
  Send,
  ExternalLink,
  Phone,
  Sparkles,
  MapPin,
  RefreshCw,
  Lock,
  UserCheck,
  Palette,
  Zap,
  Activity,
  Upload,
  Image as ImageIcon,
  Code,
  Share2,
  BarChart3,
  Server,
  Cpu,
  Smartphone,
  Laptop,
  Check,
  ShoppingBag,
  Layers
} from 'lucide-react';
import { AdminProductsTab } from './tabs/AdminProductsTab';
import { AdminCategoriesTab } from './tabs/AdminCategoriesTab';
import { AdminTeamTab } from './tabs/AdminTeamTab';

export const AdminDashboard: React.FC = () => {
  const { currentUser, currentRole, logout } = useAuth();
  const {
    users,
    addUser,
    updateUserRole,
    toggleUserStatus,
    deleteUser,
    vendors,
    updateVendorKyc,
    updateVendorCommission,
    tickets,
    replyToTicket,
    updateTicketStatus,
    assignTicket,
    posts,
    createPost,
    deletePost,
    seoConfig,
    updateSeoConfig,
    generateSitemapXml,
    generateRobotsTxt,
    emailCampaigns,
    sendEmailCampaign,
    smsMessages,
    sendSmsMessage,
    mapSettings,
    updateMapSettings,
    products,
    categories
  } = usePlatform();

  const {
    headerConfig,
    updateHeaderConfig,
    footerConfig,
    updateFooterConfig,
    updateSocialLinks,
    resetConfigs,
    heroFestivalConfig,
    updateHeroFestivalConfig,
    applyFestivalPreset
  } = useSiteConfig();

  const [searchParams, setSearchParams] = useSearchParams();
  const urlTab = searchParams.get('tab');

  // Active Tab
  const [activeTab, setActiveTab] = useState<
    'overview' | 'products' | 'categories' | 'team' | 'users' | 'vendors' | 'tickets' | 'hero_festival' | 'customizer' | 'blogs' | 'map' | 'firebase' | 'seo' | 'communications' | 'security'
  >((urlTab as any) || 'overview');

  useEffect(() => {
    if (urlTab) {
      setActiveTab(urlTab as any);
    }
  }, [urlTab]);

  // Festival Customizer Form State
  const [festivalForm, setFestivalForm] = useState(heroFestivalConfig);

  useEffect(() => {
    if (heroFestivalConfig) {
      setFestivalForm(heroFestivalConfig);
    }
  }, [heroFestivalConfig]);

  const handleSelectPreset = (presetKey: string) => {
    applyFestivalPreset(presetKey);
    const preset = FESTIVAL_PRESETS[presetKey];
    if (preset) {
      setFestivalForm({
        ...heroFestivalConfig,
        ...preset
      });
    }
    notifySave();
  };

  const handleSaveFestival = (e: React.FormEvent) => {
    e.preventDefault();
    updateHeroFestivalConfig(festivalForm);
    notifySave();
  };

  // Search & Filter States
  const [userSearch, setUserSearch] = useState('');
  const [userRoleFilter, setUserRoleFilter] = useState<string>('all');
  const [vendorSearch, setVendorSearch] = useState('');
  const [ticketFilter, setTicketFilter] = useState<'All' | 'Open' | 'In Progress' | 'Resolved'>('All');
  const [activeTicket, setActiveTicket] = useState<SupportTicket | null>(null);
  const [ticketReplyText, setTicketReplyText] = useState('');

  // Add User Modal State
  const [showAddUserModal, setShowAddUserModal] = useState(false);
  const [newUserName, setNewUserName] = useState('');
  const [newUserEmail, setNewUserEmail] = useState('');
  const [newUserPhone, setNewUserPhone] = useState('');
  const [newUserRole, setNewUserRole] = useState<UserRole>('customer');

  // Email Campaign Form State
  const [emailSubject, setEmailSubject] = useState('');
  const [emailAudience, setEmailAudience] = useState<'All Users' | 'All Vendors' | 'All Customers' | 'Unverified Vendors'>('All Users');
  const [emailBody, setEmailBody] = useState('');
  const [emailSentNotice, setEmailSentNotice] = useState(false);

  // SMS Form State
  const [smsPhone, setSmsPhone] = useState('');
  const [smsTemplateId, setSmsTemplateId] = useState('DLT-SSIND-PROMO-9001');
  const [smsText, setSmsText] = useState('Greetings from S S Industries Hardoi! Our rabi harvesting farming tools & sickles are now available with direct village dispatch.');
  const [smsSentNotice, setSmsSentNotice] = useState(false);

  // XML / Robots Copy status
  const [copiedSitemap, setCopiedSitemap] = useState(false);
  const [copiedRobots, setCopiedRobots] = useState(false);
  const [saveSuccessNotice, setSaveSuccessNotice] = useState(false);

  // Trigger Save Notification
  const notifySave = () => {
    setSaveSuccessNotice(true);
    setTimeout(() => setSaveSuccessNotice(false), 3000);
  };

  const handleLogoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        updateHeaderConfig({ logoUrl: reader.result as string });
      };
      reader.readAsDataURL(file);
    }
  };

  const handleFaviconUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        updateHeaderConfig({ faviconUrl: reader.result as string });
      };
      reader.readAsDataURL(file);
    }
  };

  // Filtered Users
  const filteredUsers = users.filter(u => {
    const matchesSearch = u.name.toLowerCase().includes(userSearch.toLowerCase()) ||
                          u.email.toLowerCase().includes(userSearch.toLowerCase()) ||
                          u.phone.includes(userSearch);
    const matchesRole = userRoleFilter === 'all' || u.role === userRoleFilter;
    return matchesSearch && matchesRole;
  });

  // Filtered Vendors
  const filteredVendors = vendors.filter(v =>
    v.name.toLowerCase().includes(vendorSearch.toLowerCase()) ||
    v.location.toLowerCase().includes(vendorSearch.toLowerCase()) ||
    v.district.toLowerCase().includes(vendorSearch.toLowerCase())
  );

  // Filtered Tickets
  const filteredTickets = tickets.filter(t => {
    if (ticketFilter === 'All') return true;
    return t.status === ticketFilter;
  });

  const handleCreateUser = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newUserName.trim() || !newUserEmail.trim()) return;
    addUser({
      name: newUserName.trim(),
      email: newUserEmail.trim(),
      phone: newUserPhone.trim() || '+91 7897492100',
      role: newUserRole,
      status: 'active'
    });
    setNewUserName('');
    setNewUserEmail('');
    setNewUserPhone('');
    setShowAddUserModal(false);
  };

  const handleSendEmail = (e: React.FormEvent) => {
    e.preventDefault();
    if (!emailSubject.trim() || !emailBody.trim()) return;
    sendEmailCampaign({
      subject: emailSubject,
      senderName: 'S S Industries / GaunGaliHat',
      senderEmail: 'namaste@gaungalihat.com',
      recipientAudience: emailAudience,
      recipientCount: emailAudience === 'All Users' ? users.length * 150 : 450,
      template: 'Promotion / Offer',
      body: emailBody
    });
    setEmailSubject('');
    setEmailBody('');
    setEmailSentNotice(true);
    setTimeout(() => setEmailSentNotice(false), 3500);
  };

  const handleSendSms = (e: React.FormEvent) => {
    e.preventDefault();
    if (!smsText.trim()) return;
    sendSmsMessage({
      recipientPhone: smsPhone || '+91 7897492100',
      audience: 'Individual Number',
      dltTemplateId: smsTemplateId,
      messageText: smsText,
      type: 'Promotional SMS'
    });
    setSmsSentNotice(true);
    setTimeout(() => setSmsSentNotice(false), 3500);
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 w-full max-w-full overflow-x-hidden min-w-0">
      
      {/* Save Success Floating Toast */}
      {saveSuccessNotice && (
        <div className="fixed bottom-6 right-6 z-50 bg-emerald-700 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 animate-in fade-in slide-in-from-bottom duration-200 text-xs font-semibold">
          <CheckCircle2 className="w-5 h-5 text-emerald-200" />
          <span>Settings saved to live site & localStorage successfully!</span>
        </div>
      )}

      {/* Top Banner with Active User & Fast Role Switcher */}
      <div className="bg-gradient-to-r from-emerald-950 via-slate-900 to-emerald-900 rounded-2xl p-4 sm:p-5 text-white shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-3 border border-emerald-800/40">
        <div>
          <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-800/80 text-amber-300 text-[11px] font-semibold mb-1 border border-amber-300/20">
            <Sparkles className="w-3 h-3 text-amber-300" />
            <span>S S Industries • Central Governance Hub</span>
          </div>
          <h1 className="text-xl sm:text-2xl font-bold font-display text-white tracking-tight">
            GaunGaliHat Operations & Marketplace Control
          </h1>
          <p className="text-xs text-emerald-100/90 mt-0.5">
            Operator: <strong>{currentUser?.name || 'Anubhav Kashyap'}</strong> ({currentUser?.email}) • Active RBAC: <span className="uppercase font-bold text-amber-300 bg-amber-400/20 px-1.5 py-0.5 rounded text-[10px] ml-1">{currentRole.replace('_', ' ')}</span>
          </p>
        </div>

        {/* Live Production Edge & Session Status */}
        <div className="bg-black/40 backdrop-blur-md p-3 rounded-xl border border-white/10 text-xs flex items-center gap-3">
          <div className="space-y-1">
            <div className="flex items-center gap-1.5 text-[11px] text-emerald-300 font-semibold">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Cloudflare Edge Active</span>
            </div>
            <div className="text-[10px] text-gray-300">
              Clearance: <span className="font-mono text-amber-300 font-bold">{currentUser?.role?.toUpperCase() || 'SUPER_ADMIN'}</span>
            </div>
          </div>
          <button
            onClick={() => {
              logout();
              window.location.href = '/';
            }}
            className="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-200 border border-red-500/40 text-xs font-semibold transition-all cursor-pointer"
          >
            Sign Out
          </button>
        </div>
      </div>

      {/* Tab Navigation Menu */}
      <div className="flex border-b border-gray-200 overflow-x-auto pb-1 scrollbar-none gap-2 text-xs font-semibold text-gray-600">
        {[
          { id: 'overview', label: 'Overview', icon: TrendingUp },
          { id: 'products', label: `Products Catalog (${products.length})`, icon: ShoppingBag, permRoles: ['super_admin', 'admin', 'marketer', 'vendor'] },
          { id: 'categories', label: `Categories (${categories.length})`, icon: Layers, permRoles: ['super_admin', 'admin', 'marketer'] },
          { id: 'team', label: `Team & Work Log (${users.filter(u => u.role !== 'customer').length})`, icon: UserCheck, permRoles: ['super_admin', 'admin'] },
          { id: 'users', label: `Users (${users.length})`, icon: Users, permRoles: ['super_admin', 'admin', 'support'] },
          { id: 'vendors', label: `Vendors (${vendors.length})`, icon: Building2, permRoles: ['super_admin', 'admin', 'vendor'] },
          { id: 'tickets', label: `Support Tickets (${tickets.length})`, icon: Ticket, permRoles: ['super_admin', 'admin', 'support'] },
          { id: 'hero_festival', label: 'Hero & Festival Customizer', icon: Sparkles, badge: 'New', permRoles: ['super_admin', 'admin', 'marketer', 'content_writer'] },
          { id: 'customizer', label: 'Header & Footer Customizer', icon: Sliders, permRoles: ['super_admin', 'admin', 'marketer'] },
          { id: 'blogs', label: `Blogs Manager (${posts.length})`, icon: FileText, permRoles: ['super_admin', 'admin', 'content_writer', 'marketer'] },
          { id: 'communications', label: 'Bulk Email & SMS', icon: Mail, permRoles: ['super_admin', 'admin', 'marketer'] },
          { id: 'map', label: 'Nearby Map Settings', icon: Compass, permRoles: ['super_admin', 'admin'] },
          { id: 'firebase', label: 'Firebase & OTP Auth', icon: Flame, permRoles: ['super_admin'] },
          { id: 'seo', label: 'SEO & Search Console', icon: Globe, permRoles: ['super_admin', 'admin', 'marketer', 'content_writer'] },
          { id: 'security', label: 'Security & Audit Logs', icon: ShieldCheck, badge: 'Shield', permRoles: ['super_admin', 'admin'] }
        ].map(tab => {
          const Icon = tab.icon;
          const isActive = activeTab === tab.id;
          const isPermitted = !tab.permRoles || tab.permRoles.includes(currentRole);
          return (
            <button
              key={tab.id}
              onClick={() => {
                setActiveTab(tab.id as any);
                setSearchParams({ tab: tab.id });
              }}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl transition-all whitespace-nowrap cursor-pointer shrink-0 ${
                isActive
                  ? 'bg-brand-green text-white shadow-xs font-bold'
                  : isPermitted
                  ? 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200/80'
                  : 'bg-gray-100 text-gray-400 border border-gray-200/50 opacity-60'
              }`}
            >
              <Icon className="w-3.5 h-3.5" />
              <span>{tab.label}</span>
              {tab.badge && (
                <span className="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-200 text-amber-900">
                  {tab.badge}
                </span>
              )}
            </button>
          );
        })}
      </div>

      {/* TAB 1: OVERVIEW & ADVANCED REVENUE TELEMETRY */}
      {activeTab === 'overview' && (
        <div className="space-y-5">
          {/* Top High-Concurrency KPIs - Compact High-Density Grid */}
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div className="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-100 shadow-2xs">
              <div className="flex items-center justify-between gap-1">
                <span className="text-[11px] text-gray-500 font-semibold truncate">GMV (Turnover)</span>
                <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 whitespace-nowrap">+24.6% YoY</span>
              </div>
              <div className="text-xl font-bold text-gray-900 font-display mt-1">₹1.42 Cr</div>
              <p className="text-[11px] text-gray-400 font-medium mt-0.5 truncate">Audited 2026 run-rate</p>
            </div>

            <div className="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-100 shadow-2xs">
              <div className="flex items-center justify-between gap-1">
                <span className="text-[11px] text-gray-500 font-semibold truncate">Orders Fulfilled</span>
                <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 whitespace-nowrap">98.4% Rate</span>
              </div>
              <div className="text-xl font-bold text-blue-600 font-display mt-1">28,450 Orders</div>
              <p className="text-[11px] text-gray-400 font-medium mt-0.5 truncate">India Post & Delhivery</p>
            </div>

            <div className="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-100 shadow-2xs">
              <div className="flex items-center justify-between gap-1">
                <span className="text-[11px] text-gray-500 font-semibold truncate">Live Shoppers</span>
                <span className="flex items-center gap-1 text-[10px] font-bold text-emerald-600 whitespace-nowrap">
                  <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                  Real-time
                </span>
              </div>
              <div className="text-xl font-bold text-brand-orange font-display mt-1">48,290 Active</div>
              <p className="text-[11px] text-gray-400 font-medium mt-0.5 truncate">Sub-20ms edge latency</p>
            </div>

            <div className="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-100 shadow-2xs">
              <div className="flex items-center justify-between gap-1">
                <span className="text-[11px] text-gray-500 font-semibold truncate">Catalog SKUs</span>
                <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 whitespace-nowrap">{categories.length} Cats</span>
              </div>
              <div className="text-xl font-bold text-purple-700 font-display mt-1">{products.length} Products</div>
              <p className="text-[11px] text-gray-400 font-medium mt-0.5 truncate">Village staples & tools</p>
            </div>

            <div className="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-100 shadow-2xs">
              <div className="flex items-center justify-between gap-1">
                <span className="text-[11px] text-gray-500 font-semibold truncate">Foundries & SHGs</span>
                <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 whitespace-nowrap">100% KYC</span>
              </div>
              <div className="text-xl font-bold text-brand-green font-display mt-1">{vendors.length} Supply Hubs</div>
              <p className="text-[11px] text-gray-400 font-medium mt-0.5 truncate">Hardoi & regional units</p>
            </div>
          </div>

          {/* 12-Month Revenue & Volume Trend Chart */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100">
              <div>
                <div className="flex items-center gap-2">
                  <BarChart3 className="w-5 h-5 text-brand-green" />
                  <h3 className="font-bold text-base text-gray-900 font-display">12-Month Gross Revenue & Seasonal Velocity</h3>
                </div>
                <p className="text-xs text-gray-500 mt-0.5">Tracking seasonal harvesting tools surges (April/Wheat & October/Paddy) and festival handicrafts.</p>
              </div>
              <div className="flex items-center gap-4 text-xs font-semibold">
                <div className="flex items-center gap-1.5 text-gray-600">
                  <span className="w-3 h-3 rounded bg-brand-green"></span>
                  <span>B2B Foundry Bulk (62%)</span>
                </div>
                <div className="flex items-center gap-1.5 text-gray-600">
                  <span className="w-3 h-3 rounded bg-brand-orange"></span>
                  <span>D2C Rural Shopper (38%)</span>
                </div>
              </div>
            </div>

            {/* Visual Bar Chart */}
            <div className="pt-4">
              <div className="h-56 flex items-end justify-between gap-1 sm:gap-3 px-2 border-b border-gray-200">
                {[
                  { month: 'Nov 25', val: 8.4, pct: 52, orders: 1720 },
                  { month: 'Dec 25', val: 11.2, pct: 70, orders: 2240 },
                  { month: 'Jan 26', val: 9.8, pct: 61, orders: 1980 },
                  { month: 'Feb 26', val: 10.5, pct: 65, orders: 2110 },
                  { month: 'Mar 26', val: 13.6, pct: 85, orders: 2680 },
                  { month: 'Apr 26', val: 15.2, pct: 95, peak: 'Harvest Peak', orders: 3040 },
                  { month: 'May 26', val: 14.1, pct: 88, orders: 2820 },
                  { month: 'Jun 26', val: 11.8, pct: 73, orders: 2360 },
                  { month: 'Jul 26', val: 12.5, pct: 78, orders: 2490 },
                  { month: 'Aug 26', val: 13.9, pct: 87, orders: 2780 },
                  { month: 'Sep 26', val: 15.8, pct: 98, orders: 3150 },
                  { month: 'Oct 26', val: 16.0, pct: 100, peak: 'Diwali Peak', orders: 3200 }
                ].map((item, idx) => (
                  <div key={idx} className="flex-1 flex flex-col items-center gap-2 group relative">
                    {/* Tooltip on hover */}
                    <div className="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-12 z-20 pointer-events-none bg-gray-900 text-white text-[10px] py-1 px-2 rounded-lg whitespace-nowrap shadow-lg">
                      <p className="font-bold">₹{item.val} Lakhs</p>
                      <p className="text-gray-300">{item.orders} orders</p>
                    </div>

                    {item.peak && (
                      <span className="text-[9px] font-bold text-brand-orange bg-orange-100 px-1 py-0.5 rounded whitespace-nowrap hidden sm:block">
                        {item.peak}
                      </span>
                    )}

                    <div className="w-full max-w-[42px] bg-gray-100 rounded-t-lg overflow-hidden flex flex-col justify-end h-40">
                      <div
                        style={{ height: `${item.pct}%` }}
                        className={`w-full rounded-t-lg transition-all duration-500 group-hover:brightness-110 ${
                          idx === 11 || idx === 5 ? 'bg-gradient-to-t from-brand-green to-emerald-400' : 'bg-brand-green/80'
                        }`}
                      ></div>
                    </div>
                    <span className="text-[10px] text-gray-500 font-semibold text-center whitespace-nowrap">
                      {item.month}
                    </span>
                  </div>
                ))}
              </div>
              <div className="flex items-center justify-between text-[11px] text-gray-500 pt-2 px-2">
                <span>Annual Base: ₹1.42 Cr Cumulative Turnover</span>
                <span className="font-semibold text-emerald-700">Average Monthly Run-Rate: ₹11.9 Lakhs/mo</span>
              </div>
            </div>
          </div>

          {/* Deep-Dive Grid: Category Volume & Regional Distribution (High Density) */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
            
            {/* Category Performance Breakdown */}
            <div className="bg-white p-4 sm:p-5 rounded-xl border border-gray-100 shadow-2xs space-y-3">
              <div className="flex items-center justify-between pb-2 border-b border-gray-100">
                <div className="flex items-center gap-2">
                  <Layers className="w-4 h-4 text-brand-orange" />
                  <h3 className="font-bold text-sm text-gray-900 font-display">Category Market Volume</h3>
                </div>
                <span className="text-[11px] text-gray-400 font-medium">Turnover Share</span>
              </div>

              <div className="space-y-2.5 text-xs">
                <div>
                  <div className="flex justify-between items-center mb-1">
                    <span className="font-bold text-gray-800">Farming & Forged Implements (Sickles, Khurpas, Spades)</span>
                    <span className="font-bold text-brand-green">44% (₹62.8L)</span>
                  </div>
                  <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div className="bg-brand-green h-full rounded-full" style={{ width: '44%' }}></div>
                  </div>
                </div>

                <div>
                  <div className="flex justify-between items-center mb-1">
                    <span className="font-bold text-gray-800">Jute Bags, Cloth Jholas & Natural Fibers</span>
                    <span className="font-bold text-brand-orange">26% (₹37.1L)</span>
                  </div>
                  <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div className="bg-brand-orange h-full rounded-full" style={{ width: '26%' }}></div>
                  </div>
                </div>

                <div>
                  <div className="flex justify-between items-center mb-1">
                    <span className="font-bold text-gray-800">Clay Pottery & Terracotta Cookware</span>
                    <span className="font-bold text-amber-600">16% (₹22.8L)</span>
                  </div>
                  <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div className="bg-amber-500 h-full rounded-full" style={{ width: '16%' }}></div>
                  </div>
                </div>

                <div>
                  <div className="flex justify-between items-center mb-1">
                    <span className="font-bold text-gray-800">Handloom Khadi Apparel & Dhurries</span>
                    <span className="font-bold text-blue-600">14% (₹20.0L)</span>
                  </div>
                  <div className="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div className="bg-blue-600 h-full rounded-full" style={{ width: '14%' }}></div>
                  </div>
                </div>
              </div>

              <div className="p-2.5 bg-emerald-50/80 rounded-lg border border-emerald-100 text-[11px] text-emerald-900 flex items-center justify-between">
                <span>Top Selling Tool SKU:</span>
                <span className="font-bold font-mono">GGH-TL-101 (Hardened Serrated Sickle)</span>
              </div>
            </div>

            {/* Regional Traffic & Device Share */}
            <div className="bg-white p-4 sm:p-5 rounded-xl border border-gray-100 shadow-2xs space-y-3">
              <div className="flex items-center justify-between pb-2 border-b border-gray-100">
                <div className="flex items-center gap-2">
                  <Globe className="w-4 h-4 text-blue-600" />
                  <h3 className="font-bold text-sm text-gray-900 font-display">Regional Traffic & Device Telemetry</h3>
                </div>
                <span className="text-[11px] text-gray-400 font-medium">330+ Edge PoPs</span>
              </div>

              <div className="grid grid-cols-2 gap-2.5 text-xs">
                <div className="p-2.5 bg-gray-50 rounded-lg border border-gray-200/70 space-y-1.5">
                  <span className="font-bold text-gray-900 text-[11px] block">Top States (Demand Geo):</span>
                  <div className="space-y-1 text-[11px] text-gray-700">
                    <div className="flex justify-between"><span>Uttar Pradesh</span><span className="font-bold">38%</span></div>
                    <div className="flex justify-between"><span>Bihar & Jharkhand</span><span className="font-bold">19%</span></div>
                    <div className="flex justify-between"><span>Maharashtra & Guj</span><span className="font-bold">14%</span></div>
                    <div className="flex justify-between"><span>Madhya Pradesh</span><span className="font-bold">11%</span></div>
                    <div className="flex justify-between"><span>Rajasthan & Punjab</span><span className="font-bold">9%</span></div>
                  </div>
                </div>

                <div className="p-2.5 bg-gray-50 rounded-lg border border-gray-200/70 space-y-2">
                  <span className="font-bold text-gray-900 text-[11px] block">Device Telemetry:</span>
                  <div className="space-y-1.5">
                    <div>
                      <div className="flex items-center justify-between mb-0.5 text-[11px]">
                        <span className="flex items-center gap-1"><Smartphone className="w-3 h-3 text-gray-500" /> Mobile (PWA)</span>
                        <span className="font-bold text-brand-green">82%</span>
                      </div>
                      <div className="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                        <div className="bg-brand-green h-full rounded-full" style={{ width: '82%' }}></div>
                      </div>
                    </div>

                    <div>
                      <div className="flex items-center justify-between mb-0.5 text-[11px]">
                        <span className="flex items-center gap-1"><Laptop className="w-3 h-3 text-gray-500" /> Desktop Workstation</span>
                        <span className="font-bold text-blue-600">15%</span>
                      </div>
                      <div className="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                        <div className="bg-blue-600 h-full rounded-full" style={{ width: '15%' }}></div>
                      </div>
                    </div>

                    <div>
                      <div className="flex items-center justify-between mb-0.5 text-[11px]">
                        <span className="flex items-center gap-1"><Layers className="w-3 h-3 text-gray-500" /> Tablet & Kiosk</span>
                        <span className="font-bold text-amber-600">3%</span>
                      </div>
                      <div className="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                        <div className="bg-amber-500 h-full rounded-full" style={{ width: '3%' }}></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div className="p-2.5 bg-blue-50/80 rounded-lg border border-blue-100 text-[11px] text-blue-900 flex items-center justify-between">
                <span>Traffic Breakdown:</span>
                <span className="font-bold">Organic Search (46%) • WhatsApp (32%) • Direct (22%)</span>
              </div>
            </div>

          </div>

          {/* High-Concurrency Edge Architecture Resilience Monitor (Crore User Capacity) */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100">
              <div className="flex items-center gap-2">
                <Server className="w-5 h-5 text-emerald-600" />
                <h3 className="font-bold text-base text-gray-900 font-display">
                  Enterprise High-Load Resilience Monitor (1+ Crore User Capacity)
                </h3>
              </div>
              <span className="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-lg flex items-center gap-1">
                <Check className="w-3.5 h-3.5" /> All Edge Nodes Operational
              </span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
              <div className="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                <div className="flex items-center justify-between mb-1">
                  <span className="font-bold text-gray-800">Cloudflare Workers Engine</span>
                  <span className="text-emerald-600 font-bold">100% Up</span>
                </div>
                <p className="text-[11px] text-gray-500">Global Anycast compute, auto-scales instantly from 0 to 10M+ req/min with zero origin bottleneck.</p>
              </div>

              <div className="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                <div className="flex items-center justify-between mb-1">
                  <span className="font-bold text-gray-800">Workers KV & Cache API</span>
                  <span className="text-emerald-600 font-bold">99.82% Hit</span>
                </div>
                <p className="text-[11px] text-gray-500">Complete catalog & inventory cached across 330+ edge data centers. Sub-20ms global delivery.</p>
              </div>

              <div className="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                <div className="flex items-center justify-between mb-1">
                  <span className="font-bold text-gray-800">D1 Database & Read Replicas</span>
                  <span className="text-emerald-600 font-bold">12ms Latency</span>
                </div>
                <p className="text-[11px] text-gray-500">Distributed transactional state. Read replicas absorb high-concurrency festival rushes without lag.</p>
              </div>

              <div className="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                <div className="flex items-center justify-between mb-1">
                  <span className="font-bold text-gray-800">WAF & DDoS Mitigation</span>
                  <span className="text-emerald-600 font-bold">0 Breaches</span>
                </div>
                <p className="text-[11px] text-gray-500">Automated L3/L4/L7 volumetric mitigation & Cloudflare Turnstile bot shielding enabled.</p>
              </div>
            </div>
          </div>

          {/* Operational Facilities */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <h3 className="font-bold text-base text-gray-900 font-display">Operational Production & Dispatch Facilities</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <span className="font-bold text-gray-900 block mb-1">🏭 Works / Foundry Unit:</span>
                <p className="text-gray-600">Madhoganj Baghauli Road, Newada, Hardoi 241126</p>
                <p className="text-brand-green font-semibold mt-2">📞 +91 7897492100</p>
              </div>
              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <span className="font-bold text-gray-900 block mb-1">🏢 Head Office & Central Hub:</span>
                <p className="text-gray-600">G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi</p>
                <p className="text-brand-green font-semibold mt-2">📞 +91 9005756321 / +91 8303578806</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* TAB: PRODUCTS CATALOG & VILLAGE STAPLES (UNIQUE IDs & VENDORS) */}
      {activeTab === 'products' && <AdminProductsTab />}

      {/* TAB: CATEGORIES MANAGER (ADD, EDIT, DELETE, STATUS) */}
      {activeTab === 'categories' && <AdminCategoriesTab />}

      {/* TAB: TEAM PROFILES & WORK AUDIT TRAIL */}
      {activeTab === 'team' && <AdminTeamTab />}

      {/* TAB 2: USERS MANAGER */}
      {activeTab === 'users' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
          <div className="flex flex-col sm:flex-row items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <div className="flex items-center gap-3 w-full sm:w-auto">
              <div className="relative w-full sm:w-64">
                <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                  type="text"
                  placeholder="Search user by name, email, phone..."
                  value={userSearch}
                  onChange={(e) => setUserSearch(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-xl"
                />
              </div>
              <select
                value={userRoleFilter}
                onChange={(e) => setUserRoleFilter(e.target.value)}
                className="px-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50"
              >
                <option value="all">All Roles</option>
                <option value="super_admin">Super Admin</option>
                <option value="admin">Admin</option>
                <option value="marketer">Marketer</option>
                <option value="content_writer">Content Writer</option>
                <option value="vendor">Vendor</option>
                <option value="support">Support</option>
                <option value="customer">Customer</option>
              </select>
            </div>

            <button
              onClick={() => setShowAddUserModal(true)}
              className="px-4 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition-all w-full sm:w-auto justify-center cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>Add New User</span>
            </button>
          </div>

          <div className="overflow-x-auto text-xs">
            <table className="w-full text-left">
              <thead className="bg-gray-50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th className="p-3">User</th>
                  <th className="p-3">Phone</th>
                  <th className="p-3">Role</th>
                  <th className="p-3">Status</th>
                  <th className="p-3">Joined Date</th>
                  <th className="p-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {filteredUsers.map(user => (
                  <tr key={user.id} className="hover:bg-gray-50/60">
                    <td className="p-3">
                      <div className="flex items-center gap-2.5">
                        <img
                          src={user.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80'}
                          alt={user.name}
                          className="w-7 h-7 rounded-full object-cover"
                        />
                        <div>
                          <p className="font-bold text-gray-900">{user.name}</p>
                          <p className="text-[11px] text-gray-500">{user.email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="p-3 font-mono text-[11px] text-gray-700">{user.phone}</td>
                    <td className="p-3">
                      <select
                        value={user.role}
                        onChange={(e) => updateUserRole(user.id, e.target.value as UserRole)}
                        className="px-2 py-1 bg-white border border-gray-200 rounded-lg text-xs font-semibold text-brand-green"
                      >
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="marketer">Marketer</option>
                        <option value="content_writer">Content Writer</option>
                        <option value="vendor">Vendor</option>
                        <option value="support">Support</option>
                        <option value="customer">Customer</option>
                      </select>
                    </td>
                    <td className="p-3">
                      <button
                        onClick={() => toggleUserStatus(user.id)}
                        className={`px-2 py-0.5 rounded-full text-[10px] font-bold ${
                          user.status === 'active'
                            ? 'bg-emerald-100 text-emerald-800'
                            : 'bg-red-100 text-red-800'
                        }`}
                      >
                        {user.status === 'active' ? 'Active' : 'Suspended'}
                      </button>
                    </td>
                    <td className="p-3 text-gray-500">{user.joinedDate}</td>
                    <td className="p-3 text-right">
                      <button
                        onClick={() => deleteUser(user.id)}
                        className="p-1.5 text-gray-400 hover:text-red-600 rounded-lg transition-colors cursor-pointer"
                        title="Delete User"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Add User Modal */}
          {showAddUserModal && (
            <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
              <div className="bg-white rounded-3xl p-6 max-w-sm w-full space-y-4 shadow-2xl animate-in zoom-in-95 duration-150">
                <h4 className="font-bold text-base text-gray-900 font-display">Create User Account</h4>
                <form onSubmit={handleCreateUser} className="space-y-3 text-xs">
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Full Name</label>
                    <input
                      type="text"
                      required
                      placeholder="e.g. Ramesh Kumar"
                      value={newUserName}
                      onChange={(e) => setNewUserName(e.target.value)}
                      className="w-full p-2 border border-gray-300 rounded-lg"
                    />
                  </div>
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Email</label>
                    <input
                      type="email"
                      required
                      placeholder="ramesh@example.com"
                      value={newUserEmail}
                      onChange={(e) => setNewUserEmail(e.target.value)}
                      className="w-full p-2 border border-gray-300 rounded-lg"
                    />
                  </div>
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Phone Number</label>
                    <input
                      type="tel"
                      placeholder="+91 98765 43210"
                      value={newUserPhone}
                      onChange={(e) => setNewUserPhone(e.target.value)}
                      className="w-full p-2 border border-gray-300 rounded-lg"
                    />
                  </div>
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Assigned Role</label>
                    <select
                      value={newUserRole}
                      onChange={(e) => setNewUserRole(e.target.value as UserRole)}
                      className="w-full p-2 border border-gray-300 rounded-lg"
                    >
                      <option value="customer">Customer / Buyer</option>
                      <option value="vendor">Vendor / Seller</option>
                      <option value="support">Support Agent</option>
                      <option value="marketer">Marketing Manager</option>
                      <option value="content_writer">Content Writer</option>
                      <option value="admin">Operations Admin</option>
                      <option value="super_admin">Super Admin</option>
                    </select>
                  </div>
                  <div className="flex gap-2 pt-2">
                    <button
                      type="submit"
                      className="flex-1 py-2.5 bg-brand-green text-white font-bold rounded-lg shadow-sm"
                    >
                      Save User
                    </button>
                    <button
                      type="button"
                      onClick={() => setShowAddUserModal(false)}
                      className="px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700"
                    >
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}
        </div>
      )}

      {/* TAB 3: VENDORS MANAGER */}
      {activeTab === 'vendors' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
          <div className="flex flex-col sm:flex-row items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <div className="relative w-full sm:w-72">
              <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                placeholder="Search vendor by name, district, craft..."
                value={vendorSearch}
                onChange={(e) => setVendorSearch(e.target.value)}
                className="w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-xl"
              />
            </div>
            <span className="text-xs text-gray-500 font-semibold">
              Showing {filteredVendors.length} Verified Foundries & Collectives
            </span>
          </div>

          <div className="overflow-x-auto text-xs">
            <table className="w-full text-left">
              <thead className="bg-gray-50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th className="p-3">Vendor / Business</th>
                  <th className="p-3">Location</th>
                  <th className="p-3">Phone</th>
                  <th className="p-3">KYC Status</th>
                  <th className="p-3">Commission %</th>
                  <th className="p-3">Total Revenue</th>
                  <th className="p-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {filteredVendors.map(vendor => (
                  <tr key={vendor.id} className="hover:bg-gray-50/60">
                    <td className="p-3">
                      <div className="flex items-center gap-2.5">
                        <img
                          src={vendor.avatar}
                          alt={vendor.name}
                          className="w-8 h-8 rounded-full object-cover"
                        />
                        <div>
                          <p className="font-bold text-gray-900">{vendor.name}</p>
                          <span className="text-[10px] text-brand-green font-semibold">{vendor.type}</span>
                        </div>
                      </div>
                    </td>
                    <td className="p-3 text-gray-600">{vendor.location}</td>
                    <td className="p-3 font-mono text-[11px] text-gray-800">{vendor.phone}</td>
                    <td className="p-3">
                      <select
                        value={vendor.kycStatus}
                        onChange={(e) => updateVendorKyc(vendor.id, e.target.value as any)}
                        className={`px-2 py-0.5 rounded-md text-[11px] font-bold border ${
                          vendor.kycStatus === 'Verified'
                            ? 'bg-emerald-50 text-emerald-800 border-emerald-300'
                            : vendor.kycStatus === 'Pending Review'
                            ? 'bg-amber-50 text-amber-800 border-amber-300'
                            : 'bg-red-50 text-red-800 border-red-300'
                        }`}
                      >
                        <option value="Verified">Verified</option>
                        <option value="Pending Review">Pending Review</option>
                        <option value="Rejected">Rejected</option>
                      </select>
                    </td>
                    <td className="p-3">
                      <div className="flex items-center gap-1">
                        <input
                          type="number"
                          min={1}
                          max={30}
                          value={vendor.commissionRate}
                          onChange={(e) => updateVendorCommission(vendor.id, Number(e.target.value))}
                          className="w-14 px-1.5 py-0.5 border border-gray-300 rounded text-center text-xs"
                        />
                        <span>%</span>
                      </div>
                    </td>
                    <td className="p-3 font-bold text-gray-900">₹{vendor.totalRevenue.toLocaleString()}</td>
                    <td className="p-3 text-right">
                      <a
                        href={`/seller/${vendor.slug}`}
                        target="_blank"
                        rel="noreferrer"
                        className="p-1.5 text-brand-green hover:underline inline-flex items-center gap-1 font-semibold"
                      >
                        <span>Store</span>
                        <ExternalLink className="w-3 h-3" />
                      </a>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* TAB 4: SUPPORT TICKET MANAGER */}
      {activeTab === 'tickets' && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
          
          {/* Tickets List */}
          <div className="lg:col-span-5 bg-white rounded-2xl border border-gray-100 shadow-xs p-4 space-y-3">
            <div className="flex items-center justify-between pb-2 border-b border-gray-100">
              <h3 className="font-bold text-sm text-gray-900 font-display">Customer Support Inbox</h3>
              <div className="flex gap-1 text-[11px]">
                {(['All', 'Open', 'In Progress', 'Resolved'] as const).map(status => (
                  <button
                    key={status}
                    onClick={() => setTicketFilter(status)}
                    className={`px-2 py-0.5 rounded-lg font-semibold transition-all ${
                      ticketFilter === status
                        ? 'bg-brand-green text-white'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    }`}
                  >
                    {status}
                  </button>
                ))}
              </div>
            </div>

            <div className="space-y-2 max-h-[500px] overflow-y-auto pr-1">
              {filteredTickets.map(ticket => (
                <div
                  key={ticket.id}
                  onClick={() => setActiveTicket(ticket)}
                  className={`p-3 rounded-xl border text-xs cursor-pointer transition-all ${
                    activeTicket?.id === ticket.id
                      ? 'border-brand-green bg-emerald-50/70 shadow-xs ring-1 ring-brand-green'
                      : 'border-gray-200 hover:border-gray-300 bg-white'
                  }`}
                >
                  <div className="flex items-center justify-between mb-1">
                    <span className="font-mono text-[10px] text-gray-400 font-semibold">{ticket.ticketNumber}</span>
                    <span className={`px-2 py-0.5 rounded-full text-[9px] font-bold ${
                      ticket.status === 'Open' ? 'bg-red-100 text-red-700' :
                      ticket.status === 'In Progress' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'
                    }`}>
                      {ticket.status}
                    </span>
                  </div>
                  <h4 className="font-bold text-gray-900 line-clamp-1">{ticket.subject}</h4>
                  <div className="flex items-center justify-between text-[11px] text-gray-500 mt-2">
                    <span>{ticket.userName}</span>
                    <span>{ticket.createdAt}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Ticket Detail & Thread */}
          <div className="lg:col-span-7 bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
            {activeTicket ? (
              <div className="space-y-4">
                <div className="flex items-start justify-between pb-3 border-b border-gray-100">
                  <div>
                    <span className="font-mono text-xs text-brand-orange font-bold">{activeTicket.ticketNumber}</span>
                    <h3 className="font-bold text-base text-gray-900 font-display mt-0.5">{activeTicket.subject}</h3>
                    <p className="text-xs text-gray-500 mt-0.5">
                      From: <strong>{activeTicket.userName}</strong> ({activeTicket.userEmail} • {activeTicket.userPhone})
                    </p>
                  </div>
                  <div className="flex gap-2">
                    <select
                      value={activeTicket.status}
                      onChange={(e) => updateTicketStatus(activeTicket.id, e.target.value as any)}
                      className="px-2.5 py-1 text-xs border border-gray-300 rounded-lg font-bold"
                    >
                      <option value="Open">Open</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Resolved">Resolved</option>
                      <option value="Closed">Closed</option>
                    </select>
                  </div>
                </div>

                {/* Messages Thread */}
                <div className="space-y-3 max-h-[300px] overflow-y-auto p-2 bg-gray-50 rounded-xl">
                  {activeTicket.messages.map(msg => (
                    <div
                      key={msg.id}
                      className={`p-3 rounded-xl text-xs max-w-[85%] ${
                        msg.senderRole === 'customer'
                          ? 'bg-white border border-gray-200 mr-auto text-gray-800'
                          : 'bg-emerald-900 text-white ml-auto shadow-sm'
                      }`}
                    >
                      <div className="flex items-center justify-between gap-4 mb-1 text-[10px] opacity-80 font-bold">
                        <span>{msg.senderName} ({msg.senderRole})</span>
                        <span>{msg.timestamp}</span>
                      </div>
                      <p className="leading-relaxed">{msg.message}</p>
                    </div>
                  ))}
                </div>

                {/* Reply Form */}
                <form
                  onSubmit={(e) => {
                    e.preventDefault();
                    if (!ticketReplyText.trim()) return;
                    replyToTicket(activeTicket.id, ticketReplyText.trim(), currentUser?.name || 'Support Agent', currentRole);
                    setTicketReplyText('');
                  }}
                  className="space-y-2 pt-2"
                >
                  <textarea
                    rows={3}
                    placeholder="Type official response to customer / vendor..."
                    value={ticketReplyText}
                    onChange={(e) => setTicketReplyText(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-brand-green"
                  />
                  <div className="flex justify-end">
                    <button
                      type="submit"
                      className="px-4 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm"
                    >
                      <Send className="w-3.5 h-3.5" />
                      <span>Send Response</span>
                    </button>
                  </div>
                </form>
              </div>
            ) : (
              <div className="p-12 text-center text-xs text-gray-500">
                Select a ticket from the left column to view message history, assign agents, and respond.
              </div>
            )}
          </div>

        </div>
      )}

      {/* TAB 5: HEADER, FOOTER & SOCIAL ICONS CUSTOMIZER */}
      {activeTab === 'customizer' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-6">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <div>
              <div className="flex items-center gap-2">
                <Palette className="w-5 h-5 text-brand-green" />
                <h3 className="font-bold text-base text-gray-900 font-display">Live Site Customization Suite</h3>
                <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                  Cloudflare Edge Synchronized
                </span>
              </div>
              <p className="text-xs text-gray-500 mt-1">
                Customize brand logos, favicons, announcement banners, social links, and insert third-party tracking scripts.
              </p>
            </div>
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={resetConfigs}
                className="px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-semibold hover:bg-gray-50 text-gray-700"
              >
                Reset Defaults
              </button>
              <button
                type="button"
                onClick={notifySave}
                className="px-5 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5"
              >
                <Check className="w-3.5 h-3.5" />
                <span>Save Changes to Live Site</span>
              </button>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs">
            
            {/* Left Column: Header, Logo, Favicon & Navigation */}
            <div className="space-y-6">
              <div className="space-y-4">
                <h4 className="font-bold text-sm text-brand-green font-display border-b border-emerald-100 pb-1.5 flex items-center gap-2">
                  <ImageIcon className="w-4 h-4" />
                  <span>Logo & Favicon Customizer</span>
                </h4>

                {/* Logo Upload & Preview */}
                <div className="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                  <label className="block font-bold text-gray-800">Official Brand Logo</label>
                  <div className="flex items-center gap-4">
                    <div className="w-20 h-20 rounded-xl border border-gray-300 bg-white p-2 flex items-center justify-center overflow-hidden shrink-0">
                      {headerConfig.logoUrl ? (
                        <img
                          src={headerConfig.logoUrl}
                          alt="Logo Preview"
                          className="max-w-full max-h-full object-contain"
                        />
                      ) : (
                        <span className="text-[10px] text-gray-400 text-center">No logo</span>
                      )}
                    </div>
                    <div className="flex-1 space-y-2">
                      <div className="flex items-center gap-2">
                        <label className="px-3 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white rounded-lg text-xs font-bold cursor-pointer flex items-center gap-1.5 shadow-xs">
                          <Upload className="w-3.5 h-3.5" />
                          <span>Upload from Device</span>
                          <input
                            type="file"
                            accept="image/*"
                            onChange={handleLogoUpload}
                            className="hidden"
                          />
                        </label>
                        {headerConfig.logoUrl && (
                          <button
                            type="button"
                            onClick={() => updateHeaderConfig({ logoUrl: '' })}
                            className="px-2 py-1.5 text-xs text-red-600 hover:bg-red-50 rounded-lg"
                          >
                            Remove
                          </button>
                        )}
                      </div>
                      <input
                        type="text"
                        placeholder="Or paste image URL (https://...)"
                        value={headerConfig.logoUrl || ''}
                        onChange={(e) => updateHeaderConfig({ logoUrl: e.target.value })}
                        className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs font-mono"
                      />
                    </div>
                  </div>
                </div>

                {/* Favicon Upload & Preview */}
                <div className="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                  <div className="flex items-center justify-between">
                    <label className="block font-bold text-gray-800">Browser Favicon (.ico / .png)</label>
                    <span className="text-[10px] text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded font-semibold">
                      Live Tab Sync Active
                    </span>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl border border-gray-300 bg-white p-1.5 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                      {headerConfig.faviconUrl ? (
                        <img
                          src={headerConfig.faviconUrl}
                          alt="Favicon Preview"
                          className="w-8 h-8 object-contain"
                        />
                      ) : (
                        <Globe className="w-6 h-6 text-gray-400" />
                      )}
                    </div>
                    <div className="flex-1 space-y-2">
                      <div className="flex items-center gap-2">
                        <label className="px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 rounded-lg text-xs font-bold cursor-pointer flex items-center gap-1.5 shadow-xs">
                          <Upload className="w-3.5 h-3.5 text-brand-green" />
                          <span>Upload Favicon</span>
                          <input
                            type="file"
                            accept="image/*,.ico"
                            onChange={handleFaviconUpload}
                            className="hidden"
                          />
                        </label>
                      </div>
                      <input
                        type="text"
                        placeholder="Favicon URL (https://...)"
                        value={headerConfig.faviconUrl || ''}
                        onChange={(e) => updateHeaderConfig({ faviconUrl: e.target.value })}
                        className="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs font-mono"
                      />
                    </div>
                  </div>
                </div>
              </div>

              {/* Announcement & Tagline Settings */}
              <div className="space-y-4">
                <h4 className="font-bold text-sm text-brand-green font-display border-b border-emerald-100 pb-1.5">
                  Header Announcement & Regional Dispatch
                </h4>
                
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Top Announcement Bar Text</label>
                  <input
                    type="text"
                    value={headerConfig.announcementText}
                    onChange={(e) => updateHeaderConfig({ announcementText: e.target.value })}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-medium"
                  />
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Brand Tagline</label>
                    <input
                      type="text"
                      value={headerConfig.tagline}
                      onChange={(e) => updateHeaderConfig({ tagline: e.target.value })}
                      className="w-full p-2.5 border border-gray-300 rounded-xl"
                    />
                  </div>
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Default Dispatch Location</label>
                    <input
                      type="text"
                      value={headerConfig.defaultLocation}
                      onChange={(e) => updateHeaderConfig({ defaultLocation: e.target.value })}
                      className="w-full p-2.5 border border-gray-300 rounded-xl"
                    />
                  </div>
                </div>

                {/* Navigation Items Editor */}
                <div>
                  <label className="block font-semibold text-gray-700 mb-2">Category Navigation Menu Items</label>
                  <div className="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                    {headerConfig.navItems.map((item, idx) => (
                      <div key={item.id || idx} className="flex items-center gap-2 p-2 bg-gray-50 rounded-xl border border-gray-200">
                        <input
                          type="checkbox"
                          checked={item.enabled}
                          onChange={(e) => {
                            const updated = [...headerConfig.navItems];
                            updated[idx] = { ...item, enabled: e.target.checked };
                            updateHeaderConfig({ navItems: updated });
                          }}
                          className="rounded text-brand-green focus:ring-brand-green"
                        />
                        <input
                          type="text"
                          value={item.label}
                          onChange={(e) => {
                            const updated = [...headerConfig.navItems];
                            updated[idx] = { ...item, label: e.target.value };
                            updateHeaderConfig({ navItems: updated });
                          }}
                          className="flex-1 p-1 text-xs border border-gray-200 rounded bg-white"
                        />
                        <input
                          type="text"
                          value={item.href}
                          onChange={(e) => {
                            const updated = [...headerConfig.navItems];
                            updated[idx] = { ...item, href: e.target.value };
                            updateHeaderConfig({ navItems: updated });
                          }}
                          className="w-36 p-1 text-xs border border-gray-200 rounded bg-white font-mono text-[11px]"
                        />
                      </div>
                    ))}
                  </div>
                </div>
              </div>

            </div>

            {/* Right Column: Footer Addresses, Social Icons & Custom Code */}
            <div className="space-y-6">
              
              <div className="space-y-4">
                <h4 className="font-bold text-sm text-brand-orange font-display border-b border-orange-100 pb-1.5 flex items-center gap-2">
                  <Building2 className="w-4 h-4" />
                  <span>Footer & S S Industries Corporate Addresses</span>
                </h4>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Company / Enterprise Legal Name</label>
                  <input
                    type="text"
                    value={footerConfig.companyName}
                    onChange={(e) => updateFooterConfig({ companyName: e.target.value })}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-bold"
                  />
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Factory / Works Unit Address</label>
                    <textarea
                      rows={2}
                      value={footerConfig.contactAddress}
                      onChange={(e) => updateFooterConfig({ contactAddress: e.target.value })}
                      className="w-full p-2 border border-gray-300 rounded-xl text-xs"
                    />
                  </div>
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">Head Office Address</label>
                    <textarea
                      rows={2}
                      value={footerConfig.headOfficeAddress}
                      onChange={(e) => updateFooterConfig({ headOfficeAddress: e.target.value })}
                      className="w-full p-2 border border-gray-300 rounded-xl text-xs"
                    />
                  </div>
                </div>

                {/* 3 Contact Numbers */}
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Enterprise Contact Numbers</label>
                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <input
                      type="text"
                      value={footerConfig.contactPhone}
                      onChange={(e) => updateFooterConfig({ contactPhone: e.target.value })}
                      className="p-2 border border-gray-300 rounded-lg font-mono text-[11px]"
                      placeholder="Primary (+91 7897492100)"
                    />
                    <input
                      type="text"
                      value={footerConfig.altPhone1 || '+91 9005756321'}
                      onChange={(e) => updateFooterConfig({ altPhone1: e.target.value })}
                      className="p-2 border border-gray-300 rounded-lg font-mono text-[11px]"
                      placeholder="Alt 1 (+91 9005756321)"
                    />
                    <input
                      type="text"
                      value={footerConfig.altPhone2 || '+91 8303578806'}
                      onChange={(e) => updateFooterConfig({ altPhone2: e.target.value })}
                      className="p-2 border border-gray-300 rounded-lg font-mono text-[11px]"
                      placeholder="Alt 2 (+91 8303578806)"
                    />
                  </div>
                </div>

                {/* Social Icons Customizer */}
                <div className="pt-2">
                  <label className="block font-semibold text-gray-700 mb-2">Social Media Channels & Direct Links</label>
                  <div className="space-y-2">
                    {[
                      { key: 'whatsapp', showKey: 'showWhatsapp', label: 'WhatsApp', placeholder: 'https://wa.me/917897492100' },
                      { key: 'instagram', showKey: 'showInstagram', label: 'Instagram', placeholder: 'https://instagram.com/...' },
                      { key: 'facebook', showKey: 'showFacebook', label: 'Facebook', placeholder: 'https://facebook.com/...' },
                      { key: 'youtube', showKey: 'showYoutube', label: 'YouTube', placeholder: 'https://youtube.com/...' },
                      { key: 'twitter', showKey: 'showTwitter', label: 'Twitter / X', placeholder: 'https://x.com/...' },
                      { key: 'linkedin', showKey: 'showLinkedin', label: 'LinkedIn', placeholder: 'https://linkedin.com/...' },
                    ].map(item => (
                      <div key={item.key} className="flex items-center gap-2">
                        <input
                          type="checkbox"
                          checked={Boolean((footerConfig.socialLinks as any)[item.showKey])}
                          onChange={(e) => updateSocialLinks({ [item.showKey]: e.target.checked } as any)}
                          className="rounded text-brand-green focus:ring-brand-green"
                        />
                        <span className="w-24 font-medium text-gray-700 shrink-0">{item.label}</span>
                        <input
                          type="text"
                          value={(footerConfig.socialLinks as any)[item.key] || ''}
                          onChange={(e) => updateSocialLinks({ [item.key]: e.target.value } as any)}
                          placeholder={item.placeholder}
                          className="flex-1 p-1.5 border border-gray-300 rounded-lg font-mono text-[11px]"
                        />
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Custom Code Inserter */}
              <div className="space-y-4 pt-2">
                <h4 className="font-bold text-sm text-indigo-700 font-display border-b border-indigo-100 pb-1.5 flex items-center gap-2">
                  <Code className="w-4 h-4" />
                  <span>Custom Scripts & Code Inserter (Advanced)</span>
                </h4>

                <div className="space-y-3">
                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">
                      Custom Head Scripts (Google Analytics GA4, Tag Manager, Meta Pixel)
                    </label>
                    <textarea
                      rows={2}
                      placeholder="<!-- Paste <script> tags or <meta> verification tags here -->"
                      value={headerConfig.customHeadCode || ''}
                      onChange={(e) => updateHeaderConfig({ customHeadCode: e.target.value })}
                      className="w-full p-2 bg-gray-50 border border-gray-300 rounded-xl font-mono text-[11px]"
                    />
                  </div>

                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">
                      Custom Footer Scripts & Badges
                    </label>
                    <textarea
                      rows={2}
                      placeholder="<!-- Paste custom footer badges or tracking pixels here -->"
                      value={footerConfig.customFooterCode || ''}
                      onChange={(e) => updateFooterConfig({ customFooterCode: e.target.value })}
                      className="w-full p-2 bg-gray-50 border border-gray-300 rounded-xl font-mono text-[11px]"
                    />
                  </div>

                  <div>
                    <label className="block font-semibold text-gray-700 mb-1">
                      Custom CSS Overrides
                    </label>
                    <textarea
                      rows={2}
                      placeholder="/* Custom CSS styling rules */"
                      value={footerConfig.customCss || ''}
                      onChange={(e) => updateFooterConfig({ customCss: e.target.value })}
                      className="w-full p-2 bg-gray-50 border border-gray-300 rounded-xl font-mono text-[11px]"
                    />
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>
      )}

      {/* TAB 6: BLOGS MANAGER */}
      {activeTab === 'blogs' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
          <div className="flex items-center justify-between pb-4 border-b border-gray-100">
            <div>
              <h3 className="font-bold text-base text-gray-900 font-display">Village Journal CMS Manager</h3>
              <p className="text-xs text-gray-500">Publish artisan success stories, tool forging documentation, and SHG updates.</p>
            </div>
            <button
              onClick={() => {
                createPost({
                  title: 'New Article: Village Blacksmithing Traditions in Hardoi',
                  slug: `village-blacksmithing-${Date.now()}`,
                  excerpt: 'How ancestral coal hearths forge high-carbon sickles that endure decades of farm work.',
                  content: '<p>Complete story authored via admin CMS...</p>',
                  category: 'Farming & Traditional Tools',
                  categorySlug: 'farming-traditional-tools',
                  image: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80',
                  author: {
                    name: currentUser?.name || 'S S Industries',
                    role: 'Editorial Lead',
                    avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80'
                  },
                  readTime: '4 min read',
                  tags: ['Tools', 'Hardoi', 'Forging'],
                  status: 'Published',
                  seo: {
                    title: 'Village Blacksmithing in Hardoi',
                    metaDescription: 'Traditional tool forging traditions.',
                    canonicalUrl: 'https://ganvgalihat.pages.dev/blog/village-blacksmithing',
                    ogImage: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80',
                    indexRobots: true
                  }
                });
                notifySave();
              }}
              className="px-4 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs"
            >
              <Plus className="w-4 h-4" />
              <span>Create New Article</span>
            </button>
          </div>

          <div className="space-y-3">
            {posts.map(post => (
              <div key={post.id} className="p-4 rounded-xl border border-gray-200 flex items-center justify-between gap-4 text-xs">
                <div className="flex items-center gap-3">
                  <img src={post.image} alt={post.title} className="w-14 h-14 rounded-lg object-cover" />
                  <div>
                    <span className="text-[10px] font-bold text-brand-orange uppercase">{post.category}</span>
                    <h4 className="font-bold text-gray-900 text-sm">{post.title}</h4>
                    <p className="text-gray-500 text-[11px] mt-0.5">By {post.author.name} • {post.publishedAt}</p>
                  </div>
                </div>

                <div className="flex items-center gap-3">
                  <span className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                    post.status === 'Published' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700'
                  }`}>
                    {post.status}
                  </span>
                  <a
                    href={`/blog/${post.slug}`}
                    target="_blank"
                    rel="noreferrer"
                    className="p-2 text-gray-600 hover:text-brand-green"
                  >
                    <ExternalLink className="w-4 h-4" />
                  </a>
                  <button
                    onClick={() => deletePost(post.id)}
                    className="p-2 text-gray-400 hover:text-red-600"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* TAB 7: NEARBY MAP SETTINGS */}
      {activeTab === 'map' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4 text-xs">
          <div className="pb-3 border-b border-gray-100">
            <h3 className="font-bold text-base text-gray-900 font-display">Nearby Location & Interactive Map Configuration</h3>
            <p className="text-gray-500">Configure map provider (100% Free OpenStreetMap or Google Maps API key).</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-3">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Map Engine Provider</label>
                <div className="flex gap-3">
                  <label className="flex items-center gap-2 p-3 rounded-xl border border-gray-300 flex-1 cursor-pointer">
                    <input
                      type="radio"
                      name="mapProvider"
                      checked={mapSettings.provider === 'leaflet'}
                      onChange={() => updateMapSettings({ provider: 'leaflet' })}
                      className="text-brand-green"
                    />
                    <div>
                      <span className="font-bold block">OpenStreetMap (Leaflet)</span>
                      <span className="text-[10px] text-emerald-600 font-semibold">100% Free • No API Key Needed</span>
                    </div>
                  </label>

                  <label className="flex items-center gap-2 p-3 rounded-xl border border-gray-300 flex-1 cursor-pointer">
                    <input
                      type="radio"
                      name="mapProvider"
                      checked={mapSettings.provider === 'google'}
                      onChange={() => updateMapSettings({ provider: 'google' })}
                      className="text-brand-green"
                    />
                    <div>
                      <span className="font-bold block">Google Maps API</span>
                      <span className="text-[10px] text-gray-500">Requires Google Cloud Key</span>
                    </div>
                  </label>
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Google Maps API Key (Optional)</label>
                <input
                  type="text"
                  placeholder="AIzaSy..."
                  value={mapSettings.googleMapsApiKey}
                  onChange={(e) => updateMapSettings({ googleMapsApiKey: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
                />
              </div>
            </div>

            <div className="space-y-3">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Default Center Lat (Hardoi)</label>
                  <input
                    type="number"
                    step="0.0001"
                    value={mapSettings.defaultCenter.lat}
                    onChange={(e) => updateMapSettings({
                      defaultCenter: { ...mapSettings.defaultCenter, lat: Number(e.target.value) }
                    })}
                    className="w-full p-2 border border-gray-300 rounded-xl"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Default Center Lng (Hardoi)</label>
                  <input
                    type="number"
                    step="0.0001"
                    value={mapSettings.defaultCenter.lng}
                    onChange={(e) => updateMapSettings({
                      defaultCenter: { ...mapSettings.defaultCenter, lng: Number(e.target.value) }
                    })}
                    className="w-full p-2 border border-gray-300 rounded-xl"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Default Radius (km)</label>
                <input
                  type="range"
                  min={10}
                  max={200}
                  value={mapSettings.searchRadiusKm}
                  onChange={(e) => updateMapSettings({ searchRadiusKm: Number(e.target.value) })}
                  className="w-full"
                />
                <span className="font-bold text-brand-green">{mapSettings.searchRadiusKm} km radius</span>
              </div>

              <button
                onClick={notifySave}
                className="w-full py-2.5 bg-brand-green text-white font-bold rounded-xl shadow-xs"
              >
                Save Map Configuration
              </button>
            </div>
          </div>
        </div>
      )}

      {/* TAB 8: FIREBASE SETTINGS */}
      {activeTab === 'firebase' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4 text-xs">
          <div className="pb-3 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h3 className="font-bold text-base text-gray-900 font-display">Firebase & Mobile Phone Auth Gateway</h3>
              <p className="text-gray-500">Configure your Google Firebase web credentials for live phone OTP and social authentication.</p>
            </div>
            <button
              onClick={notifySave}
              className="px-5 py-2 bg-brand-green text-white font-bold rounded-xl"
            >
              Save Firebase Keys
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-gray-700 mb-1">Firebase apiKey</label>
              <input
                type="text"
                defaultValue="AIzaSyD-GaunGaliHat-SSIndustries-Key"
                className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">authDomain</label>
              <input
                type="text"
                defaultValue="gaungalihat-ssindustries.firebaseapp.com"
                className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">projectId</label>
              <input
                type="text"
                defaultValue="ganvgalihat-ssindustries"
                className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">storageBucket</label>
              <input
                type="text"
                defaultValue="ganvgalihat-ssindustries.appspot.com"
                className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">messagingSenderId</label>
              <input
                type="text"
                defaultValue="7897492100"
                className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">appId</label>
              <input
                type="text"
                defaultValue="1:7897492100:web:4f8e91a0c2"
                className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
              />
            </div>
          </div>
        </div>
      )}

      {/* TAB 9: TECHNICAL SEO & SEARCH CONSOLE */}
      {activeTab === 'seo' && (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-6 text-xs">
          <div className="flex items-center justify-between pb-4 border-b border-gray-100">
            <div>
              <h3 className="font-bold text-base text-gray-900 font-display">Technical SEO, Dynamic XML Sitemap & Analytics</h3>
              <p className="text-gray-500">Google Search Console verification, dynamic XML sitemaps, robots.txt, GA4, and Meta Pixel.</p>
            </div>
            <button
              onClick={notifySave}
              className="px-5 py-2 bg-brand-green text-white font-bold rounded-xl"
            >
              Save SEO Settings
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {/* Verification & Tracking IDs */}
            <div className="space-y-3">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Google Search Console Verification Tag</label>
                <input
                  type="text"
                  value={seoConfig.googleSearchConsoleVerification}
                  onChange={(e) => updateSeoConfig({ googleSearchConsoleVerification: e.target.value })}
                  placeholder="google-site-verification=..."
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Google Analytics 4 (GA4) Measurement ID</label>
                <input
                  type="text"
                  value={seoConfig.googleAnalyticsId}
                  onChange={(e) => updateSeoConfig({ googleAnalyticsId: e.target.value })}
                  placeholder="G-XXXXXXXXXX"
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Google Tag Manager ID</label>
                <input
                  type="text"
                  value={seoConfig.googleTagManagerId}
                  onChange={(e) => updateSeoConfig({ googleTagManagerId: e.target.value })}
                  placeholder="GTM-XXXXXX"
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Meta Pixel ID</label>
                <input
                  type="text"
                  value={seoConfig.metaPixelId}
                  onChange={(e) => updateSeoConfig({ metaPixelId: e.target.value })}
                  placeholder="1234567890987654"
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
                />
              </div>
            </div>

            {/* Sitemap & Robots.txt Generator */}
            <div className="space-y-4">
              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
                <div className="flex items-center justify-between">
                  <span className="font-bold text-gray-900">Dynamic XML Sitemap Generator</span>
                  <div className="flex gap-2">
                    <button
                      onClick={() => {
                        navigator.clipboard.writeText(generateSitemapXml());
                        setCopiedSitemap(true);
                        setTimeout(() => setCopiedSitemap(false), 2000);
                      }}
                      className="px-2 py-1 bg-white border border-gray-300 rounded text-[11px] font-semibold flex items-center gap-1 hover:bg-gray-100 cursor-pointer"
                    >
                      <Copy className="w-3 h-3" />
                      <span>{copiedSitemap ? 'Copied!' : 'Copy XML'}</span>
                    </button>
                    <button
                      onClick={() => {
                        const blob = new Blob([generateSitemapXml()], { type: 'text/xml' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'sitemap.xml';
                        a.click();
                      }}
                      className="px-2 py-1 bg-brand-green text-white rounded text-[11px] font-semibold flex items-center gap-1 cursor-pointer"
                    >
                      <Download className="w-3 h-3" />
                      <span>Download</span>
                    </button>
                  </div>
                </div>
                <textarea
                  readOnly
                  rows={4}
                  value={generateSitemapXml()}
                  className="w-full p-2 bg-white border border-gray-200 rounded font-mono text-[10px] text-gray-700"
                />
              </div>

              <div className="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
                <div className="flex items-center justify-between">
                  <span className="font-bold text-gray-900">Robots.txt Configuration</span>
                  <button
                    onClick={() => {
                      const blob = new Blob([generateRobotsTxt()], { type: 'text/plain' });
                      const url = URL.createObjectURL(blob);
                      const a = document.createElement('a');
                      a.href = url;
                      a.download = 'robots.txt';
                      a.click();
                    }}
                    className="px-2 py-1 bg-brand-green text-white rounded text-[11px] font-semibold flex items-center gap-1 cursor-pointer"
                  >
                    <Download className="w-3 h-3" />
                    <span>Download</span>
                  </button>
                </div>
                <textarea
                  rows={4}
                  value={seoConfig.robotsTxt}
                  onChange={(e) => updateSeoConfig({ robotsTxt: e.target.value })}
                  className="w-full p-2 bg-white border border-gray-200 rounded font-mono text-[10px] text-gray-700"
                />
              </div>
            </div>

          </div>
        </div>
      )}

      {/* TAB 10: BULK EMAIL & SMS SENDER */}
      {activeTab === 'communications' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">
          
          {/* Bulk Email Sender */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="pb-2 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h3 className="font-bold text-base text-gray-900 font-display flex items-center gap-2">
                  <Mail className="w-4 h-4 text-brand-green" />
                  <span>Bulk Email Campaign Dispatch</span>
                </h3>
                <p className="text-gray-500 text-[11px]">Send promotional newsletters or official announcements to user segments.</p>
              </div>
            </div>

            {emailSentNotice && (
              <div className="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl font-semibold flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4" />
                <span>Bulk email campaign dispatched successfully!</span>
              </div>
            )}

            <form onSubmit={handleSendEmail} className="space-y-3">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Target Audience</label>
                <select
                  value={emailAudience}
                  onChange={(e) => setEmailAudience(e.target.value as any)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                >
                  <option value="All Users">All Registered Users ({users.length * 150} Recipients)</option>
                  <option value="All Vendors">All Rural Vendors & SHGs ({vendors.length} Foundries)</option>
                  <option value="All Customers">All Marketplace Customers</option>
                  <option value="Unverified Vendors">Unverified / Pending KYC Vendors</option>
                </select>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Email Subject Line</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. S S Industries Harvest Special: Hand-Forged Tools Ready"
                  value={emailSubject}
                  onChange={(e) => setEmailSubject(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Email Body Content</label>
                <textarea
                  rows={4}
                  required
                  placeholder="Enter message body, dispatch guidelines, or festive coupon codes..."
                  value={emailBody}
                  onChange={(e) => setEmailBody(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>

              <button
                type="submit"
                className="w-full py-3 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
              >
                <Send className="w-4 h-4" />
                <span>Launch Email Campaign</span>
              </button>
            </form>

            <div className="pt-2 border-t border-gray-100 space-y-2">
              <span className="font-bold text-gray-700 text-[11px] block">Recent Dispatches:</span>
              {emailCampaigns.map(c => (
                <div key={c.id} className="p-2.5 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between text-[11px]">
                  <div>
                    <span className="font-bold text-gray-900 block line-clamp-1">{c.subject}</span>
                    <span className="text-gray-500">{c.recipientAudience} • {c.sentDate}</span>
                  </div>
                  <span className="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold shrink-0">
                    {c.status}
                  </span>
                </div>
              ))}
            </div>
          </div>

          {/* SMS & WhatsApp Sender */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
            <div className="pb-2 border-b border-gray-100">
              <h3 className="font-bold text-base text-gray-900 font-display flex items-center gap-2">
                <MessageSquare className="w-4 h-4 text-brand-orange" />
                <span>SMS & WhatsApp Gateway Sender</span>
              </h3>
              <p className="text-gray-500 text-[11px]">Dispatch compliant DLT SMS notifications, order dispatches & WhatsApp alerts.</p>
            </div>

            {smsSentNotice && (
              <div className="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl font-semibold flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4" />
                <span>Message dispatched to telecom gateway!</span>
              </div>
            )}

            <form onSubmit={handleSendSms} className="space-y-3">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Recipient Number</label>
                <input
                  type="tel"
                  placeholder="+91 7897492100"
                  value={smsPhone}
                  onChange={(e) => setSmsPhone(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">DLT Template ID</label>
                <input
                  type="text"
                  value={smsTemplateId}
                  onChange={(e) => setSmsTemplateId(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
                />
              </div>

              <div>
                <div className="flex justify-between items-center mb-1">
                  <label className="font-semibold text-gray-700">SMS / WhatsApp Message Text</label>
                  <span className="text-[10px] text-gray-500 font-mono">{smsText.length} / 160 chars</span>
                </div>
                <textarea
                  rows={3}
                  value={smsText}
                  onChange={(e) => setSmsText(e.target.value)}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>

              <button
                type="submit"
                className="w-full py-3 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
              >
                <Phone className="w-4 h-4" />
                <span>Dispatch Instant SMS</span>
              </button>
            </form>

            <div className="pt-2 border-t border-gray-100 space-y-2">
              <span className="font-bold text-gray-700 text-[11px] block">Gateway Logs:</span>
              {smsMessages.map(s => (
                <div key={s.id} className="p-2.5 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between text-[11px]">
                  <div>
                    <span className="font-bold text-gray-900 block line-clamp-1">{s.messageText}</span>
                    <span className="text-gray-500">{s.recipientPhone} • {s.sentDate}</span>
                  </div>
                  <span className="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold shrink-0">
                    {s.status}
                  </span>
                </div>
              ))}
            </div>
          </div>

        </div>
      )}

      {/* TAB: HERO & FESTIVAL CUSTOMIZER */}
      {activeTab === 'hero_festival' && (
        <div className="space-y-6">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-gray-200">
            <div>
              <div className="flex items-center gap-2">
                <h2 className="text-xl font-extrabold text-gray-900 font-display">
                  Hero & Festival Banner Customizer
                </h2>
                <span className="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">
                  Live Storefront Sync
                </span>
              </div>
              <p className="text-xs text-gray-500 mt-1">
                Customize homepage banners for Indian festivals (Diwali, Holi, Makar Sankranti, Independence Day) with custom greetings, glowing gradients, countdown timers, and discount promo codes.
              </p>
            </div>

            <button
              onClick={handleSaveFestival}
              className="px-5 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 transition-all cursor-pointer self-start sm:self-auto"
            >
              <CheckCircle2 className="w-4 h-4" />
              <span>Apply & Save to Live Storefront</span>
            </button>
          </div>

          {/* Quick Festival Presets Selector */}
          <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-3">
            <div className="flex items-center justify-between">
              <span className="font-bold text-xs text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                <Sparkles className="w-4 h-4 text-amber-500" />
                <span>1-Click Festival Presets:</span>
              </span>
              <span className="text-[11px] text-gray-400">Clicking any preset populates the copy and colors below</span>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
              {[
                { key: 'diwali', label: 'Diwali Mahotsav', icon: '🪔', badge: 'Diwali Special' },
                { key: 'holi', label: 'Holi Rangotsav', icon: '🎨', badge: 'Spring Colors' },
                { key: 'makar_sankranti', label: 'Makar Sankranti', icon: '🌾', badge: 'Harvest Fair' },
                { key: 'patriotic', label: 'Independence / Republic', icon: '🇮🇳', badge: 'Swadeshi Pride' },
                { key: 'default', label: 'Evergreen Heritage', icon: '🌿', badge: 'Default' }
              ].map(p => (
                <button
                  key={p.key}
                  type="button"
                  onClick={() => handleSelectPreset(p.key)}
                  className="p-3 rounded-xl border border-gray-200 hover:border-brand-green hover:shadow-md transition-all text-left bg-white flex flex-col justify-between cursor-pointer group"
                >
                  <div className="flex items-center justify-between mb-2">
                    <span className="text-2xl">{p.icon}</span>
                    <span className="text-[9px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 group-hover:bg-brand-green group-hover:text-white transition-colors">
                      {p.badge}
                    </span>
                  </div>
                  <div>
                    <p className="font-bold text-xs text-gray-900 group-hover:text-brand-green">{p.label}</p>
                    <p className="text-[10px] text-gray-500 mt-0.5">Instant Apply &rarr;</p>
                  </div>
                </button>
              ))}
            </div>
          </div>

          {/* Form and Live Visual Preview */}
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {/* Form Editor (7 cols) */}
            <form onSubmit={handleSaveFestival} className="lg:col-span-7 bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4 text-xs">
              <h3 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2">
                Banner Content & Typography
              </h3>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Festival / Campaign Name</label>
                  <input
                    type="text"
                    value={festivalForm.festivalName}
                    onChange={(e) => setFestivalForm({ ...festivalForm, festivalName: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                    placeholder="e.g. Diwali Mahotsav 2026"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Gradient Color Preset</label>
                  <select
                    value={festivalForm.gradientPreset}
                    onChange={(e) => setFestivalForm({ ...festivalForm, gradientPreset: e.target.value as any })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                  >
                    <option value="diwali-gold">Diwali Gold & Deep Royal Red</option>
                    <option value="holi-vibrant">Holi Vibrant Purple & Pink</option>
                    <option value="makar-sankranti">Harvest Sun Gold & Forest</option>
                    <option value="patriotic">Swadeshi Tricolor Accent</option>
                    <option value="emerald-forest">Evergreen Village Emerald (Default)</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Top Announcement Badge Text</label>
                <input
                  type="text"
                  value={festivalForm.badgeText}
                  onChange={(e) => setFestivalForm({ ...festivalForm, badgeText: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                  placeholder="e.g. 🪔 Shubh Deepawali Special • Empowering Rural Artisans"
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Main Heading (Line 1)</label>
                  <input
                    type="text"
                    value={festivalForm.mainHeading}
                    onChange={(e) => setFestivalForm({ ...festivalForm, mainHeading: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Highlight Heading (Line 2)</label>
                  <input
                    type="text"
                    value={festivalForm.highlightHeading}
                    onChange={(e) => setFestivalForm({ ...festivalForm, highlightHeading: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-amber-600 font-bold"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Subheading Narrative</label>
                <textarea
                  rows={3}
                  value={festivalForm.subheading}
                  onChange={(e) => setFestivalForm({ ...festivalForm, subheading: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Festival Promo Code</label>
                  <input
                    type="text"
                    value={festivalForm.promoCode || ''}
                    onChange={(e) => setFestivalForm({ ...festivalForm, promoCode: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono uppercase font-bold text-brand-green"
                    placeholder="e.g. DIWALI20"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Promo Discount (%)</label>
                  <input
                    type="number"
                    value={festivalForm.promoDiscountPercent || 15}
                    onChange={(e) => setFestivalForm({ ...festivalForm, promoDiscountPercent: Number(e.target.value) })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono"
                    min={0}
                    max={80}
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-100">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Floating Badge Icon</label>
                  <input
                    type="text"
                    value={festivalForm.floatingPill1Icon || '⚒️'}
                    onChange={(e) => setFestivalForm({ ...festivalForm, floatingPill1Icon: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-center text-lg"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Floating Badge Title</label>
                  <input
                    type="text"
                    value={festivalForm.floatingPill1Title || ''}
                    onChange={(e) => setFestivalForm({ ...festivalForm, floatingPill1Title: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Floating Badge Subtitle</label>
                  <input
                    type="text"
                    value={festivalForm.floatingPill1Subtitle || ''}
                    onChange={(e) => setFestivalForm({ ...festivalForm, floatingPill1Subtitle: e.target.value })}
                    className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-xs"
                  />
                </div>
              </div>

              <div className="pt-4 flex justify-end">
                <button
                  type="submit"
                  className="px-6 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer"
                >
                  <CheckCircle2 className="w-4 h-4" />
                  <span>Update & Save Customizer</span>
                </button>
              </div>
            </form>

            {/* Live Interactive Preview Card (5 cols) */}
            <div className="lg:col-span-5 space-y-4">
              <div className="bg-white p-4 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
                <span className="font-bold text-xs text-gray-900 flex items-center gap-1.5">
                  <Activity className="w-4 h-4 text-emerald-500" />
                  <span>Live Storefront Preview</span>
                </span>
                <span className="text-[10px] text-gray-500 font-mono">Synchronized</span>
              </div>

              {/* Miniature Hero Preview Container */}
              <div className={`p-6 rounded-3xl text-white shadow-2xl relative overflow-hidden transition-all duration-500 ${
                festivalForm.gradientPreset === 'diwali-gold'
                  ? 'bg-gradient-to-br from-amber-950 via-rose-950 to-amber-900'
                  : festivalForm.gradientPreset === 'holi-vibrant'
                  ? 'bg-gradient-to-br from-purple-950 via-pink-900 to-amber-900'
                  : festivalForm.gradientPreset === 'makar-sankranti'
                  ? 'bg-gradient-to-br from-amber-900 via-yellow-900 to-emerald-950'
                  : festivalForm.gradientPreset === 'patriotic'
                  ? 'bg-gradient-to-br from-orange-950 via-slate-900 to-emerald-950'
                  : 'bg-gradient-to-br from-emerald-900 via-brand-green to-emerald-950'
              }`}>
                {/* Promo Code Pill */}
                {festivalForm.promoCode && (
                  <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 font-bold text-[10px] shadow-sm mb-3">
                    <span>🎁 {festivalForm.promoDiscountPercent}% OFF</span>
                    <span className="bg-slate-950 text-amber-300 px-1.5 py-0.5 rounded font-mono">
                      {festivalForm.promoCode}
                    </span>
                  </div>
                )}

                {/* Badge text */}
                <div className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-black/30 border border-white/20 text-amber-300 text-[10px] font-semibold mb-3 block truncate">
                  <Sparkles className="w-3 h-3 shrink-0" />
                  <span className="truncate">{festivalForm.badgeText}</span>
                </div>

                {/* Headings */}
                <h4 className="text-xl font-extrabold font-display leading-tight">
                  {festivalForm.mainHeading} <br />
                  <span className="text-amber-300 drop-shadow-sm">{festivalForm.highlightHeading}</span>
                </h4>

                <p className="text-xs text-white/80 line-clamp-3 mt-2 leading-relaxed">
                  {festivalForm.subheading}
                </p>

                {/* Mock CTAs */}
                <div className="flex gap-2 pt-4">
                  <span className="px-3 py-1.5 bg-brand-orange text-white rounded-lg text-[10px] font-bold shadow-sm">
                    Explore Marketplace &rarr;
                  </span>
                  <span className="px-3 py-1.5 bg-white/20 border border-white/30 text-white rounded-lg text-[10px] font-medium">
                    Farming Tools
                  </span>
                </div>

                {/* Floating pill preview */}
                <div className="mt-4 p-2.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center gap-2">
                  <span className="text-xl">{festivalForm.floatingPill1Icon}</span>
                  <div>
                    <p className="text-[10px] text-amber-200 font-medium">{festivalForm.floatingPill1Subtitle}</p>
                    <p className="text-xs font-bold text-white">{festivalForm.floatingPill1Title}</p>
                  </div>
                </div>
              </div>

              <div className="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-center gap-2">
                <Sparkles className="w-4 h-4 text-brand-orange shrink-0" />
                <span>Changes saved here are instantly visible to customer shoppers visiting the GaunGaliHat homepage.</span>
              </div>
            </div>

          </div>
        </div>
      )}

      {/* TAB: SECURITY & CONCURRENCY AUDIT LOGS */}
      {activeTab === 'security' && (
        <div className="space-y-6">
          <div className="pb-2 border-b border-gray-200">
            <h2 className="text-xl font-extrabold text-gray-900 font-display">
              Multi-Million User Concurrency Shield & Security Audits
            </h2>
            <p className="text-xs text-gray-500 mt-1">
              Cloudflare global edge network telemetry, high-scale traffic mitigation, RBAC permissions matrix, and tamper-evident audit logs.
            </p>
          </div>

          {/* 4 KPI cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
              <span className="text-xs text-gray-500 font-medium">Cloudflare Edge PoPs</span>
              <div className="text-2xl font-bold text-emerald-600 font-display">330+ Cities</div>
              <p className="text-[11px] text-gray-500">Sub-millisecond global TTFB</p>
            </div>
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
              <span className="text-xs text-gray-500 font-medium">Edge Cache Hit Ratio</span>
              <div className="text-2xl font-bold text-brand-green font-display">99.8%</div>
              <p className="text-[11px] text-emerald-600 font-medium">Immutable static bundles</p>
            </div>
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
              <span className="text-xs text-gray-500 font-medium">Concurrency Shield</span>
              <div className="text-2xl font-bold text-brand-orange font-display">10M+ Req/min</div>
              <p className="text-[11px] text-gray-500">Zero-cold-start workers</p>
            </div>
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-1">
              <span className="text-xs text-gray-500 font-medium">Client Crash Shield</span>
              <div className="text-2xl font-bold text-blue-600 font-display">Active</div>
              <p className="text-[11px] text-blue-600 font-medium">React ErrorBoundary guarded</p>
            </div>
          </div>

          {/* RBAC Matrix Card */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-4">
            <h3 className="font-bold text-sm text-gray-900 font-display border-b border-gray-100 pb-2">
              Role-Based Access Control (RBAC) Governance Matrix
            </h3>

            <div className="overflow-x-auto text-xs">
              <table className="w-full text-left">
                <thead className="bg-gray-50 text-gray-600 uppercase font-semibold">
                  <tr>
                    <th className="p-3">User Role</th>
                    <th className="p-3">Hero & Festivals</th>
                    <th className="p-3">Blogs & Stories</th>
                    <th className="p-3">Bulk SMS / Email</th>
                    <th className="p-3">Users & KYC</th>
                    <th className="p-3">Technical SEO</th>
                    <th className="p-3">Root Security</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  <tr className="hover:bg-gray-50/60 font-medium">
                    <td className="p-3 font-bold text-amber-600">Super Admin</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                  </tr>
                  <tr className="hover:bg-gray-50/60">
                    <td className="p-3 font-bold text-brand-green">Admin (Operations)</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Manage Users</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-gray-400">View Only</td>
                  </tr>
                  <tr className="hover:bg-gray-50/60">
                    <td className="p-3 font-bold text-purple-600">Marketer</td>
                    <td className="p-3 text-emerald-600 font-bold">Full Access</td>
                    <td className="p-3 text-emerald-600 font-bold">Editorial</td>
                    <td className="p-3 text-emerald-600 font-bold">Campaign Dispatch</td>
                    <td className="p-3 text-red-500 font-semibold">Restricted</td>
                    <td className="p-3 text-emerald-600 font-bold">Meta & Sitemaps</td>
                    <td className="p-3 text-red-500 font-semibold">Restricted</td>
                  </tr>
                  <tr className="hover:bg-gray-50/60">
                    <td className="p-3 font-bold text-cyan-600">Content Writer</td>
                    <td className="p-3 text-emerald-600 font-bold">Copywriting</td>
                    <td className="p-3 text-emerald-600 font-bold">Author & Publish</td>
                    <td className="p-3 text-gray-400">View Drafts</td>
                    <td className="p-3 text-red-500 font-semibold">Restricted</td>
                    <td className="p-3 text-emerald-600 font-bold">Blog SEO</td>
                    <td className="p-3 text-red-500 font-semibold">Restricted</td>
                  </tr>
                  <tr className="hover:bg-gray-50/60">
                    <td className="p-3 font-bold text-blue-600">Vendor / SHG</td>
                    <td className="p-3 text-gray-400">View Only</td>
                    <td className="p-3 text-gray-400">View Stories</td>
                    <td className="p-3 text-red-500 font-semibold">Restricted</td>
                    <td className="p-3 text-gray-400">Self Profile</td>
                    <td className="p-3 text-red-500 font-semibold">Restricted</td>
                    <td className="p-3 text-red-500 font-semibold">Restricted</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          {/* Audit Trail Table */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div className="p-5 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h3 className="font-bold text-sm text-gray-900 font-display">Tamper-Evident System Audit Trail</h3>
                <p className="text-[11px] text-gray-500">Security event logging with edge actor signatures</p>
              </div>
              <span className="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                Immutable Log Chain
              </span>
            </div>

            <div className="overflow-x-auto text-xs">
              <table className="w-full text-left">
                <thead className="bg-gray-50 text-gray-600 uppercase font-semibold">
                  <tr>
                    <th className="p-3">Timestamp</th>
                    <th className="p-3">Actor</th>
                    <th className="p-3">Role</th>
                    <th className="p-3">Action</th>
                    <th className="p-3">Module</th>
                    <th className="p-3">Details</th>
                    <th className="p-3">Edge IP</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {initialAuditLogs.map(log => (
                    <tr key={log.id} className="hover:bg-gray-50/60 font-mono text-[11px]">
                      <td className="p-3 text-gray-500 whitespace-nowrap">{log.timestamp}</td>
                      <td className="p-3 font-bold text-gray-900">{log.actorName}</td>
                      <td className="p-3">
                        <span className="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800">
                          {log.actorRole.replace('_', ' ')}
                        </span>
                      </td>
                      <td className="p-3 font-bold text-brand-green">{log.action}</td>
                      <td className="p-3 text-gray-700">{log.module}</td>
                      <td className="p-3 text-gray-600 max-w-xs font-sans text-xs">{log.details}</td>
                      <td className="p-3 text-gray-400 whitespace-nowrap">{log.ipAddress}</td>
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
