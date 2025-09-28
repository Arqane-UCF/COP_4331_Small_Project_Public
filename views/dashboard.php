<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require_once __DIR__ . '/../DBManager.php';
require_once __DIR__ . '/../assets/components/_head.php';
require_once __DIR__ . '/../assets/components/buttonDS.php';

$ownerId  = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? null;
$username = $_SESSION['username'] ?? null;

if (!$ownerId) {
  header('Location: /views/auth.php');
  exit;
}

if (!$username && class_exists('User')) {
  $u = User::getByID((int)$ownerId);
  if ($u) { $username = $u->username; }
}
$title_username = $username ?: '[username]';

function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

/* ---------- Load contacts (no tags) ---------- */
$db = DBGlobal::getRawDB();
if (!$db) {
  die('DB connection failed: ' . mysqli_connect_error());
}

$ownerId = (int)$ownerId;
$contacts = [];

$sql = "SELECT `id`, `firstName`, `lastName`, `email`, `phoneNum`, `favorite`
        FROM `contacts`
        WHERE `ownerid` = ?
        ORDER BY `id` DESC";
$stmt = $db->prepare($sql);
if (!$stmt) {
  die('Prepare failed: ' . htmlspecialchars($db->error));
}
$stmt->bind_param("i", $ownerId);
$stmt->execute();
$rs = $stmt->get_result();

while ($row = $rs->fetch_assoc()) {
  $id    = (int)$row['id'];
  $first = (string)($row['firstName'] ?? '');
  $last  = (string)($row['lastName']  ?? ''); // may be NULL (optional)
  $contacts[$id] = [
    'id'         => $id,
    'name'       => trim($first . ' ' . $last),
    'email'      => (string)($row['email'] ?? ''),
    'phone'      => (string)($row['phoneNum'] ?? ''),
    'favorited'  => (int)($row['favorite'] ?? 0),
    'created_at' => null,
    'tags'       => [], // keep empty so contactCard.php can handle it
  ];
}
$stmt->close();

$favorites    = array_filter($contacts, fn($c) => !empty($c['favorited']));
$nonFavorites = array_filter($contacts, fn($c) => empty($c['favorited']));
?>
<main class="dashboard">
  <div class="dashboard-container collapsed" id="dash">
    <header class="dashboard-header">
      <h1><?= e($title_username) ?>’s Infolio</h1>
      <div class="header-icons">
        <div class="profile">
          <button
            id="profile-btn"
            class="profile-btn"
            aria-haspopup="menu"
            aria-expanded="false"
            aria-label="Account menu"
            type="button">
            <svg viewBox="0 0 64 64" width="28" height="28" aria-hidden="true">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M32 5.3335C35.5019 5.33348 38.9696 6.02322 42.2049 7.36334C45.4403 8.70345 48.38 10.6677 50.8562 13.1439C53.3325 15.6202 55.2967 18.5599 56.6369 21.7952C57.977 25.0306 58.6668 28.4982 58.6668 32.0001C58.6668 46.7277 46.7276 58.6669 32 58.6669C17.2725 58.6669 5.33337 46.7277 5.33337 32.0001C5.33337 17.2726 17.2725 5.3335 32 5.3335ZM34.6668 34.6669H29.3334C22.7315 34.6669 17.0636 38.6652 14.619 44.3729C18.487 49.7967 24.8305 53.3335 32 53.3335C39.1695 53.3335 45.513 49.7967 49.3811 44.3725C46.9365 38.6652 41.2686 34.6669 34.6668 34.6669ZM32 13.3335C27.5817 13.3335 24 16.9152 24 21.3335C24 25.7517 27.5817 29.3335 32 29.3335C36.4183 29.3335 40 25.7517 40 21.3335C40 16.9152 36.4184 13.3335 32 13.3335Z"
                fill="currentColor"/>
            </svg>
          </button>

          <div id="profile-menu" class="profile-menu" role="menu" aria-hidden="true">
            <a class="menu-item" href="/api/Logout.php" role="menuitem">Sign out</a>
          </div>
        </div>
      </div>
    </header>

    <section class="favorites block">
      <h2>Favorites</h2>
      <?php if (!$favorites): ?>
        <p class="empty"></p>
      <?php else: ?>
        <div class="cards-row" id="favorites-col">
          <?php foreach ($favorites as $c): ?>
            <?php require __DIR__ . '/../assets/components/contactCard.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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
        </div>
      </div>

      <div class="cards-col">
        <?php if (!$nonFavorites): ?>
          <p class="empty">You have no contacts. Click “+ New Contact” to start keeping track of them.</p>
        <?php else: ?>
          <?php foreach ($nonFavorites as $c): ?>
            <?php require __DIR__ . '/../assets/components/contactCard.php'; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </section>
  </div>
</main>

<?php
require_once __DIR__ . '/../assets/components/modal.addContact.php';
require_once __DIR__ . '/../assets/components/modal.editContact.php';
require_once __DIR__ . '/../assets/components/modal.deleteContact.php';
require_once __DIR__ . '/../assets/components/_foot.php';
