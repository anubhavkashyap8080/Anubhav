import { HeaderConfig, FooterConfig, SEOConfig, SecurityChecklist } from '../types';

export const initialHeaderConfig: HeaderConfig = {
  logoUrl: '/logo.png',
  brandName: 'GanvGaliHut',
  tagline: 'Deshi ki mitti ka swad • Ganv se ghar tak',
  showAnnouncement: true,
  announcementText: '🌾 Pure Harvest & Village Crafts: Direct from Rural Women SHGs & Artisans | Free Delivery on Orders Over ₹499 | Cash on Delivery Available',
  showLocationSelector: true,
  defaultLocation: 'Deliver to: Mumbai 400001',
  showSearch: true,
  showIcons: true,
  navItems: [
    { label: 'All Products', href: '/products' },
    { label: 'Papad & Pickles', href: '/category/papad-pickles-snacks' },
    { label: 'Desi Ghee & Grains', href: '/category/dairy-ghee-grains' },
    { label: 'Traditional Spices', href: '/category/traditional-spices-tea' },
    { label: 'Jute Bags & Jholas', href: '/category/jute-bags-cloth-jholas' },
    { label: 'Farming Tools', href: '/category/farming-traditional-tools' },
    { label: 'Women SHG Crafts', href: '/category/handmade-by-rural-women' },
    { label: 'Village Stories & Blog', href: '/blog' },
  ],
  ctaLabel: 'Become a Seller',
  ctaHref: '/become-seller'
};

export const initialFooterConfig: FooterConfig = {
  description: 'GanvGaliHut (by S S Industries) connects authentic rural Indian women self-help groups, generational craftspeople, farmers, and cottage industries directly with conscious homes across the nation.',
  contactPhone: '+91 7897492100 / +91 9005756321 / +91 8303578806',
  contactEmail: 'namaste@ganvgalihat.com',
  contactAddress: 'Works: Madhoganj Baghauli Road, Newada, Hardoi 241126 | Head Office: G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi',
  socialLinks: {
    facebook: 'https://facebook.com/ganvgalihat',
    instagram: 'https://instagram.com/ganvgalihat',
    twitter: 'https://twitter.com/ganvgalihat',
    youtube: 'https://youtube.com/ganvgalihat'
  },
  copyrightText: '© 2026 GanvGaliHut — S S Industries. Built with pride for Rural Indian Artisans. (Frontend Design Prototype)',
  showNewsletter: true,
  columns: [
    {
      title: 'Shop Categories',
      links: [
        { label: 'Homemade Papad & Pickles', href: '/category/papad-pickles-snacks' },
        { label: 'Vedic Gir Cow Ghee', href: '/category/dairy-ghee-grains' },
        { label: 'Stone-Pounded Spices', href: '/category/traditional-spices-tea' },
        { label: 'Golden Jute Totes & Bags', href: '/category/jute-bags-cloth-jholas' },
        { label: 'Hand-Forged Desi Sickles', href: '/category/farming-traditional-tools' },
        { label: 'Channapatna Wooden Toys', href: '/category/village-home-decor-toys' },
      ]
    },
    {
      title: 'Rural Empowerment',
      links: [
        { label: 'About Our Mission', href: '/about' },
        { label: 'Meet Our Artisans', href: '/#sellers' },
        { label: 'Join as a Rural Seller', href: '/become-seller' },
        { label: 'Seller Registration', href: '/seller-register' },
        { label: 'Village Stories Blog', href: '/blog' },
        { label: 'Impact & SHG Metrics', href: '/about' },
      ]
    },
    {
      title: 'Customer Support',
      links: [
        { label: 'Track Order', href: '/account' },
        { label: 'Shipping & Delivery Policy', href: '/shipping-policy' },
        { label: 'Return & Refund Policy', href: '/returns-policy' },
        { label: 'Frequently Asked Questions', href: '/faq' },
        { label: 'Contact Us', href: '/contact' },
        { label: 'Privacy Policy', href: '/privacy-policy' },
        { label: 'Terms & Conditions', href: '/terms-conditions' },
      ]
    },
    {
      title: 'Admin Previews',
      links: [
        { label: 'Admin Dashboard', href: '/admin' },
        { label: 'Header & Footer Customization', href: '/admin/site-customization' },
        { label: 'Blog CMS Management', href: '/admin/blog' },
        { label: 'SEO & Sitemap Settings', href: '/admin/seo-settings' },
        { label: 'Security & Privacy Controls', href: '/admin/security-settings' },
        { label: 'Seller Dashboard Preview', href: '/seller/dashboard' },
      ]
    }
  ]
};

export const initialSEOConfig: SEOConfig = {
  siteTitle: 'GanvGaliHut — Deshi ki mitti ka swad | Rural Artisan Marketplace',
  metaDescription: 'Discover authentic village crafts, pure Vedic Bilona cow ghee, traditional spices, handmade apparel, and farming tools handcrafted by rural Indian women SHGs and artisans.',
  canonicalUrl: 'https://ganvgalihat.com',
  sitemapUrl: 'https://ganvgalihat.com/sitemap.xml',
  robotsTxt: `# Robots.txt for GanvGaliHut (Preview Configuration)
User-agent: *
Allow: /
Allow: /products/
Allow: /category/
Allow: /blog/
Allow: /seller/
Disallow: /admin/
Disallow: /seller/dashboard/
Disallow: /account/
Disallow: /checkout/
Disallow: /cart/

Sitemap: https://ganvgalihat.com/sitemap.xml
`,
  blogSeoDefaults: {
    titleTemplate: '%s | GanvGaliHut Village Stories',
    defaultOgImage: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=1200&q=80'
  },
  indexSearchEngines: true
};

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
