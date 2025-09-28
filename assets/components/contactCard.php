<?php
require_once __DIR__ . '/_utils.php';
require_once __DIR__ . '/tag.php';

$id      = (int)($c['id'] ?? 0);
$name    = (string)($c['name'] ?? '');
$email   = (string)($c['email'] ?? '');
$phone   = (string)($c['phone'] ?? '');
$created = isset($c['created_at']) ? formatCreated($c['created_at']) : '';
$isFav   = !empty($c['favorited']);
$tags    = is_array($c['tags'] ?? null) ? $c['tags'] : [];
?>
<article class="contact-card<?php echo $isFav ? ' is-favorited' : ''; ?>" data-contact-id="<?php echo $id; ?>">
  <div class="cc-left">
    <label class="cc-checkbox">
      <input type="checkbox" aria-label="Select contact <?php echo e($name); ?>" data-contact-id="<?php echo $id; ?>" />
      <span class="cc-checkmark"></span>
    </label>
  </div>

  <div class="cc-body">
    <h3 class="cc-name"><?php echo e($name); ?></h3>
    <?php if ($email): ?><p class="cc-email"><?php echo e($email); ?></p><?php endif; ?>
    <?php if ($phone): ?><p class="cc-phone"><?php echo e($phone); ?></p><?php endif; ?>
    <?php if ($created): ?><p class="cc-created">Created: <?php echo e($created); ?></p><?php endif; ?>
    <?php echo renderTags($tags); ?>
  </div>

  <div class="cc-actions-top">
    <form class="cc-fav-form" data-endpoint="/api/contacts/toggle-favorite.php" data-contact-id="<?php echo $id; ?>">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <input type="hidden" name="favorited" value="<?php echo $isFav ? '0' : '1'; ?>">
      <button type="button" class="cc-icon cc-favorite<?php echo $isFav ? ' active' : ''; ?>"
              aria-label="<?php echo $isFav ? 'Unfavorite' : 'Favorite'; ?>"
              title="<?php echo $isFav ? 'Unfavorite' : 'Favorite'; ?>"
              aria-pressed="<?php echo $isFav ? 'true' : 'false'; ?>">
        <svg viewBox="0 0 28 28" width="24" height="24" aria-hidden="true">
          <path d="M6.8 24.5L8.7 16.3 2.33 10.79 10.73 10.06 14 2.33 17.27 10.06 25.67 10.79 19.31 16.3 21.2 24.5 14 20.15 6.8 24.5Z"
                fill="<?php echo $isFav ? 'var(--accent)' : 'none'; ?>" stroke="var(--accent)" stroke-linejoin="round"/>
        </svg>
      </button>
    </form>

    <a class="cc-icon" aria-label="Edit" title="Edit"
       href="/contacts/edit.php?id=<?php echo $id; ?>"
       data-action="edit" data-contact-id="<?php echo $id; ?>">
      <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
        <path d="M16.99 3.9a2.3 2.3 0 013.26 3.11L8.9 18.36a3 3 0 01-1.69.86l-3.72.95.95-3.73c.08-.31.25-.6.49-.84L16.99 3.9Z" fill="var(--primary)"/>
      </svg>
    </a>
  </div>

  <div class="cc-actions-bottom">
    <form class="cc-delete-form" data-endpoint="/api/contacts/delete.php" data-contact-id="<?php echo $id; ?>">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <button type="button" class="cc-icon cc-delete" aria-label="Delete" title="Delete" data-action="delete">
        <svg viewBox="0 0 28 28" width="24" height="24" aria-hidden="true">
          <path d="M4.375 6.125h19.25m-13.125 0V3.938A1.31 1.31 0 0111.813 2.625h4.375A1.31 1.31 0 0117.5 3.938V6.125M6.125 6.125 7.219 23.625c.06 1.011.796 1.75 1.758 1.75h10.062c.966 0 1.688-.739 1.75-1.75L21.875 6.125M14 9.625V21.875M10.063 9.625 10.5 21.875M17.938 9.625 17.5 21.875"
                stroke="var(--primary)" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      </button>
    </form>
  </div>
</article>
