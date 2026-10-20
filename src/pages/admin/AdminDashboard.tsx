import React, { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { usePlatform } from '../../context/PlatformContext';
import { useSiteConfig } from '../../context/SiteConfigContext';
import { UserRole, SupportTicket, BlogPost } from '../../types';
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
  UserCheck
} from 'lucide-react';

export const AdminDashboard: React.FC = () => {
  const { currentUser, currentRole, switchRole } = useAuth();
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
    updateMapSettings
  } = usePlatform();

  const {
    headerConfig,
    updateHeaderConfig,
    footerConfig,
    updateFooterConfig,
    updateSocialLinks,
    resetConfigs
  } = useSiteConfig();

  // Active Tab
  const [activeTab, setActiveTab] = useState<
    'overview' | 'users' | 'vendors' | 'tickets' | 'customizer' | 'blogs' | 'map' | 'firebase' | 'seo' | 'communications'
  >('overview');

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
      senderName: 'S S Industries / GanvGaliHut',
      senderEmail: 'namaste@ganvgalihat.com',
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
      <div className="bg-gradient-to-r from-emerald-950 via-brand-green to-emerald-900 rounded-3xl p-6 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-800 text-amber-300 text-xs font-semibold mb-1">
            <Sparkles className="w-3.5 h-3.5" />
            <span>S S Industries Central Governance</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-bold font-display text-white">
            GanvGaliHut Operations Portal
          </h1>
          <p className="text-xs text-emerald-100/90 mt-1">
            Logged in as <strong>{currentUser?.name || 'Anubhav Kashyap'}</strong> ({currentUser?.email}) • Active Role: <span className="uppercase font-bold text-amber-300">{currentRole}</span>
          </p>
        </div>

        {/* Live Role Switcher (Required for testing multiple user level access) */}
        <div className="bg-white/10 backdrop-blur-md p-2.5 rounded-2xl border border-white/20 text-xs">
          <span className="text-[10px] text-emerald-200 font-semibold block mb-1.5">
            Switch View Role:
          </span>
          <div className="flex flex-wrap gap-1.5">
            {(['super_admin', 'admin', 'vendor', 'support', 'customer'] as UserRole[]).map((role) => (
              <button
                key={role}
                onClick={() => switchRole(role)}
                className={`px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer ${
                  currentRole === role
                    ? 'bg-amber-400 text-gray-900 shadow-sm'
                    : 'bg-emerald-900/60 text-white hover:bg-emerald-800'
                }`}
              >
                {role.replace('_', ' ')}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Tab Navigation Menu */}
      <div className="flex border-b border-gray-200 overflow-x-auto pb-1 scrollbar-none gap-2 text-xs font-semibold text-gray-600">
        {[
          { id: 'overview', label: 'Overview', icon: TrendingUp },
          { id: 'users', label: `Users (${users.length})`, icon: Users },
          { id: 'vendors', label: `Vendors (${vendors.length})`, icon: Building2 },
          { id: 'tickets', label: `Support Tickets (${tickets.length})`, icon: Ticket },
          { id: 'customizer', label: 'Header & Footer Customizer', icon: Sliders },
          { id: 'blogs', label: `Blogs Manager (${posts.length})`, icon: FileText },
          { id: 'map', label: 'Nearby Map Settings', icon: Compass },
          { id: 'firebase', label: 'Firebase & OTP Auth', icon: Flame },
          { id: 'seo', label: 'SEO & Search Console', icon: Globe },
          { id: 'communications', label: 'Bulk Email & SMS', icon: Mail }
        ].map(tab => {
          const Icon = tab.icon;
          const isActive = activeTab === tab.id;
          return (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer shrink-0 ${
                isActive
                  ? 'bg-brand-green text-white shadow-xs font-bold'
                  : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200/80'
              }`}
            >
              <Icon className="w-4 h-4" />
              <span>{tab.label}</span>
            </button>
          );
        })}
      </div>

      {/* TAB 1: OVERVIEW & ANALYTICS */}
      {activeTab === 'overview' && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
              <span className="text-xs text-gray-500 font-medium">Total Registered Users</span>
              <div className="text-2xl font-bold text-gray-900 font-display mt-1">{users.length} Users</div>
              <p className="text-[11px] text-emerald-600 font-medium mt-1">Multi-role RBAC active</p>
            </div>
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
              <span className="text-xs text-gray-500 font-medium">Onboarded Foundries & SHGs</span>
              <div className="text-2xl font-bold text-brand-orange font-display mt-1">{vendors.length} Vendors</div>
              <p className="text-[11px] text-gray-500 font-medium mt-1">Hardoi, Sandila & regional</p>
            </div>
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
              <span className="text-xs text-gray-500 font-medium">Open Support Tickets</span>
              <div className="text-2xl font-bold text-blue-600 font-display mt-1">
                {tickets.filter(t => t.status === 'Open' || t.status === 'In Progress').length} Active
              </div>
              <p className="text-[11px] text-gray-500 font-medium mt-1">Live customer triage</p>
            </div>
            <div className="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs">
              <span className="text-xs text-gray-500 font-medium">S S Industries Works Hub</span>
              <div className="text-2xl font-bold text-brand-green font-display mt-1">Online</div>
              <p className="text-[11px] text-emerald-600 font-medium mt-1">Foundry dispatch active</p>
            </div>
          </div>

          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <h3 className="font-bold text-base text-gray-900 font-display">Operational Facilities</h3>
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
                          src={user.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80'}
                          alt={user.name}
                          className="w-8 h-8 rounded-full object-cover"
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
          <div className="flex items-center justify-between pb-4 border-b border-gray-100">
            <div>
              <h3 className="font-bold text-base text-gray-900 font-display">Live Site Customization Suite</h3>
              <p className="text-xs text-gray-500">Edits reflect immediately in the header, announcement bar, social links, and footer across the whole marketplace.</p>
            </div>
            <div className="flex gap-2">
              <button
                onClick={resetConfigs}
                className="px-3 py-1.5 border border-gray-300 rounded-xl text-xs font-semibold hover:bg-gray-50"
              >
                Reset Defaults
              </button>
              <button
                onClick={notifySave}
                className="px-5 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-sm"
              >
                Save Changes to Live Site
              </button>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs">
            
            {/* Header Settings */}
            <div className="space-y-4">
              <h4 className="font-bold text-sm text-brand-green font-display border-b border-emerald-100 pb-1">
                Header & Announcement Settings
              </h4>
              
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Top Announcement Bar Text</label>
                <input
                  type="text"
                  value={headerConfig.announcementText}
                  onChange={(e) => updateHeaderConfig({ announcementText: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
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

            {/* Footer & Social Icons Customizer */}
            <div className="space-y-4">
              <h4 className="font-bold text-sm text-brand-orange font-display border-b border-orange-100 pb-1">
                Footer & S S Industries Corporate Addresses
              </h4>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Company / Enterprise Name</label>
                <input
                  type="text"
                  value={footerConfig.companyName}
                  onChange={(e) => updateFooterConfig({ companyName: e.target.value })}
                  className="w-full p-2.5 border border-gray-300 rounded-xl font-bold"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
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
                <div className="grid grid-cols-3 gap-2">
                  <input
                    type="text"
                    value={footerConfig.contactPhone}
                    onChange={(e) => updateFooterConfig({ contactPhone: e.target.value })}
                    className="p-2 border border-gray-300 rounded-lg font-mono text-[11px]"
                    placeholder="Primary"
                  />
                  <input
                    type="text"
                    value={footerConfig.altPhone1 || '+91 9005756321'}
                    onChange={(e) => updateFooterConfig({ altPhone1: e.target.value })}
                    className="p-2 border border-gray-300 rounded-lg font-mono text-[11px]"
                    placeholder="Alt 1"
                  />
                  <input
                    type="text"
                    value={footerConfig.altPhone2 || '+91 8303578806'}
                    onChange={(e) => updateFooterConfig({ altPhone2: e.target.value })}
                    className="p-2 border border-gray-300 rounded-lg font-mono text-[11px]"
                    placeholder="Alt 2"
                  />
                </div>
              </div>

              {/* Social Icons Customizer */}
              <div className="pt-2">
                <label className="block font-semibold text-gray-700 mb-2">Social Icons Customizer</label>
                <div className="space-y-2">
                  {[
                    { key: 'whatsapp', showKey: 'showWhatsapp', label: 'WhatsApp Direct', placeholder: 'https://wa.me/917897492100' },
                    { key: 'facebook', showKey: 'showFacebook', label: 'Facebook', placeholder: 'https://facebook.com/...' },
                    { key: 'instagram', showKey: 'showInstagram', label: 'Instagram', placeholder: 'https://instagram.com/...' },
                    { key: 'youtube', showKey: 'showYoutube', label: 'YouTube', placeholder: 'https://youtube.com/...' },
                    { key: 'linkedin', showKey: 'showLinkedin', label: 'LinkedIn', placeholder: 'https://linkedin.com/...' },
                  ].map(item => (
                    <div key={item.key} className="flex items-center gap-2">
                      <input
                        type="checkbox"
                        checked={(footerConfig.socialLinks as any)[item.showKey]}
                        onChange={(e) => updateSocialLinks({ [item.showKey]: e.target.checked } as any)}
                        className="rounded text-brand-green"
                      />
                      <span className="w-24 font-medium text-gray-700">{item.label}</span>
                      <input
                        type="text"
                        value={(footerConfig.socialLinks as any)[item.key] || ''}
                        onChange={(e) => updateSocialLinks({ [item.key]: e.target.value } as any)}
                        placeholder={item.placeholder}
                        className="flex-1 p-1.5 border border-gray-200 rounded-lg font-mono text-[11px]"
                      />
                    </div>
                  ))}
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
                defaultValue="AIzaSyD-GanvGaliHut-SSIndustries-Key"
                className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-[11px]"
              />
            </div>
            <div>
              <label className="block font-semibold text-gray-700 mb-1">authDomain</label>
              <input
                type="text"
                defaultValue="ganvgalihat-ssindustries.firebaseapp.com"
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

    </div>
  );
};
