/* ===========================================================================
   Import
   =========================================================================== */

// CSS
import '../scss/app.scss';
// JS
import $ from 'jquery';
import {Abide} from 'foundation-sites/js/foundation.abide';
import {OffCanvas} from 'foundation-sites/js/foundation.offcanvas';
import {Toggler} from 'foundation-sites/js/foundation.toggler';
import {Reveal} from 'foundation-sites/js/foundation.reveal';
import {Tabs} from 'foundation-sites/js/foundation.tabs';
import {Magellan} from 'foundation-sites/js/foundation.magellan';
import {Tooltip} from 'foundation-sites/js/foundation.tooltip';
import {DropdownMenu} from 'foundation-sites/js/foundation.dropdownMenu';
import './components/responsiveDom';
import './components/search';
import './components/header';
import './components/select';
import './components/step';
import './components/file';
import './components/registration';
import './components/like';
import './components/banner';
import './components/carousel';
import './components/datepicker';
import './components/city';
import './components/conversation';
import './components/vehicle';
import './components/star';
import './components/radio';
import './components/project';
import './components/avatar';
import scrollTo from './components/scrollTo';
import {activeClass} from './settings/settings.js';

Reveal.defaults.animationIn = 'slide-in-down';
Reveal.defaults.animationOut = 'fade-out';


/* ===========================================================================
   jQuery
   =========================================================================== */

$(function () {

  /* Off Canvas */

  const $offCanvas = $('[data-off-canvas]');

  $offCanvas.each((index, offcanvas) => {
    return new OffCanvas($(offcanvas));
  });

  /* Reveal */

  const $reveal = $('[data-reveal]');

  $reveal.each((index, reveal) => {
    return new Reveal($(reveal));
  });

  /* Abide */

  const $abide = $('[data-abide]');

  $abide.each((inddex, abide) => {
    return new Abide($(abide));
  });

  /* Toogle */

  const $toggles = $('[data-toggle]');

  $toggles.each((index, toggle) => {
    $(toggle).on('click', function () {
      $(this).toggleClass(activeClass);
    });
  });

  const $togglers = $('[data-toggler]');

  if ($togglers) {
    $togglers.each((index, toggler) => {
      return new Toggler($(toggler));
    });
  }

  /* Tabs */

  const $tabs = $('[data-tabs]');

  if ($tabs) {
    $tabs.each((index, tabs) => {
      return new Tabs($(tabs));
    });
  }

  /* Tabs */

  const $magellan = $('[data-magellan]');

  if ($magellan) {
    const options = {
      offset: 100
    };

    $magellan.each((index, magellan) => {
      return new Magellan($(magellan), options);
    });
  }

  /* Tooltips */

  const $tooltip = $('[data-tooltip]');

  if ($tooltip) {
    const options = {};

    $tooltip.each((index, tooltip) => {
      return new Tooltip($(tooltip), options);
    });
  }

  /* Dropdown menu */

  const $dropdownMenu = $('[data-dropdown-menu]');
  if($dropdownMenu){
    const options = {
      'data-disable-hover' : true,
      'data-autoclose' : false
    };

    $dropdownMenu.each((index, dropdown) => {
      new DropdownMenu($(dropdown), options);
    });
  }

  /* Form invalid */

  const $form = $('#js-register-form');

  $form.on('forminvalid.zf.abide', function (e) {
    let invalidFields = $(this).find('[data-invalid]');

    if (invalidFields) {
      let scrollTo = $('#' + invalidFields[0].id).offset().top - 280;

      $('html, body').animate({
        scrollTop: scrollTo
      }, 400);
    }
  });

  const $registerSimpleForm = $('#js-register-simple-form');
  if ($registerSimpleForm) {
    $registerSimpleForm.on('formvalid.zf.abide', () => {
      $('#register_submit').addClass('loader-visible');
    });

  }

  $('#message_send').on('click', function () {
    $(this).addClass('loader-visible');
  });
});


/* ===========================================================================
   DOM Content Loaded (for pure Javascript function)
   =========================================================================== */

document.addEventListener('DOMContentLoaded', function () {


  /*
     Grid
     ===================================== */

  const $buttonShowGrid = document.getElementById('button-show-grid');

  $buttonShowGrid && $buttonShowGrid.addEventListener('click', () => {
    grid.show();
  });


  /*
     ScrollTo
     ===================================== */

  const $scrollTo = document.querySelectorAll('[data-scroll-to]');

  [...$scrollTo].forEach((item) => {
    item.addEventListener('click', () => {
      const $target = item.getAttribute('data-scroll-to');

      setTimeout(() => scrollTo($target), 100);
    });
  });

}, false);


/* ===========================================================================
   Keydown
   =========================================================================== */

const $debugGrid = document.querySelector('[data-toggle="js-debug-grid"]');

document.addEventListener('keydown', function (e) {

  if ($debugGrid) {
    if (e.ctrlKey && e.which === 72) {
      e.preventDefault();
      $debugGrid.click();
    }
  }

});
