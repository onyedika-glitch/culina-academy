(function() {
	const topbar    = document.querySelector('.crisp-nav-topbar');
	const flagsBar  = document.querySelector('.crisp-nav-mobile-flags-bar');
	const header    = document.querySelector('.crisp-nav-header');

	function getVisibleBottom(el) {
		if (!el) return 0;
		const styles = window.getComputedStyle(el);
		if (styles.display === 'none' || styles.visibility === 'hidden') return 0;
		return Math.max(0, Math.round(el.getBoundingClientRect().bottom));
	}

	function getTotalHeaderHeight() {
		return Math.max(getVisibleBottom(topbar), getVisibleBottom(flagsBar), getVisibleBottom(header));
	}

	function updateBodyPadding() {
		const totalHeight = getTotalHeaderHeight();
		const currentPadding = parseInt(window.getComputedStyle(document.body).paddingTop, 10) || 0;
		if (Math.abs(currentPadding - totalHeight) > 2) {
			document.body.style.paddingTop = totalHeight + 'px';
		}
	}

	function syncHeaderPosition() {
		if (!header) return;

		if (window.innerWidth <= 1024) {
			const flagsHeight = flagsBar ? flagsBar.offsetHeight : 0;
			header.style.top = flagsHeight + 'px';
		} else if (topbar) {
			header.style.top = topbar.offsetHeight + 'px';
		}

		updateBodyPadding();
	}

	syncHeaderPosition();

	if (typeof ResizeObserver !== 'undefined') {
		const resizeObserver = new ResizeObserver(syncHeaderPosition);

		if (topbar) {
			resizeObserver.observe(topbar);
		}

		if (flagsBar) {
			resizeObserver.observe(flagsBar);
		}

		if (header) {
			resizeObserver.observe(header);
		}
	}

	window.addEventListener('resize', syncHeaderPosition);
	window.addEventListener('orientationchange', syncHeaderPosition);

	function initInteractions() {
		const toggle = document.querySelector('.crisp-nav-mobile-toggle');
		const closeBtn = document.querySelector('.crisp-nav-mobile-close');
		const navMenu = document.querySelector('.crisp-nav-nav');
		const overlay = document.querySelector('.crisp-nav-mobile-overlay');
		const navItems = document.querySelectorAll('.crisp-nav-item');
		const dropdownLinks = document.querySelectorAll('.crisp-nav-link');
		const dropdownItems = document.querySelectorAll('.crisp-nav-dropdown-item');

		function setActiveLink() {
			const currentUrl = window.location.href.replace(/\/$/, '');

			dropdownLinks.forEach(function(link) {
				link.classList.remove('active');

				if (link.href.replace(/\/$/, '') === currentUrl) {
					link.classList.add('active');
				}
			});
		}

		function openMobileMenu() {
			if (!navMenu || !overlay) {
				return;
			}

			navMenu.classList.add('active');
			overlay.style.display = 'block';
			setTimeout(function() {
				overlay.classList.add('active');
			}, 10);
			document.body.style.overflow = 'hidden';
		}

		function closeMobileMenu() {
			if (!navMenu || !overlay) {
				return;
			}

			navMenu.classList.remove('active');
			overlay.classList.remove('active');
			setTimeout(function() {
				overlay.style.display = 'none';
			}, 300);
			document.body.style.overflow = '';
			navItems.forEach(function(item) {
				item.classList.remove('active');
			});
		}

		if (toggle) {
			toggle.addEventListener('click', openMobileMenu);
		}

		if (closeBtn) {
			closeBtn.addEventListener('click', closeMobileMenu);
		}

		if (overlay) {
			overlay.addEventListener('click', closeMobileMenu);
		}

		navItems.forEach(function(item) {
			const hasDropdown = item.querySelector('.crisp-nav-dropdown');

			if (!hasDropdown) {
				return;
			}

			const link = item.querySelector('.crisp-nav-link');

			link.addEventListener('click', function(e) {
				if (window.innerWidth > 1024 || !e.target.closest('.crisp-nav-dropdown-icon')) {
					return;
				}

				e.preventDefault();
				e.stopPropagation();

				if (item.classList.contains('active')) {
					item.classList.remove('active');
					return;
				}

				navItems.forEach(function(other) {
					other.classList.remove('active');
				});
				item.classList.add('active');
			});
		});

		dropdownItems.forEach(function(item) {
			item.addEventListener('click', function() {
				if (window.innerWidth <= 1024) {
					closeMobileMenu();
				}
			});
		});

		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				closeMobileMenu();
				closeGalleryLightbox();
			}
		});

		window.addEventListener('resize', function() {
			if (window.innerWidth > 1024) {
				closeMobileMenu();
			}
		});

		const customFooter = document.querySelector('.cal-custom-footer');

		if (customFooter) {
			customFooter.style.fontFamily = window.getComputedStyle(document.body).fontFamily;
		}

		const gallerySlider = document.querySelector('.cal-gallery-slider');

		if (gallerySlider) {
			const track = gallerySlider.querySelector('.cal-gallery-track');
			const prevBtn = gallerySlider.querySelector('.cal-gallery-prev');
			const nextBtn = gallerySlider.querySelector('.cal-gallery-next');
			const total = parseInt(gallerySlider.dataset.total, 10);
			const visible = parseInt(gallerySlider.dataset.visible, 10);
			let currentIndex = 0;
			const maxIndex = Math.max(0, Math.ceil(total / visible) - 1);

			function updateSlider() {
				track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
			}

			function slideNext() {
				currentIndex = currentIndex >= maxIndex ? 0 : currentIndex + 1;
				updateSlider();
			}

			if (prevBtn) prevBtn.addEventListener('click', function() {
				currentIndex = currentIndex === 0 ? maxIndex : currentIndex - 1;
				updateSlider();
			});

			if (nextBtn) nextBtn.addEventListener('click', slideNext);

			var autoInterval = setInterval(slideNext, 3500);

			gallerySlider.addEventListener('mouseenter', function() {
				clearInterval(autoInterval);
			});

			gallerySlider.addEventListener('mouseleave', function() {
				autoInterval = setInterval(slideNext, 3500);
			});

			updateSlider();
		}

		const galleryLightbox = document.querySelector('.cal-gallery-lightbox');
		const galleryLightboxImage = galleryLightbox ? galleryLightbox.querySelector('img') : null;
		const galleryLightboxClose = galleryLightbox ? galleryLightbox.querySelector('.cal-gallery-lightbox-close') : null;
		const lightboxPrev = galleryLightbox ? galleryLightbox.querySelector('.cal-gallery-lightbox-prev') : null;
		const lightboxNext = galleryLightbox ? galleryLightbox.querySelector('.cal-gallery-lightbox-next') : null;
		let lightboxLinks = [];
		let currentLightboxIndex = -1;

		function collectLightboxLinks() {
			lightboxLinks = Array.from(document.querySelectorAll('.cal-gallery-lightbox-link'));
		}

		function openGalleryLightbox(link) {
			if (!galleryLightbox || !galleryLightboxImage) {
				return;
			}

			collectLightboxLinks();
			currentLightboxIndex = lightboxLinks.indexOf(link);

			const image = link.querySelector('img');
			galleryLightboxImage.src = link.href;
			galleryLightboxImage.alt = image ? image.alt : '';
			galleryLightbox.classList.add('active');
			galleryLightbox.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
			updateLightboxNav();
		}

		function closeGalleryLightbox() {
			if (!galleryLightbox || !galleryLightboxImage) {
				return;
			}

			galleryLightbox.classList.remove('active');
			galleryLightbox.setAttribute('aria-hidden', 'true');
			galleryLightboxImage.src = '';
			document.body.style.overflow = '';
			currentLightboxIndex = -1;
		}

		function navigateLightbox(direction) {
			if (currentLightboxIndex < 0 || lightboxLinks.length === 0) return;

			var newIndex = currentLightboxIndex + direction;

			if (newIndex < 0 || newIndex >= lightboxLinks.length) return;

			currentLightboxIndex = newIndex;
			var link = lightboxLinks[currentLightboxIndex];
			var image = link.querySelector('img');
			galleryLightboxImage.src = link.href;
			galleryLightboxImage.alt = image ? image.alt : '';
			updateLightboxNav();
		}

		function updateLightboxNav() {
			if (lightboxPrev) lightboxPrev.style.display = currentLightboxIndex > 0 ? 'flex' : 'none';
			if (lightboxNext) lightboxNext.style.display = currentLightboxIndex < lightboxLinks.length - 1 ? 'flex' : 'none';
		}

		document.querySelectorAll('.cal-gallery-lightbox-link').forEach(function(link) {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				openGalleryLightbox(link);
			});
		});

		if (galleryLightbox) {
			galleryLightbox.addEventListener('click', function(e) {
				if (e.target === galleryLightbox) {
					closeGalleryLightbox();
				}
			});
		}

		if (lightboxPrev) {
			lightboxPrev.addEventListener('click', function(e) {
				e.stopPropagation();
				navigateLightbox(-1);
			});
		}

		if (lightboxNext) {
			lightboxNext.addEventListener('click', function(e) {
				e.stopPropagation();
				navigateLightbox(1);
			});
		}

		document.addEventListener('keydown', function(e) {
			if (!galleryLightbox || !galleryLightbox.classList.contains('active')) return;

			if (e.key === 'Escape') {
				closeGalleryLightbox();
			} else if (e.key === 'ArrowLeft') {
				e.preventDefault();
				navigateLightbox(-1);
			} else if (e.key === 'ArrowRight') {
				e.preventDefault();
				navigateLightbox(1);
			}
		});

		if (galleryLightboxClose) {
			galleryLightboxClose.addEventListener('click', function(e) {
				e.stopPropagation();
				closeGalleryLightbox();
			});
		}

		setActiveLink();
	}

	if ('requestIdleCallback' in window) {
		requestIdleCallback(initInteractions, { timeout: 2000 });
	} else {
		window.addEventListener('load', initInteractions, { once: true });
	}
})();