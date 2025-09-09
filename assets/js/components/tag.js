
function initTags() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.tag--filter');
      if (!btn) return;
      const on = !btn.classList.contains('is-selected');
      btn.classList.toggle('is-selected', on);
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTags);
  } else {
    initTags();
  }
  