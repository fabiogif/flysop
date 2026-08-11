/**
 * CIOP landing — script público (sem Vite).
 */
(function () {
  'use strict';

  document.documentElement.classList.add('js-enabled');

  function initHeaderScroll() {
    var header = document.getElementById('site-header');
    if (!header) return;
    var toggle = function () {
      var scroll = window.scrollY || document.documentElement.scrollTop;
      header.classList.toggle('nav-fixed', scroll >= 80);
    };
    window.addEventListener('scroll', toggle, { passive: true });
    toggle();
  }

  function initNavbarToggler() {
    var toggler = document.querySelector('.navbar-toggler');
    var target = document.querySelector('#navbarNav');
    var header = document.querySelector('header');
    if (!toggler || !target) return;

    toggler.addEventListener('click', function () {
      target.classList.toggle('show');
      if (header) header.classList.toggle('active');
      document.body.classList.toggle('noscroll');
      var expanded = target.classList.contains('show');
      toggler.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });

    var closeOnResize = function () {
      if (window.innerWidth > 991) {
        target.classList.remove('show');
        if (header) header.classList.remove('active');
        document.body.classList.remove('noscroll');
        toggler.setAttribute('aria-expanded', 'false');
      }
    };
    window.addEventListener('resize', closeOnResize);
  }

  function initMovetop() {
    var btn = document.getElementById('movetop');
    if (!btn) return;
    var toggleVisibility = function () {
      var scroll = document.body.scrollTop || document.documentElement.scrollTop;
      if (scroll > 80) {
        btn.hidden = false;
        btn.style.display = 'block';
      } else {
        btn.hidden = true;
        btn.style.display = 'none';
      }
    };
    window.addEventListener('scroll', toggleVisibility, { passive: true });
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    toggleVisibility();
  }

  function initReveal() {
    var nodes = document.querySelectorAll('.reveal');
    if (!nodes.length) return;

    if (!('IntersectionObserver' in window)) {
      nodes.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    nodes.forEach(function (el) { observer.observe(el); });
  }

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
})();
