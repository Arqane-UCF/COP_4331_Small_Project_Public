<?php
// Minimal DS Button component
// Usage:
//   require_once __DIR__ . '/buttonDS.php';
//   render_button('+ New Contact', 'primary');
//   render_button('Delete All', 'outline', ['disabled' => true]);

if (!function_exists('render_button')) {
  function render_button(string $label, string $variant = 'primary', array $opts = []): void {
    $label    = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $id       = $opts['id'] ?? null;
    $disabled = (bool)($opts['disabled'] ?? false);
    $type     = $opts['type'] ?? 'button';

    $classes = ['ds-btn', 'ds-btn--' . $variant];
    if ($disabled) $classes[] = 'is-disabled';

    $attrs = [
      'class="' . implode(' ', $classes) . '"',
      'type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"',
    ];
    if ($id)       $attrs[] = 'id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"';
    if ($disabled) $attrs[] = 'disabled aria-disabled="true"';

    echo '<button ' . implode(' ', $attrs) . '><span class="ds-btn__label">' . $label . '</span></button>';
  }
}
