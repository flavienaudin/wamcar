/* ===========================================================================
   Video Projects
   =========================================================================== */

// Discussion
const $videoProjectDiscussion = $('#js_video_project_discussion');
if ($videoProjectDiscussion.length) {

  // Get messages
  var setTimeoutIdentifier;
  getMessages();

  const $manualUpdateButton = $('#js_discussion_update');
  if ($manualUpdateButton.length > 0) {
    $manualUpdateButton.on('click', getMessages);
  }

  function getMessages() {
    if (setTimeoutIdentifier !== undefined) {
      clearTimeout(setTimeoutIdentifier);
    }
    const $discussionMessagesSection = $('#js_video_project_discussion_messages');
    const url = $discussionMessagesSection.data('url');
    const startTimestamp = parseInt($discussionMessagesSection.data('start'));
    const endTimestamp = parseInt(new Date().getTime() / 1000);
    $.ajax({
      url: url,
      type: 'POST',
      data: {
        start: isNaN(startTimestamp) ? undefined : startTimestamp,
        end: endTimestamp
      }
    }).done(function (responseData) {
      $discussionMessagesSection.prepend(responseData.messages);
      $discussionMessagesSection.data('start', responseData.end);
    }).always(function () {
      setTimeoutIdentifier = setTimeout(getMessages, 20000);
    });
  }


  // Post a message
  initMessageFormSubmission();

  function initMessageFormSubmission() {
    const $messageForm = $('#js_message_form');
    if ($messageForm.length) {
      $messageForm.on('submit', (event) => {
        event.preventDefault();
        const $formAction = $messageForm.attr('action');

        $.ajax({
          url: $formAction,
          type: 'POST',
          data: $messageForm.serializeArray()
        }).done(function (data, textStatus) {
          $('#js_video_project_discussion_writer').html(data.messageForm);
          initMessageFormSubmission();

          // wait at least 1s as end timestamp is in second
          setTimeout(getMessages, 1000);

        }).fail(function (jqXHR, textStatus) {
          console.log('fail', textStatus);
        });
      });
    }
  }
}