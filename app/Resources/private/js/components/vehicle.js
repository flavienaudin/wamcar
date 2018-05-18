/* ===========================================================================
   Vehicle
   =========================================================================== */

const $sectionRegistration = document.getElementById('js-registration-information');
const $linkPlate = document.getElementById('js-update-plate');

if ($linkPlate) {
  const $plateField = document.getElementById($linkPlate.getAttribute('data-platenumber-field'));

  $linkPlate.addEventListener('click', function (event) {
    event.preventDefault();
    if ($plateField.value.trim()) {
      const linkTemplate = $sectionRegistration.getAttribute('data-fetch-plate');
      window.location = linkTemplate.replace('_plate_number_', $plateField.value);
    }
  });
}

const $isUsedInputController = document.getElementsByClassName('js-is-used-controller');
if ($isUsedInputController.length > 0) {
  const $usedFieldsContainer = document.getElementsByClassName('js-used-data-fields');
  $($isUsedInputController).find('input[type=radio]:checked').each((index, radioInput) => {
    if($(radioInput).val().includes('NEW')){
      $($usedFieldsContainer).each((index, element) => {
        $(element).hide(500);
      });
    }
  });
  $($isUsedInputController).find('input[type=radio]').each((index, radioInput) => {
    $(radioInput).change((event) => {
      $($usedFieldsContainer).each((index, element) => {
        $(element).toggle(500);
      });
    });
  });
}
