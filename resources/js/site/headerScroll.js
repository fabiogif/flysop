/**
 * Adiciona/remove classe "nav-fixed" no header conforme o scroll.
 */
const SCROLL_THRESHOLD = 80;

export function initHeaderScroll() {
  const header = document.getElementById('site-header');
  if (!header) return;

  const toggle = () => {
    const scroll = window.scrollY || document.documentElement.scrollTop;
    header.classList.toggle('nav-fixed', scroll >= SCROLL_THRESHOLD);
  };

  window.addEventListener('scroll', toggle, { passive: true });
  toggle();
}
