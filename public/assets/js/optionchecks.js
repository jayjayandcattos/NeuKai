document.querySelectorAll('.selection-btn').forEach(button => {
    button.addEventListener('click', () => {

        document.querySelectorAll('.selection-btn').forEach(btn => btn.classList.remove('active'));

        button.classList.add('active');
    });
});