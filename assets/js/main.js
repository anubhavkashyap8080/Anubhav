/**
 * GaunGaliHat Core JavaScript Layer
 * AJAX Live Search, Cart, Wishlist & Shipping Estimator
 * Security Hardened: DOM XSS Mitigated & CSRF Protected (Item 16, 19)
 */

document.addEventListener('DOMContentLoaded', function () {
  initLiveSearch();
  initMobileDrawer();
});

// Item 16: Central CSRF Token Helper
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? (meta.getAttribute('content') || '') : '';
}

// Item 19: Strict Output Encoding Helper
function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// Item 19: Relative URL Sanitizer (Guarantees leading '/' and disallows protocol-relative '//')
function sanitizeUrl(url) {
  if (typeof url !== 'string') return '#';
  url = url.trim();
  if (url.startsWith('/') && !url.startsWith('//')) {
    return url;
  }
  return '#';
}

// Item 19: Image URL Sanitizer (Whitelists relative and secure HTTPS hosts)
function sanitizeImageUrl(url) {
  if (typeof url !== 'string') return '/logo.png';
  url = url.trim();
  if (url.startsWith('/') && !url.startsWith('//')) {
    return url;
  }
  if (/^https:\/\/[a-zA-Z0-9.\-_]+\//.test(url)) {
    return url;
  }
  return '/logo.png';
}

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

  const iconSpan = document.createElement('span');
  iconSpan.textContent = isErr ? '⚠️' : '✓';

  const msgSpan = document.createElement('span');
  msgSpan.className = 'flex-1';
  msgSpan.textContent = message || '';

  toast.appendChild(iconSpan);
  toast.appendChild(msgSpan);

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
// 1. AJAX LIVE SEARCH (Item 19: No innerHTML for user/server data)
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
        itemsContainer.innerHTML = '';

        if (!data.results || data.results.length === 0) {
          const emptyDiv = document.createElement('div');
          emptyDiv.className = 'p-6 text-center text-xs text-gray-500';

          const emptyTitle = document.createElement('p');
          emptyTitle.className = 'font-bold text-gray-800';
          emptyTitle.textContent = `No artisan tools or produce found for "${q}"`;

          const emptyDesc = document.createElement('p');
          emptyDesc.className = 'text-[11px] text-gray-400 mt-1';
          emptyDesc.textContent = 'Try searching for "hansiya", "dal", "achar", "khurpi", or "jute".';

          emptyDiv.appendChild(emptyTitle);
          emptyDiv.appendChild(emptyDesc);
          itemsContainer.appendChild(emptyDiv);

          if (viewAllBtn) viewAllBtn.classList.add('hidden');
        } else {
          if (viewAllBtn) {
            viewAllBtn.classList.remove('hidden');
            viewAllBtn.href = sanitizeUrl(data.view_all_url);
            viewAllBtn.textContent = `View all ${data.total} results for "${q}" →`;
          }

          data.results.forEach(item => {
            const safeUrl = sanitizeUrl(item.url);
            const safeImg = sanitizeImageUrl(item.image);

            const a = document.createElement('a');
            a.href = safeUrl;
            a.className = 'p-3 flex items-center gap-3 hover:bg-stone-50 transition-colors group';

            const img = document.createElement('img');
            img.src = safeImg;
            img.alt = item.name || '';
            img.className = 'w-12 h-12 object-cover rounded-xl border border-gray-100 shrink-0';

            const infoDiv = document.createElement('div');
            infoDiv.className = 'flex-1 min-w-0';

            const tagDiv = document.createElement('div');
            tagDiv.className = 'flex items-center gap-1.5 flex-wrap';

            const codeSpan = document.createElement('span');
            codeSpan.className = 'text-[10px] font-mono px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-semibold';
            codeSpan.textContent = item.code || '';

            const catSpan = document.createElement('span');
            catSpan.className = 'text-[10px] text-brand-green font-bold uppercase tracking-wider';
            catSpan.textContent = item.category_name || '';

            tagDiv.appendChild(codeSpan);
            tagDiv.appendChild(catSpan);

            const titleH4 = document.createElement('h4');
            titleH4.className = 'font-bold text-xs text-gray-900 group-hover:text-brand-green transition-colors truncate mt-0.5';
            titleH4.textContent = item.name || '';

            infoDiv.appendChild(tagDiv);
            infoDiv.appendChild(titleH4);

            if (item.hindi_name) {
              const hindiP = document.createElement('p');
              hindiP.className = 'text-[11px] text-gray-500 font-hindi truncate';
              hindiP.textContent = item.hindi_name;
              infoDiv.appendChild(hindiP);
            }

            const priceDiv = document.createElement('div');
            priceDiv.className = 'text-right shrink-0';

            const priceSpan = document.createElement('span');
            priceSpan.className = 'font-extrabold text-xs sm:text-sm text-gray-900 block';
            priceSpan.textContent = item.price || '';
            priceDiv.appendChild(priceSpan);

            if (item.discount > 0) {
              const discSpan = document.createElement('span');
              discSpan.className = 'text-[10px] text-brand-orange font-bold';
              discSpan.textContent = `${item.discount}% off`;
              priceDiv.appendChild(discSpan);
            }

            a.appendChild(img);
            a.appendChild(infoDiv);
            a.appendChild(priceDiv);
            itemsContainer.appendChild(a);
          });
        }
      })
      .catch(() => {
        if (spinner) spinner.classList.add('hidden');
        itemsContainer.innerHTML = '';
        const errDiv = document.createElement('div');
        errDiv.className = 'p-4 text-center text-xs text-red-500';
        errDiv.textContent = 'Search service temporarily unavailable.';
        itemsContainer.appendChild(errDiv);
      });
  }
}

