import React, { useEffect } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { AnnouncementBar } from './AnnouncementBar';
import { Header } from './Header';
import { CategoryNav } from './CategoryNav';
import { Footer } from './Footer';
import { DemoModal } from '../common/DemoModal';
import { AuthModal } from '../auth/AuthModal';
import { Sparkles } from 'lucide-react';

export const Layout: React.FC = () => {
  const { pathname } = useLocation();

  // Scroll to top upon page navigation
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

  return (
    <div className="min-h-screen flex flex-col bg-[#faf8f5] w-full max-w-full overflow-x-hidden min-w-0">
      {/* Top Prototype Indicator Ribbon */}
      <div className="bg-amber-500 text-gray-900 text-[10px] sm:text-[11px] font-semibold py-1.5 px-3 text-center border-b border-amber-600 flex items-center justify-center gap-1.5 shadow-2xs w-full max-w-full overflow-hidden">
        <Sparkles className="w-3.5 h-3.5 shrink-0" />
        <span className="text-center leading-tight">
          GanvGaliHut Enterprise Platform • S S Industries • Hardoi & Sandila Industrial Area
        </span>
      </div>

      {/* Global Announcement */}
      <AnnouncementBar />

      {/* Main Brand Header */}
      <Header />

      {/* Category Navigation Bar */}
      <CategoryNav />

      {/* Page Content */}
      <main className="flex-1 w-full max-w-full overflow-x-hidden min-w-0">
        <Outlet />
      </main>

      {/* Complete Footer */}
      <Footer />

      {/* Central Reusable Demo Modal */}
      <DemoModal />

      {/* Social & Mobile Phone OTP Verification Modal */}
      <AuthModal />
    </div>
  );
};
