<?php
/**
 * GanvGaliHat About Us & Heritage Page
 * Dedicated Rural Marketplace • ODOP Artisans, Women SHGs, MSMEs & Local Producers
 * Founder: Dheerendra Shukla
 */

$page_title = 'About Us — India\'s Dedicated Rural Marketplace | GanvGaliHat';
$page_desc = 'www.GanvGaliHat.com is India\'s dedicated rural marketplace, taking authentic village products to every home across India. ODOP artisans, Women SHGs, and local makers under one roof.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs & Language Switcher Bar -->
<div class="bg-stone-100 border-b border-stone-200 py-3">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
    <div class="flex items-center gap-2 text-gray-500">
      <a href="/" class="hover:text-emerald-800 font-medium">
        <span class="lang-en">Home</span>
        <span class="lang-hi hidden">होम</span>
      </a>
      <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
      <span class="text-stone-900 font-bold">
        <span class="lang-en">About Us</span>
        <span class="lang-hi hidden">हमारे बारे में</span>
      </span>
    </div>

    <!-- Bilingual Switcher Toggle (English Default) -->
    <div class="flex items-center gap-2">
      <span class="text-[11px] font-bold text-gray-400">Language:</span>
      <?= render_bilingual_toggle('en') ?>
    </div>
  </div>
</div>

<!-- 1. Hero Story Section with Village Backdrop & Theme Gradient -->
<section class="relative bg-gradient-to-br from-emerald-950 via-[#064e3b] to-stone-950 text-white py-12 sm:py-16 overflow-hidden border-b border-emerald-900/50">
  <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
  <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-amber-500/20 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-emerald-400/10 blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-4">
    
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400/20 text-amber-300 text-xs font-black uppercase tracking-wider border border-amber-400/30 shadow-xs">
      <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
      <span class="lang-en">Atmanirbhar Bharat • Vocal for Local</span>
      <span class="lang-hi hidden">आत्मनिर्भर भारत • वोकल फॉर लोकल</span>
    </div>

    <!-- ENGLISH HERO HEADLINE -->
    <div class="lang-en space-y-3">
      <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black font-display tracking-tight text-white leading-tight max-w-4xl mx-auto">
        Taking the Authentic Products of Our Villages <span class="text-amber-400 drop-shadow-sm">to Every Home in India</span>
      </h1>
      <p class="text-xs sm:text-base text-stone-200 max-w-3xl mx-auto leading-relaxed">
        <strong>www.GanvGaliHat.com</strong> is India's dedicated rural marketplace, built with a simple dream — to take the products of our villages to every home in the country.
      </p>
    </div>

    <!-- HINDI HERO HEADLINE -->
    <div class="lang-hi hidden space-y-3">
      <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black font-display tracking-tight text-white leading-tight max-w-4xl mx-auto">
        गाँव के हुनर और मिट्टी की खुशबू को <span class="text-amber-400 drop-shadow-sm">देश के हर घर तक पहुँचाने का संकल्प</span>
      </h1>
      <p class="text-xs sm:text-base text-stone-200 max-w-3xl mx-auto leading-relaxed">
        <strong>www.GanvGaliHat.com</strong> भारत का समर्पित ग्रामीण मार्केटप्लेस है, जो एक सीधे और पावन सपने के साथ बना है — हमारे गाँवों के हुनरमंद उत्पादों को देश के हर घर तक पहुँचाना।
      </p>
    </div>

    <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
      <a href="/products.php" class="px-5 py-3 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs sm:text-sm rounded-xl shadow-lg transition active:scale-95 flex items-center gap-2">
        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
        <span class="lang-en">Shop Rural Products</span>
        <span class="lang-hi hidden">ग्रामीण उत्पाद खरीदें</span>
      </a>
      <a href="/artisans.php" class="px-5 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs sm:text-sm rounded-xl transition flex items-center gap-2">
        <i data-lucide="users" class="w-4 h-4 text-emerald-400"></i>
        <span class="lang-en">Meet Verified Artisans</span>
        <span class="lang-hi hidden">सत्यापित कारीगरों से मिलें</span>
      </a>
    </div>

  </div>
</section>