// ==========================================
// 2. AJAX CART & WISHLIST HANDLERS + RESPONSIVE POPUP (Item 16: CSRF token)
// ==========================================
let cartPopupTimer = null;

function showCartPopup(product, cartCount, cartSubtotal) {
  let modal = document.getElementById('cartPopupModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'cartPopupModal';
    modal.className = 'fixed z-[9999] pointer-events-auto transition-all duration-300 transform hidden ' +
      'bottom-3 inset-x-3 sm:bottom-auto sm:inset-x-auto sm:top-5 sm:right-5 sm:max-w-md sm:w-96';
    document.body.appendChild(modal);
  }

  const pName = escapeHtml(product ? (product.name || 'Artisan Produce') : 'Artisan Produce');
  const pImg = sanitizeImageUrl(product && product.image ? product.image : '/logo.png');
  const pPrice = escapeHtml(product ? (product.price || '') : '');
  const pQty = escapeHtml(product ? (product.qty || 1) : 1);
  const subtotalStr = escapeHtml(cartSubtotal || '');
  const countStr = escapeHtml(cartCount !== undefined ? cartCount : 1);

  modal.innerHTML = `
    <div class="bg-stone-900/95 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-stone-700/80 p-4 transition-all">
      <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-stone-800">
        <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
          <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">✓</span>
          <span>Added to Shopping Bag!</span>
        </div>
        <button type="button" onclick="closeCartPopup()" class="text-stone-400 hover:text-white p-1 rounded-lg transition-colors cursor-pointer text-sm leading-none" aria-label="Close">
          ✕
        </button>
      </div>

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

  clearTimeout(cartPopupTimer);
  cartPopupTimer = setTimeout(() => {
    closeCartPopup();
  }, 4500);

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

// Item 16: Add to cart with CSRF token in header and body
function addToCart(productId, qty = 1, e = null) {
  if (e && typeof e.preventDefault === 'function') e.preventDefault();
  
  const csrf = getCsrfToken();
  const formData = new FormData();
  formData.append('action', 'add');
  formData.append('product_id', productId);
  formData.append('quantity', qty);
  if (csrf) formData.append('csrf_token', csrf);

  fetch('/ajax/cart_actions.php', {
    method: 'POST',
    headers: {
      'X-CSRF-Token': csrf
    },
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

// Item 16: Wishlist toggle with CSRF token in header and body
function toggleWishlist(productId, btn = null, e = null) {
  if (e && typeof e.preventDefault === 'function') e.preventDefault();

  const csrf = getCsrfToken();
  const formData = new FormData();
  formData.append('product_id', productId);
  if (csrf) formData.append('csrf_token', csrf);

  fetch('/ajax/wishlist_actions.php', {
    method: 'POST',
    headers: {
      'X-CSRF-Token': csrf
    },
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
  resultDiv.textContent = 'Calculating India Post delivery charge...';

  const csrf = getCsrfToken();
  const formData = new FormData();
  formData.append('pincode', pin);
  if (csrf) formData.append('csrf_token', csrf);

  fetch('/ajax/calculate_shipping.php', {
    method: 'POST',
    headers: {
      'X-CSRF-Token': csrf
    },
    body: formData
  })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        const d = res.data;
        const displayEl = document.getElementById('header-pincode-display');
        if (displayEl) displayEl.textContent = d.pincode;

        resultDiv.className = 'p-3 rounded-xl text-xs bg-emerald-50 border border-emerald-200 text-emerald-900 block space-y-1';
        resultDiv.innerHTML = '';

        const row = document.createElement('div');
        row.className = 'flex justify-between font-bold';
        const zoneSpan = document.createElement('span');
        zoneSpan.textContent = d.zone_name;
        const feeSpan = document.createElement('span');
        feeSpan.className = 'text-brand-green';
        feeSpan.textContent = d.shipping_fee_formatted;
        row.appendChild(zoneSpan);
        row.appendChild(feeSpan);
        resultDiv.appendChild(row);

        const etaP = document.createElement('p');
        etaP.className = 'text-[11px] text-emerald-700';
        etaP.textContent = `Estimated Delivery: ${d.eta_text} via ${d.carrier}`;
        resultDiv.appendChild(etaP);

        if (!d.is_free) {
          const promoP = document.createElement('p');
          promoP.className = 'text-[10px] text-amber-700 font-semibold';
          promoP.textContent = `💡 Add ${d.remaining_for_free_formatted} more for FREE Delivery!`;
          resultDiv.appendChild(promoP);
        }

        setTimeout(() => closePincodeModal(), 2500);
      } else {
        resultDiv.className = 'p-3 rounded-xl text-xs bg-red-50 border border-red-200 text-red-700 block';
        resultDiv.textContent = res.message || 'Invalid PIN Code.';
      }
    })
    .catch(() => {
      resultDiv.className = 'p-3 rounded-xl text-xs bg-red-50 text-red-700 block';
      resultDiv.textContent = 'Error calculating shipping.';
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
