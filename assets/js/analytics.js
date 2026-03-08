/**
 * Landing Page Manager – Analytics page: stat cards, tabs, charts, engagement, heatmap, rage, by template.
 */
(function () {
    if (typeof window.lpmanagerAnalytics === 'undefined') return;

    const config = window.lpmanagerAnalytics;
    const apiUrl = config.apiUrl;
    const nonce = config.nonce;
    let currentClientId = parseInt(config.clientId, 10) || 0;
    let currentTemplateFilter = (config.templateFilter || '').replace(/^\s+|\s+$/g, '');
    let analyticsData = null;

    const root = document.getElementById('lp-analytics-root');
    const loadingEl = document.getElementById('lp-analytics-loading');
    const errorEl = document.getElementById('lp-analytics-error');
    const clientFilter = document.getElementById('lp-client-filter');
    const templateFilter = document.getElementById('lp-template-filter');

    function showLoading(show) {
        if (loadingEl) loadingEl.style.display = show ? 'block' : 'none';
        if (errorEl) errorEl.hidden = true;
    }

    function showError(msg) {
        if (errorEl) {
            errorEl.textContent = msg;
            errorEl.hidden = false;
        }
        if (loadingEl) loadingEl.style.display = 'none';
    }

    function buildQueryString() {
        let q = [];
        if (currentClientId) q.push('client_id=' + currentClientId);
        if (currentTemplateFilter) q.push('template=' + encodeURIComponent(currentTemplateFilter));
        return q.length ? '?' + q.join('&') : '';
    }

    function fetchData() {
        showLoading(true);
        const url = apiUrl + buildQueryString();
        fetch(url, {
            method: 'GET',
            headers: { 'X-WP-Nonce': nonce },
        })
            .then(function (res) {
                if (!res.ok) throw new Error(res.statusText);
                return res.json();
            })
            .then(function (data) {
                analyticsData = data;
                showLoading(false);
                populateTemplateFilter();
                renderOverview();
                renderClientsTable();
                renderPagesTable();
                renderTemplateTable();
                renderLinkPerformance();
                renderEngagement();
                renderHeatmap();
                renderRageClicks();
                initCharts();
            })
            .catch(function (err) {
                showError('Failed to load analytics: ' + (err.message || 'Unknown error'));
            });
    }

    function populateTemplateFilter() {
        if (!templateFilter) return;
        const list = (analyticsData && analyticsData.templates) ? analyticsData.templates : [];
        const current = (analyticsData && analyticsData.template_filter) !== undefined ? analyticsData.template_filter : currentTemplateFilter;
        let opts = '<option value="">All templates</option>';
        list.forEach(function (t) {
            opts += '<option value="' + escapeHtml(t.slug) + '"' + (t.slug === current ? ' selected' : '') + '>' + escapeHtml(t.name) + '</option>';
        });
        templateFilter.innerHTML = opts;
    }

    function getSummary() {
        return (analyticsData && analyticsData.summary) ? analyticsData.summary : {};
    }

    function getTrackingEnabled() {
        return (analyticsData && analyticsData.tracking_enabled) ? analyticsData.tracking_enabled : {};
    }

    function renderOverview() {
        const summary = getSummary();
        const te = getTrackingEnabled();
        const container = document.getElementById('lp-summary-cards');
        if (!container) return;
        let html = '';
        if (te.page_views) {
            html += '<div class="lp-stat-card lp-stat-card--views"><h3 class="lp-stat-card__label">Total views</h3><p class="lp-stat-card__value">' + (summary.total_views != null ? Number(summary.total_views).toLocaleString() : '0') + '</p></div>';
        }
        if (te.conversions) {
            html += '<div class="lp-stat-card lp-stat-card--conversions"><h3 class="lp-stat-card__label">Conversions</h3><p class="lp-stat-card__value">' + (summary.total_conversions != null ? Number(summary.total_conversions).toLocaleString() : '0') + '</p></div>';
        }
        if (te.link_clicks) {
            html += '<div class="lp-stat-card lp-stat-card--clicks"><h3 class="lp-stat-card__label">Link clicks</h3><p class="lp-stat-card__value">' + (summary.total_clicks != null ? Number(summary.total_clicks).toLocaleString() : '0') + '</p></div>';
        }
        if (te.conversions) {
            html += '<div class="lp-stat-card lp-stat-card--ctr"><h3 class="lp-stat-card__label">Conversion rate (CTR)</h3><p class="lp-stat-card__value">' + (summary.ctr_conversion != null ? summary.ctr_conversion + '%' : '0%') + '</p></div>';
        }
        if (!html) {
            html = '<p class="lp-empty">Enable conversion tracking options in Settings to see analytics.</p>';
        }
        container.innerHTML = html;
    }

    var chartInstances = [];

    function initCharts() {
        if (typeof Chart === 'undefined') return;
        chartInstances.forEach(function (c) { if (c) c.destroy(); });
        chartInstances = [];
        const te = getTrackingEnabled();
        const dailyVisits = (analyticsData && analyticsData.daily_visits) ? analyticsData.daily_visits : {};
        const dailyConv = (analyticsData && analyticsData.daily_conversions) ? analyticsData.daily_conversions : {};
        const dates = Object.keys(dailyVisits).concat(Object.keys(dailyConv)).filter(function (v, i, a) { return a.indexOf(v) === i; }).sort();
        const visitValues = dates.map(function (d) { return dailyVisits[d] || 0; });
        const convValues = dates.map(function (d) { return dailyConv[d] || 0; });

        const defaultOpts = {
            maintainAspectRatio: false,
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } },
        };
        const gradient = function (ctx, color, opacity) {
            const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
            g.addColorStop(0, color.replace(')', ',' + opacity + ')').replace('rgb', 'rgba'));
            g.addColorStop(1, color.replace(')', ',0)').replace('rgb', 'rgba'));
            return g;
        };
        const c1 = document.getElementById('lp-chart-daily-visits');
        if (c1 && te.page_views) {
            const ctx = c1.getContext('2d');
            const g = ctx.createLinearGradient(0, 0, 0, 300);
            g.addColorStop(0, 'rgba(34, 113, 177, 0.35)');
            g.addColorStop(1, 'rgba(34, 113, 177, 0)');
            chartInstances.push(new Chart(c1, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{ label: 'Page views', data: visitValues, borderColor: '#2271b1', backgroundColor: g, fill: true, tension: 0.3, pointRadius: 3 }],
                },
                options: defaultOpts,
            }));
        } else if (c1) { c1.parentNode.style.display = 'none'; }
        const c2 = document.getElementById('lp-chart-daily-conversions');
        if (c2 && te.conversions) {
            const ctx = c2.getContext('2d');
            const g = ctx.createLinearGradient(0, 0, 0, 300);
            g.addColorStop(0, 'rgba(0, 163, 42, 0.35)');
            g.addColorStop(1, 'rgba(0, 163, 42, 0)');
            chartInstances.push(new Chart(c2, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{ label: 'Conversions', data: convValues, borderColor: '#00a32a', backgroundColor: g, fill: true, tension: 0.3, pointRadius: 3 }],
                },
                options: defaultOpts,
            }));
        } else if (c2) { c2.parentNode.style.display = 'none'; }
    }

    function renderClientsTable() {
        const tbody = document.getElementById('lp-tbody-clients');
        if (!tbody) return;
        const rows = (analyticsData && analyticsData.by_client) ? analyticsData.by_client : [];
        const adminUrl = (config.adminUrl) ? config.adminUrl : (window.location.pathname.replace(/\/[^/]*$/, '') + '/admin.php');
        tbody.innerHTML = rows.map(function (r) {
            const url = adminUrl + '?page=lp_analytics&client_id=' + r.client_id;
            return '<tr><td><a href="' + escapeHtml(url) + '">' + escapeHtml(r.name) + '</a></td><td>' + r.pages_count + '</td><td>' + Number(r.views).toLocaleString() + '</td><td>' + Number(r.conversions).toLocaleString() + '</td><td>' + Number(r.clicks).toLocaleString() + '</td><td>' + r.ctr_conversion + '%</td></tr>';
        }).join('') || '<tr><td colspan="6">No data</td></tr>';
    }

    function renderPagesTable() {
        const tbody = document.getElementById('lp-tbody-pages');
        if (!tbody) return;
        const rows = (analyticsData && analyticsData.by_page) ? analyticsData.by_page : [];
        const editBase = config.editBase ? config.editBase : '';
        tbody.innerHTML = rows.map(function (r) {
            const editLink = editBase ? editBase.replace('REPLACE_ID', r.id) : ('post.php?post=' + r.id + '&action=edit');
            const tpl = r.template_slug ? escapeHtml(r.template_slug) : '—';
            return '<tr><td><a href="' + escapeHtml(editLink) + '">' + escapeHtml(r.title) + '</a></td><td>' + escapeHtml(r.client_name || '') + '</td><td>' + tpl + '</td><td>' + Number(r.views).toLocaleString() + '</td><td>' + Number(r.conversions).toLocaleString() + '</td><td>' + Number(r.clicks).toLocaleString() + '</td><td>' + r.ctr_conversion + '%</td></tr>';
        }).join('') || '<tr><td colspan="7">No data</td></tr>';
    }

    function renderTemplateTable() {
        const tbody = document.getElementById('lp-tbody-template');
        if (!tbody) return;
        const rows = (analyticsData && analyticsData.by_template) ? analyticsData.by_template : [];
        tbody.innerHTML = rows.map(function (r) {
            return '<tr><td>' + escapeHtml(r.template_name) + '</td><td>' + r.pages_count + '</td><td>' + Number(r.views).toLocaleString() + '</td><td>' + Number(r.conversions).toLocaleString() + '</td><td>' + Number(r.clicks).toLocaleString() + '</td><td>' + r.ctr_conversion + '%</td></tr>';
        }).join('') || '<tr><td colspan="6">No data</td></tr>';
    }

    function renderLinkPerformance() {
        const container = document.getElementById('lp-link-performance');
        if (!container) return;
        const data = (analyticsData && analyticsData.link_clicks) ? analyticsData.link_clicks : [];
        let html = '';
        data.forEach(function (block) {
            if (!block.links || block.links.length === 0) return;
            html += '<div class="lp-link-block"><h4 class="lp-link-block__title">' + escapeHtml(block.title) + '</h4><table class="lp-table lp-table--compact"><thead><tr><th>Link / label</th><th>Clicks</th></tr></thead><tbody>';
            block.links.forEach(function (link) {
                var label = link.label || link.href || '—';
                if (label.length > 80) label = label.slice(0, 77) + '…';
                html += '<tr><td>' + escapeHtml(label) + '</td><td>' + Number(link.count).toLocaleString() + '</td></tr>';
            });
            html += '</tbody></table></div>';
        });
        container.innerHTML = html || '<p class="lp-empty">No link click data yet.</p>';
    }

    function renderEngagement() {
        const container = document.getElementById('lp-engagement-content');
        if (!container) return;
        const te = getTrackingEnabled();
        if (!te.time_on_page && !te.scroll_depth) {
            container.innerHTML = '<p class="lp-empty">Enable Time on page or Scroll depth in Settings to see engagement data.</p>';
            return;
        }
        const data = (analyticsData && analyticsData.engagement) ? analyticsData.engagement : [];
        const withData = data.filter(function (e) { return e.time_count > 0 || e.scroll_25 > 0 || e.scroll_50 > 0 || e.scroll_75 > 0 || e.scroll_100 > 0; });
        if (withData.length === 0) {
            container.innerHTML = '<p class="lp-empty">No engagement data yet.</p>';
            return;
        }
        let html = '<div class="lp-table-wrapper"><table class="lp-table"><thead><tr><th>Page</th><th>Avg. time (s)</th><th>Sessions</th><th>25%</th><th>50%</th><th>75%</th><th>100%</th></tr></thead><tbody>';
        withData.forEach(function (e) {
            html += '<tr><td>' + escapeHtml(e.title) + '</td><td>' + (e.time_avg || 0) + '</td><td>' + (e.time_count || 0) + '</td><td>' + (e.scroll_25 || 0) + '</td><td>' + (e.scroll_50 || 0) + '</td><td>' + (e.scroll_75 || 0) + '</td><td>' + (e.scroll_100 || 0) + '</td></tr>';
        });
        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    /** Intensity 0–1: fully transparent (no color) → yellow → orange → red. No blue. */
    function heatmapColor(intensity) {
        if (intensity <= 0 || intensity < 0.02) return 'transparent';
        var r = 255;
        var g = intensity >= 1 ? 0 : Math.round(255 * (1 - intensity));
        var b = 0;
        var a = 0.4 + 0.55 * Math.min(1, intensity);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + a + ')';
    }

    function buildHeatmapOverlayCells(grid) {
        var max = 1;
        for (var i = 0; i < 100; i++) { if (grid[i] > max) max = grid[i]; }
        var html = '';
        for (var row = 0; row < 10; row++) {
            for (var col = 0; col < 10; col++) {
                var idx = row * 10 + col;
                var v = grid[idx] || 0;
                var intensity = max > 0 ? v / max : 0;
                html += '<div class="lp-heatmap-overlay-cell" style="background:' + heatmapColor(intensity) + '" title="' + v + '"></div>';
            }
        }
        return html;
    }

    function openHeatmapModal(item) {
        var modal = document.getElementById('lp-heatmap-modal');
        if (!modal) return;
        var titleEl = modal.querySelector('.lp-heatmap-modal__title');
        var viewLink = modal.querySelector('.lp-heatmap-modal__view-page');
        var iframe = modal.querySelector('.lp-heatmap-modal__iframe');
        var overlay = modal.querySelector('.lp-heatmap-modal__overlay');
        if (titleEl) titleEl.textContent = item.title;
        if (viewLink) { viewLink.href = item.url || '#'; viewLink.style.display = item.url ? '' : 'none'; }
        if (iframe) iframe.src = item.url || '';
        if (overlay) overlay.innerHTML = buildHeatmapOverlayCells(item.grid || []);
        modal.classList.add('lp-heatmap-modal--open');
        document.body.classList.add('lp-modal-open');
    }

    function closeHeatmapModal() {
        var modal = document.getElementById('lp-heatmap-modal');
        if (modal) {
            modal.classList.remove('lp-heatmap-modal--open');
            var iframe = modal.querySelector('.lp-heatmap-modal__iframe');
            if (iframe) iframe.src = 'about:blank';
        }
        document.body.classList.remove('lp-modal-open');
    }

    function renderHeatmap() {
        const container = document.getElementById('lp-heatmap-content');
        if (!container) return;
        const te = getTrackingEnabled();
        if (!te.heatmap) {
            container.innerHTML = '<p class="lp-empty">Enable Heatmap (mouse position) in Settings to see data.</p>';
            return;
        }
        const data = (analyticsData && analyticsData.heatmap) ? analyticsData.heatmap : [];
        const withData = data.filter(function (h) { return h.grid && h.grid.some(function (v) { return v > 0; }); });
        if (withData.length === 0) {
            container.innerHTML = '<p class="lp-empty">No heatmap data yet. Visit landing pages with heatmap tracking enabled to see where visitors focus.</p>';
            return;
        }
        var modal = document.getElementById('lp-heatmap-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'lp-heatmap-modal';
            modal.className = 'lp-heatmap-modal';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.innerHTML = '<div class="lp-heatmap-modal__backdrop"></div><div class="lp-heatmap-modal__box">' +
                '<div class="lp-heatmap-modal__header"><h3 class="lp-heatmap-modal__title"></h3><a class="lp-heatmap-modal__view-page" href="#" target="_blank" rel="noopener">View full page</a><button type="button" class="lp-heatmap-modal__close" aria-label="Close">&times;</button></div>' +
                '<div class="lp-heatmap-modal__preview">' +
                '<div class="lp-heatmap-modal__scroll"><iframe class="lp-heatmap-modal__iframe" title="Page preview"></iframe></div>' +
                '<div class="lp-heatmap-overlay lp-heatmap-modal__overlay"></div>' +
                '</div></div>';
            modal.querySelector('.lp-heatmap-modal__backdrop').addEventListener('click', closeHeatmapModal);
            modal.querySelector('.lp-heatmap-modal__close').addEventListener('click', closeHeatmapModal);
            document.body.appendChild(modal);
        }
        var cardsHtml = '<div class="lp-heatmap-cards">';
        withData.forEach(function (h) {
            var url = (h.url || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            cardsHtml += '<div class="lp-heatmap-card" role="button" tabindex="0" data-post-id="' + (h.post_id || '') + '">';
            cardsHtml += '<h4 class="lp-heatmap-card__title">' + escapeHtml(h.title) + '</h4>';
            cardsHtml += '<div class="lp-heatmap-preview">';
            cardsHtml += '<div class="lp-heatmap-preview__scroll"><iframe class="lp-heatmap-preview__iframe" src="' + url + '" title="' + escapeHtml(h.title) + '"></iframe></div>';
            cardsHtml += '<div class="lp-heatmap-overlay">' + buildHeatmapOverlayCells(h.grid || []) + '</div>';
            cardsHtml += '</div>';
            cardsHtml += '<p class="lp-heatmap-card__hint">Click to view larger</p>';
            cardsHtml += '</div>';
        });
        cardsHtml += '</div>';
        container.innerHTML = cardsHtml;
        container.querySelectorAll('.lp-heatmap-card').forEach(function (card) {
            var postId = card.getAttribute('data-post-id');
            var item = withData.filter(function (h) { return String(h.post_id) === String(postId); })[0];
            if (!item) return;
            function open() { openHeatmapModal(item); }
            card.addEventListener('click', open);
            card.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
        });
    }

    function renderRageClicks() {
        const container = document.getElementById('lp-rage-content');
        if (!container) return;
        const te = getTrackingEnabled();
        if (!te.rage_clicks) {
            container.innerHTML = '<p class="lp-empty">Enable Rage / repeated clicks in Settings to see data.</p>';
            return;
        }
        const data = (analyticsData && analyticsData.rage_clicks) ? analyticsData.rage_clicks : [];
        if (data.length === 0) {
            container.innerHTML = '<p class="lp-empty">No rage clicks recorded yet.</p>';
            return;
        }
        let html = '<div class="lp-table-wrapper"><table class="lp-table"><thead><tr><th>Page</th><th>Rage click count</th></tr></thead><tbody>';
        data.forEach(function (r) {
            html += '<tr><td>' + escapeHtml(r.title) + '</td><td>' + Number(r.count).toLocaleString() + '</td></tr>';
        });
        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    function escapeHtml(s) {
        if (s == null) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function switchTab(tabName) {
        const tabs = root.querySelectorAll('.lp-tabs__tab');
        const panels = root.querySelectorAll('.lp-tab-panel');
        tabs.forEach(function (t) {
            t.classList.toggle('lp-tabs__tab--active', t.getAttribute('data-tab') === tabName);
            t.setAttribute('aria-selected', t.getAttribute('data-tab') === tabName ? 'true' : 'false');
        });
        panels.forEach(function (p) {
            const isActive = p.id === 'panel-' + tabName;
            p.classList.toggle('lp-tab-panel--active', isActive);
            p.hidden = !isActive;
        });
    }

    if (clientFilter) {
        clientFilter.addEventListener('change', function () {
            currentClientId = parseInt(this.value, 10) || 0;
            var base = config.adminUrl ? config.adminUrl : (window.location.origin + window.location.pathname);
            if (base.indexOf('?') >= 0) base = base.split('?')[0];
            var url = base + '?page=lp_analytics';
            if (currentClientId) url += '&client_id=' + currentClientId;
            if (currentTemplateFilter) url += '&template=' + encodeURIComponent(currentTemplateFilter);
            window.location.href = url;
        });
    }
    if (templateFilter) {
        templateFilter.addEventListener('change', function () {
            currentTemplateFilter = (this.value || '').trim();
            var base = config.adminUrl ? config.adminUrl : (window.location.origin + window.location.pathname);
            if (base.indexOf('?') >= 0) base = base.split('?')[0];
            var url = base + '?page=lp_analytics';
            if (currentClientId) url += '&client_id=' + currentClientId;
            if (currentTemplateFilter) url += '&template=' + encodeURIComponent(currentTemplateFilter);
            window.location.href = url;
        });
    }

    root.querySelectorAll('.lp-tabs__tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            switchTab(this.getAttribute('data-tab'));
        });
    });

    fetchData();
})();
