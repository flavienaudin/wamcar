/* ===========================================================================
   Registration
   =========================================================================== */

let $identification = document.querySelector('#js-registration-identification');
let $identificationSelectList = document.querySelectorAll('#js-registration-identification select');

let dataFetchUrl = $identification.getAttribute('data-fetch-url');
let filterForm = new FormData();
let filterValues = {};

let filterAdd = function (dataType, value) {
  filterForm.append('filters[TYPE]'.replace('TYPE', dataType), value);
  filterValues[dataType] = value;
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

[...$identificationSelectList].forEach((select) => {

  let dataType = select.getAttribute('data-type');
  if (dataType === null) {
    return;
  }
  filterAdd(dataType, select.value);

  select.addEventListener('change', () => {
    filterAdd(select.getAttribute('data-type'), select.value);
    fetch(dataFetchUrl, {
      method: 'POST',
      body: filterForm
    })
      .then(response => response.json())
      .then((data) => {
        console.log(data);
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
