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

const $garageAside = $('#js-garage-aside');
if ($garageAside.length) {
  const $garageLogo = $('#js-garage-logo');
  if ($garageLogo.length) {
    $garageLogo.responsiveDom({
      appendTo: $garageAside,
      mediaQuery: '(min-width: 1024px)'
    });
  }

  const $garageInfos = $('#js-garage-info');
  if ($garageInfos.length) {
    $garageInfos.responsiveDom({
      appendTo: $garageAside,
      mediaQuery: '(min-width: 1024px)',
      callback: (matched) => {
        $(element).toggleClass('is-sticky');
      }
    });
  }
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

/*** PRO VEHICLE PAGE (PEEXEO) ***/

const vehicleAsideMediaQuery = '(min-width: 1024px)';
const $asideContentPrice = $('#js-aside-content-price');
if($asideContentPrice.length){
  $asideContentPrice.responsiveDom({
    appendTo: '#js-vehicle-aside',
    mediaQuery: vehicleAsideMediaQuery,
    /*callback: (matched) => {}*/
  });
}
const $asideContentSelledBy = $('#js-aside-content-selledby');
if($asideContentSelledBy.length){
  $asideContentSelledBy.responsiveDom({
    appendTo: '#js-vehicle-aside',
    mediaQuery: vehicleAsideMediaQuery,
    /*callback: (matched) => {}*/
  });
}
const $asideContentSellerItem = $('#js-aside-content-seller-item');
if($asideContentSellerItem.length){
  $asideContentSellerItem.responsiveDom({
    appendTo: '#js-vehicle-aside',
    mediaQuery: vehicleAsideMediaQuery,
    /*callback: (matched) => {}*/
  });
}

/*** Pro User Contact Form ***/

const $proProfilContactForm = $('.js-pro-profil-contact-form-container');
if($proProfilContactForm.length){
  $proProfilContactForm.each((index, element) => {
    const breakpoint = $(element).data('responsivedom-mediaquerysize');
    let options = {
      mediaQuery: '(min-width: ' + breakpoint + 'px)',
      callback: (matched) => {
        $(element).toggleClass('is-sticky block-light-shadow');
      }
    };
    const destination = $(element).data('responsivedom-destination');
    const appendTo = $(element).data('responsivedom-appendto');
    if(appendTo){
      options.appendTo = destination;
    }else{
      options.prependTo = destination;
    }
    $(element).responsiveDom(options);
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

