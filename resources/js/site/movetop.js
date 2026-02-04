/**
 * Botão "Voltar ao topo": exibe após scroll e faz scroll suave ao clicar.
 */
const SCROLL_THRESHOLD = 80;

export function initMovetop() {
  const btn = document.getElementById('movetop');
  if (!btn) return;

  const toggleVisibility = () => {
    const scroll = document.body.scrollTop || document.documentElement.scrollTop;
    btn.style.display = scroll > SCROLL_THRESHOLD ? 'block' : 'none';
  };

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  window.addEventListener('scroll', toggleVisibility, { passive: true });
  btn.addEventListener('click', scrollToTop);
  toggleVisibility();
}
