
const slidingText = document.getElementById('sliding-text');
let textPosition = 0;

function slideText() {
    textPosition -= 1; 
    if (Math.abs(textPosition) >= slidingText.scrollWidth / 2) {
        textPosition = 0; 
    }
    slidingText.style.transform = `translateX(${textPosition}px)`;
    requestAnimationFrame(slideText);
}

slideText(); 



const slideshow = document.getElementById('slideshow-images');
let imagePosition = 0;

function slideImages() {
    imagePosition -= 1;
    if (Math.abs(imagePosition) >= slideshow.scrollWidth / 2) {
        imagePosition = 0; 
    }
    slideshow.style.transform = `translateX(${imagePosition}px)`;
    requestAnimationFrame(slideImages);
}

slideImages(); 
