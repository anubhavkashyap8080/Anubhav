# GanvGaliHut (गांव गली हट) — Rural Marketplace Frontend Prototype

> **Tagline:** *Deshi ki mitti ka swad • Ganv se ghar tak*  
> **Brand Identity:** Preserves the authentic thatched-hut logo design, rustic forest green (`#0c5a34`), earthen warm cream (`#fdfbf7`), and terracotta orange (`#e65c00`).

GanvGaliHut is a modern, responsive, multi-vendor e-commerce marketplace prototype inspired by Toolsvilla's discovery-first product experience. It connects conscious urban households directly with rural Indian women Self-Help Groups (SHGs), generational ironsmiths, tribal spice cultivators, cottage industries, and smallholder organic farmers.

---

## ⚠️ Phase 1 Critical Specification: Frontend-Only Interactive Prototype

For **Phase 1**, this application is strictly a **frontend design and interactive prototype**.

* **No live backend, database, payment processing, or real accounts exist.**
* Every non-browsing transactional action (Add to Cart, Buy Now, Checkout submission, Seller registration, Newsletter signup, Contact form dispatch, Blog publishing, Header/Footer customization saving, Sitemap deployment, Security changes) automatically invokes the centralized **Global Demo Modal**:

```
Title:   This is only a demo
Message: This feature is currently available for preview only. Backend functionality will be implemented in a future phase.
Button:  Got it
```

* Harmless navigation, filtering, product searching, local tab switches, and mobile drawer toggles work smoothly in local memory.

---

## 🛠️ Technology Stack

* **Framework:** React 18 + Vite + TypeScript
* **Routing:** React Router v6
* **Styling:** Tailwind CSS (configured locally with brand color tokens)
* **Icons:** Lucide React
* **Assets:** Original thatched-hut GanvGaliHut logo (`/logo.png`) bundled directly in `public/` and `src/assets/`.

---

## 🚀 Quickstart & Local Run Commands

### 1. Navigate to Project Directory
```powershell
cd C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat
```

### 2. Start the Local Development Server
```powershell
npm run dev
```
The application will be live at `http://localhost:5173`.

### 3. Build for Production
```powershell
npm run build
```
This runs TypeScript typechecking and bundles optimized production assets into `dist/`.

### 4. Preview the Production Build
```powershell
npm run preview
```

---

## 📁 Architecture & Project Structure

