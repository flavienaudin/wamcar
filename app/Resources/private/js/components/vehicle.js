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
    if ($plateField.value.trim()) {
      const linkTemplate = $sectionRegistration.getAttribute('data-fetch-plate');
      window.location = linkTemplate.replace('_plate_number_', $plateField.value);
    } else {
      $errorField.classList.add('is-visible');
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

const $suggestedSellersTabs = $('#suggested-sellers-tabs');
if($suggestedSellersTabs) {

  $suggestedSellersTabs.on('change.zf.tabs', function (event) {
    const $inactiveTabs = $(event.currentTarget).children(':not(.is-active)');
    const $activeTabs = $(event.currentTarget).children('.is-active');
    $inactiveTabs.each((index, li) => {
      const targets = $(li).data('targets');
      $(targets).addClass('is-hidden');
    });
    $activeTabs.each((index, li) => {
      const targets = $(li).data('targets');
      $(targets).removeClass('is-hidden');
      const ajaxActivatedAction = $(li).children('a');
      ajaxActivatedAction.each((index, link)=>{
        const $link = $(link);
        const ajaxUrl = $link.data('href');
        if(ajaxUrl !== '') {
          $.ajax({
            url: ajaxUrl,
            method: 'GET'
          }).done(function (success) {
          }).fail(function (jqXHR, textStatus) {
          });
        }
      });
    });

  });

}