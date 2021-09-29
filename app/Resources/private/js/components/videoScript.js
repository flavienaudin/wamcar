import {Reveal} from 'foundation-sites/js/foundation.reveal';

/* ===========================================================================
   Video Script
   =========================================================================== */

/* Script Sequence list*/
initScriptSequencesListForm();

function initScriptSequencesListForm() {
  const $scriptSequencesCollectionHolders = $('.js-script-section-sequences-list');
  $scriptSequencesCollectionHolders.each((index, scriptSequencesCollectionHolder) => {
    const $scriptSequencesCollectionHolder = $(scriptSequencesCollectionHolder);
    const $div = $scriptSequencesCollectionHolder.children('div');
    if ($div.length === 0) {
      addScriptSequenceInput($scriptSequencesCollectionHolder);
    } else {
      /* TODO necessaire pour ajouter/supprimer des sequences
      // Security : if data where submitted then set event listeners
      $div.each((index, div) => {
        $(div).find('.js-delete-sequence').on('click', (event) => {
          $(div).remove();
        });

        $(div).change((event) => {
          $(div).find('.js-delete-sequence').removeClass('is-hidden');
        });

      });
      */
    }
  });
}

function addScriptSequenceInput($attachmentsCollectionHolder) {
  const index = parseInt($attachmentsCollectionHolder.data('index'));
  const $newForm = $($attachmentsCollectionHolder.data('prototype').replace(/__name__/g, index));
  $attachmentsCollectionHolder.data('index', index + 1);
  $attachmentsCollectionHolder.append($newForm);

  /* TODO necessaire pour ajouter/supprimer des sequences
  const $deleteSequenceButton = $newForm.find('.js-delete-sequence');
  $deleteSequenceButton.removeClass('is-hidden');
  $deleteSequenceButton.on('click', (event) => {
    console.log('delete seq', this, $newForm);
    $newForm.remove();
  });

  $newForm.find('.js-add-sequence').on('click', (event) => {
    console.log('Click add Seq', $newForm, event.currentTarget);
    // $newForm.find('.js-delete-sequence').removeClass('is-hidden');
    $(event.currentTarget).addClass('is-hidden');
    addScriptSequenceInput($attachmentsCollectionHolder);
  });*/
}


/* Script Sequence Edition */

const $editSequenceButtons = $('.js-script-sequence-getform');
const $scriptSequenceEditModalContainer = $('#jsScriptSequenceEditModalContainer');
$editSequenceButtons.each((index, editSequenceButton) => {
  const $editSequenceButton = $(editSequenceButton);
  $editSequenceButton.on('click', ($event) => {
    $.ajax({
      url: $editSequenceButton.data('url'),
      method: 'GET'
    }).done(function (success) {
      if (success.hasOwnProperty('html')) {
        $scriptSequenceEditModalContainer.html(success.html);

        const $modal = $('#' + success.modalId);
        const editFormModal = new Reveal($modal);
        editFormModal.open();

        $modal.find('form').on('submit', (e) => {
          e.preventDefault();
          const $editForm = $(e.currentTarget);
          const $formAction = $editForm.attr('action');
          const formData = new FormData($editForm[0]);

          const $submitButton = $editForm.find('button[type=submit]');
          $submitButton.attr('disabled', 'disabled');
          $submitButton.addClass('is-disabled');

          $.ajax({
            url: $formAction,
            type: 'POST',
            data: formData,
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false
          })
            .done(function (success) {
              if (success.hasOwnProperty('redirectTo')) {
                window.location = success.redirectTo;
              }
            })
            .fail(function (jqXHR, textStatus) {
              console.log('fail');
            });
        });

        $(document).on('closed.zf.reveal', '#' + success.modalId, (event) => {
          $('#' + success.modalId).parent().remove();
        });
      }
    }).fail(function (jqXHR, textStatus) {
      console.log('fail');
    });
  });
});

