import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { Layout } from './components/layout/Layout';
import { DemoModalProvider } from './context/DemoModalContext';
import { CartWishlistProvider } from './context/CartWishlistContext';
import { SiteConfigProvider } from './context/SiteConfigContext';

// Pages
import { Home } from './pages/Home';
import { Products } from './pages/Products';
import { ProductDetail } from './pages/ProductDetail';
import { Wishlist } from './pages/Wishlist';
import { Cart } from './pages/Cart';
import { Checkout } from './pages/Checkout';
import { BecomeSeller } from './pages/BecomeSeller';
import { SellerRegister } from './pages/SellerRegister';
import { SellerProfile } from './pages/SellerProfile';
import { SellerDashboard } from './pages/SellerDashboard';
import { Account } from './pages/Account';
import { BlogList } from './pages/BlogList';
import { BlogDetail } from './pages/BlogDetail';
import { About } from './pages/About';
import { Contact } from './pages/Contact';
import { FAQ } from './pages/FAQ';
import { ShippingPolicy } from './pages/ShippingPolicy';
import { ReturnsPolicy } from './pages/ReturnsPolicy';
import { PrivacyPolicy } from './pages/PrivacyPolicy';
import { TermsConditions } from './pages/TermsConditions';
import { NotFound } from './pages/NotFound';

// Admin Pages
import { AdminDashboard } from './pages/admin/AdminDashboard';
import { SiteCustomization } from './pages/admin/SiteCustomization';
import { BlogManagement } from './pages/admin/BlogManagement';
import { SeoSettings } from './pages/admin/SeoSettings';
import { SecuritySettings } from './pages/admin/SecuritySettings';

export function App() {
  return (
    <DemoModalProvider>
      <SiteConfigProvider>
        <CartWishlistProvider>
          <BrowserRouter>
            <Routes>
              <Route path="/" element={<Layout />}>
                <Route index element={<Home />} />
                
                {/* Product Catalog & Discovery */}
                <Route path="products" element={<Products />} />
                <Route path="category/:categorySlug" element={<Products />} />
                <Route path="search" element={<Products />} />
                <Route path="product/:slug" element={<ProductDetail />} />

                {/* Shopping Experience */}
                <Route path="wishlist" element={<Wishlist />} />
                <Route path="cart" element={<Cart />} />
                <Route path="checkout" element={<Checkout />} />
                <Route path="account" element={<Account />} />

                {/* Seller & Artisan Experience */}
                <Route path="become-seller" element={<BecomeSeller />} />
                <Route path="seller-register" element={<SellerRegister />} />
                <Route path="seller/:sellerSlug" element={<SellerProfile />} />
                <Route path="seller/dashboard" element={<SellerDashboard />} />

                {/* Village Journal & Blog */}
                <Route path="blog" element={<BlogList />} />
                <Route path="blog/category/:slug" element={<BlogList />} />
                <Route path="blog/:slug" element={<BlogDetail />} />

                {/* Information & Policies */}
                <Route path="about" element={<About />} />
                <Route path="contact" element={<Contact />} />
                <Route path="faq" element={<FAQ />} />
                <Route path="shipping-policy" element={<ShippingPolicy />} />
                <Route path="returns-policy" element={<ReturnsPolicy />} />
                <Route path="privacy-policy" element={<PrivacyPolicy />} />
                <Route path="terms-conditions" element={<TermsConditions />} />

                {/* Admin Management Previews */}
                <Route path="admin" element={<AdminDashboard />} />
                <Route path="admin/site-customization" element={<SiteCustomization />} />
                <Route path="admin/blog" element={<BlogManagement />} />
                <Route path="admin/seo-settings" element={<SeoSettings />} />
                <Route path="admin/security-settings" element={<SecuritySettings />} />

                {/* Catch-all 404 */}
                <Route path="404" element={<NotFound />} />
                <Route path="*" element={<NotFound />} />
              </Route>
            </Routes>
          </BrowserRouter>
        </CartWishlistProvider>
      </SiteConfigProvider>
    </DemoModalProvider>
  );
}

export default App;
