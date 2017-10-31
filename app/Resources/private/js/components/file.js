/* ===========================================================================
   File
   =========================================================================== */

import $ from 'jquery';
import {
  hideClass
} from '../settings/settings.js';

const $filePreview = document.querySelectorAll('.js-file-preview');
const $picturesList = document.getElementById('js-pictures-list');

/**
 * File Preview
 *
 * @class FilePreview
 */
class FilePreview {

  /**
   * Creates an instance of FilePreview.
   * @memberof FilePreview
   */
  constructor(file) {
    this.file = file;
    this.input = this.file.querySelector('.js-file-preview-input');
    this.image = this.file.querySelector('.js-file-preview-image');
    this.button = this.file.querySelector('.js-file-preview-remove');
    this.legend = this.file.parentNode.querySelector('.js-file-legend');
    this.defaultImage = this.image.getAttribute('src');
  }

  /**
   * Return input value on base 64
   *
   * @param {DOM element} input
   * @memberof FilePreview
   */
  readURL(input) {
    const reader = new FileReader();

    return new Promise((resolve) => {
      reader.onload = (event) => {
        // Load data image
        const data = event.target.result;
        // And show in src attribute
        this.image.setAttribute('src', data);
        this._showButtonRemove();
      };

      reader.readAsDataURL(input.files[0]);
      resolve(input.value);
    });
  }

  /**
   * Clear input value and reset image preview
   *
   * @memberof FilePreview
   */
  clear() {
    return new Promise((resolve) => {
      this.input.value = '';
      if (this.legend) {
        this.legend.value = '';
      }
      this.image.setAttribute('src', this.defaultImage);
      resolve(this._hideButtonRemove());
    });
  }

  /**
   * Show button for clear item
   *
   * @private
   * @returns
   * @memberof FilePreview
   */
  _showButtonRemove() {
    return this.button.classList.remove(hideClass);
  }

  /**
   * Hide button when item is clear
   *
   * @private
   * @returns
   * @memberof FilePreview
   */
  _hideButtonRemove() {
    return this.button.classList.add(hideClass);
  }

  /**
   * Clone default file preview
   *
   * @memberof FilePreview
   */
  clone(newValue) {
    const $defaultPreview = document.getElementById('js-file-preview-default');

    if ($defaultPreview) {
      const $clone = $defaultPreview.cloneNode(true);

      const $elements = [
        $clone.querySelector('.js-file-preview-label'),
        $clone.querySelector('.js-file-legend'),
        $clone.querySelector('.js-file-preview-input'),
        $clone.querySelector('.js-file-preview-image-container')
      ];

      $elements.forEach(element => this._updateValue(element, newValue));

      $clone.removeAttribute('id');
      $clone.classList.remove(hideClass);
      $picturesList.appendChild($clone);
    } else {
      return;
    }
  }

  /**
   *
   *
   * @private
   * @param {DOM element} element
   * @param {string} value
   * @param {string} newValue
   * @returns
   * @memberof FilePreview
   */
  _updateValue(element, newValue) {
    const forAttr = element.getAttribute('for');
    const nameAttr = element.getAttribute('name');
    const oldValue = forAttr ? forAttr : nameAttr;

    // If label element
    forAttr && element.setAttribute('for', `${oldValue}-${newValue}`);

    // And if input element
    if (nameAttr) {
      element.setAttribute('id', `${oldValue}-${newValue}`);
      element.setAttribute('name', `${oldValue}-${newValue}`);
    }
  }

}

if ($picturesList) {
  // Selector for count the number of default input
  const fileRequired = document.querySelectorAll('.js-file-required').length;
  // Set array for test if all default input has value
  // const $files = [];
  let fileCount = 0;

  /**
   * Change on picture list selector
   */
  $picturesList.addEventListener('change', (event) => {
    // Selector for count all file preview (default + clone)
    const fileIndex = document.querySelectorAll('.js-file-index').length;

    const $file = event.target.closest('.js-file-preview');
    const $button = $file.parentNode.querySelector('.js-file-preview-remove');
    const preview = new FilePreview($file);

    preview.readURL(event.target).then((data) => {
      fileCount++;

      console.log(fileCount);

      if (fileCount >= fileRequired) {
        preview.clone(fileIndex + 1);
      }
    });

    $button.addEventListener('click', () => {
      preview.clear().then((data) => {
        fileCount--;
        console.log(fileCount);
      });
    });
  });

}
