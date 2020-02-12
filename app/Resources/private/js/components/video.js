/* ===========================================================================
   Videos
   =========================================================================== */

// Vidéos Inserts

import * as Toastr from 'toastr';

const $showMoreVideos = $('.js-show-more-videos');
if ($showMoreVideos.length > 0) {
  $showMoreVideos.each((index, element) => {
    let $buttonShowMore = $(element);
    $buttonShowMore.click(() => {
      const href = $buttonShowMore.data('href');
      $.ajax({
        url: href
      }).done(function (success) {
        let videosInsertContainer = $buttonShowMore.parents('[id^="jsVideoInsertVideos-"]')[0];
        if (videosInsertContainer) {
          let $videosContainer = $(videosInsertContainer).find('.js-videosinsert-videos');
          $videosContainer.append(success.videosHtml);
          initYoutubePlayer($videosContainer.find('.youtube-player'));
          if(success.showMoreVideosLink == null) {
            $buttonShowMore.remove();
          }else{
            $buttonShowMore.data('href', success.showMoreVideosLink);
            $buttonShowMore.html(success.showMoreVideosText);
          }
        }
      }).fail(function (jqXHR, textStatus) {
        Toastr.warning(jqXHR.responseJSON);
      });
    });
  });
}

/*
   Initialize light embedded youtube video
   ===================================== */
initYoutubePlayer($('.youtube-player'));

/*
   Light embeded youtube video : functions
   ===================================== */
function initYoutubePlayer(elements) {
  $(elements).each((n, element) => {
    let div = document.createElement('div');
    div.setAttribute('data-id', element.dataset.id);
    div.innerHTML = labnolThumb(element.dataset.id);
    div.onclick = labnolIframe;
    element.appendChild(div);
  });
}

function labnolThumb(id) {
  let thumb = '<img src="https://i.ytimg.com/vi/ID/hqdefault.jpg">',
    play = '<div class="play"></div>';
  return thumb.replace('ID', id) + play;
}

function labnolIframe(event) {
  let iframe = document.createElement('iframe');
  let embed = 'https://www.youtube.com/embed/ID?rel=0&autoplay=1';
  iframe.setAttribute('src', embed.replace('ID', this.dataset.id));
  iframe.setAttribute('frameborder', '0');
  iframe.setAttribute('allowfullscreen', '1');
  this.parentNode.replaceChild(iframe, this);
}