import React, { useState } from 'react';
import {
  ChevronDown,
  HelpCircle,
  Sparkles,
  Search,
  MessageCircle
} from 'lucide-react';
import { useDemoModal } from '../context/DemoModalContext';

export const FAQ: React.FC = () => {
  const { showDemoNotice } = useDemoModal();
  const [openIndex, setOpenIndex] = useState<number | null>(0);
  const [searchQuery, setSearchQuery] = useState('');

  const faqs = [
    {
      q: 'How does GanvGaliHut verify that products are truly 100% authentic and pure?',
      a: 'We work directly with registered Self-Help Groups (SHGs), Gaushalas, and hereditary blacksmiths. We visit the villages, test sample batches for zero chemical adulteration or palm oil, and inspect production methods like wooden bilonas and stone chakkis firsthand before onboarding.'
    },
    {
      q: 'How does my purchase directly benefit rural women and artisans?',
      a: 'Unlike traditional trading networks where middlemen capture 70-80% of retail price, GanvGaliHut operates on a zero-exploitation direct model. Artisans set their own prices and receive 100% of fair product earnings directly into their individual or SHG bank accounts.'
    },
    {
      q: 'What are the delivery timelines from village workshops?',
      a: 'Because products are prepared and packed fresh in rural clusters, shipments typically arrive within 4 to 7 business days via India Post Registered Parcel and trusted regional courier networks. You receive SMS tracking at every transit hub.'
    },
    {
      q: 'What is the return policy if a handmade item arrives damaged?',
      a: 'We offer a hassle-free 7-day replacement or refund policy for damaged or transit-affected items. Simply take a quick photo of the parcel and report it through our customer support.'
    },
    {
      q: 'Is Cash on Delivery (COD) supported across all pincodes?',
      a: 'Yes, Cash on Delivery is supported across 19,000+ Indian postal codes through our India Post cash collection network.'
    },
    {
      q: 'How can our women self-help group or village cooperative sell on GanvGaliHut?',
      a: 'Any verified rural artisan, self-help group, or farmer cooperative can apply via our "Become a Seller" page or contact our local Village Mitra coordinator.'
    }
  ];

  const filteredFaqs = faqs.filter(f =>
    f.q.toLowerCase().includes(searchQuery.toLowerCase()) ||
    f.a.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
      
      {/* Header */}
      <div className="text-center space-y-3">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-brand-green text-xs font-semibold">
          <HelpCircle className="w-3.5 h-3.5" />
          <span>Frequently Asked Questions</span>
        </div>
        <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 font-display">
          Everything You Need to Know
        </h1>
        <p className="text-xs sm:text-sm text-gray-600 max-w-xl mx-auto">
          Clear answers regarding rural sourcing, bilona ghee purity, delivery logistics, and artisan compensation.
        </p>

        <div className="max-w-md mx-auto relative pt-2">
          <input
            type="text"
            placeholder="Search questions (e.g. delivery, purity, returns)..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-10 pr-4 py-2 text-xs bg-white border border-gray-300 rounded-full focus:ring-2 focus:ring-brand-green shadow-xs"
          />
          <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 mt-1" />
        </div>
      </div>

      {/* Accordion List */}
      <div className="space-y-3">
        {filteredFaqs.map((faq, idx) => {
          const isOpen = openIndex === idx;
          return (
            <div
              key={idx}
              className="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden transition-all"
            >
              <button
                onClick={() => setOpenIndex(isOpen ? null : idx)}
                className="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-gray-50/50"
              >
                <span className="font-bold text-sm text-gray-900 font-display">
                  {faq.q}
                </span>
                <ChevronDown
                  className={`w-4 h-4 text-brand-green transition-transform duration-200 shrink-0 ${
                    isOpen ? 'rotate-180' : ''
                  }`}
                />
              </button>

              {isOpen && (
                <div className="px-5 pb-5 text-xs text-gray-600 leading-relaxed border-t border-gray-50 pt-3">
                  {faq.a}
                </div>
              )}
            </div>
          );
        })}
      </div>

      {/* Still have questions */}
      <div className="p-6 bg-brand-cream rounded-3xl border border-amber-200 text-center space-y-3">
        <h3 className="font-bold text-base text-gray-900 font-display">Have a specific question not listed here?</h3>
        <p className="text-xs text-gray-600">Our customer support team is always available to help.</p>
        <button
          onClick={() => showDemoNotice('Support Chat', 'Omnichannel live chat & WhatsApp support triggers will be active in Phase 2.')}
          className="px-6 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer"
        >
          Chat with Support Mitra (Demo)
        </button>
      </div>

    </div>
  );
};
