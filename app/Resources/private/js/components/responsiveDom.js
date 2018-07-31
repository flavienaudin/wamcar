/* ===========================================================================
   ResponsiveDom
   =========================================================================== */

import $ from 'jquery';
import 'ResponsiveDom';
import 'foundation-sites';

const $navigation = '#js-navigation';
const $offCanvasNavigation = '#js-off-canvas-navigation';

$($navigation).responsiveDom({
  appendTo: $offCanvasNavigation,
  mediaQuery: '(max-width: 1023px)',
  callback: (matched) => {
    $($navigation).toggleClass('is-flex');
    if (matched) {
      $($navigation).removeClass('show-for-large');
    }
  }
});

const $movePicturesList = '#js-move-pictures-list';
const $picturesList = '#js-pictures-list';

if ($($movePicturesList).length) {
  $($picturesList).responsiveDom({
    appendTo: $movePicturesList,
    mediaQuery: '(min-width: 768px)'
  });
}

const $moveGaragePicture = '#js-move-garage-picture';
const $garagePicture = '#js-garage-picture';

if ($($garagePicture).length) {
  $($garagePicture).responsiveDom({
    appendTo: $moveGaragePicture,
    mediaQuery: '(min-width: 1024px)'
  });
}

