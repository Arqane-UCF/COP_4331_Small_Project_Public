// ---------- Helpers ----------
function updateEmptyState() {
  const col = document.querySelector('.cards-col');
  if (!col) return;
  const hasCard = !!col.querySelector('.contact-card');
  let empty = col.querySelector('.empty');
  if (!hasCard) {
    if (!empty) {
      empty = document.createElement('p');
      empty.className = 'empty';
      empty.textContent = 'You have no contacts. Click “+ New Contact” to start keeping track of them.';
      col.appendChild(empty);
    }
  } else if (empty) {
    empty.remove();
  }
}

async function refreshLabels() {
  const holder = document.getElementById('labels-list');
  if (!holder) return;
  try {
    const res = await fetch('/api/tag.php?limit=10', { credentials: 'include' });
    const j = await res.json();
    if (!j?.success || !Array.isArray(j.tags)) return;
    holder.innerHTML = '';
    j.tags.sort((a, b) => a.localeCompare(b));
    j.tags.forEach(tag => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'label';
      btn.dataset.label = tag;
      btn.textContent = tag;
      holder.appendChild(btn);
    });
    if (typeof initFilterTags === 'function') initFilterTags('.filters-group');
  } catch {}
}


async function createContact(payload) {
  const params = new URLSearchParams();
  Object.entries(payload).forEach(([k, v]) => {
    if (Array.isArray(v)) v.forEach(x => params.append('tags[]', x));
    else params.append(k, v);
  });

  const res = await fetch('/api/contacts/create.php', {
    method: 'POST',
    headers: new Headers({
      'Content-Type': 'application/x-www-form-urlencoded',
      'Accept': 'application/json'
    }),
    body: params.toString(),
    credentials: 'include'
  });

  const text = await res.text();
  let data = null;
  try { data = JSON.parse(text); } catch {
    throw new Error(`Invalid JSON from server (status ${res.status}): ${text.slice(0,200)}`);
  }
  if (!res.ok || !data.success) {
    throw new Error(data?.error || `Request failed (${res.status})`);
  }
  return data;
}

// ---------- Add Contact (modal) ----------
function bindCreateUI() {
  const modal = document.getElementById('modal-add-contact');
  if (!modal) return;

  const nameEl  = modal.querySelector('#ac-name');
  const emailEl = modal.querySelector('#ac-email');
  const phoneEl = modal.querySelector('#ac-phone');

  const selectedWrap = modal.querySelector('#ac-selected-tags');
  const input        = modal.querySelector('#ac-tag-input');
  const addBtn       = modal.querySelector('#ac-add-tag-btn');
  const availWrap    = modal.querySelector('#ac-available-tags');
  const noTagsTxt    = modal.querySelector('#ac-no-tags');

  let allTags = [];     // fetched on open from /api/tag.php
  let selected = [];    // current chosen tags (strings)
  let modalOpen = false;

  const norm = s => (s || '').trim().toLowerCase();
  const isDupInAll = v => allTags.some(t => norm(t) === norm(v));
  const isSelected = v => selected.some(t => norm(t) === norm(v));

  function setAddEnabled() {
    const v = (input.value || '').trim();
    const ok = v.length > 0 && !isDupInAll(v);
    addBtn.disabled = !ok;
    addBtn.classList.toggle('is-disabled', !ok);
    const lbl = addBtn.querySelector('.ds-btn__label') || addBtn;
    lbl.textContent = v ? `+ Add "${v}"` : '+ Add Tag';
  }

  function renderSelected() {
    selectedWrap.innerHTML = '';
    selected.forEach(label => {
      const chip = document.createElement('span');
      chip.className = 'ac-chip';
      chip.setAttribute('data-tag-name', label);
      chip.innerHTML = `
        <span>${label}</span>
        <button class="ac-chip__remove" type="button" aria-label="Remove ${label}">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>`;
      chip.querySelector('.ac-chip__remove').addEventListener('click', () => {
        selected = selected.filter(t => norm(t) !== norm(label));
        renderAll();
      });
      selectedWrap.appendChild(chip);
    });
  }

  function renderAvailable(filtered) {
    availWrap.innerHTML = '';
    availWrap.style.visibility = 'hidden';

    const maxW = availWrap.clientWidth || availWrap.getBoundingClientRect().width;
    let shown = 0;

    for (const label of filtered) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ac-tag';
      btn.textContent = label;
      btn.addEventListener('click', () => {
        if (!isSelected(label)) selected.push(label);
        renderAll();
      });
      availWrap.appendChild(btn);
      shown++;
      if (availWrap.scrollWidth > maxW) {
        availWrap.removeChild(btn);
        shown--;
        break;
      }
    }

    const remaining = filtered.length - shown;
    if (remaining > 0) {
      const more = document.createElement('div');
      more.className = 'ac-more';
      more.textContent = `+${remaining} more`;
      availWrap.appendChild(more);

      if (availWrap.scrollWidth > maxW && shown > 0) {
        availWrap.removeChild(availWrap.children[shown - 1]);
        shown--;
        more.textContent = `+${filtered.length - shown} more`;
      }
    }

    availWrap.style.visibility = '';
  }

  function renderAll() {
    renderSelected();

    const needle = norm(input.value);
    let list = allTags.filter(t => !isSelected(t));
    if (needle) list = list.filter(t => norm(t).startsWith(needle));

    const none = list.length === 0;
    noTagsTxt.hidden = !none;

    renderAvailable(list);
    setAddEnabled();
  }

  function addTagFromInput() {
    const raw = (input.value || '').trim();
    if (!raw || isDupInAll(raw)) return;
    allTags.unshift(raw);
    if (!isSelected(raw)) selected.push(raw);
    input.value = '';
    renderAll();
  }

  // typing / enter / add click
  input.addEventListener('input', renderAll);
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); if (!addBtn.disabled) addTagFromInput(); }
  });
  addBtn.addEventListener('click', () => { if (!addBtn.disabled) addTagFromInput(); });

  // fetch tags when modal opens
  document.addEventListener('click', async (e) => {
    if (e.target.closest('[data-modal-open="#modal-add-contact"]')) {
      try {
        const res = await fetch('/api/tag.php?limit=50', { credentials: 'include' });
        const j = await res.json();
        allTags = (j?.success && Array.isArray(j.tags)) ? j.tags.slice() : [];
      } catch { allTags = []; }
      selected = [];
      input.value = '';
      setTimeout(renderAll, 10);
    }
    if (e.target.closest('#modal-add-contact [data-modal-close]') ||
        e.target.classList.contains('c-modal__backdrop')) {
      modalOpen = false;
    }
  });

  // Save (event delegation so it always binds)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('#ac-save');
    if (!btn) return;
    e.preventDefault();

    const name  = nameEl?.value?.trim()  || '';
    const email = emailEl?.value?.trim() || '';
    const phone = phoneEl?.value?.trim() || '';
    const tags  = Array.from(selected);

    if (!name) {
      Swal.fire({ title: 'Name required', text: 'Please enter a name.', icon: 'error' });
      return;
    }

    btn.disabled = true;
    try {
      const { card_html } = await createContact({ name, email, phone, tags });
      const col = document.querySelector('.cards-col');
      if (col) {
        const empty = col.querySelector('.empty'); if (empty) empty.remove();
        const wrap = document.createElement('div');
        wrap.innerHTML = card_html;
        const node = wrap.firstElementChild;
        if (node) col.prepend(node);
      }
      await refreshLabels();
      // close modal
      document.querySelector('#modal-add-contact [data-modal-close]')?.click();
      Swal.fire({ title: 'Contact created', icon: 'success', timer: 1200, showConfirmButton: false });

      // reset fields
      nameEl.value = ''; emailEl.value = ''; phoneEl.value = '';
      selected = []; input.value = ''; renderAll();
      updateEmptyState();
    } catch (err) {
      Swal.fire({ title: 'Error', text: String(err.message || err), icon: 'error' });
    } finally {
      btn.disabled = false;
    }
  });
}

