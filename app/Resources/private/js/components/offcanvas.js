/* ===========================================================================
   Offcanvas
   =========================================================================== */

import $ from 'jquery';
import {
  $body,
  $offCanvas
} from '../settings/settings';

const offCanvasFixed = () => {
  $($offCanvas).on('opened.zf.offcanvas', () => {
    $body.style.overflow = 'hidden';
  }).on('closed.zf.offcanvas', () => {
    $body.style.overflow = 'auto';
  });
};

export default offCanvasFixed;