```
ganvgalihat/
├── index.html                   # Entry HTML with Inter & Poppins typography
├── package.json                 # Project dependencies & scripts
├── tsconfig.json                # TypeScript compiler configuration
├── tailwind.config.js           # Custom theme colors (brand-green, brand-orange, brand-cream)
├── postcss.config.js            # PostCSS pipeline with Tailwind & Autoprefixer
├── public/
│   └── logo.png                 # Original GanvGaliHut logo asset
├── src/
│   ├── assets/
│   │   └── logo.png             # Bundled brand identity logo
│   ├── types/
│   │   └── index.ts             # Typed models for Product, Seller, Category, BlogPost, Configs
│   ├── context/
│   │   ├── DemoModalContext.tsx # Centralized DemoModal & useDemoAction system
│   │   ├── CartWishlistContext.tsx # Local state for mock cart & wishlist
│   │   └── SiteConfigContext.tsx   # Live reactive state for header/footer CMS customizations
│   ├── components/
│   │   ├── common/
│   │   │   ├── DemoModal.tsx    # Accessible, keyboard-friendly global demo dialog
│   │   │   └── ProductCard.tsx  # Reusable product card with trust badges & demo triggers
│   │   └── layout/
│   │       ├── AnnouncementBar.tsx # Dynamic announcement ribbon with language switcher
│   │       ├── Header.tsx       # Search with autocomplete, location selector, cart & account
│   │       ├── CategoryNav.tsx  # Horizontal scrollable category ribbon with icons
│   │       ├── Footer.tsx       # Multi-column footer with newsletter preview & trust metrics
│   │       └── Layout.tsx       # Master wrapper with scroll-to-top & indicator banner
│   ├── data/
│   │   ├── mockProducts.ts      # 45+ realistic products across all 7 rural categories
│   │   ├── mockCategories.ts    # 8 detailed category models with item counts
│   │   ├── mockSellers.ts       # 8 rich rural artisan / SHG cooperative profiles
│   │   ├── mockBlogPosts.ts     # Authentic rural journal articles & editorial metadata
│   │   └── mockConfig.ts        # Default CMS configurations for header, footer, SEO, security
│   ├── pages/
│   │   ├── Home.tsx             # 19-section comprehensive marketplace homepage
│   │   ├── Products.tsx         # Catalog with price/producer filters & mobile drawer
│   │   ├── ProductDetail.tsx    # Gallery, specs, artisan story tab, verified reviews
│   │   ├── Wishlist.tsx         # Saved items with move-to-cart demo triggers
│   │   ├── Cart.tsx             # Order summary, coupon form, and free shipping threshold
│   │   ├── Checkout.tsx         # 3-step checkout flow (Address, Carrier, Payment method)
│   │   ├── Account.tsx          # Buyer profile, address book, and order shipment history
│   │   ├── BecomeSeller.tsx     # Rural seller landing page with 0% middleman benefits
│   │   ├── SellerRegister.tsx   # Multi-step artisan onboarding application form
│   │   ├── SellerProfile.tsx    # Public artisan storefront with craft origin stories
│   │   ├── SellerDashboard.tsx  # Seller operations hub: GMV, orders, inventory, payouts
│   │   ├── BlogList.tsx         # Village journal listing with category filter & search
│   │   ├── BlogDetail.tsx       # Article view with TOC, author bio, and related reads
│   │   ├── About.tsx            # Mission, vision, and core ethics charter
│   │   ├── Contact.tsx          # Headquarters info and inquiry form preview
│   │   ├── FAQ.tsx              # Interactive accordion answering rural commerce questions
│   │   ├── ShippingPolicy.tsx   # Direct village dispatch guidelines
│   │   ├── ReturnsPolicy.tsx    # 7-day guarantee and food freshness terms
│   │   ├── PrivacyPolicy.tsx    # India DPDP Act data rights preview
│   │   ├── TermsConditions.tsx  # Platform usage terms and handcrafted variances
│   │   ├── NotFound.tsx         # Friendly village 404 page with search fallback
│   │   └── admin/
│   │       ├── AdminDashboard.tsx   # Central operations hub with platform metrics
│   │       ├── SiteCustomization.tsx# Real-time Header & Footer editor with mobile preview
│   │       ├── BlogManagement.tsx   # Editorial CMS with rich toolbar and OG metadata
│   │       ├── SeoSettings.tsx      # Sitemaps, Robots.txt, and Schema.org JSON-LD
│   │       └── SecuritySettings.tsx # Security headers, CSP, DPDP, and hardening checklist
│   ├── App.tsx                  # React Router mapping all 28+ routes
│   ├── main.tsx                 # Application DOM entrypoint
│   └── index.css                # Tailwind directives, custom fonts & scrollbars
```

---

## 🌾 Rural Product Catalog Breakdown (45+ Realistic Items)

The prototype includes over 45 realistic products with specifications, ingredients, origin villages, and seller attributions across all specified categories:

1. **Papad, Chips, Mathri, Namkeen & Pickles:**
   - Handmade Sun-Dried Moong Dal Papad (Heeng Jeera Special)
   - Traditional Banarasi Stuffed Red Chilli Pickle (Lal Mirch Bharwa)
   - Desi Masala Mathri with Ajwain & Kasuri Methi
   - Village Ratlami Sev with Clove & Black Pepper
   - Organic Desi Aam Ka Achaar (Sun-Matured)
   - Handcrafted Jaggery Millet Biscuits (Ragi & Jowar)
   - Village Sun-Dried Potato Wafers
   - Sweet & Sour Lemon Ginger Pickle (Zero Oil)

