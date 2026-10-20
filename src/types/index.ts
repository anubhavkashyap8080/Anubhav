export type UserRole = 'super_admin' | 'admin' | 'marketer' | 'content_writer' | 'vendor' | 'support' | 'customer';

export interface UserWorkLog {
  id: string;
  action: string;
  module: string;
  category?: string;
  timestamp: string;
  details: string;
  status?: 'completed' | 'in_progress';
}

export interface User {
  id: string;
  name: string;
  email: string;
  phone: string;
  role: UserRole;
  avatar?: string;
  department?: string;
  status: 'active' | 'suspended' | 'pending';
  joinedDate: string;
  lastLogin?: string;
  ordersCount?: number;
  assignedTickets?: number;
  vendorId?: string;
  workLog?: UserWorkLog[];
}

export interface Seller {
  id: string;
  name: string;
  businessName?: string;
  slug: string;
  type: 'SHG' | 'Artisan' | 'Farmer' | 'Cottage Industry';
  avatar: string;
  coverImage: string;
  location: string;
  district: string;
  state: string;
  coordinates: {
    lat: number;
    lng: number;
  };
  phone: string;
  email: string;
  story: string;
  yearEstablished: number;
  memberCount: number;
  verified: boolean;
  kycStatus: 'Verified' | 'Pending Review' | 'Rejected';
  rating: number;
  reviewsTotal: number;
  categories: string[];
  totalProducts: number;
  totalRevenue: number;
  commissionRate: number; // percentage, e.g. 5
}

export interface Product {
  id: string;
  code?: string; // Unique Product Code / SKU e.g. GGH-TL-101
  name: string;
  hindiName?: string;
  slug: string;
  category: string;
  categorySlug: string;
  subCategory?: string;
  price: number;
  originalPrice: number;
  discount: number;
  rating: number;
  reviewCount: number;
  image: string;
  gallery: string[];
  stock?: number;
  unit?: string;
  seller: {
    id: string;
    name: string;
    slug: string;
    type: 'SHG' | 'Artisan' | 'Farmer' | 'Cottage Industry';
    location: string;
    district: string;
    state: string;
    verified: boolean;
    rating: number;
    memberCount?: number;
    avatar: string;
    phone?: string;
  };
  description: string;
  story: string;
  attributes: Record<string, string>;
  stockStatus: 'In Stock' | 'Only Few Left' | 'Out of Stock';
  isBestSeller?: boolean;
  isRuralWomenMade?: boolean;
  isTool?: boolean;
  isHardware?: boolean;
  isCraft?: boolean;
  tags: string[];
  isDemoData?: boolean;
}

