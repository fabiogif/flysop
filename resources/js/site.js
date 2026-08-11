/**
 * Bundle do site público – ES6 módulos, sem jQuery.
 */
import { initHeaderScroll } from './site/headerScroll.js';
import { initNavbarToggler } from './site/navbarToggler.js';
import { initMovetop } from './site/movetop.js';
import { initReveal } from './site/reveal.js';

function init() {
  initHeaderScroll();
  initNavbarToggler();
  initMovetop();
  initReveal();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
