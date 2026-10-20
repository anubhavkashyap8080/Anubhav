import React, { useEffect } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { AnnouncementBar } from './AnnouncementBar';
import { Header } from './Header';
import { CategoryNav } from './CategoryNav';
import { Footer } from './Footer';
import { DemoModal } from '../common/DemoModal';
import { Sparkles, Info } from 'lucide-react';

export const Layout: React.FC = () => {
  const { pathname } = useLocation();

  // Scroll to top upon page navigation
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

  return (
    <div className="min-h-screen flex flex-col bg-[#faf8f5]">
      {/* Top Prototype Indicator Ribbon */}
      <div className="bg-amber-500 text-gray-900 text-[11px] font-semibold py-1 px-4 text-center border-b border-amber-600 flex items-center justify-center gap-1.5 shadow-2xs">
        <Sparkles className="w-3.5 h-3.5 shrink-0" />
        <span>GanvGaliHut Phase 1 Frontend Prototype • Realistic Mock Data & Centralized Demo Notices</span>
      </div>

      {/* Global Announcement */}
      <AnnouncementBar />

      {/* Main Brand Header */}
      <Header />

      {/* Category Navigation Bar */}
      <CategoryNav />

      {/* Page Content */}
      <main className="flex-1">
        <Outlet />
      </main>

      {/* Complete Footer */}
      <Footer />

      {/* Central Reusable Demo Modal */}
      <DemoModal />
    </div>
  );
};
