/* ===========================================================================
   Registration
   =========================================================================== */

let $information = document.querySelector('#js-registration-information');
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
  let clearSelect = function (select) {
    let selectOptions = select.getElementsByTagName('option');
    for (let index in selectOptions) {
      select.remove(selectOptions[index]);
    }
    let defaultOption = document.createElement("option");
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
              clearSelect(selectorToFill);
              for (let value in data[key]) {
                if (data[key].hasOwnProperty(value)) {
                  let option = document.createElement("option");
                  option.text = data[key][value];
                  option.value = value;
                  selectorToFill.add(option);
                }
              }
              selectorToFill.value = filterValues[key];
            }
          }
        })
        .catch(err => {
          throw err;
        });
    });
  });
}


// Todo : auto add images when list full
// let $collectionHolder = document.querySelector('#js-pictures-list');
// let $addPictureButton = document.querySelector('#js-tmp-add-picture');
//
// $collectionHolder.setAttribute('data-index', $collectionHolder.querySelectorAll('input[type="file"]').length);
//
// $addPictureButton.addEventListener('click', () => {
//   let index = parseInt($collectionHolder.getAttribute('data-index'));
//   let newForm = $collectionHolder.getAttribute('data-prototype').replace(/__name__/g, index);
//   $collectionHolder.setAttribute('data-index', index + 1);
//   $collectionHolder.insertAdjacentHTML('beforeend', newForm);
// });
