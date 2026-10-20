import React from 'react';
import { useSiteConfig } from '../../context/SiteConfigContext';
import { Sparkles, MapPin } from 'lucide-react';
import { useDemoModal } from '../../context/DemoModalContext';

export const AnnouncementBar: React.FC = () => {
  const { headerConfig } = useSiteConfig();
  const { showDemoNotice } = useDemoModal();

  if (!headerConfig.showAnnouncement) return null;

  return (
    <div className="bg-brand-green text-white text-xs font-medium py-2 px-4 shadow-sm border-b border-brand-green-dark">
      <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
        <div className="flex items-center justify-center gap-2 overflow-hidden">
          <Sparkles className="w-3.5 h-3.5 text-amber-300 shrink-0 animate-soft-pulse" />
          <span className="truncate">{headerConfig.announcementText}</span>
        </div>

        <div className="flex items-center gap-4 text-[11px] shrink-0 text-emerald-100">
          <button
            onClick={() => showDemoNotice('Language Selector', 'Multi-lingual support (Hindi, Marathi, Bengali, Tamil, etc.) will be active in the live release.')}
            className="hover:text-white transition-colors flex items-center gap-1 cursor-pointer"
          >
            <span>🇮🇳 हिन्दी / English</span>
          </button>
          <span className="text-emerald-300/40">|</span>
          <button
            onClick={() => showDemoNotice('Track Order', 'Live order tracking portal will connect to India Post & rural courier APIs in Phase 2.')}
            className="hover:text-white transition-colors cursor-pointer"
          >
            Track Order
          </button>
        </div>
      </div>
    </div>
  );
};
