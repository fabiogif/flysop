/**
 * Botão "Voltar ao topo": exibe após scroll e faz scroll suave ao clicar.
 */
const SCROLL_THRESHOLD = 80;

export function initMovetop() {
  const btn = document.getElementById('movetop');
  if (!btn) return;

  const toggleVisibility = () => {
    const scroll = document.body.scrollTop || document.documentElement.scrollTop;
    if (scroll > SCROLL_THRESHOLD) {
      btn.hidden = false;
      btn.style.display = 'block';
    } else {
      btn.hidden = true;
      btn.style.display = 'none';
    }
  };

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  window.addEventListener('scroll', toggleVisibility, { passive: true });
  btn.addEventListener('click', scrollToTop);
  toggleVisibility();
}
