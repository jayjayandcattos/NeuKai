document.querySelectorAll('.nav-link').forEach(button => {
    button.addEventListener('click', () => {
        const target = document.getElementById(button.dataset.target);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});