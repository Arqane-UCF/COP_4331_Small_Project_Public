(() => {
    'use strict';
  
    const body = document.body;
  
    // open/close helpers
    function openModal(modal) {
      if (!modal) return;
      modal.classList.add('is-open');
      modal.removeAttribute('aria-hidden');
      body.style.overflow = 'hidden';
      // focus first focusable element inside
      const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      focusable?.focus();
    }
    function closeModal(modal) {
      if (!modal) return;
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      body.style.overflow = '';
    }
  
    // event delegation for all modals
    document.addEventListener('click', (e) => {
      const openTrigger = e.target.closest('[data-modal-open]');
      if (openTrigger) {
        const sel = openTrigger.getAttribute('data-modal-open');
        const modal = sel ? document.querySelector(sel) : null;
        openModal(modal);
        return;
      }
      const closeTrigger = e.target.closest('[data-modal-close]');
      if (closeTrigger) {
        const modal = e.target.closest('.c-modal');
        closeModal(modal);
        return;
      }
      // backdrop click
      if (e.target.classList.contains('c-modal__backdrop')) {
        const modal = e.target.closest('.c-modal');
        closeModal(modal);
      }
    });
  
    // esc to close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.c-modal.is-open').forEach(closeModal);
      }
    });
  
    console.log('[modal] ready');
  })();
  