document.addEventListener('DOMContentLoaded', () => {
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
});

// TEMP: demo route to dashboard until backend is ready
['view-login','view-signup'].forEach(id => {
  const form = document.querySelector(`#${id} form`);
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();             
      window.location.href = 'dashboard.php'; 
    });
  }
});
