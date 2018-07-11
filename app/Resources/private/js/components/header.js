/* ===========================================================================
   Header
   =========================================================================== */

import { $header } from '../settings/settings.js';

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