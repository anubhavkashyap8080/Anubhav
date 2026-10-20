import React from 'react';
import { useSiteConfig } from '../../context/SiteConfigContext';
import { Sparkles } from 'lucide-react';
import { useDemoModal } from '../../context/DemoModalContext';

export const AnnouncementBar: React.FC = () => {
  const { headerConfig } = useSiteConfig();
  const { showDemoNotice } = useDemoModal();

  if (!headerConfig.showAnnouncement) return null;

  return (
    <div className="bg-brand-green text-white text-xs font-medium py-1.5 sm:py-2 px-3 sm:px-4 shadow-sm border-b border-brand-green-dark w-full max-w-full overflow-hidden">
      <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1.5 sm:gap-2 text-center sm:text-left w-full min-w-0">
        <div className="flex items-center justify-center gap-1.5 w-full sm:w-auto min-w-0 overflow-hidden px-1">
          <Sparkles className="w-3.5 h-3.5 text-amber-300 shrink-0 animate-soft-pulse" />
          <span className="truncate block max-w-full text-[11px] sm:text-xs">
            {headerConfig.announcementText}
          </span>
        </div>

        <div className="flex items-center justify-center gap-3 text-[10px] sm:text-[11px] shrink-0 text-emerald-100">
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
