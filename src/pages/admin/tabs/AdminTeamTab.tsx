import React, { useState } from 'react';
import { usePlatform } from '../../../context/PlatformContext';
import { useAuth } from '../../../context/AuthContext';
import { User, UserRole, UserWorkLog } from '../../../types';
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
  Lock
} from 'lucide-react';

export const AdminTeamTab: React.FC = () => {
  const { users, updateUserRole, toggleUserStatus } = usePlatform();
  const { currentUser } = useAuth();

  const [searchTerm, setSearchTerm] = useState('');
  const [selectedRoleFilter, setSelectedRoleFilter] = useState<string>('all');
  const [selectedUser, setSelectedUser] = useState<User | null>(null);

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

          <span className="text-xs text-gray-500 font-semibold">
            Click on any team member to inspect their full profile & work audit history
          </span>
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
                          <p className="font-bold text-gray-900 group-hover:text-brand-green transition-colors">
                            {member.name}
                          </p>
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
    </div>
  );
};
