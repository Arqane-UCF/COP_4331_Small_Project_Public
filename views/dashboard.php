<?php
require_once __DIR__ . '/components/_head.php';
?>

<!-- card wrapper -->
<article class="contact-card" data-contact-id="123">
  <!-- multiselect checkbox-->
  <div class="cc-left">
    <label class="cc-checkbox">
      <input type="checkbox" aria-label="Select contact" />
      <span class="cc-checkmark"></span>
    </label>
  </div>

  <!-- main content (dummy content) -->
  <div class="cc-body">
    <h3 class="cc-name">Hamming Hammington</h3>
    <p class="cc-email">john.smith@example.com</p>
    <p class="cc-phone">123-456-7890</p>
    <p class="cc-created">Created: 08/16/2025</p>

    <!-- tags group (will add logic for truncating later)-->
    <div class="cc-tags">
      <button class="cc-tag">Work</button>
      <button class="cc-tag">Work</button>
      <button class="cc-tag">Work</button>
      <button class="cc-tag">Work</button>
      <button class="cc-tag">Work</button>
      <button class="cc-tag">Work</button>
      <button class="cc-tag">Work</button>
      <span class="cc-tags-more" hidden>+2 more</span>
    </div>
  </div>

  <!-- actions (edit, delete, favorite) -->
  <div class="cc-actions">
    <button class="cc-icon" aria-label="Favorite" title="Favorite">
      <!-- favorite -->
      <svg viewBox="0 0 28 28" width="24" height="24" aria-hidden="true">
        <path d="M6.8 24.5L8.7 16.3 2.33 10.79 10.73 10.06 14 2.33 17.27 10.06 25.67 10.79 19.31 16.3 21.2 24.5 14 20.15 6.8 24.5Z" fill="var(--accent)"/>
      </svg>
    </button>
    <button class="cc-icon" aria-label="Edit" title="Edit">
      <!-- edit -->
      <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
        <path d="M16.99 3.9a2.3 2.3 0 013.26 3.11L8.9 18.36a3 3 0 01-1.69.86l-3.72.95.95-3.73c.08-.31.25-.6.49-.84L16.99 3.9Z" fill="var(--primary)"/>
      </svg>
    </button>
    <button class="cc-icon cc-delete" aria-label="Delete" title="Delete">
      <!-- delete -->
      <svg viewBox="0 0 28 28" width="24" height="24" aria-hidden="true">
        <path d="M4.375 6.125h19.25m-13.125 0V3.938A1.31 1.31 0 0111.813 2.625h4.375A1.31 1.31 0 0117.5 3.938V6.125M6.125 6.125 7.219 23.625c.06 1.011.796 1.75 1.758 1.75h10.062c.966 0 1.688-.739 1.75-1.75L21.875 6.125M14 9.625V21.875M10.063 9.625 10.5 21.875M17.938 9.625 17.5 21.875" stroke="var(--primary)" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </div>
</article>
