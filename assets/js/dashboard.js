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
  if (typeof initFilterTags === 'function') {
    initFilterTags('.filters-group');
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboard);
} else {
  initDashboard();
}

function initAddContactUI() {
  const modal = document.getElementById('modal-add-contact');
  if (!modal) return;

  const nameEl = modal.querySelector('#ac-name');
  const emailEl = modal.querySelector('#ac-email');
  const phoneEl = modal.querySelector('#ac-phone');

  const selectedWrap = modal.querySelector('#ac-selected-tags');
  const input = modal.querySelector('#ac-tag-input');
  const addBtn = modal.querySelector('#ac-add-tag-btn');
  const availWrap = modal.querySelector('#ac-available-tags');
  const noTagsTxt = modal.querySelector('#ac-no-tags');

  let allTags = [];
  const ALLOW_CREATE_TEMP = true;
  let selected = [];
  let open = false;

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
    if (!ALLOW_CREATE_TEMP) return;
    const raw = (input.value || '').trim();
    if (!raw || isDupInAll(raw)) return;
    allTags.unshift(raw);
    if (!isSelected(raw)) selected.push(raw);
    input.value = '';
    renderAll();
  }

  input.addEventListener('input', renderAll);
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); if (!addBtn.disabled) addTagFromInput(); }
  });
  addBtn.addEventListener('click', () => { if (!addBtn.disabled) addTagFromInput(); });

  const reflow = () => { if (open) renderAll(); };
  window.addEventListener('resize', reflow);

  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-modal-open="#modal-add-contact"]')) {
      open = true;
      setTimeout(renderAll, 10);
    }
    if (e.target.closest('#modal-add-contact [data-modal-close]') ||
        e.target.classList.contains('c-modal__backdrop')) {
      open = false;
    }
  });
}

(function bootstrapAC() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAddContactUI);
  } else {
    initAddContactUI();
  }
})();
