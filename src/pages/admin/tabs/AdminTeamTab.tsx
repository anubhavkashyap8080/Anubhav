import React, { useState } from 'react';
import { usePlatform } from '../../../context/PlatformContext';
import { useAuth } from '../../../context/AuthContext';
import { User, UserRole, UserWorkLog } from '../../../types';
import { hashPassword } from '../../../utils/cryptoUtils';
import {
  Users,
  Shield,
  Briefcase,
  Calendar,
  Phone,
  Mail,
  CheckCircle2,
  Clock,
  Plus,
  Search,
  ExternalLink,
  Activity,
  FileText,
  ShieldCheck,
  Send,
  UserCheck,
  Lock,
  Key,
  Eye,
  EyeOff,
  Copy,
  Check
} from 'lucide-react';

export const AdminTeamTab: React.FC = () => {
  const { users, updateUserRole, updateUserPassword, toggleUserStatus, addUser } = usePlatform();
  const { currentUser } = useAuth();

  const [searchTerm, setSearchTerm] = useState('');
  const [selectedRoleFilter, setSelectedRoleFilter] = useState<string>('all');
  const [selectedUser, setSelectedUser] = useState<User | null>(null);

  // New Team Member Modal State
  const [showAddMemberModal, setShowAddMemberModal] = useState(false);
  const [newMemberName, setNewMemberName] = useState('');
  const [newMemberUsername, setNewMemberUsername] = useState('');
  const [newMemberPassword, setNewMemberPassword] = useState('');
  const [newMemberEmail, setNewMemberEmail] = useState('');
  const [newMemberPhone, setNewMemberPhone] = useState('');
  const [newMemberRole, setNewMemberRole] = useState<UserRole>('admin');
  const [newMemberDepartment, setNewMemberDepartment] = useState('');
  const [showMemberPasswordInForm, setShowMemberPasswordInForm] = useState(false);
  const [resettingMember, setResettingMember] = useState<User | null>(null);
  const [newMemberResetPass, setNewMemberResetPass] = useState('');
  const [resetMemberSuccess, setResetMemberSuccess] = useState<string | null>(null);

  // New Work Log Entry Form in Modal
  const [showAddLogForm, setShowAddLogForm] = useState(false);
  const [newLogTitle, setNewLogTitle] = useState('');
  const [newLogCategory, setNewLogCategory] = useState<string>('Operations');
  const [newLogDetails, setNewLogDetails] = useState('');

  // Internal team roles (exclude pure customer if desired, or show all with team priority)
  const isTeamRole = (role: UserRole) => role !== 'customer';

  const teamMembers = users.filter((u) => {
    const isTeam = isTeamRole(u.role);
    const matchesSearch =
      u.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      u.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
      u.phone.includes(searchTerm) ||
      (u.department && u.department.toLowerCase().includes(searchTerm.toLowerCase()));
    const matchesRole = selectedRoleFilter === 'all' || u.role === selectedRoleFilter;
    return isTeam && matchesSearch && matchesRole;
  });

  const handleOpenProfile = (user: User) => {
    setSelectedUser(user);
    setShowAddLogForm(false);
  };

  const handleAddWorkLog = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedUser || !newLogTitle.trim()) return;

    const newEntry: UserWorkLog = {
      id: `wl-${Date.now()}`,
      action: newLogTitle.trim(),
      module: 'Operations',
      category: newLogCategory,
      timestamp: new Date().toLocaleString('en-IN', {
        timeZone: 'Asia/Kolkata',
        dateStyle: 'medium',
        timeStyle: 'short'
      }) + ' IST',
      details: newLogDetails.trim() || 'Action performed and logged to GaunGaliHat audit trail.',
      status: 'completed'
    };

    const updatedUser = {
      ...selectedUser,
      workLog: [newEntry, ...(selectedUser.workLog || [])]
    };

    // Update localStorage and in-memory list
    const updatedUsers = users.map((u) => (u.id === selectedUser.id ? updatedUser : u));
    localStorage.setItem('ggh_platform_users', JSON.stringify(updatedUsers));
    setSelectedUser(updatedUser);

    setNewLogTitle('');
    setNewLogDetails('');
    setShowAddLogForm(false);
  };

  const handleCreateTeamMember = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMemberName.trim() || !newMemberEmail.trim()) return;
    const cleanUsername = newMemberUsername.trim() || newMemberName.trim().toLowerCase().replace(/[^a-zA-Z0-9_]/g, '_');
    const cleanPassword = newMemberPassword.trim() || `Ggh@${Math.floor(1000 + Math.random() * 9000)}`;
    const { hashHex, saltHex } = await hashPassword(cleanPassword);
    addUser({
      name: newMemberName.trim(),
      username: cleanUsername,
      passwordHash: hashHex,
      passwordSalt: saltHex,
      email: newMemberEmail.trim(),
      phone: newMemberPhone.trim() || '+91 7897492100',
      role: newMemberRole,
      department: newMemberDepartment.trim() || (newMemberRole === 'admin' ? 'Operations & Dispatch' : newMemberRole === 'marketer' ? 'Growth Marketing' : newMemberRole === 'content_writer' ? 'Rural Editorial' : newMemberRole === 'support' ? 'Customer Care' : 'Management'),
      status: 'active'
    });
    setNewMemberName('');
    setNewMemberUsername('');
    setNewMemberPassword('');
    setNewMemberEmail('');
    setNewMemberPhone('');
    setNewMemberDepartment('');
    setShowAddMemberModal(false);
  };

  const handleResetMemberPass = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!resettingMember || !newMemberResetPass.trim()) return;
    const { hashHex, saltHex } = await hashPassword(newMemberResetPass.trim());
    updateUserPassword(resettingMember.id, hashHex, saltHex);
    setResetMemberSuccess(`Password reset & encrypted with PBKDF2 for @${resettingMember.username || resettingMember.name}!`);
    setTimeout(() => {
      setResetMemberSuccess(null);
      setResettingMember(null);
      setNewMemberResetPass('');
    }, 2500);
  };

  const getRoleBadgeColor = (role: UserRole) => {
    switch (role) {
      case 'super_admin':
        return 'bg-purple-100 text-purple-900 border-purple-200';
      case 'admin':
        return 'bg-emerald-100 text-emerald-900 border-emerald-200';
      case 'marketer':
        return 'bg-blue-100 text-blue-900 border-blue-200';
      case 'content_writer':
        return 'bg-amber-100 text-amber-900 border-amber-200';
      case 'support':
        return 'bg-teal-100 text-teal-900 border-teal-200';
      case 'vendor':
        return 'bg-orange-100 text-orange-900 border-orange-200';
      default:
        return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  const totalLogsCount = teamMembers.reduce(
    (acc, u) => acc + (u.workLog?.length || 0),
    0
  );

  return (
    <div className="space-y-4">
      {/* Metrics Row */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Internal Staff & Team</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-gray-900">
              {teamMembers.length} Members
            </span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
              Active Roster
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Logged Team Work Items</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-brand-green">
              {totalLogsCount} Audited
            </span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
              Audit Trail
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Departments Covered</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-blue-600">5 Divisions</span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
              Full Spectrum
            </span>
          </div>
        </div>

        <div className="bg-white p-3.5 rounded-xl border border-gray-100 shadow-xs">
          <span className="text-[11px] text-gray-500 font-medium">Security Access</span>
          <div className="flex items-center justify-between mt-1">
            <span className="text-xl font-bold font-display text-purple-700">RBAC Level 4</span>
            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
              MFA Active
            </span>
          </div>
        </div>
      </div>

      {/* Main Team Table Card */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-4 sm:p-5 space-y-4">
        {/* Search & Filter Header */}
        <div className="flex flex-col sm:flex-row items-center justify-between gap-3 pb-3 border-b border-gray-100">
          <div className="flex items-center gap-2 w-full sm:w-auto flex-1">
            <div className="relative flex-1 sm:max-w-xs">
              <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                placeholder="Search staff by name, email, department..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50/50 focus:bg-white focus:border-brand-green outline-hidden"
              />
            </div>

            <select
              value={selectedRoleFilter}
              onChange={(e) => setSelectedRoleFilter(e.target.value)}
              className="px-3 py-2 text-xs border border-gray-200 rounded-xl bg-gray-50 text-gray-700 outline-hidden font-medium"
            >
              <option value="all">All Roles ({teamMembers.length})</option>
              <option value="super_admin">Super Admin</option>
              <option value="admin">Operations Admin</option>
              <option value="marketer">Marketing Manager</option>
              <option value="content_writer">Content Writer</option>
              <option value="support">Customer Support</option>
              <option value="vendor">Vendor Lead</option>
            </select>
          </div>

          <div className="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <span className="text-[11px] text-gray-500 hidden md:inline">
              Click profile to view login key & audit trail
            </span>
            <button
              onClick={() => setShowAddMemberModal(true)}
              className="px-3.5 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>Add Team Member</span>
            </button>
          </div>
        </div>

        {/* Team Members List */}
        <div className="overflow-x-auto text-xs">
          <table className="w-full text-left border-collapse">
            <thead className="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] tracking-wider">
              <tr>
                <th className="p-3">Team Member</th>
                <th className="p-3">Role & Access</th>
                <th className="p-3">Department</th>
                <th className="p-3">Phone</th>
                <th className="p-3 text-center">Work Logs</th>
                <th className="p-3 text-center">Status</th>
                <th className="p-3 text-right">Profile & Audit</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {teamMembers.map((member) => {
                const logsCount = member.workLog?.length || 0;
                const memberHandle = member.username || member.email.split('@')[0];
                return (
                  <tr
                    key={member.id}
                    onClick={() => handleOpenProfile(member)}
                    className="hover:bg-emerald-50/40 transition-colors cursor-pointer group"
                  >
                    {/* User Profile */}
                    <td className="p-3">
                      <div className="flex items-center gap-2.5">
                        <img
                          src={
                            member.avatar ||
                            'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80'
                          }
                          alt={member.name}
                          className="w-8 h-8 rounded-full object-cover border border-gray-200"
                        />
                        <div>
                          <div className="flex items-center gap-1.5">
                            <p className="font-bold text-gray-900 group-hover:text-brand-green transition-colors">
                              {member.name}
                            </p>
                            <span className="font-mono text-[9px] bg-slate-100 text-slate-700 px-1.5 py-0.2 rounded font-semibold">
                              @{memberHandle}
                            </span>
                          </div>
                          <p className="text-[11px] text-gray-500 font-mono">{member.email}</p>
                        </div>
                      </div>
                    </td>

                    {/* Role */}
                    <td className="p-3 whitespace-nowrap">
                      <span
                        className={`inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize ${getRoleBadgeColor(
                          member.role
                        )}`}
                      >
                        {member.role.replace('_', ' ')}
                      </span>
                    </td>

                    {/* Department */}
                    <td className="p-3 text-gray-700 font-medium whitespace-nowrap">
                      {member.department || 'Operations'}
                    </td>

                    {/* Phone */}
                    <td className="p-3 font-mono text-[11px] text-gray-600 whitespace-nowrap">
                      {member.phone}
                    </td>

                    {/* Work Logs Count */}
                    <td className="p-3 text-center whitespace-nowrap">
                      <span className="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-800">
                        {logsCount} {logsCount === 1 ? 'Task' : 'Tasks'}
                      </span>
                    </td>

                    {/* Status */}
                    <td className="p-3 text-center whitespace-nowrap">
                      <span
                        className={`inline-block px-2 py-0.5 rounded-full text-[10px] font-bold ${
                          member.status === 'active'
                            ? 'bg-emerald-100 text-emerald-800'
                            : 'bg-red-100 text-red-800'
                        }`}
                      >
                        {member.status === 'active' ? 'Active' : 'Suspended'}
                      </span>
                    </td>

                    {/* Actions */}
                    <td className="p-3 text-right whitespace-nowrap">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          handleOpenProfile(member);
                        }}
                        className="px-2.5 py-1 bg-brand-green/10 hover:bg-brand-green text-brand-green hover:text-white rounded-lg text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-1"
                      >
                        <span>View Work Log</span>
                        <ExternalLink className="w-3 h-3" />
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* Team Member Full Profile & Interactive Work Audit Modal */}
      {selectedUser && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
          <div className="bg-white rounded-3xl p-6 max-w-2xl w-full space-y-5 shadow-2xl animate-in zoom-in-95 duration-150 my-6">
            {/* Modal Header */}
            <div className="flex items-start justify-between pb-4 border-b border-gray-100">
              <div className="flex items-center gap-3">
                <img
                  src={
                    selectedUser.avatar ||
                    'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80'
                  }
                  alt={selectedUser.name}
                  className="w-14 h-14 rounded-2xl object-cover border-2 border-brand-green shadow-xs"
                />
                <div>
                  <div className="flex items-center gap-2">
                    <h3 className="font-bold text-lg text-gray-900 font-display">
                      {selectedUser.name}
                    </h3>
                    <span
                      className={`px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize ${getRoleBadgeColor(
                        selectedUser.role
                      )}`}
                    >
                      {selectedUser.role.replace('_', ' ')}
                    </span>
                  </div>
                  <p className="text-xs text-brand-green font-medium mt-0.5">
                    {selectedUser.department || 'Operations Team'} • Joined {selectedUser.joinedDate}
                  </p>
                  <div className="flex items-center gap-3 text-xs text-gray-500 mt-1">
                    <span className="flex items-center gap-1">
                      <Mail className="w-3 h-3" /> {selectedUser.email}
                    </span>
                    <span className="flex items-center gap-1">
                      <Phone className="w-3 h-3" /> {selectedUser.phone}
                    </span>
                  </div>
                </div>
              </div>

              <button
                onClick={() => setSelectedUser(null)}
                className="text-gray-400 hover:text-gray-600 text-sm font-bold p-1 cursor-pointer"
              >
                ✕
              </button>
            </div>

            {/* Login Credentials & Clearance */}
            <div className="p-3.5 bg-slate-900 text-white rounded-2xl border border-slate-800 space-y-2">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-1.5 font-bold text-xs text-amber-300">
                  <ShieldCheck className="w-4 h-4 text-amber-400" />
                  <span>Portal Login Credentials & Clearance</span>
                </div>
                <span className="font-mono text-[10px] text-emerald-400">● 256-Bit Edge Guarded</span>
              </div>
              
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1">
                <div className="p-2.5 bg-slate-950 rounded-xl border border-slate-800">
                  <span className="text-[10px] text-slate-400 block font-medium">Username / User ID:</span>
                  <span className="font-mono font-bold text-white text-xs">@{selectedUser.username || selectedUser.email.split('@')[0]}</span>
                </div>

                <div className="p-2.5 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between">
                  <div>
                    <span className="text-[10px] text-slate-400 block font-medium">Password Security:</span>
                    <span className="font-mono text-emerald-400 text-xs font-semibold flex items-center gap-1 mt-0.5">
                      <ShieldCheck className="w-3.5 h-3.5" />
                      <span>PBKDF2-SHA256 (Protected)</span>
                    </span>
                  </div>
                  <button
                    type="button"
                    onClick={() => {
                      setResettingMember(selectedUser);
                      setNewMemberResetPass(`Ggh@${Math.floor(1000 + Math.random() * 9000)}`);
                    }}
                    className="px-2.5 py-1 bg-amber-400 hover:bg-amber-300 text-slate-950 rounded-lg text-[10px] font-bold cursor-pointer transition-colors"
                  >
                    Reset Password
                  </button>
                </div>
              </div>
            </div>

            {/* Quick Staff Controls: Role Switch & Status */}
            <div className="bg-gray-50 p-3 rounded-xl border border-gray-200 flex flex-wrap items-center justify-between gap-3 text-xs">
              <div className="flex items-center gap-2">
                <span className="font-semibold text-gray-700">Assigned Role:</span>
                <select
                  value={selectedUser.role}
                  onChange={(e) => {
                    const newRole = e.target.value as UserRole;
                    updateUserRole(selectedUser.id, newRole);
                    setSelectedUser({ ...selectedUser, role: newRole });
                  }}
                  className="px-2.5 py-1 bg-white border border-gray-300 rounded-lg font-bold text-brand-green"
                >
                  <option value="super_admin">Super Admin</option>
                  <option value="admin">Operations Admin</option>
                  <option value="marketer">Marketing Manager</option>
                  <option value="content_writer">Content Writer</option>
                  <option value="support">Customer Support</option>
                  <option value="vendor">Vendor Lead</option>
                </select>
              </div>

              <div className="flex items-center gap-2">
                <span className="font-semibold text-gray-700">Account Status:</span>
                <button
                  onClick={() => {
                    toggleUserStatus(selectedUser.id);
                    setSelectedUser({
                      ...selectedUser,
                      status: selectedUser.status === 'active' ? 'suspended' : 'active'
                    });
                  }}
                  className={`px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                    selectedUser.status === 'active'
                      ? 'bg-emerald-600 text-white'
                      : 'bg-red-600 text-white'
                  }`}
                >
                  {selectedUser.status === 'active' ? 'Active (Enabled)' : 'Suspended (Disabled)'}
                </button>
              </div>
            </div>

            {/* Work Activity & Audit Trail Header */}
            <div className="flex items-center justify-between pt-1">
              <div className="flex items-center gap-2">
                <Activity className="w-4 h-4 text-brand-green" />
                <h4 className="font-bold text-sm text-gray-900 font-display">
                  Audited Work History & Operational Log ({selectedUser.workLog?.length || 0})
                </h4>
              </div>

              <button
                onClick={() => setShowAddLogForm(!showAddLogForm)}
                className="px-3 py-1.5 bg-brand-green hover:bg-brand-green-dark text-white rounded-lg text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>{showAddLogForm ? 'Close Form' : 'Log Task / Activity'}</span>
              </button>
            </div>

            {/* Log New Task Form */}
            {showAddLogForm && (
              <form
                onSubmit={handleAddWorkLog}
                className="bg-emerald-50/60 border border-emerald-200 p-3.5 rounded-xl space-y-2.5 text-xs animate-in fade-in duration-150"
              >
                <div className="font-bold text-emerald-950 flex items-center gap-1.5">
                  <FileText className="w-3.5 h-3.5 text-emerald-700" />
                  <span>Record New Task Execution to Audit Log</span>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                  <div className="sm:col-span-2">
                    <input
                      type="text"
                      required
                      placeholder="Task Title (e.g. Bulk Inventory Restock, DLT Campaign, Ticket Triage...)"
                      value={newLogTitle}
                      onChange={(e) => setNewLogTitle(e.target.value)}
                      className="w-full p-2 border border-gray-300 rounded-lg bg-white"
                    />
                  </div>

                  <div>
                    <select
                      value={newLogCategory}
                      onChange={(e) => setNewLogCategory(e.target.value as any)}
                      className="w-full p-2 border border-gray-300 rounded-lg bg-white"
                    >
                      <option value="Operations">Operations</option>
                      <option value="Inventory">Inventory</option>
                      <option value="Marketing">Marketing</option>
                      <option value="Editorial">Editorial</option>
                      <option value="Support">Support</option>
                      <option value="Security">Security</option>
                    </select>
                  </div>
                </div>

                <div>
                  <textarea
                    rows={2}
                    placeholder="Execution details, metric impact, or approval reference..."
                    value={newLogDetails}
                    onChange={(e) => setNewLogDetails(e.target.value)}
                    className="w-full p-2 border border-gray-300 rounded-lg bg-white text-xs"
                  ></textarea>
                </div>

                <div className="flex justify-end gap-2">
                  <button
                    type="submit"
                    className="px-3 py-1.5 bg-brand-green text-white font-bold rounded-lg cursor-pointer"
                  >
                    Save to Audit Trail
                  </button>
                  <button
                    type="button"
                    onClick={() => setShowAddLogForm(false)}
                    className="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 cursor-pointer"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            )}

            {/* Interactive Timeline of Work Logs */}
            <div className="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
              {(!selectedUser.workLog || selectedUser.workLog.length === 0) ? (
                <div className="text-center py-6 text-gray-400 text-xs">
                  No work tasks logged yet for this team member.
                </div>
              ) : (
                selectedUser.workLog.map((log) => (
                  <div
                    key={log.id}
                    className="p-3 rounded-xl border border-gray-200 bg-white hover:border-gray-300 transition-all text-xs space-y-1"
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <span className="font-bold text-gray-900">{log.action}</span>
                        <span className="px-1.5 py-0.2 rounded text-[9px] font-bold bg-gray-100 text-gray-700">
                          {log.category || log.module}
                        </span>
                      </div>
                      <span className="font-mono text-[10px] text-gray-400 font-medium">
                        {log.timestamp}
                      </span>
                    </div>
                    <p className="text-gray-600 text-[11px] leading-relaxed">{log.details}</p>
                    <div className="flex items-center gap-1 text-[10px] text-emerald-700 font-semibold pt-1">
                      <CheckCircle2 className="w-3 h-3" />
                      <span>Verified & Executed</span>
                    </div>
                  </div>
                ))
              )}
            </div>

            <div className="pt-2 border-t border-gray-100 flex justify-end">
              <button
                type="button"
                onClick={() => setSelectedUser(null)}
                className="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-xl transition-colors cursor-pointer text-xs"
              >
                Close Profile
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Add Team Member Modal */}
      {showAddMemberModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-md w-full space-y-4 shadow-2xl animate-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between border-b border-gray-100 pb-3">
              <div>
                <h4 className="font-bold text-base text-gray-900 font-display">Add Official Team Member</h4>
                <p className="text-[11px] text-gray-500">Configure team access credentials and department assignment.</p>
              </div>
              <button
                onClick={() => setShowAddMemberModal(false)}
                className="p-1 text-gray-400 hover:text-gray-700 rounded-lg cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateTeamMember} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Full Name *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Vikramaditya Singh"
                  value={newMemberName}
                  onChange={(e) => {
                    setNewMemberName(e.target.value);
                    if (!newMemberUsername) {
                      setNewMemberUsername(e.target.value.toLowerCase().replace(/[^a-zA-Z0-9_]/g, '_'));
                    }
                  }}
                  className="w-full p-2.5 border border-gray-300 rounded-xl"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">
                    Username (for Login) *
                  </label>
                  <div className="relative">
                    <span className="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 font-mono">@</span>
                    <input
                      type="text"
                      required
                      autoCapitalize="none"
                      autoCorrect="off"
                      spellCheck={false}
                      placeholder="vikram_ops"
                      value={newMemberUsername}
                      onChange={(e) => setNewMemberUsername(e.target.value.toLowerCase().replace(/[^a-zA-Z0-9_]/g, ''))}
                      className="w-full pl-7 pr-2.5 py-2.5 border border-gray-300 rounded-xl font-mono text-xs"
                    />
                  </div>
                </div>

                <div>
                  <div className="flex items-center justify-between mb-1">
                    <label className="font-semibold text-gray-700">Password *</label>
                    <button
                      type="button"
                      onClick={() => setNewMemberPassword(`Admin@${Math.floor(1000 + Math.random() * 9000)}`)}
                      className="text-[10px] text-brand-green hover:underline cursor-pointer font-semibold"
                    >
                      Generate Key
                    </button>
                  </div>
                  <div className="relative">
                    <input
                      type={showMemberPasswordInForm ? 'text' : 'password'}
                      required
                      autoCapitalize="none"
                      autoCorrect="off"
                      spellCheck={false}
                      placeholder="Admin@2026"
                      value={newMemberPassword}
                      onChange={(e) => setNewMemberPassword(e.target.value)}
                      className="w-full pl-2.5 pr-8 py-2.5 border border-gray-300 rounded-xl font-mono text-xs"
                    />
                    <button
                      type="button"
                      onClick={() => setShowMemberPasswordInForm(!showMemberPasswordInForm)}
                      className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 p-0.5"
                    >
                      {showMemberPasswordInForm ? <EyeOff className="w-3.5 h-3.5" /> : <Eye className="w-3.5 h-3.5" />}
                    </button>
                  </div>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Official Email *</label>
                  <input
                    type="email"
                    required
                    autoCapitalize="none"
                    autoCorrect="off"
                    spellCheck={false}
                    placeholder="vikram@ssindustries.in"
                    value={newMemberEmail}
                    onChange={(e) => setNewMemberEmail(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Phone Number</label>
                  <input
                    type="tel"
                    placeholder="+91 98765 43210"
                    value={newMemberPhone}
                    onChange={(e) => setNewMemberPhone(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Department</label>
                  <input
                    type="text"
                    placeholder="e.g. Regional Logistics & QC"
                    value={newMemberDepartment}
                    onChange={(e) => setNewMemberDepartment(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-gray-700 mb-1">Assigned Role *</label>
                  <select
                    value={newMemberRole}
                    onChange={(e) => setNewMemberRole(e.target.value as UserRole)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-semibold bg-white"
                  >
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Operations Admin</option>
                    <option value="marketer">Marketing Manager</option>
                    <option value="content_writer">Content Writer</option>
                    <option value="support">Customer Support</option>
                    <option value="vendor">Vendor Lead</option>
                  </select>
                </div>
              </div>

              <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-emerald-900 flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 text-brand-green shrink-0" />
                <span>The staff member can authenticate immediately with this username/password across all clearance gates.</span>
              </div>

              <div className="flex gap-2 pt-2">
                <button
                  type="submit"
                  className="flex-1 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                >
                  Confirm & Provision Member
                </button>
                <button
                  type="button"
                  onClick={() => setShowAddMemberModal(false)}
                  className="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 cursor-pointer"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Reset Member Password Modal */}
      {resettingMember && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-md w-full space-y-4 shadow-2xl animate-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between border-b border-gray-100 pb-3">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center">
                  <Key className="w-4 h-4" />
                </div>
                <div>
                  <h4 className="font-bold text-base text-gray-900 font-display">Reset Staff Credentials</h4>
                  <p className="text-[11px] text-gray-500">Re-key with PBKDF2-HMAC-SHA256 encryption</p>
                </div>
              </div>
              <button
                onClick={() => setResettingMember(null)}
                className="p-1 text-gray-400 hover:text-gray-700 rounded-lg cursor-pointer"
              >
                ✕
              </button>
            </div>

            {resetMemberSuccess ? (
              <div className="p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl text-xs font-semibold flex items-center gap-2">
                <Check className="w-4 h-4 text-emerald-600" />
                <span>{resetMemberSuccess}</span>
              </div>
            ) : (
              <form onSubmit={handleResetMemberPass} className="space-y-3 text-xs">
                <div className="p-2.5 bg-slate-900 text-white rounded-xl text-xs space-y-1">
                  <div className="flex items-center justify-between">
                    <span className="text-slate-400">Team Member:</span>
                    <span className="font-bold text-amber-300">@{resettingMember.username || resettingMember.name}</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-slate-400">Department:</span>
                    <span className="text-slate-300 text-[11px]">{resettingMember.department || 'Operations'}</span>
                  </div>
                </div>

                <div>
                  <div className="flex items-center justify-between mb-1">
                    <label className="font-semibold text-gray-700">New Password *</label>
                    <button
                      type="button"
                      onClick={() => setNewMemberResetPass(`Ggh@${Math.floor(1000 + Math.random() * 9000)}`)}
                      className="text-[10px] text-brand-green hover:underline cursor-pointer font-semibold"
                    >
                      Auto-Generate Secure Key
                    </button>
                  </div>
                  <input
                    type="text"
                    required
                    placeholder="Enter new password"
                    value={newMemberResetPass}
                    onChange={(e) => setNewMemberResetPass(e.target.value)}
                    className="w-full p-2.5 border border-gray-300 rounded-xl font-mono text-xs"
                  />
                  <p className="text-[10px] text-gray-400 mt-1">
                    Will be securely hashed with PBKDF2 (100,000 rounds) before storing.
                  </p>
                </div>

                <div className="flex items-center justify-end gap-2 pt-2">
                  <button
                    type="button"
                    onClick={() => setResettingMember(null)}
                    className="px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 cursor-pointer"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-2 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold cursor-pointer transition-colors shadow-xs"
                  >
                    Save & Encrypt Key
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      )}

    </div>
  );
};
