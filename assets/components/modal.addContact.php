<?php
require_once __DIR__ . '/buttonDS.php';
require_once __DIR__ . '/input.php'; 
?>
<div id="modal-add-contact" class="c-modal modal--figma" role="dialog" aria-modal="true" aria-labelledby="ac-title" aria-hidden="true">
  <div class="c-modal__backdrop" aria-hidden="true"></div>
  <div class="c-modal__dialog">
    <!-- Header -->
    <div class="c-modal__header">
      <h2 id="ac-title" class="c-modal__title">Add New Contact</h2>
    </div>

    <!-- Body -->
    <div class="c-modal__body">
      <div class="c-col" style="gap:16px">
        <?php

          Input('name',  'text',  'Name',  'Enter contact’s name...',  '', 'ac-name');
          Input('email', 'email', 'Email', 'Enter contact’s email...', '', 'ac-email');
          Input('phone', 'tel',   'Phone', 'Enter contact’s phone...', '', 'ac-phone');
        ?>

        <!-- Tags -->
        <label class="label label--lg">Tags</label>

        <!-- Selected chips (appear above the search) -->
        <div id="ac-selected-tags" class="ac-selected"></div>

        <div class="ac-toolbar">
          <div class="c-input-wrap">
            <input id="ac-tag-input" class="input input--lg" type="text" placeholder="Search or add a tag..." autocomplete="off">
            <span class="c-input-icon" aria-hidden="true"></span>
          </div>
          <?php
            // Disabled until input has a non-duplicate value
            render_button('+ Add Tag', 'primary', [
              'id'       => 'ac-add-tag-btn',
              'disabled' => true
            ]);
          ?>
        </div>

        <!-- Helper when nothing matches -->
        <p id="ac-no-tags" class="ac-no-tags" hidden>No tags found.</p>

        <!-- One-line available tags (excludes selected) -->
        <div id="ac-available-tags" class="ac-available" aria-live="polite"></div>
      </div>
    </div>

    <!-- Footer -->
    <div class="c-modal__footer">
      <?php render_button('Save', 'primary', ['id' => 'ac-save']); ?>
      <?php render_button('Cancel', 'outline', ['data-modal-close' => '']); ?>
    </div>
  </div>
</div>
