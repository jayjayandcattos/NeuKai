const menuToggle = document.getElementById('menu-toggle');
const mobileMenu = document.getElementById('mobile-menu');
const icon = document.getElementById('hamburger-icon');

menuToggle.addEventListener('click', () => {
    mobileMenu.classList.toggle('active');
    icon.classList.toggle('active');
    if (icon.classList.contains('active')) {
        icon.setAttribute('stroke', '#fbb801');
    } else {
        icon.setAttribute('stroke', '#FFFFFF');
    }
});
