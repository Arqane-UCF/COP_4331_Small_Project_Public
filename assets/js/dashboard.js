/* ================================
   dashboard.js — organized by feature
   ================================ */

/* ---------- Config / Selectors ---------- */
const MAIN_COL_SEL            = '.cards-col';
const FAVORITES_SECTION_SEL   = '.favorites';
const FAVORITES_ROW_ID        = 'favorites-col';
const FAVORITES_EMPTY_ID      = 'fav-empty';
const CREATED_DEMO_KEY        = 'cc_demo_created_map';

/* ---------- Generic Helpers ---------- */
function safeAlert() {}

function ensureErrorEl(inputEl) {
  if (!inputEl) return null;
  let help = inputEl.nextElementSibling;
  if (!help || !help.classList || !help.classList.contains('c-help--error')) {
    help = document.createElement('div');
    help.className = 'c-help c-help--error';
    inputEl.insertAdjacentElement('afterend', help);
  }
  return help;
}
function showFieldError(inputEl, msg) {
  if (!inputEl) return;
  inputEl.classList.add('is-error');
  inputEl.setAttribute('aria-invalid', 'true');
  const help = ensureErrorEl(inputEl);
  if (help) help.textContent = msg || '';
}
function clearFieldError(inputEl) {
  if (!inputEl) return;
  inputEl.classList.remove('is-error');
  inputEl.removeAttribute('aria-invalid');
  const help = inputEl.nextElementSibling;
  if (help && help.classList.contains('c-help--error')) help.textContent = '';
}
function clearErrors(inputs) { inputs.forEach(clearFieldError); }

function sanitizePhoneValue(v) { return (v || '').replace(/[^\d+\-().\s]/g, ''); }
function attachPhoneGuards(selector) {
  const el = document.querySelector(selector);
  if (!el) return;
  const handler = (e) => {
    const cleaned = sanitizePhoneValue(e.target.value);
    if (cleaned !== e.target.value) e.target.value = cleaned;
  };
  el.addEventListener('input', handler);
  el.addEventListener('paste', () => setTimeout(() => handler({ target: el }), 0));
}

function splitName(full) {
  const s = (full || '').trim().replace(/\s+/g, ' ');
  const i = s.lastIndexOf(' ');
  return i > 0 ? [s.slice(0, i), s.slice(i + 1)] : [s, ''];
}

function extractContactId(root) {
  if (!root) return '';
  const direct = root.dataset?.contactId || root.getAttribute?.('data-contact-id') || root.getAttribute?.('data-id');
  if (direct) return String(direct);
  const inDelete = root.querySelector?.('.cc-delete-form input[name="id"]')?.value;
  if (inDelete) return String(inDelete);
  const inFav = root.querySelector?.('.cc-fav-form input[name="id"]')?.value;
  if (inFav) return String(inFav);
  const anyDataId = root.querySelector?.('[data-contact-id]')?.getAttribute('data-contact-id')
                   || root.querySelector?.('[data-id]')?.getAttribute('data-id');
  return anyDataId ? String(anyDataId) : '';
}

function updateEmptyState() {
  const col = document.querySelector(MAIN_COL_SEL);
  if (!col) return;
  const hasCard = !!col.querySelector('.contact-card');
  const empty = col.querySelector('.empty');
  if (!hasCard && !empty) {
    const p = document.createElement('p');
    p.className = 'empty';
    p.textContent = 'You have no contacts. Click “+ New Contact” to start keeping track of them.';
    col.appendChild(p);
  } else if (hasCard && empty) {
    empty.remove();
  }
}

/* ---------- “Date created” (local demo) ---------- */
function loadDemoCreated() { try { return JSON.parse(localStorage.getItem(CREATED_DEMO_KEY) || '{}'); } catch { return {}; } }
function saveDemoCreated(map) { try { localStorage.setItem(CREATED_DEMO_KEY, JSON.stringify(map)); } catch {} }
function removeDemoCreated(id) { if (!id) return; const map = loadDemoCreated(); if (map[id]) { delete map[id]; saveDemoCreated(map); } }
function ensureDemoCreated(card) {
  const id = extractContactId(card) || card.dataset.contactId;
  if (!id) return;
  const map = loadDemoCreated();
  if (!map[id]) { map[id] = new Date().toISOString(); saveDemoCreated(map); }
  const el = card.querySelector('.cc-created');
  if (el) {
    el.textContent = `Date created: ${new Date(map[id]).toLocaleDateString(undefined, { year:'numeric', month:'short', day:'numeric' })}`;
  }
}

