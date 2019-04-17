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

  const $vehiclesToDeclareDatatable = $('.js-vehicles-to-declare-datatable');
  if ($vehiclesToDeclareDatatable) {
    $vehiclesToDeclareDatatable.each((index, datatable) => {
      let ajaxUrl = $(datatable).data('href');
      let transUrl = $(datatable).data('trans');
      $(datatable).DataTable({
        'processing': true,
        'serverSide': true,
        'scrollX': true,
        'responsive': true,
        'autoWidth': true,
        'order': [[ 1, 'desc' ]],
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
        'columnDefs': [
          {'targets': 0, 'data': 'image', 'searchable': false, 'orderable': false, 'className':'dt-image'},
          {'targets': 1, 'data': 'vehicle', 'searchable': true, 'orderable': true},
          {'targets': 2, 'data': 'date', 'searchable': false, 'orderable': true},
          {'targets': 3, 'data': 'actions', 'searchable': false, 'orderable': false},
        ]
      });
    });
  }
});