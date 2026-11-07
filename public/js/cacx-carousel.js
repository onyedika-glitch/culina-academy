(function () {
    function initCarousel(sec) {
        var trackWrap = sec.querySelector('.cacx-track-wrap');
        var track = sec.querySelector('.cacx-track');
        var slides = sec.querySelectorAll('.cacx-slide');
        var btnPrev = sec.querySelector('.cacx-prev');
        var btnNext = sec.querySelector('.cacx-next');
        var dotsWrap = sec.querySelector('.cacx-dots');
        var autoplay = parseInt(sec.dataset.autoplay, 10) || 0;
        var GAP = 16;
        var cur = 0;
        var timer = null;
        var total = slides.length;

        if (!trackWrap || !track || !slides.length) return;

        function vis() {
            var w = trackWrap.getBoundingClientRect().width;
            if (w >= 960) return 4;
            if (w >= 660) return 3;
            if (w >= 420) return 2;
            return 1;
        }

        function sw() {
            var v = vis();
            return (trackWrap.getBoundingClientRect().width - GAP * (v - 1)) / v;
        }

        function applyWidths() {
            var w = sw();
            slides.forEach(function (s) {
                s.style.width = w + 'px';
            });
        }

        function maxCur() {
            return Math.max(0, total - vis());
        }

        function goTo(n) {
            cur = Math.max(0, Math.min(n, maxCur()));
            track.style.transform = 'translateX(-' + cur * (sw() + GAP) + 'px)';
            if (btnPrev) btnPrev.disabled = cur === 0;
            if (btnNext) btnNext.disabled = cur >= maxCur();
            if (!dotsWrap) return;
            var dotBtns = dotsWrap.querySelectorAll('.cacx-dot-btn');
            var activeDot = Math.min(cur, dotBtns.length - 1);
            dotBtns.forEach(function (btn, di) {
                if (di === activeDot) {
                    btn.classList.add('cacx-active');
                    btn.setAttribute('aria-current', 'true');
                    btn.style.cssText = 'width:24px!important;background:#ffb703!important;opacity:1!important;';
                } else {
                    btn.classList.remove('cacx-active');
                    btn.setAttribute('aria-current', 'false');
                    btn.style.cssText = 'width:8px!important;background:rgba(255,183,3,.25)!important;opacity:0.5!important;';
                }
            });
        }

        function buildDots() {
            if (!dotsWrap) return;
            dotsWrap.innerHTML = '';
            for (var i = 0; i < total; i++) {
                var b = document.createElement('button');
                b.className = 'cacx-dot-btn';
                b.setAttribute('aria-label', 'Go to course slide ' + (i + 1));
                (function (x) {
                    b.onclick = function () {
                        goTo(Math.min(x, maxCur()));
                        reset();
                    };
                })(i);
                dotsWrap.appendChild(b);
            }
        }

        function reset() {
            clearInterval(timer);
            if (autoplay > 0) {
                timer = setInterval(function () {
                    goTo(cur >= maxCur() ? 0 : cur + 1);
                }, autoplay);
            }
        }

        function init() {
            applyWidths();
            buildDots();
            goTo(cur);
        }

        if (btnPrev) btnPrev.onclick = function () { goTo(cur - 1); reset(); };
        if (btnNext) btnNext.onclick = function () { goTo(cur + 1); reset(); };

        var sx = 0, dragging = false;
        track.addEventListener('mousedown', function (e) { sx = e.clientX; dragging = false; track.classList.add('cacx-dragging'); });
        track.addEventListener('mousemove', function (e) { if (track.classList.contains('cacx-dragging') && Math.abs(e.clientX - sx) > 4) dragging = true; });
        window.addEventListener('mouseup', function (e) {
            if (!track.classList.contains('cacx-dragging')) return;
            track.classList.remove('cacx-dragging');
            if (Math.abs(e.clientX - sx) > 40) { goTo(e.clientX < sx ? cur + 1 : cur - 1); reset(); }
        });
        track.addEventListener('click', function (e) { if (dragging) e.preventDefault(); }, true);

        var tx = 0;
        track.addEventListener('touchstart', function (e) { tx = e.touches[0].clientX; }, { passive: true });
        track.addEventListener('touchend', function (e) {
            var d = tx - e.changedTouches[0].clientX;
            if (Math.abs(d) > 40) { goTo(d > 0 ? cur + 1 : cur - 1); reset(); }
        });

        function run() { init(); if (autoplay > 0) reset(); }
        if (document.readyState === 'complete') run();
        else window.addEventListener('load', run);

        var rto;
        window.addEventListener('resize', function () {
            clearTimeout(rto);
            rto = setTimeout(function () { cur = Math.min(cur, maxCur()); init(); }, 80);
        });
    }

    function hydrateLazyImages(root) {
        root.querySelectorAll('.cacx-lazy-img').forEach(function (img) {
            if (img.dataset.loaded === '1') {
                return;
            }
            var src = img.getAttribute('data-src');
            if (!src) {
                return;
            }
            img.src = src;
            img.dataset.loaded = '1';
        });
    }

    function observeLazyImages() {
        var sections = document.querySelectorAll('.cacx-section');
        if (!('IntersectionObserver' in window)) {
            sections.forEach(hydrateLazyImages);
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    hydrateLazyImages(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: '200px 0px' });

        sections.forEach(function (section) {
            observer.observe(section);
        });
    }

    function boot() {
        observeLazyImages();
        document.querySelectorAll('.cacx-section').forEach(initCarousel);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();