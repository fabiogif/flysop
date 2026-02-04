/**
 * Toggle do menu mobile: classes "show" no collapse e "active" no header; "noscroll" no body.
 */
const TOGGLER_SELECTOR = '.navbar-toggler';
const TARGET_SELECTOR = '#navbarNav';
const HEADER_SELECTOR = 'header';
const BODY_NOSCROLL_CLASS = 'noscroll';

export function initNavbarToggler() {
  const toggler = document.querySelector(TOGGLER_SELECTOR);
  const target = document.querySelector(TARGET_SELECTOR);
  const header = document.querySelector(HEADER_SELECTOR);
  if (!toggler || !target) return;

  toggler.addEventListener('click', () => {
    target.classList.toggle('show');
    if (header) header.classList.toggle('active');
    document.body.classList.toggle(BODY_NOSCROLL_CLASS);
  });

  const closeOnResize = () => {
    if (window.innerWidth > 991) {
      target.classList.remove('show');
      if (header) header.classList.remove('active');
      document.body.classList.remove(BODY_NOSCROLL_CLASS);
    }
  };
  window.addEventListener('resize', closeOnResize);
  window.addEventListener('orientationchange', closeOnResize);
}
