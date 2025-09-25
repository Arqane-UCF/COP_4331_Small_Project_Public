<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require_once __DIR__ . '/../DBManager.php';
require_once __DIR__ . '/../assets/components/_head.php';
require_once __DIR__ . '/../assets/components/tag.php';
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

// ---- Load contacts for this user ----
$db = DBGlobal::getRawDB();

$contacts = [];
$tagsByContact = [];

// Contacts
$stmt = $db->prepare("SELECT id, firstName, lastName, email, phoneNum, favorite FROM contacts WHERE ownerid = ? ORDER BY id DESC");
$stmt->bind_param("i", $ownerId);
$stmt->execute();
$rs = $stmt->get_result();
while ($row = $rs->fetch_assoc()) {
  $id = (int)$row['id'];
  $contacts[$id] = [
    'id'         => $id,
    'name'       => trim(($row['firstName'] ?? '').' '.($row['lastName'] ?? '')),
    'email'      => (string)($row['email'] ?? ''),
    'phone'      => (string)($row['phoneNum'] ?? ''),
    'favorited'  => (int)($row['favorite'] ?? 0),
    'created_at' => null,    
    'tags'       => [],      
  ];
}
$stmt->close();

if (!empty($contacts)) {
  $stmt = $db->prepare("
    SELECT t.contactid, t.value
    FROM tags t
    INNER JOIN contacts c ON c.id = t.contactid
    WHERE c.ownerid = ?
    ORDER BY t.value
  ");
  $stmt->bind_param("i", $ownerId);
  $stmt->execute();
  $rs = $stmt->get_result();
  while ($row = $rs->fetch_assoc()) {
    $cid = (int)$row['contactid'];
    if (isset($contacts[$cid])) {
      $contacts[$cid]['tags'][] = (string)$row['value'];
    }
  }
  $stmt->close();
}


// Derive favorites and tag list
$favorites = array_filter($contacts, fn($c) => !empty($c['favorited']));
$allTagsSet = [];
foreach ($contacts as $c) {
  foreach ($c['tags'] as $t) { $allTagsSet[$t] = true; }
}
$allTagNames = array_keys($allTagsSet);
?>
<main class="dashboard">
  <div class="dashboard-container collapsed" id="dash">
    <header class="dashboard-header">
      <h1><?= e($title_username) ?>’s Infolio</h1>
      <div class="header-icons">
        <a href="/api/Logout.php" class="signout-link">Sign out</a>
      </div>
    </header>


    <section class="favorites block">
      <h2>Favorites</h2>
      <?php if (!$favorites): ?>
        <p>You have no favorited contacts. Click the “★” icon to display them here.</p>
      <?php else: ?>
        <div class="cards-row">
          <?php foreach ($favorites as $c): ?>
            <?php
        
              require __DIR__ . '/../assets/components/contactCard.php';
            ?>
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
          <?php render_button('Delete All', 'outline', ['disabled' => empty($contacts)]); ?>
        </div>
      </div>

      <div class="contacts-split">
        <aside class="filters-col" aria-label="Filter contacts by label">
          <div class="filters-group" data-select="single" id="labels-panel">
            <h3 class="filters-header">Filter</h3>

            <div id="labels-list" class="labels-list" aria-live="polite" aria-busy="false">
              <?= renderTagList($allTagNames) ?>
            </div>

            <div class="filters-actions">
              <?php render_button('Manage Tags', 'primary', ['data-modal-open' => '#modal-manage-tags']); ?>
            </div>
          </div>
        </aside>

        <div class="cards-col">
          <?php if (!$contacts): ?>
            <p class="empty">You have no contacts. Click “+ New Contact” to start keeping track of them.</p>
          <?php else: ?>
            <?php foreach ($contacts as $c): ?>
              <?php require __DIR__ . '/../assets/components/contactCard.php'; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</main>

<?php
require_once __DIR__ . '/../assets/components/modal.manageTags.php';
require_once __DIR__ . '/../assets/components/modal.addContact.php';
require_once __DIR__ . '/../assets/components/_foot.php';