2. **Dairy Products, Desi Ghee, Daliya, Grains & Flours:**
   - Vedic A2 Gir Cow Bilona Ghee (Curd-Churned Glass Jar)
   - Fresh Malai Paneer Block
   - Hand-Broken Organic Wheat Daliya
   - Pure Sugarcane Desi Gur Cubes (Chemical-Free)
   - Stone-Milled Malwa Sharbati Whole Wheat Atta
   - Unpolished Native Arhar / Toor Dal
   - Organic Black Bengal Gram (Kala Chana Desi)
   - Wood-Pressed Kachhi Ghani Mustard Oil

3. **Traditional Spices & Tea:**
   - Wayanad High-Curcumin Lakadong Turmeric Powder (Tested 8.2%+ Curcumin)
   - Rajasthan Mathania Hand-Pounded Red Chilli Powder
   - Stone-Ground Fragrant Coriander Powder (Desi Dhaniya)
   - Hand-Plucked Assam Organic CTC & Orthodox Black Tea
   - Wayanad Green Cardamom Pods (Bold 8mm Elaichi)
   - Stone-Pounded Garam Masala Blend (16 Whole Spices)

4. **Jute Bags, Cloth Jholas & Baskets:**
   - Heavy-Duty Natural Golden Jute Market Jhola (20kg capacity)
   - Handcrafted Kantha Embroidered Cotton Travel Pouch
   - Village Sabai Grass Handwoven Storage Basket with Lid
   - Desi Khadi Cotton Grocery Tote Bag
   - Hand-Knotted Macrame Jute Plant Hanger
   - Natural River Reed (Moonj Grass) Chatai Floor Mat

5. **Clothing, Handmade Garments, Bangles & Accessories:**
   - Traditional Rajasthani Handcrafted Lac Bangles (Kundan Mirrorwork Set)
   - Handloom Khadi Cotton Kurta (Natural Indigo Plant Dye)
   - Hand-Block Printed Bagru Chanderi Cotton Silk Dupatta
   - Handmade Resham & Mirror Work Potli Bag
   - Handcrafted Terracotta Clay Bead Necklace & Earrings Set

6. **Wooden Toys, Dolls, Mugs & Home Decor:**
   - GI Channapatna Handcrafted Wooden Rocking Horse (Non-toxic vegetable dyes)
   - Traditional Handmade Cloth Rag Dolls (Gudda Gudiya Pair)
   - Handcrafted Clay Terracotta Kulhads & Tea Mugs (Set of 6)
   - Coir & Rubber Welcome Door Mat (Hand-Tufted Coconut Fiber)
   - Hand-Carved Sheesham Wood Incense Burner & Box
   - Hand-Painted Madhubani Folk Art Wooden Wall Clock

7. **Khurpi, Fawda, Hathodi, Desi Hansiya & Traditional Farming Tools:**
   - Hand-Forged Carbon Steel Desi Hansiya (Micro-Serrated Sickle)
   - Heavy-Duty Iron Garden Khurpi (Wide 3-Inch Blade)
   - Traditional Heavy Farm Fawda / Kudal (Agricultural Spade Blade)
   - Village Blacksmith Hathodi (1.5lb Cross-Peen Hammer)
   - Narrow Pointed Weeding Khurpi (Nursery Kitchen Garden Special)
   - Hand-Carved Neem Wood Kitchen Mortar & Pestle (Okhli Musal)

---

## 🛠️ Administrative & Technical Management Previews

1. **Site Customization Studio (`/admin/site-customization`):**
   - Live reactive header & footer editor.
   - Toggle announcement bar, delivery location selector, search bar, and action icons.
   - Edit navigation menu taxonomy, CTA buttons, copyright, and contact details.
   - Real-time **Desktop and Mobile preview panes**.
   - "Save Changes" invokes the global demo modal.

2. **Village Journal CMS (`/admin/blog`):**
   - Full post manager with status badges (`Published`, `Draft`, `Scheduled`).
   - Rich-text editor simulator with formatting toolbar.
   - SEO Title, Meta Description, Canonical URL, and Open Graph card preview.
   - Search engine index/noindex robots toggle.

