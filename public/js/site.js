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

    var markVisible = function (el) {
      el.classList.add('is-visible');
    };

    // Garante conteúdo legível mesmo se o observer falhar
    var fallback = window.setTimeout(function () {
      nodes.forEach(markVisible);
    }, 800);

    if (!('IntersectionObserver' in window)) {
      nodes.forEach(markVisible);
      window.clearTimeout(fallback);
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        markVisible(entry.target);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.05, rootMargin: '0px 0px 15% 0px' });

    nodes.forEach(function (el) {
      var rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight * 0.95) {
        markVisible(el);
      } else {
        observer.observe(el);
      }
    });

    window.setTimeout(function () {
      window.clearTimeout(fallback);
    }, 900);
  }

  function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function initParallax() {
    if (prefersReducedMotion()) return;

    var bands = document.querySelectorAll('[data-parallax]');
    var scenes = document.querySelectorAll('[data-parallax-scene]');
    if (!bands.length && !scenes.length) return;

    var ticking = false;

    var update = function () {
      var vh = window.innerHeight || document.documentElement.clientHeight;

      bands.forEach(function (band) {
        var rect = band.getBoundingClientRect();
        var visible = rect.bottom > 0 && rect.top < vh;
        if (!visible) return;

        var progress = (vh / 2 - (rect.top + rect.height / 2)) / vh;
        var layers = band.querySelectorAll('[data-parallax-layer]');
        layers.forEach(function (layer) {
          var speed = parseFloat(layer.getAttribute('data-speed') || '0.3');
          var y = progress * speed * 120;
          layer.style.transform = 'translate3d(0, ' + y.toFixed(2) + 'px, 0)';
        });
      });

      scenes.forEach(function (scene) {
        var rect = scene.getBoundingClientRect();
        if (rect.bottom < 0 || rect.top > vh) return;
        var speed = parseFloat(scene.getAttribute('data-speed') || '0.1');
        var progress = (vh / 2 - (rect.top + rect.height / 2)) / vh;
        var y = progress * speed * 80;
        scene.style.transform = 'translate3d(0, ' + y.toFixed(2) + 'px, 0)';
      });

      ticking = false;
    };

    var onScroll = function () {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(update);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
  }

  function init() {
    initHeaderScroll();
    initNavbarToggler();
    initMovetop();
    initReveal();
    initParallax();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
