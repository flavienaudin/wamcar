/* ===========================================================================
   Phone number
   =========================================================================== */

const $phoneNumberElement = $('.js-phone-number-button');

if ($phoneNumberElement.length) {
  $phoneNumberElement.each((index, element) => {
    $(element).on('click', (event) => {
      $(element).find('.js-see-number,.js-phone-number').toggleClass('is-hidden');
    });
  });
}