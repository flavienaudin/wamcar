/* ===========================================================================
   Star
   =========================================================================== */

const $starResetInputs = document.querySelectorAll('.star-reset');

[...$starResetInputs].forEach((input) => {
  input.addEventListener('click', () => {
    let starList = $(input).closest('.star-list');
    $(starList).find('input').prop('checked', false);
  });
});
