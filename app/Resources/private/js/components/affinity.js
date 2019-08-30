/* ===========================================================================
   Affinity elements
   =========================================================================== */

import Chart from 'chart.js';

/** Affinty Radar Chart **/

const $radarChart = $('.js-radar-chart');

if ($radarChart.length) {
  $radarChart.each((index, element) => {
    let myRadarChart = new Chart(element, {
      type: 'radar',
      data: $(element).data('dataset'),
      options: {
        scale: {
          ticks: {
            beginAtZero: true,
            max: 100
          }
        }
      }
    });
  });
}

const $affinityAjaxForm = $('.js_affinity_ajax_form');
if ($affinityAjaxForm.length) {
  init_ajax_form();
}

function init_ajax_form() {
  console.log('init_ajax_form');

  $(document).on('forminvalid.zf.abide', function (ev, $form) {
    console.log('Affinity.js : Form ' + ev.currentTarget.name + ' is invalid');
  });

  init_input_form();

  $(document).on('submit', '.js_affinity_ajax_form', function (ev) {
    ev.preventDefault();
    console.log('Affinity.js : Form ' + ev.currentTarget.name + ' intercepted');

    ev.preventDefault();
    let $form = $(ev.currentTarget);

    let $serializedFormData = $form.serialize();
    // Add manually the submit button to request
    let clickedSubmit = $(this).find('[clicked=clicked]');
    if (clickedSubmit.length) {
      $serializedFormData += '&'
        + encodeURI($(clickedSubmit[0]).attr('name'))
        + '='
        + encodeURI($(clickedSubmit[0]).val());
    }
    console.log($serializedFormData);

    $.ajax({
      method: $form.attr('method'),
      url: $form.attr('action'),
      data: $serializedFormData
    })
      .done(function (data) {
        console.log('Done');
        if (typeof data.message !== 'undefined') {
          alert(data.message);
        }
        if (data.hasOwnProperty('nextQuestion')) {
          $form.replaceWith(data.nextQuestion);
          init_input_form();
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Fail');
        console.log(jqXHR.responseJSON);
        if (typeof jqXHR.responseJSON !== 'undefined') {
          if (jqXHR.responseJSON.hasOwnProperty('form')) {
            $(this).html(jqXHR.responseJSON.form);
          }

          $('.form_error').html(jqXHR.responseJSON.message);

        } else {
          alert(errorThrown);
        }

      });
  });
}

function init_input_form() {
  // Set the right default submit button when hitting ENTER key
  $('form.js_affinity_ajax_form input[type!=submit]').keydown(function (event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      $('form.js_affinity_ajax_form .js-default_submit[type=submit]').click();
    }
  });

  // Decide which submit button is clicked
  $('form.js_affinity_ajax_form [type=submit]').on('click', function () {
    $('[type=submit]', $(this).parents('form')).removeAttr('clicked');
    $(this).attr('clicked', 'clicked');
  });
}