'usestrict';
let current = 0;
const slides = document.querySelectorAll('#slideshow .slide');
const total = slides.length;

setInterval(() => {
  slides[current].classList.remove('active');
  current = (current + 1) % total;
  slides[current].classList.add('active');
}, 3000); // 3秒ごとに切り替え
