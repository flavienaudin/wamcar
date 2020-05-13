/* ===========================================================================
   Register
   =========================================================================== */

import { Abide } from 'foundation-sites/js/foundation.abide';
import $ from 'jquery';
import Siema from 'siema';
import { activeClass, disabledClass, hideClass } from '../settings/settings.js';
import getIndex from './getIndex';
import scrollTo from './scrollTo';
import {initSelect2, killSelect2} from './city';

const $step = document.getElementById('js-step');
export const $registerForm = document.getElementById('js-register-form');

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
    this.nbSteps = document.querySelectorAll('.js-step-navigation').length;
  }

  /**
   * init Abide on current step
   *
   * @memberof Step
   */
  initAbide() {
    this.updateProgressBar();
    this.step = new Abide($(this._getCurrentSlideItem()));
    this.autoHeight();
  }

  /**
   * Set auto height on form change event
   *
   * @returns
   * @memberof Step
   */
  autoHeight() {
    return this.carousel.setAutoHeight();
  }

  setAllTabIndex() {
    const $disableElements = document.getElementById('js-step').querySelectorAll('input, textarea, select, button, a');

    [...$disableElements].forEach((item) => {
      item.setAttribute('tabindex', '-1');
    });

  }

  setActiveTabIndex() {
    const currentSlide = this._getCurrentSlideItem();
    const $inputs = currentSlide.querySelectorAll('[tabindex="-1"]');

    [...$inputs].forEach((item) => {
      item.setAttribute('tabindex', '1');
    });
  }

  setActiveSelect2City() {
    if (this.getCurrentSlide() === 3) {
      initSelect2(document.querySelector('.js-city-autocomplete-manual'));
    } else {
      if (document.querySelector('.select2-container')) {
        killSelect2(document.querySelector('.js-city-autocomplete-manual'));
      }
    }
  }

  /**
   * Init carousel
   *
   * @private
   * @returns {Object} Siema carousel object
   * @memberof Step
   */
  _initCarousel() {
    const $this = this;
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
      onInit: function () {
        this._init = true;
        setTimeout(() => {
          $this.setAllTabIndex();
          $this.setActiveTabIndex();
        }, 2000);
        window.history.pushState(
          { step: 1 },
          document.title + ' - etape-1',
          '#etape-1'
        );
      },
      onChange: function() {
        $this.setActiveSelect2City();
        $this.setAllTabIndex();
        $this.setActiveTabIndex();
        this.setAutoHeight();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
      }
    });

    Siema.prototype.setAutoHeight = function(stopTime) {
      let timeout, i;

      const autoHeight = () => {
        let currentItems, min, max, itemHeightList, height, maxHeight;

        min = this.currentSlide;
        max = min + this.perPage;
        itemHeightList = [];

        for (i = min; i < max; i++) {
          height = parseInt(this.innerElements[i].scrollHeight, 10);
          itemHeightList.push(height);
        }

        maxHeight = Math.max.apply(null, itemHeightList);
        this.sliderFrame.style.height = maxHeight + 'px';
      };

      this.sliderFrame.style.height = '';
      clearTimeout(timeout);
      timeout = setTimeout(autoHeight, 500);

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
    this.carousel.setAutoHeight();
    return new Promise(
      resolve =>
        isValid ? resolve(scrollTo('body')) : scrollTo('.is-invalid-input')
    );
  }

  /**
   * Check all required field if valid
   *
   * @returns boolean
   * @memberof Step
   */
  validBool() {
    return this.step.validateForm();
  }

  /**
   * Go to next step
   *
   * @returns {Promise} Promise object end of carousel.next() method
   * @memberof Step
   */
  next() {
    return new Promise(resolve => resolve(this.carousel.next()));
  }

  /**
   * Go to prev step
   *
   * @returns {Promise} Promise object end of carousel.prev() method
   * @memberof Step
   */
  prev() {
    return new Promise(resolve => resolve(this.carousel.prev()));
  }

  /**
   * Go to step index
   *
   * @param {number} index
   * @returns
   * @memberof Step
   */
  goToSlide(index) {
    return new Promise(resolve => resolve(this.carousel.goTo(index)));
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
    return document.querySelector(
      `[data-step="${currentSlide}"]`
    );
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
   * @param {boolean} [fromNavigation=false]
   * @memberof Step
   */
  updateNavigation(direction, fromNavigation = false) {
    const $activeElement = this._getActiveItemNavigation();
    const $nextElement = $activeElement.nextElementSibling;
    const offSetLeftNextElement = $nextElement && $nextElement.offsetLeft;
    const $prevElement = $activeElement.previousElementSibling;
    const offSetLeftPrevElement = $prevElement && $prevElement.offsetLeft;
    const $stepNavigation = document.getElementById('js-step-navigation');

    if (this.getCurrentSlide() === this.nbSteps) {
      $stepNavigation.classList.add(hideClass);
    } else {
      $stepNavigation.classList.remove(hideClass);
    }

    if (direction === 'next') {
      $activeElement.classList.remove(activeClass);
      $activeElement.classList.add('is-valid');

      this.updateHeaderNavigation($nextElement, offSetLeftNextElement);
    } else {
      $activeElement.classList.remove(activeClass);
      $activeElement.classList.remove('is-valid');

      this.updateHeaderNavigation($prevElement, offSetLeftPrevElement);
    }
  }

  /**
   *
   * @param newElementActive
   * @param offset
   */
  updateHeaderNavigation(newElementActive, offset) {
    newElementActive.classList.remove(disabledClass);
    newElementActive.classList.add(activeClass);

    let $prevs = $(newElementActive).prevAll('.js-step-navigation');
    let stepNum = $prevs.length + 1;

    window.history.pushState(
      { step: stepNum },
      document.title + ' - Etape -' + stepNum,
      '#etape-' + stepNum
    );

    this.updateProgressBar(offset);
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
  const $stepNavigation = document.getElementById(
    'js-register-step-navigation'
  );
  const $prevButton = document.querySelector('.js-carousel-prev');
  const $nextButton = document.querySelector('.js-carousel-next');
  const step = new Step();
  step.initAbide();

  window.addEventListener('popstate', event => {
    let currentSlide = step.getCurrentSlide();
    const index = event.state.step;
    // Prev direction
    if (index < currentSlide) {
      while (index < currentSlide) {
        step.prev().then(() => {
          step.updateNavigation('prev');
          step.initAbide();
          if (step.getCurrentSlide() === 1) {
            hidePrevButton();
          }
        });
        currentSlide = step.getCurrentSlide();
      }
    } else if (currentSlide < index) {
      // Next direction
      step
        .valid()
        .then(() => {
          return step.next();
        })
        .then(() => {
          step.updateNavigation('next');
          step.initAbide();
          showPrevButton();
        });
    }
  });

  if ($registerForm) {
    const registerFormAbide = new Abide($($registerForm));

    $registerForm.addEventListener('change', () => {
      step.autoHeight();
    });

    $registerForm.addEventListener('submit', () => {
      $('#register_submit').addClass('loader-visible');
      setTimeout(() => step.autoHeight());
    });

    $registerForm.addEventListener('pictureAdd', () => step.autoHeight());
    $(window).on('resize', () => step.autoHeight());
  }

  function showPrevButton() {
    document
      .getElementById('js-step-navigation')
      .querySelector('.js-carousel-prev')
      .classList.remove(hideClass);
  }

  function hidePrevButton() {
    document
      .getElementById('js-step-navigation')
      .querySelector('.js-carousel-prev')
      .classList.add(hideClass);
  }

  // Button prev step
  [...document.querySelectorAll('.js-carousel-prev')].forEach(item => {
    item.addEventListener('click', () => {
      step.prev().then(() => {
        step.updateNavigation('prev');
        step.initAbide();
        if (step.getCurrentSlide() === 1) {
          hidePrevButton();
        }
      });
    });
  });

  // Button next step
  $nextButton.addEventListener('click', () => {
    step
      .valid()
      .then(() => {
        return step.next();
      })
      .then(() => {
        step.updateNavigation('next');
        step.initAbide();
        document
          .getElementById('js-step-navigation')
          .querySelector('.js-carousel-prev')
          .classList.remove(hideClass);
      });
  });

  // Step navigation
  [...document.querySelectorAll('.js-step-navigation')].forEach(item => {
    item.addEventListener('click', () => {
      let currentSlide = step.getCurrentSlide();
      const index = getIndex(item);

      // Prev direction
      if (index < currentSlide) {
        while (index < currentSlide) {
          step.prev().then(() => {
            step.updateNavigation('prev');
            step.initAbide();
            if (step.getCurrentSlide() === 1) {
              hidePrevButton();
            }
          });
          currentSlide = step.getCurrentSlide();
        }
      } else if (currentSlide < index) {
        while (currentSlide < index) {
          if (step.validBool()) {
            step.next().then(() => {
              step.updateNavigation('next');
              step.initAbide();
              showPrevButton();
            });

            currentSlide = step.getCurrentSlide();
          } else {
            currentSlide = 999;
          }
        }
      }
    });
  });
}
