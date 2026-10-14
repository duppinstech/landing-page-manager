(function ($) {
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function (e) {
            var href = this.getAttribute('href');
            if (!href || href === '#') return;
            var target = $(href);
            if (!target.length) return;
            e.preventDefault();
            $('html, body').animate({ scrollTop: target.offset().top }, 600);
        });
    }

    function initMobileMenu() {
        var toggleBtn = $('#mobile-menu-toggle');
        var closeBtn = $('#mobile-menu-close');
        var mobileMenu = $('#mobile-menu');
        var overlay = $('#mobile-menu-overlay');
        if (!toggleBtn.length || !mobileMenu.length || !overlay.length) return;

        function openMenu() {
            toggleBtn.attr('aria-expanded', 'true');
            mobileMenu.addClass('open').removeAttr('hidden');
            overlay.addClass('active').removeAttr('hidden');
        }

        function closeMenu() {
            toggleBtn.attr('aria-expanded', 'false');
            mobileMenu.removeClass('open').attr('hidden', true);
            overlay.removeClass('active').attr('hidden', true);
        }

        toggleBtn.on('click', function () {
            if (mobileMenu.hasClass('open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });
        closeBtn.on('click', closeMenu);
        overlay.on('click', closeMenu);
    }

    function ensureReviewsSwiperMarkup(swiperEl) {
        var wrapper = swiperEl.querySelector('.swiper-wrapper');
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'swiper-wrapper';
            var children = Array.prototype.slice.call(swiperEl.children);
            children.forEach(function (child) {
                if (!child.classList.contains('swiper-pagination') &&
                    !child.classList.contains('swiper-button-prev') &&
                    !child.classList.contains('swiper-button-next')) {
                    child.classList.add('swiper-slide');
                    wrapper.appendChild(child);
                }
            });
            swiperEl.insertBefore(wrapper, swiperEl.firstChild);
        } else {
            Array.prototype.forEach.call(wrapper.children, function (slide) {
                slide.classList.add('swiper-slide');
            });
        }

        if (!swiperEl.querySelector('.swiper-pagination')) {
            var pagination = document.createElement('div');
            pagination.className = 'swiper-pagination';
            swiperEl.appendChild(pagination);
        }
        if (!swiperEl.querySelector('.swiper-button-prev')) {
            var prev = document.createElement('button');
            prev.className = 'swiper-button-prev';
            prev.setAttribute('type', 'button');
            prev.setAttribute('aria-label', 'Previous review');
            swiperEl.appendChild(prev);
        }
        if (!swiperEl.querySelector('.swiper-button-next')) {
            var next = document.createElement('button');
            next.className = 'swiper-button-next';
            next.setAttribute('type', 'button');
            next.setAttribute('aria-label', 'Next review');
            swiperEl.appendChild(next);
        }
    }

    function initReviewsCarousel() {
        if (typeof Swiper === 'undefined') return;
        var selectors = '.reviews-html .reviews-swiper, .reviews-html .swiper, .reviews-html .swiper-container';
        var swipers = document.querySelectorAll(selectors);
        swipers.forEach(function (swiperEl) {
            if (swiperEl.classList.contains('swiper-initialized')) return;
            swiperEl.classList.add('swiper');
            ensureReviewsSwiperMarkup(swiperEl);

            var paginationEl = swiperEl.querySelector('.swiper-pagination');
            var prevEl = swiperEl.querySelector('.swiper-button-prev');
            var nextEl = swiperEl.querySelector('.swiper-button-next');
            new Swiper(swiperEl, {
                loop: true,
                slidesPerView: 1,
                spaceBetween: 20,
                autoHeight: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false
                },
                speed: 900,
                navigation: { prevEl: prevEl, nextEl: nextEl },
                pagination: { el: paginationEl, clickable: true },
                breakpoints: {
                    640: { slidesPerView: 2 },
                    1024: { slidesPerView: 3 }
                }
            });
        });
    }

    function initFaqAccordion() {
        var items = document.querySelectorAll('.e-n-accordion .e-n-accordion-item');
        items.forEach(function (item, index) {
            var summary = item.querySelector('summary');
            var panel = item.querySelector('.faq-answer-panel') || item.querySelector('div[role="region"]') || item.querySelector('div');
            if (!summary || !panel) return;

            var panelId = panel.id || ('faq-panel-' + index);
            panel.id = panelId;
            summary.setAttribute('aria-controls', panelId);
            summary.setAttribute('aria-expanded', item.open ? 'true' : 'false');
            panel.setAttribute('role', 'region');

            panel.style.overflow = 'hidden';
            panel.style.transition = 'max-height 260ms ease, opacity 260ms ease';
            panel.style.maxHeight = item.open ? panel.scrollHeight + 'px' : '0px';
            panel.style.opacity = item.open ? '1' : '0';

            item.addEventListener('toggle', function () {
                summary.setAttribute('aria-expanded', item.open ? 'true' : 'false');
                if (item.open) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    panel.style.opacity = '1';
                } else {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    requestAnimationFrame(function () {
                        panel.style.maxHeight = '0px';
                        panel.style.opacity = '0';
                    });
                }
            });
        });
    }

    $(function () {
        initSmoothScroll();
        initMobileMenu();
        initReviewsCarousel();
        initFaqAccordion();
    });
})(jQuery);


