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
            GanvGaliHat B2B Retailer Hub
          </div>
          <p class="text-stone-400 text-xs leading-relaxed">
            भारत के ग्रामीण कारीगरों और ग्रामोद्योग क्लस्टरों से सीधे थोक माल खरीदकर अपनी दुकान का मुनाफा बढ़ाएं।
          </p>
        </div>

        <div>
          <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-2">थोक सहायता एवं हेल्पडेस्क</h4>
          <p class="text-xs text-stone-400">फोन / व्हाट्सएप सहायता: <strong class="text-amber-400">+91 94540 50000</strong></p>
          <p class="text-xs text-stone-400 mt-1">B2B ईमेल: <span class="text-stone-300">b2b@ganvgalihat.com</span></p>
          <p class="text-[11px] text-stone-500 mt-1">सोमवार से शनिवार: प्रातः 9:00 से सायं 8:00 तक</p>
        </div>

        <div>
          <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-2">व्यापारी सुविधाएं</h4>
          <ul class="space-y-1 text-xs">
            <li><i class="fa-solid fa-check text-emerald-500 mr-1.5"></i> 100% पक्का GST टैक्स इनवॉइस</li>
            <li><i class="fa-solid fa-check text-emerald-500 mr-1.5"></i> सुरक्षित डोरस्टेप ट्रांसपोर्ट व पार्सल</li>
            <li><i class="fa-solid fa-check text-emerald-500 mr-1.5"></i> टियर 2 बल्क वॉल्यूम अतिरिक्त छूट</li>
            <li><i class="fa-solid fa-check text-emerald-500 mr-1.5"></i> सत्यापित ODOP व स्वयं सहायता समूह माल</li>
          </ul>
        </div>

        <div>
          <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-2">त्वरित लिंक्स</h4>
          <div class="flex flex-col gap-1.5 text-xs">
            <a href="/retailer/products.php" class="hover:text-amber-400 transition">&rarr; थोक कैटलॉग (Wholesale Catalog)</a>
            <a href="/retailer/orders.php" class="hover:text-amber-400 transition">&rarr; ऑर्डर स्थिति एवं GST बिल</a>
            <a href="/retailer/profile.php" class="hover:text-amber-400 transition">&rarr; गोदाम व टैक्स विवरण</a>
            <a href="/terms.php" target="_blank" class="hover:text-amber-400 transition">&rarr; B2B नियम व शर्तें</a>
          </div>
        </div>

      </div>

      <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-stone-500">
        <div>
          &copy; <?= date('Y') ?> <strong class="text-stone-300">GanvGaliHat</strong> (गावंगलीहाट) • India's Dedicated Rural Marketplace. सर्वाधिकार सुरक्षित।
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
