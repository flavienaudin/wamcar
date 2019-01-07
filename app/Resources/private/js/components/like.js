/* ===========================================================================
   Like
   =========================================================================== */

import {activeClass} from '../settings/settings';

const $like = document.querySelectorAll('.js-like');

if ($like) {
  [...$like].forEach((like) => {
    /*like.addEventListener('click', (event) => {
      event.preventDefault();
      const $this = event.target;
      // for Ajax request
      const href = $this.getAttribute('data-href');
      fetch(href).then((response) => {
        $like.forEach((allLike) => {
          allLike.classList.toggle(activeClass);
        });
      });
    });*/
  });
}
