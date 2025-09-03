<?php
function Input($name, $type = "text", $label = "", $placeholder = "", $value = "") {
  ?>
  <label class="label" for="<?= htmlspecialchars($name) ?>">
    <?= htmlspecialchars($label) ?>
  </label>
  <input
    class="input"
    id="<?= htmlspecialchars($name) ?>"
    name="<?= htmlspecialchars($name) ?>"
    type="<?= htmlspecialchars($type) ?>"
    placeholder="<?= htmlspecialchars($placeholder) ?>"
    value="<?= htmlspecialchars($value) ?>"
  />
  <?php
}
