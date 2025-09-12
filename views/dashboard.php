<?php
require_once __DIR__ . '/../assets/components/_head.php';
require_once __DIR__ . '/../assets/components/tag.php';
require_once __DIR__ . '/../assets/components/buttonDS.php';

/* ============================================================
 DATABASE THINGS IDK

  require_once __DIR__ . '/[RELATIVE_PATH_TO_DB_MANAGER].php';
  $db = DBGlobal::getRawDB(); // mysqli connection

Session user id (per-user contacts)
  session_start();
  $ownerId = $_SESSION['[SESSION_USER_ID_KEY]'] ?? null; // e.g., 'user_id'

   - If none of this is filled yet, the page stays on empty states by design.
============================================================ */

/* ---------- Session + username for header ---------- */
session_start();
$ownerId  = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? null;
$username = $_SESSION['username'] ?? null;

// pull the username
if (!$username && $ownerId && class_exists('User')) {
  $u = User::getByID((int)$ownerId);
  if ($u) { $username = $u->username; }
}
$title_username = $username ?: '[username]';

/* ---------- Helpers for components ---------- */
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function formatCreated(?string $dt): string {
  if (!$dt) return '';
  try { return (new DateTime($dt))->format('m/d/Y'); } catch (Throwable $e) { return $dt; }
}
function renderTags(array $tags): string {
  if (!$tags) return '<div class="cc-tags"></div>';
  $first = array_slice($tags, 0, 3);
  $more  = max(0, count($tags) - count($first));
  $html  = '<div class="cc-tags">';
  foreach ($first as $t) $html .= '<button class="cc-tag" type="button">'.e($t).'</button>';
  if ($more > 0) $html .= '<span class="cc-tags-more">+'.(int)$more.' more</span>';
  return $html . '</div>';
}

/* ---------- Data fetch (empty until DB + session exist) ---------- */
function fetchContactsForCurrentUser(): array {
  // No DB manager available -> stay empty
  if (!class_exists('DBGlobal')) return [];

  // Require a logged-in user id in session
  $ownerId = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? null;
  if (!$ownerId) return [];

  $db = DBGlobal::getRawDB();
  if (!$db) return [];

  // Adjust column names if schema differs later
  $sql = "
    SELECT
      c.id,
      c.firstName,
      c.lastName,
      c.email,
      c.phoneNum,
      c.favorite,
      c.created_at,
      GROUP_CONCAT(t.value) AS tags
    FROM contacts c
    LEFT JOIN tags t ON c.id = t.contactid
    WHERE c.ownerid = ?
    GROUP BY c.id
    ORDER BY c.id DESC
    LIMIT 200
  ";

  $stmt = $db->prepare($sql);
  if (!$stmt) return [];
  $stmt->bind_param('i', $ownerId);
  if (!$stmt->execute()) return [];

  $res = $stmt->get_result();
  if (!$res) return [];

  $rows = [];
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id'         => (int)($r['id'] ?? 0),
      'name'       => trim(($r['firstName'] ?? '') . ' ' . ($r['lastName'] ?? '')),
      'email'      => (string)($r['email'] ?? ''),
      'phone'      => (string)($r['phoneNum'] ?? ''),
      'created_at' => $r['created_at'] ?? null,
      'favorited'  => (bool)($r['favorite'] ?? 0),
      'tags'       => isset($r['tags']) && $r['tags'] !== '' ? explode(',', $r['tags']) : [],
    ];
  }
  return $rows;
}

$contacts     = fetchContactsForCurrentUser(); // [] by default until DB+session
$contactCount = count($contacts);
$favorites    = array_values(array_filter($contacts, fn($c) => !empty($c['favorited'])));
$favCount     = count($favorites);

/* ---------- Labels ---------- */
$labels = [];
if (class_exists('DBGlobal')) {
  $labels = DBGlobal::getAllTags() ?? [];
}
?>

<!-- Component assets -->
<link rel="stylesheet" href="/assets/css/tag.css">
<script src="/assets/js/tag.js" defer></script>

<link rel="stylesheet" href="/assets/css/buttonDS.css">
<script src="/assets/js/buttonDS.js" defer></script>

<main class="dashboard">
  <div class="dashboard-container collapsed" id="dash">
    <header class="dashboard-header">
      <h1><?php echo e($title_username); ?>’s Infolio</h1>
      <div class="header-icons">
        <!-- theme/user icons later -->
      </div>
    </header>

    <!-- FAVORITES -->
    <section class="favorites block">
      <h2>Favorites</h2>
      <?php if ($favCount === 0): ?>
        <p>You have no favorited contacts. Click the “★” icon to display them here.</p>
      <?php else: ?>
        <section class="favorites-grid">
          <?php foreach ($favorites as $c): ?>
            <?php include __DIR__ . '/../assets/components/contactCard.php'; ?>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>
    </section>

    <!-- CONTACTS -->
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
          <?php render_button('Delete All', 'outline', ['disabled' => $contactCount === 0]); ?>
        </div>
      </div>

      <!-- Split layout: filters (left) + cards (right) -->
      <div class="contacts-split">
        <aside class="filters-col" aria-label="Filter contacts by label">
          <div class="filters-group" data-select="single" id="labels-panel">

            <!-- Dynamic labels list (populated later)-->
            <div id="labels-list"
                 class="labels-list"
                 data-endpoint="/labels/list.php"
                 aria-live="polite"
                 aria-busy="false">
              <?php if (!empty($labels)): ?>
                <?php foreach ($labels as $lbl): ?>
                  <?php render_tag($lbl, false, 'filter'); ?>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="filters-actions">
              <?php
              render_button('+ New Label', 'primary', [
                'id' => 'new-label-btn',
                'data-action' => 'open-new-label',
                'data-endpoint' => '/labels/create.php'
              ]);
              ?>
            </div>
          </div>
        </aside>

        <div class="cards-col">
          <?php if ($contactCount === 0): ?>
            <p class="empty">
              You have no contacts. Click “+ New Contact” to start keeping track of them.
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
