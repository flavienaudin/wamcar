/* ===========================================================================
   City
   =========================================================================== */

import 'select2';
const fr = require('select2/src/js/select2/i18n/fr');

const $cities = document.querySelectorAll('.js-city-autocomplete');

if ($cities.length) {
  $(document).ready(function() {
    [...$cities].forEach(function (city) {

      const $city = $(city);
      $city.select2({
        language: fr,
        placeholder: 'Ville, CP',
        minimumInputLength: 2,
        maximumSelectionLength: 1,
        multiple: true,
        allowClear: true,
        width: '100%',
        ajax: {
          url: $city.data('autocomplete-url'),
          dataType: 'json'
        },
        templateResult: function (data, container) {
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
        }
      });

      $city.on('select2:select', function (e) {
        let data = e.params.data;
        let selectInput = e.target;
        let latitudeInput = document.getElementById(selectInput.getAttribute('data-latitude-field'));
        let longitudeInput = document.getElementById(selectInput.getAttribute('data-longitude-field'));
        let cityInput = document.getElementById(selectInput.getAttribute('data-city-field'));

        latitudeInput.value = data.latitude;
        longitudeInput.value = data.longitude;
        cityInput.value = data.cityName;
      });
      $city.on('select2:unselect', function (e) {
        let selectInput = e.target;
        let latitudeInput = document.getElementById(selectInput.getAttribute('data-latitude-field'));
        let longitudeInput = document.getElementById(selectInput.getAttribute('data-longitude-field'));
        let cityInput = document.getElementById(selectInput.getAttribute('data-city-field'));

        latitudeInput.value = null;
        longitudeInput.value = null;
        cityInput.value = null;
      });

    });

  });
}