/* ---------- Tags / Filters ---------- */
async function refreshLabels() {
  const holder = document.getElementById('labels-list');
  if (!holder) return;
  try {
    const res = await fetch('/api/tag.php?limit=50', { credentials: 'include' });
    const j = await res.json();
    if (!j?.success || !Array.isArray(j.tags)) return;
    const arr = j.tags.slice().sort((a,b)=>a.localeCompare(b, undefined, {sensitivity:'base'}));
    holder.innerHTML = '';
    if (!arr.length) { holder.innerHTML = '<p class="empty">No tags found.</p>'; return; }
    for (const tag of arr) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'tag tag--filter';
      btn.setAttribute('aria-pressed', 'false');
      btn.innerHTML = `<span class="tag-icon" aria-hidden="true"></span><span class="tag-label"></span>`;
      btn.querySelector('.tag-label').textContent = tag;
      holder.appendChild(btn);
    }
  } catch (e) { console.error('refreshLabels failed', e); }
}
function initFilterTags(scopeSelector) {
  const scope = document.querySelector(scopeSelector || 'body');
  if (!scope) return;
  scope.addEventListener('click', (e) => {
    const btn = e.target.closest('.tag.tag--filter');
    if (!btn || !scope.contains(btn)) return;
    const pressed = btn.getAttribute('aria-pressed') === 'true';
    btn.setAttribute('aria-pressed', String(!pressed));
    btn.classList.toggle('is-selected', !pressed);
  });
}

/* ---------- Card Template ---------- */
function contactCardHTML(c) {
  const favActive = c.isFavorite ? ' active' : '';
  const starFill  = c.isFavorite ? 'var(--accent)' : 'none';
  const safe = (s) => (s ?? '');
  return `
<article class="contact-card" data-contact-id="${String(c.id)}">
  <div class="cc-left" aria-hidden="true"></div>
  <div class="cc-body">
    <h3 class="cc-name">${safe(c.name)}</h3>
    ${c.email ? `<p class="cc-email">${safe(c.email)}</p>` : ''}
    ${c.phone ? `<p class="cc-phone">${safe(c.phone)}</p>` : ''}
    <p class="cc-created"></p>
  </div>
  <div class="cc-actions-top">
    <form class="cc-fav-form" data-contact-id="${String(c.id)}">
      <input type="hidden" name="id" value="${String(c.id)}">
      <input type="hidden" name="favorited" value="${c.isFavorite ? '1' : '0'}">
      <button type="button" class="cc-icon cc-favorite${favActive}" aria-label="${c.isFavorite ? 'Unfavorite' : 'Favorite'}" aria-pressed="${c.isFavorite ? 'true' : 'false'}" title="${c.isFavorite ? 'Unfavorite' : 'Favorite'}">
        <svg viewBox="0 0 28 28" width="24" height="24" aria-hidden="true">
          <path d="M6.8 24.5L8.7 16.3 2.33 10.79 10.73 10.06 14 2.33 17.27 10.06 25.67 10.79 19.31 16.3 21.2 24.5 14 20.15 6.8 24.5Z"
                fill="${starFill}" stroke="var(--accent)" stroke-linejoin="round"/>
        </svg>
      </button>
    </form>
    <a class="cc-icon" aria-label="Edit" title="Edit" href="javascript:void(0);" data-action="edit" data-contact-id="${String(c.id)}">
      <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
        <path d="M16.99 3.9a2.3 2.3 0 013.26 3.11L8.9 18.36a3 3 0 01-1.69.86l-3.72.95.95-3.73c.08-.31.25-.6.49-.84L16.99 3.9Z" fill="var(--primary)"/>
      </svg>
    </a>
  </div>
  <div class="cc-actions-bottom">
    <form class="cc-delete-form" data-contact-id="${String(c.id)}">
      <input type="hidden" name="id" value="${String(c.id)}">
      <button type="button" class="cc-icon cc-delete" aria-label="Delete" title="Delete" data-action="delete">
        <svg viewBox="0 0 28 28" width="24" height="24" aria-hidden="true">
          <path d="M4.375 6.125h19.25m-13.125 0V3.938A1.31 1.31 0 0111.813 2.625h4.375A1.31 1.31 0 0117.5 3.938V6.125M6.125 6.125 7.219 23.625c.06 1.011.796 1.75 1.758 1.75h10.062c.966 0 1.688-.739 1.75-1.75L21.875 6.125M14 9.625V21.875M10.063 9.625 10.5 21.875M17.938 9.625 17.5 21.875"
                stroke="var(--primary)" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </form>
  </div>
</article>`;
}

