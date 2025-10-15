// Custom JavaScript for the "third option" homepage
// This script handles navigation toggling, reveal animations, animated
// counters, and the testimonials slider. It builds upon the existing
// design philosophy by adding interactivity without overwhelming
// visitors, preserving smooth performance on all devices.

document.addEventListener('DOMContentLoaded', () => {
  /* -----------------------------------------------------
   * Navigation toggling
   * ----------------------------------------------------- */
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.querySelector('.nav-menu');
  const dropdownItems = document.querySelectorAll('.nav-item.dropdown');

  // Toggle the mobile navigation menu
  if (navToggle) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('open');
      navToggle.classList.toggle('active');
    });
  }

  // Expand or collapse dropdowns on mobile
  dropdownItems.forEach(item => {
    item.addEventListener('click', function (e) {
      if (window.innerWidth <= 992) {
        e.preventDefault();
        this.classList.toggle('open');
      }
    });
  });

  /* -----------------------------------------------------
   * Reveal animations using IntersectionObserver
   * Elements with the class `.reveal` will fade and slide
   * into view when scrolled into the viewport. Additional
   * delay classes (delay-1, delay-2, delay-3) are handled
   * via CSS transition delays.
   * ----------------------------------------------------- */
  const revealElements = document.querySelectorAll('.reveal');
  const revealObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.15
  });
  revealElements.forEach(el => revealObserver.observe(el));

  /* -----------------------------------------------------
   * Animated statistics counters
   * Counts up numbers in the `.stats` section when it
   * enters the viewport. Each `.number` element should
   * have a `data-target` attribute specifying the final
   * count value.
   * ----------------------------------------------------- */
  const statsSection = document.querySelector('.stats');
  const numberElements = document.querySelectorAll('.stat .number');
  let countersStarted = false;

  function animateCounter(el, target, duration = 2000) {
    const startTime = performance.now();
    const startValue = 0;
    function updateCounter(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const value = Math.floor(progress * target);
      el.textContent = value;
      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        el.textContent = target;
      }
    }
    requestAnimationFrame(updateCounter);
  }

  function handleStats(entries, observer) {
    entries.forEach(entry => {
      if (entry.isIntersecting && !countersStarted) {
        countersStarted = true;
        numberElements.forEach(el => {
          const target = parseInt(el.getAttribute('data-target'));
          if (!isNaN(target)) {
            animateCounter(el, target);
          }
        });
        observer.unobserve(entry.target);
      }
    });
  }

  if (statsSection) {
    const statsObserver = new IntersectionObserver(handleStats, {
      threshold: 0.4
    });
    statsObserver.observe(statsSection);
  }

  /* -----------------------------------------------------
   * Testimonials slider using Swiper.js
   * Initializes the carousel when the page loads.
   * Pagination dots are clickable, and autoplay is enabled
   * for continuous rotation of testimonials.
   * ----------------------------------------------------- */
  if (typeof Swiper !== 'undefined') {
    new Swiper('.swiper-container', {
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true
      },
    });
  }
});