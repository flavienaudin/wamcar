/* ===========================================================================
   Phone number
   =========================================================================== */

const $phoneNumberElement = $('.js-see-number');

if ($phoneNumberElement.length) {
  $phoneNumberElement.each((index, element) => {
    $(element).on('click', (event) => {
      $(element).parent().find('.js-see-number,.js-phone-number').toggleClass('is-hidden');
    });
  });
}