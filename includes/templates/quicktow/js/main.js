(function () {
	'use strict';

	document.querySelectorAll('.wra-faq-q').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var item = this.closest('.wra-faq-item');
			var wasOpen = item.classList.contains('wra-faq-open');
			document.querySelectorAll('.wra-faq-item').forEach(function (i) {
				i.classList.remove('wra-faq-open');
			});
			if (!wasOpen) {
				item.classList.add('wra-faq-open');
			}
		});
	});

	var obs = new IntersectionObserver(
		function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('wra-fi-on');
					obs.unobserve(e.target);
				}
			});
		},
		{ threshold: 0.1 }
	);
	document.querySelectorAll('.wra-fi').forEach(function (el) {
		obs.observe(el);
	});
})();
