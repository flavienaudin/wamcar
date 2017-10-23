/* ===========================================================================
   Select
   =========================================================================== */

import {
  disabledClass
} from '../settings/settings.js';

const $toggleId = document.querySelectorAll('[data-toggle-id]');

[...$toggleId].forEach((select) => {
  const id = select.getAttribute('data-toggle-id');

  select.addEventListener('change', () => {
    const value = select.value;
    const element = document.getElementById(id);
    !value ? element.classList.add(disabledClass) : element.classList.remove(disabledClass);
  });
});
