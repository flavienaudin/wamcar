/* ===========================================================================
   Select 2
   =========================================================================== */

import 'select2';

const fr = require('select2/src/js/select2/i18n/fr');

$.fn.select2.amd.define('NbSelectedItemSelectionAdapter', ['select2/utils', 'select2/selection/multiple', 'select2/selection/placeholder', 'select2/selection/eventRelay', 'select2/selection/single'],
  function (Utils, MultipleSelection, Placeholder, EventRelay, SingleSelection) {
    // Decorates MultipleSelection with Placeholder
    let adapter = Utils.Decorate(MultipleSelection, Placeholder);
    // Decorates adapter with EventRelay - ensures events will continue to fire
    // e.g. selected, changed
    adapter = Utils.Decorate(adapter, EventRelay);

    adapter.prototype.render = function () {
      // Use selection-box from SingleSelection adapter
      // This implementation overrides the default implementation
      return SingleSelection.prototype.render.call(this);
    };

    adapter.prototype.update = function (data) {
      // copy and modify SingleSelection adapter
      this.clear();

      let $rendered = this.$selection.find('.select2-selection__rendered');
      let noItemsSelected = data.length === 0;
      let formatted = '';

      if (noItemsSelected) {
        formatted = this.options.get('placeholder') || '';
      } else {
        let itemsData = {
          placeholder: this.options.get('placeholder'),
          selected: data || [],
          all: this.$element.find('option') || []
        };
        // Pass selected and all items to display method
        // which calls templateSelection
        formatted = this.display(itemsData, $rendered);
      }

      $rendered.empty().append(formatted);
      $rendered.prop('title', formatted);
    };

    return adapter;
  });


/* ======================== */
/*** Search Pro : filters ***/
/* ======================== */

const selec2tInputs = document.querySelectorAll('.js-select2-input');
[...selec2tInputs].forEach((select) => {
  const $select2 = $(select);
  const options = {
    language: fr,
    closeOnSelect: true,
    width: '100%',
    templateResult: (state) => {
      return $('<span class="icon-">' + state.text + '</span>');
    }
  };
  if ($select2.data('multiple')) {
    options.multiple = true;
    options.allowClear = true;
    options.closeOnSelect = false;
    options.selectionAdapter = $.fn.select2.amd.require('NbSelectedItemSelectionAdapter');
    options.templateSelection = (data) => {
      if (data.selected.length > 1) {
        return `${data.selected[0].text} +${data.selected.length - 1}`;
      } else {
        return `${data.selected[0].text}`;
      }
    };
  }
  $select2.select2(options);

  $select2.on('select2:opening select2:closing', function (event) {
    let $searchfield = $(this).parent().find('.select2-search__field');
    $searchfield.prop('disabled', true);
  });
});


/* ========================================================== */
/*** Formulaire de recherche des conseillers dans l'en-tête ***/
/* ========================================================== */

const $advisorsHeaderSearchForm = $('#advisors-header-search-form');
if ($advisorsHeaderSearchForm.length) {
  const $searchField = $advisorsHeaderSearchForm.find('#advisor-header-search-field');
  const options = {
    width: '100%',
    language: fr,
    closeOnSelect: false,
    placeholder: $searchField.data('placeholder'),
    minimumInputLength: 2,
    multiple: false,
    tags: true,
    allowClear: true,
    createTag: (params) => {
      var term = $.trim(params.term);
      if (term === '') {
        return null;
      }
      return {
        id: term,
        text: term,
        newTag: true
      };
    },
    templateSelection: (data) => {
      if (data.newTag || data.id === '') {
        return data.text;
      } else {
        return $('<span class="tag">' + data.text + '</span>');
      }
    },
    dropdownParent: $advisorsHeaderSearchForm,
    delay: 250,
    ajax: {
      url: $searchField.data('autocomplete-url'),
      dataType: 'json'
    }
  };

  const $searchFieldSelect2 = $searchField.select2(options);
  $searchFieldSelect2.on('select2:select', (e) => {
    if(!e.params.data.newTag){
      const directorySearchByServiceLink = $searchField.data('directory-service-search');
      window.location = directorySearchByServiceLink.replace('_serviceslug_', e.params.data.id);
    }
  });



}