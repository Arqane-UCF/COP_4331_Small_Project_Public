<?php
$page  = basename($_SERVER['PHP_SELF'], '.php');
$jsUrl = "/assets/js/{$page}.js";
$jsFs  = dirname(__DIR__) . "/js/{$page}.js";

if (file_exists($jsFs)) {
  echo '<script src="'.$jsUrl.'?v='.time().'" defer></script>';
}
if ($page === 'dashboard') {
  echo '<script src="/assets/js/components/modal.js?v='.time().'" defer></script>';
}
?>
</body>
</html>
