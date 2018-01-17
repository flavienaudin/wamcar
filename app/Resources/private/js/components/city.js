/* ===========================================================================
   City
   =========================================================================== */

let $zipcodeInputs = document.querySelectorAll('.js-postalCode');
let $cityInputs = document.querySelectorAll('.js-complete-coordinate');

let clearSelect = function (select) {
  let selectOptions = select.getElementsByTagName('option');
  for (let index in selectOptions) {
    select.remove(selectOptions[index]);
  }
};

[...$zipcodeInputs].forEach((input) => {

  input.addEventListener('keyup', () => {
    let zipcodeValue = input.value;
    let dataFetchUrl = input.getAttribute('data-fetch-url');
    let cityInput = document.getElementById(input.getAttribute('data-city-field'));

    if (zipcodeValue.length !== 5) {
      clearSelect(cityInput);
      return;
    }

    let filterForm = new FormData();
    filterForm.append('zipcode', zipcodeValue);

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
        clearSelect(cityInput);

        if (data['success'] === true) {
          data['result_raw']['places'].forEach(function (element, idx) {
            let option = document.createElement('option');
            option.text = element['place name'];
            option.value = element['place name'];
            option.setAttribute('data-latitude', element['latitude']);
            option.setAttribute('data-longitude', element['longitude']);
            if (idx === 0) {
              option.selected = true;
            }
            cityInput.add(option);
          });
          let event = new Event('change');
          cityInput.dispatchEvent(event);
        } else {
          let option = document.createElement('option');
          option.text = 'Aucune ville n\'a été trouvée';
          option.value = '';
          cityInput.add(option);
        }

      })
      .catch(err => {
        clearSelect(cityInput);
        throw err;
      });
  });
});

[...$cityInputs].forEach((input) => {
  input.addEventListener('change', () => {
    let latitudeInput = document.getElementById(input.getAttribute('data-latitude-field'));
    let longitudeInput = document.getElementById(input.getAttribute('data-longitude-field'));
    let optionSelected =  input.options[input.selectedIndex];
    latitudeInput.value = optionSelected.getAttribute('data-latitude');
    longitudeInput.value = optionSelected.getAttribute('data-longitude');
  });
});
