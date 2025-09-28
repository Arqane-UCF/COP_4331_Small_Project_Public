<?php require_once __DIR__ . '/buttonDS.php'; ?>
<div id="modal-manage-tags" class="c-modal" role="dialog" aria-modal="true" aria-labelledby="mt-title" aria-hidden="true">
  <div class="c-modal__backdrop" aria-hidden="true"></div>
  <div class="c-modal__dialog">
    <div class="c-modal__header">
      <h2 id="mt-title" class="c-modal__title">Manage Tags</h2>
    </div>
    <div class="c-modal__body">
      <div class="c-col">
        <label for="mt-input" class="sr-only">Search or add tag</label>
        <input id="mt-input" class="c-field" type="text" placeholder="Search or add tag…" autocomplete="off">
        <div id="mt-tag-list" class="c-row" style="flex-wrap:wrap; gap:12px;"></div>
      </div>
    </div>
    <div class="c-modal__footer">
      <?php render_button('+ Add Tag', 'outline', ['id'=>'mt-add-btn']); ?>
      <?php render_button('Close', 'primary', ['data-modal-close' => '']); ?>
    </div>
  </div>
</div>
