/* ===========================================================================
   Video Projects
   =========================================================================== */

// Discussion
const $discussionSection = $('#js_video_project_discussion');
if($discussionSection){
  getMessages($discussionSection);
}

const $manualUpdateButton = $('#js_discussion_update');
if($manualUpdateButton){
  $manualUpdateButton.click(() => {
    getMessages($($manualUpdateButton.data('update-section')));
  });
}

function getMessages($discussionSection){
  const url = $discussionSection.data('url');
  const start = $discussionSection.data('start');
  console.log('url', url);
  console.log('start', start);
  $.ajax({
    url: url,
    type: 'POST',
    data: {
      start: start,
      end: new Date()
    }
  }).done(function (success) {
    $discussionSection.prepend(success.messages);
    $discussionSection.data('start', success.end);
  });
}