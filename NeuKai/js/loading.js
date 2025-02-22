//eme lang to, can be deleted anytime HAHAHA

document.addEventListener('DOMContentLoaded', function () {
    const loadingOverlay = document.getElementById('loading-overlay');
  
    
    document.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', function (e) {
        if (link.href && !link.href.includes('#')) {
          e.preventDefault();
          loadingOverlay.classList.remove('opacity-0', 'pointer-events-none');
          setTimeout(() => {
            window.location.href = link.href;
          }, 300);
        }
      });
    });
  
    
    loadingOverlay.classList.add('opacity-0', 'pointer-events-none');
  });