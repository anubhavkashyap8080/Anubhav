# -*- coding: utf-8 -*-
"""
Seed data/pages.json with comprehensive, production-grade bilingual content
for all standard policy and CMS pages:
1. about-us
2. privacy-policy
3. terms-conditions
4. shipping-policy
5. terms-of-supply
6. support
"""
import os
import json

data_dir = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\data'
os.makedirs(data_dir, exist_ok=True)
pages_file = os.path.join(data_dir, 'pages.json')

pages = {
    "about-us": {
        "slug": "about-us",
        "title": "About Us — India's Dedicated Rural Marketplace",
        "hindi_title": "हमारे बारे में — भारत का समर्पित ग्रामीण मार्केटप्लेस",
        "meta_description": "GanvGaliHat is India's dedicated rural marketplace bridging ODOP artisans, Women SHGs, and local village makers directly to every home across India.",
        "is_active": 1,
        "is_system": 1,
        "content_en": """
<div class="space-y-8">
  <div class="p-6 sm:p-8 bg-emerald-900 text-white rounded-3xl shadow-sm">
    <span class="inline-block px-3 py-1 bg-amber-400 text-stone-950 text-xs font-black rounded-lg uppercase tracking-wider mb-3">Atmanirbhar Bharat • Vocal for Local</span>
    <h2 class="text-2xl sm:text-3xl font-black mb-3">Connecting Bharat's Villages to Every Indian Home</h2>
    <p class="text-stone-200 text-sm sm:text-base leading-relaxed">
      <strong>www.GanvGaliHat.com</strong> is India's dedicated rural marketplace, built with a simple dream — to take the products of our villages to every home in the country.
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-xs">
      <h3 class="text-lg font-black text-gray-900 mb-2">The Rural Skill & The Gap</h3>
      <p class="text-gray-600 text-xs sm:text-sm leading-relaxed">
        We saw that our villages have no shortage of skill. Every district of India has its own unique product — pure spices, sun-cured pickles, handloom textiles, handicrafts, and handcrafted agricultural tools. Everything is pure and authentic. But our local producers never get the right price due to middlemen and lack of market access.
      </p>
      <div class="mt-4 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-900 font-bold text-xs sm:text-sm">
        GanvGaliHat bridges this gap.
      </div>
    </div>

    <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-xs">
      <h3 class="text-lg font-black text-gray-900 mb-2">All Local Products Under One Roof</h3>
      <p class="text-gray-600 text-xs sm:text-sm leading-relaxed">
        We bring <strong>ODOP (One District One Product) artisans, Women-led SHGs, MSMEs, and All Local Producers & Sellers from All Over India</strong> onto a single digital platform, giving them a direct national market.
      </p>
      <p class="text-gray-600 text-xs sm:text-sm leading-relaxed mt-2">
        We are proud to bring you <em>All Local Products in India</em> under one roof.
      </p>
    </div>
  </div>

  <div class="p-6 sm:p-8 bg-stone-50 rounded-3xl border border-gray-200">
    <h3 class="text-xl font-black text-gray-900 mb-4">What We Do</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="p-5 bg-white rounded-2xl border border-gray-200">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black mb-3">1</div>
        <h4 class="font-bold text-sm text-gray-900 mb-1">Direct From Village Clusters</h4>
        <p class="text-xs text-gray-500 leading-relaxed">We source directly from Village Clusters, SHG groups and local sellers across India, with zero middlemen.</p>
      </div>
      <div class="p-5 bg-white rounded-2xl border border-gray-200">
        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-black mb-3">2</div>
        <h4 class="font-bold text-sm text-gray-900 mb-1">Certified & Authentic</h4>
        <p class="text-xs text-gray-500 leading-relaxed">Every product is quality-checked, certified and carries the authentic story of its native soil.</p>
      </div>
      <div class="p-5 bg-white rounded-2xl border border-gray-200">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center font-black mb-3">3</div>
        <h4 class="font-bold text-sm text-gray-900 mb-1">Empowerment Model</h4>
        <p class="text-xs text-gray-500 leading-relaxed">Every purchase empowers an SHG didi, a farmer, a local producer and a small-town seller from every corner of India.</p>
      </div>
    </div>
  </div>

  <div class="p-6 bg-gradient-to-r from-amber-500 to-amber-600 rounded-3xl text-stone-950 text-center">
    <h3 class="text-xl sm:text-2xl font-black mb-1">Our Mission: Villager Products to National Market</h3>
    <p class="text-xs sm:text-sm font-semibold max-w-2xl mx-auto">
      GanvGaliHat is not just a store, it is a movement for Atmanirbhar Bharat and Vocal for Local.
    </p>
  </div>

  <!-- Founder Spotlight Card -->
  <div class="p-6 sm:p-10 bg-white rounded-3xl border-2 border-emerald-900/10 shadow-lg">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-10 items-center">
      <div class="md:col-span-4 flex justify-center">
        <div class="relative w-48 sm:w-56 aspect-square rounded-3xl overflow-hidden shadow-xl border-4 border-amber-400">
          <img src="/assets/images/founder_dheerendra_shukla.jpg" alt="Founder Dheerendra Shukla" class="w-full h-full object-cover" />
          <div class="absolute bottom-2 inset-x-2 py-1 px-2 bg-stone-950/80 backdrop-blur-xs text-white text-center rounded-xl text-[10px] font-bold">
            Founder & Visionary
          </div>
        </div>
      </div>
      <div class="md:col-span-8 space-y-3 text-left">
        <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-800 text-xs font-black rounded-lg uppercase">Leadership & Vision</span>
        <h3 class="text-2xl sm:text-3xl font-black text-gray-900">Dheerendra Shukla</h3>
        <p class="text-sm font-bold text-amber-700">Founder, GanvGaliHat</p>
        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
          With a mission to empower all local producers and sellers of Rural India. Dedicated to bridging the digital divide and ensuring fair compensation directly to generational artisans, farmers, and women self-help groups across Bharat.
        </p>
        <div class="p-4 bg-stone-50 border-l-4 border-emerald-700 rounded-r-xl">
          <p class="text-xs sm:text-sm font-black italic text-emerald-900">
            "We don't just sell products, we sell stories of Rural India."
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
""",
        "content_hi": """
<div class="space-y-8">
  <div class="p-6 sm:p-8 bg-emerald-900 text-white rounded-3xl shadow-sm">
    <span class="inline-block px-3 py-1 bg-amber-400 text-stone-950 text-xs font-black rounded-lg uppercase tracking-wider mb-3">आत्मनिर्भर भारत • वोकल फॉर लोकल</span>
    <h2 class="text-2xl sm:text-3xl font-black mb-3">भारत के गाँवों को देश के हर घर से जोड़ना</h2>
    <p class="text-stone-200 text-sm sm:text-base leading-relaxed">
      <strong>www.GanvGaliHat.com</strong> भारत का समर्पित ग्रामीण मार्केटप्लेस है, जो एक सीधे और पावन सपने के साथ बना है — हमारे गाँवों के हुनरमंद उत्पादों को देश के हर घर तक पहुँचाना।
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-xs">
      <h3 class="text-lg font-black text-gray-900 mb-2">ग्रामीण हुनर और बाजार की दूरी</h3>
      <p class="text-gray-600 text-xs sm:text-sm leading-relaxed">
        हमने देखा कि हमारे गाँवों में हुनर की कोई कमी नहीं है। भारत के हर जिले का अपना विशिष्ट उत्पाद है — शुद्ध मसाले, पारंपरिक देसी अचार, हथकरघा वस्त्र, हस्तशिल्प और हाथ से गढ़े कृषि औजार। सब कुछ शुद्ध और प्रामाणिक है। लेकिन हमारे स्थानीय उत्पादकों को बिचौलियों और बाजार तक सीधी पहुँच न होने के कारण कभी सही दाम नहीं मिल पाता।
      </p>
      <div class="mt-4 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-900 font-bold text-xs sm:text-sm">
        गाँव गली हाट (GanvGaliHat) इसी अंतर को पाटता है।
      </div>
    </div>

    <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-xs">
      <h3 class="text-lg font-black text-gray-900 mb-2">सभी स्थानीय उत्पाद एक मंच पर</h3>
      <p class="text-gray-600 text-xs sm:text-sm leading-relaxed">
        हम <strong>ओडीओपी (ODOP - एक जिला एक उत्पाद) कारीगरों, महिला स्वयं सहायता समूहों (SHG), एमएसएमई (MSME) और पूरे भारत के सभी स्थानीय उत्पादकों व विक्रेताओं</strong> को एक ही डिजिटल मंच पर लाते हैं, जिससे उन्हें सीधा राष्ट्रीय बाजार मिलता है।
      </p>
      <p class="text-gray-600 text-xs sm:text-sm leading-relaxed mt-2">
        हमें पूरे भारत के स्थानीय उत्पादों को एक छत के नीचे लाने पर गर्व है।
      </p>
    </div>
  </div>

  <div class="p-6 sm:p-8 bg-stone-50 rounded-3xl border border-gray-200">
    <h3 class="text-xl font-black text-gray-900 mb-4">हम क्या करते हैं</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="p-5 bg-white rounded-2xl border border-gray-200">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black mb-3">1</div>
        <h4 class="font-bold text-sm text-gray-900 mb-1">गाँव क्लस्टर्स से सीधा जुड़ाव</h4>
        <p class="text-xs text-gray-500 leading-relaxed">हम पूरे भारत के ग्रामीण क्लस्टरों, महिला SHG समूहों और स्थानीय विक्रेताओं से बिना किसी बिचौलिए के सीधे उत्पाद प्राप्त करते हैं।</p>
      </div>
      <div class="p-5 bg-white rounded-2xl border border-gray-200">
        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-black mb-3">2</div>
        <h4 class="font-bold text-sm text-gray-900 mb-1">प्रमाणित एवं शत-प्रतिशत प्रामाणिक</h4>
        <p class="text-xs text-gray-500 leading-relaxed">प्रत्येक उत्पाद गुणवत्ता-जाँच से प्रमाणित है और अपनी मिट्टी की असली और अनूठी कहानी समेटे हुए है।</p>
      </div>
      <div class="p-5 bg-white rounded-2xl border border-gray-200">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center font-black mb-3">3</div>
        <h4 class="font-bold text-sm text-gray-900 mb-1">सशक्तिकरण मॉडल</h4>
        <p class="text-xs text-gray-500 leading-relaxed">आपकी प्रत्येक खरीदारी भारत के हर कोने की एक SHG दीदी, एक किसान, एक स्थानीय उत्पादक और छोटे कस्बे के विक्रेता को आर्थिक रूप से सशक्त बनाती है।</p>
      </div>
    </div>
  </div>

  <div class="p-6 bg-gradient-to-r from-amber-500 to-amber-600 rounded-3xl text-stone-950 text-center">
    <h3 class="text-xl sm:text-2xl font-black mb-1">हमारा मिशन: ग्रामीण उत्पाद, राष्ट्रीय बाजार तक</h3>
    <p class="text-xs sm:text-sm font-semibold max-w-2xl mx-auto">
      गाँव गली हाट केवल एक ऑनलाइन स्टोर नहीं, बल्कि यह आत्मनिर्भर भारत और वोकल फॉर लोकल का एक राष्ट्रव्यापी अभियान है।
    </p>
  </div>

  <!-- Founder Spotlight Card -->
  <div class="p-6 sm:p-10 bg-white rounded-3xl border-2 border-emerald-900/10 shadow-lg">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-10 items-center">
      <div class="md:col-span-4 flex justify-center">
        <div class="relative w-48 sm:w-56 aspect-square rounded-3xl overflow-hidden shadow-xl border-4 border-amber-400">
          <img src="/assets/images/founder_dheerendra_shukla.jpg" alt="संस्थापक धीरेंद्र शुक्ल" class="w-full h-full object-cover" />
          <div class="absolute bottom-2 inset-x-2 py-1 px-2 bg-stone-950/80 backdrop-blur-xs text-white text-center rounded-xl text-[10px] font-bold">
            संस्थापक एवं विजनरी
          </div>
        </div>
      </div>
      <div class="md:col-span-8 space-y-3 text-left">
        <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-800 text-xs font-black rounded-lg uppercase">नेतृत्व एवं दूरदृष्टि</span>
        <h3 class="text-2xl sm:text-3xl font-black text-gray-900">धीरेंद्र शुक्ल (Dheerendra Shukla)</h3>
        <p class="text-sm font-bold text-amber-700">संस्थापक, गाँव गली हाट (GanvGaliHat)</p>
        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
          ग्रामीण भारत के सभी स्थानीय उत्पादकों और विक्रेताओं को सशक्त बनाने के पावन मिशन के साथ। भारत भर के पारंपरिक कारीगरों, किसानों और महिला स्वयं सहायता समूहों को सीधे राष्ट्रीय बाजार से जोड़कर पारदर्शी और सम्मानजनक मूल्य दिलाने हेतु निरंतर समर्पित।
        </p>
        <div class="p-4 bg-stone-50 border-l-4 border-emerald-700 rounded-r-xl">
          <p class="text-xs sm:text-sm font-black italic text-emerald-900">
            "हम केवल उत्पाद नहीं बेचते, हम ग्रामीण भारत की गौरव गाथाएं बेचते हैं।"
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
"""
    },

    "privacy-policy": {
        "slug": "privacy-policy",
        "title": "Privacy Policy — GanvGaliHat",
        "hindi_title": "गोपनीयता नीति (Privacy Policy) — GanvGaliHat",
        "meta_description": "Official Privacy Policy of GanvGaliHat. Details on data protection, customer security, OTP verification, and secure payments under Indian DPDP Act.",
        "is_active": 1,
        "is_system": 1,
        "content_en": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
    <p class="text-emerald-900 font-semibold">
      At <strong>GanvGaliHat</strong> (accessible at <a href="https://ganvgalihat.com" class="text-emerald-800 underline">www.ganvgalihat.com</a>), your privacy and data security are of utmost importance. This Privacy Policy outlines the types of personal data we collect, how it is safeguarded, and your rights under the Digital Personal Data Protection Act (DPDP), 2023.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">1. Information We Collect</h3>
    <p>To process orders, verify deliveries, and connect you with village artisans, we collect the following information:</p>
    <ul class="list-disc pl-5 mt-2 space-y-1 text-gray-600">
      <li><strong>Personal Identity:</strong> Full Name, Mobile Phone Number (verified via OTP), and optional Email address.</li>
      <li><strong>Shipping & Delivery:</strong> Complete delivery address, Landmark, Village/Town, District, State, and 6-digit PIN code.</li>
      <li><strong>Transaction Data:</strong> Order history, payment reference IDs, Cash on Delivery (COD) status, and India Post tracking barcodes.</li>
      <li><strong>Technical Data:</strong> IP address, device type, browser information, and secure session cookies for shopping cart continuity.</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">2. How We Use Your Data</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li>To process, dispatch, and track orders directly from village artisan clusters and regional hubs.</li>
      <li>To send real-time SMS / WhatsApp parcel dispatch updates and delivery notifications.</li>
      <li>To prevent fraudulent orders through mandatory phone OTP verification.</li>
      <li>To provide prompt customer support desk assistance and resolve complaints.</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">3. Payment Security & Financial Protection</h3>
    <p>
      We do <strong>NOT</strong> store or view your credit card, debit card numbers, CVVs, or Net Banking credentials on our servers. All digital payments are processed through PCI-DSS Level 1 certified RBI-authorized payment gateways (RazorPay and PayU) utilizing 256-bit SSL encryption.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">4. Zero Data Selling Pledge</h3>
    <p>
      GanvGaliHat strictly pledges that we <strong>never sell, rent, or trade</strong> your personal information to third-party marketing brokers or advertisers. Data is shared strictly with essential logistics partners (India Post / authorized couriers) solely for the physical delivery of your parcels.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">5. Data Retention & Your Rights</h3>
    <p>
      You have the right to request access to your personal data, request correction of inaccurate addresses, or request the deletion of your account. Contact our Privacy Desk at <a href="mailto:privacy@ganvgalihat.com" class="text-emerald-800 font-bold underline">privacy@ganvgalihat.com</a>.
    </p>
  </div>

  <div class="p-4 bg-stone-100 rounded-xl border border-stone-200">
    <h4 class="font-bold text-gray-900 text-xs sm:text-sm">Grievance & Privacy Officer</h4>
    <p class="text-xs text-gray-600 mt-1">
      GanvGaliHat Central Governance Hub<br>
      Kalyani agrofood pvt ltd, G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, Uttar Pradesh - 241204<br>
      Email: <a href="mailto:info@ganvgalihat.com" class="text-emerald-800 underline">info@ganvgalihat.com</a> • Phone: +91 7897492100
    </p>
  </div>
</div>
""",
        "content_hi": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
    <p class="text-emerald-900 font-semibold">
      <strong>गाँव गली हाट (GanvGaliHat)</strong> पर आपकी व्यक्तिगत जानकारी एवं डेटा सुरक्षा हमारे लिए सर्वोपरि है। यह गोपनीयता नीति स्पष्ट करती है कि हम आपका डेटा कैसे एकत्र, सुरक्षित और उपयोग करते हैं।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">1. हम कौन सी जानकारी एकत्र करते हैं</h3>
    <ul class="list-disc pl-5 mt-2 space-y-1 text-gray-600">
      <li><strong>पहचान विवरण:</strong> पूरा नाम, मोबाइल फोन नंबर (OTP द्वारा सत्यापित), एवं ईमेल पता।</li>
      <li><strong>डिलीवरी पता:</strong> घर का पता, गाँव/शहर, जिला, राज्य एवं 6-अंकीय पिनकोड।</li>
      <li><strong>ऑर्डर डेटा:</strong> खरीदारी का विवरण, भारतीय डाक (Speed Post) ट्रैकिंग बारकोड और भुगतान की स्थिति।</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">2. डेटा का उपयोग</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li>गाँव के कारीगरों और हमारे औद्योगिक केंद्र से सीधे पार्सल प्रेषण एवं डिलीवरी हेतु।</li>
      <li>पार्सल की रवानगी और डिलीवरी हेतु रियल-टाइम SMS एवं व्हाट्सएप सूचनाएं भेजने के लिए।</li>
      <li>फर्जी ऑर्डर से बचाव हेतु फोन नंबर सत्यापन के लिए।</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">3. भुगतान सुरक्षा</h3>
    <p>
      हम अपने सर्वर पर आपके डेबिट कार्ड, क्रेडिट कार्ड नंबर या UPI पिन कभी स्टोर नहीं करते। सभी ऑनलाइन भुगतान भारतीय रिजर्व बैंक (RBI) द्वारा अधिकृत सुरक्षित गेटवे (RazorPay / PayU) के 256-bit एन्क्रिप्शन द्वारा सुरक्षित होते हैं।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">4. डेटा न बेचने का दृढ़ संकल्प</h3>
    <p>
      गाँव गली हाट यह वचन देता है कि हम आपका व्यक्तिगत डेटा किसी भी तीसरे पक्ष या विज्ञापन एजेंसी को कभी नहीं बेचते। जानकारी केवल भारतीय डाक (India Post) के साथ पार्सल पहुँचाने हेतु साझा की जाती है।
    </p>
  </div>

  <div class="p-4 bg-stone-100 rounded-xl border border-stone-200">
    <h4 class="font-bold text-gray-900 text-xs sm:text-sm">गोपनीयता अधिकारी एवं संपर्क</h4>
    <p class="text-xs text-gray-600 mt-1">
      गाँव गली हाट केंद्रीय प्रशासन हब<br>
      कल्याणी एग्रोफूड प्रा. लि., G145 Phase 2 Industrial Area, संडीला, हरदोई, उत्तर प्रदेश - 241204<br>
      ईमेल: <a href="mailto:info@ganvgalihat.com" class="text-emerald-800 underline">info@ganvgalihat.com</a> • फोन: +91 7897492100
    </p>
  </div>
</div>
"""
    },

    "terms-conditions": {
        "slug": "terms-conditions",
        "title": "Terms & Conditions — GanvGaliHat",
        "hindi_title": "नियम एवं शर्तें (Terms & Conditions) — GanvGaliHat",
        "meta_description": "Official Terms of Service for GanvGaliHat rural marketplace. Transparent consumer rights, pricing, and seller guarantees.",
        "is_active": 1,
        "is_system": 1,
        "content_en": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-amber-50 border border-amber-200 rounded-2xl">
    <p class="text-amber-950 font-semibold">
      Welcome to <strong>www.GanvGaliHat.com</strong>. By accessing or shopping on our platform, you agree to be bound by the following Terms and Conditions, designed to ensure fair trade for rural Indian artisans and a seamless experience for buyers.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">1. Rural Marketplace Model</h3>
    <p>
      GanvGaliHat operates as a direct bridge connecting ODOP craftspeople, rural blacksmiths, women self-help groups (SHGs), and agricultural producers directly to consumers nationwide. We guarantee that all products offered are authentic village-crafted goods.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">2. Account Registration & Phone Verification</h3>
    <p>
      To prevent fraudulent activity and protect artisan livelihood, user orders require a verified mobile phone number via SMS OTP. Users agree to provide accurate, complete delivery coordinates.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">3. Transparent Pricing & Invoicing</h3>
    <p>
      All prices displayed on GanvGaliHat are in Indian Rupees (INR) and are inclusive of all applicable statutory GST. We do not apply hidden surcharges. Official computer-generated GST tax invoices are provided with every dispatched shipment.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">4. Payment Terms & Cash on Delivery (COD)</h3>
    <p>
      We accept UPI, Net Banking, Credit/Debit Cards, and Cash on Delivery (COD) across eligible PIN codes. In case of COD orders, buyers are required to pay the exact invoice amount to the India Post delivery personnel upon receipt.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">5. Handcrafted Variations</h3>
    <p>
      Because our goods are individually hand-forged in charcoal anvils, hand-spun on looms, or stone-ground by rural artisans, minor organic variations in weight, grain, or finish are natural hallmarks of authentic craftsmanship and do not constitute manufacturing defects.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">6. Jurisdiction & Legal Governance</h3>
    <p>
      These terms are governed by the laws of India. Any legal dispute arising out of transactions on this platform shall be subject to the exclusive jurisdiction of the competent courts in Hardoi / Lucknow, Uttar Pradesh.
    </p>
  </div>
</div>
""",
        "content_hi": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-amber-50 border border-amber-200 rounded-2xl">
    <p class="text-amber-950 font-semibold">
      <strong>www.GanvGaliHat.com</strong> पर आपका स्वागत है। हमारे मंच से खरीदारी करने पर आप निम्नलिखित नियमों एवं शर्तों से बंधे हैं, जो ग्रामीण कारीगरों के लिए न्यायसंगत व्यापार और ग्राहकों के लिए पारदर्शी अनुभव सुनिश्चित करते हैं।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">1. ग्रामीण मार्केटप्लेस मॉडल</h3>
    <p>
      गाँव गली हाट देश भर के ग्रामीण शिल्पकारों, लोहारों, महिला स्वयं सहायता समूहों (SHG) और किसानों को सीधे उपभोक्ताओं से जोड़ता है। हम गारंटी देते हैं कि मंच पर उपलब्ध सभी उत्पाद 100% प्रामाणिक स्वदेशी हैं।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">2. पारदर्शी मूल्य एवं बिलिंग</h3>
    <p>
      प्रदर्शित सभी कीमतें भारतीय रुपये (INR) में हैं और लागू जीएसटी (GST) सहित हैं। कोई छुपा हुआ शुल्क नहीं लिया जाता। प्रत्येक पार्सल के साथ आधिकारिक जीएसटी टैक्स इनवॉइस प्रदान किया जाता है।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">3. हस्तनिर्मित विविधता</h3>
    <p>
      चूंकि हमारे उत्पाद कारीगरों द्वारा हाथ से भट्टी में तपाकर या पारंपरिक करघों पर बनाए जाते हैं, इसलिए रंग, वजन या बनावट में मामूली प्राकृतिक अंतर प्रामाणिक हस्तशिल्प की पहचान है, कोई दोष नहीं।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">4. विधिक अधिकार क्षेत्र</h3>
    <p>
      यह नियम भारतीय कानूनों के अधीन हैं। किसी भी विवाद की स्थिति में न्यायिक क्षेत्र हरदोई / लखनऊ, उत्तर प्रदेश रहेगा।
    </p>
  </div>
</div>
"""
    },

    "shipping-policy": {
        "slug": "shipping-policy",
        "title": "Shipping & Return Policy — GanvGaliHat",
        "hindi_title": "शिपिंग एवं वापसी नीति (Shipping & Return Policy) — GanvGaliHat",
        "meta_description": "Pan-India delivery across 19,000+ PIN codes via India Post Speed Post. 24-48h dispatch, 7-day hassle-free replacement policy.",
        "is_active": 1,
        "is_system": 1,
        "content_en": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
    <p class="text-emerald-950 font-semibold">
      We deliver authentic rural products directly from village workshops and central fulfillment centers to <strong>19,000+ PIN codes</strong> across India via our direct partnership with <strong>India Post (Speed Post)</strong> and trusted express parcel logistics.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">1. Dispatch Timelines</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li><strong>Standard Stock Items:</strong> Dispatched within 24 to 48 business hours from our Sandila Industrial Hub.</li>
      <li><strong>Handcrafted / Made-to-Order Tools:</strong> Forged and oil-tempered within 3 business days and dispatched immediately.</li>
      <li>Orders placed on Sundays or public gazetted holidays are dispatched on the next working business day.</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">2. Estimated Delivery Timelines</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li><strong>Uttar Pradesh & NCR:</strong> 2 to 4 business days.</li>
      <li><strong>Metro Cities (Delhi, Mumbai, Bengaluru, Kolkata, Chennai):</strong> 3 to 5 business days.</li>
      <li><strong>Rest of India & Rural PIN codes:</strong> 4 to 7 business days via India Post Speed Post.</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">3. Live Shipment Tracking</h3>
    <p>
      As soon as your parcel is handed over to India Post or the courier node, you will receive an automated SMS and email containing your unique 13-digit Speed Post tracking barcode (e.g., <code>EU123456789IN</code>) to track your delivery in real time.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">4. 7-Day Hassle-Free Replacement Policy</h3>
    <p>
      We inspect every product prior to dispatch. However, if your order arrives physically damaged in transit or has a manufacturing defect, you can request a <strong>100% Free Replacement within 7 days</strong> of delivery.
    </p>
    <ul class="list-disc pl-5 mt-2 space-y-1 text-gray-600">
      <li>Simply WhatsApp our support desk at <strong>+91 7897492100</strong> with photos of the damaged item and outer parcel label.</li>
      <li>We arrange reverse pickup or immediately dispatch a fresh replacement parcel via express post.</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">5. Non-Returnable Items</h3>
    <p>
      For health and hygiene safety, opened bottles of cold-pressed edible oils, unsealed jars of traditional achar (pickles), and custom-engraved tools are non-returnable unless delivered damaged or leaked.
    </p>
  </div>
</div>
""",
        "content_hi": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
    <p class="text-emerald-950 font-semibold">
      हम <strong>भारतीय डाक (Speed Post)</strong> और विश्वसनीय कूरियर सेवाओं द्वारा सीधे गाँव के कार्यशालाओं से देश के <strong>19,000+ पिनकोडों</strong> तक सुरक्षित डिलीवरी सुनिश्चित करते हैं।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">1. डिस्पैच समय सीमा</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li>सभी सामान्य ऑर्डर 24 से 48 कार्य घंटों के भीतर संडीला हब से डिस्पैच किए जाते हैं।</li>
      <li>हाथ से गढ़े जाने वाले भारी कृषि औजार 2-3 कार्य दिवसों में भट्टी से तैयार कर तुरंत भेजे जाते हैं।</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">2. डिलीवरी समय</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li><strong>उत्तर प्रदेश एवं एनसीआर:</strong> 2 से 4 कार्य दिवस।</li>
      <li><strong>मेट्रो शहर:</strong> 3 से 5 कार्य दिवस।</li>
      <li><strong>शेष भारत एवं ग्रामीण क्षेत्र:</strong> 4 से 7 कार्य दिवस।</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">3. 7 दिन की आसान रिप्लेसमेंट सुविधा</h3>
    <p>
      यदि पार्सल रास्ते में टूट-फूट का शिकार हो जाता है या उत्पाद में कोई खराबी पाई जाती है, तो आप डिलीवरी के 7 दिनों के भीतर <strong>निःशुल्क रिप्लेसमेंट</strong> का अनुरोध कर सकते हैं।
    </p>
    <p class="mt-1 text-gray-600">
      सपोर्ट व्हाट्सएप <strong>+91 7897492100</strong> पर टूटे उत्पाद का फोटो भेजें, हम तुरंत नया उत्पाद भेजेंगे।
    </p>
  </div>
</div>
"""
    },

    "terms-of-supply": {
        "slug": "terms-of-supply",
        "title": "Terms of Supply & Quality Pledge — GanvGaliHat",
        "hindi_title": "आपूर्ति के नियम एवं गुणवत्ता वचन (Terms of Supply) — GanvGaliHat",
        "meta_description": "GanvGaliHat Terms of Supply: Direct village sourcing standards, 55 HRC steel hardness, 100% unadulterated cold-pressed oils.",
        "is_active": 1,
        "is_system": 1,
        "content_en": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
    <p class="text-emerald-950 font-semibold">
      The <strong>Terms of Supply</strong> define the strict quality charters and direct artisan commitments governing every product listed and fulfilled through <strong>GanvGaliHat</strong>.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">1. 100% Direct Village Origin Verification</h3>
    <p>
      Every vendor and artisan cluster on our platform is physically onboarded and geo-verified. We guarantee that agricultural tools are forged from railway spring steel in rural foundries, edible oils are pressed on wooden kolhus, and textiles are woven on indigenous handlooms.
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">2. Agricultural Implements Technical Charter</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li>All hand-forged sickles, khurpis, axes, and spades are crafted from high-carbon EN45 leaf springs.</li>
      <li>Tempered at 52–56 HRC Rockwell hardness for lifelong edge retention.</li>
      <li>Fitted with seasoned neem, babool, or sheesham wooden handles riveted for extreme shock resistance.</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">3. Cold-Pressed Edible Staples & Pure Foods</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li>Mustard oils are 100% pure Kachi Ghani, expelled below 38°C with zero chemical solvent extraction.</li>
      <li>Pulses are unpolished, retaining natural dietary fiber, protein, and authentic flavor.</li>
      <li>Achar and pickles are prepared according to traditional Awadhi grandma recipes using pure spices and sun curing.</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">4. Fair Remittance Pledge</h3>
    <p>
      Under our direct-to-artisan financial charter, up to 96% of the sales value is credited directly into the artisan or SHG bank accounts, completely eliminating exploitative wholesale intermediaries.
    </p>
  </div>
</div>
""",
        "content_hi": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
    <p class="text-emerald-950 font-semibold">
      <strong>आपूर्ति के नियम (Terms of Supply)</strong> गाँव गली हाट द्वारा ग्राहकों को दी जाने वाली गुणवत्ता और कारीगरों के प्रति निष्ठा का अनुबंध है।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">1. 100% प्रामाणिक ग्रामीण मूल</h3>
    <p>
      हम गारंटी देते हैं कि मंच पर उपलब्ध सभी औजार ग्रामीण भट्टियों में पत्ती स्प्रिंग स्टील से बने हैं, खाद्य तेल लकड़ी के कोल्हू से निकाले गए हैं और कपड़े पारंपरिक हथकरघे से बुने गए हैं।
    </p>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">2. कृषि औजारों की गुणवत्ता मानक</h3>
    <ul class="list-disc pl-5 space-y-1 text-gray-600">
      <li>हंसिया, खुरपी और कुल्हाड़ी रेलवे स्प्रिंग स्टील से निर्मित हैं।</li>
      <li>55 HRC रॉकवेल कठोरता तक तपाए गए, जो जीवन भर धार बनाए रखते हैं।</li>
      <li>नीम एवं शीशम की मजबूत लकड़ियों के हत्थे।</li>
    </ul>
  </div>

  <div>
    <h3 class="text-base sm:text-lg font-black text-gray-900 mb-2">3. सीधा कारीगर भुगतान</h3>
    <p>
      बिक्री मूल्य का 96% तक हिस्सा सीधे गाँव के कारीगरों और महिला SHG बहनों के बैंक खाते में जाता है।
    </p>
  </div>
</div>
"""
    },

    "support": {
        "slug": "support",
        "title": "Customer Support & Help Desk — GanvGaliHat",
        "hindi_title": "ग्राहक सहायता एवं संपर्क केंद्र — GanvGaliHat",
        "meta_description": "Dedicated customer support desk for GanvGaliHat. Order assistance, artisan inquiries, wholesale inquiries, and live help.",
        "is_active": 1,
        "is_system": 1,
        "content_en": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
    <h3 class="text-base sm:text-lg font-black text-emerald-900 mb-1">We are here to assist you!</h3>
    <p class="text-emerald-800">
      Whether you need assistance tracking a Speed Post parcel, exploring wholesale bulk orders, or onboarding as a village artisan, our support team is available Monday to Saturday, 9:00 AM – 7:00 PM IST.
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="p-5 bg-white rounded-2xl border border-gray-200 text-center space-y-2">
      <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mx-auto text-lg font-bold">
        📞
      </div>
      <h4 class="font-bold text-gray-900 text-sm">Direct Phone & WhatsApp</h4>
      <p class="text-xs text-gray-500">+91 7897492100</p>
      <a href="https://wa.me/917897492100" target="_blank" class="inline-block px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition">
        Chat on WhatsApp
      </a>
    </div>

    <div class="p-5 bg-white rounded-2xl border border-gray-200 text-center space-y-2">
      <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mx-auto text-lg font-bold">
        ✉️
      </div>
      <h4 class="font-bold text-gray-900 text-sm">Official Email</h4>
      <p class="text-xs text-gray-500">info@ganvgalihat.com</p>
      <a href="mailto:info@ganvgalihat.com" class="inline-block px-3 py-1 bg-stone-900 hover:bg-black text-white font-bold text-xs rounded-lg transition">
        Send Email
      </a>
    </div>

    <div class="p-5 bg-white rounded-2xl border border-gray-200 text-center space-y-2">
      <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center mx-auto text-lg font-bold">
        📍
      </div>
      <h4 class="font-bold text-gray-900 text-sm">Central Hub Address</h4>
      <p class="text-xs text-gray-500">G145 Phase 2 Industrial Area, UPSIDA Sandila, Hardoi, UP - 241204</p>
      <span class="inline-block px-3 py-1 bg-stone-100 text-stone-700 font-bold text-xs rounded-lg">
        Speed Post Hub
      </span>
    </div>
  </div>
</div>
""",
        "content_hi": """
<div class="space-y-6 text-gray-700 text-xs sm:text-sm leading-relaxed">
  <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
    <h3 class="text-base sm:text-lg font-black text-emerald-900 mb-1">हम आपकी सेवा में तत्पर हैं!</h3>
    <p class="text-emerald-800">
      ऑर्डर ट्रैकिंग, कारीगर पंजीकरण या थोक खरीद से जुड़े किसी भी सवाल के लिए हमारी सहायता टीम सोमवार से शनिवार, सुबह 9:00 से शाम 7:00 बजे तक उपलब्ध है।
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="p-5 bg-white rounded-2xl border border-gray-200 text-center space-y-2">
      <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mx-auto text-lg font-bold">📞</div>
      <h4 class="font-bold text-gray-900 text-sm">फोन एवं व्हाट्सएप</h4>
      <p class="text-xs text-gray-500">+91 7897492100</p>
      <a href="https://wa.me/917897492100" target="_blank" class="inline-block px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition">
        व्हाट्सएप पर बात करें
      </a>
    </div>

    <div class="p-5 bg-white rounded-2xl border border-gray-200 text-center space-y-2">
      <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mx-auto text-lg font-bold">✉️</div>
      <h4 class="font-bold text-gray-900 text-sm">आधिकारिक ईमेल</h4>
      <p class="text-xs text-gray-500">info@ganvgalihat.com</p>
      <a href="mailto:info@ganvgalihat.com" class="inline-block px-3 py-1 bg-stone-900 hover:bg-black text-white font-bold text-xs rounded-lg transition">
        ईमेल भेजें
      </a>
    </div>

    <div class="p-5 bg-white rounded-2xl border border-gray-200 text-center space-y-2">
      <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center mx-auto text-lg font-bold">📍</div>
      <h4 class="font-bold text-gray-900 text-sm">केंद्रीय हब पता</h4>
      <p class="text-xs text-gray-500">G145 Phase 2 Industrial Area, UPSIDA Sandila, हरदोई, उप्र - 241204</p>
      <span class="inline-block px-3 py-1 bg-stone-100 text-stone-700 font-bold text-xs rounded-lg">स्पीड पोस्ट नोड</span>
    </div>
  </div>
</div>
"""
    }
}

with open(pages_file, 'w', encoding='utf-8') as f:
    json.dump(pages, f, indent=2, ensure_ascii=False)

print(f"Created {pages_file} with {len(pages)} comprehensive CMS pages.")
