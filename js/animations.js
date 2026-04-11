/* ================================================================
   ANIMATIONS.JS — Nu leren voor later
   Scroll-reveal + glassmorphism header + hero stagger
   Versie: 1.0 — 2026-04-11
   ================================================================ */
(function () {
  'use strict';

  /* ── Hero stagger animatie ── */
  /* Voeg .hero-loaded toe zodra pagina geladen is */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      requestAnimationFrame(function () {
        document.body.classList.add('hero-loaded');
      });
    });
  } else {
    requestAnimationFrame(function () {
      document.body.classList.add('hero-loaded');
    });
  }

  /* ── Header: glassmorphism scrolled-state ── */
  var header = document.querySelector('.site-header');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('scrolled', window.scrollY > 24);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); /* directe check bij laden */
  }

  /* ── Scroll Reveal via IntersectionObserver ── */
  if (!('IntersectionObserver' in window)) {
    /* Fallback voor zeer oude browsers: alles direct tonen */
    document.querySelectorAll('.reveal').forEach(function (el) {
      el.classList.add('is-visible');
    });
    return;
  }

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target); /* fire-once */
        }
      });
    },
    {
      threshold: 0.08,
      rootMargin: '0px 0px -40px 0px'
    }
  );

  /* Observeer alle .reveal elementen */
  document.querySelectorAll('.reveal').forEach(function (el) {
    observer.observe(el);
  });

})();
