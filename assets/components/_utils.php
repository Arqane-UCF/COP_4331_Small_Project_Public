<?php
if (!function_exists('e')) {
  function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('formatCreated')) {
  function formatCreated($ts): string {
    if (!$ts) return '';
    $t = is_numeric($ts) ? (int)$ts : strtotime((string)$ts);
    if (!$t) return '';
    return date('M j, Y', $t);
  }
}
