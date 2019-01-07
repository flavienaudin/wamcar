/* ===========================================================================
   Carousel
   =========================================================================== */

import $ from 'jquery';
import slick from 'slick-carousel';

const $carousel = document.querySelector('.js-carousel');

if ($carousel) {

  const $carouselVehicle = document.getElementById('js-carousel-vehicle');
  if ($carouselVehicle) {
    const $carouselNavigation = document.getElementById('js-carousel-navigation-vehicle');

    $($carouselVehicle).slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      fade: false,
      infinite: false,
      asNavFor: '#js-carousel-navigation-vehicle'
    });

    if ($carouselNavigation) {
      $($carouselNavigation).slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        asNavFor: '#js-carousel-vehicle',
        nextArrow: '<button type="button" class="carousel-button next icon-arrow-right-2 slick-next"><span class="show-for-sr">Suivant</span></button>',
        prevArrow: '<button type="button" class="carousel-button prev icon-arrow-left-2 slick-prev"><span class="show-for-sr">Précédent</span></button>',
        dots: false,
        focusOnSelect: true,
        infinite: false,
        responsive: [
          {
            breakpoint: 1025,
            settings: {
              slidesToShow: 5
            }
          },
          {
            breakpoint: 640,
            settings: {
              slidesToShow: 4
            }
          }
        ]
      });
    }
  }

  const $modalCarouselVehicle = document.getElementById('js-carousel-vehicle-modal');
  if ($modalCarouselVehicle) {
    $('#js-modal-carousel-vehicle').on('open.zf.reveal', () => {
      // Wait modal to be completely opened
      setTimeout(function(){
        $($modalCarouselVehicle).slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          nextArrow: '<button type="button" class="carousel-button next icon-arrow-right-2 slick-next"><span class="show-for-sr">Suivant</span></button>',
          prevArrow: '<button type="button" class="carousel-button prev icon-arrow-left-2 slick-prev"><span class="show-for-sr">Précédent</span></button>',
          fade: false,
          infinite: true
        });
      }, 100);
    });
  }
}
