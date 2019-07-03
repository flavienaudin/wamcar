/* ===========================================================================
   Like
   =========================================================================== */
import * as Toastr from 'toastr';

const $likes = $('.js-like');
if ($likes.length > 0) {
  $likes.each((index, element) => {
    const $like = $(element);
    $like.on('click', (event) => {
      event.preventDefault();
      $like.toggleClass('icon-thumbs-up');
      $like.toggleClass('icon-thumbs-o-up');

      let action = $like.attr('data-wtaction');
      if(action.startsWith('LI')){
        action = action.replace('LI','UL');
      }else {
        action = action.replace('UL', 'LI');
      }
      const href = $like.attr('data-href');
      $.ajax({
        url: href
      }).done(function (nbPositiveLikes) {
        $like.children('sub').html(nbPositiveLikes);
        $like.attr('data-wtaction', action);
      }).fail(function (jqXHR, textStatus ) {
        Toastr.warning(jqXHR.responseJSON);
        $like.toggleClass('icon-thumbs-up');
        $like.toggleClass('icon-thumbs-o-up');
      });
    });
  });
}
