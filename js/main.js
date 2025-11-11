document.addEventListener('DOMContentLoaded', function () {
  const navToggle = document.querySelector('.nav-toggle');
  const navList = document.getElementById('primary-navigation');
  const header = document.querySelector('.site-header');

  // Mobile menu toggle
  if (navToggle && navList) {
    navToggle.addEventListener('click', () => {
      const isOpen = navList.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    // Close menu on link click (mobile)
    navList.querySelectorAll('a[href^="#"]').forEach((link) => {
      link.addEventListener('click', () => {
        if (navList.classList.contains('open')) {
          navList.classList.remove('open');
          navToggle.setAttribute('aria-expanded', 'false');
        }
      });
    });
  }

  // Smooth scroll for in-page anchors
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (!href || href === '#' || href.length < 2) return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      const headerOffset = header ? header.offsetHeight + 8 : 0;
      const elementPosition = target.getBoundingClientRect().top + window.pageYOffset;
      const offsetPosition = elementPosition - headerOffset;
      window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
    });
  });

  // Header subtle shrink on scroll
  let lastY = window.pageYOffset;
  const onScroll = () => {
    const y = window.pageYOffset;
    if (!header) return;
    if (y > 10) {
      header.style.boxShadow = '0 6px 18px rgba(0,0,0,0.06)';
    } else {
      header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.04)';
    }
    lastY = y;
  };
  window.addEventListener('scroll', onScroll, { passive: true });

  // Timeline scroll animation
  const timelineContainer = document.querySelector('.timeline-container');
  if (timelineContainer) {
    const timelineItems = document.querySelectorAll('.timeline-item');
    const timelineLine = document.querySelector('.timeline-line');
    
    // Intersection Observer for timeline items
    const observerOptions = {
      root: null,
      rootMargin: '0px 0px -100px 0px',
      threshold: 0.2
    };

    const timelineObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          // Get the index of this item in the timelineItems array
          const itemIndex = Array.from(timelineItems).indexOf(entry.target);
          
          // Add delay based on index for staggered animation
          setTimeout(() => {
            entry.target.classList.add('visible');
            
            // Animate timeline line progressively
            const visibleItems = document.querySelectorAll('.timeline-item.visible');
            if (visibleItems.length === timelineItems.length && timelineLine) {
              timelineLine.classList.add('animated');
            }
          }, itemIndex * 200); // 200ms delay between each item
          
          // Stop observing once visible
          timelineObserver.unobserve(entry.target);
        }
      });
    }, observerOptions);

    // Observe each timeline item
    timelineItems.forEach((item) => {
      timelineObserver.observe(item);
    });

    // Check if timeline is already in view on page load
    const checkInitialView = () => {
      const rect = timelineContainer.getBoundingClientRect();
      const isInView = rect.top < window.innerHeight && rect.bottom > 0;
      
      if (isInView) {
        timelineItems.forEach((item, index) => {
          const itemRect = item.getBoundingClientRect();
          const isItemVisible = itemRect.top < window.innerHeight && itemRect.bottom > 0;
          
          if (isItemVisible) {
            setTimeout(() => {
              item.classList.add('visible');
            }, index * 200);
          }
        });
        
        // Animate timeline line if all items are visible
        setTimeout(() => {
          const allVisible = Array.from(timelineItems).every(item => item.classList.contains('visible'));
          if (allVisible && timelineLine) {
            timelineLine.classList.add('animated');
          }
        }, timelineItems.length * 200);
      }
    };

    // Check on page load
    checkInitialView();
  }

  // Contribution cards counting animation
  const contributionCards = document.querySelectorAll('.contribution-card');
  if (contributionCards.length > 0) {
    const contributionObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const card = entry.target;
          const valueElement = card.querySelector('.contribution-value');
          if (valueElement && !valueElement.classList.contains('counted')) {
            const targetValue = parseInt(valueElement.getAttribute('data-target'));
            animateCounter(valueElement, targetValue);
            valueElement.classList.add('counted');
            contributionObserver.unobserve(entry.target);
          }
        }
      });
    }, {
      root: null,
      rootMargin: '0px 0px -50px 0px',
      threshold: 0.3
    });

    contributionCards.forEach((card) => {
      contributionObserver.observe(card);
    });

    function animateCounter(element, target) {
      const duration = 2000; // 2 seconds
      const start = 0;
      const increment = target / (duration / 16); // 60fps
      let current = start;

      const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
          element.textContent = target;
          clearInterval(timer);
        } else {
          element.textContent = Math.floor(current);
        }
      }, 16);
    }

    // Check if cards are already in view on page load
    contributionCards.forEach((card) => {
      const rect = card.getBoundingClientRect();
      const isInView = rect.top < window.innerHeight && rect.bottom > 0;
      
      if (isInView) {
        const valueElement = card.querySelector('.contribution-value');
        if (valueElement && !valueElement.classList.contains('counted')) {
          const targetValue = parseInt(valueElement.getAttribute('data-target'));
          animateCounter(valueElement, targetValue);
          valueElement.classList.add('counted');
        }
      }
    });
  }
});


