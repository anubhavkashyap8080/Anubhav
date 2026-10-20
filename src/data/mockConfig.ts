import {
  HeaderConfig,
  FooterConfig,
  SEOConfig,
  SecurityChecklist,
  FirebaseSettings,
  MapSettings,
  User,
  SupportTicket,
  EmailCampaign,
  SmsMessage
} from '../types';

export const initialHeaderConfig: HeaderConfig = {
  logoUrl: '/logo.png',
  brandName: 'GanvGaliHut',
  tagline: 'S S Industries Enterprise • Ganv se ghar tak',
  showAnnouncement: true,
  announcementText: '🌾 S S Industries Tools, Eco Jute Bags & Village Crafts: Direct from Rural Artisans | Free Delivery Over ₹499 | Cash on Delivery Available',
  showLocationSelector: true,
  defaultLocation: 'Hardoi / Sandila 241126',
  showSearch: true,
  showIcons: true,
  navItems: [
    { id: 'nav-1', label: 'All Products', href: '/products', enabled: true },
    { id: 'nav-2', label: 'Farming Tools', href: '/category/farming-traditional-tools', enabled: true },
    { id: 'nav-3', label: 'Workshop & Ironware', href: '/category/hardware-cottage-implements', enabled: true },
    { id: 'nav-4', label: 'Eco Jute Bags', href: '/category/jute-bags-cloth-jholas', enabled: true },
    { id: 'nav-5', label: 'Village Decor & Toys', href: '/category/village-home-decor-toys', enabled: true },
    { id: 'nav-6', label: 'Women SHG Crafts', href: '/category/handmade-by-rural-women', enabled: true },
    { id: 'nav-7', label: 'Rural Machinery', href: '/category/rural-utility-machinery', enabled: true },
    { id: 'nav-8', label: 'Nearby Artisans (Map)', href: '/nearby-artisans', enabled: true },
    { id: 'nav-9', label: 'Village Journal', href: '/blog', enabled: true },
  ],
  ctaLabel: 'Become a Seller',
  ctaHref: '/become-seller'
};

export const initialFooterConfig: FooterConfig = {
  companyName: 'S S Industries',
  description: 'GanvGaliHut is an enterprise initiative promoted by S S Industries to manufacture high-durability farming tools and directly connect rural Indian women self-help groups, generational craftspeople, and cottage workshops with customers across Bharat.',
  contactPhone: '+91 7897492100',
  altPhone1: '+91 9005756321',
  altPhone2: '+91 8303578806',
  contactEmail: 'namaste@ganvgalihat.com',
  contactAddress: 'Works / Factory: Madhoganj Baghauli Road, Newada, Hardoi, Uttar Pradesh - 241126',
  headOfficeAddress: 'Head Office: G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, Uttar Pradesh',
  socialLinks: {
    facebook: 'https://facebook.com/ganvgalihat',
    instagram: 'https://instagram.com/ganvgalihat',
    twitter: 'https://twitter.com/ganvgalihat',
    youtube: 'https://youtube.com/ganvgalihat',
    whatsapp: 'https://wa.me/917897492100',
    linkedin: 'https://linkedin.com/company/ss-industries-hardoi',
    showFacebook: true,
    showInstagram: true,
    showTwitter: true,
    showYoutube: true,
    showWhatsapp: true,
    showLinkedin: true
  },
  copyrightText: '© 2026 GanvGaliHut — S S Industries. All Rights Reserved. Engineered for Rural Crafts & Indian Farming.',
  showNewsletter: true,
  columns: [
    {
      title: 'Tools & Implements',
      links: [
        { label: 'Forged Desi Hansiya (Sickles)', href: '/category/farming-traditional-tools' },
        { label: 'Hardened Steel Khurpis', href: '/category/farming-traditional-tools' },
        { label: 'Heavy Agricultural Fawdas', href: '/category/farming-traditional-tools' },
        { label: 'Blacksmith Sledge Hammers', href: '/category/hardware-cottage-implements' },
        { label: 'Rotary Toka Chaff Blades', href: '/category/hardware-cottage-implements' },
        { label: 'Push Seed Dibbler Planters', href: '/category/rural-utility-machinery' }
      ]
    },
    {
      title: 'Rural Artisan Crafts',
      links: [
        { label: 'Heavy Golden Jute Totes', href: '/category/jute-bags-cloth-jholas' },
        { label: 'GI Channapatna Wooden Toys', href: '/category/village-home-decor-toys' },
        { label: 'Jaipur Natural Lac Bangles', href: '/category/handmade-by-rural-women' },
        { label: 'Varanasi Khadi Kurtas', href: '/category/handmade-apparel-textiles' },
        { label: 'Moradabad Temple Brass Bells', href: '/category/village-home-decor-toys' },
        { label: 'Nearby Artisan Map', href: '/nearby-artisans' }
      ]
    },
    {
      title: 'Customer & Operations',
      links: [
        { label: 'Track Order & Support', href: '/support' },
        { label: 'Help & Knowledgebase FAQ', href: '/faq' },
        { label: 'Contact Us', href: '/contact' },
        { label: 'Shipping & Delivery Policy', href: '/shipping-policy' },
        { label: 'Return & Exchange Policy', href: '/returns-policy' },
        { label: 'Privacy & Data Terms', href: '/privacy-policy' }
      ]
    },
    {
      title: 'Admin & Governance',
      links: [
        { label: 'Super Admin Dashboard', href: '/admin' },
        { label: 'User & Role Manager', href: '/admin?tab=users' },
        { label: 'Vendor KYC & Store Hub', href: '/admin?tab=vendors' },
        { label: 'Customer Ticket Center', href: '/admin?tab=tickets' },
        { label: 'Communications & Bulk SMS', href: '/admin?tab=communications' },
        { label: 'SEO & Search Console', href: '/admin?tab=seo' }
      ]
    }
  ]
};

