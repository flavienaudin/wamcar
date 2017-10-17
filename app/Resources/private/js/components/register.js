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
