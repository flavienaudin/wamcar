/* ===========================================================================
   Banner
   =========================================================================== */

import { hideClass } from '../settings/settings';

const $banner = document.querySelector('.js-banner-full');

/**
 * Utilisation :
 *
 * <div class="js-banner-full-container">
 *   <div class="js-banner-full banner-full is-hidden" aria-hidden="true"></div>
 * </div>
 *
 * Si une image est présente dans .js-banner-full-container, lui ajouter la classe .js-banner-full-image.
 *
 * Ex. : <img src="{{ asset(garage.picture.default) }}" alt="Toyota Villeurbanne" class="js-banner-full-image" width="100%">
 */

if ($banner) {
  const $bannerContainer = document.querySelector('.js-banner-full-container');
  const $bannerImage = document.querySelector('.js-banner-full-image');
  const offset = 40; // Hauteur du file d'ariane qui est fix
  let height;

  const setHeight = () => {
    height = $bannerContainer.clientHeight + offset;
    $banner.style.height = `${height}px`;
    $banner.classList.remove(hideClass);
  };

  if ($bannerImage) {
    $bannerImage.addEventListener('load', () => setHeight());
  } else {
    setHeight();
  }
}
