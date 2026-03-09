/**
 * Landing Page Manager – front-end tracking.
 * Only runs features enabled via lpmanagerTracking (Settings → Conversion Tracking).
 * - Page views: counted server-side on template_redirect.
 * - Link clicks: sent via AJAX for CTR / link performance.
 * - Conversions: phone/call button clicks.
 * - Time on page / abandonment, scroll depth, heatmap, rage clicks when enabled.
 */
(function () {
    if (typeof window.lpmanagerTracking === 'undefined') return;

    var cfg = window.lpmanagerTracking;
    function resolveAjaxUrl(url) {
        if (!url) return '/wp-admin/admin-ajax.php';
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

    var ajaxurl = resolveAjaxUrl(cfg.ajaxurl);
    var nonce = cfg.nonce;
    var postId = parseInt(cfg.postId, 10) || 0;

    var trackLinkClicks = !!cfg.trackLinkClicks;
    var trackConversions = !!cfg.trackConversions;
    var trackTimeOnPage = !!cfg.trackTimeOnPage;
    var trackScrollDepth = !!cfg.trackScrollDepth;
    var trackHeatmap = !!cfg.trackHeatmap;
    var trackRageClicks = !!cfg.trackRageClicks;
    var trackOutboundClicks = !!cfg.trackOutboundClicks;
    var trackCtaVisibility = !!cfg.trackCtaVisibility;
    var trackFormInteractions = !!cfg.trackFormInteractions;

    function sendClick(href, text) {
        if (!postId || !href) return;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.send(
            'action=lpmanager_record_click' +
            '&nonce=' + encodeURIComponent(nonce) +
            '&post_id=' + postId +
            '&href=' + encodeURIComponent(href || '') +
            '&text=' + encodeURIComponent(text || '')
        );
    }

    function sendConversion(pid) {
        var id = pid || postId;
        if (!id) return;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.send(
            'action=lpmanager_record_conversion' +
            '&nonce=' + encodeURIComponent(nonce) +
            '&post_id=' + id
        );
    }

    function sendEngagement(seconds, scrollDepth) {
        if (!postId) return;
        var body = 'action=lpmanager_record_engagement&nonce=' + encodeURIComponent(nonce) + '&post_id=' + postId;
        if (trackTimeOnPage && seconds > 0) body += '&seconds=' + Math.round(seconds);
        if (trackScrollDepth && scrollDepth) body += '&scroll_depth=' + scrollDepth;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.send(body);
    }

    function sendHeatmap(cells) {
        if (!postId || !cells || cells.length === 0) return;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.send(
            'action=lpmanager_record_heatmap' +
            '&nonce=' + encodeURIComponent(nonce) +
            '&post_id=' + postId +
            '&cells=' + encodeURIComponent(JSON.stringify(cells))
        );
    }

    function sendRageClick() {
        if (!postId) return;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.send('action=lpmanager_record_rage_click&nonce=' + encodeURIComponent(nonce) + '&post_id=' + postId);
    }

    // —— Link clicks & conversions (only when enabled) ——
    if (trackLinkClicks || trackConversions) {
        document.addEventListener('click', function (e) {
            var a = e.target && e.target.closest ? e.target.closest('a') : null;
            if (!a || !a.href) return;
            var href = a.getAttribute('href');
            if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
            var text = (a.textContent || '').trim().replace(/\s+/g, ' ');
            var isOutbound = trackOutboundClicks && (a.target === '_blank' || (a.hostname && a.hostname !== window.location.hostname));
            if (trackLinkClicks) sendClick(href, text);
            if (trackConversions && (a.classList.contains('lpmanager-call-button') || a.classList.contains('cta-call-button') || /^tel:/i.test(href))) {
                var dataPostId = a.getAttribute('data-post-id');
                sendConversion(dataPostId ? parseInt(dataPostId, 10) : postId);
            }
        }, true);
    }

    // —— Time on page & scroll depth ——
    if (trackTimeOnPage || trackScrollDepth) {
        var pageStart = Date.now();
        var scrollDepthsSent = { 25: false, 50: false, 75: false, 100: false };

        function onLeave() {
            if (trackTimeOnPage) {
                var seconds = Math.round((Date.now() - pageStart) / 1000);
                if (seconds > 0) sendEngagement(seconds, null);
            }
        }

        function onScroll() {
            if (!trackScrollDepth) return;
            var h = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            if (h <= 0) return;
            var pct = Math.round((window.scrollY || document.documentElement.scrollTop) / h * 100);
            [25, 50, 75, 100].forEach(function (d) {
                if (pct >= d && !scrollDepthsSent[d]) {
                    scrollDepthsSent[d] = true;
                    sendEngagement(null, d);
                }
            });
        }

        window.addEventListener('beforeunload', onLeave);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') onLeave();
        });
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // —— Heatmap: sample mouse position, bin into 10x10 grid, send on unload ——
    if (trackHeatmap) {
        var grid = [];
        var i;
        for (i = 0; i < 100; i++) grid[i] = 0;
        var lastSend = 0;
        var throttle = 200;

        function bin(x, y) {
            var w = window.innerWidth;
            var h = window.innerHeight;
            if (w <= 0 || h <= 0) return -1;
            var col = Math.min(9, Math.floor((x / w) * 10));
            var row = Math.min(9, Math.floor((y / h) * 10));
            return row * 10 + col;
        }

        function recordPosition(e) {
            var idx = bin(e.clientX, e.clientY);
            if (idx >= 0) grid[idx]++;
        }

        document.addEventListener('mousemove', function (e) {
            var now = Date.now();
            if (now - lastSend < throttle) {
                recordPosition(e);
                return;
            }
            lastSend = now;
            recordPosition(e);
        }, { passive: true });

        function flushHeatmap() {
            var cells = [];
            for (i = 0; i < 100; i++) {
                var n = grid[i];
                while (n-- > 0) cells.push(i);
            }
            sendHeatmap(cells);
        }

        window.addEventListener('beforeunload', flushHeatmap);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') flushHeatmap();
        });
    }

    // —— Rage clicks: multiple clicks in same area within short time ——
    if (trackRageClicks) {
        var lastClick = { x: 0, y: 0, t: 0, count: 0 };
        var radius = 30;
        var windowMs = 500;

        document.addEventListener('click', function (e) {
            var t = Date.now();
            var dx = e.clientX - lastClick.x;
            var dy = e.clientY - lastClick.y;
            if (t - lastClick.t < windowMs && (dx * dx + dy * dy) < radius * radius) {
                lastClick.count++;
                if (lastClick.count >= 3) {
                    sendRageClick();
                    lastClick.count = 0;
                }
            } else {
                lastClick.x = e.clientX;
                lastClick.y = e.clientY;
                lastClick.count = 1;
            }
            lastClick.t = t;
        }, true);
    }

    // —— CTA visibility (when CTA enters viewport) ——
    if (trackCtaVisibility) {
        var ctaSent = {};
        function checkCta() {
            var ctas = document.querySelectorAll('.lpmanager-call-button, .cta-call-button, a[href^="tel:"]');
            ctas.forEach(function (el) {
                var key = el.getBoundingClientRect ? (el.getBoundingClientRect().top + '-' + el.offsetHeight) : el.href;
                if (ctaSent[key]) return;
                var rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    ctaSent[key] = true;
                    sendEngagement(null, null);
                }
            });
        }
        window.addEventListener('scroll', checkCta, { passive: true });
        window.addEventListener('load', checkCta);
    }

    // —— Form interactions (focus/blur/submit) ——
    if (trackFormInteractions) {
        document.addEventListener('focus', function (e) {
            if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT')) {
                sendEngagement(null, null);
            }
        }, true);
        document.addEventListener('submit', function (e) {
            if (e.target && e.target.tagName === 'FORM') sendEngagement(null, null);
        }, true);
    }
})();
