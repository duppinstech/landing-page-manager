jQuery(document).ready(function($) {
    function resolveAjaxUrl(url) {
        if (!url) {
            return '/wp-admin/admin-ajax.php';
        }
        if (url.indexOf('http://') === 0 || url.indexOf('https://') === 0) {
            try {
                var parsed = new URL(url);
                if (parsed.origin !== window.location.origin) {
                    return '/wp-admin/admin-ajax.php';
                }
            } catch (e) {
                return '/wp-admin/admin-ajax.php';
            }
        }
        return url;
    }

    var ajaxUrl = resolveAjaxUrl(lpmanager_vars.ajaxurl);

    function loadReviews(postId) {
        $.post(ajaxUrl, {
            action: 'fetch_google_reviews',
            security: lpmanager_vars.nonce,
            post_id: postId
        }, function(response) {
            console.log(response);
            if (response.success) {
                $('#reviews-container').html(response.data);

                // Initialize Swiper AFTER content is loaded
                new Swiper('.reviews-swiper', {
                    loop: true,
                    slidesPerView: 4,
                    spaceBetween: 20,
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        320: { slidesPerView: 1 },
                        640: { slidesPerView: 2 },
                        960: { slidesPerView: 3 },
                        1200: { slidesPerView: 4 },
                    }
                });

            } else {
                console.error(response.data);
                $('#reviews-container').html('<p>No reviews available.</p>');
            }
        }).fail(function(xhr, status, error) {
            console.error("AJAX error", status, error, xhr.responseText);
        });
    }

    // Example trigger
    var postId = lpmanager_vars.post_id;
    if (postId) {
        loadReviews(postId);
    }
});