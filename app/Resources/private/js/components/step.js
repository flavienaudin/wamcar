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
    this._init && this.updateProgressBar();
    this.step = new Abide($(this._getCurrentSlideItem()));
  }

  /**
   * Init carousel
   *
   * @private
   * @returns {Object} Siema carousel object
   * @memberof Step
   */
  _initCarousel() {
    const carousel = new Siema({
      selector: '#js-step',
      duration: 600,
      easing: 'ease-out',
      perPage: 1,
      startIndex: 0,
      draggable: false,
      multipleDrag: false,
      threshold: 20,
      loop: false,
      onInit: () => {
        this._init = true;
      },
      onChange: function() {
        this.setAutoHeight();
      }
    });

    Siema.prototype.setAutoHeight = function(stopTime) {
      let that, timeout;
      that = this;

      function autoHeight() {
        let currentItems, min, max, itemHeightList, height, maxHeight, i;

        min = that.currentSlide;
        max =  min + that.perPage;
        itemHeightList = [];

        for (i = min; i < max; i++) {
          height = parseInt(that.innerElements[i].scrollHeight, 10);
          itemHeightList.push(height);
        }

        maxHeight = Math.max.apply(null, itemHeightList);
        that.sliderFrame.style.height = maxHeight + 'px';
      }

      window.addEventListener('resize', function() {
        that.sliderFrame.style.height = '';
        clearTimeout(timeout);
        timeout = setTimeout(autoHeight, 500);
      });

      autoHeight();
    };

    return carousel;
  }

  /**
   * Check all required field if valid
   *
   * @returns {Promise} Promise object resolve if step is valid
   * @memberof Step
   */
  valid() {
    const isValid = this.step.validateForm();
    return new Promise((resolve) => isValid && resolve(this.carousel.setAutoHeight()));
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
    const $currentSlide = document.querySelector(`[data-step="${currentSlide}"]`);
    return $currentSlide;
  }

  /**
   * Return the active item in step navigation
   *
   * @private
   * @returns {DOM Element}
   * @memberof Step
   */
  _getActiveItemNavigation() {
    return document.querySelector('.js-step-navigation.is-active');
  }

  /**
   * Update active step in navigation
   *
   * @param {string} direction (next or prev)
   * @memberof Step
   */
  updateNavigation(direction) {
    const $activeElement = this._getActiveItemNavigation();
    const $nextElement = $activeElement.nextElementSibling;
    const offSetLeftNextElement = $nextElement.offsetLeft;
    const $prevElement = $activeElement.previousElementSibling;
    const offSetLeftPrevElement = $prevElement && $prevElement.offsetLeft;

    if (direction === 'next') {
      $activeElement.classList.remove(activeClass);
      $nextElement.classList.remove(disabledClass);
      $nextElement.classList.add(activeClass);
      this.updateProgressBar(offSetLeftNextElement);
    } else {
      $activeElement.classList.remove(activeClass);
      $activeElement.classList.add(disabledClass);
      $prevElement.classList.add(activeClass);
      this.updateProgressBar(offSetLeftPrevElement);
    }
  }

  /**
   * Update progress bar on step change
   *
   * @memberof Step
   */
  updateProgressBar() {
    const $progressBar = document.getElementById('js-step-progress-bar');
    const value = this._getActiveItemNavigation().offsetLeft;
    $progressBar.style.width = `${value}px`;
  }

}

/* Running */

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
  [...document.querySelectorAll('.js-step-navigation')].forEach((item) => {
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
