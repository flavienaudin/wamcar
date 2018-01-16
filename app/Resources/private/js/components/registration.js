/* ===========================================================================
   Registration
   =========================================================================== */

import {$registerForm} from './step';

let $information = document.getElementById('js-registration-information');
let $informationSelectList = document.querySelectorAll('#js-registration-information select');

if ($information != null) {
  let dataFetchUrl = $information.getAttribute('data-fetch-url');
  let filterForm = new FormData();
  let filterValues = {};

  let filterAdd = function (dataType, value) {
    filterValues[dataType] = value;
    filterForm.append('filters[TYPE]'.replace('TYPE', dataType), value);
  };
  let filterRemove = function (dataType) {
    filterValues[dataType] = null;
    filterForm.delete('filters[TYPE]'.replace('TYPE', dataType));
    clearSelect(document.querySelector('select[data-type="%type%"]'.replace('%type%', dataType)));
  };
  let clearSelect = function (select, doAddEmpty) {
    let selectOptions = select.getElementsByTagName('option');
    for (let index in selectOptions) {
      select.remove(selectOptions[index]);
    }

    if (!doAddEmpty) {
      return;
    }

    let defaultOption = document.createElement('option');
    defaultOption.text = '';
    select.add(defaultOption);
  };

  [...$informationSelectList].forEach((select) => {

    let dataType = select.getAttribute('data-type');
    if (dataType === null) {
      return;
    }
    filterAdd(dataType, select.value);

    select.addEventListener('change', () => {
      let selectDataType = select.getAttribute('data-type') + '';

      filterAdd(selectDataType, select.value);
      switch (selectDataType) {
      case 'make':
        filterRemove('model', null);
      case 'model': // This fallthrough is on purpose since "make" selection should also clean "model" sub fields
        filterRemove('modelVersion', null);
        filterRemove('engine', null);
        filterRemove('fuel', null);
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
          for (let key in data) {
            if (data.hasOwnProperty(key)) {
              let selectorToFill = document.querySelector('select[data-type="%type%"]'.replace('%type%', key));
              let hasMultipleOptions = Object.keys(data[key]).length > 1;
              clearSelect(selectorToFill, hasMultipleOptions);
              for (let value in data[key]) {
                if (data[key].hasOwnProperty(value)) {
                  let option = document.createElement('option');
                  option.text = data[key][value];
                  option.value = value;
                  selectorToFill.add(option);
                }
              }
              selectorToFill.value = filterValues[key];
              if (!hasMultipleOptions) {
                selectorToFill.selectedIndex = 0;
              }
            }
          }
        })
        .catch(err => {
          throw err;
        });
    });
  });
}

let $collectionHolder = document.getElementById('js-pictures-list');
let $inputCollectionHolder = document.querySelectorAll('#js-pictures-list input[type="file"]');

if ($collectionHolder && $inputCollectionHolder) {

  $collectionHolder.setAttribute('data-index', $collectionHolder.querySelectorAll('input[type="file"]').length);

  function refreshVar() {
    $collectionHolder = document.getElementById('js-pictures-list');
    $inputCollectionHolder = document.querySelectorAll('#js-pictures-list input[type="file"]');
    $collectionHolder.setAttribute('data-index', $collectionHolder.querySelectorAll('input[type="file"]').length);
  }

  function addChangeEvent() {
    [...$inputCollectionHolder].forEach((input) => {
      input.addEventListener('click', () => {
        addScriptChange();
      });
    });
  }

  function addPictureForm() {
    let index = parseInt($collectionHolder.getAttribute('data-index'));
    let newForm = $collectionHolder.getAttribute('data-prototype').replace(/__name__/g, index);
    $collectionHolder.setAttribute('data-index', index + 1);
    $collectionHolder.insertAdjacentHTML('beforeend', newForm);

    const event = new Event('pictureAdd');
    $registerForm.dispatchEvent(event);
    refreshVar();
    addChangeEvent();
  }

  function addScriptChange() {
    let nbEmpty = 0;
    [...$inputCollectionHolder].forEach((inputChange) => {
      if (inputChange.value === '') {
        nbEmpty++;
      }
    });

    if (nbEmpty === 0) {
      addPictureForm();
    }
  }

  addChangeEvent();
}
