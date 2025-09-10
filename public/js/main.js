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
