/* ===========================================================================
   Datatable
   =========================================================================== */
import 'datatables.net/js/jquery.dataTables.min';

$(function () {
  const $leadsDatatable = $('.js-lead-datatable');
  if ($leadsDatatable) {
    $leadsDatatable.each((index, datatable) => {
      let ajaxUrl = $(datatable).data('href');
      let transUrl = $(datatable).data('trans');
      $(datatable).DataTable({
        'processing': true,
        'serverSide': true,
        'responsive': true,
        'autoWidth': true,
        'searchDelay': 1000,
        'language': {
          'url': transUrl
        },
        'lengthChange': false,
        'ajax': ajaxUrl,
        'columns': [
          {'data': 'leadName', 'searchable': true, 'orderable': true},
          {'data': 'lastContactAt', 'searchable': false, 'orderable': true},
          {'data': 'proPhoneStats', 'searchable': false, 'orderable': true},
          {'data': 'profilePhoneStats', 'searchable': false, 'orderable': true},
          {'data': 'messageStats', 'searchable': false, 'orderable': true},
          {'data': 'likeStats', 'searchable': false, 'orderable': true},
          {'data': 'action', 'searchable': false, 'orderable': true}
        ]
      });
    });
  }
});