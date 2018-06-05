/* ===========================================================================
   Vehicle
   =========================================================================== */

const $sectionRegistration = document.getElementById('js-registration-information');
const $linkPlate = document.getElementById('js-update-plate');

if ($linkPlate) {
  const $plateField = document.getElementById($linkPlate.getAttribute('data-platenumber-field'));
  const $errorField = document.getElementById($linkPlate.getAttribute('data-platenumber-error'));

  $linkPlate.addEventListener('click', function (event) {
    event.preventDefault();
    if($plateField.value.trim()) {
      const linkTemplate = $sectionRegistration.getAttribute('data-fetch-plate');
      window.location = linkTemplate.replace('_plate_number_', $plateField.value);
    } else {
      $errorField.classList.add('is-visible');
    }
  });
}
