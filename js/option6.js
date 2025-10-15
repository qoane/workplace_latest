// Option 6 — site JS (nav, reveal, counters, sliders + hero text animation)
document.addEventListener('DOMContentLoaded', () => {
  /* ========== Mobile navigation ========== */
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.querySelector('.nav-menu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('open');
      navToggle.classList.toggle('active');
    });

    // Tap-to-open submenus on mobile only
    navMenu.querySelectorAll('.nav-item.dropdown > a').forEach(link => {
      link.addEventListener('click', (e) => {
        if (window.innerWidth <= 992) {
          e.preventDefault();
          link.parentElement.classList.toggle('open');
        }
      });
    });
  }

  /* ========== Reveal animations ========== */
  const revealElements = document.querySelectorAll('.reveal');
  if (revealElements.length) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealElements.forEach(el => revealObserver.observe(el));
  }

  /* ========== Stats counters ========== */
  const statsSection = document.querySelector('.stats');
  if (statsSection) {
    const numberEls = statsSection.querySelectorAll('.stat .number');
    let started = false;

    const animateCounter = (el, target, duration = 2000) => {
      const startTime = performance.now();
      const tick = (now) => {
        const p = Math.min((now - startTime) / duration, 1);
        el.textContent = Math.floor(p * target);
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = target;
      };
      requestAnimationFrame(tick);
    };

    const statsObserver = new IntersectionObserver((entries, obs) => {
      if (!started && entries.some(e => e.isIntersecting)) {
        started = true;
        numberEls.forEach(el => {
          const tgt = parseInt(el.getAttribute('data-target'), 10);
          if (!Number.isNaN(tgt)) animateCounter(el, tgt);
        });
        obs.disconnect();
      }
    }, { threshold: 0.4 });

    statsObserver.observe(statsSection);
  }

  /* ========== Testimonials slider ========== */
  if (typeof Swiper !== 'undefined') {
    const testiContainer = document.querySelector('.testimonials .swiper-container');
    if (testiContainer) {
      new Swiper(testiContainer, {
        loop: true,
        speed: 600,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.testimonials .swiper-pagination', clickable: true }
      });
    }
  }

  /* ========== HOME-ONLY: Hero Swiper (autoplay always on) ========== */
  const heroEl = document.querySelector('.home .hero-slider');
  if (heroEl && typeof Swiper !== 'undefined') {
    // 1) Ensure all slides get the same overlay content:
    const firstOverlay = heroEl.querySelector('.swiper-slide .hero-overlay');
    const overlayHTML = firstOverlay ? firstOverlay.innerHTML : '';

    heroEl.querySelectorAll('.swiper-slide .hero-overlay').forEach((ov, idx) => {
      if (idx > 0 && ov.children.length === 1 && ov.querySelector('.container') && !ov.querySelector('.hero-title')) {
        // empty placeholder with only .container -> inject cloned hero copy
        ov.innerHTML = overlayHTML;
      }
    });

    // 2) Init Swiper
    const sliderDelay = parseInt(heroEl.dataset.autoplayDelay, 10);
    const sliderSpeed = parseInt(heroEl.dataset.transitionSpeed, 10);
    const autoplayDelay = Number.isFinite(sliderDelay) ? sliderDelay : 6000;
    const transitionSpeed = Number.isFinite(sliderSpeed) ? sliderSpeed : 1200;

    const heroSwiper = new Swiper(heroEl, {
      effect: 'fade',
      fadeEffect: { crossFade: true },
      loop: true,
      speed: transitionSpeed,
      autoplay: {
        delay: autoplayDelay,
        disableOnInteraction: false,
        pauseOnMouseEnter: false // keep playing even when hovered
      },
      allowTouchMove: true,
      pagination: { el: '.hero-pagination', clickable: true },
      navigation: { nextEl: '.hero-next', prevEl: '.hero-prev' }
    });

    // 3) Text entrance animation per slide
    const runTextAnim = () => {
      // reset
      heroEl.querySelectorAll('.hero-anim').forEach(el => el.classList.remove('in'));

      // get active slide's animatable items
      const active = heroEl.querySelector('.swiper-slide-active');
      if (!active) return;
      const items = active.querySelectorAll('.hero-anim');
      items.forEach((el, i) => {
        setTimeout(() => el.classList.add('in'), i * 120); // nice stagger
      });
    };

    heroSwiper.on('init', runTextAnim);
    heroSwiper.on('slideChangeTransitionStart', runTextAnim);

    // Swiper 9 triggers 'init' automatically, but to be safe:
    setTimeout(runTextAnim, 0);
  }
});
