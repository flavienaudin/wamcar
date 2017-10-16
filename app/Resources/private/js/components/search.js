/* ===========================================================================
   Search
   =========================================================================== */

import {
  $header
} from '../settings/settings.js';

const $searchForm = document.getElementById('js-search-form');

if ($searchForm) {

  /* Scroll */

  const headerHeight = $header.clientHeight;
  const transparentClass = 'is-transparent';

  document.addEventListener('scroll', () => {
    const currentScroll = document.documentElement.scrollTop;
    currentScroll > headerHeight/2 ? $header.classList.remove(transparentClass) : $header.classList.add(transparentClass);
  });

}
