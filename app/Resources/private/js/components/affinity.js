/* ===========================================================================
   Affinity elements
   =========================================================================== */

import Chart from 'chart.js';
import {Abide} from 'foundation-sites/js/foundation.abide';
import initRadioDeselectableInputs from './radio';

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

const $affinityAjaxForm = $('.js-affinity-ajax-form');
if ($affinityAjaxForm.length) {
  init_ajax_form();
}

/**
 * Initialize submission of Affinity form
 */
function init_ajax_form() {
  console.log('init_ajax_form');

  $(document).on('forminvalid.zf.abide', function (ev, $form) {
    console.log('Affinity.js : Form ' + ev.currentTarget.name + ' is invalid');
  });

  init_input_form();

  $(document).on('submit', '.js-affinity-ajax-form', function (ev) {
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
            $form.replaceWith(jqXHR.responseJSON.form);
            init_input_form();
          }

          /*$('.form_error').html(jqXHR.responseJSON.message);*/

        } else {
          alert(errorThrown);
        }

      });
  });
}

/**
 * Initialize specific behaviors on input and submit
 */
function init_input_form() {

  /* Abide = JS input validation */
  const $abide = $('form.js-affinity-ajax-form');

  $abide.each((inddex, abide) => {
    return new Abide($(abide));
  });

  // Selectable radio
  initRadioDeselectableInputs();

  // Set the right default submit button when hitting ENTER key
  $('form.js-affinity-ajax-form input[type!=submit]').keydown(function (event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      $('form.js-affinity-ajax-form .js-default_submit[type=submit]').click();
    }
  });

  // Decide which submit button is clicked
  $('form.js-affinity-ajax-form [type=submit]').on('click', function () {
    $('[type=submit]', $(this).parents('form')).removeAttr('clicked');
    $(this).attr('clicked', 'clicked');
  });

  $('form.js-affinity-ajax-form [type=submit][formnovalidate]').on('click', function () {
    console.log('formnovalidate clicked');
    $($(this).parents('form')).foundation('destroy');
  });
}