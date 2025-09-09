<?php
require_once __DIR__ . '/../assets/components/_head.php';
require_once __DIR__ . '/../assets/components/tag.php';   
require_once __DIR__ . '/../assets/components/buttonDS.php'; 

$contacts = []; // array of contacts
$contactCount = count($contacts);
?>

<!-- Component assets -->
<link rel="stylesheet" href="/assets/css/tag.css">
<script src="/assets/js/tag.js" defer></script>

<link rel="stylesheet" href="/assets/css/buttonDS.css">
<script src="/assets/js/buttonDS.js" defer></script>

<main class="dashboard">
  <div class="dashboard-container collapsed" id="dash">
    <header class="dashboard-header">
      <h1>[username]’s Infolio</h1>
      <div class="header-icons">
        <!-- theme/user icons later -->
      </div>
    </header>

    <section class="favorites block">
      <h2>Favorites</h2>
      <p>You have no favorited contacts. Click the “★” icon to display them here.</p>
    </section>

    <section class="contacts block">
      <div class="contacts-top">
        <h2>Contacts</h2>

        <!-- Toolbar: search + DS buttons -->
        <div class="toolbar">
          <div class="search">
            <input type="text" placeholder="Search..." aria-label="Search contacts">
            <span class="search-icon" aria-hidden="true">

              <svg width="41" height="41" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M33.2868 34.4298L22.5893 23.7322C21.7351 24.4599 20.7528 25.0231 19.6424 25.4217C18.532 25.8203 17.4159 26.0196 16.2941 26.0196C13.5584 26.0196 11.2431 25.0727 9.34798 23.1787C7.45287 21.2847 6.50531 18.9699 6.50531 16.2343C6.50531 13.4987 7.45173 11.1828 9.34456 9.28652C11.2374 7.39027 13.5516 6.441 16.2872 6.43872C19.0228 6.43645 21.3393 7.384 23.2367 9.28139C25.1341 11.1788 26.0828 13.4947 26.0828 16.2292C26.0828 17.4159 25.8727 18.5645 25.4524 19.6749C25.0322 20.7853 24.4798 21.7351 23.7954 22.5244L34.4929 33.2203L33.2868 34.4298ZM16.2958 24.3096C18.5622 24.3096 20.4755 23.5295 22.0358 21.9692C23.596 20.4089 24.3762 18.495 24.3762 16.2275C24.3762 13.9599 23.596 12.0466 22.0358 10.4875C20.4755 8.92834 18.5622 8.1482 16.2958 8.14706C14.0294 8.14592 12.1155 8.92606 10.5541 10.4875C8.99264 12.0489 8.2125 13.9622 8.21364 16.2275C8.21478 18.4927 8.99492 20.4061 10.5541 21.9675C12.1132 23.5289 14.0265 24.309 16.2941 24.3079" fill="var(--primary)"/>
              </svg>
            </span>
          </div>

          <?php render_button('+ New Contact', 'primary'); ?>
          <?php render_button('Delete All', 'outline', ['disabled' => true]); ?>
        </div>
      </div>

      <!-- Split layout: filters (left) + cards (right) -->
      <div class="contacts-split">
        <aside class="filters-col" aria-label="Filter contacts by label">
          <div class="filters-group" data-select="single">
            <h3 class="filters-title">Filters</h3>
            <p class="filters-empty">No labels yet.</p>
          </div>
        </aside>

        <div class="cards-col">
          <?php if ($contactCount === 0): ?>
            <p class="empty">
              You have no contacts. Click the “+ New Contact” button to start keeping track of them.
            </p>
          <?php else: ?>
            <section class="contacts-grid">
              <?php foreach ($contacts as $c): ?>
                <?php include __DIR__ . '/../assets/components/contactCard.php'; ?>
              <?php endforeach; ?>
            </section>
          <?php endif; ?>
        </div>
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
