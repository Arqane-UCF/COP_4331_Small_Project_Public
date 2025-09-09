// /assets/js/dashboard.js
import { initFilterTags } from '../components/tag.js'

/* View more / See less */
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

/* Bootstrap */
function initDashboard() {
  initContactsToggle();

  initFilterTags('.filters-group');

  const group = document.querySelector('.filters-group');
  if (group) {
    group.addEventListener('tags:change', (e) => {
      // e.detail.selected is an array of labels (['All'] or ['Work', ...])

    });
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboard);
} else {
  initDashboard();
}
