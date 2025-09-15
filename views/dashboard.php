<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../assets/components/_head.php';
require_once __DIR__ . '/../assets/components/tag.php';
require_once __DIR__ . '/../assets/components/buttonDS.php';

session_start();
$ownerId  = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? null;
$username = $_SESSION['username'] ?? null;
if (!$username && $ownerId && class_exists('User')) {
  $u = User::getByID((int)$ownerId);
  if ($u) { $username = $u->username; }
}
$title_username = $username ?: '[username]';

function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

// No data yet (endpoints not wired)
$contactCount = 0;
?>
<main class="dashboard">
  <div class="dashboard-container collapsed" id="dash">
    <header class="dashboard-header">
      <h1><?= e($title_username) ?>’s Infolio</h1>
      <div class="header-icons"></div>
    </header>

    <section class="favorites block">
      <h2>Favorites</h2>
      <p>You have no favorited contacts. Click the “★” icon to display them here.</p>
    </section>

    <section class="contacts block">
      <div class="contacts-top">
        <h2>Contacts</h2>

        <div class="toolbar">
          <div class="search">
            <input type="text" placeholder="Search..." aria-label="Search contacts">
            <span class="search-icon" aria-hidden="true"></span>
          </div>

          <?php render_button('+ New Contact', 'primary', ['data-modal-open' => '#modal-add-contact']); ?>
          <?php render_button('Delete All', 'outline', ['disabled' => true]); ?>
        </div>
      </div>

      <div class="contacts-split">
        <aside class="filters-col" aria-label="Filter contacts by label">
          <div class="filters-group" data-select="single" id="labels-panel">
            <h3 class="filters-header">Filter</h3>

            <div id="labels-list" class="labels-list" aria-live="polite" aria-busy="false"></div>

            <div class="filters-actions">
              <?php render_button('Manage Tags', 'primary', ['data-modal-open' => '#modal-manage-tags']); ?>
            </div>
          </div>
        </aside>

        <div class="cards-col">
          <p class="empty">You have no contacts. Click “+ New Contact” to start keeping track of them.</p>
        </div>
      </div>
    </section>
  </div>
</main>

<?php
// Include each modal ONCE, here:
require_once __DIR__ . '/../assets/components/modal.manageTags.php';
require_once __DIR__ . '/../assets/components/modal.addContact.php';

// Close page; scripts are loaded in _foot.php
require_once __DIR__ . '/../assets/components/_foot.php';
