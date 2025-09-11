// Offcanvas (menu trượt bên phải)
const btnMenu   = document.getElementById('btnMenu');
const btnClose  = document.getElementById('btnCloseMenu');
const menuRight = document.getElementById('menuRight');
const scrim     = document.getElementById('scrim');

function openMenu(){
  menuRight.classList.add('open');
  menuRight.setAttribute('aria-hidden','false');
  btnMenu?.setAttribute('aria-expanded','true');
  scrim.classList.add('show');
}
function closeMenu(){
  menuRight.classList.remove('open');
  menuRight.setAttribute('aria-hidden','true');
  btnMenu?.setAttribute('aria-expanded','false');
  scrim.classList.remove('show');
}
btnMenu?.addEventListener('click', openMenu);
btnClose?.addEventListener('click', closeMenu);
scrim?.addEventListener('click', closeMenu);
window.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });

// Submit form lead (demo – chưa lưu DB)
document.getElementById('leadForm')?.addEventListener('submit', (e) => {
  e.preventDefault();
  alert('Đã ghi nhận! (Bạn có thể nối route POST /lead sau)');
  e.target.reset();
});

/* ===== Stats: random -> count up -> stop ===== */
(function() {
  const els = Array.from(document.querySelectorAll('.js-stat'));
  if (!els.length) return;

  const prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function fmt(n, pad, suffix) {
    let s = String(Math.round(n));
    if (pad) s = s.padStart(parseInt(pad, 10), '0');
    return s + (suffix || '');
  }

  function animate(el) {
    if (el.dataset.done === '1') return;
    el.dataset.done = '1';

    const target = parseFloat(el.dataset.target || '0');
    const pad    = el.dataset.pad || '';
    const suffix = el.dataset.suffix || '';

    if (prefersReduce) { el.textContent = fmt(target, pad, suffix); return; }

    // Pha 1: random nhanh
    const scrambleMs = 600;  // thời gian random
    const scrambleStep = 30; // tốc độ đổi
    const maxRnd = Math.max(1, Math.floor(target * 1.3));
    const scr = setInterval(() => {
      el.textContent = fmt(Math.floor(Math.random() * maxRnd), pad, suffix);
    }, scrambleStep);

    // Pha 2: đếm mượt đến đúng số
    setTimeout(() => {
      clearInterval(scr);
      const dur = 900;
      const start = performance.now();

      function easeOutCubic(t){ return 1 - Math.pow(1 - t, 3); }
      function tick(now){
        const p = Math.min(1, (now - start) / dur);
        const val = target * easeOutCubic(p);
        el.textContent = fmt(val, pad, suffix);
        if (p < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    }, scrambleMs);
  }

  // Chạy khi stats vào khung nhìn; cũng thử chạy ngay nếu đã nhìn thấy
  const section = document.getElementById('stats');

  function inView(el){
    const r = el.getBoundingClientRect();
    return r.top < (window.innerHeight * 0.8) && r.bottom > 0;
  }

  function kick() {
    if (!section) return;
    if (inView(section)) {
      els.forEach(animate);
      window.removeEventListener('scroll', kick);
      window.removeEventListener('resize', kick);
    }
  }

  // Fallback nếu trình duyệt có IntersectionObserver
  if ('IntersectionObserver' in window && section) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          els.forEach(animate);
          io.disconnect();
        }
      });
    }, { threshold: 0.25 });
    io.observe(section);
  } else {
    // Fallback phổ thông
    window.addEventListener('scroll', kick);
    window.addEventListener('resize', kick);
    // Gọi ngay lần đầu
    setTimeout(kick, 50);
  }
})();
