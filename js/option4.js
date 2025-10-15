// Custom JavaScript for the "fourth option" homepage (best design)
// This script manages the mobile navigation, reveal animations,
// statistics counters, and the testimonials slider. It maintains
// smooth performance while introducing a modern hero with form.

document.addEventListener('DOMContentLoaded', () => {
  /* Navigation toggling */
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.querySelector('.nav-menu');
  const dropdownItems = document.querySelectorAll('.nav-item.dropdown');

  if (navToggle) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('open');
      navToggle.classList.toggle('active');
    });
  }

  dropdownItems.forEach(item => {
    item.addEventListener('click', function (e) {
      if (window.innerWidth <= 992) {
        e.preventDefault();
        this.classList.toggle('open');
      }
    });
  });

  /* Reveal animations */
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

  /* Statistics counters */
  const statsSection = document.querySelector('.stats');
  const numberElements = document.querySelectorAll('.stat .number');
  let countersStarted = false;

  function animateCounter(el, target, duration = 2000) {
    const startTime = performance.now();
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

  /* Testimonials slider */
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