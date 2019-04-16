/* ===========================================================================
   Datatable
   =========================================================================== */
import 'datatables.net/js/jquery.dataTables.min';
import * as Toastr from 'toastr';

$(function () {
  const $leadsDatatable = $('.js-lead-datatable');
  if ($leadsDatatable) {
    $leadsDatatable.each((index, datatable) => {
      let ajaxUrl = $(datatable).data('href');
      let transUrl = $(datatable).data('trans');
      $(datatable).DataTable({
        'processing': true,
        'serverSide': true,
        'scrollX': true,
        'responsive': true,
        'autoWidth': true,
        'searchDelay': 1000,
        'language': {
          'url': transUrl
        },
        'lengthChange': false,
        'ajax': {
          'url': ajaxUrl,
          'error': function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.hasOwnProperty('responseJSON') && jqXHR.responseJSON.hasOwnProperty('error')) {
              Toastr.warning(jqXHR.responseJSON.error);
            } else {
              Toastr.warning(textStatus);
            }
          }
        },
        'columns': [
          {'data': 'leadName', 'searchable': true, 'orderable': true},
          {'data': 'lastContactAt', 'searchable': false, 'orderable': true},
          {'data': 'proPhoneStats', 'searchable': false, 'orderable': true, 'className': 'dt-center'},
          {'data': 'profilePhoneStats', 'searchable': false, 'orderable': true, 'className': 'dt-center'},
          {'data': 'messageStats', 'searchable': false, 'orderable': true, 'className': 'dt-center'},
          {'data': 'likeStats', 'searchable': false, 'orderable': true, 'className': 'dt-center'},
          {'data': 'status', 'searchable': false, 'orderable': true},
          {'data': 'action', 'searchable': false, 'orderable': true}
        ]
      }).on('draw', () => {
        let $leadStatusSelect = $('select.js-change-status');
        $leadStatusSelect.each((index, selectElt) => {
          $(selectElt).on('change', (event) => {
            let url = $(event.currentTarget).find('option:selected').first().val();
            $.ajax({
              url: url
            }).done(function (success) {
              Toastr.success(success);
            }).fail(function (jqXHR, textStatus) {
              if (jqXHR.hasOwnProperty('responseJSON') && jqXHR.responseJSON.hasOwnProperty('error')) {
                Toastr.warning(jqXHR.responseJSON.error);
              } else {
                Toastr.warning(textStatus);
              }
            });
          });
        });
      });
    });
  }
});