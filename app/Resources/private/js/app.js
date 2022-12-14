/* ===========================================================================
   Import
   =========================================================================== */

// CSS
import '../scss/app.scss';
// JS
import $ from 'jquery';
import {Abide} from 'foundation-sites/js/foundation.abide';
import {OffCanvas} from 'foundation-sites/js/foundation.offcanvas';
import {Toggler} from 'foundation-sites/js/foundation.toggler';
import {Reveal} from 'foundation-sites/js/foundation.reveal';
import {Tabs} from 'foundation-sites/js/foundation.tabs';
import {Magellan} from 'foundation-sites/js/foundation.magellan';
import {Tooltip} from 'foundation-sites/js/foundation.tooltip';
import {Orbit} from 'foundation-sites/js/foundation.orbit';

import {Dropdown} from './foundation/foundation.override.dropdown'; // Overridded version of DropDown
import {DropdownMenu} from 'foundation-sites/js/foundation.dropdownMenu';
import 'linkifyjs';
import 'waypoints/lib/noframework.waypoints';
import 'waypoints/lib/shortcuts/inview';
import * as Toastr from 'toastr';

import './components/utils';
import './components/responsiveDom';
import './components/search';
import './components/header';
import './components/select';
import './components/select2';
import './components/step';
import './components/file';
import './components/fileUploader';
import './components/garage';
import './components/registration';
import './components/like';
import './components/banner';
import './components/carousel';
import './components/datepicker';
import './components/city';
import './components/conversation';
import './components/vehicle';
import './components/star';
import './components/radio';
import './components/project';
import './components/form_event';
import './components/notification';
import './components/phone_number';
import './components/affinity';
import './components/datatable';
import './components/sales';
import './components/expert';
import './components/video';
import './components/videoProject';
import './components/videoScript';
import './components/scriptVersionWizardStep';

import scrollTo from './components/scrollTo';

import {default as autosize} from 'autosize';

Reveal.defaults.animationIn = 'slide-in-down';
Reveal.defaults.animationOut = 'fade-out';


/* ===========================================================================
   jQuery
   =========================================================================== */

