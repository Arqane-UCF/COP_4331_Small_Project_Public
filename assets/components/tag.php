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

/*
=====================================================
TAG ENDPOINTS (to implement later)

Expected by /assets/js/manageTags.js:

1) GET /tags/list.php
   - Returns: JSON array of tags
     [
       {"id": 1, "label": "Work"},
       {"id": 2, "label": "Friends"}
     ]
   - TODO: Pull from DB, scoped to current owner if needed.

2) POST /tags/create.php
   - Body (JSON): {"label": "School"}
   - Behavior:
       * Trim label
       * Reject empty
       * Case-insensitive duplicate check
       * Insert & return created tag
   - Returns (JSON): {"id": 123, "label": "School"}
   - Status codes: 201 on success, 409 on duplicate, 400 on bad input

3) DELETE /tags/delete.php?id=123
   - Behavior:
       * Remove tag (and disassociate from all contacts)
   - Returns: 204 No Content on success
   - Status codes: 404 if missing/not found, 500 on DB failure

Security:
   * Require logged-in session (ownerId) and validate ownership.
   * CSRF token on POST/DELETE if you have a framework for it.

=====================================================
*/

