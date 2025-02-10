document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.getElementById('navbar');

    window.addEventListener('scroll', () => {
        console.log('Scrolling ka na bakla!'); 

        if (window.scrollY > 50) {
            navbar.classList.add('bg-orange-500');
            navbar.classList.remove('bg-white/4');
            console.log('Navbar turned orange'); 
        } else {
            navbar.classList.add('bg-white/4');
            navbar.classList.remove('bg-orange-500');
            console.log('Navbar back to transparent'); 
        }
    });
});
