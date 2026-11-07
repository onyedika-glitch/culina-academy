document.addEventListener('DOMContentLoaded', function() {
	var grid = document.getElementById('videoGrid');
	var modal = document.getElementById('modal');
	var player = document.getElementById('player');
	var closeModalBtn = document.getElementById('closeModalBtn');
	var previousFocus = null;

	if (!grid || !modal || !player || !closeModalBtn) {
		return;
	}

	function openYoutube(id) {
		var iframe = document.createElement('iframe');
		iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
		iframe.title = document.getElementById('reviewsModalTitle').textContent;
		iframe.allow = 'autoplay; encrypted-media';
		iframe.allowFullscreen = true;
		player.replaceChildren(iframe);
		modal.style.display = 'flex';
		modal.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
		closeModalBtn.focus();
	}

	function openMp4(url) {
		var unsupportedText = (window.calReviews && calReviews.unsupportedVideoText) ? calReviews.unsupportedVideoText : '';
		var video = document.createElement('video');
		var source = document.createElement('source');
		video.controls = true;
		video.autoplay = true;
		video.playsInline = true;
		video.preload = 'metadata';
		source.src = url;
		source.type = 'video/mp4';
		video.appendChild(source);
		video.appendChild(document.createTextNode(unsupportedText));
		player.replaceChildren(video);
		modal.style.display = 'flex';
		modal.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
		closeModalBtn.focus();
	}

	function closeModal() {
		modal.style.display = 'none';
		modal.setAttribute('aria-hidden', 'true');
		player.replaceChildren();
		document.body.style.overflow = '';
		if (previousFocus) {
			previousFocus.focus();
			previousFocus = null;
		}
	}

	grid.addEventListener('click', function(event) {
		var trigger = event.target.closest('.card-video-trigger');

		if (!trigger) {
			return;
		}

		previousFocus = trigger;
		if (trigger.dataset.videoType === 'mp4') {
			openMp4(trigger.dataset.videoSrc);
		} else {
			openYoutube(trigger.dataset.videoSrc);
		}
	});

	closeModalBtn.addEventListener('click', closeModal);
	modal.addEventListener('click', function(event) {
		if (event.target === modal) {
			closeModal();
		}
	});
	document.addEventListener('keydown', function(event) {
		if (event.key === 'Escape' && modal.style.display === 'flex') {
			closeModal();
		}
		if (event.key === 'Tab' && modal.style.display === 'flex') {
			event.preventDefault();
			closeModalBtn.focus();
		}
	});
});
