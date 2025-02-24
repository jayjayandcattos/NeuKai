const slidingText = document.getElementById("sliding-text");
const slideshow = document.getElementById("slideshow-images");

slidingText.innerHTML += slidingText.innerHTML;
slideshow.innerHTML += slideshow.innerHTML;

let textPosition = 0;
let imagePosition = 0;
const textSpeed = 0.5;
const imageSpeed = 0.5; 

function slideText() {
  textPosition -= textSpeed;
  if (Math.abs(textPosition) >= slidingText.scrollWidth / 2) {
    textPosition = 0;
  }
  slidingText.style.transform = `translateX(${textPosition}px)`;
  requestAnimationFrame(slideText);
}

function slideImages() {
  imagePosition -= imageSpeed;
  if (Math.abs(imagePosition) >= slideshow.scrollWidth / 2) {
    imagePosition = 0;
  }
  slideshow.style.transform = `translateX(${imagePosition}px)`;
  requestAnimationFrame(slideImages);
}

slideText();
slideImages();