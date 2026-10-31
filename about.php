<?php
/**
 * GaunGaliHat About Us & Heritage Page
 * Kalyani agrofood pvt ltd Enterprise • Rural Artisan Hub, Hand-Forged Tools, Unpolished Pulses & Women SHG Empowerment
 */

$page_title = 'हमारे बारे में (About Kalyani agrofood pvt ltd & GaunGaliHat)';
$page_desc = 'गाँव, गली और हाट की धरोहर — कल्याणी एग्रोफूड प्रा. लि. (Kalyani agrofood pvt ltd) द्वारा संचालित हरदोई, उत्तर प्रदेश के ग्रामीण लोहारों, कुटीर कारीगरों और महिला स्वयं सहायता समूहों का सशक्त मंच।';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="bg-stone-100 border-b border-stone-200 py-3">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs text-gray-500 flex items-center gap-2">
    <a href="/" class="hover:text-emerald-800 font-medium">होम (Home)</a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
    <span class="text-stone-900 font-bold">हमारे बारे में (About Us)</span>
  </div>
</div>

<!-- 1. Hero Story Section with Village Backdrop & Theme Gradient -->
<section class="relative bg-gradient-to-br from-emerald-950 via-[#1b4332] to-stone-900 text-white py-14 sm:py-20 overflow-hidden border-b border-emerald-900/50">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
  <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-amber-500/20 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-5">
    
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black uppercase tracking-wider border border-amber-400/30 shadow-xs">
      <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
      <span>Kalyani agrofood pvt ltd • स्वदेशी ग्रामीण धरोहर</span>
    </div>

    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black font-display tracking-tight text-white leading-tight max-w-4xl mx-auto">
      गाँव, गली और हाट की धरोहर — <span class="text-amber-400 drop-shadow-sm">सीधे आपके घर तक</span>
    </h1>

    <p class="text-xs sm:text-base text-stone-300 max-w-3xl mx-auto leading-relaxed">
      हरदोई, उत्तर प्रदेश के ग्रामीण लोहारों, कुटीर कारीगरों, दाल उत्पादक किसानों और स्वयं सहायता समूहों का सशक्त डिजिटल मंच। बिचौलियों से मुक्त, शुद्ध स्वदेशी उत्पाद और अटूट कारीगरी का भरोसा।
    </p>

    <div class="flex flex-wrap items-center justify-center gap-3 pt-3">
      <a href="/products.php" class="px-6 py-3 bg-brand-orange hover:bg-orange-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg transition active:scale-95 flex items-center gap-2">
        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
        <span>Explore Marketplace (उत्पाद देखें)</span>
      </a>
      <a href="/artisans.php" class="px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2">
        <i data-lucide="map-pin" class="w-4 h-4 text-amber-400"></i>
        <span>Locate Nearby Artisans (निकटतम कारीगर)</span>
      </a>
    </div>

  </div>
</section>

