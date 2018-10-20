/* ===========================================================================
   Garage
   =========================================================================== */

import 'select2';

const fr = require('select2/src/js/select2/i18n/fr');

$(document).ready(function () {
  const $select = $('.js-garage-pro-invitation-emails');

  if ($select.length) {
    $select.select2({
      language: fr,
      placeholder: $select.attr('placeholder'),
      tags: true,
      tokenSeparators: [';', ',', ' '],
      multiple: true,
      width: '100%',
      AttachContainer: true
    });

    $select.on('select2:close', function (e) {
      setTimeout(function () {
        $select.parent().find('.select2-search__field').focus();
      }, 100);
    });
  }
});