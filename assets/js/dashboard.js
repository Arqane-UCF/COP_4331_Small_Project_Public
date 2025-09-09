

function initContactsToggle() {
    const container = document.getElementById('dash');
    if (!container) return;
  
    const toggleBtn = document.getElementById('toggle-contacts');
    if (!toggleBtn) return; // only rendered when 6+ contacts
  
    toggleBtn.addEventListener('click', () => {
      const expanded = container.classList.toggle('expanded');
      container.classList.toggle('collapsed', !expanded);
      toggleBtn.textContent = expanded ? 'See Less' : 'View More';
    });
  }
  
  // If loaded with defer/type=module (as in _foot.php), DOM is parsed already.
  // Run immediately, but also handle the rare case it isn't ready yet.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initContactsToggle);
  } else {
    initContactsToggle();
  }
  