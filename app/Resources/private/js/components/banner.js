/* ===========================================================================
   Banner
   =========================================================================== */

import { hideClass } from '../settings/settings';

const $banner = document.querySelector('.js-banner-full');

if ($banner) {
  const $bannerContainer = document.querySelector('.js-banner-full-container');
  const $bannerImage = document.querySelector('.js-banner-full-image');
  let height;

  const setHeight = () => {
    height = $bannerContainer.clientHeight;
    $banner.style.height = `${height + 40}px`;
    $banner.classList.remove(hideClass);
  };

  if ($bannerImage) {
    $bannerImage.addEventListener('load', () => setHeight());
  } else {
    setHeight();
  }
}
