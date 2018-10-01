/* ===========================================================================
   Header
   =========================================================================== */

import {$header} from '../settings/settings.js';

const $vehicleForm = document.getElementById('js-vehicle-form');

if ($vehicleForm) {
  const transparentClass = 'is-transparent';
  const scrollLimit = 30;

  document.addEventListener('scroll', () => {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    currentScroll > scrollLimit ? $header.classList.remove(transparentClass) : $header.classList.add(transparentClass);
  });
}

/* Graphic trick : on rend visible les liens de l'entête une fois le JS chargé */
$('.l-navigation.is-hidden').removeClass('is-hidden');


const $headerSearchForm = $('#header-search-form');
if ($headerSearchForm.length) {

  let $submitActor = null;
  const $submitActors = $headerSearchForm.find('button[type=submit]');

  $headerSearchForm.on('submit', () => {
    if (null === $submitActor) {
      // select the first submit if no button explicitly clicked
      $submitActor = $submitActors[0];
    }
    $headerSearchForm.attr('action', $submitActor.data('action'));
    $('#js-header-search').attr('name', $submitActor.data('input-name'));
    return true;
  });

  $headerSearchForm.find('button').on('click', (evt) => {
    $submitActor = $(evt.currentTarget);
  });
}