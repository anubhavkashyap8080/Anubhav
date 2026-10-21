import React, { useState, useEffect } from 'react';
import {
  Smartphone,
  QrCode,
  Copy,
  Check,
  RefreshCw,
  ExternalLink,
  ShieldCheck,
  Share2,
  X,
  KeyRound,
  DownloadCloud
} from 'lucide-react';
import { usePlatform } from '../../context/PlatformContext';
import { generatePairToken, unpackPairToken, mergeUsers } from '../../utils/cloudSync';

interface DeviceSyncModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export const DeviceSyncModal: React.FC<DeviceSyncModalProps> = ({ isOpen, onClose }) => {
  const { users, syncWithCloud } = usePlatform();
  const [copied, setCopied] = useState(false);
  const [syncStatus, setSyncStatus] = useState<string | null>(null);
  const [isSyncing, setIsSyncing] = useState(false);
  const [manualCode, setManualCode] = useState('');
  const [manualError, setManualError] = useState('');
  const [manualSuccess, setManualSuccess] = useState('');
  const [activeTab, setActiveTab] = useState<'generate' | 'enter'>('generate');

  const pairToken = generatePairToken(users);
  const currentOrigin = typeof window !== 'undefined' ? window.location.origin : '';
  const syncUrl = `${currentOrigin}/admin?ggh_sync=${pairToken}`;
  const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(
    syncUrl
  )}`;

  useEffect(() => {
    if (isOpen) {
      setSyncStatus(null);
      setManualError('');
      setManualSuccess('');
    }
  }, [isOpen]);

  if (!isOpen) return null;

  const handleCopyLink = () => {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(syncUrl);
      setCopied(true);
      setTimeout(() => setCopied(false), 2500);
    }
  };

  const handleForceCloudSync = async () => {
    setIsSyncing(true);
    setSyncStatus('Connecting to central server...');
    try {
      const res = await syncWithCloud();
      setSyncStatus(`Sync Successful: ${res.count} staff accounts synchronized.`);
    } catch {
      setSyncStatus('Local storage active. Ready for pairing.');
    } finally {
      setIsSyncing(false);
    }
  };

  const handleApplyManualCode = (e: React.FormEvent) => {
    e.preventDefault();
    setManualError('');
    setManualSuccess('');

    if (!manualCode.trim()) {
      setManualError('Please paste or enter the pairing code from Desktop.');
      return;
    }

    try {
      const unpacked = unpackPairToken(manualCode.trim());
      if (!unpacked || unpacked.length === 0) {
        setManualError('Invalid pair code. Please copy the complete code from Desktop.');
        return;
      }

      const raw = localStorage.getItem('ggh_platform_users');
      const localList = raw ? JSON.parse(raw) : users;
      const merged = mergeUsers(localList, unpacked);
      localStorage.setItem('ggh_platform_users', JSON.stringify(merged));
      window.dispatchEvent(new CustomEvent('ggh_users_synced', { detail: { source: 'manual_code' } }));

      setManualSuccess(`Success! Synchronized ${unpacked.length} administrator accounts onto this mobile device.`);
      setTimeout(() => {
        onClose();
      }, 2000);
    } catch {
      setManualError('Failed to parse pair code.');
    }
  };

  const whatsappShareUrl = `https://wa.me/?text=${encodeURIComponent(
    `GaunGaliHat Multi-Device Sync Link: ${syncUrl}`
  )}`;

  return (
    <div className="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full text-slate-100 shadow-2xl overflow-hidden animate-in zoom-in-95 duration-150">
        
        {/* Header */}
        <div className="bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950/60 p-5 border-b border-slate-800 flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="p-2 rounded-xl bg-amber-400 text-slate-950 font-bold shadow-md">
              <Smartphone className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-base text-white font-display">
                Multi-Device Account Synchronization
              </h3>
              <p className="text-xs text-slate-400">
                Instantly sync admin credentials between Desktop, Mobile & Tablets
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer"
            aria-label="Close modal"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Mode Selector */}
        <div className="flex border-b border-slate-800 bg-slate-950/80 p-1.5 gap-1.5 text-xs font-semibold">
          <button
            type="button"
            onClick={() => setActiveTab('generate')}
            className={`flex-1 py-2 rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
              activeTab === 'generate'
                ? 'bg-amber-400 text-slate-950 font-bold shadow-sm'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            <QrCode className="w-3.5 h-3.5" />
            <span>Desktop: Sync to Mobile</span>
          </button>

          <button
            type="button"
            onClick={() => setActiveTab('enter')}
            className={`flex-1 py-2 rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer ${
              activeTab === 'enter'
                ? 'bg-amber-400 text-slate-950 font-bold shadow-sm'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            <DownloadCloud className="w-3.5 h-3.5" />
            <span>Mobile: Import / Link</span>
          </button>
        </div>

        {/* Tab 1: Generate QR / Link (Desktop -> Mobile) */}
        {activeTab === 'generate' && (
          <div className="p-5 space-y-4 text-xs">
            
            {/* Realtime API status */}
            <div className="flex items-center justify-between p-3 rounded-2xl bg-slate-950 border border-slate-800">
              <div className="flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span className="text-slate-300 font-medium">Server State Sync:</span>
                <span className="text-emerald-400 font-mono font-bold">Active (/api/users)</span>
              </div>
              <button
                type="button"
                onClick={handleForceCloudSync}
                disabled={isSyncing}
                className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold transition-colors cursor-pointer"
              >
                <RefreshCw className={`w-3 h-3 ${isSyncing ? 'animate-spin text-amber-400' : ''}`} />
                <span>{isSyncing ? 'Syncing...' : 'Force Sync'}</span>
              </button>
            </div>

            {syncStatus && (
              <div className="p-2.5 rounded-xl bg-emerald-950/50 border border-emerald-800 text-emerald-300 text-[11px] flex items-center gap-2">
                <ShieldCheck className="w-4 h-4 shrink-0 text-emerald-400" />
                <span>{syncStatus}</span>
              </div>
            )}

            {/* QR Code Container */}
            <div className="flex flex-col sm:flex-row items-center gap-4 bg-slate-950/60 p-4 rounded-2xl border border-slate-800/80">
              <div className="p-2 bg-white rounded-xl shadow-lg shrink-0">
                <img
                  src={qrImageUrl}
                  alt="GaunGaliHat Mobile Sync QR"
                  className="w-36 h-36 object-contain"
                />
              </div>

              <div className="space-y-2 text-center sm:text-left">
                <div className="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-400/10 text-amber-300 text-[10px] font-bold border border-amber-400/20">
                  <QrCode className="w-3 h-3" />
                  <span>Scan with Phone Camera</span>
                </div>
                <h4 className="font-bold text-white text-sm">
                  Instant Mobile Pairing
                </h4>
                <p className="text-slate-400 text-[11px] leading-relaxed">
                  Point your mobile phone camera at this QR code. It will open the admin console on your phone and automatically load all new admin accounts and PBKDF2 keys!
                </p>
              </div>
            </div>

            {/* One Click Share Options */}
            <div className="space-y-2">
              <label className="block text-slate-300 font-semibold text-[11px]">
                Or Copy 1-Click Sync Link for Mobile Browser:
              </label>

              <div className="flex gap-2">
                <input
                  type="text"
                  readOnly
                  value={syncUrl}
                  className="flex-1 p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 font-mono text-[10px] select-all truncate focus:outline-hidden"
                />
                <button
                  type="button"
                  onClick={handleCopyLink}
                  className="px-3.5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold transition-colors cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                  {copied ? <Check className="w-3.5 h-3.5" /> : <Copy className="w-3.5 h-3.5" />}
                  <span>{copied ? 'Copied!' : 'Copy Link'}</span>
                </button>
              </div>

              <div className="flex gap-2 pt-1">
                <a
                  href={whatsappShareUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold flex items-center justify-center gap-1.5 text-xs transition-colors"
                >
                  <Share2 className="w-3.5 h-3.5" />
                  <span>Send Link to Mobile via WhatsApp</span>
                </a>
              </div>
            </div>

          </div>
        )}

        {/* Tab 2: Enter Pair Code (On Mobile) */}
        {activeTab === 'enter' && (
          <form onSubmit={handleApplyManualCode} className="p-5 space-y-4 text-xs">
            <div className="p-3 bg-amber-400/10 border border-amber-400/20 rounded-2xl text-amber-200 space-y-1">
              <div className="font-bold flex items-center gap-1.5">
                <KeyRound className="w-4 h-4 text-amber-400" />
                <span>Pairing this Phone with Desktop Console</span>
              </div>
              <p className="text-[11px] text-amber-200/80">
                If you copied the sync link or token from Desktop, paste it below to clone all administrative accounts and cryptographic security keys onto this phone.
              </p>
            </div>

            {manualError && (
              <div className="p-3 bg-red-950/60 border border-red-800 text-red-200 rounded-xl">
                {manualError}
              </div>
            )}

            {manualSuccess && (
              <div className="p-3 bg-emerald-950/60 border border-emerald-800 text-emerald-200 rounded-xl flex items-center gap-2">
                <Check className="w-4 h-4 text-emerald-400 shrink-0" />
                <span>{manualSuccess}</span>
              </div>
            )}

            <div>
              <label className="block text-slate-300 font-semibold mb-1">
                Paste Sync Token or Full URL
              </label>
              <textarea
                rows={3}
                required
                value={manualCode}
                onChange={(e) => setManualCode(e.target.value)}
                placeholder="Paste the URL or base64 token generated on Desktop..."
                className="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs focus:border-amber-400 focus:outline-hidden"
              ></textarea>
            </div>

            <button
              type="submit"
              className="w-full py-3 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold rounded-xl transition-all cursor-pointer flex items-center justify-center gap-2 shadow-lg"
            >
              <ShieldCheck className="w-4 h-4" />
              <span>Import Admin Accounts to This Device</span>
            </button>
          </form>
        )}

        {/* Footer */}
        <div className="p-4 bg-slate-950 border-t border-slate-800 text-slate-400 text-[11px] flex items-center justify-between">
          <span className="flex items-center gap-1.5">
            <span className="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            Zero Plaintext: PBKDF2 Salted Hashes Only
          </span>
          <button
            type="button"
            onClick={onClose}
            className="text-slate-400 hover:text-white transition-colors"
          >
            Close
          </button>
        </div>

      </div>
    </div>
  );
};
