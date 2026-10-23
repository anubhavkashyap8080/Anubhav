<?php
$page_title = 'हमारे बारे में (About Us) — GaunGaliHat';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Story Section -->
<div class="bg-gradient-to-b from-stone-900 to-stone-800 text-white py-16 sm:py-24 relative overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold mb-4 border border-amber-500/30">
      <i class="fa-solid fa-wheat-awn"></i> S S Industries Enterprise
    </div>
    <h1 class="text-3xl sm:text-5xl font-black tracking-tight mb-4 text-white">
      गाँव, गली और हाट की धरोहर — सीधे आपके घर
    </h1>
    <p class="text-sm sm:text-base text-stone-300 max-w-2xl mx-auto leading-relaxed">
      हरदोई, उत्तर प्रदेश के ग्रामीण लोहारों, कुटीर कारीगरों, दाल उत्पादक किसानों और स्वयं सहायता समूहों का सशक्त डिजिटल मंच।
    </p>
  </div>
</div>

<div class="bg-stone-50 py-12">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
    
    <!-- Mission Statement 2-Col -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-white p-8 sm:p-12 rounded-3xl border border-gray-200 shadow-sm">
      <div class="space-y-4">
        <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
          हमारा उद्देश्य (Our Mission)
        </span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">
          पारंपरिक कुटीर कला एवं स्वदेशी औजारों को वैश्विक पहचान
        </h2>
        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
          <strong>एस. एस. इंडस्ट्रीज (S S Industries)</strong> द्वारा संचालित 'गाँव गली हाट' का मुख्य उद्देश्य बिचौलियों को समाप्त कर ग्रामीण कारीगर को उसकी कड़ी मेहनत का 100% सही मूल्य दिलाना है।
        </p>
        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
          चाहे वह हरदोई के लोहारों द्वारा पत्ती स्प्रिंग से हाथ से कूटा हुआ मजबूत हंसिया और खुरपी हो, या अवध के किसानों की बिना पॉलिश वाली शुद्ध अरहर व चना दाल, या फिर बनारसी कड़वे तेल का पारंपरिक आम का अचार — हर उत्पाद अपनी मिट्टी की खुशबू समेटे हुए है।
        </p>
      </div>

      <div class="relative rounded-2xl overflow-hidden shadow-lg border border-gray-200 h-80">
        <img src="https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80"
          alt="Traditional Iron Forge S S Industries" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
        <div class="absolute bottom-4 left-4 right-4 text-white text-xs">
          <p class="font-bold text-sm">एस. एस. इंडस्ट्रीज वर्कशॉप, नेवादा, हरदोई</p>
          <p class="text-[11px] opacity-80">कृषि औजार, कुल्हाड़ी व बागवानी टूल्स निर्माण इकाई</p>
        </div>
      </div>
    </div>

    <!-- 4 Core Values -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto text-xl">
          <i class="fa-solid fa-hammer"></i>
        </div>
        <h3 class="font-bold text-sm text-gray-900">हस्तनिर्मित व टिकाऊ</h3>
        <p class="text-xs text-gray-500">हर कृषि औजार पारंपरिक भट्टी में तपाकर और पानी में बुझाकर तैयार किया जाता है।</p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-brand-green flex items-center justify-center mx-auto text-xl">
          <i class="fa-solid fa-wheat-awn"></i>
        </div>
        <h3 class="font-bold text-sm text-gray-900">100% शुद्ध व अपोलिश</h3>
        <p class="text-xs text-gray-500">किसान समूहों की देसी दालें एवं तेल बिना किसी केमिकल या कृत्रिम रंग के।</p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto text-xl">
          <i class="fa-solid fa-truck-fast"></i>
        </div>
        <h3 class="font-bold text-sm text-gray-900">भारतीय डाक साझेदारी</h3>
        <p class="text-xs text-gray-500">हरदोई मुख्य डाकघर से सीधा स्पीड पोस्ट द्वारा देश के हर पिनकोड तक सुरक्षित वितरण।</p>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto text-xl">
          <i class="fa-solid fa-handshake"></i>
        </div>
        <h3 class="font-bold text-sm text-gray-900">महिला SHG सशक्तिकरण</h3>
        <p class="text-xs text-gray-500">जूट बैग एवं अचार निर्माण से 120+ ग्रामीण बहनों को नियमित आजीविका।</p>
      </div>
    </div>

    <!-- Company Locations -->
    <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm">
      <h3 class="text-lg font-bold text-gray-900 mb-6 text-center">एस. एस. इंडस्ट्रीज — हमारे केंद्र (Our Locations)</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-gray-700">
        <div class="p-5 rounded-2xl bg-stone-50 border border-gray-100 space-y-2">
          <div class="flex items-center gap-2 font-bold text-sm text-stone-900">
            <i class="fa-solid fa-industry text-amber-600"></i>
            कारखाना एवं निर्माण इकाई (Manufacturing Works)
          </div>
          <p class="text-gray-600 leading-relaxed">
            माधौगंज बघौली रोड, नेवादा, जिला - हरदोई, उत्तर प्रदेश - 241126
          </p>
          <p class="text-gray-500"><strong>कार्य:</strong> कृषि उपकरण, लोहारी भट्टी, हस्तशिल्प पैकेजिंग केंद्र</p>
        </div>

        <div class="p-5 rounded-2xl bg-stone-50 border border-gray-100 space-y-2">
          <div class="flex items-center gap-2 font-bold text-sm text-stone-900">
            <i class="fa-solid fa-building text-indigo-600"></i>
            प्रधान कार्यालय एवं औद्योगिक केंद्र (Head Office & Hub)
          </div>
          <p class="text-gray-600 leading-relaxed">
            G145 Phase 2 Industrial Area, UPSIDA Sandila, जिला - हरदोई, उत्तर प्रदेश - 241204
          </p>
          <p class="text-gray-500"><strong>कार्य:</strong> गुणवत्ता परीक्षण, अखिल भारतीय लॉजिस्टिक्स एवं तकनीकी संचालन</p>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
