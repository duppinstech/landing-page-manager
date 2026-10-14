jQuery(function($) {
	document.querySelectorAll('a[href^="#"]').forEach(anchor => {
		anchor.addEventListener('click', function(e) {
			e.preventDefault();  // Prevents any default scrolling or refresh issues

			const target = document.querySelector(this.getAttribute('href'));
			if (target) {
				target.scrollIntoView({
					behavior: 'smooth' // Optional: for smooth scrolling effect
				});
			}
		});
	});
	
    /** open contact form on link START **/
    if( $('.js-contact-form').length ){
        $('.js-contact-form').on('click', function () {
            $('#contact_form').modal('show');
        });
    }
    /** open contact form on link END **/

    $.browser.safari = ($.browser.webkit && !(/chrome/.test(navigator.userAgent.toLowerCase())));

    if ($.browser.safari) {
        $('body').addClass('is_safari');
    }

    window.wow = new WOW();
    wow.init();

    $('.hero.section .section__layer').each(function () {
        var attr = $(this).data('bg-videos');
        if( (typeof attr !== typeof undefined && attr !== false) && attr != 'false'){

            var bgVideos = $(this).data('bg-videos');

            if ( bgVideos ) {
                var bgVideo = bgVideos[Math.floor(Math.random() * bgVideos.length)];

                if( bgVideo.bg_video_poster ) {
                    if(bgVideo.bg_video){
                        $(this).find('video').attr('src', bgVideo.bg_video).attr('poster', bgVideo.bg_video_poster);
                    }else{
                        $(this).css('background-image','url(' + bgVideo.bg_video_poster + ')');
                    }
                }
                if( bgVideo.modal_youtube_link ){
                    $($(this).find('.js-video-play').data('target')).find('iframe').attr('src', bgVideo.modal_youtube_link);
                }
            }
        }
    });

    $('.how_we_can_help .tabs_wrapper .tab').on('click', function () {

        if( !$(this).hasClass('active') ){
            $('.how_we_can_help .tabs_wrapper .tab').removeClass('active');
            $('.how_we_can_help .tab_content').removeClass('active');
            $(this).addClass('active');
            $('.how_we_can_help .tab_content.tab_content_' + $(this).attr('data-index') ).addClass('active');
        }

    });
    $('.how_we_can_help .mobile_tab').on('click', function () {
        if( !$(this).hasClass('active') ){
            $('.how_we_can_help .tabs_content_wrapper .mobile_tab').removeClass('active');
            $('.how_we_can_help .tab_content').removeClass('active');
            $(this).addClass('active');
            $('.how_we_can_help .tab_content.tab_content_' + $(this).attr('data-index') ).addClass('active');
        }
    });

    $('.how_we_can_help .arrows .arrow').on('click', function () {
        var elementFotTrigger = $('.how_we_can_help .tabs_wrapper .tab.active').prev();
        if( $(this).hasClass('right') ){
            elementFotTrigger = $('.how_we_can_help .tabs_wrapper .tab.active').next();
        }

        if( elementFotTrigger.length <= 0 ){
            elementFotTrigger = $('.how_we_can_help .tabs_wrapper .tab:last-child');
            if( $(this).hasClass('right') ){
                elementFotTrigger = $('.how_we_can_help .tabs_wrapper .tab:first-child');
            }
        }

        elementFotTrigger.trigger('click');
    });


    if ( $(".js_reviews_slider").length ) {
        var reviewsSwiper = new Swiper('.js_reviews_slider', {
            spaceBetween: 8,
            slidesPerView: 1,
            slidesPerGroup: 1,
            followFinger: false,
            autoplay: {
                delay: 10000,
            },
            speed: 1500,
            navigation: {
                nextEl: '.reviews .swiper-button-next',
                prevEl: '.reviews .swiper-button-prev',
            },
            pagination: {
                el: '.reviews .swiper-pagination',
                type: 'fraction',
            },
            breakpoints: {
                630: {
                    spaceBetween: 8,
                    slidesPerView: 2,
                    slidesPerGroup: 1,
                    loopedSlides: 18,
                },
                1200: {
                    spaceBetween: 8,
                    slidesPerView: 3,
                    slidesPerGroup: 1,
                },
                1300: {
                    spaceBetween: 8,
                    slidesPerView: 4,
                    slidesPerGroup: 1,
                },
                1530: {
                    spaceBetween: 8,
                    slidesPerView: 5,
                    slidesPerGroup: 1,
                }
            }
        });
    }

    if ( $(".team_slider").length ) {
        var reviewsSwiper = new Swiper('.team_slider', {
            spaceBetween: 0,
            slidesPerView: 1,
            slidesPerGroup: 1,
            loop: true,
            followFinger: false,
            autoplay: {
                delay: 10000,
            },
            speed: 1500,
            navigation: {
                nextEl: '.our_team .swiper-button-next',
                prevEl: '.our_team .swiper-button-prev',
            },
            pagination: {
                el: '.our_team .swiper-pagination',
                type: 'fraction',
            },
            breakpoints: {
                630: {
                    spaceBetween: 35,
                    slidesPerView: 2,
                    slidesPerGroup: 1,
                },
                1200: {
                    spaceBetween: 35,
                    slidesPerView: 3,
                    slidesPerGroup: 1,
                },
                1530: {
                    spaceBetween: 35,
                    slidesPerView: 4,
                    slidesPerGroup: 1,
                }
            }
        });
    }

	function scrollTo(hash) {
		location.hash = "#" + hash;
	}
    var geocoder;
    var map;
    var bounds = new google.maps.LatLngBounds();

    function initialize() {
        //#151418
        var styledMapType = new google.maps.StyledMapType(
            [
                {
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#151418"
                        }
                    ]
                },
                {
                    "elementType": "labels.icon",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                        }
                    ]
                },
                {
                    "elementType": "labels.text.stroke",
                    "stylers": [
                        {
                            "color": "#212121"
                        }
                    ]
                },
                {
                    "featureType": "administrative",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#757575"
                        }
                    ]
                },
                {
                    "featureType": "administrative.country",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                        }
                    ]
                },
                {
                    "featureType": "administrative.land_parcel",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "administrative.locality",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#bdbdbd"
                        }
                    ]
                },
                {
                    "featureType": "administrative.neighborhood",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "labels.text",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                        }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#181818"
                        }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#616161"
                        }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "labels.text.stroke",
                    "stylers": [
                        {
                            "color": "#1b1b1b"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry.fill",
                    "stylers": [
                        {
                            "color": "#2c2c2c"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "labels",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#8a8a8a"
                        }
                    ]
                },
                {
                    "featureType": "road.arterial",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#1C1B20"
                        }
                    ]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#1C1B20"
                        }
                    ]
                },
                {
                    "featureType": "road.highway.controlled_access",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#1C1B20"
                        }
                    ]
                },
                {
                    "featureType": "road.local",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#1C1B20"
                        }
                    ]
                },
                {
                    "featureType": "transit",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#000000"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "labels.text",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#3d3d3d"
                        }
                    ]
                }
            ]
        );

        var lat = 34.1406544;
        var lang = -84.2392474;
        if( $(window).width() < 992 ){
            lat = 34.2414069
            lang = -84.4295848;
        }
        var zoom = 10;
        if( $(window).width() < 1399 && $(window).width() > 989 ){
            zoom = 10;
        }
        map = new google.maps.Map(
            document.getElementById("map_canvas"), {
                center: new google.maps.LatLng(lat, lang),
                zoom: zoom,
                fitBounds: false,
                draggable: true,
                /*zoomControl: true,
                scaleControl: true,*/
                mapTypeId: google.maps.MapTypeId.ROADMAP,
            });

        map.mapTypes.set('styled_map', styledMapType);
        map.setMapTypeId('styled_map');

        geocoder = new google.maps.Geocoder();

        for (i = 0; i < locations.length; i++) {


            geocodeAddress(locations[i], i);
        }
    }
    if( $('#map_canvas').length > 0 ){
        google.maps.event.addDomListener(window, "load", initialize);
    }

    function createMapMarker(options) {
        var hasMapId = options.map && typeof options.map.get === 'function' && !!options.map.get('mapId');
        var canUseAdvanced = hasMapId && google.maps.marker && google.maps.marker.AdvancedMarkerElement;
        if (canUseAdvanced) {
            var advancedOptions = {
                map: options.map,
                position: options.position,
                title: options.title || ''
            };
            if (options.icon) {
                var iconEl = document.createElement('img');
                iconEl.src = options.icon;
                iconEl.alt = options.title || '';
                iconEl.style.width = '40px';
                iconEl.style.height = '40px';
                advancedOptions.content = iconEl;
            }
            return new google.maps.marker.AdvancedMarkerElement(advancedOptions);
        }
        return new google.maps.Marker(options);
    }

    function bindMarkerClick(marker, callback) {
        if (!marker || typeof callback !== 'function') {
            return;
        }
        if (typeof marker.addEventListener === 'function') {
            marker.addEventListener('gmp-click', callback);
            return;
        }
        if (typeof marker.addListener === 'function') {
            marker.addListener('click', callback);
        }
    }

    function geocodeAddress(locations, i) {
        var title = locations;
        var address = locations;
        geocoder.geocode({
                'address': locations
            },

            function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var marker = createMapMarker({
                        icon: map_icon,
                        map: map,
                        position: results[0].geometry.location,
                        title: title,
                        animation: google.maps.Animation.DROP,
                        address: address,
                    });
                    //infoWindow(marker, map, title, address);
                    bounds.extend(results[0].geometry.location);
                    //map.fitBounds(bounds);
                } else {
                    alert("geocode of " + address + " failed:" + status);
                }
            });
    }

    function infoWindow(marker, map, title, address) {
        bindMarkerClick(marker, function() {
            var html = "<div><h3>" + title + "</h3><p>" + address + "<br></div></p></div>";
            iw = new google.maps.InfoWindow({
                content: html,
                maxWidth: 350
            });
            iw.open(map, marker);
        });
    }

    function createMarker(results) {
        var position = results[0].geometry.location;
        var marker = createMapMarker({
            icon: 'http://maps.google.com/mapfiles/ms/icons/blue.png',
            map: map,
            position: position,
            title: title,
            animation: google.maps.Animation.DROP,
            address: address,
        });
        bounds.extend(position);
        map.fitBounds(bounds);
        infoWindow(marker, map, title, address);
        return marker;
    }

    $(window).scroll(function(){

        if( $(this).scrollTop() > $('header.header').height()){
            $('header.header').addClass('before_stiky');
        }else{
            $('header.header').removeClass('before_stiky');
        }
        if( $(this).scrollTop() > 400){
            $('header.header').addClass('stiky');
        }else{
            $('header.header').removeClass('stiky');
        }

    }).scroll();

    function showMore () {
        if($('.page-template-page-location').length > 0 || $('.single-service').length > 0) {
            $('.hero.inside .description').addClass('description-show');

            let initialHeight = $('.hero.inside .description-show').height();

            const close = () => {
                $('.hero.inside .description-show').css('max-height', initialHeight);
                $('.hero.inside .show_more_wrapper').removeClass('opened');
                setTimeout(()=>{
                    $('.hero.inside .description-show').removeClass('opened');
                },400);
            }

            $('.hero.inside .show_more_wrapper>span').on('click', () => {
                let newHeight = $('.hero.inside .description-show p')[0].scrollHeight;

                if($('.hero.inside .description-show.opened').length > 0) {
                    close();
                } else {
                    $('.hero.inside .description-show').addClass('opened');
                    $('.hero.inside .description-show').css('max-height', newHeight);
                    $('.hero.inside .show_more_wrapper').addClass('opened');
                }
            })

            if(window.matchMedia('(min-width: 1024px)').matches) {
                $(window).resize(()=> {
                    close();
                })
            }

            if(window.matchMedia('(max-width: 1023px)').matches) {
                $(window).on('orientationchange', () => {
                    close();
                })
            }
        }
    }

    $(document).ready(() => {
        showMore();
    })

    $(document).on('click', function(event){
        var container = $(".header__nav .phone_number");
        if(event.originalEvent === undefined){
            return;
        }
        if (!container.is(event.target) &&            // If the target of the click isn't the container...
            container.has(event.target).length === 0 && event.originalEvent.isTrusted) // ... nor a descendant of the container
        {
            $('.header__nav .phone_number').addClass('fadeOut');
            setTimeout(function(){
                $('.header__nav .phone_number').removeClass('show');
                $('.header__nav .phone_number').removeClass('fadeOut');
                $(this).parent().removeClass('animating');

            },500);
            // Do whatever you want to do when click is outside the element
        }
    });

    // $('.header__nav .phone_number>a.open_phones').on('click',function (e) {
    //     e.preventDefault();
    //     if( !$(this).parent().hasClass('animating') ){

    //         $(this).parent().addClass('animating');
    //         if( $(this).parent().hasClass('show') ){

    //             $(this).parent().addClass('fadeOut');
    //             var phone_icon_parent = $(this).parent();
    //             setTimeout(function(){
    //                 phone_icon_parent.removeClass('show');
    //                 phone_icon_parent.removeClass('fadeOut');
    //                 phone_icon_parent.removeClass('animating');
    //             },500);

    //         }else{

    //             $(this).parent().addClass('show');
    //             $(this).parent().removeClass('animating');
    //         }

    //     }
    // });

    $('.menu-item a[href^="#"]').on('click',function(e){
        e.preventDefault();
    });

    $("#register_form").on('show.bs.modal', function( event ){
        var triggerElement = $(event.relatedTarget);
        $('#nf-field-42').val( triggerElement.attr('data-title') );
        $('#nf-field-43').val( triggerElement.attr('data-url') );
    });

    if( $('.load_more_posts').length > 0 ){
        var onload_height = $('.cars_section .articles_wrapper>.row_wrapper').height();
        $('.cars_section .articles_wrapper>.row_wrapper').height(onload_height);

        var max_pages = $('.load_more_posts').attr('data-max-pages');
        var paged = 2;
        var more_button_text = $('.load_more_posts').text();

        $('.load_more_posts').click(function(){

            var button = $(this),
                data = {
                    'action': 'loadmore',
                    'query': ajaxInfo.posts,
                    'page' : paged
                };

            $.ajax({ // you can also use $.post here
                url : ajaxInfo.ajaxurl, // AJAX handler
                data : data,
                type : 'POST',
                beforeSend : function ( xhr ) {
                    button.find('span').text('Loading...'); // change the button text, you can also add a preloader image
                },
                success : function( data ){
                    if( data ) {
                        button.find('span').text( more_button_text ); // insert new posts
                        $('.cars_section .articles_wrapper .row').append( data )
                        $('.cars_section .articles_wrapper .row_wrapper').height( $('.cars_section .articles_wrapper .row').height() );
                        if ( paged == max_pages )
                            button.remove(); // if last page, remove the button

                        paged++;
                        // you can also fire the "post-load" event here if you use a plugin that requires it
                        // $( document.body ).trigger( 'post-load' );
                    } else {
                        button.remove(); // if no data, remove the button as well
                    }
                }
            });
        });
    }


    var galleryTop,thumbsSwiper = '';

    $("#gallery_slider").on('show.bs.modal', function( event ){
        var images_list = $.parseJSON( $(event.relatedTarget).parents('.post').attr('data-images') );
        if( images_list.length > 0 ){
            $.each(images_list, function( key, value ){
                $("#gallery_slider").find('.photogaller_modal_slider .swiper-wrapper').append('<div data-index="'+key+'" class="swiper-slide" style="background-image: url(' + value['large'] + ')"><img src="' + value['large'] + '" /></div>');
                $("#gallery_slider").find('.photogaller_thumbs_slider .swiper-wrapper').append('<div data-index="'+key+'" class="swiper-slide photogaller_thumbs"><div class="column demo cursor"  style="background-image: url(' + value['thumb'] + ')"><img src="' + value['thumb'] + '" /></div></div>');
            });
        }
    });

    $("#gallery_slider").on('shown.bs.modal', function( event ){
        var images_list = $.parseJSON( $(event.relatedTarget).parents('.post').attr('data-images') );
        if( images_list.length > 0 ){
            galleryTop = new Swiper('#gallery_slider .photogaller_modal_slider', {
                spaceBetween: 10,
                autoHeight: false,
                loop: true,
                followFinger: false,
                loopedSlides: 2,
                breakpoints: {
                    630: {
                        loop: false,
                    },
                }
            });

            thumbsSwiper = new Swiper('#gallery_slider .photogaller_thumbs_slider', {
                spaceBetween: 0,
                slidesPerView: 2,
                slidesPerGroup: 1,
                slideToClickedSlide: true,
                centeredSlides: false,
                loop: true,
                followFinger: false,
                autoplay: false,
                speed: 700,
                loopedSlides: 2,
                navigation: {
                    nextEl: '#gallery_slider .swiper-button-next',
                    prevEl: '#gallery_slider .swiper-button-prev',
                },
                pagination: {
                    el: '#gallery_slider .photogaller_thumbs_slider .swiper-pagination',
                    type: 'fraction',
                },
                breakpoints: {
                    630: {
                        spaceBetween: 0,
                        slidesPerView: 3,
                        slidesPerGroup: 1,
                        centeredSlides: true,
                        loop: false,
                    },
                    991: {
                        spaceBetween: 0,
                        slidesPerView: 5,
                        slidesPerGroup: 1,
                        centeredSlides: true,
                        loop: false,
                    },
                    1200: {
                        spaceBetween: 0,
                        slidesPerView: 11,
                        slidesPerGroup: 1,
                        centeredSlides: true,
                        loop: false,
                    }
                }
            });
            /*var active_slide_to_show = $("#gallery_slider .photogaller_modal_slider .swiper-slide[data-index='0']").index();
            var active_slide_to_show_thumbs = $("#gallery_slider .photogaller_thumbs_slider .swiper-slide[data-index='0']").index();
            */
            if($(window).width() > 630){
                galleryTop.slideTo( Math.round( images_list.length / 2 ) - 1 );
                galleryTop.update();
                thumbsSwiper.slideTo(Math.round( images_list.length / 2 ) - 1 );
                thumbsSwiper.update();

            }
            thumbsSwiper.controller.control = galleryTop;
        }
    })

    $('#gallery_slider').on('hidden.bs.modal', function () {
        galleryTop.destroy();
        thumbsSwiper.destroy();

        galleryTop = undefined;
        thumbsSwiper = undefined;

        $("#gallery_slider").find('.swiper-wrapper').removeAttr('style');
        $('.swiper-slide').remove();
        $('body').addClass('after_modal_close');
    });

    $('#contact_form,#register_form').on('hidden.bs.modal', function () {
        $('body .modal-body .nf-response-msg').empty();
        $('body .modal-body .nf-response-msg').removeAttr('style');
        $('body .modal-body .nf-form-title').removeAttr('style');
        $('body .modal-form .nf-form-layout form').removeAttr('style');
    });

    if($('.js-section-gallery-slider').length && $('.js-section-gallery-slider-thumbs').length) {

        let $sliderGallerySection = $('.js-section-gallery-slider');
        let $sliderGallerySectionThumbs = $('.js-section-gallery-slider-thumbs');
        let sliderGallerySection;
        let sliderGallerySectionThumbs;

        function initGalleryMainSlider(prevButton, nextButton) {
            sliderGallerySection = new Swiper($sliderGallerySection[0], {
                slidesPerView: 1,
                slidesPerGroup: 1,
                loop: true,
                speed: 1000,
                spaceBetween: 5,
                navigation: {
                    nextEl: nextButton,
                    prevEl: prevButton,
                },            
                pagination: {
                    el: '.section-gallery__main .swiper-nav .pagination',
                    type: 'fraction',
                },
                thumbs: {
                    swiper: sliderGallerySectionThumbs,
                },
                breakpoints: {
                    1200: {
                        spaceBetween: 10,
                    }
                }
            });
        }

        function initGalleryThumbSlider() {
            sliderGallerySectionThumbs = new Swiper($sliderGallerySectionThumbs[0], {
                slidesPerView: 5,
                slidesPerGroup: 1,
                loop: true,
                centeredSlides: true,
                speed: 1000,
                spaceBetween: 10,
                watchSlidesProgress: true,
                direction: 'vertical',
            });
        }

        initGalleryThumbSlider()        

        if(window.matchMedia('(max-width: 1200px)').matches) {
            let prevButton = '.section-gallery__main .js-swiper-button-prev-mob';
            let nextButton = '.section-gallery__main .js-swiper-button-next-mob';

            initGalleryMainSlider(prevButton, nextButton)
        } else {
            let prevButton = '.section-gallery__main .js-swiper-button-prev';
            let nextButton = '.section-gallery__main .js-swiper-button-next';

            initGalleryMainSlider(prevButton, nextButton)
        }
        
        $(window).resize(function() {
            if(window.matchMedia('(max-width: 1200px)').matches) {
                sliderGallerySection.destroy()
                sliderGallerySectionThumbs.destroy()

                initGalleryThumbSlider()

                let prevButton = '.section-gallery__main .js-swiper-button-prev-mob';
                let nextButton = '.section-gallery__main .js-swiper-button-next-mob';

                initGalleryMainSlider(prevButton, nextButton)
            } else {
                sliderGallerySection.destroy()
                sliderGallerySectionThumbs.destroy()
                
                initGalleryThumbSlider()

                let prevButton = '.section-gallery__main .js-swiper-button-prev';
                let nextButton = '.section-gallery__main .js-swiper-button-next';

                initGalleryMainSlider(prevButton, nextButton)
            }
        })
    }

    if( $('*').is('#gallery_page_modal_slider') ) {
        let galleryTop = new Swiper('#gallery_page_modal_slider .gallerysection_modal_slider', {
            spaceBetween: 10,
            autoHeight: false,
            followFinger: false,
            loopedSlides: 2,
            breakpoints: {
                630: {
                    loop: false,
                },
            }
        });

        let thumbsSwiper = new Swiper('#gallery_page_modal_slider .gallerysection_thumbs_slider', {
            spaceBetween: 0,
            slidesPerView: 'auto',
            slidesPerGroup: 1,
            slideToClickedSlide: true,
            centeredSlides: true,
            followFinger: false,
            autoplay: false,
            speed: 700,
            navigation: {
                nextEl: '#gallery_page_modal_slider .swiper-button-next',
                prevEl: '#gallery_page_modal_slider .swiper-button-prev',
            },
            pagination: {
                el: '#gallery_page_modal_slider .gallerysection_thumbs_slider .swiper-pagination',
                type: 'fraction',
            },
            breakpoints: {
                630: {
                    spaceBetween: 0,
                    slidesPerView: 4,
                    slidesPerGroup: 1,
                    centeredSlides: true,
                    loop: false,
                },
                991: {
                    spaceBetween: 0,
                    slidesPerView: 7,
                    slidesPerGroup: 1,
                    centeredSlides: true,
                    loop: false,
                },
                1200: {
                    spaceBetween: 0,
                    slidesPerView: 12,
                    slidesPerGroup: 1,
                    centeredSlides: true,
                    loop: false,
                }
            }
        });
        /*var active_slide_to_show = $("#gallery_slider .photogaller_modal_slider .swiper-slide[data-index='0']").index();
        var active_slide_to_show_thumbs = $("#gallery_slider .photogaller_thumbs_slider .swiper-slide[data-index='0']").index();
        */
        thumbsSwiper.controller.control = galleryTop;

        $(window).resize(function() {
            galleryTop.update();
            thumbsSwiper.update();
        })
        

        $("#gallery_page_modal_slider").on('shown.bs.modal', function( event ) {
            let slideIndex = $(event.relatedTarget).attr('data-modal-slide-target')
            galleryTop.slideTo( slideIndex, 500 );
            galleryTop.update();
            thumbsSwiper.slideTo( slideIndex, 500 );
            thumbsSwiper.update();
        })
    }

    $(window).resize(function(){
        if($('.reviews .swiper-slide').length > 0){
            var tallest_height = 0;

            $('.reviews .swiper-slide').each(function(){
                if( $(this).find('.review-block').innerHeight() > tallest_height ){
                    tallest_height = $(this).find('.review-block').innerHeight();
                }
            });

            $('.reviews .swiper-slide .review-block').innerHeight( tallest_height );
        }

        if($('.page-template-page-location').length) {
            if(window.matchMedia('(max-width: 1200px)').matches) {
                $('.phone_number.button').removeClass('button');
            } else {
                $('.header__nav .phone_number').addClass('button');
            }
        }

        if($('.cars_section .articles_wrapper .row_wrapper').length > 0){
            $('.cars_section .articles_wrapper .row_wrapper').height( $('.cars_section .articles_wrapper .row').height() );
        }
    });

    $('#navbar_supported_content1').on('show.bs.collapse', function () {
        $('body').addClass('overlay-is-navbar-collapse');
    });
    $('#navbar_supported_content1').on('hide.bs.collapse', function () {
        $('body').removeClass('overlay-is-navbar-collapse');
    });

    $('body').on('DOMSubtreeModified', '.nf-form-content .file_upload-wrap .files_uploaded', function(){
        if( $(this).is(':empty')){
            $(this).parents('.file_upload-wrap').removeClass('already_uploaded');
            $(this).parents('.file_upload-wrap').find('.btn.ninja-forms-field span').text('Select file');
        }else{
            $(this).parents('.file_upload-wrap').addClass('already_uploaded');
            $(this).parents('.file_upload-wrap').find('.btn.ninja-forms-field span').text('Selected file');
        }
    });

    $('body').on('DOMSubtreeModified', '.section .nf-response-msg', function(){
        if( !$(this).is(':empty')){
            $("#form_response .nf-form-wrap").empty();
            $(this).clone().appendTo($("#form_response .nf-form-wrap"));
            $('#form_response').modal('show');
        }
    });

    $(".modal-video").on('hidden.bs.modal', function(){
        $(".modal-video iframe").attr('src', $(".modal-video iframe").attr('src') );
    });

    $(".modal_video").on("shown.bs.modal", function() {
        var modal = $(this),
            youtubeSrc = modal.find("iframe").attr("src"),
            autoplay = 'autoplay',
            autoplayTrue = 'autoplay=1';

        if (modal.find("video").length > 0) {
            modal.find('video').first().trigger('play');
        }

        if (modal.find("iframe").length > 0) {
            modal.find("iframe").attr("autoplay", 1);
            modal.find("iframe").attr("allow", 'autoplay');
        }

        if(modal.find("iframe").length > 0 && youtubeSrc.includes(autoplay)) {
            let newSrc = youtubeSrc.replace(/autoplay=0/gi, autoplayTrue);
            modal.find("iframe").attr("src", `${newSrc}`);
        } else if (modal.find("iframe").length > 0) {
            modal.find("iframe").attr("src", `${youtubeSrc}?autoplay=1`);
        }

        setTimeout(function blur() {
            $(".js-play-video").blur();
        }, 100);
    });

    if( $('.show_more_seo_content').length > 0 ) {

        $('.show_more_seo_content').slideUp();
        $('.show_more_seo_content').removeClass('hide_on_init');
        $('.show_more_seo_button').on('click', function () {

            if ($('.show_more_seo_content').is(':hidden')) {
                $(this).addClass('showed');
                $(this).find('.text').text('Show Less');
                $('.show_more_seo_content').slideDown();
            } else {
                $(this).removeClass('showed');
                $(this).find('.text').text('Show More');
                $('.show_more_seo_content').slideUp();
            }
        });
    }

    if( $('.js-bg-image-change').length > 0 ) {
        jsBgImageChange()
        $(window).on('resize', function(){
            jsBgImageChange();
        })
    }

    function jsBgImageChange() {
        let pc_image = $('.js-bg-image-change').attr('bg-image-pc');
        let tablet_image = $('.js-bg-image-change').attr('bg-image-tablet');
        let mobile_image = $('.js-bg-image-change').attr('bg-image-mobile');

        if ($(window).width() > 990) {
            $('.js-bg-image-change').css('background-image', 'url(\''+pc_image+'\')');
        } else if ($(window).width() < 991) {
            $('.js-bg-image-change').css('background-image', 'url(\''+tablet_image+'\')');
        } else if ($(window).width() < 551) {
            $('.js-bg-image-change').css('background-image', 'url(\''+mobile_image+'\')');
        }
    }

});
