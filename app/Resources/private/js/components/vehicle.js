/* ===========================================================================
   Vehicle
   =========================================================================== */

import 'select2/dist/js/select2.full';
const fr = require('select2/src/js/select2/i18n/fr');

const $sectionRegistration = document.getElementById('js-registration-information');
const $linkPlate = document.getElementById('js-update-plate');

if ($linkPlate) {
  const $plateField = document.getElementById($linkPlate.getAttribute('data-platenumber-field'));

  $linkPlate.addEventListener('click', function (event) {
    event.preventDefault();
    if($plateField.value.trim()) {
      const linkTemplate = $sectionRegistration.getAttribute('data-fetch-plate');
      window.location = linkTemplate.replace('_plate_number_', $plateField.value);
    }
  });
}




const $makeSelectors = document.querySelectorAll('.js-make-autocomplete');

if ($makeSelectors.length) {
  $(document).ready(function() {
    [...$makeSelectors].forEach(function (make) {
      const $make = $(make);
      $make.select2({
        language: fr

        /*templateResult: function (data, container) {
          if (data.cityName) {
            $(container).attr('data-cityname', data.cityName);
          }
          if (data.latitude) {
            $(container).attr('data-latitude', data.latitude);
          }
          if (data.longitude) {
            $(container).attr('data-longitude', data.longitude);
          }
          return data.text;
        },
        templateSelection: function (data, container) {
          if (data.cityName) {
            $(data.element).attr('data-cityname', data.cityName);
          }
          if (data.latitude) {
            $(data.element).attr('data-latitude', data.latitude);
          }
          if (data.longitude) {
            $(data.element).attr('data-longitude', data.longitude);
          }
          return data.text;
        }*/
      });

      //$make.addClass('no-margin');

      /*$make.on('select2:select', function (e) {

      });
      $make.on('select2:unselect', function (e) {

      });*/

    });

  });
}
