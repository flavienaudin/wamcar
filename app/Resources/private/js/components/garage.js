/* ===========================================================================
   Garage
   =========================================================================== */

import 'select2';

const fr = require('select2/src/js/select2/i18n/fr');

const $selects = $('.js-garage-pro-invitation-emails');
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
        AttachContainer: true
      });

      $select.on('select2:close', function (e) {
        setTimeout(function () {
          $select.parent().find('.select2-search__field').focus();
        }, 100);
      });
    });
  });
}

// Search vehicle in garage page
const $garageSearchForm = $('#js-garage-vehicle-search-form');
if($garageSearchForm.length){
  // Submit form when using pagination navigation
  let $paginationItems = $('.pagination-item');
  if($paginationItems.length){
    $paginationItems.on('click', (e) => {
      e.preventDefault();
      $garageSearchForm.attr('action', $(e.currentTarget).attr('href'));
      $garageSearchForm.submit();
    });
  }
}