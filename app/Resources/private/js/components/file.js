/* ===========================================================================
   File
   =========================================================================== */

import {
  hideClass
} from '../settings/settings.js';

const $filePreview = document.querySelectorAll('.js-file-preview');

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
  constructor(input, image, button) {
    this.input = input;
    this.image = image;
    this.button = button;
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

    reader.onload = (event) => {
      // Load data image
      const data = event.target.result;
      // And show in src attribute
      this.image.setAttribute('src', data);
      this._showButtonRemove();
    };

    reader.readAsDataURL(input.files[0]);
  }

  /**
   * Clear input value and reset image preview
   *
   * @memberof FilePreview
   */
  clear() {
    this.input.value = '';
    this.image.setAttribute('src', this.defaultImage);
    this._hideButtonRemove();
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
   *
   *
   * @memberof FilePreview
   */
  clone() {
    console.log('Clone !');
  }

}

if ($filePreview) {

  [...$filePreview].forEach((file) => {
    const $image = file.parentNode.querySelector('.js-file-preview-image');
    const $input = file.parentNode.querySelector('.js-file-preview-input');
    const $button = file.parentNode.querySelector('.js-file-preview-remove');
    const preview = new FilePreview($input, $image, $button);

    file.addEventListener('change', (event) => {
      preview.readURL(event.target);
    });

    $button.addEventListener('click', () => {
      preview.clear();
    });

  });

}
