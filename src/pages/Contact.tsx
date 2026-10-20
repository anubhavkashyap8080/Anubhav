import React, { useState } from 'react';
import {
  Phone,
  Mail,
  MapPin,
  Clock,
  Send,
  MessageSquare,
  Sparkles,
  Info
} from 'lucide-react';
import { useDemoModal } from '../context/DemoModalContext';
import { usePlatform } from '../context/PlatformContext';

export const Contact: React.FC = () => {
  const { showDemoNotice } = useDemoModal();
  const { createTicket } = usePlatform();
  const [submittedTicketId, setSubmittedTicketId] = useState<string | null>(null);

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    subject: 'Artisan Inquiry / Tool Customization',
    message: ''
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name || !formData.email || !formData.message) {
      showDemoNotice('Form Incomplete', 'Please fill in your name, email, and message inquiry.');
      return;
    }

    // Creates an actual live ticket inside PlatformContext!
    const newTicket = createTicket({
      subject: formData.subject,
      userName: formData.name,
      userEmail: formData.email,
      userPhone: formData.phone || '+91 7897492100',
      priority: 'Medium',
      department: 'Product Inquiry',
      message: formData.message
    });

    setSubmittedTicketId(newTicket.id);
    showDemoNotice(
      `Support Ticket Generated: ${newTicket.id}`,
      `Namaste ${formData.name}! Your inquiry has been routed to our Support Operations Center. Ticket #${newTicket.id} is now live and can be viewed or triaged in the Admin Ticket Manager.`
    );
    setFormData({ name: '', email: '', phone: '', subject: 'Artisan Inquiry / Tool Customization', message: '' });
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
      
      {/* Header */}
      <div className="text-center max-w-2xl mx-auto space-y-3">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-brand-green text-xs font-semibold">
          <Sparkles className="w-3.5 h-3.5" />
          <span>We'd Love to Hear From You</span>
        </div>
        <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 font-display">
          Contact GaunGaliHat
        </h1>
        <p className="text-xs sm:text-sm text-gray-600">
          Have a question about an artisan collective, our hand-forged tools, eco jute bags, or bulk order customization? Reach out anytime.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Contact Info (Left) */}
        <div className="lg:col-span-5 space-y-6">
          <div className="bg-brand-green text-white rounded-3xl p-6 sm:p-8 space-y-6 shadow-md">
            <div>
              <span className="text-[11px] font-semibold text-amber-300 uppercase tracking-wider block">
                Parent Enterprise
              </span>
              <h2 className="text-2xl font-bold font-display text-white mt-0.5">
                S S Industries
              </h2>
              <p className="text-xs text-emerald-100/90 mt-1">
                GaunGaliHat is an initiative by S S Industries to empower rural Indian artisans, SHGs, and farmers.
              </p>
            </div>

            <div className="space-y-4 text-xs">
              <div className="flex items-start gap-3">
                <MapPin className="w-5 h-5 text-amber-300 shrink-0 mt-0.5" />
                <div>
                  <p className="font-bold text-white">Works / Unit Address</p>
                  <p className="text-emerald-100/90 leading-relaxed">
                    Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126
                  </p>
                </div>
              </div>

              <div className="flex items-start gap-3">
                <MapPin className="w-5 h-5 text-brand-orange-light shrink-0 mt-0.5" />
                <div>
                  <p className="font-bold text-white">Head Office</p>
                  <p className="text-emerald-100/90 leading-relaxed">
                    G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, Uttar Pradesh
                  </p>
                </div>
              </div>

              <div className="flex items-start gap-3">
                <Phone className="w-5 h-5 text-amber-300 shrink-0 mt-0.5" />
                <div>
                  <p className="font-bold text-white mb-1">Direct Helplines & WhatsApp</p>
                  <div className="space-y-1 text-emerald-100">
                    <div>
                      <a href="tel:+917897492100" className="hover:text-amber-300 font-semibold underline underline-offset-2">
                        +91 7897492100
                      </a>
                    </div>
                    <div>
                      <a href="tel:+919005756321" className="hover:text-amber-300 font-semibold underline underline-offset-2">
                        +91 9005756321
                      </a>
                    </div>
                    <div>
                      <a href="tel:+918303578806" className="hover:text-amber-300 font-semibold underline underline-offset-2">
                        +91 8303578806
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div className="flex items-start gap-3">
                <Mail className="w-5 h-5 text-amber-300 shrink-0 mt-0.5" />
                <div>
                  <p className="font-bold text-white">Official Correspondence</p>
                  <p className="text-emerald-100/80">namaste@ganvgalihat.com</p>
                </div>
              </div>

              <div className="flex items-start gap-3">
                <Clock className="w-5 h-5 text-amber-300 shrink-0 mt-0.5" />
                <div>
                  <p className="font-bold text-white">Working Hours</p>
                  <p className="text-emerald-100/80">Monday – Saturday: 9:00 AM – 7:00 PM IST</p>
                </div>
              </div>
            </div>
          </div>

          {/* Support Guarantee Callout */}
          <div className="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-900 flex items-start gap-2.5">
            <Info className="w-4 h-4 text-brand-green shrink-0 mt-0.5" />
            <div>
              <span className="font-bold">Direct Support Assurance:</span> Our village artisan coordination team reviews every inquiry and responds within 24 business hours.
            </div>
          </div>
        </div>

        {/* Contact Form (Right) */}
        <div className="lg:col-span-7 bg-white rounded-3xl border border-gray-100 shadow-md p-6 sm:p-10">
          <form onSubmit={handleSubmit} className="space-y-4 text-xs">
            <h2 className="text-lg font-bold text-gray-900 font-display border-b border-gray-100 pb-3">
              Send us a Message
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Your Full Name *</label>
                <input
                  type="text"
                  placeholder="e.g. Ramesh Chandra"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg"
                  required
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Email Address *</label>
                <input
                  type="email"
                  placeholder="name@example.com"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg"
                  required
                />
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block font-semibold text-gray-700 mb-1">Contact Phone Number</label>
                <input
                  type="tel"
                  placeholder="+91 98765 00000"
                  value={formData.phone}
                  onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg"
                />
              </div>

              <div>
                <label className="block font-semibold text-gray-700 mb-1">Inquiry Subject *</label>
                <select
                  value={formData.subject}
                  onChange={(e) => setFormData({ ...formData, subject: e.target.value })}
                  className="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg"
                >
                  <option value="Artisan Inquiry / Order Support">Artisan Inquiry / Order Support</option>
                  <option value="Bulk Wedding / Corporate Gifting">Bulk Wedding / Corporate Gifting</option>
                  <option value="Become a Rural Seller / SHG Lead">Become a Rural Seller / SHG Lead</option>
                  <option value="Media & Press Inquiries">Media & Press Inquiries</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block font-semibold text-gray-700 mb-1">Your Message *</label>
              <textarea
                rows={5}
                placeholder="How can we assist you today? Tell us what you're looking for..."
                value={formData.message}
                onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                className="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl"
                required
              />
            </div>

            <div className="pt-2">
              <button
                type="submit"
                className="w-full sm:w-auto px-8 py-3.5 bg-brand-green hover:bg-brand-green-dark text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95"
              >
                <span>Send Message</span>
                <Send className="w-3.5 h-3.5" />
              </button>
            </div>
          </form>
        </div>

      </div>

    </div>
  );
};
