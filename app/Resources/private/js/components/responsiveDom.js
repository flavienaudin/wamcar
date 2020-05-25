/* ===========================================================================
   ResponsiveDom
   =========================================================================== */

import $ from 'jquery';
import 'ResponsiveDom';
import 'foundation-sites';

/** Nouvelle barre de recherche de conseiller
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
*/



const $advisorsHeaderSearchForm = $('#advisors-header-search-form');
if($advisorsHeaderSearchForm.length) {
  const $navigationHeaderSearch = $('.js-navigation-header-search');

  $($advisorsHeaderSearchForm).responsiveDom({
    appendTo: $navigationHeaderSearch,
    mediaQuery: '(min-width: 1024px)',
    callback: (matched) => {
      if(matched) {
        $('#advisors-header-search').addClass('is-hidden');
      }
    }
  });
}


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
        $garageInfos.toggleClass('is-sticky');
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

const $proProfileImage = $('.js-profile-image');
if($proProfileImage.length){
  $proProfileImage.removeClass('is-invisible');
  $proProfileImage.responsiveDom({
    prependTo: '.js-profile-summary',
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


/*** Personal User Contact/Login Form ***/

const $loginBlock = $('.js-contact-login-block');
if($loginBlock.length){
  $loginBlock.responsiveDom({
    appendTo: '.js-profile-right-column',
    mediaQuery: '(min-width: 1024px)',
    callback: (matched) => {
      $loginBlock.toggleClass('is-sticky');
    }
  });
}

/*** Pro User Contact Form ***/

const $proProfilContactForm = $('.js-pro-profile-contact-form-container');
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

