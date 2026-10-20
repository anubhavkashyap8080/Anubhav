import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { Layout } from './components/layout/Layout';
import { AuthProvider } from './context/AuthContext';
import { PlatformProvider } from './context/PlatformContext';
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
import { NearbyArtisans } from './pages/NearbyArtisans';

// Admin Pages
import { AdminDashboard } from './pages/admin/AdminDashboard';
import { SiteCustomization } from './pages/admin/SiteCustomization';
import { BlogManagement } from './pages/admin/BlogManagement';
import { SeoSettings } from './pages/admin/SeoSettings';
import { SecuritySettings } from './pages/admin/SecuritySettings';
import { AdminLayout } from './components/admin/AdminLayout';
import { ErrorBoundary } from './components/common/ErrorBoundary';

export function App() {
  return (
    <ErrorBoundary>
      <AuthProvider>
        <PlatformProvider>
          <SiteConfigProvider>
            <CartWishlistProvider>
              <DemoModalProvider>
                <BrowserRouter>
                  <Routes>
                    {/* Public Customer Storefront Routes (Customer Header, Navigation & Footer) */}
                    <Route path="/" element={<Layout />}>
                      <Route index element={<Home />} />
                      
                      {/* Product Catalog & Discovery */}
                      <Route path="products" element={<Products />} />
                      <Route path="category/:categorySlug" element={<Products />} />
                      <Route path="search" element={<Products />} />
                      <Route path="product/:slug" element={<ProductDetail />} />
                      <Route path="nearby-artisans" element={<NearbyArtisans />} />

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

                      {/* Catch-all 404 for storefront */}
                      <Route path="404" element={<NotFound />} />
                      <Route path="*" element={<NotFound />} />
                    </Route>

                    {/* Dedicated Enterprise Admin Console (NO Marketplace Header or Footer!) */}
                    <Route path="/admin" element={<AdminLayout />}>
                      <Route index element={<AdminDashboard />} />
                      <Route path="site-customization" element={<SiteCustomization />} />
                      <Route path="blog" element={<BlogManagement />} />
                      <Route path="seo-settings" element={<SeoSettings />} />
                      <Route path="security-settings" element={<SecuritySettings />} />
                    </Route>
                  </Routes>
                </BrowserRouter>
              </DemoModalProvider>
            </CartWishlistProvider>
          </SiteConfigProvider>
        </PlatformProvider>
      </AuthProvider>
    </ErrorBoundary>
  );
}

export default App;
