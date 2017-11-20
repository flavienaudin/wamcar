/* ===========================================================================
   Select
   =========================================================================== */

import {
  disabledClass
} from '../settings/settings.js';

const $toggleId = document.querySelectorAll('[data-toggle-id]');

[...$toggleId].forEach((select) => {
  const id = select.getAttribute('data-toggle-id');
  const dataFetchUrl = select.getAttribute('data-fetch-url');

  select.addEventListener('change', () => {
    let value = select.value;
    let target = document.getElementById(id);
    target.classList.add(disabledClass);

    let targetSelect = target.getElementsByTagName('select')[0];
    let targetOptions = targetSelect.getElementsByTagName('option');
    for (let index in targetOptions) {
      targetSelect.remove(targetOptions[index]);
    }

    fetch(dataFetchUrl.replace('%value%', select.value), {
      credentials: 'include',
      headers: new Headers({
        'X-Requested-With': 'XMLHttpRequest'
      })
    }).then(response => response.json())
      .then((data) => {
        for (let key in data) {
          if (data.hasOwnProperty(key)) {
            let option = document.createElement('option');
            option.text = data[key];
            option.value = key;
            targetSelect.add(option);
          }
        }

        !value ? target.classList.add(disabledClass) : target.classList.remove(disabledClass);
      })
      .catch(err => {
        throw err;
      });
  });
});
