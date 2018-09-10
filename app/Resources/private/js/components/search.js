/* ===========================================================================
   Seach
   =========================================================================== */

import {Tabs} from 'foundation-sites/js/foundation.tabs';

require('formdata-polyfill');


const $searchTabs = document.getElementById('js-search-tabs');
const $searchForm = $('#js-search-form');

const $information = document.getElementById('js-search-form-container');
const $makeSelect = document.getElementById('search_vehicle_make');
const $modelSelect = document.getElementById('search_vehicle_model');


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

  if ($searchForm.length) {
    $searchForm.on('submit', () => {
      $('#search_vehicle_tab').val($($searchTabs).find('li.is-active').data('tab'));
    });
  }

  if ($makeSelect) {
    let clearSelect = function (select) {
      let selectOptions = select.getElementsByTagName('OPTION');
      while (selectOptions.length > 0) {
        select.remove(selectOptions[0]);
      }

      let defaultOption = document.createElement('option');
      defaultOption.text = 'Modèle du véhicule';
      defaultOption.value = '';
      select.add(defaultOption);
    };

    let dataFetchUrl = $information.getAttribute('data-fetch-url');
    $makeSelect.addEventListener('change', () => {

      clearSelect($modelSelect);

      let filterForm = new FormData();
      filterForm.append('filters[make]', $makeSelect.value);

      fetch(dataFetchUrl, {
        method: 'POST',
        body: filterForm,
        credentials: 'include',
        headers: new Headers({
          'X-Requested-With': 'XMLHttpRequest'
        })
      })
        .then(response => response.json())
        .then((data) => {
          for (let value in data['model']) {
            if (data['model'].hasOwnProperty(value)) {
              let option = document.createElement('option');
              option.text = data['model'][value];
              option.value = value;
              $modelSelect.add(option);
            }
          }
        })
        .catch(err => {
          throw err;
        });
    });
  }

  /*
  $($searchTabs).on('change.zf.tabs', (event, $target) => {
    const url = $($target).data('href');
    return getVehicle(url).then((data) => console.log(JSON.parse(data)));
  });
  */

  /* Intégré mais non utilisé
  const $searchLabel = document.getElementById('js-search-label');
  if ($searchLabel) {
    const fixedClass = 'is-fixed';
    const scrollLimit = 120;

    document.addEventListener('scroll', () => {
      let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
      currentScroll > scrollLimit ? $searchLabel.classList.add(fixedClass) : $searchLabel.classList.remove(fixedClass);
    });
  }*/
}
