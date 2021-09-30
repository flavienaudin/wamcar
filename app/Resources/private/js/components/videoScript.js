import {Reveal} from 'foundation-sites/js/foundation.reveal';
import {scriptVersionForm} from './scriptVersionWizardStep';

/* ===========================================================================
   Video Script
   =========================================================================== */

/* Script Sequence list*/
initScriptSequencesListForm();

function initScriptSequencesListForm() {
  const $scriptSequencesCollectionManagers = $('.js-script-section-sequences-manager');
  $scriptSequencesCollectionManagers.each((index, scriptSequencesCollectionManager) => {
    const $scriptSequencesCollectionManager = $(scriptSequencesCollectionManager);
    const $scriptSequencesCollectionHolder = $($scriptSequencesCollectionManager.find('.js-script-section-sequences-list')[0]);
    const $addSequenceButton = $($scriptSequencesCollectionManager.find('.js-add-sequence-button')[0]);
    $addSequenceButton.on('click', (event) => {
      addScriptSequenceInput($scriptSequencesCollectionHolder);
    });

    const $sequenceDiv = $scriptSequencesCollectionHolder.children('fieldset');
    if ($sequenceDiv.length === 0) {
      addScriptSequenceInput($scriptSequencesCollectionHolder);
    } else {
      // Security : if data where submitted then set event listeners
      $sequenceDiv.each((index, div) => {
        configDeleteSequenceButton($(div), false);
      });
    }
  });
}

function addScriptSequenceInput($attachmentsCollectionHolder) {
  const index = parseInt($($attachmentsCollectionHolder[0]).data('index'));
  const $newForm = $($($attachmentsCollectionHolder).data('prototype').replace(/__name__/g, index));
  $($attachmentsCollectionHolder).data('index', index + 1);
  $attachmentsCollectionHolder.append($newForm);

  // Mettre à jour la hauteur du formulaire
  scriptVersionForm.dispatchEvent(new Event('change'));
  // Configure le bouton pour supprimer la séquence
  configDeleteSequenceButton($newForm, true);
}

/**
 * Affiche le bouton "Supprimer la séquence" et enregistre l'événement au "clic" selon "isNewSequence" :
 * true : supprime la div qui a été ajouté via JS et n'existe donc pas déjà en base de données
 * false :
 * - vide les données du formulare (le back-end supprime les séquences vides)
 * - cache la séquence à l'utilisaeur
 * @param $sequenceDiv le conteneur de la séquence
 * @param isNewSequence true si la sequence a été ajouté via JS (sans ID), false si existente en base de données
 */
function configDeleteSequenceButton($sequenceDiv, isNewSequence) {
  const $deleteSequenceButton = $sequenceDiv.find('.js-delete-sequence');
  $deleteSequenceButton.removeClass('is-hidden');
  $deleteSequenceButton.on('click', (event) => {
    $sequenceDiv.addClass('is-hidden');
    if (isNewSequence) {
      $sequenceDiv.remove();
    } else {
      const $inputsToClear = $sequenceDiv.find('.js-empty-on-remove');
      $inputsToClear.each((index, input) => {
        input.value = null;
      });
    }

    // Mettre à jour la hauteur du formulaire
    scriptVersionForm.dispatchEvent(new Event('change'));
  });
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

