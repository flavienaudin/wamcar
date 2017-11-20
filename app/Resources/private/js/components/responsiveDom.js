/* ===========================================================================
   ResponsiveDom
   =========================================================================== */

import $ from 'jquery';
import responsiveDom from 'ResponsiveDom';

const $navigation = '#js-navigation';
const $offCanvasNavigation = '#js-off-canvas-navigation';

$($navigation).responsiveDom({
  appendTo: $offCanvasNavigation,
  mediaQuery: '(max-width: 1023px)',
  callback: (matched) => {
    matched && $($navigation).toggleClass('show-for-large');
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

const $moveVehicleAside = '#js-move-vehicle-aside';
const $vehicleAside = '#js-vehicle-aside';

if ($($vehicleAside).length) {
  $($vehicleAside).responsiveDom({
    appendTo: $moveVehicleAside,
    mediaQuery: '(min-width: 1024px)'
  });
}
