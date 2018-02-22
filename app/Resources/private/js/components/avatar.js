/* ===========================================================================
   Avatar
   =========================================================================== */

let $inputs = document.querySelectorAll('.js-onchange-submit');

[...$inputs].forEach((input) => {
  input.addEventListener('change', () => {
    $(input).closest('form').submit();
  });
});
