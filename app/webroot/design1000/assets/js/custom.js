$(document).ready(function() {
    'use strict';

    /**
     * Properties Carousel
     */
    if ($.fn.owlCarousel) {
        var listingCarousel = $('.listing-carousel');
        if (listingCarousel.length > 0) {
            listingCarousel.owlCarousel({
                items: 4,
                margin: 30,
                nav: true,
                navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
                responsive: {
                    0: {
                        items: 1
                    },
                    540: {
                        items: 2
                    },
                    766: {
                        items: 2
                    },
                    990: {
                        items: 3
                    },
                    1200: {
                        items: 4
                    }
                }
            });
        }

        /**
         * Cover carousel
         */
        var coverCarousel = $('.cover-carousel');
        if (coverCarousel.length > 0) {
            coverCarousel.owlCarousel({
                items: 1,
                nav: true,
                navText: ['<img src="design1000/assets/img/lnr-chevron-left.svg" alt="">', '<img src="design1000/assets/img/lnr-chevron-right.svg" alt="">']
            });
        }

        /**
         * Image gallery
         */
        var galleryCarousel = $('.gallery');
        if (galleryCarousel.length > 0) {
            galleryCarousel.owlCarousel({
                autoplay: 3000,
                items: 1,
                nav: true,
                navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>']
            });
        }
    }

    /**
     * Customizer
     */
    $('.customizer-title').on('click', function() {
        $('.customizer').toggleClass('open');
    });

    $('.customizer a').click('click', function(e) {
        e.preventDefault();

        var cssFile = $(this).attr('href');
        $('#css-primary').attr('href', cssFile);
    });

    // Magnific Popup
    var imagePopup = $('.image-popup');
    if (imagePopup.length > 0 && $.fn.magnificPopup) {
        imagePopup.magnificPopup({
            type: 'image',
            removalDelay: 300,
            mainClass: 'mfp-fade',
            overflowY: 'scroll'
        });
    }
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Google Map - Property Detail
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function initMap(_latitude, _longitude, _sicon) {
    if (typeof google === 'undefined' || !google.maps || !document.getElementById('property-detail-map')) {
        return;
    }

    var myLatLng = {lat: _latitude, lng: _longitude};

    var map = new google.maps.Map(document.getElementById('property-detail-map'), {
        zoom: 15,
        center: myLatLng
    });

    var marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
        icon: _sicon
    });
}