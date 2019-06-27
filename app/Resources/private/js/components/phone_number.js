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
    action = $(clickable).data('wtaction'),
    from = $(clickable).data('wtfrom'),
    to = $(clickable).data('wtto');
  $(clickable).on('click', () => {
    $.ajax({
      url: url,
      method: 'POST',
      data: {'action': action, 'from': from, 'to': to}
    }).done(function (data) {
      $(clickable).off('click');
    });
  });
});