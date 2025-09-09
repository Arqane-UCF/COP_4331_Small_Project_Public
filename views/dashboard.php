<?php
require_once __DIR__ . '/../assets/components/_head.php';

$contacts = []; // array of contacts
$contactCount = count($contacts);
?>

<main class="dashboard">
  <div class="dashboard-container collapsed" id="dash">
    <header class="dashboard-header">
      <h1>[username]’s Infolio</h1>
      <div class="header-icons">
        <!-- put theme/user icons here later -->
      </div>
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
            <button class="icon-btn" aria-label="Search">
              <!-- magnifier (SVG later) -->
              🔍
            </button>
          </div>
          <button class="btn btn-primary">+ New Contact</button>
          <button class="btn btn-outline">Delete All</button>
        </div>
      </div>

      <div class="labels">
        <button class="chip chip-ghost">• All</button>
        <button class="chip chip-solid">Work</button>
        <button class="chip chip-ghost">Emergency</button>
        <button class="chip chip-solid">Family</button>
        <button class="chip chip-primary">+ New Label</button>
      </div>

      <div class="contacts-list">
        <?php if ($contactCount === 0): ?>
          <p class="empty">You have no contacts. Click the “+ New Contact” button to start keeping track of them.</p>
        <?php else: ?>
          <section class="contacts-grid">
            <?php foreach ($contacts as $c): ?>
              <?php include __DIR__ . '/../assets/components/contactCard.php'; ?>
            <?php endforeach; ?>
          </section>
        <?php endif; ?>
      </div>

      <?php if ($contactCount >= 6): ?>
        <div class="contacts-toggle">
          <button class="btn btn-secondary" id="toggle-contacts">View More</button>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../assets/components/_foot.php'; ?>
