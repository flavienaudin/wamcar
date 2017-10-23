/* ===========================================================================
   Search
   =========================================================================== */

import {
  $header
} from '../settings/settings.js';

const $searchForm = document.getElementById('js-search-form');

if ($searchForm) {
  const transparentClass = 'is-transparent';
  const scrollLimit = 30;

  document.addEventListener('scroll', () => {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    currentScroll > scrollLimit ? $header.classList.remove(transparentClass) : $header.classList.add(transparentClass);
  });
}
