with open('login.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update redirect logic when logged in
target_redir1 = """        if (in_array($logged_user['role'], ['super_admin', 'admin'])) {
            header('Location: /admin/index.php');
            exit;
        } elseif ($logged_user['role'] === 'vendor') {
            header('Location: /seller/dashboard.php');
            exit;
        } else {
            header('Location: /account.php');
            exit;
        }"""

new_redir1 = """        if (in_array($logged_user['role'], ['super_admin', 'admin'])) {
            header('Location: /admin/index.php');
            exit;
        } elseif ($logged_user['role'] === 'vendor') {
            header('Location: /seller/dashboard.php');
            exit;
        } elseif ($logged_user['role'] === 'retailer') {
            header('Location: /retailer/dashboard.php');
            exit;
        } else {
            header('Location: /account.php');
            exit;
        }"""

assert target_redir1 in content, "target_redir1 not found in login.php"
content = content.replace(target_redir1, new_redir1, 1)

# 2. Update role_group assignment on post
target_role_post = """        $role_group = null;
        if ($tab === 'seller') $role_group = 'seller';
        if ($tab === 'staff') $role_group = 'admin';"""

new_role_post = """        $role_group = null;
        if ($tab === 'seller') $role_group = 'seller';
        if ($tab === 'staff') $role_group = 'admin';
        if ($tab === 'retailer') $role_group = 'retailer';"""

assert target_role_post in content, "target_role_post not found in login.php"
content = content.replace(target_role_post, new_role_post, 1)

# 3. Update redirect after login
target_redir2 = """            // Direct according to role
            if (in_array($user['role'], ['super_admin', 'admin'])) {
                header('Location: /admin/index.php');
                exit;
            } elseif ($user['role'] === 'vendor') {
                header('Location: /seller/dashboard.php');
                exit;
            } else {
                header('Location: /account.php');
                exit;
            }"""

new_redir2 = """            // Direct according to role
            if (in_array($user['role'], ['super_admin', 'admin'])) {
                header('Location: /admin/index.php');
                exit;
            } elseif ($user['role'] === 'vendor') {
                header('Location: /seller/dashboard.php');
                exit;
            } elseif ($user['role'] === 'retailer') {
                header('Location: /retailer/dashboard.php');
                exit;
            } else {
                header('Location: /account.php');
                exit;
            }"""

assert target_redir2 in content, "target_redir2 not found in login.php"
content = content.replace(target_redir2, new_redir2, 1)

# 4. Update tab headers grid (change grid-cols-3 to grid-cols-4 and add retailer tab)
target_tabs = """      <!-- Tab Header: Shopper, Artisan/Seller, Staff -->
      <div class="grid grid-cols-3 border-b border-gray-200 bg-stone-100 text-center text-xs font-bold text-gray-500">
        <button type="button" onclick="switchLoginTab('shopper')" id="tab-btn-shopper"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'shopper' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-bag-shopping block text-sm mb-1"></i>
          Customer
        </button>
        <button type="button" onclick="switchLoginTab('seller')" id="tab-btn-seller"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'seller' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-store block text-sm mb-1"></i>
          Seller / Artisan
        </button>
        <button type="button" onclick="switchLoginTab('staff')" id="tab-btn-staff"
          class="py-3.5 px-2 transition border-b-2 <?= $active_tab === 'staff' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-shield-halved block text-sm mb-1"></i>
          Staff & Admin
        </button>
      </div>"""

new_tabs = """      <!-- Tab Header: Shopper, Retailer/B2B, Artisan/Seller, Staff -->
      <div class="grid grid-cols-4 border-b border-gray-200 bg-stone-100 text-center text-[11px] sm:text-xs font-bold text-gray-500">
        <button type="button" onclick="switchLoginTab('shopper')" id="tab-btn-shopper"
          class="py-3 px-1 transition border-b-2 <?= $active_tab === 'shopper' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-bag-shopping block text-sm mb-1"></i>
          Customer
        </button>
        <button type="button" onclick="switchLoginTab('retailer')" id="tab-btn-retailer"
          class="py-3 px-1 transition border-b-2 <?= $active_tab === 'retailer' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-boxes-stacked block text-sm mb-1"></i>
          Retailer B2B
        </button>
        <button type="button" onclick="switchLoginTab('seller')" id="tab-btn-seller"
          class="py-3 px-1 transition border-b-2 <?= $active_tab === 'seller' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-store block text-sm mb-1"></i>
          Seller
        </button>
        <button type="button" onclick="switchLoginTab('staff')" id="tab-btn-staff"
          class="py-3 px-1 transition border-b-2 <?= $active_tab === 'staff' ? 'border-brand-green text-brand-green bg-white' : 'border-transparent hover:text-gray-800' ?>">
          <i class="fa-solid fa-shield-halved block text-sm mb-1"></i>
          Staff
        </button>
      </div>"""

assert target_tabs in content, "target_tabs not found in login.php"
content = content.replace(target_tabs, new_tabs, 1)

# 5. Update banner text and classes for retailer
target_banner = """        <!-- Active Portal Banner -->
        <div id="portal-banner" class="mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 <?= $active_tab === 'staff' ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : ($active_tab === 'seller' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-emerald-50 text-emerald-900 border border-emerald-200') ?>">
          <i id="portal-banner-icon" class="fa-solid <?= $active_tab === 'staff' ? 'fa-user-tie' : ($active_tab === 'seller' ? 'fa-hammer' : 'fa-basket-shopping') ?>"></i>
          <span id="portal-banner-text">
            <?= $active_tab === 'staff' ? 'Staff & Administrative Control Portal' : ($active_tab === 'seller' ? 'Artisan Workshop & Seller Central Portal' : 'Customer Shopping & Orders Account') ?>
          </span>
        </div>"""

new_banner = """        <!-- Active Portal Banner -->
        <div id="portal-banner" class="mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 <?= $active_tab === 'staff' ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : ($active_tab === 'retailer' ? 'bg-blue-50 text-blue-900 border border-blue-200' : ($active_tab === 'seller' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-emerald-50 text-emerald-900 border border-emerald-200')) ?>">
          <i id="portal-banner-icon" class="fa-solid <?= $active_tab === 'staff' ? 'fa-user-tie' : ($active_tab === 'retailer' ? 'fa-boxes-stacked' : ($active_tab === 'seller' ? 'fa-hammer' : 'fa-basket-shopping')) ?>"></i>
          <span id="portal-banner-text">
            <?= $active_tab === 'staff' ? 'Staff & Administrative Control Portal' : ($active_tab === 'retailer' ? 'Retailer & B2B Wholesale Portal (थोक खरीद)' : ($active_tab === 'seller' ? 'Artisan Workshop & Seller Central Portal' : 'Customer Shopping & Orders Account')) ?>
          </span>
        </div>"""

assert target_banner in content, "target_banner not found in login.php"
content = content.replace(target_banner, new_banner, 1)

# 6. Update label and submit button defaults
target_id_lbl = "<?= $active_tab === 'staff' ? 'Admin Username or Official Email' : ($active_tab === 'seller' ? 'Seller Username / Email / Mobile' : 'Email Address or Mobile Number') ?>"
new_id_lbl = "<?= $active_tab === 'staff' ? 'Admin Username or Official Email' : ($active_tab === 'retailer' ? 'Retailer Mobile / Business Email / GSTIN' : ($active_tab === 'seller' ? 'Seller Username / Email / Mobile' : 'Email Address or Mobile Number')) ?>"
content = content.replace(target_id_lbl, new_id_lbl, 1)

target_btn_lbl = "<?= $active_tab === 'staff' ? 'Sign In as Staff' : ($active_tab === 'seller' ? 'Open Seller Dashboard' : 'Sign In') ?>"
new_btn_lbl = "<?= $active_tab === 'staff' ? 'Sign In as Staff' : ($active_tab === 'retailer' ? 'Sign In to Retailer Hub' : ($active_tab === 'seller' ? 'Open Seller Dashboard' : 'Sign In')) ?>"
content = content.replace(target_btn_lbl, new_btn_lbl, 1)

# 7. Update JS switchLoginTab
target_js = """function switchLoginTab(tab) {
  const tabs = ['shopper', 'seller', 'staff'];
  tabs.forEach(t => {
    const btn = document.getElementById('tab-btn-' + t);
    if (t === tab) {
      btn.className = 'py-3.5 px-2 transition border-b-2 border-brand-green text-brand-green bg-white';
    } else {
      btn.className = 'py-3.5 px-2 transition border-b-2 border-transparent hover:text-gray-800';
    }
  });

  document.getElementById('portal_tab_input').value = tab;

  const banner = document.getElementById('portal-banner');
  const icon = document.getElementById('portal-banner-icon');
  const text = document.getElementById('portal-banner-text');
  const btnLabel = document.getElementById('submit-btn-label');
  const idLabel = document.getElementById('identifier-label');

  if (tab === 'staff') {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-indigo-50 text-indigo-900 border border-indigo-200';
    icon.className = 'fa-solid fa-user-tie';
    text.innerText = 'Staff & Administrative Control Portal';
    btnLabel.innerText = 'Sign In as Staff';
    idLabel.innerText = 'Admin Username or Official Email';
  } else if (tab === 'seller') {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-amber-50 text-amber-900 border border-amber-200';
    icon.className = 'fa-solid fa-hammer';
    text.innerText = 'Artisan Workshop & Seller Central Portal';
    btnLabel.innerText = 'Open Seller Dashboard';
    idLabel.innerText = 'Seller Username / Email / Mobile';
  } else {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-emerald-50 text-emerald-900 border border-emerald-200';
    icon.className = 'fa-solid fa-basket-shopping';
    text.innerText = 'Customer Shopping & Orders Account';
    btnLabel.innerText = 'Sign In';
    idLabel.innerText = 'Email Address or Mobile Number';
  }"""

new_js = """function switchLoginTab(tab) {
  const tabs = ['shopper', 'retailer', 'seller', 'staff'];
  tabs.forEach(t => {
    const btn = document.getElementById('tab-btn-' + t);
    if (btn) {
      if (t === tab) {
        btn.className = 'py-3 px-1 transition border-b-2 border-brand-green text-brand-green bg-white font-black';
      } else {
        btn.className = 'py-3 px-1 transition border-b-2 border-transparent hover:text-gray-800';
      }
    }
  });

  document.getElementById('portal_tab_input').value = tab;

  const banner = document.getElementById('portal-banner');
  const icon = document.getElementById('portal-banner-icon');
  const text = document.getElementById('portal-banner-text');
  const btnLabel = document.getElementById('submit-btn-label');
  const idLabel = document.getElementById('identifier-label');

  if (tab === 'staff') {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-indigo-50 text-indigo-900 border border-indigo-200';
    icon.className = 'fa-solid fa-user-tie';
    text.innerText = 'Staff & Administrative Control Portal';
    btnLabel.innerText = 'Sign In as Staff';
    idLabel.innerText = 'Admin Username or Official Email';
  } else if (tab === 'retailer') {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-blue-50 text-blue-900 border border-blue-200';
    icon.className = 'fa-solid fa-boxes-stacked';
    text.innerText = 'Retailer & B2B Wholesale Portal (थोक खरीद)';
    btnLabel.innerText = 'Sign In to Retailer Hub';
    idLabel.innerText = 'Retailer Mobile / Business Email / GSTIN';
  } else if (tab === 'seller') {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-amber-50 text-amber-900 border border-amber-200';
    icon.className = 'fa-solid fa-hammer';
    text.innerText = 'Artisan Workshop & Seller Central Portal';
    btnLabel.innerText = 'Open Seller Dashboard';
    idLabel.innerText = 'Seller Username / Email / Mobile';
  } else {
    banner.className = 'mb-5 p-3 rounded-xl text-xs flex items-center gap-2.5 bg-emerald-50 text-emerald-900 border border-emerald-200';
    icon.className = 'fa-solid fa-basket-shopping';
    text.innerText = 'Customer Shopping & Orders Account';
    btnLabel.innerText = 'Sign In';
    idLabel.innerText = 'Email Address or Mobile Number';
  }"""

assert target_js in content, "target_js not found in login.php"
content = content.replace(target_js, new_js, 1)

# 8. Add link to retailer registration in footer of login card
target_reg_links = """        <!-- Registration Links -->
        <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-3">
          <div>
            Don't have an account? 
            <a href="/register.php" class="font-bold text-brand-green hover:underline">Register</a>
          </div>
          <div>
            Want to sell? 
            <a href="/seller-register.php" class="font-bold text-brand-orange hover:underline">Register as Artisan</a>
          </div>
        </div>"""

new_reg_links = """        <!-- Registration Links -->
        <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-3">
          <div>
            Customer: 
            <a href="/register.php" class="font-bold text-brand-green hover:underline">Register</a>
          </div>
          <div>
            Retailer / B2B: 
            <a href="/retailer-register.php" class="font-bold text-blue-600 hover:underline">Retailer Signup</a>
          </div>
          <div>
            Artisan / Seller: 
            <a href="/seller-register.php" class="font-bold text-brand-orange hover:underline">Sell on GGH</a>
          </div>
        </div>"""

if target_reg_links in content:
    content = content.replace(target_reg_links, new_reg_links, 1)

with open('login.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("login.php updated successfully")
