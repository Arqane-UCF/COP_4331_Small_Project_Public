<?php
function Input($name, $type = "text", $label = "", $placeholder = "", $value = "", $id = null) {
  // Default to $name if no custom id given
  $id = $id ?: $name;
  ?>
  <label class="label" for="<?= htmlspecialchars($id) ?>">
    <?= htmlspecialchars($label) ?>
  </label>
  <input
    class="input"
    id="<?= htmlspecialchars($id) ?>"
    name="<?= htmlspecialchars($name) ?>"
    type="<?= htmlspecialchars($type) ?>"
    placeholder="<?= htmlspecialchars($placeholder) ?>"
    value="<?= htmlspecialchars($value) ?>"
  />
  <?php
}
