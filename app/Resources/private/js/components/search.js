/* ===========================================================================
   Seach
   =========================================================================== */

require('formdata-polyfill');


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

const $searchForm = $('#js-search-form');
if ($searchForm.length) {
  // Mise à jour de la valeur du champ caché pour la sélection de l'onglet
  const $searchTabs = $('#js-search-tabs');
  if ($searchTabs.length) {
    $searchForm.on('submit', () => {
      $('#search_vehicle_tab').val($searchTabs.find('li.is-active').data('tab'));
    });

    // Suppression du filtre par soumission de formulaire
    let $filterLinks = $('.search-filter');
    if ($filterLinks.length) {
      $filterLinks.each((index, elt) => {
        $(elt).find('a').on('click', function (e) {
          e.preventDefault();
          let toResetFieldArray = ($(this).data('field-id')).split(',');
          toResetFieldArray.forEach((element) => {
            if (element.indexOf('=') === -1) {
              $('#' + element).val(null);
            } else {
              let field_value = element.split('=');
              $('#' + field_value[0]).val(field_value[1]);
            }
          });
          $searchForm.submit();
        });
      });
    }
  }
}



const $makeSelect = document.getElementById('search_vehicle_make');
const $modelSelect = document.getElementById('search_vehicle_model');
if ($makeSelect && $modelSelect) {
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

  const $information = document.getElementById('js-search-form-container');
  if ($information) {
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
}

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
