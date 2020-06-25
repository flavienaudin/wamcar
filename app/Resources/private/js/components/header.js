/* ===========================================================================
   Header
   =========================================================================== */

import {$header} from '../settings/settings.js';
import {debounce} from './utils';

const $vehicleForm = document.getElementById('js-vehicle-form');

if ($vehicleForm) {
  const transparentClass = 'is-transparent';
  const scrollLimit = 30;

  document.addEventListener('scroll', () => {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    currentScroll > scrollLimit ? $header.classList.remove(transparentClass) : $header.classList.add(transparentClass);
  });
}

/* Graphic trick : on rend visible les liens de l'entête une fois le JS chargé */
$('.l-navigation.is-hidden').removeClass('is-hidden');


/*
TODO : nouvelle barre de recherche : A supprimer

const $headerSearchForm = $('#header-search-form');
if ($headerSearchForm.length) {

  let $submitActor = null;
  const $submitActors = $headerSearchForm.find('button[type=submit]');

  // Update the fields'name according to submit action
  $headerSearchForm.on('submit', () => {
    if (null === $submitActor) {
      // select the first submit if no button explicitly clicked
      $submitActor = $submitActors[0];
    }
    $headerSearchForm.attr('action', $submitActor.data('action'));
    $headerSearchForm.find('input').each((index, elt)=>{
      $(elt).attr('name', $(elt).attr('name').replace('search_vehicle', $submitActor.data('input-name')));
    });
    return true;
  });

  $headerSearchForm.find('button').on('click', (evt) => {
    $submitActor = $(evt.currentTarget);
  });
}
*/

/**
 * Formulaire de recherche des conseillers & Voitures dans l'en-tête
 */

const $advisorsHeaderSearchForm = $('#advisors-header-search-form');
if ($advisorsHeaderSearchForm.length) {

  const $searchField = $advisorsHeaderSearchForm.find('#advisor-header-search-text');
  const $keywordsCloudContainer = $advisorsHeaderSearchForm.find('.js-keywords-cloud-container');
  const $keywordsCloudLoaderContainer = $keywordsCloudContainer.find('.loader-container');
  const updateKeywordsCloud = (event) => {
    const autocompleteUrl = $(event.currentTarget).data('autocomplete-url');
    let termQuery = $searchField.val();
    $keywordsCloudLoaderContainer.removeClass('is-hidden');
    $keywordsCloudContainer.children('.tag-container').html('');
    $keywordsCloudContainer.removeClass('is-hidden');
    removeClickListener();
    $.ajax({
      url: autocompleteUrl,
      method: 'POST',
      data: {'term': termQuery}
    }).done(function (success) {
      if (success.hasOwnProperty('html')) {
        $keywordsCloudLoaderContainer.addClass('is-hidden');
        $keywordsCloudContainer.children('.tag-container').html(success.html);
        document.addEventListener('click', outsideClickListener);
      }
    }).fail(function (jqXHR, textStatus) {
      console.log('fail', jqXHR, textStatus);
    });
  };

  $searchField.on('focus', (event) => {
    if ($keywordsCloudContainer.hasClass('is-hidden')) {
      updateKeywordsCloud(event);
    }
  });
  $searchField.on('input', debounce(updateKeywordsCloud, 500));

  const outsideClickListener = (event) => {
    const $target = $(event.target);
    if (!$target.closest('.js-keywords-cloud-container, #advisor-header-search-text').length && $keywordsCloudContainer.is(':visible')) {
      $keywordsCloudContainer.addClass('is-hidden');
      removeClickListener();
    }
  };

  const removeClickListener = () => {
    document.removeEventListener('click', outsideClickListener);
  };




}