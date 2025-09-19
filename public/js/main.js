// ===== Stats: random -> count-up -> stop (clean & forced) =====
(() => {
  const section  = document.getElementById('stats');
  const items    = Array.from(document.querySelectorAll('.js-stat'));
  const FORCE    = true; // <— tạm bật để chắc chắn thấy hiệu ứng
  console.log('[stats] items:', items.length);
  if (!items.length) return;

  const prefersReduce = !FORCE && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const format = (n, el) => {
    const pad = el.dataset.pad;
    const suffix = el.dataset.suffix || '';
    let s = String(Math.round(n));
    if (pad) s = s.padStart(parseInt(pad, 10), '0');
    return s + suffix;
  };

  function animate(el) {
    if (el.dataset.done === '1') return;
    el.dataset.done = '1';

    const target = parseFloat(el.dataset.target || '0');
    if (prefersReduce) { el.textContent = format(target, el); return; }

    // 1) Scramble nhanh
    const SCR_MS = 600, STEP = 30, MAX = Math.max(1, Math.floor(target * 1.3));
    const scr = setInterval(() => { el.textContent = format(Math.floor(Math.random() * MAX), el); }, STEP);

    // 2) Đếm mượt tới đúng số
    setTimeout(() => {
      clearInterval(scr);
      const DUR = 900, t0 = performance.now();
      const ease = t => 1 - Math.pow(1 - t, 3);
      (function tick(now){
        const p = Math.min(1, (now - t0) / DUR);
        el.textContent = format(target * ease(p), el);
        if (p < 1) requestAnimationFrame(tick);
      })(t0);
    }, SCR_MS);
  }

  const start = () => { console.log('[stats] start'); items.forEach(animate); };

  // Kích hoạt: IO nếu có, fallback scroll, và đảm bảo tự chạy sau 1.2s
  const inView = () => {
    if (!section) return true;
    const r = section.getBoundingClientRect();
    return r.top < window.innerHeight * 0.7 && r.bottom > 0;
  };

  if ('IntersectionObserver' in window && section) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { start(); io.disconnect(); } });
    }, { threshold: 0.25 });
    io.observe(section);
  } else {
    if (inView()) start();
    else {
      const onScroll = () => { if (inView()) { start(); window.removeEventListener('scroll', onScroll); window.removeEventListener('resize', onScroll); } };
      window.addEventListener('scroll', onScroll, { passive:true });
      window.addEventListener('resize', onScroll);
      setTimeout(start, 1200);
    }
  }

  // Dev helper: chạy lại bằng console
  window.__runStats = () => items.forEach(el => { el.dataset.done=''; animate(el); });
})();

/* ===== Off-canvas menu: safe init ===== */
(() => {
  const q = (id) => document.getElementById(id);
  const btnMenu = q('btnMenu');
  const btnClose = q('btnCloseMenu');
  const menu = q('menuRight');
  const scrim = q('scrim');

  if (!menu || !btnMenu) {
    console.warn('[menu] thiếu phần tử: menuRight/btnMenu');
    return;
  }

  const open = () => {
    menu.classList.add('open');
    menu.setAttribute('aria-hidden','false');
    btnMenu.setAttribute('aria-expanded','true');
    scrim?.classList.add('show');
  };
  const close = () => {
    menu.classList.remove('open');
    menu.setAttribute('aria-hidden','true');
    btnMenu.setAttribute('aria-expanded','false');
    scrim?.classList.remove('show');
  };

  // gắn listener
  btnMenu.addEventListener('click', open);
  btnClose?.addEventListener('click', close);
  scrim?.addEventListener('click', close);
  window.addEventListener('keydown', e => e.key === 'Escape' && close());

  // tiện test
  window.__menuTest = { open, close };
  console.log('[menu] ready');
})();