// ---------- Delete ----------
function bindDeleteHandlers() {
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cc-delete-form [data-action="delete"]');
    if (!btn) return;
    e.preventDefault();

    const form = btn.closest('.cc-delete-form');
    const id = form?.querySelector('input[name="id"]')?.value;
    if (!id) return;

    const ok = await Swal.fire({
      title: 'Delete contact?',
      text: 'This cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
    }).then(r => r.isConfirmed);
    if (!ok) return;

    try {
      const res = await fetch('/api/contacts/delete.php', {
        method: 'POST',
        headers: new Headers({
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json'
        }),
        body: new URLSearchParams({ id }).toString(),
        credentials: 'include'
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.error || 'Delete failed');
      form.closest('.contact-card')?.remove();
      updateEmptyState();
      Swal.fire({ title: 'Deleted', icon: 'success', timer: 900, showConfirmButton: false });
    } catch (err) {
      Swal.fire({ title: 'Error', text: String(err.message || err), icon: 'error' });
    }
  });
}

// ---------- Favorite ----------
function bindFavoriteHandlers() {
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cc-fav-form .cc-favorite');
    if (!btn) return;
    e.preventDefault();

    const form = btn.closest('.cc-fav-form');
    const id = form?.querySelector('input[name="id"]')?.value;
    const favInput = form?.querySelector('input[name="favorited"]');
    if (!id || !favInput) return;

    const next = favInput.value === '1' ? 0 : 1;

    try {
      const res = await fetch('/api/contacts/toggle-favorite.php', {
        method: 'POST',
        headers: new Headers({
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json'
        }),
        body: new URLSearchParams({ id, favorited: String(next) }).toString(),
        credentials: 'include'
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.error || 'Toggle failed');

      favInput.value = String(next);
      btn.classList.toggle('active', !!next);
      btn.setAttribute('aria-pressed', next ? 'true' : 'false');
    } catch (err) {
      Swal.fire({ title: 'Error', text: String(err.message || err), icon: 'error' });
    }
  });
}

// ---------- Init ----------
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

function initDashboard() {
  initContactsToggle();
  bindCreateUI();
  bindDeleteHandlers();
  bindFavoriteHandlers();
  updateEmptyState();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboard);
} else {
  initDashboard();
}
