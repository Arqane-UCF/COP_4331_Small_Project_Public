<?php
require_once __DIR__ . '/../assets/components/_head.php';
require_once __DIR__ . '/../assets/components/buttonDS.php';
?>
<main style="min-height:100vh; display:grid; place-items:center; padding:32px;">
  <div class="row">

    <div class="search">
      <input type="text" placeholder="Search..." aria-label="Search contacts">
      <span class="search-icon" aria-hidden="true">
        <svg width="41" height="41" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M33.2868 34.4298L22.5893 23.7322C21.7351 24.4599 20.7528 25.0231 19.6424 25.4217C18.532 25.8203 17.4159 26.0196 16.2941 26.0196C13.5584 26.0196 11.2431 25.0727 9.34798 23.1787C7.45287 21.2847 6.50531 18.9699 6.50531 16.2343C6.50531 13.4987 7.45173 11.1828 9.34456 9.28652C11.2374 7.39027 13.5516 6.441 16.2872 6.43872C19.0228 6.43645 21.3393 7.384 23.2367 9.28139C25.1341 11.1788 26.0828 13.4947 26.0828 16.2292C26.0828 17.4159 25.8727 18.5645 25.4524 19.6749C25.0322 20.7853 24.4798 21.7351 23.7954 22.5244L34.4929 33.2203L33.2868 34.4298ZM16.2958 24.3096C18.5622 24.3096 20.4755 23.5295 22.0358 21.9692C23.596 20.4089 24.3762 18.495 24.3762 16.2275C24.3762 13.9599 23.596 12.0466 22.0358 10.4875C20.4755 8.92834 18.5622 8.1482 16.2958 8.14706C14.0294 8.14592 12.1155 8.92606 10.5541 10.4875C8.99264 12.0489 8.2125 13.9622 8.21364 16.2275C8.21478 18.4927 8.99492 20.4061 10.5541 21.9675C12.1132 23.5289 14.0265 24.309 16.2941 24.3079" fill="var(--primary)"/>
        </svg>
      </span>
    </div>


    <div class="btn-group">
      <?php render_button('+ New Contact', 'primary'); ?>
      <?php render_button('Delete All', 'outline', ['disabled' => true]); ?>
    </div>
  </div>
</main>

<style>
  :root{
    --bg:#F9EED7; --accent:#183A33; --action:#D5DEC6; --primary:#07072E;
    --placeholder: rgba(7,7,46,.60); --ring: rgba(24,58,51,.18);
    --control-h: 56px; 
  }

  .row{
    display: inline-flex; align-items: center; gap: 24px;
    width: min(1000px, 92vw);
  }


  .search{
    flex: 1 1 0;
    display: flex; align-items: center; justify-content: space-between;
    height: var(--control-h);
    padding: 0 24px;
    background: var(--bg);
    border-radius: 50px;
    outline: 2px solid var(--primary);
    outline-offset: -2px;
    min-width: 0;
  }
  .search input{
    border: none; outline: none; background: transparent;
    width: 100%; min-width: 0; height: 100%;
    font: 400 28px/1 Kurale, serif; color: var(--primary);
  }
  .search input::placeholder{ color: var(--placeholder); }
  .search-icon{ display: inline-flex; flex: 0 0 auto; }

  .ds-btn{
    display: inline-flex; align-items: center; justify-content: center;
    height: var(--control-h);
    padding: 0 16px;
    border-radius: 50px;
    border: none;
    font: 400 24px/1 Kurale, serif;
    cursor: pointer;
    transition: background-color .16s ease, color .16s ease;
    user-select: none; white-space: nowrap;
  }
  .ds-btn__label{ display:inline-block; }


  .ds-btn--primary{
    background: var(--primary);
    color: var(--bg);
  }
  .ds-btn--outline{
    background: var(--bg);
    color: var(--primary);
    outline: 2px solid var(--primary);
    outline-offset: -2px;
  }


  .ds-btn:not(.is-disabled):not(:disabled):hover{
    background: var(--action);
    color: var(--bg);
  }


  .ds-btn--primary:not(.is-disabled):not(:disabled):active{
    background: var(--primary);
    color: var(--bg);
  }
  .ds-btn--outline:not(.is-disabled):not(:disabled):active{
    background: var(--bg);
    color: var(--primary);
  }


  .ds-btn:focus-visible{
    outline: 3px solid var(--ring);
    outline-offset: 2px;
  }


  .ds-btn.is-disabled,
  .ds-btn:disabled{
    opacity: .55;
    cursor: not-allowed;
    pointer-events: none; 
  }

  .btn-group{ display:flex; gap:24px; align-items:flex-start; flex:0 0 auto; }
</style>

<script>

</script>

<?php require_once __DIR__ . '/../assets/components/_foot.php'; ?>
