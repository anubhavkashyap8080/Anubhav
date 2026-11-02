import re

path = r"C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\seller-register.php"
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update the Stepper HTML (lines 212-263)
old_stepper = """    <!-- Multi-Step Progress Tracker Bar -->
    <div class="bg-white rounded-3xl border border-gray-200 p-4 sm:p-6 shadow-sm mb-8">
      <!-- Progress Bar Track -->
      <div class="relative mb-6">
        <div class="h-1.5 w-full bg-stone-100 rounded-full overflow-hidden">
          <div id="stepProgressBar" class="h-full bg-amber-600 transition-all duration-500 ease-out" style="width: 20%;"></div>
        </div>
      </div>

      <!-- 5 Step Indicator Pills -->
      <div class="grid grid-cols-5 gap-1 sm:gap-2 text-center">
        <!-- Step 1 Tab -->
        <div id="stepTab1" class="step-tab cursor-pointer" onclick="goToStep(1)">
          <div class="step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-amber-600 text-white">
            1
          </div>
          <p class="step-label text-[10px] sm:text-xs font-bold mt-2 text-amber-700 truncate">1. Personal</p>
        </div>

        <!-- Step 2 Tab -->
        <div id="stepTab2" class="step-tab cursor-pointer" onclick="goToStep(2)">
          <div class="step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
            2
          </div>
          <p class="step-label text-[10px] sm:text-xs font-bold mt-2 text-stone-400 truncate">2. Address</p>
        </div>

        <!-- Step 3 Tab -->
        <div id="stepTab3" class="step-tab cursor-pointer" onclick="goToStep(3)">
          <div class="step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
            3
          </div>
          <p class="step-label text-[10px] sm:text-xs font-bold mt-2 text-stone-400 truncate">3. Documents</p>
        </div>

        <!-- Step 4 Tab -->
        <div id="stepTab4" class="step-tab cursor-pointer" onclick="goToStep(4)">
          <div class="step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
            4
          </div>
          <p class="step-label text-[10px] sm:text-xs font-bold mt-2 text-stone-400 truncate">4. Bank</p>
        </div>

        <!-- Step 5 Tab -->
        <div id="stepTab5" class="step-tab cursor-pointer" onclick="goToStep(5)">
          <div class="step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
            5
          </div>
          <p class="step-label text-[10px] sm:text-xs font-bold mt-2 text-stone-400 truncate">5. Submit</p>
        </div>
      </div>
    </div>"""

new_stepper = """    <!-- Multi-Step Progress Tracker Bar (Strictly Horizontal on All Mobile & Desktop Screens) -->
    <style>
      .stepper-horizontal-container {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        width: 100% !important;
        position: relative !important;
      }
      .stepper-horizontal-item {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
        cursor: pointer !important;
        position: relative !important;
        z-index: 2 !important;
      }
      .stepper-connector-line {
        position: absolute !important;
        top: 14px !important;
        left: 10% !important;
        right: 10% !important;
        height: 2px !important;
        background-color: #e7e5e4 !important;
        z-index: 1 !important;
      }
      .stepper-connector-fill {
        height: 100% !important;
        background-color: #d97706 !important;
        transition: width 0.4s ease-out !important;
      }
      @media (min-width: 640px) {
        .stepper-connector-line {
          top: 18px !important;
        }
      }
    </style>

    <div class="bg-white rounded-3xl border border-gray-200 p-3 sm:p-6 shadow-sm mb-8">
      <div class="relative w-full">
        <!-- Connecting Line Behind Step Pills -->
        <div class="stepper-connector-line">
          <div id="stepProgressBar" class="stepper-connector-fill" style="width: 20%;"></div>
        </div>

        <!-- 5 Step Indicator Pills (Forced Horizontal Flex Row) -->
        <div class="stepper-horizontal-container">
          <!-- Step 1 Tab -->
          <div id="stepTab1" class="stepper-horizontal-item step-tab" onclick="goToStep(1)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-amber-600 text-white ring-4 ring-amber-100">
              1
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-amber-700 truncate w-full px-0.5">1. Personal</p>
          </div>

          <!-- Step 2 Tab -->
          <div id="stepTab2" class="stepper-horizontal-item step-tab" onclick="goToStep(2)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
              2
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5">2. Address</p>
          </div>

          <!-- Step 3 Tab -->
          <div id="stepTab3" class="stepper-horizontal-item step-tab" onclick="goToStep(3)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
              3
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5">3. Docs</p>
          </div>

          <!-- Step 4 Tab -->
          <div id="stepTab4" class="stepper-horizontal-item step-tab" onclick="goToStep(4)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
              4
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5">4. Bank</p>
          </div>

          <!-- Step 5 Tab -->
          <div id="stepTab5" class="stepper-horizontal-item step-tab" onclick="goToStep(5)">
            <div class="step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500">
              5
            </div>
            <p class="step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5">5. Submit</p>
          </div>
        </div>
      </div>
    </div>"""

# 2. Update goToStep in JavaScript
old_js_classes = """    if (i === step) {
      p.classList.remove('hidden');
      ind.className = 'step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-amber-600 text-white';
      lbl.className = 'step-label text-[10px] sm:text-xs font-bold mt-2 text-amber-700 truncate';
    } else if (i < step) {
      p.classList.add('hidden');
      ind.className = 'step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-emerald-600 text-white';
      ind.innerHTML = '<i class="fa-solid fa-check text-xs"></i>';
      lbl.className = 'step-label text-[10px] sm:text-xs font-bold mt-2 text-emerald-700 truncate';
    } else {
      p.classList.add('hidden');
      ind.className = 'step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-2xl mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500';
      ind.innerText = i;
      lbl.className = 'step-label text-[10px] sm:text-xs font-bold mt-2 text-stone-400 truncate';
    }"""

new_js_classes = """    if (i === step) {
      p.classList.remove('hidden');
      ind.className = 'step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-amber-600 text-white ring-4 ring-amber-100';
      ind.innerText = i;
      lbl.className = 'step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-amber-700 truncate w-full px-0.5';
    } else if (i < step) {
      p.classList.add('hidden');
      ind.className = 'step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-emerald-600 text-white';
      ind.innerHTML = '<i class="fa-solid fa-check text-[10px] sm:text-xs"></i>';
      lbl.className = 'step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-emerald-700 truncate w-full px-0.5';
    } else {
      p.classList.add('hidden');
      ind.className = 'step-indicator w-7 h-7 sm:w-10 sm:h-10 rounded-full mx-auto flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-xs bg-stone-100 text-stone-500';
      ind.innerText = i;
      lbl.className = 'step-label text-[9px] sm:text-xs font-bold mt-1 sm:mt-2 text-stone-400 truncate w-full px-0.5';
    }"""

norm_content = content.replace('\r\n', '\n')
norm_old_stepper = old_stepper.replace('\r\n', '\n')
norm_old_js = old_js_classes.replace('\r\n', '\n')

if norm_old_stepper in norm_content:
    norm_content = norm_content.replace(norm_old_stepper, new_stepper)
    print("SUCCESS: Updated horizontal stepper in seller-register.php")
else:
    print("FAILED to find old stepper in seller-register.php")

if norm_old_js in norm_content:
    norm_content = norm_content.replace(norm_old_js, new_js_classes)
    print("SUCCESS: Updated goToStep JS in seller-register.php")
else:
    print("FAILED to find old JS in seller-register.php")

with open(path, 'w', encoding='utf-8') as f:
    f.write(norm_content)
