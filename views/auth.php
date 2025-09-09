<?php
require_once __DIR__ . '/components/_head.php';
require_once __DIR__ . '/components/input.php';
require_once __DIR__ . '/components/button.php';
?>

<div class="auth-bg">
  <div class="overlay"></div>

  <section class="auth-card" aria-live="polite">
    <?php include __DIR__ . '/components/logo.php'; ?>

    <h2 class="title js-title">Welcome back to Infolio.</h2>
    <p class="subtitle js-subtitle">Please login to your account below.</p>

    <div class="auth-views">
      
      <div
        id="view-login"
        class="auth-view is-active"
        role="tabpanel"
        aria-labelledby="tab-login"
        data-name="login"
      >
        <form novalidate>
          <div class="field">
            <?php Input("username", "text", "Username", "Enter your username...", "", "login-username"); ?>
          </div>
          <div class="field">
            <?php Input("password", "password", "Password", "Enter your password...", "", "login-password"); ?>
          </div>

          <div class="actions"><?php Button("Login"); ?></div>
        </form>

        <p class="switch-text">
          Don’t have an account yet?
          <a class="switch-link js-switch" id="tab-signup" href="#signup" data-target="signup" role="button">Signup</a>
        </p>
      </div>

      <div
        id="view-signup"
        class="auth-view"
        role="tabpanel"
        aria-labelledby="tab-signup"
        data-name="signup"
        hidden
      >
        <form novalidate>
          <div class="field">
            <?php Input("username", "text", "Username", "Enter your username...", "", "signup-username"); ?>
          </div>
          <div class="field">
            <?php Input("password", "password", "Password", "Enter your password...", "", "signup-password"); ?>
          </div>
          <div class="field field--confirm">
            <?php Input("confirm", "password", "Confirm Password", "Confirm your password...", "", "signup-confirm"); ?>
          </div>

          <div class="actions"><?php Button("Signup"); ?></div>
        </form>

        <p class="switch-text">
          Already have an account?
          <a class="switch-link js-switch" id="tab-login" href="#login" data-target="login" role="button">Login</a>
        </p>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/components/_foot.php'; ?>