<!-- Main Story & Values Section -->
<main class="bg-stone-50 py-12 sm:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

    <!-- 2. Mission Statement 2-Column Showcase -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center bg-white p-6 sm:p-12 rounded-3xl border border-gray-200 shadow-sm">
      
      <div class="lg:col-span-7 space-y-5">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-brand-green border border-emerald-200 text-xs font-bold uppercase tracking-wider">
          <i data-lucide="target" class="w-3.5 h-3.5 text-brand-green"></i>
          <span>हमारा संकल्प (Our Core Mission)</span>
        </div>

        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 font-display leading-tight">
          पारंपरिक कुटीर कला एवं स्वदेशी औजारों को राष्ट्रीय एवं वैश्विक पहचान
        </h2>

        <div class="space-y-3 text-xs sm:text-sm text-gray-600 leading-relaxed">
          <p>
            <strong>कल्याणी एग्रोफूड प्रा. लि. (Kalyani agrofood pvt ltd)</strong> द्वारा संचालित <em>'गाँव गली हाट'</em> का मुख्य उद्देश्य ग्रामीण क्षेत्रों के हुनरमंद कारीगरों को सशक्त बनाना और उनके उत्पादों को बिना किसी बिचौलिए के सीधे उपभोक्ताओं तक पहुँचाना है।
          </p>
          <p>
            हमारा विश्वास है कि भारत की असली ताकत उसकी मिट्टी, उसकी पारंपरिक भट्टियों और गाँव की पगडंडियों में बसती है। हम हर कारीगर को उसकी कड़ी मेहनत का 100% सही और पारदर्शी मूल्य दिलाते हैं।
          </p>
        </div>

        <!-- 4 Bullet Highlights -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          <div class="p-3 bg-stone-50 rounded-2xl border border-gray-100 flex items-start gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 text-sm font-bold">🔨</span>
            <div>
              <h4 class="font-bold text-xs text-gray-900">पत्ती स्प्रिंग लोहारी</h4>
              <p class="text-[11px] text-gray-500">अटूट मजबूत हंसिया, खुरपी व कुल्हाड़ी</p>
            </div>
          </div>

          <div class="p-3 bg-stone-50 rounded-2xl border border-gray-100 flex items-start gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 text-sm font-bold">🌾</span>
            <div>
              <h4 class="font-bold text-xs text-gray-900">100% अपोलिश दालें</h4>
              <p class="text-[11px] text-gray-500">पत्थर चक्की से निर्मित शुद्ध देसी अनाज</p>
            </div>
          </div>

          <div class="p-3 bg-stone-50 rounded-2xl border border-gray-100 flex items-start gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-red-100 text-red-800 flex items-center justify-center shrink-0 text-sm font-bold">🫙</span>
            <div>
              <h4 class="font-bold text-xs text-gray-900">बनारसी देसी अचार</h4>
              <p class="text-[11px] text-gray-500">कच्ची घानी सरसों तेल में पारंपरिक स्वाद</p>
            </div>
          </div>

          <div class="p-3 bg-stone-50 rounded-2xl border border-gray-100 flex items-start gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-800 flex items-center justify-center shrink-0 text-sm font-bold">🛍️</span>
            <div>
              <h4 class="font-bold text-xs text-gray-900">महिला SHG आजीविका</h4>
              <p class="text-[11px] text-gray-500">120+ ग्रामीण बहनों द्वारा निर्मित जूट बैग</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Workshop Visual Showcase -->
      <div class="lg:col-span-5 relative">
        <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-amber-500/20 aspect-4/3 sm:aspect-square">
          <img
            src="https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1000&q=80"
            alt="Traditional Blacksmith Forge Kalyani agrofood pvt ltd"
            class="w-full h-full object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
          
          <div class="absolute bottom-5 left-5 right-5 text-white space-y-1">
            <span class="inline-block px-2.5 py-0.5 rounded-md bg-amber-500 text-stone-950 text-[10px] font-black uppercase tracking-wider">
              नेवादा, हरदोई (Hardoi Works)
            </span>
            <h4 class="font-black text-sm sm:text-base text-white">
              कल्याणी एग्रोफूड (Kalyani agrofood pvt ltd) पारंपरिक लोहारी भट्टी
            </h4>
            <p class="text-[11px] text-stone-300">
              भट्टी में तपाकर और पानी में बुझाकर तैयार अटूट कृषि औजार
            </p>
          </div>
        </div>

        <!-- Floating Stat Badge -->
        <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl p-3.5 border border-gray-200 shadow-xl hidden sm:flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-lg">
            <i data-lucide="truck" class="w-5 h-5 text-emerald-700"></i>
          </div>
          <div>
            <div class="text-xs font-black text-gray-900">Speed Post Dispatch</div>
            <div class="text-[10px] text-emerald-700 font-bold">24-48 घंटे में रवानगी</div>
          </div>
        </div>
      </div>

    </div>

    <!-- 3. The 4 Pillars of GaunGaliHat -->
    <div class="space-y-6">
      <div class="text-center max-w-2xl mx-auto space-y-1.5">
        <span class="text-xs font-black text-amber-700 uppercase tracking-wider">
          हमारे आधार स्तंभ (Our Foundation)
        </span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display">
          चार मजबूत स्तंभ जिन पर टिका है गाँव गली हाट
        </h2>
        <p class="text-xs text-gray-500">
          हर उत्पाद के पीछे हमारी प्रामाणिकता और कारीगरों के प्रति निष्ठा का संकल्प
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-xs hover:shadow-lg transition-all duration-300 text-center space-y-3 group">
          <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center mx-auto text-2xl group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-hammer"></i>
          </div>
          <h3 class="font-bold text-sm text-gray-900">हस्तनिर्मित व टिकाऊ</h3>
          <p class="text-xs text-gray-500 leading-relaxed">
            हर कृषि औजार पारंपरिक भट्टी में तपाकर, हथौड़े से पीटकर और पानी में बुझाकर अटूट मजबूती के साथ तैयार किया जाता है।
          </p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-xs hover:shadow-lg transition-all duration-300 text-center space-y-3 group">
          <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-brand-green flex items-center justify-center mx-auto text-2xl group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-wheat-awn"></i>
          </div>
          <h3 class="font-bold text-sm text-gray-900">100% शुद्ध व अपोलिश</h3>
          <p class="text-xs text-gray-500 leading-relaxed">
            किसान उत्पादक संगठनों की देसी दालें, तेल व मसाले बिना किसी केमिकल, कृत्रिम रंग या पॉलिश के सीधे प्राकृतिक रूप में।
          </p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-xs hover:shadow-lg transition-all duration-300 text-center space-y-3 group">
          <div class="w-14 h-14 rounded-2xl bg-orange-100 text-brand-orange flex items-center justify-center mx-auto text-2xl group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-truck-fast"></i>
          </div>
          <h3 class="font-bold text-sm text-gray-900">भारतीय डाक साझेदारी</h3>
          <p class="text-xs text-gray-500 leading-relaxed">
            हरदोई प्रधान डाकघर से सीधा स्पीड पोस्ट और पार्सल सेवा द्वारा देश के हर गाँव और शहर (19,000+ पिनकोड) तक सुरक्षित होम डिलीवरी।
          </p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-xs hover:shadow-lg transition-all duration-300 text-center space-y-3 group">
          <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center mx-auto text-2xl group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-handshake-angle"></i>
          </div>
          <h3 class="font-bold text-sm text-gray-900">महिला SHG सशक्तिकरण</h3>
          <p class="text-xs text-gray-500 leading-relaxed">
            पर्यावरण-अनुकूल जूट झोला निर्माण, हाथ की बुनाई एवं अचार पैकेजिंग से 120+ ग्रामीण बहनों को नियमित आत्मनिर्भर आजीविका।
          </p>
        </div>

      </div>
    </div>

    <!-- 4. Impact Statistics Counters -->
    <div class="bg-gradient-to-r from-emerald-950 via-[#133827] to-stone-900 text-white rounded-3xl p-8 sm:p-12 shadow-xl border border-emerald-900/40">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center divide-y sm:divide-y-0 sm:divide-x divide-white/10">
        
        <div class="p-3 space-y-1">
          <div class="text-3xl sm:text-4xl lg:text-5xl font-black text-amber-400 font-display">40+</div>
          <div class="text-xs font-bold text-white">कारीगर कार्यशालाएं</div>
          <div class="text-[11px] text-stone-300">Village Artisan Clusters</div>
        </div>

        <div class="p-3 space-y-1">
          <div class="text-3xl sm:text-4xl lg:text-5xl font-black text-amber-400 font-display">120+</div>
          <div class="text-xs font-bold text-white">सशक्त ग्रामीण बहनें</div>
          <div class="text-[11px] text-stone-300">Women SHG Members</div>
        </div>

        <div class="p-3 space-y-1">
          <div class="text-3xl sm:text-4xl lg:text-5xl font-black text-amber-400 font-display">100%</div>
          <div class="text-xs font-bold text-white">बिचौलिया-मुक्त भुगतान</div>
          <div class="text-[11px] text-stone-300">Direct Fair Remittance</div>
        </div>

        <div class="p-3 space-y-1">
          <div class="text-3xl sm:text-4xl lg:text-5xl font-black text-amber-400 font-display">19,000+</div>
          <div class="text-xs font-bold text-white">पिनकोड पर डिलीवरी</div>
          <div class="text-[11px] text-stone-300">All-India India Post Reach</div>
        </div>

      </div>
    </div>

    <!-- 5. Manufacturing Facilities & Corporate Locations -->
    <div class="bg-white p-8 sm:p-12 rounded-3xl border border-gray-200 shadow-sm space-y-6">
      <div class="text-center max-w-xl mx-auto space-y-1">
        <span class="text-xs font-black text-emerald-800 uppercase tracking-wider">
          हमारे केंद्र (Our Operational Units)
        </span>
        <h3 class="text-xl sm:text-2xl font-black text-gray-900 font-display">
          एस. एस. इंडस्ट्रीज — निर्माण एवं प्रशासनिक केंद्र
        </h3>
        <p class="text-xs text-gray-500">
          उत्तर प्रदेश स्थित हमारे प्राथमिक विनिर्माण एवं केंद्रीय लॉजिस्टिक्स हब
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-gray-700">
        
        <!-- Nevada Works -->
        <div class="p-6 rounded-2xl bg-stone-50 border border-gray-200 space-y-3 hover:border-amber-500 transition-colors">
          <div class="flex items-center gap-2 font-bold text-sm text-stone-900">
            <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center">
              <i class="fa-solid fa-industry"></i>
            </span>
            <span>कारखाना एवं निर्माण इकाई (Manufacturing Works)</span>
          </div>
          <p class="text-gray-600 leading-relaxed font-medium">
            माधौगंज बघौली रोड, नेवादा, जिला - हरदोई, उत्तर प्रदेश - 241126
          </p>
          <div class="pt-2 border-t border-gray-200/60 text-[11px] space-y-1 text-gray-500">
            <p><strong>प्राथमिक कार्य:</strong> कृषि उपकरण, पत्ती स्प्रिंग लोहारी भट्टी, हस्तशिल्प पैकेजिंग केंद्र</p>
            <p><strong>लॉजिस्टिक्स:</strong> हरदोई मुख्य डाकघर स्पीड पोस्ट नोड</p>
          </div>
        </div>

        <!-- Sandila Hub -->
        <div class="p-6 rounded-2xl bg-stone-50 border border-gray-200 space-y-3 hover:border-indigo-500 transition-colors">
          <div class="flex items-center gap-2 font-bold text-sm text-stone-900">
            <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center">
              <i class="fa-solid fa-building"></i>
            </span>
            <span>प्रधान कार्यालय एवं औद्योगिक केंद्र (Head Office & Hub)</span>
          </div>
          <p class="text-gray-600 leading-relaxed font-medium">
            G145 Phase 2 Industrial Area, UPSIDA Sandila, जिला - हरदोई, उत्तर प्रदेश - 241204
          </p>
          <div class="pt-2 border-t border-gray-200/60 text-[11px] space-y-1 text-gray-500">
            <p><strong>प्राथमिक कार्य:</strong> गुणवत्ता परीक्षण, अखिल भारतीय लॉजिस्टिक्स एवं तकनीकी संचालन</p>
            <p><strong>संपर्क:</strong> namaste@ganvgalihat.com • +91 7897492100</p>
          </div>
        </div>

      </div>
    </div>

    <!-- 6. Trust & Heritage Guarantees -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-2xl border border-gray-200 text-center space-y-1">
        <i data-lucide="shield-check" class="w-6 h-6 text-emerald-700 mx-auto"></i>
        <h5 class="text-xs font-bold text-gray-900">100% प्रामाणिक स्वदेशी</h5>
        <p class="text-[10px] text-gray-500">सीधा गाँव के कारीगरों से</p>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-gray-200 text-center space-y-1">
        <i data-lucide="package" class="w-6 h-6 text-brand-orange mx-auto"></i>
        <h5 class="text-xs font-bold text-gray-900">सुरक्षित पार्सल पैकिंग</h5>
        <p class="text-[10px] text-gray-500">टूट-फूट मुक्त सुरक्षा</p>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-gray-200 text-center space-y-1">
        <i data-lucide="headset" class="w-6 h-6 text-indigo-600 mx-auto"></i>
        <h5 class="text-xs font-bold text-gray-900">कारीगर सहायता डेस्क</h5>
        <p class="text-[10px] text-gray-500">व्हाट्सएप व कॉल सपोर्ट</p>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-gray-200 text-center space-y-1">
        <i data-lucide="credit-card" class="w-6 h-6 text-amber-600 mx-auto"></i>
        <h5 class="text-xs font-bold text-gray-900">कैश ऑन डिलीवरी (COD)</h5>
        <p class="text-[10px] text-gray-500">घर पर उत्पाद देखकर भुगतान</p>
      </div>
    </div>

    <!-- 7. Call to Action Banner -->
    <div class="bg-gradient-to-r from-amber-600 to-amber-700 text-white rounded-3xl p-8 sm:p-10 shadow-lg text-center space-y-4">
      <h3 class="text-2xl sm:text-3xl font-black font-display text-white">
        ग्रामीण भारत के हुनर को अपने घर लाएं
      </h3>
      <p class="text-xs sm:text-sm text-amber-100 max-w-xl mx-auto leading-relaxed">
        आपके द्वारा खरीदा गया प्रत्येक उत्पाद किसी गाँव के लोहार, किसान या कुटीर कारीगर परिवार के चेहरे पर मुस्कान लाता है।
      </p>
      <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
        <a href="/products.php" class="px-6 py-2.5 bg-stone-950 hover:bg-black text-white font-bold text-xs rounded-xl shadow transition">
          मार्केटप्लेस एक्सप्लोर करें (Shop Now)
        </a>
        <a href="/artisans.php" class="px-6 py-2.5 bg-white text-stone-900 hover:bg-stone-100 font-bold text-xs rounded-xl shadow transition">
          निकटतम कारीगर खोजें (Nearby Artisans)
        </a>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
