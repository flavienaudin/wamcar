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
  }
});