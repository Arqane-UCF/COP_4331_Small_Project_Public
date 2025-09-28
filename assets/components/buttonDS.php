<?php
if (!function_exists('render_button')) {
  function render_button(string $label, string $variant = 'primary', array $opts = []): void {
    $label    = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $type     = $opts['type'] ?? 'button';
    $classes  = ['ds-btn', 'ds-btn--' . $variant];
    $disabled = (bool)($opts['disabled'] ?? false);

    $attrs = [
      'class="' . implode(' ', $classes) . '"',
      'type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"',
    ];
    // id/disabled
    if (!empty($opts['id'])) $attrs[] = 'id="' . htmlspecialchars($opts['id'], ENT_QUOTES, 'UTF-8') . '"';
    if ($disabled) { $attrs[] = 'disabled aria-disabled="true"'; }

    // pass through any data-* or aria-* attrs
    foreach ($opts as $k => $v) {
      if (strpos($k, 'data-') === 0 || strpos($k, 'aria-') === 0) {
        $attrs[] = $k . '="' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '"';
      }
    }

    echo '<button ' . implode(' ', $attrs) . '><span class="ds-btn__label">' . $label . '</span></button>';
  }
}
