/* ===========================================================================
   Video Projects
   =========================================================================== */

import 'select2';
import {initAttachmentsListForm} from './conversation';
import {sameDay} from './utils';
import linkifyHtml from 'linkifyjs/html';
import {default as autosize} from 'autosize';
import * as Toastr from 'toastr';
import {confirm} from '../app';

// Viewers / Creators
const $toogleCreatorStatusLinks = $('.js-toogle-creator-status');
if ($toogleCreatorStatusLinks.length > 0) {
  $toogleCreatorStatusLinks.each((index, element) => {
    const $toogleCreatorStatusLink = $(element);
    $toogleCreatorStatusLink.on('click', (event) => {
      event.preventDefault();
      const href = $toogleCreatorStatusLink.attr('href');
      $.ajax({
        url: href
      }).done(function (success) {
        $toogleCreatorStatusLink.parents('li.js-follower-item').find('img').toggleClass('creator');
        if(success.isCreator) {
          $toogleCreatorStatusLink.html($toogleCreatorStatusLink.data('set-viewer-label'));
        }else{
          $toogleCreatorStatusLink.html($toogleCreatorStatusLink.data('set-creator-label'));
        }
        Toastr.success(success.message);
      }).fail(function (jqXHR, textStatus ) {
        Toastr.warning(jqXHR.responseJSON.error);
      });
    });
  });
}


// Discussion
const $videoProjectDiscussion = $('#js_video_project_discussion');
let updateLastVisitedAtURL = undefined,
  setTimeoutIdentifier = undefined,
  updateLastVisitedCallLockFree = true,
  $showPreviousMessagesButton, $manualUpdateButton;

if ($videoProjectDiscussion.length) {
  // Get URL to update last VisitedAt
  updateLastVisitedAtURL = $videoProjectDiscussion.data('discussion-update-visitedat-url');

  $manualUpdateButton = $('#js_discussion_update');
  if ($manualUpdateButton.length > 0) {
    $manualUpdateButton.on('click', () => {
      // Ne pas appeler directement getMessages() sinon il y a une erreur à l'exécution de la fonction
      getMessages();
    });
  }

  // Get messages
  getMessages();

  // Post a message
  initMessageFormSubmission();

  // Show previous messages
  $showPreviousMessagesButton = $('#js_show_previous_messages');
  if ($showPreviousMessagesButton.length > 0) {
    $showPreviousMessagesButton.on('click', (event) => {
      event.preventDefault();
      getMessages(true);
    });
  }
}

/** Ajax request to get video project messages and set the timeout for the next request */
function getMessages(showPrevious = false) {
  if (setTimeoutIdentifier !== undefined) {
    clearTimeout(setTimeoutIdentifier);
  }
  const $discussionMessagesSection = $('#js_video_project_discussion_messages');
  const url = $discussionMessagesSection.data('url');
  const startTimestamp = showPrevious ? undefined : parseInt($discussionMessagesSection.data('start'));
  let searchEndTimestamp = undefined;
  if (showPrevious) {
    const $olderMessage = $discussionMessagesSection.children('div').last();
    searchEndTimestamp = $olderMessage.data('postedat');
  }
  const endTimestamp = searchEndTimestamp ? searchEndTimestamp : parseInt(new Date().getTime() / 1000);

  $manualUpdateButton.addClass('rotate');
  $.ajax({
    url: url,
    type: 'POST',
    data: {
      start: isNaN(startTimestamp) ? undefined : startTimestamp,
      end: endTimestamp,
      showPrevious: (showPrevious ? 1 : 0)
    }
  }).done(function (responseData) {
    if (showPrevious) {
      const $lastDiscussionElt = $discussionMessagesSection.children().last();
      if ($lastDiscussionElt.prop('tagName') === 'HR') {
        const endDate = new Date(parseInt(responseData.end) * 1000);
        const firstMessageDate = new Date(parseInt(responseData.firstMessageDate) * 1000);
        if (sameDay(endDate, firstMessageDate)) {
          $lastDiscussionElt.remove();
        }
      }

      $discussionMessagesSection.append(responseData.messages);
      if (responseData.messages.length === 0 && $showPreviousMessagesButton.length > 0) {
        $showPreviousMessagesButton.addClass('is-hidden');
        $('#js_nomore_previous_message').removeClass('is-hidden');
      }
    } else {
      let $messages = $(responseData.messages);
      if (responseData.lastMessageDate != null) {
        const lastMessageDate = new Date(parseInt(responseData.lastMessageDate) * 1000);
        const $topMessage = $discussionMessagesSection.children().first();
        if ($topMessage.length) {
          const topMessageDate = new Date(parseInt($topMessage.data('postedat')) * 1000);
          if (sameDay(lastMessageDate, topMessageDate)) {
            $messages.last().hide();
          }
        }
      }

      $discussionMessagesSection.prepend($messages);
      // Set next start timestamp with the "end" param of last request
      $discussionMessagesSection.data('start', responseData.end);
    }

    /* Detect URL in message to wrap with <a>*/
    const $messagesContents = $discussionMessagesSection.find('.message-content');
    if ($messagesContents.length) {
      $messagesContents.each((index, element) => {
        element.innerHTML = linkifyHtml(element.innerHTML);
      });
    }

    const $unreadMessages = $discussionMessagesSection.children('.unread:not(.withwaypoint)');
    $unreadMessages.each(function (idx, element) {
      $(element).addClass('withwaypoint');
      new Waypoint.Inview({
        element: element,
        enter: function (direction) {
          setTimeout(() => {
            $(element).removeClass('unread withwaypoint');

            // Update last visited update
            if (updateLastVisitedCallLockFree) {
              updateLastVisitedCallLockFree = false;
              $.ajax({
                url: updateLastVisitedAtURL
              }).always(() => {
                updateLastVisitedCallLockFree = true;
              });
            }
          }, 5000);
          this.destroy();
        }
      });
    });


  }).always(function () {
    // Stop the rotation with a minimum of 1s of duration
    setTimeout(() => {
      $manualUpdateButton.removeClass('rotate');
    }, 1000);
    setTimeoutIdentifier = setTimeout(getMessages, 20000);
  });
}

