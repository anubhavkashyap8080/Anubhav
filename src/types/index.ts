export interface Seller {
  id: string;
  name: string;
  slug: string;
  type: 'SHG' | 'Artisan' | 'Farmer' | 'Cottage Industry';
  avatar: string;
  coverImage: string;
  location: string;
  district: string;
  state: string;
  story: string;
  yearEstablished: number;
  memberCount: number;
  verified: boolean;
  rating: number;
  reviewsTotal: number;
  categories: string[];
}

export interface Product {
  id: string;
  name: string;
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
  };
  description: string;
  story: string;
  attributes: Record<string, string>;
  stockStatus: 'In Stock' | 'Only Few Left' | 'Out of Stock';
  isBestSeller?: boolean;
  isRuralWomenMade?: boolean;
  isTraditionalFood?: boolean;
  isTool?: boolean;
  tags: string[];
  isDemoData: boolean;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
  description: string;
  image: string;
  productCount: number;
  iconName: string;
}

export interface CartItem {
  product: Product;
  quantity: number;
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

export interface HeaderConfig {
  logoUrl: string;
  brandName: string;
  tagline: string;
  showAnnouncement: boolean;
  announcementText: string;
  showLocationSelector: boolean;
  defaultLocation: string;
  showSearch: boolean;
  showIcons: boolean;
  navItems: Array<{ label: string; href: string }>;
  ctaLabel: string;
  ctaHref: string;
}

export interface FooterConfig {
  description: string;
  contactPhone: string;
  contactEmail: string;
  contactAddress: string;
  socialLinks: {
    facebook: string;
    instagram: string;
    twitter: string;
    youtube: string;
  };
  copyrightText: string;
  showNewsletter: boolean;
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
  blogSeoDefaults: {
    titleTemplate: string;
    defaultOgImage: string;
  };
  indexSearchEngines: boolean;
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
