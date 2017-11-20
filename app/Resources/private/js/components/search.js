/* ===========================================================================
   Seach
   =========================================================================== */

import { Tabs } from 'foundation-sites/js/foundation.tabs';

const $searchTabs = document.getElementById('js-search-tabs');

/**
 * Get vehicle
 *
 * @param {String} url
 */
const getVehicle = async (url) => {
  try {
    const result = await fetch(url);
    return result.text();
  } catch (error) {
    return console.log(`Erreur: ${error}`);
  }
};

if ($searchTabs) {

  const searchTabs = new Tabs($($searchTabs));

  $($searchTabs).on('change.zf.tabs', (event, $target) => {
    const url = $($target).data('href');
    return getVehicle(url).then((data) => console.log(JSON.parse(data)));
  });

  const $searchLabel = document.getElementById('js-search-label');
  const fixedClass = 'is-fixed';
  const scrollLimit = 120;

  if ($searchLabel) {
    document.addEventListener('scroll', () => {
      let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
      currentScroll > scrollLimit ? $searchLabel.classList.add(fixedClass) : $searchLabel.classList.remove(fixedClass);
    });
  }
}