export const initialSEOConfig: SEOConfig = {
  siteTitle: 'GanvGaliHut — S S Industries | Farming Tools & Rural Artisan Marketplace',
  metaDescription: 'Shop high-carbon steel agricultural tools, desi sickles, heavy fawdas forged by S S Industries in Hardoi, alongside eco jute bags, Channapatna wooden toys, and handlooms.',
  canonicalUrl: 'https://ganvgalihat.pages.dev',
  sitemapUrl: 'https://ganvgalihat.pages.dev/sitemap.xml',
  robotsTxt: `# Robots.txt for GanvGaliHut (S S Industries)
User-agent: *
Allow: /
Allow: /products/
Allow: /category/
Allow: /blog/
Allow: /seller/
Allow: /nearby-artisans/
Disallow: /admin/
Disallow: /account/
Disallow: /checkout/

Sitemap: https://ganvgalihat.pages.dev/sitemap.xml
`,
  googleSearchConsoleVerification: 'google-site-verification=GanvGaliHut-SSIndustries-Verification2026',
  googleAnalyticsId: 'G-7XYZ987654',
  googleTagManagerId: 'GTM-VILLAGE1',
  metaPixelId: '1234567890987654',
  blogSeoDefaults: {
    titleTemplate: '%s | GanvGaliHut Village Journal',
    defaultOgImage: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1200&q=80'
  },
  indexSearchEngines: true
};

export const initialFirebaseSettings: FirebaseSettings = {
  apiKey: 'AIzaSyD-demoKey-GanvGaliHutSSIndustries',
  authDomain: 'ganvgalihat-ssindustries.firebaseapp.com',
  projectId: 'ganvgalihat-ssindustries',
  storageBucket: 'ganvgalihat-ssindustries.appspot.com',
  messagingSenderId: '7897492100',
  appId: '1:7897492100:web:4f8e91a0c2',
  measurementId: 'G-7XYZ987654',
  enableSocialLogin: true,
  enablePhoneAuth: true
};

export const initialMapSettings: MapSettings = {
  provider: 'leaflet',
  googleMapsApiKey: '',
  defaultCenter: {
    lat: 27.1265,
    lng: 80.1450 // Hardoi / Sandila, UP
  },
  defaultZoom: 10,
  searchRadiusKm: 50
};

