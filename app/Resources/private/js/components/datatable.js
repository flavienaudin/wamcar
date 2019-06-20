/* ===========================================================================
   Datatable
   =========================================================================== */
import 'datatables.net/js/jquery.dataTables.min';
import 'datatables.net-responsive/js/dataTables.responsive.min';
import 'datatables.net-buttons/js/dataTables.buttons.min';
import 'datatables.net-buttons/js/buttons.html5.min';

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
        'responsive': {
          'details': {
            'renderer': function (api, rowIdx, columns) {
              var data = $.map(columns, function (col, i) {
                return col.hidden ?
                  '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                  '<td class="is-flex">' + col.title + '&nbsp;:&nbsp;' + col.data + '</td></tr>' :
                  '';
              }).join('');
              return data ? $('<table/>').append(data) : false;
            }
          }
        },
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
          {'data': 'control', 'searchable': false, 'orderable': false, 'className': 'control'},
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
      }).on('responsive-display', (e, datatable, columns) => {
        let $leadStatusSelect = $('td.child select.js-change-status');
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
        'responsive': {
          'details': {
            'renderer': function (api, rowIdx, columns) {
              var data = $.map(columns, function (col, i) {
                return col.hidden ?
                  '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                  '<td class="is-flex ' + (col.columnIndex === 1 ? 'dt-image' : '') + '">' +
                  (col.columnIndex !== 1 && col.columnIndex !== 4 ? col.title + '&nbsp;:&nbsp;' : '') + col.data +
                  '</td></tr>'
                  : '';
              }).join('');
              return data ? $('<table/>').append(data) : false;
            }
          }
        },
        'order': [[3, 'desc']],
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
          {'targets': 0, 'data': 'control', 'searchable': false, 'orderable': false, 'className': 'control'},
          {'targets': 1, 'data': 'image', 'searchable': false, 'orderable': false, 'className': 'dt-image'},
          {'targets': 2, 'data': 'vehicle', 'searchable': true, 'orderable': true},
          {'targets': 3, 'data': 'date', 'searchable': false, 'orderable': true},
          {'targets': 4, 'data': 'actions', 'searchable': false, 'orderable': true},
        ]
      });
    });
  }

  const $declaredSalesDatatable = $('.js-perf-declared-sales-datatable');
  if ($declaredSalesDatatable) {
    $declaredSalesDatatable.each((index, datatable) => {
      let transUrl = $(datatable).data('trans');
      $(datatable).DataTable({
        'responsive': {
          'details': {
            'type': 'column'
          }
        },
        'order': [],
        'lengthChange': false,
        'paging': false,
        'searching': false,
        'info': false,
        'language': {'url': transUrl},
        'columnDefs': [
          {'targets': '_all', 'searchable': false, 'orderable': false},
          {'targets': 0, 'className': 'control'}
        ]
      });
    });
  }


  const $usersListStatisticsDatatable = $('.js-personal-users-statistics-datatable,.js-pro-users-statistics-datatable');
  if ($usersListStatisticsDatatable) {
    $usersListStatisticsDatatable.each((index, datatable) => {
      let ajaxUrl = $(datatable).data('href');
      let transUrl = $(datatable).data('trans');
      $(datatable).DataTable({
        'processing': true,
        'serverSide': true,
        'responsive': {
          'details': {
            'renderer': function (api, rowIdx, columns) {
              var data = $.map(columns, function (col, i) {
                return col.hidden ?
                  '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                  '<td class="is-flex ' + (col.columnIndex === 1 ? 'dt-image' : '') + '">' +
                  (col.columnIndex !== 1 && col.columnIndex !== 4 ? col.title + '&nbsp;:&nbsp;' : '') + col.data +
                  '</td></tr>'
                  : '';
              }).join('');
              return data ? $('<table/>').append(data) : false;
            }
          }
        },
        'ordering': false,
        'paging': false,
        'searching': false,
        'lengthChange': false,
        'language': {
          'url': transUrl
        },
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
        'dom':'Birt',
        'buttons':[
          {
            'action':'copy',
            'text':'Copier',
            'className':'text-underline margin-left-1'
          },
          {
            'action':'csv',
            'text':'Export CSV',
            'className':'text-underline margin-left-1'
          }
        ]
      });
    });
  }
});