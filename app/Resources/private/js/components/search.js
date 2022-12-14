/* ===========================================================================
   Search
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

  // Submit form when changing sorting select
  let $sortingSelect = $('#js-search-sorting-select');
  if ($sortingSelect.length) {
    $sortingSelect.on('change', () => {
      $searchForm.submit();
    });
  }

  // Submit form when using pagination navigation
  let $paginationItems = $('.pagination-item');
  if ($paginationItems.length) {
    $paginationItems.on('click', (e) => {
      e.preventDefault();
      $searchForm.attr('action', $(e.currentTarget).attr('href'));
      $searchForm.submit();
    });
  }

  // Submit form when selecting new checkbox (Search)
  let $searchTypesCheckbox = $('.js-search-type-list li input[type="checkbox"]');
  if ($searchTypesCheckbox.length) {
    $searchTypesCheckbox.on('change', () => {
      $searchForm.submit();
    });
  }
}


const $makeSelect = document.getElementById('search_vehicle_make');
const $modelSelect = document.getElementById('search_vehicle_model');
const $fuelSelect = document.getElementById('search_vehicle_fuel');
if ($makeSelect && $modelSelect) {
  let clearSelect = function (select, defaultValue) {
    let selectOptions = select.getElementsByTagName('OPTION');
    while (selectOptions.length > 0) {
      select.remove(selectOptions[0]);
    }

    let defaultOption = document.createElement('option');
    defaultOption.text = defaultValue;
    defaultOption.value = '';
    select.add(defaultOption);
  };

  const $information = document.getElementById('js-search-form-container');
  if ($information) {
    let dataFetchUrl = $information.getAttribute('data-fetch-url');
    $makeSelect.addEventListener('change', () => {
      clearSelect($modelSelect, 'Mod??le du v??hicule');
      if ($fuelSelect) {
        clearSelect($fuelSelect, 'Energie');
      }

      let filterForm = new FormData();
      filterForm.append('filters[make]', $makeSelect.value);

      let $searchTypeField = $($information).find('input[name="search_vehicle[type][]"]');
      if ($searchTypeField.length > 0) {
        $searchTypeField.each(function (index, $searchType) {
          if ($($searchType).attr('checked') === 'checked') {
            filterForm.append('type[]', $($searchType).val());
          }
        });
      }

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
          if ($fuelSelect) {
            for (let value in data['fuel']) {
              if (data['fuel'].hasOwnProperty(value)) {
                let option = document.createElement('option');
                option.text = data['fuel'][value];
                option.value = value;
                $fuelSelect.add(option);
              }
            }
          }
        })
        .catch(err => {
          throw err;
        });
    });

    if ($fuelSelect) {
      $modelSelect.addEventListener('change', () => {
        clearSelect($fuelSelect, 'Energie');

        let filterForm = new FormData();
        filterForm.append('filters[make]', $makeSelect.value);
        filterForm.append('filters[model]', $modelSelect.value);

        let $searchTypeField = $($information).find('input[name="search_vehicle[type][]"]');
        if ($searchTypeField.length > 0) {
          $searchTypeField.each(function (index, $searchType) {
            if ($($searchType).attr('checked') === 'checked') {
              filterForm.append('type[]', $($searchType).val());
            }
          });
        }

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
            for (let value in data['fuel']) {
              if (data['fuel'].hasOwnProperty(value)) {
                let option = document.createElement('option');
                option.text = data['fuel'][value];
                option.value = value;
                $fuelSelect.add(option);
              }
            }
          })
          .catch(err => {
            throw err;
          });
      });
    }

  }
}

/* Int??gr?? mais non utilis??
const $searchLabel = document.getElementById('js-search-label');
if ($searchLabel) {
  const fixedClass = 'is-fixed';
  const scrollLimit = 120;
  document.addEventListener('scroll', () => {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    currentScroll > scrollLimit ? $searchLabel.classList.add(fixedClass) : $searchLabel.classList.remove(fixedClass);
  });
}*/