export const initialUsers: User[] = [
  {
    id: 'user-01',
    name: 'Anubhav Kashyap',
    email: 'anubhav@ssindustries.in',
    phone: '+91 7897492100',
    role: 'super_admin',
    avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Jan 15, 2026',
    lastLogin: 'Today, 2:15 PM'
  },
  {
    id: 'user-02',
    name: 'Sunil Sharma',
    email: 'sunil.ops@ssindustries.in',
    phone: '+91 9005756321',
    role: 'admin',
    avatar: 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Feb 02, 2026',
    lastLogin: 'Yesterday'
  },
  {
    id: 'user-03',
    name: 'Ramkishan Lohar',
    email: 'ramkishan@ganvgalihat.com',
    phone: '+91 8303578806',
    role: 'vendor',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Feb 18, 2026',
    vendorId: 'seller-3',
    ordersCount: 48
  },
  {
    id: 'user-04',
    name: 'Priya Verma',
    email: 'priya.support@ssindustries.in',
    phone: '+91 94512 88410',
    role: 'support',
    avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Mar 01, 2026',
    assignedTickets: 7
  },
  {
    id: 'user-05',
    name: 'Aarav Gupta',
    email: 'aarav.buyer@gmail.com',
    phone: '+91 98112 34567',
    role: 'customer',
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Apr 10, 2026',
    ordersCount: 5
  }
];

export const initialTickets: SupportTicket[] = [
  {
    id: 'tkt-101',
    ticketNumber: 'GGH-2026-881',
    userId: 'user-05',
    userName: 'Aarav Gupta',
    userEmail: 'aarav.buyer@gmail.com',
    userPhone: '+91 98112 34567',
    subject: 'Bulk inquiry for 50 Channapatna wooden stacking ring toys for school',
    department: 'Product Inquiry',
    priority: 'High',
    status: 'Open',
    createdAt: 'Today, 11:30 AM',
    updatedAt: 'Today, 12:45 PM',
    assignedAgent: 'Priya Verma',
    messages: [
      {
        id: 'msg-1',
        senderId: 'user-05',
        senderName: 'Aarav Gupta',
        senderRole: 'customer',
        message: 'Namaste, we run a Montessori pre-school in Lucknow and want to purchase 50 units of the Channapatna vegetable-dyed wooden ring toys. Can S S Industries provide a special cooperative discount and custom batch packing?',
        timestamp: 'Today, 11:30 AM'
      },
      {
        id: 'msg-2',
        senderId: 'user-04',
        senderName: 'Priya Verma',
        senderRole: 'support',
        message: 'Namaste Aarav ji! Yes, we offer institutional pricing for educational trusts. Our Sandila logistics team can pack and dispatch directly to Lucknow within 48 hours.',
        timestamp: 'Today, 12:45 PM'
      }
    ]
  },
  {
    id: 'tkt-102',
    ticketNumber: 'GGH-2026-742',
    userId: 'user-03',
    userName: 'Ramkishan Lohar',
    userEmail: 'ramkishan@ganvgalihat.com',
    userPhone: '+91 8303578806',
    subject: 'Request to update banking details for weekly payout batch',
    department: 'Payment & Payout',
    priority: 'Medium',
    status: 'In Progress',
    createdAt: 'Yesterday, 3:20 PM',
    updatedAt: 'Yesterday, 5:10 PM',
    assignedAgent: 'Sunil Sharma',
    messages: [
      {
        id: 'msg-3',
        senderId: 'user-03',
        senderName: 'Ramkishan Lohar',
        senderRole: 'vendor',
        message: 'Namaste team, I have opened a new current account in Bank of Baroda Baghauli branch for my tool forgery. Please update my IFSC code for this Friday\'s tool sales payout.',
        timestamp: 'Yesterday, 3:20 PM'
      }
    ]
  },
  {
    id: 'tkt-103',
    ticketNumber: 'GGH-2026-619',
    userId: 'user-06',
    userName: 'Kusum Devi (Awadh SHG)',
    userEmail: 'kusum.shg@gmail.com',
    userPhone: '+91 94512 88410',
    subject: 'New listing submission for braided sabai grass vegetable storage hampers',
    department: 'Vendor Verification',
    priority: 'Low',
    status: 'Resolved',
    createdAt: 'Oct 17, 2026',
    updatedAt: 'Oct 18, 2026',
    assignedAgent: 'Anubhav Kashyap',
    messages: [
      {
        id: 'msg-4',
        senderId: 'user-06',
        senderName: 'Kusum Devi',
        senderRole: 'vendor',
        message: 'Uploaded photographs and dimensions of our new sabai grass baskets crafted by 12 village artisans.',
        timestamp: 'Oct 17, 2026'
      },
      {
        id: 'msg-5',
        senderId: 'user-01',
        senderName: 'Anubhav Kashyap',
        senderRole: 'super_admin',
        message: 'Product batch reviewed and approved for live catalog under Eco Jute & Cloth Jholas. Quality test passed.',
        timestamp: 'Oct 18, 2026'
      }
    ]
  }
];

