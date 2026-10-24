/**
 * GaunGaliHat Core JavaScript
 * AJAX Live Search, Cart, Wishlist & Shipping Estimator
 */

document.addEventListener('DOMContentLoaded', function () {
  initLiveSearch();
  initMobileDrawer();
});

// Toast notification helper (Bottom on phone, Top-Right on desktop)
function showToast(type, message) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed z-[99999] pointer-events-none flex flex-col gap-2.5 bottom-4 inset-x-4 sm:bottom-auto sm:inset-x-auto sm:top-5 sm:right-5 sm:max-w-sm w-auto';
    document.body.appendChild(container);
  }

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
// 2. AJAX CART & WISHLIST HANDLERS + RESPONSIVE POPUP
// (Pops up at BOTTOM on phone screen & TOP on desktop screen)
// ==========================================
let cartPopupTimer = null;

function showCartPopup(product, cartCount, cartSubtotal) {
  let modal = document.getElementById('cartPopupModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'cartPopupModal';
    // Position: Bottom on phone (< sm), Top-Right on desktop (>= sm)
    modal.className = 'fixed z-[9999] pointer-events-auto transition-all duration-300 transform hidden ' +
      'bottom-3 inset-x-3 sm:bottom-auto sm:inset-x-auto sm:top-5 sm:right-5 sm:max-w-md sm:w-96';
    document.body.appendChild(modal);
  }

  const pName = product ? (product.name || 'Artisan Produce') : 'Artisan Produce';
  const pImg = (product && product.image) ? product.image : '/logo.png';
  const pPrice = product ? (product.price || '') : '';
  const pQty = product ? (product.qty || 1) : 1;
  const subtotalStr = cartSubtotal || '';
  const countStr = cartCount !== undefined ? cartCount : 1;

  modal.innerHTML = `
    <div class="bg-stone-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-stone-700/80 p-4 transition-all">
      <!-- Header Row -->
      <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-stone-800">
        <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
          <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">✓</span>
          <span>Added to Shopping Bag!</span>
        </div>
        <button type="button" onclick="closeCartPopup()" class="text-stone-400 hover:text-white p-1 rounded-lg transition-colors cursor-pointer text-sm leading-none" aria-label="Close">
          ✕
        </button>
      </div>

      <!-- Item Row -->
      <div class="flex items-center gap-3 mb-3">
        <img src="${pImg}" alt="${pName}" class="w-14 h-14 object-cover rounded-xl border border-stone-700 bg-stone-800 shrink-0">
        <div class="flex-1 min-w-0">
          <h4 class="font-bold text-xs sm:text-sm text-stone-100 truncate">${pName}</h4>
          <div class="flex items-center gap-2 mt-0.5 text-xs">
            <span class="font-black text-amber-400">${pPrice}</span>
            <span class="text-[11px] text-stone-400 bg-stone-800 px-1.5 py-0.5 rounded font-mono">Qty: ${pQty}</span>
          </div>
          <p class="text-[11px] text-stone-300 mt-1">Bag Total: <strong class="text-white">${subtotalStr}</strong> <span class="text-stone-400">(${countStr} items)</span></p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="grid grid-cols-2 gap-2 pt-1">
        <button type="button" onclick="closeCartPopup()" class="w-full py-2.5 px-3 bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-bold rounded-xl transition cursor-pointer text-center">
          Keep Shopping
        </button>
        <a href="/cart.php" class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer text-center">
          <span>View Bag</span>
          <span>→</span>
        </a>
      </div>
    </div>
  `;

  modal.classList.remove('hidden');

  // Auto-dismiss after 4.5 seconds
  clearTimeout(cartPopupTimer);
  cartPopupTimer = setTimeout(() => {
    closeCartPopup();
  }, 4500);

  // Pause timer on hover
  modal.onmouseenter = () => clearTimeout(cartPopupTimer);
  modal.onmouseleave = () => {
    cartPopupTimer = setTimeout(() => {
      closeCartPopup();
    }, 2500);
  };
}

function closeCartPopup() {
  const modal = document.getElementById('cartPopupModal');
  if (modal) {
    modal.classList.add('hidden');
  }
}

function addToCart(productId, qty = 1, e = null) {
  if (e && typeof e.preventDefault === 'function') e.preventDefault();
  
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
        showCartPopup(data.product, data.cart_count, data.cart_subtotal);
        updateCartBadge(data.cart_count, data.cart_subtotal);
        showToast('success', data.message || 'Product added to your cart!');
      } else {
        showToast('error', data.message || 'Failed to add product.');
      }
    })
    .catch(err => {
      console.error(err);
      showToast('error', 'Network error adding to cart.');
    });
}

function updateCartBadge(count, subtotal = null) {
  const headerBadge = document.getElementById('header-cart-count');
  const mobileBadge = document.getElementById('mobile-cart-count');
  const headerTotal = document.getElementById('header-cart-total');
  
  if (headerBadge) {
    headerBadge.innerText = count;
    headerBadge.classList.toggle('hidden', count === 0);
  }
  if (mobileBadge) {
    mobileBadge.innerText = count;
    mobileBadge.classList.toggle('hidden', count === 0);
  }
  if (headerTotal && subtotal) {
    headerTotal.innerText = subtotal;
  }
}

function toggleWishlist(productId, btn = null, e = null) {
  if (e && typeof e.preventDefault === 'function') e.preventDefault();

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
          btn.classList.toggle('text-red-500', data.in_wishlist);
          btn.classList.toggle('text-gray-400', !data.in_wishlist);

          const svg = btn.querySelector('svg');
          if (svg) {
            if (data.in_wishlist) {
              svg.setAttribute('fill', '#ea580c');
              svg.style.color = '#ea580c';
            } else {
              svg.setAttribute('fill', 'none');
              svg.style.color = '';
            }
          }
        }
      } else {
        showToast('error', data.message || 'Wishlist update failed.');
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
