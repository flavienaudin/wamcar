/* ===========================================================================
   Radio button
   =========================================================================== */


function initRadioDeselectableInputs() {
  const $radioDeselectableInputs = document.querySelectorAll('.js-radio-deselectable');

  [...$radioDeselectableInputs].forEach((input) => {
    input.addEventListener('click', (event) => {
      let radioItem = $(event.target).closest('.js-radio-item-container').find('input:radio');
      if ($(radioItem).prop('checked')) {
        $(radioItem).prop('checked', false);
        event.preventDefault();
      }
    });
  });
}

$(initRadioDeselectableInputs());

export default initRadioDeselectableInputs;
