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
  SmsMessage,
  HeroFestivalConfig,
  AuditLogEntry
} from '../types';

export const initialHeaderConfig: HeaderConfig = {
  logoUrl: '/logo.png',
  brandName: 'GaunGaliHat',
  tagline: 'S S Industries Enterprise • Ganv se ghar tak',
  faviconUrl: '/logo.png',
  customHeadCode: '',
  customHeaderCode: '',
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
  description: 'GaunGaliHat is an enterprise initiative promoted by S S Industries to manufacture high-durability farming tools and directly connect rural Indian women self-help groups, generational craftspeople, and cottage workshops with customers across Bharat.',
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
  copyrightText: '© 2026 GaunGaliHat — S S Industries. All Rights Reserved. Engineered for Rural Crafts & Indian Farming.',
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
  siteTitle: 'GaunGaliHat — S S Industries | Farming Tools & Rural Artisan Marketplace',
  metaDescription: 'Shop high-carbon steel agricultural tools, desi sickles, heavy fawdas forged by S S Industries in Hardoi, alongside eco jute bags, Channapatna wooden toys, and handlooms.',
  canonicalUrl: 'https://ganvgalihat.pages.dev',
  sitemapUrl: 'https://ganvgalihat.pages.dev/sitemap.xml',
  robotsTxt: `# Robots.txt for GaunGaliHat (S S Industries)
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
  googleSearchConsoleVerification: 'google-site-verification=GaunGaliHat-SSIndustries-Verification2026',
  googleAnalyticsId: 'G-7XYZ987654',
  googleTagManagerId: 'GTM-VILLAGE1',
  metaPixelId: '1234567890987654',
  blogSeoDefaults: {
    titleTemplate: '%s | GaunGaliHat Village Journal',
    defaultOgImage: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1200&q=80'
  },
  indexSearchEngines: true
};

export const initialFirebaseSettings: FirebaseSettings = {
  apiKey: 'AIzaSyD-demoKey-GaunGaliHatSSIndustries',
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
    department: 'Executive Governance & Foundry Operations',
    avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Jan 15, 2026',
    lastLogin: 'Today, 2:15 PM',
    workLog: [
      { id: 'wl-101', action: 'Approved Vendor KYC & Onboarding', module: 'Vendor Hub', timestamp: 'Today, 1:45 PM', details: 'Verified GSTIN and bank passbook for Purvanchal Kauna Grass Weavers SHG.', status: 'completed' },
      { id: 'wl-102', action: 'Live Site Favicon & Header Update', module: 'Site Customizer', timestamp: 'Today, 11:20 AM', details: 'Synchronized brand logo asset and favicon across Cloudflare global edge.', status: 'completed' },
      { id: 'wl-103', action: 'Platform Commission Override', module: 'Financials', timestamp: 'Yesterday, 4:10 PM', details: 'Set S S Industries promotional zero commission on handloom textiles.', status: 'completed' },
      { id: 'wl-104', action: 'RBAC Role Assignment', module: 'Access Control', timestamp: 'Oct 18, 2026', details: 'Promoted Sunil Sharma to Operations Admin with dispatch privileges.', status: 'completed' }
    ]
  },
  {
    id: 'user-02',
    name: 'Sunil Sharma',
    email: 'sunil.ops@ssindustries.in',
    phone: '+91 9005756321',
    role: 'admin',
    department: 'Factory Dispatch & Regional Warehousing',
    avatar: 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Feb 02, 2026',
    lastLogin: 'Today, 1:10 PM',
    workLog: [
      { id: 'wl-201', action: 'Bulk Inventory Synchronization', module: 'Product Catalog', timestamp: 'Today, 12:30 PM', details: 'Restocked 150 Hansiya sickles and 80 Kulhadi axes from Madhoganj plant batch #B-902.', status: 'completed' },
      { id: 'wl-202', action: 'India Post Manifest Generation', module: 'Logistics', timestamp: 'Yesterday, 5:45 PM', details: 'Printed 42 dispatch barcode labels for rural orders across UP and Bihar.', status: 'completed' },
      { id: 'wl-203', action: 'Stock Level Threshold Alert', module: 'Warehouse', timestamp: 'Oct 19, 2026', details: 'Flagged low stock on Sugarcane Harvesting Sickles (12 units remaining).', status: 'completed' }
    ]
  },
  {
    id: 'user-06',
    name: 'Neha Singhania',
    email: 'neha.marketing@ssindustries.in',
    phone: '+91 91234 56780',
    role: 'marketer',
    department: 'Growth Marketing & SEO Strategy',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Mar 15, 2026',
    lastLogin: 'Today, 11:30 AM',
    workLog: [
      { id: 'wl-601', action: 'Executed DLT SMS Broadcast', module: 'Communications', timestamp: 'Today, 10:15 AM', details: 'Sent Rabi harvesting tools announcement to 2,400 registered farmers in Hardoi.', status: 'completed' },
      { id: 'wl-602', action: 'Updated Festival Hero Banner', module: 'Festival Customizer', timestamp: 'Yesterday, 3:30 PM', details: 'Configured Diwali Special theme with 20% discount code "FESTIVAL2026".', status: 'completed' },
      { id: 'wl-603', action: 'XML Sitemap Feed Regeneration', module: 'Technical SEO', timestamp: 'Oct 18, 2026', details: 'Submitted 24 product URLs and category index to Google Search Console.', status: 'completed' }
    ]
  },
  {
    id: 'user-07',
    name: 'Devendra Tripathi',
    email: 'devendra.writer@ssindustries.in',
    phone: '+91 93456 78901',
    role: 'content_writer',
    department: 'Rural Editorial & Artisan Journalism',
    avatar: 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Mar 20, 2026',
    lastLogin: 'Today, 9:45 AM',
    workLog: [
      { id: 'wl-701', action: 'Authored Village Craft Feature', module: 'Village Journal', timestamp: 'Today, 9:15 AM', details: 'Published in-depth documentary: "Ancestral Coal Forging in Madhoganj".', status: 'completed' },
      { id: 'wl-702', action: 'Product Copywriting Refinement', module: 'Catalog CMS', timestamp: 'Yesterday, 2:00 PM', details: 'Authored cultural craft origin specifications for Lucknowi Chikan Suit Sets.', status: 'completed' },
      { id: 'wl-703', action: 'Artisan Interview Archive', module: 'Media Library', timestamp: 'Oct 17, 2026', details: 'Uploaded 8 high-res photographs of pottery kiln firing in Gorakhpur.', status: 'completed' }
    ]
  },
  {
    id: 'user-04',
    name: 'Priya Verma',
    email: 'priya.support@ssindustries.in',
    phone: '+91 94512 88410',
    role: 'support',
    department: 'Customer Helpdesk & Order Resolution',
    avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Mar 01, 2026',
    assignedTickets: 7,
    workLog: [
      { id: 'wl-401', action: 'Resolved Customer Support Ticket', module: 'Helpdesk', timestamp: 'Today, 11:50 AM', details: 'Provided India Post speed-post tracking consignment #EU98214IN to Vikram Joshi.', status: 'completed' },
      { id: 'wl-402', action: 'Initiated Replacement Order', module: 'Returns & Claims', timestamp: 'Yesterday, 1:20 PM', details: 'Approved courier dispatch of new wooden handle for transit damaged khurpi.', status: 'completed' }
    ]
  },
  {
    id: 'user-08',
    name: 'Kavita Saini',
    email: 'kavita.catalog@ssindustries.in',
    phone: '+91 98765 43210',
    role: 'admin',
    department: 'Quality Inspection & SKU Curation',
    avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Apr 05, 2026',
    workLog: [
      { id: 'wl-801', action: 'Assigned Unique Product Codes', module: 'Catalog Management', timestamp: 'Today, 10:00 AM', details: 'Created SKU codes GGH-TL-101 through GGH-WM-002 with vendor mapping.', status: 'completed' },
      { id: 'wl-802', action: 'Inspected Pottery Batch Quality', module: 'Foundry Verification', timestamp: 'Yesterday, 4:00 PM', details: 'Verified lead-free earthen clay certification for Gorakhpur cooking handis.', status: 'completed' }
    ]
  },
  {
    id: 'user-03',
    name: 'Ramkishan Lohar',
    email: 'ramkishan@ganvgalihat.com',
    phone: '+91 8303578806',
    role: 'vendor',
    department: 'Blacksmith Artisan Partner (External)',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Feb 18, 2026',
    vendorId: 'seller-3',
    ordersCount: 48,
    workLog: [
      { id: 'wl-301', action: 'Dispatched Heavy Sledge Hammers', module: 'Vendor Portal', timestamp: 'Today, 8:30 AM', details: 'Handed over 12 tempered 3kg hammers to S S Industries transit van.', status: 'completed' }
    ]
  },
  {
    id: 'user-05',
    name: 'Aarav Gupta',
    email: 'aarav.buyer@gmail.com',
    phone: '+91 98112 34567',
    role: 'customer',
    department: 'Retail Customer (D2C)',
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80',
    status: 'active',
    joinedDate: 'Apr 10, 2026',
    ordersCount: 5,
    workLog: [
      { id: 'wl-501', action: 'Placed Order #ORD-9821', module: 'Storefront', timestamp: 'Today, 10:30 AM', details: 'Purchased Forged Carbon Steel Hansiya Sickle via UPI.', status: 'completed' }
    ]
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
    senderName: 'GaunGaliHat Marketplace',
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
    subject: 'Welcome to GaunGaliHat: Direct Village Commerce from S S Industries',
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
    messageText: 'Your GaunGaliHat verification OTP is 482910. Valid for 10 minutes. S S Industries.',
    sentDate: 'Today, 1:40 PM',
    status: 'Delivered',
    type: 'Transactional OTP'
  },
  {
    id: 'sms-02',
    recipientPhone: '+91 9005756321',
    audience: 'All Vendors',
    dltTemplateId: 'DLT-SSIND-PAY-100589',
    messageText: 'GaunGaliHat Notice: Weekly vendor settlement of ₹18,400 has been credited to your bank account. S S Industries.',
    sentDate: 'Oct 18, 2026',
    status: 'Delivered',
    type: 'Dispatch Notification'
  },
  {
    id: 'sms-03',
    recipientPhone: '+91 8303578806',
    audience: 'All Customers',
    dltTemplateId: 'DLT-SSIND-DISP-100812',
    messageText: 'Your order #GGH-98124 for Desi Hansiya has been dispatched from S S Industries Hardoi Hub via India Post tracking #IN8923412. Track live on GaunGaliHat.',
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

export const initialHeroFestivalConfig: HeroFestivalConfig = {
  enabled: true,
  festivalName: 'Diwali Special',
  badgeText: '🪔 Shubh Deepavali Mahotsav • Special 25% Off Artisan Pack',
  mainHeading: 'Gaon ke hunar,',
  highlightHeading: 'ab har ghar tak',
  subheading: 'High-carbon forged farming tools, authentic Channapatna wooden toys, Moradabad brass puja bells, and golden jute gift jholas crafted with love by rural artisans across Bharat.',
  theme: 'diwali',
  gradientPreset: 'festive-gold',
  ctaPrimaryText: 'Explore Festive Marketplace',
  ctaPrimaryLink: '/products',
  ctaSecondaryText: 'Farming & Garden Tools',
  ctaSecondaryLink: '/category/farming-traditional-tools',
  showCountdown: true,
  countdownEndDate: '2026-11-15T23:59:59',
  promoCode: 'DIWALI2026',
  promoDiscountPercent: 25,
  heroImageUrl: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80',
  floatingPill1Title: 'High-Carbon Steel',
  floatingPill1Subtitle: 'Forged Desi Hansiya (Sickle)',
  floatingPill1Icon: '⚒️',
  floatingPill2Title: '100% Eco Biodegradable',
  floatingPill2Subtitle: 'Golden Jute Shopping Jhola',
  floatingPill2Badge: '★ 4.96'
};

export const FESTIVAL_PRESETS: Record<string, Partial<HeroFestivalConfig>> = {
  diwali: {
    festivalName: 'Diwali Special',
    badgeText: '🪔 Shubh Deepavali Mahotsav • Flat 25% Off Artisan Pack',
    mainHeading: 'Deepawali ke shubh avsar par,',
    highlightHeading: 'Gaon ke hunar aapke dwar',
    subheading: 'Special festival discounts on Moradabad hand-crafted brass bells, clay diyas, S S Industries agricultural tools, and golden jute gift hampers.',
    gradientPreset: 'festive-gold',
    theme: 'diwali',
    promoCode: 'DIWALI2026',
    promoDiscountPercent: 25,
    showCountdown: true,
    floatingPill1Icon: '🪔',
    floatingPill1Title: 'Brass Puja Bell',
    floatingPill1Subtitle: 'Moradabad Hand-Cast Metal'
  },
  holi: {
    festivalName: 'Holi Dhamaka',
    badgeText: '🎨 Rangotsav Special • Rural Handloom & Eco Craft',
    mainHeading: 'Rang birange tyohar me,',
    highlightHeading: 'Desi mitti ki mehak aur khushiyaan',
    subheading: 'Celebrate Holi with 100% natural fiber jute pouches, hand-spun Varanasi khadi kurtas, and handcrafted Channapatna wooden toys.',
    gradientPreset: 'crimson-ruby',
    theme: 'holi',
    promoCode: 'HOLI2026',
    promoDiscountPercent: 20,
    showCountdown: true,
    floatingPill1Icon: '🎨',
    floatingPill1Title: 'Handloom Khadi',
    floatingPill1Subtitle: 'Pure Spun Cotton Kurta'
  },
  harvest: {
    festivalName: 'Makar Sankranti & Baisakhi',
    badgeText: '🌾 Kisan Kranti & Rabi Harvest Season Tools',
    mainHeading: 'Khet aur khalihaan ki shaan,',
    highlightHeading: 'S S Industries Forged Tools',
    subheading: 'Heavy-duty agricultural sickles, hardened carbon steel garden khurpis, heavy fawdas, and chaff cutter toka blades direct from Hardoi foundry.',
    gradientPreset: 'saffron-glory',
    theme: 'harvest',
    promoCode: 'KISAN2026',
    promoDiscountPercent: 15,
    showCountdown: true,
    floatingPill1Icon: '🌾',
    floatingPill1Title: 'Forged Hansiya',
    floatingPill1Subtitle: 'High-Carbon Steel Edge'
  },
  patriotic: {
    festivalName: 'Independence & Republic Day',
    badgeText: '🇮🇳 Swadeshi Bharat • Khadi & Village Industries',
    mainHeading: 'Garv se apnayein swadeshi,',
    highlightHeading: 'Har gaon bane atmanirbhar',
    subheading: 'Empowering 5,000+ rural women self-help groups, generational blacksmiths, and cottage workshops across Uttar Pradesh & Bharat.',
    gradientPreset: 'royal-indigo',
    theme: 'patriotic',
    promoCode: 'AZADI2026',
    promoDiscountPercent: 26,
    showCountdown: false,
    floatingPill1Icon: '🇮🇳',
    floatingPill1Title: 'Atmanirbhar Gaon',
    floatingPill1Subtitle: '100% Indian Made'
  },
  default: {
    festivalName: 'Evergreen Rural Marketplace',
    badgeText: '🌾 S S Industries Enterprise • Empowering 5,000+ Rural Artisans & Smiths',
    mainHeading: 'Gaon ke hunar,',
    highlightHeading: 'ab har ghar tak',
    subheading: 'Discover high-durability hand-forged agricultural tools, high-carbon steel sickles, heavy garden khurpis, eco golden jute bags, GI Channapatna wooden toys, and handlooms.',
    gradientPreset: 'emerald-forest',
    theme: 'default',
    promoCode: 'VILLAGE10',
    promoDiscountPercent: 10,
    showCountdown: false,
    floatingPill1Icon: '⚒️',
    floatingPill1Title: 'High-Carbon Steel',
    floatingPill1Subtitle: 'Forged Desi Hansiya (Sickle)'
  }
};

export const initialAuditLogs: AuditLogEntry[] = [
  {
    id: 'log-01',
    actorName: 'Anubhav Kashyap',
    actorRole: 'super_admin',
    action: 'SYSTEM_BOOT',
    module: 'Security',
    details: 'System initialized with edge CDN security policies, XSS input filters, and role-based permissions matrix.',
    timestamp: '2026-10-20 14:30:15',
    ipAddress: '103.21.244.0 (Cloudflare Edge)'
  },
  {
    id: 'log-02',
    actorName: 'Anubhav Kashyap',
    actorRole: 'super_admin',
    action: 'FESTIVAL_BANNER_CONFIG',
    module: 'Festival Theme',
    details: 'Applied Diwali Special festive theme with 25% discount promo code DIWALI2026.',
    timestamp: '2026-10-20 15:10:44',
    ipAddress: '103.21.244.0 (Cloudflare Edge)'
  },
  {
    id: 'log-03',
    actorName: 'Sunil Sharma',
    actorRole: 'admin',
    action: 'VENDOR_KYC_VERIFIED',
    module: 'Vendors',
    details: 'Approved KYC documents for Ramkishan Lohar (Hardoi Blacksmith Foundry).',
    timestamp: '2026-10-20 15:20:00',
    ipAddress: '103.21.244.12'
  },
  {
    id: 'log-04',
    actorName: 'Neha Singhania',
    actorRole: 'marketer',
    action: 'CAMPAIGN_DISPATCH',
    module: 'SEO',
    details: 'Prepared Festive Rabi Harvest newsletter campaign targeting 4,500 registered rural customers.',
    timestamp: '2026-10-20 16:05:10',
    ipAddress: '103.21.244.45'
  }
];