<!-- Main Story & Values Section -->
<main class="bg-stone-50 py-12 sm:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16">

    <!-- 2. The Core Mission & The Bridge -->
    <div class="bg-white p-6 sm:p-12 rounded-3xl border border-gray-200 shadow-xs">
      
      <!-- ENGLISH CONTENT -->
      <div class="lang-en space-y-6">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold uppercase tracking-wider">
          <i data-lucide="target" class="w-3.5 h-3.5 text-emerald-700"></i>
          <span>Our Origin & Purpose</span>
        </div>

        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 font-display leading-tight">
          Bridging the Gap Between Rural Artisans & National Consumers
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs sm:text-sm text-gray-600 leading-relaxed">
          <div class="space-y-4">
            <p>
              We saw that our villages have no shortage of skill. Every district of India has its own unique product — pure spices, traditional sun-cured pickles, handloom textiles, handicrafts, and handcrafted agricultural tools. Everything is pure, natural, and authentic.
            </p>
            <p>
              But our local producers never get the right price due to middlemen and lack of modern market access. Generational craftspeople and smallholders are often forced to sell at minimal margins while consumers in cities pay high prices for diluted alternatives.
            </p>
            <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl font-bold text-sm text-amber-950">
              ⚡ GanvGaliHat bridges this gap.
            </div>
          </div>

          <div class="space-y-4">
            <p>
              We bring <strong>ODOP (One District One Product) artisans, Women-led SHGs, MSMEs, and All Local Producers & Sellers from All Over India</strong> onto a single digital platform, giving them a direct national market.
            </p>
            <p>
              We are proud to bring you <strong>All Local Products in India</strong> under one roof — ensuring that every Indian household can access pure, unadulterated village produce with full transparency.
            </p>
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-600 rounded-r-2xl text-xs font-semibold text-emerald-900">
              🌿 100% Direct Fair Remittance directly into artisan & SHG bank accounts.
            </div>
          </div>
        </div>
      </div>

      <!-- HINDI CONTENT -->
      <div class="lang-hi hidden space-y-6">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold uppercase tracking-wider">
          <i data-lucide="target" class="w-3.5 h-3.5 text-emerald-700"></i>
          <span>हमारा संकल्प एवं उद्देश्य</span>
        </div>

        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 font-display leading-tight">
          ग्रामीण शिल्पकारों और राष्ट्रीय उपभोक्ताओं के बीच की दूरी को मिटाना
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs sm:text-sm text-gray-600 leading-relaxed">
          <div class="space-y-4">
            <p>
              हमने देखा कि हमारे गाँवों में हुनर की कोई कमी नहीं है। भारत के हर जिले का अपना विशिष्ट उत्पाद है — शुद्ध मसाले, पारंपरिक देसी अचार, हथकरघा वस्त्र, हस्तशिल्प और हाथ से गढ़े कृषि औजार। सब कुछ शुद्ध और प्रामाणिक है।
            </p>
            <p>
              लेकिन हमारे स्थानीय उत्पादकों को बिचौलियों और बाजार तक सीधी पहुँच न होने के कारण कभी सही दाम नहीं मिल पाता। पीढ़ियों से काम कर रहे कारीगरों को उनकी मेहनत का पूरा मूल्य नहीं मिल पाता।
            </p>
            <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl font-bold text-sm text-amber-950">
              ⚡ गाँव गली हाट (GanvGaliHat) इसी अंतर को पाटता है।
            </div>
          </div>

          <div class="space-y-4">
            <p>
              हम <strong>ओडीओपी (ODOP - एक जिला एक उत्पाद) कारीगरों, महिला स्वयं सहायता समूहों (SHG), एमएसएमई (MSME) और पूरे भारत के सभी स्थानीय उत्पादकों व विक्रेताओं</strong> को एक ही डिजिटल मंच पर लाते हैं, जिससे उन्हें सीधा राष्ट्रीय बाजार मिलता है।
            </p>
            <p>
              हमें पूरे भारत के स्थानीय उत्पादों को एक छत के नीचे लाने पर गर्व है — ताकि देश का हर परिवार शुद्ध और प्राकृतिक उत्पाद सीधे गाँव से प्राप्त कर सके।
            </p>
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-600 rounded-r-2xl text-xs font-semibold text-emerald-900">
              🌿 100% बिचौलिया-मुक्त भुगतान सीधे कारीगरों एवं महिला SHG बहनों के बैंक खाते में।
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- 3. What We Do - 3 Core Pillars -->
    <div class="space-y-6">
      <div class="text-center max-w-2xl mx-auto space-y-1.5">
        <span class="text-xs font-black text-emerald-800 uppercase tracking-wider">
          <span class="lang-en">Our Operating Model</span>
          <span class="lang-hi hidden">हमारी कार्यप्रणाली</span>
        </span>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 font-display">
          <span class="lang-en">What We Do</span>
          <span class="lang-hi hidden">हम क्या करते हैं</span>
        </h2>
        <p class="text-xs text-gray-500">
          <span class="lang-en">Three pillars of transparency, authenticity, and rural empowerment</span>
          <span class="lang-hi hidden">पारदर्शिता, प्रामाणिकता और ग्रामीण सशक्तिकरण के तीन आधार स्तंभ</span>
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Pillar 1 -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-xs hover:border-emerald-600 hover:shadow-md transition">
          <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black text-xl mb-4">
            1
          </div>
          <h3 class="font-black text-base text-gray-900 mb-2">
            <span class="lang-en">Direct From Village Clusters</span>
            <span class="lang-hi hidden">गाँव क्लस्टर्स से सीधा जुड़ाव</span>
          </h3>
          <p class="text-xs text-gray-600 leading-relaxed">
            <span class="lang-en">We source directly from Village Clusters, SHG groups and local sellers across India, with zero middlemen. Every product travels straight from rural workshops to your doorstep.</span>
            <span class="lang-hi hidden">हम पूरे भारत के ग्रामीण क्लस्टरों, महिला SHG समूहों और स्थानीय विक्रेताओं से बिना किसी बिचौलिए के सीधे उत्पाद प्राप्त करते हैं।</span>
          </p>
        </div>

        <!-- Pillar 2 -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-xs hover:border-amber-500 hover:shadow-md transition">
          <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xl mb-4">
            2
          </div>
          <h3 class="font-black text-base text-gray-900 mb-2">
            <span class="lang-en">Certified & Authentic</span>
            <span class="lang-hi hidden">प्रमाणित एवं शत-प्रतिशत प्रामाणिक</span>
          </h3>
          <p class="text-xs text-gray-600 leading-relaxed">
            <span class="lang-en">Every product is quality-checked, certified and carries the authentic story of its soil. From 55 HRC tempered railway spring steel to cold-pressed mustard oil, purity is guaranteed.</span>
            <span class="lang-hi hidden">प्रत्येक उत्पाद गुणवत्ता-जाँच से प्रमाणित है और अपनी मिट्टी की असली कहानी समेटे हुए है। तपाए गए कृषि औजारों से लेकर कच्ची घानी तेल तक, शुद्धता सुनिश्चित है।</span>
          </p>
        </div>

        <!-- Pillar 3 -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-xs hover:border-indigo-600 hover:shadow-md transition">
          <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center font-black text-xl mb-4">
            3
          </div>
          <h3 class="font-black text-base text-gray-900 mb-2">
            <span class="lang-en">Empowerment Model</span>
            <span class="lang-hi hidden">सर्वांगीण सशक्तिकरण मॉडल</span>
          </h3>
          <p class="text-xs text-gray-600 leading-relaxed">
            <span class="lang-en">Every purchase empowers an SHG didi, a farmer, a local producer and a small-town seller from every corner of India. You directly support rural livelihood and dignity.</span>
            <span class="lang-hi hidden">आपकी प्रत्येक खरीदारी भारत के हर कोने की एक SHG दीदी, एक किसान, एक स्थानीय उत्पादक और छोटे कस्बे के विक्रेता को आत्मनिर्भर बनाती है।</span>
          </p>
        </div>

      </div>
    </div>

    <!-- 4. Movement Banner -->
    <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 text-stone-950 rounded-3xl p-8 sm:p-10 shadow-lg text-center space-y-3">
      <span class="inline-block px-3 py-1 bg-stone-950 text-white text-[11px] font-black rounded-lg uppercase tracking-wider">
        <span class="lang-en">National Movement</span>
        <span class="lang-hi hidden">राष्ट्रीय जन-अभियान</span>
      </span>
      <h3 class="text-2xl sm:text-3xl font-black font-display text-white">
        <span class="lang-en">Our Mission: Villager Products to National Market</span>
        <span class="lang-hi hidden">हमारा मिशन: ग्रामीण उत्पाद, राष्ट्रीय बाजार तक</span>
      </h3>
      <p class="text-xs sm:text-sm text-stone-950 font-bold max-w-2xl mx-auto leading-relaxed">
        <span class="lang-en">GanvGaliHat is not just a store, it is a movement for Atmanirbhar Bharat and Vocal for Local.</span>
        <span class="lang-hi hidden">गाँव गली हाट केवल एक ऑनलाइन स्टोर नहीं, बल्कि यह आत्मनिर्भर भारत और वोकल फॉर लोकल का एक राष्ट्रव्यापी अभियान है।</span>
      </p>
    </div>

    <!-- 5. Founder Spotlight Card (Exclusively on About Us Page) -->
    <div class="bg-white rounded-3xl border-2 border-emerald-900/15 shadow-xl p-6 sm:p-12 overflow-hidden relative">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        
        <!-- Founder Photo with Badge -->
        <div class="lg:col-span-5 flex justify-center">
          <div class="relative group">
            <div class="relative w-64 sm:w-72 aspect-4/5 rounded-3xl overflow-hidden shadow-2xl border-4 border-amber-400 bg-stone-900">
              <img 
                src="/assets/images/founder_dheerendra_shukla.jpg" 
                alt="Founder Dheerendra Shukla — GanvGaliHat" 
                class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500" 
              />
              <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 via-transparent to-transparent"></div>
              <div class="absolute bottom-4 inset-x-4 text-center">
                <span class="inline-block px-3 py-1 bg-amber-500 text-stone-950 font-black text-xs rounded-xl shadow-md">
                  Founder & Visionary
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Founder Details & Mission Text -->
        <div class="lg:col-span-7 space-y-4 text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold uppercase tracking-wider">
            <i data-lucide="award" class="w-3.5 h-3.5 text-emerald-700"></i>
            <span>Leadership & Vision</span>
          </div>

          <div>
            <h3 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
              Dheerendra Shukla
            </h3>
            <p class="text-sm font-bold text-amber-700 mt-0.5">
              <span class="lang-en">Founder, GanvGaliHat</span>
              <span class="lang-hi hidden">संस्थापक, गाँव गली हाट (GanvGaliHat)</span>
            </p>
          </div>

          <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
            <span class="lang-en">
              With a mission to empower all local producers and sellers of Rural India. Committed to transforming village clusters into self-sustaining digital hubs, unlocking fair market access, and delivering indigenous quality to every doorstep across the nation.
            </span>
            <span class="lang-hi hidden">
              ग्रामीण भारत के सभी स्थानीय उत्पादकों और विक्रेताओं को सशक्त बनाने के पावन मिशन के साथ। पारंपरिक कारीगरों, कुटीर किसानों और महिला स्वयं सहायता समूहों को सीधे राष्ट्रीय बाजार से जोड़कर आत्मनिर्भर भारत के निर्माण हेतु पूर्णतः समर्पित।
            </span>
          </p>

          <!-- Founder's Signature Quote -->
          <div class="p-5 bg-stone-50 border-l-4 border-emerald-800 rounded-r-2xl shadow-xs">
            <p class="text-sm sm:text-base font-black italic text-emerald-950 leading-snug">
              "We don't just sell products, we sell stories of Rural India."
            </p>
            <p class="text-[11px] text-gray-500 font-semibold mt-1">
              — Dheerendra Shukla, Founder
            </p>
          </div>

          <div class="pt-2 flex flex-wrap items-center gap-3">
            <span class="px-3 py-1.5 rounded-xl bg-stone-100 text-gray-700 text-xs font-bold flex items-center gap-1.5">
              <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
              <span>ODOP Supporter</span>
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-stone-100 text-gray-700 text-xs font-bold flex items-center gap-1.5">
              <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
              <span>Rural MSME Champion</span>
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-stone-100 text-gray-700 text-xs font-bold flex items-center gap-1.5">
              <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
              <span>Women SHG Ally</span>
            </span>
          </div>
        </div>

      </div>
    </div>

    <!-- 6. Operational Facilities & Central Hub -->
    <div class="bg-white p-6 sm:p-10 rounded-3xl border border-gray-200 shadow-xs space-y-6">
      <div class="text-center max-w-xl mx-auto space-y-1">
        <span class="text-xs font-black text-emerald-800 uppercase tracking-wider">
          <span class="lang-en">Fulfillment & Operations</span>
          <span class="lang-hi hidden">निर्माण एवं लॉजिस्टिक्स केंद्र</span>
        </span>
        <h3 class="text-xl sm:text-2xl font-black text-gray-900 font-display">
          Kalyani agrofood pvt ltd — Production & Administrative Hub
        </h3>
        <p class="text-xs text-gray-500">
          <span class="lang-en">Our central industrial and dispatch infrastructure in Uttar Pradesh</span>
          <span class="lang-hi hidden">उत्तर प्रदेश स्थित हमारे प्राथमिक विनिर्माण एवं केंद्रीय लॉजिस्टिक्स हब</span>
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-gray-700">
        
        <!-- Sandila Hub -->
        <div class="p-6 rounded-2xl bg-stone-50 border border-gray-200 space-y-3 hover:border-emerald-600 transition-colors">
          <div class="flex items-center gap-2 font-bold text-sm text-stone-900">
            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
              <i data-lucide="building-2" class="w-4 h-4"></i>
            </span>
            <span>Head Office & Central Industrial Hub</span>
          </div>
          <p class="text-gray-600 leading-relaxed font-medium">
            G145 Phase 2 Industrial Area, UPSIDA Sandila, District Hardoi, Uttar Pradesh - 241204
          </p>
          <div class="pt-2 border-t border-gray-200/60 text-[11px] space-y-1 text-gray-500">
            <p><strong>Primary Function:</strong> Quality testing, Pan-India parcel dispatch, technical administration</p>
            <p><strong>Contact:</strong> info@ganvgalihat.com • +91 7897492100</p>
          </div>
        </div>

        <!-- Nevada Works -->
        <div class="p-6 rounded-2xl bg-stone-50 border border-gray-200 space-y-3 hover:border-amber-500 transition-colors">
          <div class="flex items-center gap-2 font-bold text-sm text-stone-900">
            <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center">
              <i data-lucide="hammer" class="w-4 h-4"></i>
            </span>
            <span>Forge & Manufacturing Works</span>
          </div>
          <p class="text-gray-600 leading-relaxed font-medium">
            Madhoganj Baghauli Road, Nevada, District Hardoi, Uttar Pradesh - 241126
          </p>
          <div class="pt-2 border-t border-gray-200/60 text-[11px] space-y-1 text-gray-500">
            <p><strong>Primary Function:</strong> Leaf-spring forged tools, wood-pressed oil units, SHG craft packaging</p>
            <p><strong>Logistics:</strong> India Post Speed Post Node</p>
          </div>
        </div>

      </div>
    </div>

    <!-- 7. Call to Action Banner -->
    <div class="bg-gradient-to-r from-emerald-900 to-emerald-950 text-white rounded-3xl p-8 sm:p-10 shadow-lg text-center space-y-4">
      <h3 class="text-2xl sm:text-3xl font-black font-display text-white">
        <span class="lang-en">Bring Bharat's Craft & Harvest Home Today</span>
        <span class="lang-hi hidden">ग्रामीण भारत के हुनर को अपने घर लाएं</span>
      </h3>
      <p class="text-xs sm:text-sm text-stone-300 max-w-xl mx-auto leading-relaxed">
        <span class="lang-en">Every product you purchase brings dignity and fair livelihood directly to a village artisan family.</span>
        <span class="lang-hi hidden">आपके द्वारा खरीदा गया प्रत्येक उत्पाद किसी गाँव के लोहार, किसान या कुटीर कारीगर परिवार के चेहरे पर मुस्कान लाता है।</span>
      </p>
      <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
        <a href="/products.php" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs rounded-xl shadow transition">
          <span class="lang-en">Shop Catalog</span>
          <span class="lang-hi hidden">उत्पाद देखें</span>
        </a>
        <a href="/seller-register.php" class="px-6 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 transition">
          <span class="lang-en">Register as Rural Seller</span>
          <span class="lang-hi hidden">विक्रेता के रूप में जुड़ें</span>
        </a>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
