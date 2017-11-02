/* ===========================================================================
   Like
   =========================================================================== */

import { activeClass } from '../settings/settings';

const $like = document.querySelectorAll('.js-like');

if ($like) {

  [...$like].forEach((like) => {

    like.addEventListener('click', (event) => {
      const $this = event.target;
      // for Ajax request
      const href = $this.getAttribute('href');
      // Run fetch(href).then(data => console.log(data));
      // And add this on .then();
      $this.classList.toggle(activeClass);

      event.preventDefault();
    });

  });

}