export interface Category {
  id: string;
  name: string;
  hindiName?: string;
  slug: string;
  description: string;
  image: string;
  productCount: number;
  itemCount?: number;
  iconName?: string;
  status?: 'active' | 'inactive';
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface TicketMessage {
  id: string;
  senderId: string;
  senderName: string;
  senderRole: UserRole;
  message: string;
  timestamp: string;
  attachments?: string[];
}

export interface SupportTicket {
  id: string;
  ticketNumber: string;
  userId: string;
  userName: string;
  userEmail: string;
  userPhone: string;
  subject: string;
  department: 'Order Issue' | 'Vendor Verification' | 'Payment & Payout' | 'Product Inquiry' | 'General Support';
  priority: 'Urgent' | 'High' | 'Medium' | 'Low';
  status: 'Open' | 'In Progress' | 'Resolved' | 'Closed';
  createdAt: string;
  updatedAt: string;
  assignedAgent?: string;
  messages: TicketMessage[];
}

export interface BlogPost {
  id: string;
  title: string;
  slug: string;
  excerpt: string;
  content: string;
  category: string;
  categorySlug: string;
  image: string;
  author: {
    name: string;
    role: string;
    avatar: string;
  };
  publishedAt: string;
  readTime: string;
  featured?: boolean;
  tags: string[];
  status: 'Published' | 'Draft' | 'Scheduled';
  seo: {
    title: string;
    metaDescription: string;
    canonicalUrl: string;
    ogImage: string;
    indexRobots: boolean;
  };
}

export interface SocialLinksConfig {
  facebook: string;
  instagram: string;
  twitter: string;
  youtube: string;
  whatsapp: string;
  linkedin: string;
  showFacebook: boolean;
  showInstagram: boolean;
  showTwitter: boolean;
  showYoutube: boolean;
  showWhatsapp: boolean;
  showLinkedin: boolean;
}

export interface HeaderConfig {
  logoUrl: string;
  brandName: string;
  tagline: string;
  faviconUrl?: string;
  customHeadCode?: string;
  customHeaderCode?: string;
  showAnnouncement: boolean;
  announcementText: string;
  showLocationSelector: boolean;
  defaultLocation: string;
  showSearch: boolean;
  showIcons: boolean;
  navItems: Array<{ id: string; label: string; href: string; enabled: boolean }>;
  ctaLabel: string;
  ctaHref: string;
}

export interface FooterConfig {
  companyName: string;
  description: string;
  contactPhone: string;
  altPhone1?: string;
  altPhone2?: string;
  contactEmail: string;
  contactAddress: string;
  headOfficeAddress: string;
  socialLinks: SocialLinksConfig;
  copyrightText: string;
  showNewsletter: boolean;
  customFooterCode?: string;
  customCss?: string;
  columns: Array<{
    title: string;
    links: Array<{ label: string; href: string }>;
  }>;
}

export interface SEOConfig {
  siteTitle: string;
  metaDescription: string;
  canonicalUrl: string;
  sitemapUrl: string;
  robotsTxt: string;
  googleSearchConsoleVerification: string;
  googleAnalyticsId: string;
  googleTagManagerId: string;
  metaPixelId: string;
  blogSeoDefaults: {
    titleTemplate: string;
    defaultOgImage: string;
  };
  indexSearchEngines: boolean;
}

export interface FirebaseSettings {
  apiKey: string;
  authDomain: string;
  projectId: string;
  storageBucket: string;
  messagingSenderId: string;
  appId: string;
  measurementId: string;
  enableSocialLogin: boolean;
  enablePhoneAuth: boolean;
}

export interface MapSettings {
  provider: 'leaflet' | 'google';
  googleMapsApiKey: string;
  defaultCenter: {
    lat: number;
    lng: number;
  };
  defaultZoom: number;
  searchRadiusKm: number;
}

export interface EmailCampaign {
  id: string;
  subject: string;
  senderName: string;
  senderEmail: string;
  recipientAudience: 'All Users' | 'All Vendors' | 'All Customers' | 'Unverified Vendors';
  recipientCount: number;
  template: 'Promotion / Offer' | 'System Announcement' | 'Artisan Spotlight' | 'Account Security Alert';
  body: string;
  sentDate: string;
  status: 'Sent' | 'Scheduled' | 'Draft';
  openRate: string;
}

export interface SmsMessage {
  id: string;
  recipientPhone: string;
  audience: 'Individual Number' | 'All Customers' | 'All Vendors';
  dltTemplateId: string;
  messageText: string;
  sentDate: string;
  status: 'Delivered' | 'Pending Gateway' | 'Failed';
  type: 'Transactional OTP' | 'Dispatch Notification' | 'Promotional SMS' | 'WhatsApp Direct';
}

export interface SecurityChecklist {
  httpsConfigured: boolean;
  cspEnabled: boolean;
  hstsEnabled: boolean;
  secureCookies: boolean;
  rateLimitingConfigured: boolean;
  sqlInjectionSanitized: boolean;
  fileUploadRestricted: boolean;
  sessionTimeoutMinutes: number;
  lastAuditedDate: string;
}

export interface HeroFestivalConfig {
  enabled: boolean;
  festivalName: string; // e.g. "Diwali Special", "Holi Dhamaka", "Makar Sankranti", "Independence Day", "Custom"
  badgeText: string;
  mainHeading: string;
  highlightHeading: string;
  subheading: string;
  theme: 'diwali' | 'holi' | 'harvest' | 'patriotic' | 'default';
  gradientPreset: 'festive-gold' | 'emerald-forest' | 'saffron-glory' | 'royal-indigo' | 'crimson-ruby' | 'diwali-gold' | 'holi-vibrant' | 'makar-sankranti' | 'patriotic' | string;
  ctaPrimaryText: string;
  ctaPrimaryLink: string;
  ctaSecondaryText: string;
  ctaSecondaryLink: string;
  showCountdown: boolean;
  countdownEndDate: string;
  promoCode: string;
  promoDiscountPercent: number;
  heroImageUrl: string;
  floatingPill1Title: string;
  floatingPill1Subtitle: string;
  floatingPill1Icon: string;
  floatingPill2Title: string;
  floatingPill2Subtitle: string;
  floatingPill2Badge: string;
}

export interface AuditLogEntry {
  id: string;
  actorName: string;
  actorRole: UserRole;
  action: string;
  module: 'Users' | 'Vendors' | 'Tickets' | 'Festival Theme' | 'Customizer' | 'Blogs' | 'SEO' | 'Security';
  details: string;
  timestamp: string;
  ipAddress: string;
}
