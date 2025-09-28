<?php
require_once __DIR__ . '/buttonDS.php';
require_once __DIR__ . '/input.php';
?>
<div id="modal-edit-contact" class="c-modal modal--figma" role="dialog"
     aria-modal="true" aria-labelledby="ec-title" aria-hidden="true">
  <div class="c-modal__backdrop" aria-hidden="true"></div>
  <div class="c-modal__dialog">

    <div class="c-modal__header">
      <h2 id="ec-title" class="c-modal__title">Edit Contact</h2>
    </div>

    <div class="c-modal__body">
      <div class="c-col" style="gap:16px">
        <?php
          Input('name',  'text',  'Name',  'Enter contact’s name...',  '', 'ec-name');
          Input('email', 'email', 'Email', 'Enter contact’s email...', '', 'ec-email');
          Input('phone', 'tel',   'Phone', 'Enter contact’s phone...', '', 'ec-phone');
        ?>
      </div>
    </div>

    <div class="c-modal__footer">
      <?php render_button('Save',   'primary',  ['id' => 'ec-save', 'type' => 'button']); ?>
      <?php render_button('Cancel', 'outline',  ['data-modal-close' => '', 'type' => 'button']); ?>
    </div>

  </div>
</div>
