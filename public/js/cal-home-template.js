(function() {
	var ajaxCfg = window.calHomeAjax || {};

	document.querySelectorAll('.wpcul-slider').forEach(function(slider) {
		const track = slider.querySelector('.wpcul-track');
		const slides = slider.querySelectorAll('.wpcul-slide');
		const prev = slider.querySelector('.wpcul-prev');
		const next = slider.querySelector('.wpcul-next');
		const dotsWrap = slider.querySelector('.wpcul-dots');

		if (!track || slides.length < 2 || !dotsWrap) {
			return;
		}

		let index = 0;

		if (!dotsWrap.children.length) {
			slides.forEach(function(_, i) {
				const dot = document.createElement('button');
				dot.type = 'button';
				dot.className = 'wpcul-dot';
				dot.setAttribute('aria-label', (ajaxCfg.bannerLabel || 'Go to slide') + ' ' + (i + 1));

				if (i === 0) {
					dot.classList.add('active');
				}

				dot.addEventListener('click', function() {
					go(i);
				});
				dotsWrap.appendChild(dot);
			});
		}

		const dots = dotsWrap.querySelectorAll('.wpcul-dot');
		dots.forEach(function(dot, i) {
			dot.addEventListener('click', function() {
				go(i);
			});
		});

		function update() {
			track.style.transform = 'translateX(-' + (index * 100) + '%)';
			dots.forEach(function(dot, i) {
				dot.classList.toggle('active', i === index);
				dot.setAttribute('aria-current', i === index ? 'true' : 'false');
			});
		}

		function hydrateSlide(slide) {
			var img = slide ? slide.querySelector('.wpcul-slide-deferred') : null;
			if (!img || img.dataset.loaded === '1') {
				return;
			}
			var src = img.getAttribute('data-src');
			if (!src) {
				return;
			}
			img.src = src;
			img.dataset.loaded = '1';
			img.classList.remove('wpcul-slide-deferred');
		}

		function go(i) {
			index = i;
			hydrateSlide(slides[i]);
			update();
		}

		function nextSlide() {
			index = (index + 1) % slides.length;
			hydrateSlide(slides[index]);
			update();
		}

		function prevSlide() {
			index = (index - 1 + slides.length) % slides.length;
			hydrateSlide(slides[index]);
			update();
		}

		if (next) {
			next.addEventListener('click', nextSlide);
		}

		if (prev) {
			prev.addEventListener('click', prevSlide);
		}

		if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			window.setInterval(nextSlide, 6500);
		}

		update();
	});

	/* ── Enquiry Form ── */
	function initEnquiryIntlTel(form, attempt) {
			attempt = attempt || 0;
			if (!form) {
				console.warn('[CAL] initEnquiryIntlTel: no form element');
				return;
			}
			var phone = form.querySelector('input[type="tel"][name="phone"]');
			var hidden = form.querySelector('input.ca-intl-code[name="country_code"]');

			if (!phone) {
				console.warn('[CAL] initEnquiryIntlTel: phone input not found');
				return;
			}
			if (phone.dataset.caIntlReady === '1') {
				console.log('[CAL] initEnquiryIntlTel: already initialized');
				return;
			}

			if (typeof window.intlTelInput !== 'function') {
				console.log('[CAL] initEnquiryIntlTel: intlTelInput not ready (attempt ' + attempt + '), retrying in 500ms');
				if (attempt < 30) {
					window.setTimeout(function() {
						initEnquiryIntlTel(form, attempt + 1);
					}, 500);
				} else {
					console.error('[CAL] initEnquiryIntlTel: gave up after 30 retries');
				}
				return;
			}

			console.log('[CAL] initEnquiryIntlTel: initializing intlTelInput');
			phone.dataset.caIntlReady = '1';

			try {
				var iti = window.intlTelInput(phone, {
					initialCountry: 'gb',
					separateDialCode: true,
					utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js'
				});

				phone._caIti = iti;
				console.log('[CAL] intlTelInput initialized successfully');

				function syncCode() {
					var data = (typeof iti.getSelectedCountryData === "function" ? iti.getSelectedCountryData() : iti.getSelectedCountry());
					if (hidden) {
						hidden.value = data && data.dialCode ? data.dialCode : '44';
					}
				}

				phone.addEventListener('countrychange', syncCode);
				phone.addEventListener('input', syncCode);
				window.setTimeout(syncCode, 200);
			} catch (e) {
				console.error('[CAL] intlTelInput init error:', e.message);
				phone.dataset.caIntlReady = '0';
			}
		}

	function setupEnquiryForm() {
		var enquiryForm = document.querySelector('.cal-enquiry-form');
		if (!enquiryForm) return;

		initEnquiryIntlTel(enquiryForm);

		if (enquiryForm.dataset.listenerAttached === '1') return;
		enquiryForm.dataset.listenerAttached = '1';

		var note = enquiryForm.querySelector('.cal-enquiry-note');

		enquiryForm.addEventListener('submit', function(e) {
			e.preventDefault();

			var name  = enquiryForm.querySelector('[name="name"]').value.trim();
			var email = enquiryForm.querySelector('[name="email"]').value.trim();
			var phone = enquiryForm.querySelector('[name="phone"]').value.trim();

			if (!name || !email || !phone) {
				note.textContent = ajaxCfg.requiredText || 'Please fill in all required fields.';
				note.className = 'cal-enquiry-note cal-enquiry-note-er';
				return;
			}

			var phoneInput = enquiryForm.querySelector('[name="phone"]');
			var codeField = enquiryForm.querySelector('[name="country_code"]');
			if (phoneInput && phoneInput._caIti) {
				var selected = (typeof phoneInput._caIti.getSelectedCountryData === "function" ? phoneInput._caIti.getSelectedCountryData() : phoneInput._caIti.getSelectedCountry());
				if (selected && selected.dialCode && codeField) {
					codeField.value = selected.dialCode;
				}
			}
			var code = codeField ? codeField.value : '44';
			var btn  = enquiryForm.querySelector('.cal-enquiry-submit');
			btn.disabled = true;
			btn.textContent = ajaxCfg.sendingText || 'Sending...';
			note.textContent = '';
			note.className = 'cal-enquiry-note';

			var formData = new FormData();
			formData.append('action', 'cal_enquiry_submit');
			formData.append('nonce', ajaxCfg.nonce || '');
			formData.append('page_language', ajaxCfg.language || document.documentElement.lang || '');
			formData.append('name', name);
			formData.append('email', email);
			formData.append('country_code', code);
			formData.append('phone', phone);
			var websiteField = enquiryForm.querySelector('[name="website"]');
			formData.append('website', websiteField ? websiteField.value : '');
			var formStartedField = enquiryForm.querySelector('[name="form_started"]');
			formData.append('form_started', formStartedField ? formStartedField.value : '');

			fetch(ajaxCfg.ajaxurl || '/contact-us', {
				method: 'POST',
				headers: { 'X-Requested-With': 'XMLHttpRequest' },
				body: formData
			})
			.then(function(r) { return r.json(); })
			.then(function(resp) {
				if (resp.success) {
					note.textContent = resp.data.message;
					note.className = 'cal-enquiry-note cal-enquiry-note-ok';
					enquiryForm.reset();
				} else {
					note.textContent = resp.data.message;
					note.className = 'cal-enquiry-note cal-enquiry-note-er';
				}
			})
			.catch(function() {
				note.textContent = ajaxCfg.errorText || 'Something went wrong. Please try again.';
				note.className = 'cal-enquiry-note cal-enquiry-note-er';
			})
			.finally(function() {
				btn.disabled = false;
				btn.textContent = ajaxCfg.enquireText || 'Enquire now';
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupEnquiryForm);
	} else {
		setupEnquiryForm();
	}
	window.addEventListener('load', setupEnquiryForm);

	/* ── Testimonials / Reviews Slider ── */
	(function() {
		var track = document.querySelector('.reviews-slider-track');
		var dotsWrap = document.querySelector('.slider-dots');
		if (!track || !dotsWrap) return;

		var cards = track.querySelectorAll('.review-card');
		if (cards.length < 2) return;

		var total = cards.length / 2; // half are clones
		var idx = 0;
		var gap = 28;
		var gapCss = getComputedStyle(track).gap;
		if (gapCss) gap = parseInt(gapCss, 10) || 28;

		function getCardsPerView() {
			return window.innerWidth < 901 ? 1 : window.innerWidth < 1201 ? 2 : 3;
		}

		if (!dotsWrap.children.length) {
			for (var i = 0; i < total; i++) {
				var dot = document.createElement('button');
				dot.type = 'button';
				dot.className = 'slider-dot';
				if (i === 0) dot.classList.add('active');
				dot.setAttribute('aria-label', (ajaxCfg.reviewLabel || 'Go to review') + ' ' + (i + 1));
				dot.addEventListener('click', (function(idx) {
					return function() { go(idx); };
				})(i));
				dotsWrap.appendChild(dot);
			}
		}

		var dots = dotsWrap.querySelectorAll('.slider-dot');
		dots.forEach(function(dot, i) {
			dot.addEventListener('click', function() {
				go(i);
			});
		});

		function getCardWidth() {
			var first = cards[0];
			var style = getComputedStyle(first);
			var w = first.offsetWidth;
			var ml = parseFloat(style.marginLeft) || 0;
			var mr = parseFloat(style.marginRight) || 0;
			return w + ml + mr;
		}

		function update() {
			var cw = getCardWidth();
			var offset = idx * (cw + gap);
			track.style.transform = 'translateX(-' + offset + 'px)';
			dots.forEach(function(d, i) {
				d.classList.toggle('active', i === idx);
			});
		}

		function go(i) {
			idx = i;
			update();
		}

		var resizeTimer;
		window.addEventListener('resize', function() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function() {
				var pv = getCardsPerView();
				var maxI = Math.max(0, total - pv);
				if (idx > maxI) idx = maxI;
				if (idx < 0) idx = 0;
				update();
			}, 150);
		});

		update();
	})();
})();
