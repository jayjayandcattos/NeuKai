document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.getElementById('navbar');

    window.addEventListener('scroll', () => {
        console.log('Scrolling ka na bakla!'); 

        if (window.scrollY > 60) {
            navbar.classList.add('bg-orange-500');
            navbar.classList.remove('bg-white/4');
            console.log('orange'); 
        } else {
            navbar.classList.add('bg-white/4');
            navbar.classList.remove('bg-orange-500');
            console.log('transparent'); 
        }
    });
});