export const initialEmailCampaigns: EmailCampaign[] = [
  {
    id: 'camp-01',
    subject: 'S S Industries Harvest Special: Heavy Forged Desi Sickles & Khurpis Ready for Dispatch',
    senderName: 'GanvGaliHut Marketplace',
    senderEmail: 'namaste@ganvgalihat.com',
    recipientAudience: 'All Users',
    recipientCount: 2480,
    template: 'Promotion / Offer',
    body: 'Greetings from S S Industries Hardoi! Prepare for the upcoming rabi harvesting season with our high-carbon spring steel Hansiya and heavy weeding Khurpis, direct from our foundry.',
    sentDate: 'Oct 15, 2026',
    status: 'Sent',
    openRate: '46.8%'
  },
  {
    id: 'camp-02',
    subject: 'Welcome to GanvGaliHut: Direct Village Commerce from S S Industries',
    senderName: 'Anubhav Kashyap',
    senderEmail: 'anubhav@ssindustries.in',
    recipientAudience: 'All Customers',
    recipientCount: 1820,
    template: 'System Announcement',
    body: 'Thank you for joining our movement to empower rural Indian artisans and smallholders. Every tool and craft purchased supports real families in Hardoi and across Bharat.',
    sentDate: 'Oct 18, 2026',
    status: 'Sent',
    openRate: '58.2%'
  }
];

export const initialSmsMessages: SmsMessage[] = [
  {
    id: 'sms-01',
    recipientPhone: '+91 7897492100',
    audience: 'Individual Number',
    dltTemplateId: 'DLT-SSIND-OTP-100234',
    messageText: 'Your GanvGaliHut verification OTP is 482910. Valid for 10 minutes. S S Industries.',
    sentDate: 'Today, 1:40 PM',
    status: 'Delivered',
    type: 'Transactional OTP'
  },
  {
    id: 'sms-02',
    recipientPhone: '+91 9005756321',
    audience: 'All Vendors',
    dltTemplateId: 'DLT-SSIND-PAY-100589',
    messageText: 'GanvGaliHut Notice: Weekly vendor settlement of ₹18,400 has been credited to your bank account. S S Industries.',
    sentDate: 'Oct 18, 2026',
    status: 'Delivered',
    type: 'Dispatch Notification'
  },
  {
    id: 'sms-03',
    recipientPhone: '+91 8303578806',
    audience: 'All Customers',
    dltTemplateId: 'DLT-SSIND-DISP-100812',
    messageText: 'Your order #GGH-98124 for Desi Hansiya has been dispatched from S S Industries Hardoi Hub via India Post tracking #IN8923412. Track live on GanvGaliHut.',
    sentDate: 'Oct 19, 2026',
    status: 'Delivered',
    type: 'Dispatch Notification'
  }
];

export const initialSecurityChecklist: SecurityChecklist = {
  httpsConfigured: true,
  cspEnabled: true,
  hstsEnabled: true,
  secureCookies: true,
  rateLimitingConfigured: true,
  sqlInjectionSanitized: true,
  fileUploadRestricted: true,
  sessionTimeoutMinutes: 30,
  lastAuditedDate: 'October 20, 2026'
};
