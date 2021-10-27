/* ===========================================================================
   Drag and drop file uploader & Ajax submission with progress bar
   =========================================================================== */

import {hashCode} from './utils';
import * as Toastr from 'toastr';
import {initVideoProjectDocumentDeleteLinks} from './videoProject';

let $dragArea = null, $progressArea = null, $uploadedArea = null, $fileInput = null;

$(document).on('closed.zf.reveal', '#js-add-document', (event) => {
  if ($progressArea != null) {
    $progressArea.html(null);
  }
  if ($uploadedArea != null) {
    $uploadedArea.html(null);
  }
});

const $addDocumentForm = $('#jsAddDocumentForm');
if ($addDocumentForm.length) {
  $fileInput = $addDocumentForm.find('.file-input');
  $dragArea = $addDocumentForm.parent('.drag-area-wrapper').parent('.drag-area');
  $progressArea = $addDocumentForm.parent('.drag-area-wrapper').find('.progress-area');
  $uploadedArea = $addDocumentForm.parent('.drag-area-wrapper').find('.uploaded-area');

  //If user Drag File Over DropArea
  $addDocumentForm.on('dragover', (event) => {
    event.preventDefault(); //preventing from default behaviour
    $addDocumentForm.addClass('active');
  });
  //If user leave dragged File from DropArea
  $addDocumentForm.on('dragleave', () => {
    $addDocumentForm.removeClass('active');
  });
  //If user drop File on DropArea
  $addDocumentForm.on('drop', (event) => {
    event.preventDefault(); //preventing from default behaviour
    $addDocumentForm.removeClass('active');
    for (let idx = 0; idx < event.originalEvent.dataTransfer.files.length; idx++) {
      uploadFile(event.originalEvent.dataTransfer.files[idx]);
    }
  });

  $fileInput.on('change', (event) => {
    for (let idx = 0; idx < event.currentTarget.files.length; idx++) {
      uploadFile(event.currentTarget.files[idx]);
    }
  });
}

// file upload function
function uploadFile(file) {
  const idName = hashCode(file.name);
  let name = file.name, $progressElementDiv = null;
  if (name.length >= 12) {
    //if file name length is greater than 12 then split it and add ...
    const splitName = name.split('.');
    name = splitName[0].substring(0, 12) + '... .' + splitName[splitName.length - 1];
  }

  const xhr = new XMLHttpRequest(); //creating new xhr object (AJAX)
  xhr.open('POST', $addDocumentForm.attr('action')); //sending post request to the specified URL
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

  // Gèère la réponse à la requête
  xhr.addEventListener('load', (event) => {
    // Retire l'élément "fichier en cours d'envoi";
    $('#' + idName).remove();
    $uploadedArea.removeClass('onprogress');

    if (xhr.status === 200) {
      const uploadedHTML = `<div class='uploaded-file-item'>
                              <span class='icon-file-text22'></span>
                              <div class='content upload'>
                                <div class='details'>
                                  <span class='name'>${name} • ${$dragArea.data('terminated')}</span>
                                </div>
                              </div>
                              <span class='icon-check-circle success-color no-margin'></span>
                          </div>`;
      $uploadedArea.prepend(uploadedHTML);

      const responseJson = JSON.parse(xhr.responseText);
      if (responseJson.message) {
        Toastr.success(responseJson.message);
      }
      if (responseJson.documents) {
        $('#js-library-documents-list').html($(responseJson.documents));
        initVideoProjectDocumentDeleteLinks();
      }
    } else {
      Toastr.error(xhr.statusText);
      const uploadedHTML = `<div class='uploaded-file-item'>
                              <span class='icon-file-text22'></span>
                              <div class='content upload'>
                                <div class='details'>
                                  <span class='name'>${name} • ${$dragArea.data('error')}</span>
                                </div>
                              </div>
                              <span class='icon-times-circle danger-color no-margin'></span>
                          </div>`;
      $uploadedArea.prepend(uploadedHTML);
    }
  });
  // Gère la progression de l'envoi de la requête (fichier)
  xhr.upload.addEventListener('progress', (event) => {
    //file uploading progress event
    const fileLoaded = Math.floor((event.loaded / event.total) * 100);  //getting percentage of loaded file size

    $progressElementDiv = $('#' + idName);
    if ($progressElementDiv.length === 0) {
      $progressElementDiv = $('<div class="uploading-file-item" id="' + idName + '">/<div>');
      $progressArea.append($progressElementDiv);
    }

    const $progressHTMLContent = $(`<span class='icon-file-text22'></span>
                          <div class="content">
                            <div class='details'>
                              <span class='name'>${name} • ${$dragArea.data('uploading')}</span>
                              <span class='percent'>${fileLoaded}%</span>
                            </div>
                            <div class='progress-bar'>
                              <div class='progress' style='width: ${fileLoaded}%'></div>
                            </div>
                          </div>`);
    $uploadedArea.addClass('onprogress');
    $progressElementDiv.html($progressHTMLContent);
    if (event.loaded === event.total) {
      $progressElementDiv.html($(`<span class='icon-file-text22'></span>
                          <div class="content">
                            <div class='details'>
                              <span class='name'>${name} • ${$dragArea.data('recording')}</span>
                              <span class='percent'>${fileLoaded}%</span>
                            </div>
                            <div class='progress-bar'>
                              <div class='progress' style='width: ${fileLoaded}%'></div>
                            </div>
                          </div>`));
    }
  });
  const data = new FormData($addDocumentForm.get(0));
  data.append($fileInput.attr('name'), file);
  xhr.send(data); //sending form data
}


