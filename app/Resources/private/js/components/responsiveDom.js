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

const $moveVehicleActions = '.js-vehicle-actions';

if ($($moveVehicleActions).length) {
  $($moveVehicleActions).responsiveDom({
    appendTo: 'body',
    mediaQuery: '(max-width: 1023px)'
  });
}

const $proProfilImage = $('.js-pro-profil-image');
if($proProfilImage.length){
  $proProfilImage.removeClass('is-invisible');
  $proProfilImage.responsiveDom({
    prependTo: '.js-pro-profil-summary',
    mediaQuery: '(min-width: 640px)'
  });
}


const $proProfilContactForm = $('.js-pro-profil-contact-form-container');
if($proProfilContactForm.length){
  $proProfilContactForm.responsiveDom({
    prependTo: '.js-pro-profil-right-column',
    mediaQuery: '(min-width: 640px)'
  });
}


/*** HOMEPAGE PEEXEO ***/

const $baselineTitle = $('#baseline');
if($baselineTitle.length){

  $baselineTitle.responsiveDom({
    prependTo: '.top-section > .row',
    mediaQuery: '(max-width: 639px)',
    callback: (matched) => {
      if(matched){
        $baselineTitle.addClass('column w-100');
      }else{
        $baselineTitle.removeClass('column w-100');
      }
    }
  });
}

