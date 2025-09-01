<?php
require __DIR__ . '/components/input.php';
require __DIR__ . '/components/button.php';
require __DIR__ . '/components/_head.php';
?>

<!-- Page background + overlay -->
<div class="auth-bg">
  <div class="overlay"></div>

  <!-- Auth card -->
  <section class="auth-card">
    <?php include __DIR__ . '/components/logo.php'; ?>

    <h2 class="title">Welcome back to Infolio.</h2>
    <p class="subtitle">Please login to your account below.</p>

    <form method="POST" action="#" novalidate>
      <?php
        Input("username", "text", "Username", "Enter your username...");
        Input("password", "password", "Password", "Enter your password...");
      ?>

      <div class="actions">
        <?php Button("Login"); ?>
      </div>
    </form>

    <p class="switch-text">
      Don’t have an account yet?
      <a class="switch-link" href="/public/index.php?action=signup">Signup</a>
    </p>
  </section>
</div>

<?php require __DIR__ . '/components/_foot.php'; ?>
