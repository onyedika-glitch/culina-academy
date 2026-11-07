(function () {
	var phoneInitRetries = 0;

	function initPhoneField(form) {
		var phone = form.querySelector('input[name="phone"]');
		var code = form.querySelector('input[name="intl_code"]');
		var countryInput = form.querySelector('input[name="country"]');

		if (!phone || !code || phone.dataset.caIntlReady === '1') {
			return;
		}

		if (typeof window.intlTelInput !== 'function') {
			if (phoneInitRetries < 20) {
				phoneInitRetries += 1;
				window.setTimeout(function () {
					initPhoneField(form);
				}, 250);
			}
			return;
		}

		phoneInitRetries = 0;
		phone.dataset.caIntlReady = '1';

		var iti = window.intlTelInput(phone, {
			initialCountry: 'gb',
			separateDialCode: true,
			utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js'
		});

		function syncCode() {
			var country = (typeof iti.getSelectedCountryData === "function" ? iti.getSelectedCountryData() : iti.getSelectedCountry());
			code.value = country && country.dialCode ? country.dialCode : '';
			if (countryInput) {
				countryInput.value = country && country.name ? country.name : '';
			}
		}

		phone.addEventListener('countrychange', syncCode);
		phone.addEventListener('input', syncCode);
		window.setTimeout(syncCode, 300);
	}

	function showNotice(notice, message, ok) {
		if (!notice) {
			return;
		}

		notice.classList.remove('is-success', 'is-error');
		notice.classList.add(ok ? 'is-success' : 'is-error');
		notice.textContent = message;
	}

	function formErrorMessage(payload, fallback) {
		if (!payload) return fallback;
		if (payload.data && typeof payload.data === 'string') return payload.data;
		if (payload.data && payload.data.message) return payload.data.message;
		if (payload.message) return payload.message;
		if (payload.errors) {
			var firstKey = Object.keys(payload.errors)[0];
			if (firstKey && payload.errors[firstKey] && payload.errors[firstKey][0]) {
				return payload.errors[firstKey][0];
			}
		}
		return fallback;
	}

	function initApplicationForm() {
		var form = document.getElementById('caStandaloneApplicationForm');
		var notice = document.getElementById('caStandaloneApplicationNotice');

		if (!form || form.dataset.ready === '1') {
			return;
		}

		form.dataset.ready = '1';
		initPhoneField(form);

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			var token = (window.caData && window.caData.nonce)
				|| (document.querySelector('meta[name="csrf-token"]') || {}).content
				|| '';

			if (!window.caData || !window.caData.ajaxUrl || !token) {
				showNotice(notice, calCourseEnrollment.pluginMissing, false);
				return;
			}

			if (!form.querySelector('input[name="courses[]"]:checked')) {
				showNotice(notice, calCourseEnrollment.selectCourse, false);
				return;
			}

			var submit = form.querySelector('button[type="submit"]');
			var originalText = submit ? submit.textContent : '';
			var data = new FormData(form);

			data.set('_token', token);
			data.append('action', 'ca_submit_standalone_application');
			data.append('nonce', token);
			data.append('page_language', document.documentElement.lang || '');
			data.append('page_url', window.location.href);

			if (submit) {
				submit.disabled = true;
				submit.textContent = calCourseEnrollment.submitting;
			}
			if (notice) {
				notice.classList.remove('is-success', 'is-error');
			}

			window.fetch(window.caData.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': token
				},
				body: data
			})
				.then(function (response) {
					return response.json().then(function (result) {
						return { ok: response.ok, result: result };
					});
				})
				.then(function (payload) {
					if (payload.ok && payload.result.success) {
						showNotice(
							notice,
							(payload.result.data && payload.result.data.message) || calCourseEnrollment.successMessage,
							true
						);
						form.reset();
						initPhoneField(form);
						return;
					}

					showNotice(
						notice,
						calCourseEnrollment.errorPrefix + ' ' + formErrorMessage(payload.result, calCourseEnrollment.tryAgain),
						false
					);
				})
				.catch(function () {
					showNotice(notice, calCourseEnrollment.networkError, false);
				})
				.finally(function () {
					if (submit) {
						submit.disabled = false;
						submit.textContent = originalText;
					}
				});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initApplicationForm);
	} else {
		initApplicationForm();
	}

	window.addEventListener('load', initApplicationForm);
	window.setTimeout(initApplicationForm, 600);
}());