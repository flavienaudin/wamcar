/* ===========================================================================
   Import
   =========================================================================== */

// CSS
import '../scss/app.scss';
// JS
import $ from 'jquery';
import { Foundation } from 'foundation-sites/js/foundation.core';
import { Abide } from 'foundation-sites/js/foundation.abide';
import { MediaQuery } from 'foundation-sites/js/foundation.util.mediaQuery';
import offCanvasFixed from './components/offcanvas';



/* ===========================================================================
   jQuery
   =========================================================================== */

$(function() {

});



/* ===========================================================================
   DOM Content Loaded (for pure Javascript function)
   =========================================================================== */

document.addEventListener('DOMContentLoaded', function() {


  /*
     Offcanvas fixed
     ===================================== */

  MediaQuery._init();
  if (!MediaQuery.atLeast('large')) {
    offCanvasFixed();
  }


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