/** Init the Ajax form submission */
function initMessageFormSubmission() {
  const $messageForm = $('#js_message_form');
  if ($messageForm.length) {
    $messageForm.on('submit', (event) => {
      event.preventDefault();
      const $submitButton = $messageForm.find('button[type=submit]');
      $submitButton.attr('disabled', 'disabled');
      $submitButton.addClass('is-disabled');
      $submitButton.addClass('loader-visible');

      const $formAction = $messageForm.attr('action');
      const form = $messageForm[0];
      const formData = new FormData(form);

      $.ajax({
        url: $formAction,
        type: 'POST',
        data: formData,
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        cache: false
      }).done(function (data, textStatus) {
        $('#js_video_project_discussion_writer').html(data.messageForm);

        /* Textarea autosize */
        autosize($('textarea'));

        initAttachmentsListForm();
        initMessageFormSubmission();

        // wait at least 1s as end timestamp is in second
        setTimeout(getMessages, 1000);

      }).fail(function (jqXHR, textStatus) {
        console.log('fail', textStatus);
      });
    });
  }
}

// Partage
const fr = require('select2/src/js/select2/i18n/fr');
const $selects = $('.js-videoproject-share-emails');
if ($selects.length) {
  $(document).ready(() => {
    [...$selects].forEach(function (select) {
      const $select = $(select);
      $select.select2({
        language: fr,
        placeholder: $select.attr('data-placeholder'),
        multiple: true,
        allowClear: true,
        width: '100%',
        tags: true,
        tokenSeparators: [';', ',', ' '],
        AttachContainer: true,
        selectOnClose: true
      });

      $select.on('select2:close', function (e) {
        setTimeout(function () {
          $select.parent().find('.select2-search__field').focus();
        }, 100);
      });
    });
  });
}

// Documents
const $jsAddDocumentForm = $('#jsAddDocumentForm');
if ($jsAddDocumentForm.length) {
  $jsAddDocumentForm.find('.js-delete-attachment').on('click', (event) => {
    const inputFile = $(event.currentTarget).siblings('input:file');
    inputFile.val(null);
    $(event.currentTarget).siblings('label').html(inputFile.data('label'));
    $(event.currentTarget).addClass('is-hidden');
  });

  $jsAddDocumentForm.change((event) => {
    let fullPath = $(event.currentTarget).find('input:file').val();
    if (fullPath) {
      let startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
      let filename = fullPath.substring(startIndex);
      if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
        filename = filename.substring(1);
      }

      $jsAddDocumentForm.find('label').html(filename);
      $jsAddDocumentForm.find('label').removeClass('is-hidden');
      $jsAddDocumentForm.find('.js-delete-attachment').removeClass('is-hidden');
    }
  });
}

const $videoProjectlibraryDocumentsDeleteLink = $('.videoproject-library .js-delete-vp-document');
$videoProjectlibraryDocumentsDeleteLink.each((index, element) => {
  $(element).on('click', (event) => {
    event.preventDefault();
    const href = $(event.currentTarget).attr('href'),
      id = $(event.currentTarget).data('id'),
      title = $(event.currentTarget).data('title'),
      message = $(event.currentTarget).data('message');

    confirm(title, message, id, (param) => {
      $.ajax({
        url: param.href,
        type: 'DELETE'
      })
        .done((data, textStatus) => {
          $(element).parent('.js-vp-document').remove();
          Toastr.success(data.message);
        })
        .fail((jqXHR) => {
          Toastr.warning(jqXHR.responseJSON.errorMessage);
        });
    }, {'href': href});
  });
});
