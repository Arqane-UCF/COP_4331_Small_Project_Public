<?php
require_once __DIR__ . '/buttonDS.php';
?>
<div id="modal-delete-contact" class="c-modal modal--figma" role="dialog" aria-modal="true" aria-labelledby="dc-title" aria-hidden="true">
  <div class="c-modal__backdrop" aria-hidden="true"></div>
  <div class="c-modal__dialog">

    <div class="c-modal__header">
      <h2 id="dc-title" class="c-modal__title">Delete Contact</h2>
    </div>

    <div class="c-modal__body">
      <p style="margin:0; font:400 24px/1.3 Kurale, serif; color:var(--primary,#07072E)">
        Are you sure you want to delete <strong id="dc-name">this contact</strong>? This action cannot be undone.
      </p>
    </div>

    <div class="c-modal__footer">
      <?php // Make Confirm look exactly like Add Contact "Save"
        render_button('Confirm', 'primary', ['id' => 'dc-confirm', 'type' => 'button']);
      ?>
      <?php // Make Cancel identical to Add Contact "Cancel"
        render_button('Cancel', 'outline', ['data-modal-close' => '', 'type' => 'button']);
      ?>
    </div>

  </div>
</div>
