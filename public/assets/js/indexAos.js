
document.addEventListener('DOMContentLoaded', function() {
    
    const animatedElements = document.querySelectorAll('.aos');
    
    
    const threshold = 0.1; 
    const rootMargin = '0px'; 
    
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        
        if (entry.isIntersecting) {
          
          const animationType = entry.target.dataset.aosAnimation || 'fade-in';
          const animationDelay = entry.target.dataset.aosDelay || '0';
          
          entry.target.classList.add('aos-animate', animationType);
          entry.target.style.transitionDelay = `${animationDelay}ms`;
          
          
          if (entry.target.dataset.aosOnce !== 'false') {
            observer.unobserve(entry.target);
          }
        } else {
          
          if (entry.target.dataset.aosOnce === 'false') {
            entry.target.classList.remove('aos-animate');
          }
        }
      });
    }, {
      threshold: threshold,
      rootMargin: rootMargin
    });
    
    
    animatedElements.forEach(element => {
      observer.observe(element);
    });
  });