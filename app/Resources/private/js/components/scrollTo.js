/* ===========================================================================
   ScrollTo
   =========================================================================== */

import anim from 'animated-scrollto';


/**
* Scroll to element with animation
* @function
*/
export default (selector) => {
  const $element = document.querySelector(selector);
  const scrollTop = $element.getBoundingClientRect().top - document.body.getBoundingClientRect().top - 80;
  anim(document.body, scrollTop, 700);
  anim(document.documentElement, scrollTop, 700);
};