3. **SEO & Technical Sitemaps (`/admin/seo-settings`):**
   - Sub-sitemap management for Pages, Products, Categories, and Blog.
   - Syntax-highlighted read-only XML preview with one-click copy and generation triggers.
   - Interactive Robots.txt editor with crawler instruction guidance.
   - Schema.org Organization & WebSite JSON-LD structured data preview.

4. **Security & Privacy Architecture Controls (`/admin/security-settings`):**
   - Comprehensive checklist covering TLS 1.3, HSTS, and CSP headers.
   - HttpOnly, Secure, and SameSite session cookie specifications.
   - India DPDP Act compliance requirements for personal data localization.
   - Clear disclosure that frontend-only prototypes do not execute live authentication or encrypted payment transactions.

---

## 🗺️ Complete Route Directory

| Route Path | Description |
| :--- | :--- |
| `/` | Complete 19-section Marketplace Homepage |
| `/products` | Shop All Products with faceted sidebar filters & sorting |
| `/category/:categorySlug` | Category-specific product discovery |
| `/search?q=...` | Live product search results with autocomplete |
| `/product/:slug` | Detailed product showcase, artisan story, and reviews |
| `/wishlist` | Saved items with move-to-cart controls |
| `/cart` | Shopping basket, price breakdown, and coupon form |
| `/checkout` | 3-step checkout flow (Address, Carrier, Payment) |
| `/account` | Buyer profile, order tracking, and address book |
| `/become-seller` | Artisan onboarding landing page with benefits |
| `/seller-register` | Multi-step rural seller application form |
| `/seller/:sellerSlug` | Public artisan storefront and story |
| `/seller/dashboard` | Seller operational hub with sales metrics & orders |
| `/blog` | Village Journal article feed with category filters |
| `/blog/category/:slug` | Category-filtered blog stories |
| `/blog/:slug` | Full article view with Table of Contents & author bio |
| `/about` | Marketplace origin, mission, and core values |
| `/contact` | Customer support, phone, email, and inquiry form |
| `/faq` | Interactive accordion addressing rural commerce queries |
| `/shipping-policy` | Village dispatch timelines & free delivery terms |
| `/returns-policy` | 7-day guarantee & food freshness return terms |
| `/privacy-policy` | DPDP Act data rights & cookie transparency |
| `/terms-conditions` | Platform rules and handcrafted artisan variances |
| `/admin` | Central operations overview & GMV analytics |
| `/admin/site-customization` | Live Header & Footer CMS editor with mobile preview |
| `/admin/blog` | Blog post management & rich editor preview |
| `/admin/seo-settings` | Sitemap XML feeds, Robots.txt, and JSON-LD schema |
| `/admin/security-settings` | Hardening checklist & security architecture roadmap |
| `/404` | Custom village-themed 404 page with search fallback |

---

## 🔮 Phase 2 Backend Roadmap Requirements

When transitioning from this frontend prototype to production, the backend architecture must implement:
1. **Authentication & RBAC:** Secure password hashing (Argon2id), session management with HttpOnly Secure SameSite cookies, and role-based permissions (Buyer, Seller, Village Mitra, Super Admin).
2. **Payment Gateway Integration:** Direct payment routing (Razorpay / Cashfree / India Post Payments Bank) with HMAC webhook signature validation and 256-bit SSL encryption.
3. **Real-time Order Lifecycle:** Order state machines connecting India Post API, Delhivery, or regional village courier webhooks.
4. **Dynamic Sitemap & Robots Generation:** Automated edge generation of `sitemap.xml` strictly including published, canonical URLs while excluding private administrative routes.
5. **Secure Object Storage:** Presigned uploads to Cloudflare R2 / AWS S3 with MIME-type verification, malware scanning, and EXIF geolocation stripping for artisan media.
6. **Rate Limiting & WAF:** API gateway rate limiting on checkout endpoints, brute-force throttling on login routes, and Cloudflare Turnstile bot verification.
