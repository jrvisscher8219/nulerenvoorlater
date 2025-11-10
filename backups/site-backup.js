/* filepath: js\site.js */
(function(){
  document.addEventListener('DOMContentLoaded', () => {
    // Wrap DOM queries in try-catch
    try {
      const btn = document.querySelector(".nav-toggle");
      const nav = document.getElementById("primary-nav");
      const navLinks = document.querySelectorAll(".nav a");
      const footerLinks = document.querySelectorAll(".footer-nav a");
      const ALL_LINKS = Array.from(navLinks).concat(Array.from(footerLinks || []));

      function openNav() {
        if (!nav) return;
        nav.classList.add('open');
        if (btn) btn.setAttribute('aria-expanded', 'true');
        const first = nav.querySelector('a');
        if (first) first.focus();
      }
      function closeNav(returnFocus = true) {
        if (!nav) return;
        nav.classList.remove('open');
        if (btn) {
          btn.setAttribute('aria-expanded', 'false');
          if (returnFocus) btn.focus();
        }
      }

      if (btn && nav) {
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
          const target = e.target;
          if (!nav.contains(target) && target !== btn && !btn.contains(target)) {
            closeNav(false);
          }
        });
      }

      // mark active links & aria-current
      const path = location.pathname.split("/").pop() || "index.html";
      ALL_LINKS.forEach(a => {
        try {
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
        } catch (err) {}
      });

      // close mobile nav when link clicked
      navLinks.forEach(link => {
        link.addEventListener('click', () => {
          const toggleVisible = btn && window.getComputedStyle(btn).display !== 'none';
          if (toggleVisible && nav && nav.classList.contains('open')) closeNav();
        });
      });

      // set current year
      const y = document.getElementById('year');
      if (y) y.textContent = new Date().getFullYear();

      // scroll-to-top button
      let toTop = document.getElementById('toTop');
      if (!toTop) {
        toTop = document.createElement('button');
        toTop.id = 'toTop';
        toTop.type = 'button';
        toTop.setAttribute('aria-label', 'Terug naar boven');
        toTop.hidden = true;
        toTop.className = 'to-top';
        toTop.textContent = '↑';
        toTop.style.position = 'fixed';
        toTop.style.right = '16px';
        toTop.style.bottom = '16px';
        toTop.style.padding = '10px 12px';
        toTop.style.borderRadius = '999px';
        toTop.style.boxShadow = '0 6px 16px rgba(0,0,0,0.12)';
        toTop.style.zIndex = '9999';
        document.body.appendChild(toTop);
      }

      let ticking = false;

      function onScroll() {
        if (!ticking) {
          requestAnimationFrame(() => {
            const show = window.scrollY >= 600;
            toTop.hidden = !show;
            ticking = false;
          });
          ticking = true;
        }
      }
      window.addEventListener('scroll', onScroll, { passive: true });

      toTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        toTop.hidden = true;
      });
      toTop.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });

      // lightweight focus/mouse helper
      document.addEventListener('mousedown', () => document.documentElement.classList.add('using-mouse'));
      document.addEventListener('keydown', () => document.documentElement.classList.remove('using-mouse'));

      // CONTACT FORM: add client-side submit, honeypot check and inline status message
      document.querySelectorAll('form').forEach(form => {
        const hasName = !!form.querySelector('[name="name"]');
        const hasEmail = !!form.querySelector('[name="email"]');
        const hasMessage = !!form.querySelector('[name="message"], textarea[name="message"]');
        if (hasName && hasEmail && hasMessage) {
          if (!form.id) form.id = 'contact';
          // ensure honeypot exists
          if (!form.querySelector('[name="company"]')) {
            const hp = document.createElement('input');
            hp.type = 'text'; hp.name = 'company'; hp.tabIndex = -1; hp.autocomplete = 'off';
            hp.style.position = 'absolute'; hp.style.left = '-9999px';
            form.prepend(hp);
          }
          // ensure status element exists
          if (!form.querySelector('#form-msg')) {
            const p = document.createElement('p'); p.id = 'form-msg'; p.setAttribute('role','status'); p.setAttribute('aria-live','polite');
            form.appendChild(p);
          }

          form.addEventListener('submit', async (e) => {
            // If using a server handler (PHP) or external provider, allow native submit to proceed
            const action = (form.getAttribute('action') || '').trim();
            const isNetlify = form.hasAttribute('data-netlify');
            const isFormspree = /formspree\.io\//.test(action);
            const isPhpHandler = /\.php(\?|$)/i.test(action);
            if (isNetlify || isFormspree || isPhpHandler) {
              return; // let the browser submit normally
            }

            e.preventDefault();
            const honeypot = form.querySelector('[name="company"]');
            if (honeypot && honeypot.value) return; // spam
            const msg = form.querySelector('#form-msg');
            msg && (msg.textContent = 'Bezig met verzenden…');
            const data = new FormData(form);
            try {
              const actionUrl = form.action && form.action !== '#' ? form.action : '/api/contact';
              const controller = new AbortController();
              const timeoutId = setTimeout(() => controller.abort(), 8000); // 8s timeout
              
              const res = await fetch(actionUrl, { 
                method: 'POST', 
                body: data,
                signal: controller.signal 
              });
              
              clearTimeout(timeoutId);
              
              if (res.ok) {
                msg && (msg.textContent = 'Bedankt, ik reageer snel.');
                form.reset();
              } else {
                msg && (msg.textContent = 'Er ging iets mis. Probeer later nog eens.');
              }
            } catch (err) {
              if (err.name === 'AbortError') {
                msg && (msg.textContent = 'Timeout - probeer het later opnieuw.');
              } else {
                msg && (msg.textContent = 'Er ging iets mis. Controleer je verbinding.');
              }
            }
          });

          // ARIA: markeer ongeldige velden en maak het live bij typen
          const requiredFields = form.querySelectorAll('[required]');
          requiredFields.forEach(f => {
            const err = form.querySelector(`.field-error[data-error-for="${f.id}"]`);
            // wanneer browser-validatie faalt
            f.addEventListener('invalid', () => {
              f.setAttribute('aria-invalid','true');
              if (err) err.textContent = f.validationMessage || 'Vul dit veld in.';
            }, true);
            // haal aria-invalid weg zodra veld geldig is
            f.addEventListener('input', () => {
              if (f.checkValidity()) {
                f.removeAttribute('aria-invalid');
                if (err) err.textContent = '';
              }
            });
          });
        }
      });

      // expose small helpers for debugging
      try { window.__site_helpers = { openNav: openNav, closeNav: closeNav }; } catch(e){}
    } catch (err) {
      console.warn('Nav elements not found:', err);
    }
  });
})();
 
