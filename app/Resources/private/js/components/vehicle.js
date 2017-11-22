
const $plateField = document.getElementById('pro_vehicle_registrationNumber');
const $sectionRegistration = document.getElementById('js-registration-information');
const $linkPlate = document.getElementById('js-update-plate');

if ($linkPlate) {

  $linkPlate.addEventListener('click', function(event) {
    event.preventDefault();
    const linkTemplate = $sectionRegistration.getAttribute('data-fetch-plate');
    window.location = linkTemplate.replace('_plate_number_', $plateField.value);
  });

}
