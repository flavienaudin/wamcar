/* ===========================================================================
   Phone number
   =========================================================================== */

// Show the phone number
const $phoneNumberElement = $('.js-see-number');
if ($phoneNumberElement.length) {
  $phoneNumberElement.each((index, element) => {
    $(element).on('click', (event) => {
      $(element).parent().find('.js-see-number,.js-phone-number').toggleClass('is-hidden');
    });
  });
}

// Ajax request to update number of clicks on "tel" (show and call)
const $phoneNumberClickables = $('.js-callphone-action,.js-showphone-action');
$phoneNumberClickables.each((index, clickable) => {
  const url = $(clickable).data('href'),
    eventId = $(clickable).attr('id');
  $(clickable).on('click', () => {
    $.ajax({
      url: url,
      method: 'POST',
      data: {'eventId': eventId}
    }).done(function (data) {
      $(clickable).off('click');
    });
  });
});