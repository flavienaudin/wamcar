/* ===========================================================================
   Select
   =========================================================================== */

import {
  disabledClass
} from '../settings/settings.js';

const $togglePropDisabled = document.querySelectorAll('[data-toggle-prop-disabled]');

[...$togglePropDisabled].forEach((select) => {
  const id = select.getAttribute('data-toggle-prop-disabled');

  select.addEventListener('change', () => {
    const value = select.value;
    const element = document.getElementById(id);
    !value ? element.classList.add(disabledClass) : element.classList.remove(disabledClass);
  });
});
