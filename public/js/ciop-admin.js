/**
 * CIOP painel — pequenos ajustes de UX responsiva
 */
(function () {
  'use strict';

  function closeSidebarOnNavClick() {
    if (window.innerWidth >= 992) return;
    document.addEventListener('click', function (event) {
      var link = event.target.closest('.main-sidebar .nav-link');
      if (!link || link.getAttribute('data-widget') === 'treeview') return;
      if (!document.body.classList.contains('sidebar-open')) return;
      document.body.classList.remove('sidebar-open');
      document.body.classList.add('sidebar-closed', 'sidebar-collapse');
    });
  }

  function closeSidebarOnOverlay() {
    document.addEventListener('click', function (event) {
      if (window.innerWidth >= 992) return;
      if (!document.body.classList.contains('sidebar-open')) return;
      if (event.target.closest('.main-sidebar') || event.target.closest('[data-widget="pushmenu"]')) {
        return;
      }
      // Clique fora do menu fecha o drawer no mobile
      if (event.target === document.body || event.target.classList.contains('content-wrapper')) {
        document.body.classList.remove('sidebar-open');
      }
    });
  }

  function init() {
    closeSidebarOnNavClick();
    closeSidebarOnOverlay();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
