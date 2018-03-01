/* ===========================================================================
   File
   =========================================================================== */

import $ from 'jquery';
import {
  hideClass
} from '../settings/settings.js';

const $filePreview = document.querySelectorAll('.js-file-preview');
const $picturesList = document.getElementById('js-pictures-list');

if ($picturesList) {

  const defaultThumbnailSrc = $picturesList.getAttribute('data-preview-src');
  let nbEmptyPicture;
  let nbFullPicture;

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
      this.defaultImage = defaultThumbnailSrc;
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
        this.image.setAttribute('src', this.image.getAttribute('data-default-src'));
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
      let $defaultPreview = $picturesList.getAttribute('data-prototype');

      if ($defaultPreview) {
        $defaultPreview = $defaultPreview.replace(/__name__/g, newValue);
        $picturesList.insertAdjacentHTML('beforeend', $defaultPreview);

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

        if (fileCount >= fileRequired) {
          //preview.clone(fileIndex + 1);
        }
      });

      $button.addEventListener('click', () => {
        preview.clear().then((data) => {
          fileCount--;
        });
      });
      addCloneIfNecessary();
    });

    [...document.querySelectorAll('.js-file-preview-remove')].forEach((button) => {
      button.addEventListener('click', function () {
        const $parent = this.closest('.js-file-index');
        $parent.querySelector('.js-file-preview-input').value = '';
        $parent.querySelector('.js-file-preview-image').setAttribute('src', $parent.querySelector('.js-file-preview-image').getAttribute('data-default-src'));
        $parent.querySelector('.js-file-remove-input').checked = true;
        this.classList.add(hideClass);
        fileCount--;
      });
    });

    [...document.querySelectorAll('.js-file-preview-input')].forEach((file) => {
      file.addEventListener('change', function () {
        const $parent = this.closest('.js-file-index');
        $parent.querySelector('.js-file-remove-input').checked = false;
        fileCount++;
        addCloneIfNecessary();
      });
    });

    function addCloneIfNecessary()
    {
      countEmptyPicture();
      if (nbEmptyPicture < 2) {
        const $file = document.querySelector('.js-file-preview');
        const preview = new FilePreview($file);
        preview.clone(nbFullPicture + 1);
      }
    }

    function countEmptyPicture() {
      nbEmptyPicture = 0;
      nbFullPicture = 0;

      [...document.querySelectorAll('.js-file-preview-image')].forEach((preview) => {
        if (preview.getAttribute('src') === preview.getAttribute('data-default-src')) {
          nbEmptyPicture++;
        } else {
          nbFullPicture++;
        }
      });
    }
  }
}
