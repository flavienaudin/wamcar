
const $plateField = document.querySelector('#pro_vehicle_registrationNumber');
const $sectionRegistration = document.querySelector('#js-registration-information');
const $linkPlate = document.querySelector('#js-update-plate');

$plateField.addEventListener('keyup', () => {
  if ($plateField.value.length > 4) {
      let link = $sectionRegistration.getAttribute('data-fetch-plate');
      let completeLink = link.replace('_plate_number_', $plateField.value);
      $linkPlate.href= completeLink;
  }
});