/* ================================
   FAVORITES — UI + behavior
   ================================ */
function applyFavoriteTileStyles(card) {
  if (!card) return;
  Object.assign(card.style, {
    flex: '0 0 auto',
    width: '320px',
    maxWidth: '320px',
    margin: '0',
    scrollSnapAlign: 'start'
  });
}
function clearFavoriteTileStyles(card) {
  if (!card) return;
  card.style.flex = '';
  card.style.width = '';
  card.style.maxWidth = '';
  card.style.margin = '';
  card.style.scrollSnapAlign = '';
}
function ensureFavoritesRow() {
  const favSection = document.querySelector(FAVORITES_SECTION_SEL);
  if (!favSection) return null;
  favSection.querySelector(`#${FAVORITES_EMPTY_ID}`)?.remove();
  let row = document.getElementById(FAVORITES_ROW_ID);
  if (!row) {
    row = document.createElement('div');
    row.id = FAVORITES_ROW_ID;
    row.className = 'cards-row';
    favSection.appendChild(row);
  }
  return row;
}
function forceFavoritesScroller() {
  const row = document.getElementById(FAVORITES_ROW_ID);
  if (!row) return;
  Object.assign(row.style, {
    display: 'flex',
    flexWrap: 'nowrap',
    gap: '16px',
    width: '100%',
    overflowX: 'auto',
    overflowY: 'hidden',
    paddingBottom: '24px',
    WebkitOverflowScrolling: 'touch',
    scrollSnapType: 'x proximity'
  });
  row.querySelectorAll('.contact-card').forEach(applyFavoriteTileStyles);
}
function setFavoritesEmptyState() {
  const favSection = document.querySelector(FAVORITES_SECTION_SEL);
  if (!favSection) return;
  const row = document.getElementById(FAVORITES_ROW_ID);
  if (row && row.querySelector('.contact-card')) {
    favSection.querySelector(`#${FAVORITES_EMPTY_ID}`)?.remove();
    forceFavoritesScroller();
    return;
  }
  row?.remove();
  let p = favSection.querySelector(`#${FAVORITES_EMPTY_ID}`);
  if (!p) {
    p = document.createElement('p');
    p.id = FAVORITES_EMPTY_ID;
    p.className = 'empty';
    p.textContent = 'You have no favorited contacts. Click the “★” icon to display them here.';
    favSection.appendChild(p);
  }
}
function syncFavoritesUI() {
  const row = document.getElementById(FAVORITES_ROW_ID);
  if (row && row.querySelector('.contact-card')) {
    forceFavoritesScroller();
  } else {
    setFavoritesEmptyState();
  }
}
function observeFavorites() {
  const favSection = document.querySelector(FAVORITES_SECTION_SEL);
  if (!favSection) return;
  const mo = new MutationObserver(() => syncFavoritesUI());
  mo.observe(favSection, { childList: true, subtree: true });
}
function findCardIn(sel, id) {
  const scope = document.querySelector(sel);
  if (!scope || !id) return null;
  return scope.querySelector(`.contact-card[data-contact-id="${CSS.escape(String(id))}"]`)
      || scope.querySelector(`.contact-card [value="${CSS.escape(String(id))}"]`)?.closest('.contact-card')
      || null;
}
function setStarUI(card, isFav) {
  if (!card) return;
  const btn   = card.querySelector('.cc-fav-form .cc-favorite');
  const input = card.querySelector('.cc-fav-form input[name="favorited"]');
  if (input) input.value = isFav ? '1' : '0';
  if (btn) {
    btn.classList.toggle('active', !!isFav);
    btn.setAttribute('aria-pressed', isFav ? 'true' : 'false');
    const star = btn.querySelector('svg path, svg polygon, svg rect, svg circle');
    if (star) star.setAttribute('fill', isFav ? 'var(--accent)' : 'none');
  }
}
function moveCardToFavorites(id) {
  const src = findCardIn(MAIN_COL_SEL, id);
  if (!src) return;
  const favRow = ensureFavoritesRow();
  if (!favRow) return;
  setStarUI(src, true);
  favRow.prepend(src);
  syncFavoritesUI();
  updateEmptyState();
}
function moveCardToMain(id) {
  const src = findCardIn(`#${FAVORITES_ROW_ID}`, id);
  if (!src) return;
  const main = document.querySelector(MAIN_COL_SEL);
  if (!main) return;
  setStarUI(src, false);
  src.removeAttribute('style');
  main.prepend(src);
  updateEmptyState();
  syncFavoritesUI();
}
function reconcileFavorites() {
  const main = document.querySelector(MAIN_COL_SEL);
  const favSection = document.querySelector(FAVORITES_SECTION_SEL);
  if (!main || !favSection) return;

  let row = document.getElementById(FAVORITES_ROW_ID);
  if (!row) {
    row = document.createElement('div');
    row.id = FAVORITES_ROW_ID;
    row.className = 'cards-row';
    favSection.appendChild(row);
  }

  const favIds = new Set();
  row.querySelectorAll('.contact-card').forEach(card => {
    const id = extractContactId(card);
    if (id) {
      favIds.add(id);
      setStarUI(card, true);
      applyFavoriteTileStyles(card);
    }
  });

  main.querySelectorAll('.contact-card').forEach(card => {
    const id = extractContactId(card);
    if (!id) return;
    const isFav = card.querySelector('.cc-fav-form input[name="favorited"]')?.value === '1'
               || card.querySelector('.cc-favorite')?.classList.contains('active');

    if (isFav) {
      if (favIds.has(id)) {
        card.remove(); // remove duplicate from main
      } else {
        setStarUI(card, true);
        applyFavoriteTileStyles(card);
        row.prepend(card);
        favIds.add(id);
      }
    }
  });

  syncFavoritesUI();
  updateEmptyState();
}
function bindFavoriteHandlers() {
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cc-fav-form .cc-favorite');
    if (!btn) return;
    e.preventDefault();

    const form     = btn.closest('.cc-fav-form');
    const id       = form?.querySelector('input[name="id"]')?.value
                  || extractContactId(btn.closest('.contact-card'));
    const favInput = form?.querySelector('input[name="favorited"]');
    if (!id || !favInput) return;

    const isCurrentlyFav = btn.classList.contains('active') || favInput.value === '1';
    const next = isCurrentlyFav ? 0 : 1;

    try {
      const res = await fetch(`/api/contact.php?id=${encodeURIComponent(id)}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
        body: new URLSearchParams({ favorite: String(next) }).toString(),
        credentials: 'include'
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.error || 'Toggle failed');

      if (next) moveCardToFavorites(id);
      else      moveCardToMain(id);
    } catch (err) {
      console.error('Favorite toggle error:', err);
    }
  });
}

/* ================================
   CREATE — modal + API + insert
   ================================ */
async function createContact(payload) {
  const [first, last] = splitName(payload.name || '');
  const params = new URLSearchParams({
    firstName: first,
    lastName : last,
    email    : payload.email || '',
    phone    : payload.phone || ''
  });

  const res = await fetch('/api/contact.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
    body: params.toString(),
    credentials: 'include'
  });

  const text = await res.text();
  let data = null;
  try { data = JSON.parse(text); } catch {
    throw new Error(`Invalid JSON from server (status ${res.status}): ${text.slice(0,200)}`);
  }
  if (!res.ok || !data.success) throw new Error(data?.error || `Request failed (${res.status})`);

  const contact = {
    id: data.id,
    name: payload.name,
    email: payload.email,
    phone: payload.phone,
    isFavorite: false
  };
  return { card_html: contactCardHTML(contact) };
}

function prependNewContactCard(card_html) {
  const col = document.querySelector(MAIN_COL_SEL);
  if (!col) return;
  col.querySelector('.empty')?.remove();
  const wrap = document.createElement('div');
  wrap.innerHTML = card_html;
  let node = wrap.querySelector('.contact-card') || wrap.firstElementChild;
  if (!node) return;
  const id = extractContactId(node);
  if (id) node.dataset.contactId = id;
  node.querySelectorAll('[data-action="edit"]').forEach(btn => btn.setAttribute('data-contact-id', id));
  node.querySelectorAll('.cc-delete-form [name="id"]').forEach(input => { if (!input.value && id) input.value = id; });
  node.querySelectorAll('.cc-fav-form [name="id"]').forEach(input => { if (!input.value && id) input.value = id; });
  col.prepend(node);
  ensureDemoCreated(node);
}

function bindCreateUI() {
  const modal = document.getElementById('modal-add-contact');
  if (!modal || modal.dataset.bound === '1') return;
  modal.dataset.bound = '1';

  const nameEl  = modal.querySelector('#ac-name');
  const emailEl = modal.querySelector('#ac-email');
  const phoneEl = modal.querySelector('#ac-phone');
  attachPhoneGuards('#ac-phone');

  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-modal-open="#modal-add-contact"]');
    if (!opener) return;
    e.preventDefault();
    if (nameEl)  nameEl.value  = '';
    if (emailEl) emailEl.value = '';
    if (phoneEl) phoneEl.value = '';
    modal.classList.add('is-open');
    modal.removeAttribute('aria-hidden');
    document.body.style.overflow = 'hidden';
    modal.querySelector('input,button,textarea,select,[href],[tabindex]:not([tabindex="-1"])')?.focus();
  }, { passive: false });

  modal.addEventListener('click', async (e) => {
    const btn = e.target.closest('#ac-save');
    if (!btn) return;
    e.preventDefault();
    if (btn.dataset.busy === '1') return;
    btn.dataset.busy = '1';
    btn.disabled = true;
    try {
      const nameVal  = (nameEl?.value || '').trim();
      const emailVal = (emailEl?.value || '').trim();
      const phoneVal = (phoneEl?.value || '').trim();
      [nameEl, emailEl, phoneEl].forEach(el => el && el.addEventListener('input', () => clearFieldError(el), { once: true }));
      clearErrors([nameEl, emailEl, phoneEl]);

      let ok = true;
      if (!nameVal) { showFieldError(nameEl, 'Name is required.'); ok = false; }
      else if (nameVal.length > 30) { showFieldError(nameEl, 'Max 30 characters.'); ok = false; }
      const emailOk = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(emailVal);
      if (!emailVal) { showFieldError(emailEl, 'Email is required.'); ok = false; }
      else if (!emailOk) { showFieldError(emailEl, 'Enter a valid email, e.g. name@example.com.'); ok = false; }
      const phoneOk = /^[0-9+\-().\s]*$/.test(phoneVal);
      if (!phoneVal) { showFieldError(phoneEl, 'Phone is required.'); ok = false; }
      else if (!phoneOk || phoneVal.length > 20) {
        showFieldError(phoneEl, 'Up to 20 chars. Use digits and + - ( ) . spaces only.');
        ok = false;
      }
      if (!ok) { modal.querySelector('.is-error')?.focus(); return; }

      const { card_html } = await createContact({ name: nameVal, email: emailVal, phone: phoneVal, tags: [] });
      prependNewContactCard(card_html);

      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (nameEl)  nameEl.value  = '';
      if (emailEl) emailEl.value = '';
      if (phoneEl) phoneEl.value = '';
      updateEmptyState();
    } catch (err) {
      console.error('Create contact error:', err);
    } finally {
      btn.disabled = false;
      btn.dataset.busy = '0';
    }
  });
}

/* ================================
   EDIT — open modal + save
   ================================ */
let __currentEditCardId = '';

function bindEditHandlers() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="edit"]');
    if (!btn) return;
    e.preventDefault();
    const card = btn.closest('.contact-card');
    if (!card) return;
    const id = btn.getAttribute('data-contact-id') || extractContactId(card);
    __currentEditCardId = id || '';
    const name  = card.querySelector('.cc-name')?.textContent.trim() || '';
    const email = card.querySelector('.cc-email')?.textContent.trim() || '';
    const phone = card.querySelector('.cc-phone')?.textContent.trim() || '';
    const modal = document.querySelector('#modal-edit-contact');
    if (!modal) { console.error('Edit modal #modal-edit-contact not found.'); return; }
    modal.dataset.contactId = __currentEditCardId;
    modal.querySelector('#ec-name').value  = name;
    modal.querySelector('#ec-email').value = email;
    modal.querySelector('#ec-phone').value = phone;
    attachPhoneGuards('#ec-phone');
    modal.classList.add('is-open');
    modal.removeAttribute('aria-hidden');
    document.body.style.overflow = 'hidden';
    modal.querySelector('input,button,textarea,select,[href],[tabindex]:not([tabindex="-1"])')?.focus();
  });
}

function bindEditSave() {
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('#ec-save');
    if (!btn) return;
    const modal = document.querySelector('#modal-edit-contact');
    if (!modal) return;
    const id = modal.dataset.contactId || __currentEditCardId || '';
    if (!id) { console.error('Edit save: missing contact id'); return; }

    const nameEl  = modal.querySelector('#ec-name');
    const emailEl = modal.querySelector('#ec-email');
    const phoneEl = modal.querySelector('#ec-phone');
    const name  = nameEl?.value?.trim()  || '';
    const email = emailEl?.value?.trim() || '';
    const phone = phoneEl?.value?.trim() || '';
    [nameEl, emailEl, phoneEl].forEach(el => el && el.addEventListener('input', () => clearFieldError(el), { once: true }));
    clearErrors([nameEl, emailEl, phoneEl]);

    let ok = true;
    if (!name) { showFieldError(nameEl, 'Name is required.'); ok = false; }
    else if (name.length > 30) { showFieldError(nameEl, 'Max 30 characters.'); ok = false; }
    const emailOk = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email);
    if (!email) { showFieldError(emailEl, 'Email is required.'); ok = false; }
    else if (!emailOk) { showFieldError(emailEl, 'Enter a valid email, e.g. name@example.com.'); ok = false; }
    const phoneOk = /^[0-9+\-().\s]*$/.test(phone);
    if (!phone) { showFieldError(phoneEl, 'Phone is required.'); ok = false; }
    else if (!phoneOk || phone.length > 20) {
      showFieldError(phoneEl, 'Up to 20 chars. Use digits and + - ( ) . spaces only.');
      ok = false;
    }
    if (!ok) { modal.querySelector('.is-error')?.focus(); return; }

    try {
      btn.disabled = true;
      const [first, last] = splitName(name);
      const res = await fetch(`/api/contact.php?id=${encodeURIComponent(id)}`, {
        method: 'PATCH',
        headers: new Headers({ 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' }),
        body: new URLSearchParams({ firstName:first, lastName:last, email, phone }).toString(),
        credentials: 'include'
      });
      const text = await res.text();
      let data;
      try { data = JSON.parse(text); }
      catch (e2) { throw new Error(`Invalid JSON (${res.status}). Body: ${text.slice(0, 500)}`); }
      if (!res.ok || !data.success) throw new Error(data?.error || `Update failed (${res.status})`);

      const card = document.querySelector(`.contact-card[data-contact-id="${CSS.escape(id)}"]`)
                || document.querySelector(`.contact-card [value="${CSS.escape(id)}"]`)?.closest('.contact-card');

      if (card) {
        card.querySelector('.cc-name')?.replaceChildren(document.createTextNode(name));
        const emailElCard = card.querySelector('.cc-email');
        if (emailElCard) emailElCard.textContent = email; else if (email) {
          const p = document.createElement('p'); p.className='cc-email'; p.textContent=email;
          card.querySelector('.cc-body')?.insertBefore(p, card.querySelector('.cc-phone'));
        }
        const phoneElCard = card.querySelector('.cc-phone');
        if (phoneElCard) phoneElCard.textContent = phone; else if (phone) {
          const p = document.createElement('p'); p.className='cc-phone'; p.textContent=phone;
          card.querySelector('.cc-body')?.appendChild(p);
        }
      }

      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden','true');
      document.body.style.overflow = '';
    } catch (err) {
      console.error('Edit save error:', err);
    } finally {
      btn.disabled = false;
    }
  });
}

/* ================================
   DELETE — open modal + confirm
   ================================ */

   let __pendingDelete = { id: '', card: null };

   function bindDeleteHandlers() {
     document.addEventListener('click', (e) => {
       const btn = e.target.closest('.cc-delete-form [data-action="delete"]');
       if (!btn) return;
       e.preventDefault();
   
       const form = btn.closest('.cc-delete-form');
       const card = btn.closest('.contact-card');
       const id   = form?.querySelector('input[name="id"]')?.value
                 || card?.dataset.contactId
                 || extractContactId(card);
   
       if (!id || !card) return;
   
       const modal = document.getElementById('modal-delete-contact');
       if (!modal) { console.error('Delete modal not found'); return; }
   
       __pendingDelete.id   = String(id);
       __pendingDelete.card = card;
   
       const nameTxt = card.querySelector('.cc-name')?.textContent?.trim() || 'this contact';
   
       modal.dataset.contactId = __pendingDelete.id;
       const nameEl = modal.querySelector('#dc-name');
       if (nameEl) nameEl.textContent = nameTxt;
   
       modal.classList.add('is-open');
       modal.removeAttribute('aria-hidden');
       document.body.style.overflow = 'hidden';
   
       // focus a sensible first control
       (modal.querySelector('#dc-confirm, [data-action="confirm"], button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])') || {}).focus?.();
     }, { passive: false });
   }
   
   function setupDeleteModal() {
     const modal = document.getElementById('modal-delete-contact');
     if (!modal || modal.dataset.bound === '1') return;
     modal.dataset.bound = '1';
   
     const close = () => {
       modal.classList.remove('is-open');
       modal.setAttribute('aria-hidden', 'true');
       document.body.style.overflow = '';
       __pendingDelete.id = '';
       __pendingDelete.card = null;
     };
   
     // Click handlers (confirm / cancel / backdrop)
     modal.addEventListener('click', async (e) => {
       // Cancel/close: supports #dc-cancel, [data-action="cancel"], [data-modal-close], and clicking the backdrop
       if (e.target.closest('#dc-cancel,[data-action="cancel"],[data-modal-close],.c-modal__backdrop')) {
         e.preventDefault();
         close();
         return;
       }
   
       // Confirm delete
       const confirmBtn = e.target.closest('#dc-confirm,[data-action="confirm"]');
       if (!confirmBtn) return;
   
       e.preventDefault();
   
       const id = modal.dataset.contactId || __pendingDelete.id;
       if (!id) { close(); return; }
   
       confirmBtn.disabled = true;
       try {
         const res = await fetch(`/api/contact.php?id=${encodeURIComponent(id)}`, {
           method: 'DELETE',
           credentials: 'include',
           headers: { 'Accept': 'application/json' }
         });
   
         let data = null;
         try { data = await res.json(); } catch {}
   
         if (!res.ok || !data?.success) {
           throw new Error(data?.error || `Delete failed (${res.status})`);
         }
   
         // Remove card from both main and favorites, if present
         const mainCard = findCardIn(MAIN_COL_SEL, id);
         if (mainCard) mainCard.remove();
         const favCard = findCardIn(`#${FAVORITES_ROW_ID}`, id);
         if (favCard) favCard.remove();
   
         // Clean up local “created” demo stamp
         removeDemoCreated(id);
   
         updateEmptyState();
         syncFavoritesUI();
         close();
       } catch (err) {
         console.error('Delete failed:', err);
         close();
       } finally {
         confirmBtn.disabled = false;
       }
     });
   
     // ESC to close when open
     document.addEventListener('keydown', (e) => {
       if (e.key === 'Escape' && modal.classList.contains('is-open')) {
         e.preventDefault();
         modal.querySelector('#dc-cancel,[data-action="cancel"]')?.click() || close();
       }
     });
   }
   

