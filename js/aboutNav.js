

document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('neukai-menu-toggle');
    const mobileNav = document.getElementById('neukai-mobile-nav');
    const menuLinks = document.querySelectorAll('.neukai-menu-link');
    const navIndicator = document.getElementById('neukai-nav-indicator');
    
    
    menuToggle.addEventListener('click', function() {
        this.classList.toggle('active');
        mobileNav.classList.toggle('active');
        
        
        if (mobileNav.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    });
    
    
    menuLinks.forEach(link => {
        
        link.addEventListener('mouseenter', function() {
            const linkRect = this.getBoundingClientRect();
            navIndicator.style.top = `${linkRect.top}px`;
            navIndicator.style.height = `${linkRect.height}px`;
        });
        
        
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            
            const targetId = this.getAttribute('data-target');
            const targetSection = document.getElementById(targetId);
            
            
            const ripple = document.createElement('span');
            ripple.classList.add('absolute', 'w-full', 'h-full', 'bg-white', 'rounded-full', 'opacity-0');
            ripple.style.transform = 'scale(0)';
            ripple.style.left = '0';
            ripple.style.top = '0';
            ripple.style.animation = 'neukai-ripple 0.6s ease-out';
            this.appendChild(ripple);
            
            
            setTimeout(() => {
                ripple.remove();
                
                
                menuToggle.classList.remove('active');
                mobileNav.classList.remove('active');
                document.body.style.overflow = '';
                
                
                if (targetSection) {
                    setTimeout(() => {
                        targetSection.scrollIntoView({ behavior: 'smooth' });
                    }, 300);
                }
            }, 300);
        });
    });
    
    
    document.addEventListener('click', function(e) {
        if (!mobileNav.contains(e.target) && !menuToggle.contains(e.target) && mobileNav.classList.contains('active')) {
            menuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});