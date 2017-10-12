/* ===========================================================================
   ResponsiveDom
   =========================================================================== */

import $ from 'jquery';
import responsiveDom from 'ResponsiveDom';

const $navigation = '#js-navigation';
const $offCanvasNavigation = '#js-off-canvas-navigation';

$($navigation).responsiveDom({
	appendTo: $offCanvasNavigation,
  mediaQuery: '(max-width: 1023px)',
  callback: (matched) => {
    matched && $($navigation).toggleClass('show-for-large');
  }
});
