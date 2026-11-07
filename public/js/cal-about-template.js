document.addEventListener('DOMContentLoaded', function() {
	document.querySelectorAll('[data-about-reviews-slider]').forEach(function(slider) {
		var track = slider.querySelector('.cal-about-reviews-track');
		var prev = slider.querySelector('.cal-about-reviews-prev');
		var next = slider.querySelector('.cal-about-reviews-next');
		var slides = Array.prototype.slice.call(slider.querySelectorAll('.cal-about-review-card'));
		if (!track || slides.length === 0) {
			return;
		}

		var index = 0;
		var autoplay = null;
		var isActive = false;

		function visibleCount() {
			if (window.innerWidth <= 560) {
				return 1;
			}

			if (window.innerWidth <= 900) {
				return 2;
			}

			if (window.innerWidth <= 1180) {
				return 3;
			}

			return 4;
		}

		function update() {
			var visible = visibleCount();
			var max = Math.max(0, slides.length - visible);
			index = Math.min(index, max);
			track.style.transform = 'translateX(' + (-index * (100 / visible)) + '%)';
			if (prev) {
				prev.disabled = index === 0;
			}
			if (next) {
				next.disabled = index >= max;
			}
		}

		function step() {
			var visible = visibleCount();
			var max = Math.max(0, slides.length - visible);
			index = index >= max ? 0 : index + 1;
			update();
		}

		function startAutoplay() {
			if (autoplay || slides.length <= visibleCount()) {
				return;
			}

			autoplay = window.setInterval(step, 1800);
		}

		function stopAutoplay() {
			if (!autoplay) {
				return;
			}

			window.clearInterval(autoplay);
			autoplay = null;
		}

		if (prev) {
			prev.addEventListener('click', function() {
				index = Math.max(0, index - 1);
				update();

				if (isActive) {
					stopAutoplay();
					startAutoplay();
				}
			});
		}

		if (next) {
			next.addEventListener('click', function() {
				index = Math.min(Math.max(0, slides.length - visibleCount()), index + 1);
				update();

				if (isActive) {
					stopAutoplay();
					startAutoplay();
				}
			});
		}

		slider.addEventListener('mouseenter', stopAutoplay);
		slider.addEventListener('mouseleave', function() {
			if (isActive) {
				startAutoplay();
			}
		});

		window.addEventListener('resize', function() {
			update();

			if (isActive) {
				stopAutoplay();
				startAutoplay();
			}
		});

		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					isActive = entry.isIntersecting;

					if (isActive) {
						startAutoplay();
					} else {
						stopAutoplay();
					}
				});
			}, { rootMargin: '180px 0px 180px 0px', threshold: 0.08 }).observe(slider);
		} else {
			isActive = true;
			startAutoplay();
		}

		update();
	});
});
