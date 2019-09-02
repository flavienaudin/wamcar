/* ===========================================================================
   Expert
   =========================================================================== */
import * as Toastr from 'toastr';

const $addExpertLinks = $('.js-expert');
if ($addExpertLinks.length > 0) {
  $addExpertLinks.each((index, element) => {
    const $addExpertLink = $(element);
    $addExpertLink.on('click', (event) => {
      event.preventDefault();
      $addExpertLink.toggleClass('icon-heart');
      $addExpertLink.toggleClass('icon-heart-o');

      let action = $addExpertLink.data('wtaction');
      if(action.startsWith('ADD')){
        action = action.replace('ADD','REMOVE');
      }else {
        action = action.replace('REMOVE', 'ADD');
      }
      const href = $addExpertLink.data('href');
      $.ajax({
        url: href
      }).done(function (success) {
        $addExpertLink.data('wtaction', action);
        Toastr.success(success);
      }).fail(function (jqXHR, textStatus ) {
        $addExpertLink.toggleClass('icon-heart');
        $addExpertLink.toggleClass('icon-heart-o');
        Toastr.warning(jqXHR.responseJSON.error);
      });
    });
  });
}
