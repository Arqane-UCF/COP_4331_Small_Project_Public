<?php

if (!function_exists('render_tag')) {
  function render_tag(string $label = 'Tag', bool $selected = false, string $variant = 'filter', ?string $id = null): string {
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $isFilter = ($variant === 'filter');

    $classes = 'tag tag--' . ($isFilter ? 'filter' : 'chip') . ($selected ? ' is-selected' : '');
    $attrs   = $isFilter
      ? 'type="button" class="'.$classes.'" aria-pressed="'.($selected ? 'true' : 'false').'"'
      : 'class="'.$classes.'" aria-disabled="true"';
    if ($id) $attrs .= ' id="'.htmlspecialchars($id, ENT_QUOTES, 'UTF-8').'"';

    if ($isFilter) {
      return '<button '.$attrs.'>'
        .  '<span class="tag-icon" aria-hidden="true">'
        .    '<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">'
        .      '<path d="M14.1166 15.2833C14.7609 15.2833 15.2833 14.7609 15.2833 14.1166C15.2833 13.4723 14.7609 12.95 14.1166 12.95C13.4723 12.95 12.95 13.4723 12.95 14.1166C12.95 14.7609 13.4723 15.2833 14.1166 15.2833Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
        .    '</svg>'
        .  '</span>'
        .  '<span class="tag-label">'.$label.'</span>'
        .'</button>';
    }
    return '<span '.$attrs.'><span class="tag-label">'.$label.'</span></span>';
  }
}

if (!function_exists('renderTags')) {
  function renderTags(array $tags): string {
    if (empty($tags)) return '<div class="cc-tags"></div>';
    $tags = array_values(array_unique(array_map('strval', $tags)));
    $out = '<div class="cc-tags">';
    $shown = 0;
    foreach ($tags as $t) {
      $t = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
      $out .= '<span class="cc-tag">'.$t.'</span>';
      if (++$shown >= 5) break; 
    }
    if (count($tags) > $shown) {
      $out .= '<span class="cc-tags-more">+'.(count($tags)-$shown).' more</span>';
    }
    return $out.'</div>';
  }
}

if (!function_exists('renderTagList')) {
  function renderTagList(array $names): string {
    if (empty($names)) return '<p class="empty">No tags found.</p>';
    $names = array_values(array_unique(array_map('strval', $names)));
    sort($names, SORT_NATURAL | SORT_FLAG_CASE);
    $html = '';
    foreach ($names as $name) {
      $html .= render_tag($name, false, 'filter');
    }
    return $html;
  }
}
