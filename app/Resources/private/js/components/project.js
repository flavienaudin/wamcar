/* ===========================================================================
   User Project
   =========================================================================== */

require('formdata-polyfill');

let $collectionHolder = document.getElementById('js-project-list');
let $addInput = document.getElementById('add-project-list');

if ($collectionHolder && $addInput) {
  let $makeSelect;
  let $modelSelect;

  let clearSelect = function (select) {
    select.find('OPTION').remove();
    select.append('<option value="">Modèle du véhicule</option>');
  };

  $addInput.addEventListener('click', () => {
    addProjectInTheList();
  });

  $collectionHolder.setAttribute('data-index', $collectionHolder.querySelectorAll('li.wish-item').length + 1);

  function addRemoveEvent() {
    let $removeInput = document.querySelectorAll('a.delete-project');

    [...$removeInput].forEach((element) => {
      element.addEventListener('click', (element) => {
        removeProjectInTheList(element);
      });
    });
  }

  function refreshVar() {
    $makeSelect = $collectionHolder.querySelectorAll('.wish-item .make-select');
    $modelSelect = $collectionHolder.querySelectorAll('.wish-item .model-select');
  }

  function addProjectInTheList() {
    let index = parseInt($collectionHolder.getAttribute('data-index'));
    let newForm = $collectionHolder.getAttribute('data-prototype').replace(/__name__/g, index);
    $collectionHolder.setAttribute('data-index', index + 1);
    $collectionHolder.insertAdjacentHTML('beforeend', newForm);
    refreshVar();
    addRemoveEvent();
    addMakeEvent();
  }

  function removeProjectInTheList(element) {
    element.target.closest('.wish-item').remove();
  }

  function addMakeEvent() {
    let dataFetchUrl = $collectionHolder.getAttribute('data-fetch-url');
    [...$makeSelect].forEach((makeSelect) => {
      makeSelect.addEventListener('change', (event) => {
        let $liActive = $(event.target).closest('.wish-item');
        let $nextModelSelect = $liActive.find('.model-select').first();
        clearSelect($nextModelSelect);

        let filterForm = new FormData();
        filterForm.append('filters[make]', event.target.value);

        fetch(dataFetchUrl, {
          method: 'POST',
          body: filterForm,
          credentials: 'include',
          headers: new Headers({
            'X-Requested-With': 'XMLHttpRequest'
          })
        })
          .then(response => response.json())
          .then((data) => {
            for (let value in data['model']) {
              if (data['model'].hasOwnProperty(value)) {
                $nextModelSelect.append('<option value="' + value + '">' + data['model'][value] + '</option>');
              }
            }
          })
          .catch(err => {
            throw err;
          });
      });
    });
  }

  refreshVar();
  addRemoveEvent();
  addMakeEvent();
}
