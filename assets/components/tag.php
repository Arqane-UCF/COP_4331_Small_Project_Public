<?php
// Minimal Tag component matching your Figma
// - "filter"  (interactive button with 28x28 dot icon)
// - "chip"    (display-only pill for contact cards)
//
// Usage later:
//   render_tag('Work');                    // filter, unselected
//   render_tag('Work', true);              // filter, selected
//   render_tag('VIP', false, 'chip');      // chip, display-only
//
if (!function_exists('render_tag')) {
  function render_tag(string $label = 'Tag', bool $selected = false, string $variant = 'filter', ?string $id = null): void {
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $isFilter = ($variant === 'filter');

    $classes = 'tag tag--' . ($isFilter ? 'filter' : 'chip') . ($selected ? ' is-selected' : '');
    $attrs   = $isFilter
      ? 'type="button" class="'.$classes.'" aria-pressed="'.($selected ? 'true' : 'false').'"'
      : 'class="'.$classes.'" aria-disabled="true"';

    if ($id) $attrs .= ' id="'.htmlspecialchars($id, ENT_QUOTES, 'UTF-8').'"';

    if ($isFilter) {
      echo '<button '.$attrs.'>'
        .  '<span class="tag-icon" aria-hidden="true">'
        .    '<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">'
        .      '<path d="M14.1166 15.2833C14.7609 15.2833 15.2833 14.7609 15.2833 14.1166C15.2833 13.4723 14.7609 12.95 14.1166 12.95C13.4723 12.95 12.95 13.4723 12.95 14.1166C12.95 14.7609 13.4723 15.2833 14.1166 15.2833Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
        .    '</svg>'
        .  '</span>'
        .  '<span class="tag-label">'.$label.'</span>'
        .'</button>';
    } else {
      echo '<span '.$attrs.'><span class="tag-label">'.$label.'</span></span>';
    }
  }
}
