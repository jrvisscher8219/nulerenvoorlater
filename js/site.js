(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initActiveLinks();
    initScrollToTop();
    initContactForm();
    initTestimonialSlider();
    initCookieBanner();
    initMouseFocusHelper();
  });

  /**
   * Mobile Navigation Toggle
   */
  function initNavigation() {
    const btn = document.querySelector(".nav-toggle");
    const nav = document.getElementById("primary-nav");
    
    if (!btn || !nav) return;

    function openNav() {
      nav.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      const first = nav.querySelector('a');
      if (first) first.focus();
    }

    function closeNav(returnFocus = true) {
      nav.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
      if (returnFocus) btn.focus();
    }

    btn.addEventListener('click', () => {
      const isOpen = nav.classList.toggle('open');
      btn.setAttribute('aria-expanded', String(isOpen));
      if (isOpen) {
        const first = nav.querySelector('a');
        if (first) first.focus();
      }
    });

    btn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const isOpen = nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', String(isOpen));
        if (isOpen) {
          const first = nav.querySelector('a');
          if (first) first.focus();
        }
      } else if (e.key === 'Escape') {
        closeNav();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && nav.classList.contains('open')) {
        closeNav();
      }
    });

    document.addEventListener('click', (e) => {
      if (!nav.classList.contains('open')) return;
      if (!nav.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
        closeNav(false);
      }
    });

    // Close nav when a link is clicked
    nav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.getComputedStyle(btn).display !== 'none' && nav.classList.contains('open')) {
          closeNav();
        }
      });
    });

    // Expose helpers for debugging if needed
    window.__site_helpers = { openNav, closeNav };
  }

  /**
   * Active Link Highlighting
   */
  function initActiveLinks() {
    const path = location.pathname.split("/").pop() || "index.html";
    const navLinks = document.querySelectorAll(".nav a");
    const footerLinks = document.querySelectorAll(".footer-nav a");
    const allLinks = [...navLinks, ...footerLinks];

    allLinks.forEach(a => {
      const href = a.getAttribute("href");
      if (!href) return;
      const hrefName = href.split("/").pop();
      if (hrefName === path || (path === "" && hrefName === "index.html")) {
        a.classList.add("active");
        a.setAttribute("aria-current", "page");
      } else {
        a.classList.remove("active");
        a.removeAttribute("aria-current");
      }
    });
  }

  /**
   * Scroll to Top Button
   */
  function initScrollToTop() {
    let toTop = document.getElementById('toTop');
    
    // Create button if it doesn't exist
    if (!toTop) {
      toTop = document.createElement('button');
      toTop.id = 'toTop';
      toTop.type = 'button';
      toTop.setAttribute('aria-label', 'Terug naar boven');
      toTop.hidden = true;
      toTop.className = 'to-top';
      toTop.textContent = '↑';
      
      // Inline styles for the button
      Object.assign(toTop.style, {
        position: 'fixed',
        right: '16px',
        bottom: '16px',
        padding: '10px 12px',
        borderRadius: '999px',
        boxShadow: '0 6px 16px rgba(0,0,0,0.12)',
        zIndex: '9999',
        border: 'none',
        background: 'var(--accent, #507a76)',
        color: '#fff',
        cursor: 'pointer'
      });
      
      document.body.appendChild(toTop);
    }

    // Update year if element exists
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    let ticking = false;
    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          toTop.hidden = window.scrollY < 600;
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });

    const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });

    toTop.addEventListener('click', scrollToTop);
    toTop.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        scrollToTop();
      }
    });
  }

  /**
   * Contact Form Handling (PHP + reCAPTCHA)
   */
  function initContactForm() {
    document.querySelectorAll('form').forEach(form => {
      // Basic check to see if it's a contact form
      if (!form.querySelector('[name="email"]')) return;

      if (!form.id) form.id = 'contact';

      // Honeypot
      if (!form.querySelector('[name="company"]')) {
        const hp = document.createElement('input');
        hp.type = 'text'; hp.name = 'company'; hp.tabIndex = -1; hp.autocomplete = 'off';
        hp.style.position = 'absolute'; hp.style.left = '-9999px';
        hp.setAttribute('aria-hidden', 'true');
        form.prepend(hp);
      }

      // Status message area
      let msg = form.querySelector('#form-msg');
      if (!msg) {
        msg = document.createElement('p');
        msg.id = 'form-msg';
        msg.setAttribute('role', 'status');
        msg.setAttribute('aria-live', 'polite');
        form.appendChild(msg);
      }

      form.addEventListener('submit', async (e) => {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.textContent : '';
        
        // Check honeypot
        const honeypot = form.querySelector('[name="company"]');
        if (honeypot && honeypot.value) {
          e.preventDefault();
          return; // Silent fail for bots
        }

        // Handle reCAPTCHA if present
        if (typeof grecaptcha !== 'undefined') {
          e.preventDefault();
          
          if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Verzenden…';
          }

          try {
            const token = await grecaptcha.execute('6LfPHgssAAAAAKkkIX0V0qhgc7yvR0ZUJ9ACkJ4P', {
              action: 'submit_contact'
            });
            
            let tokenInput = form.querySelector('[name="recaptcha_token"]');
            if (!tokenInput) {
              tokenInput = document.createElement('input');
              tokenInput.type = 'hidden';
              tokenInput.name = 'recaptcha_token';
              form.appendChild(tokenInput);
            }
            tokenInput.value = token;
            
            form.submit();
          } catch (err) {
            console.warn('reCAPTCHA error:', err);
            // Fallback: submit anyway
            form.submit();
          }
        } else {
          // No reCAPTCHA, just submit normally (or via fetch if preferred, but keeping it simple as per PHP handler)
          if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Verzenden…';
          }
        }
      });

      // Live validation feedback
      const requiredFields = form.querySelectorAll('[required]');
      requiredFields.forEach(f => {
        const err = form.querySelector(`.field-error[data-error-for="${f.id}"]`);
        f.addEventListener('invalid', () => {
          f.setAttribute('aria-invalid', 'true');
          if (err) err.textContent = f.validationMessage || 'Vul dit veld in.';
        }, true);
        
        f.addEventListener('input', () => {
          if (f.checkValidity()) {
            f.removeAttribute('aria-invalid');
            if (err) err.textContent = '';
          }
        });
      });
    });
  }

  /**
   * Testimonial Slider
   */
  function initTestimonialSlider() {
    const rows = document.querySelectorAll('.testimonial-row');
    const dots = document.querySelectorAll('.testimonial-dot');
    const sliderContainer = document.querySelector('.testimonial-slider');
    
    if (!rows.length || !dots.length) return;

    let currentIndex = 0;
    let intervalId;
    let isPaused = false;

    function showTestimonial(index) {
      if (index < 0 || index >= rows.length) return;
      
      rows.forEach((row, i) => {
        row.classList.toggle('active', i === index);
        row.setAttribute('aria-hidden', i === index ? 'false' : 'true');
      });
      
      dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
        dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
        dot.setAttribute('tabindex', i === index ? '0' : '-1');
      });
      
      currentIndex = index;
    }

    function nextTestimonial() {
      if (!isPaused) {
        showTestimonial((currentIndex + 1) % rows.length);
      }
    }

    function startAutoplay() {
      stopAutoplay();
      intervalId = setInterval(nextTestimonial, 5000);
    }

    function stopAutoplay() {
      clearInterval(intervalId);
    }

    // Event Listeners for Dots
    dots.forEach((dot) => {
      const handleDotClick = (e) => {
        e.preventDefault();
        const targetIndex = parseInt(dot.getAttribute('data-index'), 10);
        stopAutoplay();
        showTestimonial(targetIndex);
        startAutoplay(); // Restart timer
      };

      dot.addEventListener('click', handleDotClick);
      dot.addEventListener('touchend', handleDotClick);
      
      dot.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          handleDotClick(e);
        }
      });
    });

    // Pause on hover/focus
    if (sliderContainer) {
      sliderContainer.addEventListener('mouseenter', () => { isPaused = true; });
      sliderContainer.addEventListener('mouseleave', () => { isPaused = false; });
      sliderContainer.addEventListener('focusin', () => { isPaused = true; });
      sliderContainer.addEventListener('focusout', () => { isPaused = false; });
    }

    // Initialize
    showTestimonial(0);
    startAutoplay();
  }

  /**
   * Cookie Consent Banner (Consent Mode v2)
   */
  function initCookieBanner() {
    const CONSENT_COOKIE = 'nlvl_consent';
    const GA_MEAS_ID = 'G-2XKMZ9VR1Z';
    const ONE_YEAR = 365 * 24 * 60 * 60;

    function setCookie(name, value, maxAgeSeconds) {
      document.cookie = `${name}=${encodeURIComponent(value)}; Max-Age=${maxAgeSeconds}; Path=/; SameSite=Lax`;
    }

    function getCookie(name) {
      const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.$?*|{}()\[\]\\\/\+^]/g, '\\$&') + '=([^;]*)'));
      return m ? decodeURIComponent(m[1]) : null;
    }

    function applyConsent(value) {
      const granted = value === 'granted';
      if (typeof gtag === 'function') {
        gtag('consent', 'update', {
          ad_storage: granted ? 'granted' : 'denied',
          analytics_storage: granted ? 'granted' : 'denied'
        });
        if (granted) {
          gtag('config', GA_MEAS_ID);
        }
      }
    }

    function createBanner() {
      if (document.getElementById('cookie-banner')) return;

      const banner = document.createElement('div');
      banner.id = 'cookie-banner';
      banner.setAttribute('role', 'dialog');
      banner.setAttribute('aria-live', 'polite');
      banner.setAttribute('aria-label', 'Cookiekeuze');
      
      Object.assign(banner.style, {
        position: 'fixed',
        inset: 'auto 1rem 1rem 1rem',
        zIndex: '99999',
        background: '#ffffff',
        color: '#0b1f1e',
        border: '1px solid rgba(0,0,0,0.1)',
        boxShadow: '0 12px 32px rgba(0,0,0,0.18)',
        borderRadius: '12px',
        padding: '1rem',
        maxWidth: '720px',
        margin: '0 auto',
        font: '14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial'
      });

      banner.innerHTML = `
        <div style="display:flex; gap:1rem; flex-direction:column;">
           <div style="flex:1; min-width:0;">
             <strong style="display:block; margin-bottom:.5rem; color:#22524f; font-size:15px;">Cookies en privacy</strong>
             <p style="margin:0; color:#355c59; font-size:14px; line-height:1.5;">We gebruiken alleen analytische cookies om onze site te verbeteren. 
             We plaatsen deze pas na jouw akkoord. Je keuze kun je later altijd wijzigen via de privacy-pagina.</p>
           </div>
           <div style="display:flex; gap:.5rem; flex-wrap:wrap; justify-content:stretch;">
             <button id="cb-decline" type="button" style="flex:1; min-width:120px; padding:.65rem 1rem; border-radius:10px; border:1px solid #cfd8d7; background:#fff; color:#22524f; font-size:14px; cursor:pointer;">Weigeren</button>
             <button id="cb-accept" type="button" style="flex:1; min-width:120px; padding:.65rem 1rem; border-radius:10px; border:0; background:#507a76; color:#fff; font-size:14px; cursor:pointer;">Alles accepteren</button>
           </div>
         </div>
      `;

      document.body.appendChild(banner);

      const btnAccept = banner.querySelector('#cb-accept');
      const btnDecline = banner.querySelector('#cb-decline');

      const hide = () => banner.remove();

      btnAccept.addEventListener('click', () => {
        setCookie(CONSENT_COOKIE, 'granted', ONE_YEAR);
        applyConsent('granted');
        hide();
      });

      btnDecline.addEventListener('click', () => {
        setCookie(CONSENT_COOKIE, 'denied', ONE_YEAR);
        applyConsent('denied');
        hide();
      });
    }

    const choice = getCookie(CONSENT_COOKIE);
    if (choice === 'granted' || choice === 'denied') {
      applyConsent(choice);
    } else {
      createBanner();
    }
  }

  /**
   * Mouse vs Keyboard Focus Helper
   */
  function initMouseFocusHelper() {
    document.addEventListener('mousedown', () => document.documentElement.classList.add('using-mouse'));
    document.addEventListener('keydown', () => document.documentElement.classList.remove('using-mouse'));
  }

})();
