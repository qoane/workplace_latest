// JavaScript for interactive behaviours on the website

document.addEventListener('DOMContentLoaded', () => {
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.querySelector('.nav-menu');
  const dropdownItems = document.querySelectorAll('.nav-item.dropdown');

  // Toggle navigation on mobile
  if (navToggle) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('open');
      navToggle.classList.toggle('active');
    });
  }

  // Toggle dropdowns on mobile
  dropdownItems.forEach(item => {
    item.addEventListener('click', function (e) {
      if (window.innerWidth <= 992) {
        e.preventDefault();
        this.classList.toggle('open');
      }
    });
  });

  // Intersection Observer for reveal animations
  const reveals = document.querySelectorAll('.reveal');
  const observerOptions = {
    threshold: 0.1
  };
  const revealObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  reveals.forEach(el => revealObserver.observe(el));
});