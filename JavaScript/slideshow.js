'use strict';

// スライドショーの制御
let currentSlideIndex = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');
let slideInterval;

function showSlide(index) {
    // 全てのスライドを非アクティブに
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    // 指定されたスライドをアクティブに
    slides[index].classList.add('active');
    dots[index].classList.add('active');

    currentSlideIndex = index;
}

function nextSlide() {
    const nextIndex = (currentSlideIndex + 1) % slides.length;
    showSlide(nextIndex);
}

function prevSlide() {
    const prevIndex = (currentSlideIndex - 1 + slides.length) % slides.length;
    showSlide(prevIndex);
}

function currentSlide(index) {
    showSlide(index - 1);
    // 手動操作時は自動スライドを一時停止し、3秒後に再開
    clearInterval(slideInterval);
    startAutoSlide();
}

function startAutoSlide() {
    slideInterval = setInterval(nextSlide, 5000); // 5秒間隔
}

// 自動スライド開始
startAutoSlide();

// スライドショーにマウスが乗った時は自動スライドを停止
const slideshow = document.getElementById('slideshow');
slideshow.addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
});

// マウスが離れた時は自動スライドを再開
slideshow.addEventListener('mouseleave', () => {
    startAutoSlide();
});