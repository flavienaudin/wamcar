/* ===========================================================================
   Register
   =========================================================================== */

import $ from 'jquery';
import { Abide } from 'foundation-sites/js/foundation.abide';
import Siema from 'siema';

const $registerForm = document.getElementById('js-register-form');

if ($registerForm) {

  // Init carousel (for step)
  const carousel = new Siema({
    selector: '#js-register-carousel',
    duration: 200,
    easing: 'ease-out',
    perPage: 1,
    startIndex: 0,
    draggable: false,
    multipleDrag: false,
    threshold: 20,
    loop: false,
    onInit: () => {
    },
    onChange: () => {
    },
  });

  // Pagination
  // Add a function that generates pagination to prototype
  // Siema.prototype.addPagination = function() {
  //   for (let i = 0; i < this.innerElements.length; i++) {
  //     const btn = document.createElement('button');
  //     const item = document.createElement('li');
  //     btn.textContent = i;
  //     btn.addEventListener('click', () => this.goTo(i));
  //     item.appendChild(btn);
  //     item.classList.add('register-step-item');
  //     document.getElementById('js-register-step').appendChild(item);
  //   }
  // };

  // carousel.addPagination();

  let step;

  const initAbide = () => {
    const currentSlide = carousel.currentSlide + 1;
    const $currentSlide = document.querySelector(`[data-step="${currentSlide}"`);
    step = new Abide($($currentSlide));
  };

  // Init Abide for current slide only
  initAbide();

  const validStep = () => {
    const isValid = step.validateForm();
    return new Promise((resolve) => isValid && resolve());
  };

  // Navigation
  document.querySelector('.js-carousel-prev').addEventListener('click', () => {
    carousel.prev();
    initAbide();
  });

  document.querySelector('.js-carousel-next').addEventListener('click', () => {
    validStep().then(() => {
      carousel.next();
      initAbide();
    });
  });
}