$(function () {

  /* Off Canvas */

  const $offCanvas = $('[data-off-canvas]');

  $offCanvas.each((index, offcanvas) => {
    return new OffCanvas($(offcanvas));
  });

  /* Reveal */

  const $reveal = $('[data-reveal]');

  $reveal.each((index, reveal) => {
    return new Reveal($(reveal));
  });

  /* Abide */

  const $abide = $('[data-abide]');

  $abide.each((index, abide) => {
    return new Abide($(abide));
  });

  /* Toogle : g??rer par les scrips Foundation */

  /*const $toggles = $('[data-toggle]');
  $toggles.each((index, toggle) => {
    $(toggle).on('click', function () {
      $(this).toggleClass(activeClass);
    });
  });*/

  const $togglers = $('[data-toggler]');

  if ($togglers) {
    $togglers.each((index, toggler) => {
      return new Toggler($(toggler));
    });
  }

  /* Tabs */

  const $tabs = $('[data-tabs]');

  if ($tabs) {
    $tabs.each((index, tabs) => {
      return new Tabs($(tabs));
    });
  }

  /* Tabs */

  const $magellan = $('[data-magellan]');

  if ($magellan) {
    const options = {
      offset: 100
    };

    $magellan.each((index, magellan) => {
      let $currentMagellan = new Magellan($(magellan), options);
      if (window.location.hash) {
        $(magellan).foundation('scrollToLoc', window.location.hash);
      }
      return $currentMagellan;
    });
  }

  /* Orbit */

  const $orbits = $('.orbit');
  if ($orbits.length) {
    $orbits.each((index, orbit) => {
      new Orbit($(orbit));
    });
  }

  /* Tooltips */

  const $tooltip = $('[data-tooltip]');

  if ($tooltip) {
    const options = {};

    $tooltip.each((index, tooltip) => {
      return new Tooltip($(tooltip), options);
    });
  }

  /* Dropdown */
  const $dropdown = $('[data-dropdown]');
  if ($dropdown) {
    const options = {};

    $dropdown.each((index, dropdown) => {
      new Dropdown($(dropdown), options);
    });
  }

  /* Dropdown menu */
  const $dropdownMenu = $('[data-dropdown-menu]');
  if ($dropdownMenu) {
    const options = {
      'data-disable-hover': true,
      'data-autoclose': false
    };

    $dropdownMenu.each((index, dropdown) => {
      new DropdownMenu($(dropdown), options);
    });
  }

  /* Textarea autosize */
  autosize($('textarea'));

  /* Toastr */
  const levelToFunction = {
    'success': 'success', 'warning': 'warning', 'alert': 'error'
  };
  Toastr.options = {
    'positionClass': 'toast-top-full-width',
    'timeOut': '5000',
    'extendedTimeOut': '3000'
  };
  const $flashes = $('.js-flash-message');
  if ($flashes) {
    $flashes.each((index, flash) => {
      let flashLevel = $(flash).data('level');
      let flashMessage = $(flash).data('message');
      Toastr[levelToFunction[flashLevel]](flashMessage);
    });
  }

  /* Form invalid */

  const $form = $('#js-register-form, #js-scriptversion-form, form[data-abide]');

  $form.on('forminvalid.zf.abide', function (e) {
    let invalidFields = $(this).find('[data-invalid]');
    if (invalidFields) {
      let scrollTo = $('#' + invalidFields[0].id).offset().top - 280;

      $('html, body').animate({
        scrollTop: scrollTo
      }, 400);
    }
  });

  /* Form valid */

  const $registerSimpleForm = $('#js-register-simple-form');
  if ($registerSimpleForm) {
    $registerSimpleForm.on('formvalid.zf.abide', () => {
      $('#register_submit').addClass('loader-visible');
    });

  }

  /*
     Conversation : update last opened
     ===================================== */
  const $lastMessage = $('#last-message');
  if ($lastMessage.length > 0) {
    let w = new Waypoint.Inview({
      element: document.getElementById('last-message'), //$lastMessage,
      enter: function (direction) {
        const href = $lastMessage.data('conversation-open-url');
        fetch(href).then((response) => {
        });
        this.destroy();
      }
    });
  }

  $('#message_send').on('click', function () {
    $(this).addClass('loader-visible');
  });

  /*
    Confirm box
    ===================================== */
  $('a.js-confirm-box').on('click', (e) => {
    e.preventDefault();
    const href = e.currentTarget.href,
      id = $(e.currentTarget).data('id'),
      title = $(e.currentTarget).data('title'),
      message = $(e.currentTarget).data('message');

    confirm(title, message, id, (param) => {
      window.location = param.href;
    }, {'href': href});
  });


  const $landingRegistration = $('#js-landing-orientation');
  if ($landingRegistration.length > 0) {
    $landingRegistration.find('input').on('click', (e) => {
      $landingRegistration.submit();
    });
  }

  /*
   * Scroll here : Scroll to DOM element
   ===================================== */
  const $scrollToNow = $('[data-scroll-to-now]');
  if ($scrollToNow.length > 0) {
    scrollTo('#' + $($scrollToNow[0]).data('scroll-to-now'));
  }

  /*
     ScrollTo : A v??rifier lors de la premi??re utilisation
     ===================================== */
  /*const $scrollTo = document.querySelectorAll('[data-scroll-to]');
  [...$scrollTo].forEach((item) => {
    item.addEventListener('click', () => {
      const $target = item.getAttribute('data-scroll-to');

      setTimeout(() => scrollTo($target), 100);
    });
  });*/

});


/*
   Confirm box : functions
   ===================================== */
export function confirm(title, message, id, callback, callbackParam) {
  let modal =
    '<div class="reveal small" id="' + id + '" data-reveal>' +
    '<header class="off-canvas-header">' +
    '<strong>' + title + '</strong>' +
    '<button class="small-right icon-close" data-close><span class="show-for-sr">Close</span></button>' +
    '</header>' +
    '<div class="modal-content row">' +
    '<p class="lead">' + message + '</p>' +
    '<p class="full-width is-flex align-spaced">' +
    '<button class="button white yes">Oui</button>' +
    '<button class="button" data-close>Non</button></p>' +
    '</div>' +
    '</div>';
  $('body').append(modal);

  let $modal = $('#' + id);
  let confirmation = new Reveal($modal);
  confirmation.open();
  $modal.find('.yes').on('click', () => {
    confirmation.close();
    $('#' + id).parent().remove();
    callback(callbackParam);
  });
  $(document).on('closed.zf.reveal', '#' + id, () => {
    $('#' + id).parent().remove();
  });
}
