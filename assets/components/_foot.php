<?php
$page  = basename($_SERVER['PHP_SELF'], '.php');     
$jsUrl = "/assets/js/{$page}.js";
$jsFs  = dirname(__DIR__) . "/js/{$page}.js";       
?>

</body>
</html>
