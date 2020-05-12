/* ===========================================================================
   Form Events
   =========================================================================== */

let $onChangeInputs = document.querySelectorAll('.js-onchange-submit');

[...$onChangeInputs].forEach((input) => {
  input.addEventListener('change', () => {
    $(input).closest('form').submit();
  });
});


let removePictureSubmitButtons = document.querySelectorAll('.js-file-preview-remove-submit');

[...removePictureSubmitButtons].forEach((button) => {
  button.addEventListener('click', () => {
    $(button).addClass('loader-visible');
    $(button).parent().find('.js-file-remove-input').prop('checked', true);
    $(button).closest('form').submit();
  });
});


