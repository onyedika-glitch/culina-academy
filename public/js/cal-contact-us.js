document.addEventListener('DOMContentLoaded', function () {
	var form = document.getElementById('calContactForm');
	if (!form) return;
	var note = form.querySelector('.cal-contact-note');

	var phoneInput = form.querySelector('input[type="tel"][name="cal_phone"]');
	if (phoneInput && typeof window.intlTelInput === 'function' && phoneInput.dataset.caIntlReady !== '1') {
		phoneInput.dataset.caIntlReady = '1';
		var hidden = form.querySelector('input.ca-intl-code[name="country_code"]');
		var iti = window.intlTelInput(phoneInput, {
			initialCountry: 'gb',
			separateDialCode: true,
			utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js'
		});
		phoneInput._caIti = iti;
		function syncCode() {
			var data = (typeof iti.getSelectedCountryData === "function" ? iti.getSelectedCountryData() : iti.getSelectedCountry());
			if (hidden) hidden.value = data && data.dialCode ? data.dialCode : '';
		}
		phoneInput.addEventListener('countrychange', syncCode);
		phoneInput.addEventListener('input', syncCode);
	}

	function formErrorMessage(payload, fallback) {
		if (!payload) return fallback;
		if (typeof payload.data === 'string' && payload.data) return payload.data;
		if (payload.message) return payload.message;
		if (payload.errors) {
			var firstKey = Object.keys(payload.errors)[0];
			if (firstKey && payload.errors[firstKey] && payload.errors[firstKey][0]) {
				return payload.errors[firstKey][0];
			}
		}
		return fallback;
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var btn = form.querySelector('.cal-contact-submit');
		var token = calContact.nonce || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
		btn.disabled = true;
		btn.textContent = calContact.sendingText;
		note.textContent = '';
		note.className = 'cal-contact-note';

		var data = new FormData(form);
		data.set('action', 'cal_send_contact');
		data.set('_token', token);
		data.set('nonce', token);
		data.set('page_language', calContact.language || document.documentElement.lang || '');
		data.set('page_url', window.location.href);
		data.set('name', form.querySelector('[name="cal_name"]').value);
		data.set('email', form.querySelector('[name="cal_email"]').value);
		data.set('phone', form.querySelector('[name="cal_phone"]').value);
		data.set('message', form.querySelector('[name="cal_message"]').value);

		fetch(calContact.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Accept': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-CSRF-TOKEN': token
			},
			body: new URLSearchParams(data)
		})
			.then(function (r) {
				return r.json().then(function (res) {
					return { ok: r.ok, status: r.status, res: res };
				});
			})
			.then(function (result) {
				if (result.ok && result.res.success) {
					note.textContent = result.res.data || result.res.message;
					note.className = 'cal-contact-note cal-contact-note-ok';
					form.reset();
					return;
				}

				note.textContent = formErrorMessage(result.res, calContact.networkError);
				note.className = 'cal-contact-note cal-contact-note-er';
			})
			.catch(function () {
				note.textContent = calContact.networkError;
				note.className = 'cal-contact-note cal-contact-note-er';
			})
			.finally(function () {
				btn.disabled = false;
				btn.textContent = calContact.submitText;
			});
	});
});