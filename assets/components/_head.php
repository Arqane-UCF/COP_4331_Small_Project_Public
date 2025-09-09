<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <?php $page = basename($_SERVER['PHP_SELF'], '.php'); ?>
  <title>Infolio – <?= htmlspecialchars(ucfirst($page)) ?></title>

  <!-- Page-level stylesheet -->
  <link rel="stylesheet" href="/assets/css/<?= $page ?>.css">

  <!-- Component styles only on dashboard -->
  <?php if ($page === 'dashboard'): ?>
    <link rel="stylesheet" href="/assets/css/components/tag.css">
    <link rel="stylesheet" href="/assets/css/components/buttonDS.css">
  <?php endif; ?>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Kurale&family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Libs -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://browser.sentry-cdn.com/10.8.0/bundle.tracing.replay.min.js" crossorigin="anonymous" on-lazy="no"></script>
  <script src="/assets/js/loader.js"></script>
</head>
<body>
