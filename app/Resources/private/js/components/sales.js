/* ===========================================================================
   Sales
   =========================================================================== */

$(function () {
  const $leadCustomerSelect = $('.js-lead-customer-select');
  if ($leadCustomerSelect) {
    $leadCustomerSelect.each((index, selectElt) => {

      $(selectElt).on('change', (event) => {
        let selectedOption = $(event.currentTarget).find('option:selected').first();
        $('#' + $(selectElt).data('customer-firstname')).val(selectedOption.data('firstname'));
        $('#' + $(selectElt).data('customer-lastname')).val(selectedOption.data('lastname'));
      });
    });
  }

  const $proVehicleSelect = $('.js-pro-vehicle-select');
  if ($proVehicleSelect) {
    $proVehicleSelect.each((index, selectElt) => {
      $(selectElt).on('change', (event) => {
        let selectedOption = $(event.currentTarget).find('option:selected').first();
        $('#' + $(selectElt).data('provehicle-price')).val(selectedOption.data('price'));
      });
    });
  }

});