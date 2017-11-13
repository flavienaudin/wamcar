/* ===========================================================================
   City
   =========================================================================== */

let $zipcodeInputs = document.querySelectorAll('.js-postalCode');

let clearSelect = function (select) {
  let selectOptions = select.getElementsByTagName('option');
  for (let index in selectOptions) {
    select.remove(selectOptions[index]);
  }
};

[...$zipcodeInputs].forEach((input) => {

  input.addEventListener('keyup', () => {
    let zipcodeValue = input.value;
    if (zipcodeValue.length === 5) {
      let dataFetchUrl = input.getAttribute('data-fetch-url');
      let cityInput = document.querySelector('#' + input.getAttribute('data-city-field'));

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
              let option = document.createElement("option");
              option.text = element['place name'];
              option.value = element['place name'];
              if (idx === 0) {
                option.selected = true;
              }
              cityInput.add(option);
            });
          }

        })
        .catch(err => {
          clearSelect(cityInput);
          throw err;
        });
      }
  });
});


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
