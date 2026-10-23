/**
 * GaunGaliHat Core JavaScript
 * AJAX Live Search, Cart, Wishlist & Shipping Estimator
 */

document.addEventListener('DOMContentLoaded', function () {
  initLiveSearch();
  initMobileDrawer();
});

// Toast notification helper
function showToast(type, message) {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  const isErr = type === 'error';
  toast.className = `p-3.5 rounded-2xl shadow-xl border pointer-events-auto flex items-center gap-2.5 text-xs font-semibold text-white transition-all transform duration-300 translate-y-4 opacity-0 ${
    isErr ? 'bg-red-600 border-red-700' : 'bg-brand-green border-emerald-800'
  }`;
  toast.innerHTML = `
    <span>${isErr ? '⚠️' : '✓'}</span>
    <span class="flex-1">${message}</span>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.remove('translate-y-4', 'opacity-0');
  }, 10);

  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-2');
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// ==========================================
// 1. AJAX LIVE SEARCH
// ==========================================
function initLiveSearch() {
  const input = document.getElementById('header-live-search-input');
  const catSelect = document.getElementById('search-category-select');
  const dropdown = document.getElementById('live-search-dropdown');
  const itemsContainer = document.getElementById('live-search-items');
  const spinner = document.getElementById('live-search-spinner');
  const viewAllBtn = document.getElementById('live-search-view-all');
  const clearBtn = document.getElementById('search-clear-btn');

  if (!input || !dropdown) return;

  let debounceTimer = null;

  input.addEventListener('input', function () {
    const q = input.value.trim();
    if (clearBtn) clearBtn.classList.toggle('hidden', q.length === 0);

    clearTimeout(debounceTimer);
    if (q.length < 2) {
      dropdown.classList.add('hidden');
      return;
    }

    debounceTimer = setTimeout(() => {
      fetchLiveSearch(q, catSelect ? catSelect.value : '');
    }, 220);
  });

  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      input.value = '';
      clearBtn.classList.add('hidden');
      dropdown.classList.add('hidden');
      input.focus();
    });
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.add('hidden');
    }
  });

  function fetchLiveSearch(q, catId) {
    if (spinner) spinner.classList.remove('hidden');
    itemsContainer.innerHTML = '';
    dropdown.classList.remove('hidden');

    const url = `/ajax/live_search.php?q=${encodeURIComponent(q)}&category_id=${encodeURIComponent(catId)}`;

    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (spinner) spinner.classList.add('hidden');
        if (!data.results || data.results.length === 0) {
          itemsContainer.innerHTML = `
            <div class="p-6 text-center text-xs text-gray-500">
              <p class="font-bold text-gray-800">No artisan tools or produce found for "${q}"</p>
              <p class="text-[11px] text-gray-400 mt-1">Try searching for "hansiya", "dal", "achar", "khurpi", or "jute".</p>
            </div>
          `;
          viewAllBtn.classList.add('hidden');
        } else {
          viewAllBtn.classList.remove('hidden');
          viewAllBtn.href = data.view_all_url;
          viewAllBtn.innerText = `View all ${data.total} results for "${q}" →`;

          let html = '';
          data.results.forEach(item => {
            html += `
              <a href="${item.url}" class="p-3 flex items-center gap-3 hover:bg-stone-50 transition-colors group">
                <img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded-xl border border-gray-100 shrink-0">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-semibold">${item.code}</span>
                    <span class="text-[10px] text-brand-green font-bold uppercase tracking-wider">${item.category_name}</span>
                  </div>
                  <h4 class="font-bold text-xs text-gray-900 group-hover:text-brand-green transition-colors truncate mt-0.5">${item.name}</h4>
                  ${item.hindi_name ? `<p class="text-[11px] text-gray-500 font-hindi truncate">${item.hindi_name}</p>` : ''}
                </div>
                <div class="text-right shrink-0">
                  <span class="font-extrabold text-xs sm:text-sm text-gray-900 block">${item.price}</span>
                  ${item.discount > 0 ? `<span class="text-[10px] text-brand-orange font-bold">${item.discount}% off</span>` : ''}
                </div>
              </a>
            `;
          });
          itemsContainer.innerHTML = html;
        }
      })
      .catch(err => {
        spinner.classList.add('hidden');
        itemsContainer.innerHTML = `<div class="p-4 text-center text-xs text-red-500">Search service temporarily unavailable.</div>`;
      });
  }
}

// ==========================================
// 2. AJAX CART & WISHLIST HANDLERS
// ==========================================
function addToCart(productId, qty = 1, e = null) {
  if (e) e.preventDefault();
  
  const formData = new FormData();
  formData.append('action', 'add');
  formData.append('product_id', productId);
  formData.append('quantity', qty);

  fetch('/ajax/cart_actions.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast('success', data.message);
        updateCartBadge(data.cart_count);
      } else {
        showToast('error', data.message || 'Failed to add product.');
      }
    })
    .catch(() => showToast('error', 'Network error adding to cart.'));
}

function updateCartBadge(count) {
  const headerBadge = document.getElementById('header-cart-count');
  const mobileBadge = document.getElementById('mobile-cart-count');
  
  if (headerBadge) {
    headerBadge.innerText = count;
    headerBadge.classList.toggle('hidden', count === 0);
  }
  if (mobileBadge) {
    mobileBadge.innerText = count;
    mobileBadge.classList.toggle('hidden', count === 0);
  }
}

function toggleWishlist(productId, btn = null, e = null) {
  if (e) e.preventDefault();

  const formData = new FormData();
  formData.append('product_id', productId);

  fetch('/ajax/wishlist_actions.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast('success', data.message);
        const headerBadge = document.getElementById('header-wishlist-count');
        const mobileBadge = document.getElementById('mobile-wishlist-count');
        if (headerBadge) {
          headerBadge.innerText = data.wishlist_count;
          headerBadge.classList.toggle('hidden', data.wishlist_count === 0);
        }
        if (mobileBadge) {
          mobileBadge.innerText = data.wishlist_count;
          mobileBadge.classList.toggle('hidden', data.wishlist_count === 0);
        }

        if (btn) {
          btn.classList.toggle('text-brand-orange', data.in_wishlist);
          btn.classList.toggle('text-gray-400', !data.in_wishlist);
        }
      }
    })
    .catch(() => showToast('error', 'Wishlist update failed.'));
}

// ==========================================
// 3. PINCODE CHECKER MODAL
// ==========================================
function openPincodeModal() {
  const modal = document.getElementById('pincode-modal');
  if (modal) {
    modal.classList.remove('hidden');
    document.getElementById('modal-pincode-input')?.focus();
  }
}

function closePincodeModal() {
  const modal = document.getElementById('pincode-modal');
  if (modal) modal.classList.add('hidden');
}

function handlePincodeSubmit(e) {
  e.preventDefault();
  const input = document.getElementById('modal-pincode-input');
  const resultDiv = document.getElementById('modal-pincode-result');
  const pin = input.value.trim();

  if (pin.length !== 6) return;

  resultDiv.className = 'p-3 rounded-xl text-xs bg-gray-100 text-gray-700 block';
  resultDiv.innerHTML = 'Calculating India Post delivery charge...';

  const formData = new FormData();
  formData.append('pincode', pin);

  fetch('/ajax/calculate_shipping.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        const d = res.data;
        document.getElementById('header-pincode-display').innerText = d.pincode;
        resultDiv.className = 'p-3 rounded-xl text-xs bg-emerald-50 border border-emerald-200 text-emerald-900 block space-y-1';
        resultDiv.innerHTML = `
          <div class="flex justify-between font-bold">
            <span>${d.zone_name}</span>
            <span class="text-brand-green">${d.shipping_fee_formatted}</span>
          </div>
          <p class="text-[11px] text-emerald-700">Estimated Delivery: ${d.eta_text} via ${d.carrier}</p>
          ${!d.is_free ? `<p class="text-[10px] text-amber-700 font-semibold">💡 Add ${d.remaining_for_free_formatted} more for FREE Delivery!</p>` : ''}
        `;
        setTimeout(() => closePincodeModal(), 2500);
      } else {
        resultDiv.className = 'p-3 rounded-xl text-xs bg-red-50 border border-red-200 text-red-700 block';
        resultDiv.innerHTML = res.message || 'Invalid PIN Code.';
      }
    })
    .catch(() => {
      resultDiv.className = 'p-3 rounded-xl text-xs bg-red-50 text-red-700 block';
      resultDiv.innerHTML = 'Error calculating shipping.';
    });
}

// ==========================================
// 4. MOBILE DRAWER
// ==========================================
function initMobileDrawer() {
  const openBtn = document.getElementById('mobile-menu-open');
  const closeBtn = document.getElementById('mobile-menu-close');
  const drawer = document.getElementById('mobile-menu-drawer');

  if (openBtn && drawer) {
    openBtn.addEventListener('click', () => drawer.classList.remove('hidden'));
  }
  if (closeBtn && drawer) {
    closeBtn.addEventListener('click', () => drawer.classList.add('hidden'));
  }
  if (drawer) {
    drawer.addEventListener('click', (e) => {
      if (e.target === drawer) drawer.classList.add('hidden');
    });
  }
}
