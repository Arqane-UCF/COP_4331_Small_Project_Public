const SwalWithDefaults = Swal.mixin({
  heightAuto: false
});
window.Swal = SwalWithDefaults;

console.log("auth.js loaded");

document.addEventListener('DOMContentLoaded', () => {
  console.log("auth.js init");
  const wrapper = document.querySelector('.auth-views');
  const titleEl = document.querySelector('.js-title');
  const subEl   = document.querySelector('.js-subtitle');

  const views = {
    login:  document.getElementById('view-login'),
    signup: document.getElementById('view-signup')
  };
  const copy = {
    login:  { title: 'Welcome back to Infolio.', sub: 'Please login to your account below.' },
    signup: { title: 'Welcome to Infolio.',      sub: 'Please sign up below to continue.' }
  };

  if (!wrapper || !views.login || !views.signup) return;

  let current = (location.hash.replace('#','') === 'signup') ? 'signup' : 'login';

  function setView(viewName, push = true) {
    if (viewName !== 'login' && viewName !== 'signup') return;
    if (viewName === current) return;

    const nextEl = views[viewName];
    const curEl  = views[current];

    const wasHidden = nextEl.hidden;
    const prevDisp  = nextEl.style.display;
    const prevPos   = nextEl.style.position;
    const prevVis   = nextEl.style.visibility;
    const prevPE    = nextEl.style.pointerEvents;

    nextEl.hidden = false;
    nextEl.style.display = 'block';
    nextEl.style.position = 'absolute';
    nextEl.style.visibility = 'hidden';
    nextEl.style.pointerEvents = 'none';

    const endH = nextEl.scrollHeight;

    nextEl.style.position = prevPos;
    nextEl.style.visibility = prevVis;
    nextEl.style.pointerEvents = prevPE;
    nextEl.style.display = prevDisp || '';
    nextEl.hidden = wasHidden;

    const startH = wrapper.offsetHeight;
    wrapper.style.height = startH + 'px';
    void wrapper.offsetHeight;

    nextEl.classList.add('is-active');
    nextEl.hidden = false;

    curEl.classList.add('is-leaving');
    wrapper.style.height = endH + 'px';

    const EXIT_MS = 220;
    setTimeout(() => {
      curEl.classList.remove('is-leaving');
      curEl.classList.remove('is-active');
      curEl.hidden = true;
    }, EXIT_MS);

    const onEnd = (e) => {
      if (e.propertyName !== 'height') return;
      wrapper.removeEventListener('transitionend', onEnd);
      wrapper.style.height = 'auto';
    };
    wrapper.addEventListener('transitionend', onEnd, { once: true });

    titleEl && (titleEl.textContent = copy[viewName].title);
    subEl   && (subEl.textContent   = copy[viewName].sub);
    current = viewName;

    if (push) {
      const url = new URL(location.href);
      url.hash = viewName;
      history.pushState({ view: viewName }, '', url);
    }
  }

  Object.entries(views).forEach(([name, el]) => {
    const active = name === current;
    el.hidden = !active;
    el.classList.toggle('is-active', active);
  });
  titleEl && (titleEl.textContent = copy[current].title);
  subEl   && (subEl.textContent   = copy[current].sub);

  document.querySelectorAll('.js-switch').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const target = a.dataset.target;
      if (target === 'login' || target === 'signup') setView(target);
    });
  });

  window.addEventListener('popstate', (ev) => {
    const v = (ev.state && ev.state.view) || (location.hash ? location.hash.replace('#','') : 'login');
    if (v === 'login' || v === 'signup') setView(v, false);
  });

  async function submitTo(endpoint, form) {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: new Headers({
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'application/json'
      }),
      body: new URLSearchParams([...new FormData(form).entries()]).toString(),
      credentials: 'same-origin'
    });

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch { data = null; }

    if (res.ok && data && data.success) {
      window.location.href = data.redirect || '/views/dashboard.php';
      return;
    }

    const msg = (data && data.error) ||
                (text && text.trim()) ||
                `Request failed (${res.status})`;
    Swal.fire({ title: "Error", text: msg, icon: "error" });
  }

  [['view-login','/api/Login.php'], ['view-signup','/api/Signup.php']].forEach(([id, endpoint]) => {
    const form = document.querySelector(`#${id} form`);
    if (!form) return;

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      if (id === 'view-signup' && fd.get('password') !== fd.get('confirm')) {
        return Swal.fire({ title: "Password mismatch", text: "Please retype your password.", icon: "error" });
      }
      submitTo(endpoint, form).catch(err => {
        console.error(err);
        if (window.Sentry) Sentry.captureException(err);
        Swal.fire({ title: "Network error", text: "Please try again.", icon: "error" });
      });
    });
  });
});
