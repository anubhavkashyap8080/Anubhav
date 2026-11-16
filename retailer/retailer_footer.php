<?php
/**
 * GanvGaliHat Retailer Hub (B2B Wholesale) Layout Footer
 */
?>
  </main>

  <!-- Retailer B2B Footer -->
  <footer class="bg-stone-900 text-stone-400 border-t border-stone-800 text-xs py-8 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pb-6 border-b border-stone-800">
        
        <div>
          <div class="flex items-center gap-2 mb-2 text-white font-black text-sm">
            <i class="fa-solid fa-boxes-packing text-amber-500"></i>
            GanvGaliHat B2B Wholesale Hub
          </div>
          <p class="text-stone-400 text-xs leading-relaxed">
            <span class="lang-en">Sourcing directly from India's rural artisans, village clusters, and craft cooperatives to maximize your retail shop margins.</span>
            <span class="lang-hi hidden">भारत के ग्रामीण कारीगरों और ग्रामोद्योग क्लस्टरों से सीधे थोक माल खरीदकर अपनी दुकान का मुनाफा बढ़ाएं।</span>
          </p>
        </div>

        <div>
          <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-2">
            <span class="lang-en">Wholesale Desk & Support</span>
            <span class="lang-hi hidden">थोक सहायता एवं हेल्पडेस्क</span>
          </h4>
          <p class="text-xs text-stone-400">Phone / WhatsApp: <strong class="text-amber-400">+91 94540 50000</strong></p>
          <p class="text-xs text-stone-400 mt-1">B2B Email: <span class="text-stone-300">b2b@ganvgalihat.com</span></p>
          <p class="text-[11px] text-stone-500 mt-1">Monday – Saturday: 9:00 AM to 8:00 PM</p>
        </div>

        <div>
          <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-2">
            <span class="lang-en">Retailer Advantages</span>
            <span class="lang-hi hidden">व्यापारी सुविधाएं</span>
          </h4>
          <ul class="space-y-1 text-xs">
            <li><i class="fa-solid fa-check text-emerald-500 mr-1.5"></i> 100% Verified GST Tax Invoices</li>
            <li><i class="fa-solid fa-check text-emerald-500 mr-1.5"></i> Insured Doorstep Cargo & Transport</li>
            <li><i class="fa-solid fa-check text-emerald-500 mr-1.5"></i> Tier 2 Volume Bulk Discounts</li>
            <li><i class="fa-solid fa-check text-emerald-500 mr-1.5"></i> Authentic ODOP & SHG Certified Lots</li>
          </ul>
        </div>

        <div>
          <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-2">
            <span class="lang-en">Quick Links</span>
            <span class="lang-hi hidden">त्वरित लिंक्स</span>
          </h4>
          <div class="flex flex-col gap-1.5 text-xs">
            <a href="/retailer/products.php" class="hover:text-amber-400 transition">&rarr; Wholesale Catalog</a>
            <a href="/retailer/orders.php" class="hover:text-amber-400 transition">&rarr; Bulk Orders & GST Invoices</a>
            <a href="/retailer/profile.php" class="hover:text-amber-400 transition">&rarr; Godown & Tax Details</a>
            <a href="/terms.php" target="_blank" class="hover:text-amber-400 transition">&rarr; B2B Terms & Conditions</a>
          </div>
        </div>

      </div>

      <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-stone-500">
        <div>
          &copy; <?= date('Y') ?> <strong class="text-stone-300">GanvGaliHat</strong> • India's Dedicated Rural Marketplace. All rights reserved.
        </div>
        <div class="flex items-center gap-4">
          <span class="flex items-center gap-1"><i class="fa-solid fa-lock text-emerald-500"></i> 256-Bit SSL Encrypted B2B</span>
          <span class="flex items-center gap-1"><i class="fa-solid fa-shield-check text-amber-500"></i> GST Compliant Invoicing</span>
        </div>
      </div>
    </div>
  </footer>

</body>
</html>
