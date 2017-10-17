/* ===========================================================================
   Register
   =========================================================================== */

import $ from 'jquery';
import { Abide } from 'foundation-sites/js/foundation.abide';
import Siema from 'siema';
import getIndex from './getIndex';
import {
  activeClass,
  disabledClass
} from '../settings/settings.js';

const $step = document.getElementById('js-step');

/**
 *
 *
 * @class Step
 */
class Step {

  /**
   * Creates an instance of Register.
   * @memberof Step
   */
  constructor() {
    this.carousel = this._initCarousel();
  }

  /**
   * init Abide on current step
   *
   * @memberof Step
   */
  initAbide() {
    const step = new Abide($(this._getCurrentSlideItem()));
    this.step = step;
  }

  /**
   * Init carousel
   *
   * @private
   * @returns {Object} Siema carousel object
   * @memberof Step
   */
  _initCarousel() {
    return new Siema({
      selector: '#js-step',
      duration: 200,
      easing: 'ease-out',
      perPage: 1,
      startIndex: 0,
      draggable: false,
      multipleDrag: false,
      threshold: 20,
      loop: false,
      onInit: () => {},
      onChange: () => {}
    });
  }

  /**
   * Check all required field if valid
   *
   * @returns {Promise} Promise object resolve if step is valid
   * @memberof Step
   */
  valid() {
    const isValid = this.step.validateForm();
    return new Promise((resolve) => isValid && resolve());
  }

  /**
   * Go to next step
   *
   * @returns {Promise} Promise object end of carousel.next() method
   * @memberof Step
   */
  next() {
    return new Promise((resolve) => resolve(this.carousel.next()));
  }

  /**
   * Go to prev step
   *
   * @returns {Promise} Promise object end of carousel.prev() method
   * @memberof Step
   */
  prev() {
    return new Promise((resolve) => resolve(this.carousel.prev()));
  }

  /**
   * Return the current step number
   *
   * @returns {string}
   * @memberof Step
   */
  getCurrentSlide() {
    return this.carousel.currentSlide + 1;
  }

  /**
   * Get current step item
   *
   * @private
   * @returns {DOM element}
   * @memberof Step
   */
  _getCurrentSlideItem() {
    const currentSlide = this.carousel.currentSlide + 1;
    const $currentSlide = document.querySelector(`[data-step="${currentSlide}"`);
    return $currentSlide;
  }

  /**
   * Update active step in navigation
   *
   * @param {string} direction (next or prev)
   * @memberof Step
   */
  updateNavigation(direction) {
    const $activeElement = document.querySelector('.js-step-navigation.is-active');
    const $nextElement = $activeElement.nextElementSibling;
    const $prevElement = $activeElement.previousElementSibling;

    if (direction === 'next') {
      $activeElement.classList.remove(activeClass);
      $nextElement.classList.remove(disabledClass);
      $nextElement.classList.add(activeClass);
    } else {
      $activeElement.classList.remove(activeClass);
      $activeElement.classList.add(disabledClass);
      $prevElement.classList.add(activeClass);
    }
  }

}

if ($step) {

  const $stepNavigation = document.getElementById('js-register-step-navigation');
  const step = new Step();
  step.initAbide();

  // Button prev step
  document.querySelector('.js-carousel-prev').addEventListener('click', () => {
    step.prev().then(() => {
      step.updateNavigation('prev');
      step.initAbide();
    });
  });

  // Button next step
  document.querySelector('.js-carousel-next').addEventListener('click', () => {
    step.valid().then(() => {
      return step.next();
    }).then(() => {
      step.updateNavigation('next');
      step.initAbide();
    });
  });

  // Step navigation
  [...$stepNavigation.querySelectorAll('a')].forEach((item) => {
    item.addEventListener('click', () => {
      const currentSlide = step.getCurrentSlide();
      const index = getIndex(item);

      if (index < currentSlide) {
        step.prev().then(() => {
          step.updateNavigation('prev');
          step.initAbide();
        });
      }
    });
  });
}
