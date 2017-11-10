/* ===========================================================================
   Date picker
   =========================================================================== */

import datepicker from 'air-datepicker';
import '../../../../../node_modules/air-datepicker/src/js/i18n/datepicker.fr.js';

const $datepicker = $('.js-datepicker');

if ($datepicker) {
  $datepicker.datepicker({
    language: 'fr'
  });
}
