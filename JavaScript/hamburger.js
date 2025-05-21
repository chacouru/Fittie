'use strict';

const menuButton = document.querySelector('.menu_button');
const menuOverlay = document.querySelector('.menu_overlay');

menuButton.addEventListener('click', () => {
    menuOverlay.classList.toggle('active');
    menuButton.classList.toggle('is-active');
});