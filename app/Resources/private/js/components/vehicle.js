
const $plateField = document.querySelector('#pro_vehicle_registrationNumber');
const $sectionRegistration = document.querySelector('#js-registration-information');
const $linkPlate = document.querySelector('#js-update-plate');

$linkPlate.addEventListener('click', function(event) {
  event.preventDefault();
  const linkTemplate = $sectionRegistration.getAttribute('data-fetch-plate');
  window.location = linkTemplate.replace('_plate_number_', $plateField.value);
});