/* ================================
   SEARCH — toolbar filter
   ================================ */
function bindSearch() {
  const input = document.querySelector('.contacts .toolbar .search input');
  const col   = document.querySelector(MAIN_COL_SEL);
  if (!input || !col) return;

  const emptySearchP = (() => {
    const p = document.createElement('p');
    p.className = 'empty empty-search';
    p.style.display = 'none';
    col.appendChild(p);
    return p;
  })();

  let t = 0;
  const debounce = (fn, d=200) => (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), d); };

  async function fetchResults(q) {
    const params = new URLSearchParams();
    if (q && q.trim() !== '') params.set('firstName', q.trim());
    const res = await fetch(`/api/contact.php?${params.toString()}`, {
      credentials: 'include',
      headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.error || 'Search failed');
    return data.contacts || [];
  }
  

  async function apply(q) {
    try {
      const items = await fetchResults(q);
      if (!items.length) {
        col.innerHTML = '';
        emptySearchP.textContent = q ? 'No matches.' : 'No contacts.';
        emptySearchP.style.display = '';
        return;
      }
      emptySearchP.style.display = 'none';
      col.innerHTML = items.map(contactCardHTML).join('');
      col.querySelectorAll('.contact-card').forEach(ensureDemoCreated);
      reconcileFavorites();
      syncFavoritesUI();
    } catch (e) {
      console.error(e);
    }
  }

  input.addEventListener('input', debounce(() => apply(input.value), 200), { passive: true });
  apply('');
}

