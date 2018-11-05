/* ===========================================================================
   Conversation
   =========================================================================== */

import linkifyHtml from 'linkifyjs/html';

let $removeButton = document.getElementById('js-remove-vehicle-selected');
let $inputText = document.getElementById('message_content');

if ($removeButton) {
  let $input = document.getElementById('message_vehicle');

  $removeButton.addEventListener('click', () => {
    $input.value = '';
    const preview = $removeButton.closest('.messages-item-vehicle-taken');
    preview.remove();
  });
}

if ($inputText) {
  window.addEventListener('beforeunload', function (e) {
    let srcUnloadEvent = document.activeElement;
    let unlockRefresh = srcUnloadEvent.hasAttribute('type') && srcUnloadEvent.getAttribute('type') === 'submit';

    if ($inputText.value !== '' && !unlockRefresh) {
      let confirmationMessage = 'Etes vous sûr de vouloir changer de page ? Vous risquez de perdre le message commencé.';

      e.returnValue = confirmationMessage;     // Gecko, Trident, Chrome 34+
      return confirmationMessage;              // Gecko, WebKit, Chrome <34
    }
  });
}

/* Detect URL in message to wrap with <a>*/
const $messagesItemContents = $('.messages-item-content');
if ($messagesItemContents.length) {
  $messagesItemContents.each((index, element) => {
    element.innerHTML = linkifyHtml(element.innerHTML);
  });
}

let $attachmentsCollectionHolder = $('#js-attachments-list');

if ($attachmentsCollectionHolder.length) {

  function addAttachmentInput() {
    if ($attachmentsCollectionHolder.find('input:file').filter(function () {
      return $(this).val() === '';
    }).length === 0) {

      let index = parseInt($attachmentsCollectionHolder.data('index'));
      let $newForm = $($attachmentsCollectionHolder.data('prototype').replace(/__name__/g, index));
      $attachmentsCollectionHolder.data('index', index + 1);
      $attachmentsCollectionHolder.append($newForm);

      $newForm.find('.js-delete-attachment').on('click', (event) => {
        $newForm.remove();
      });

      $newForm.change((event) => {
        let fullPath = $(event.currentTarget).find('input:file').val();
        if (fullPath) {
          let startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
          let filename = fullPath.substring(startIndex);
          if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
            filename = filename.substring(1);
          }

          $newForm.find('label').html(filename);
          $newForm.find('label').removeClass('text-underline');

          $newForm.find('.js-delete-attachment').removeClass('is-hidden');
        }
        addAttachmentInput();
      });
    }
  }

  let $div = $attachmentsCollectionHolder.children('div');
  if ($div.length === 0) {
    addAttachmentInput();
  } else {
    // Security : if data where submitted then set event listeners
    $div.each((index, div) => {
      $(div).find('.js-delete-attachment').on('click', (event) => {
        $(div).remove();
      });

      $(div).change((event) => {
        let fullPath = $(event.currentTarget).find('input:file').val();
        if (fullPath) {
          let startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
          let filename = fullPath.substring(startIndex);
          if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
            filename = filename.substring(1);
          }

          $(div).find('label').html(filename);
          $(div).find('label').removeClass('text-underline');

          $(div).find('.js-delete-attachment').removeClass('is-hidden');
        }
      });

    });
  }
}

