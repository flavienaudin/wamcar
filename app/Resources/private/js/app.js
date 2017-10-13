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
import './components/responsiveDom';
import {
  activeClass
} from './settings/settings.js';



/* ===========================================================================
   jQuery
   =========================================================================== */

$(function() {

  /* Off Canvas */

  const $offCanvas = $('[data-off-canvas]');

  $offCanvas.each((index, offcanvas) => {
    return new OffCanvas($(offcanvas));
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
