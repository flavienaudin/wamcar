/* ===========================================================================
   Registration
   =========================================================================== */

import {$registerForm} from './step';
require('formdata-polyfill');

export let clearSelectOptions = function (select, doAddEmpty) {
  $(select).parents('#js-register-form, form[data-abide]').foundation('removeErrorClasses', $(select));

  $(select).find('option').remove();

  if (!doAddEmpty) {
    return;
  }

  let defaultOption = document.createElement('option');
  if(select.hasAttribute('emptyOptionText')){
    defaultOption.text = select.getAttribute('emptyOptionText');
  }else {
    defaultOption.text = '';
  }
  defaultOption.value = '';
  defaultOption.setAttribute('disabled','disabled');
  defaultOption.setAttribute('selected','selected');
  select.add(defaultOption);
};

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
    clearSelectOptions(document.querySelector('select[data-type="%type%"]'.replace('%type%', dataType)), true);
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
        filterRemove('engine', null);
        filterRemove('fuel', null);
      case 'model': // This fallthrough is on purpose since "make" selection should also clean "model" sub fields
        filterRemove('engine', null);
        filterRemove('fuel', null);
      case 'engine': // This fallthrough is on purpose since "model" selection should also clean "engine" sub fields
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
              clearSelectOptions(selectorToFill, hasMultipleOptions);

              for (let value in data[key]) {
                if (data[key].hasOwnProperty(value)) {
                  let option = document.createElement('option');
                  option.text = data[key][value];
                  option.value = value;
                  selectorToFill.add(option);
                }
              }
              selectorToFill.value = filterValues[key];
              selectorToFill.selectedIndex = 0;
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
