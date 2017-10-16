/* ===========================================================================
   Search
   =========================================================================== */

import {
  $header
} from '../settings/settings.js';

const $searchForm = document.getElementById('js-search-form');

if ($searchForm) {
  const transparentClass = 'is-transparent';

  document.addEventListener('scroll', () => {
    const currentScroll = document.documentElement.scrollTop;
    currentScroll > 30 ? $header.classList.remove(transparentClass) : $header.classList.add(transparentClass);
  });
}
