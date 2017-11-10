/* ===========================================================================
   Import
   =========================================================================== */

// CSS
import '../scss/app.scss';
// JS
import $ from 'jquery';
import { Foundation } from 'foundation-sites/js/foundation.core';
import { Abide } from 'foundation-sites/js/foundation.abide';
import { OffCanvas } from 'foundation-sites/js/foundation.offcanvas';
import { Toggler } from 'foundation-sites/js/foundation.toggler';
import { Reveal } from 'foundation-sites/js/foundation.reveal';
import { Tabs } from 'foundation-sites/js/foundation.tabs';
import { Magellan } from 'foundation-sites/js/foundation.magellan';
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
import {
  activeClass
} from './settings/settings.js';

Reveal.defaults.animationIn = 'slide-in-down';
Reveal.defaults.animationOut = 'fade-out';



/* ===========================================================================
   jQuery
   =========================================================================== */

$(function() {

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
    $(toggle).on('click', function() {
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


});



/* ===========================================================================
   DOM Content Loaded (for pure Javascript function)
   =========================================================================== */

document.addEventListener('DOMContentLoaded', function() {


  /*
     Grid
     ===================================== */

  const $buttonShowGrid = document.getElementById('button-show-grid');

  $buttonShowGrid && $buttonShowGrid.addEventListener('click', () => { grid.show(); });

}, false);



/* ===========================================================================
   Keydown
   =========================================================================== */

const $debugGrid = document.querySelector('[data-toggle="js-debug-grid"]');

document.addEventListener('keydown', function(e) {

  if ($debugGrid) {
    if (e.ctrlKey && e.which === 72) {
      e.preventDefault();
      $debugGrid.click();
    }
  }

});
