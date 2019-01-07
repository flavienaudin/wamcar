/* ===========================================================================
   Notification
   =========================================================================== */

const $notificationMarkLinks = document.querySelectorAll('.js-notification-mark-link');

if ($notificationMarkLinks) {
  [...$notificationMarkLinks].forEach((notificationMarkLink) => {
    notificationMarkLink.addEventListener('click', (event) => {
      event.preventDefault();
      const $target = $(event.target);
      const href = $target.data('href');
      fetch(href)
        .then((response) => {
          if(response.ok) {
            return response.json();
          }else{
            console.log('Error toogling notification state');
          }
        })
        .then((responseJson) => {
          if(responseJson.notification_id) {
            $('.js-notification-' + responseJson.notification_id).each((index, elt) => {
              $(elt).toggleClass('notification-unseen').toggleClass('notification-seen');
            });
          }
        });
    });
  });
}
