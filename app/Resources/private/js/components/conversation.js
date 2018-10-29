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
    console.log('addAttachmentInput');
    let index = parseInt($attachmentsCollectionHolder.data('index'));
    let newForm = $attachmentsCollectionHolder.data('prototype').replace(/__name__/g, index);
    $attachmentsCollectionHolder.data('index', index + 1);

    $attachmentsCollectionHolder.last().off('change');
    $attachmentsCollectionHolder.append($(newForm));
    $attachmentsCollectionHolder.last().change(addAttachmentInput);


  }

  addAttachmentInput();
}

