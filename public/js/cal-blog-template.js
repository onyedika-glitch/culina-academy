(function() {
	var grid = document.getElementById('cal-blog-grid');
	var loader = document.getElementById('cal-blog-loader');
	var maxPages = document.querySelector('.cal-blog-max-pages');

	if (!grid || !maxPages) return;

	var page = 1;
	var max = parseInt(maxPages.textContent, 10);
	var loading = false;
	var allLoaded = false;

	function loadMore() {
		if (loading || allLoaded) return;
		loading = true;

		if (loader) loader.style.display = 'block';

		var xhr = new XMLHttpRequest();
		xhr.open('POST', calBlog.ajaxUrl, true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.onload = function() {
			if (xhr.status === 200) {
				try {
					var response = JSON.parse(xhr.responseText);
					if (response.items) {
						var temp = document.createElement('div');
						temp.innerHTML = response.items;
						while (temp.firstChild) {
							grid.appendChild(temp.firstChild);
						}
						page++;
					}
					if (response.status === 'no-more-posts' || page >= max) {
						allLoaded = true;
						if (loader) loader.style.display = 'none';
					}
				} catch (e) {}
			}
			loading = false;
			if (loader && allLoaded) loader.style.display = 'none';
		};
		xhr.onerror = function() {
			loading = false;
			if (loader) loader.style.display = 'none';
		};
		xhr.send('action=cal_blog_load_more&_token=' + encodeURIComponent(calBlog.nonce) + '&page=' + (page + 1));
	}

	if (window.IntersectionObserver) {
		var sentinel = document.createElement('div');
		sentinel.style.height = '1px';
		grid.parentNode.appendChild(sentinel);

		var observer = new IntersectionObserver(function(entries) {
			if (entries[0].isIntersecting) {
				loadMore();
			}
		}, { rootMargin: '400px' });

		observer.observe(sentinel);
	} else {
		function onScroll() {
			if (loading || allLoaded) return;
			var scrollPos = window.innerHeight + window.scrollY;
			var threshold = document.body.scrollHeight - 400;
			if (scrollPos >= threshold) {
				loadMore();
			}
		}
		window.addEventListener('scroll', onScroll);
		onScroll();
	}
})();