/* ================================
   MISC UI — profile menu & toggle
   ================================ */
function bindProfileMenu(){
  const btn  = document.getElementById('profile-btn');
  const menu = document.getElementById('profile-menu');
  if(!btn || !menu) return;
  const close = () => {
    menu.classList.remove('is-open');
    btn.setAttribute('aria-expanded','false');
    menu.setAttribute('aria-hidden','true');
  };
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const open = menu.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.setAttribute('aria-hidden', open ? 'false' : 'true');
  });
  document.addEventListener('click', (e) => {
    if(!menu.contains(e.target) && !btn.contains(e.target)) close();
  });
}

function initContactsToggle() {
  const container = document.getElementById('dash');
  const toggleBtn = document.getElementById('toggle-contacts');
  if (!container || !toggleBtn) return;
  toggleBtn.addEventListener('click', () => {
    const expanded = container.classList.toggle('expanded');
    container.classList.toggle('collapsed', !expanded);
    toggleBtn.textContent = expanded ? 'See Less' : 'View More';
  });
}

/* ================================
   INIT
   ================================ */
function initDashboard() {
  initContactsToggle();
  initFilterTags('.filters-group');

  // Modals & forms
  bindCreateUI();
  bindDeleteHandlers();
  bindFavoriteHandlers();
  bindEditHandlers();
  bindEditSave();
  setupDeleteModal();

  // Other UI behaviors
  bindSearch();
  bindProfileMenu();

  // Input guards
  attachPhoneGuards('#ac-phone');
  attachPhoneGuards('#ec-phone');

  // Initial state
  updateEmptyState();
  document.querySelectorAll('.contact-card').forEach(ensureDemoCreated);
  reconcileFavorites();
  observeFavorites();
  syncFavoritesUI();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboard);
} else {
  initDashboard();
}
