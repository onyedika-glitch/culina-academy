<?php

require __DIR__.'/admin.php';

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OnlineCourseController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\WaitlistInviteController;
use App\Http\Middleware\CachePublicResponse;
use App\Http\Middleware\SetLocale;

// All public languages use a URL prefix: /en (default), /fr, /es, /it, /zh, /zh-tw
$locales = 'en|fr|es|it|zh|zh-tw';
// Include legacy Chinese listing labels so old bookmarks still resolve.
$onlineListingSlugs = 'online-courses|cours-en-ligne|cursos-en-linea|corsi-online|zaixian-kecheng|zaixian-kecheng-tw|在线课程|線上課程';
$onlineCourseSlugs = 'introduction-to-the-professional-chef-online-course|introduction-to-the-professional-chef-online-course-(?:fr|es|it|zh|zh-tw)|michelin-star-advanced-techniques-online-course|michelin-star-advanced-techniques-online-course-(?:fr|es|it|zh|zh-tw)';

$localePath = static function (string $locale, string $path = ''): string {
    $path = trim($path, '/');

    return $path === '' ? '/' . $locale : '/' . $locale . '/' . $path;
};

// Root always lands on English locale home.
Route::get('/', static fn () => redirect('/en', 301));

// ── Chinese legacy URL redirects (encoded CJK slugs → ASCII) ──
$chinesePageRedirects = [
    'zh' => [
        '在线课程' => 'zaixian-kecheng',
        '课程注册' => 'kecheng-zhuce',
        '酒店餐饮发展' => 'jiudian-canyin-fazhan',
        '合作餐厅' => 'hezuo-canting',
    ],
    'zh-tw' => [
        '線上課程' => 'zaixian-kecheng-tw',
        '課程報名' => 'kecheng-baoming',
        '餐旅發展' => 'canlu-fazhan',
        '合作餐廳' => 'hezuo-canting-tw',
    ],
];

foreach ($chinesePageRedirects as $locale => $map) {
    foreach ($map as $from => $to) {
        Route::get('/' . $locale . '/' . $from, static fn () => redirect('/' . $locale . '/' . $to, 301));
        Route::get('/' . $locale . '/' . rawurlencode($from), static fn () => redirect('/' . $locale . '/' . $to, 301));
    }

    // Online course detail under Chinese listing label → ASCII listing
    $listingFrom = $locale === 'zh' ? '在线课程' : '線上課程';
    $listingTo = $locale === 'zh' ? 'zaixian-kecheng' : 'zaixian-kecheng-tw';
    Route::get('/' . $locale . '/' . $listingFrom . '/{courseSlug}', static function (string $courseSlug) use ($locale, $listingTo) {
        return redirect('/' . $locale . '/' . $listingTo . '/' . $courseSlug, 301);
    })->where('courseSlug', $onlineCourseSlugs);
    Route::get('/' . $locale . '/' . rawurlencode($listingFrom) . '/{courseSlug}', static function (string $courseSlug) use ($locale, $listingTo) {
        return redirect('/' . $locale . '/' . $listingTo . '/' . $courseSlug, 301);
    })->where('courseSlug', $onlineCourseSlugs);
}

// ── Duplicate homepage aliases → canonical locale root (301) ──
Route::get('/en/home', static fn () => redirect('/en', 301));
Route::get('/fr/accueil', static fn () => redirect('/fr', 301));
Route::get('/it/home-2', static fn () => redirect('/it', 301));
Route::get('/es/inicio', static fn () => redirect('/es', 301));
Route::get('/zh/home-3', static fn () => redirect('/zh', 301));
Route::get('/zh-tw/home-4', static fn () => redirect('/zh-tw', 301));

// ── Duplicate Chinese blog/careers routes → canonical (301) ──
Route::get('/zh/blog', static fn () => redirect('/zh/blog-zh', 301));
Route::get('/zh/careers', static fn () => redirect('/zh/careers-zh', 301));
Route::get('/zh-tw/blog', static fn () => redirect('/zh-tw/blog-zh-tw', 301));
Route::get('/zh-tw/careers', static fn () => redirect('/zh-tw/careers-zh-tw', 301));

Route::get('/sitemap.xml', [SitemapController::class, 'xml']);
Route::get('/sitemap', static fn () => redirect('/sitemap.xml', 301));
Route::get('/robots.txt', [SitemapController::class, 'robots']);

// ── Legacy bookmark / broken URL 301 redirects (audit fixes) ──
// Italian blog terms post → Italian terms page
Route::get('/it/blog/termini-e-condizioni', static fn () => redirect('/it/terms-and-conditions', 301));
// Spanish/Italian legacy blog posts → respective blog indexes
Route::get('/es/blog/656-que-significa-ser-un-chef', static fn () => redirect('/es/blog', 301));
Route::get('/it/blog/900-la-cucina-italiana-simbolo-di-arte-e-cultura', static fn () => redirect('/it/blog', 301));
// Hospitality course URL used old/short slug in all affected locales
Route::get('/en/courses/hospitality-management-advanced-food-science', static fn () => redirect('/en/courses/hospitality-management-advanced-food-science-course', 301));
Route::get('/fr/courses/hospitality-management-advanced-food-science', static fn () => redirect('/fr/courses/gestion-hoteliere-sciences-alimentaires-avancees', 301));
Route::get('/es/courses/hospitality-management-advanced-food-science', static fn () => redirect('/es/courses/gestion-hotelera-ciencia-avanzada-alimentos', 301));
// Legacy Joomla URLs (application form, old course slugs, auto-generated)
Route::get('/en/component/rsform/form/4-application-form', static fn () => redirect('/en/course-enrollment', 301));
Route::get('/en/professional-chef-course-italian-cuisine-325-hours-3', static fn () => redirect('/en/courses/professional-chef-italian-cuisine', 301));
Route::get('/en/professional-patisserie-and-confectionary-course-1040-hours', static fn () => redirect('/en/courses/professional-patisserie', 301));
Route::get('/en/auto-generate-from-title', static fn () => redirect('/en', 301));

Route::get('/wp-content/uploads/{path}', function (string $path) {
    return redirect('/uploads/' . $path, 301);
})->where('path', '.*');

Route::get('/images/{path}', function (string $path) {
    return redirect('/uploads/joomla/images/' . $path, 301);
})->where('path', '.*');

// ── SEO: Old Joomla URL 301 Redirects ──
// Order matters: more specific routes first, then catch-all .html strip

// 1. Old Joomla Blog Post 301 Redirects (strip Joomla ID prefix like /blog/105-slug.html)
Route::get('/{locale}/blog/{id}-{slug}.html', function (string $locale, string $id, string $slug) use ($localePath) {
    return redirect($localePath($locale, 'blog/' . $slug), 301);
})->where('locale', $locales)->where('id', '[0-9]+')->where('slug', '[a-zA-Z0-9\\\\-]+');

Route::get('/blog/{id}-{slug}.html', function (string $id, string $slug) use ($localePath) {
    return redirect($localePath('en', 'blog/' . $slug), 301);
})->where('id', '[0-9]+')->where('slug', '[a-zA-Z0-9\\\\-]+');

// 2. Old Joomla Course URL 301 Redirects (cooking-courses/...html → courses/...)
$oldCourseRedirects = [
    'professional-chef-course-{hours}' => 'courses/professional-chef-course',
    'advanced-professional-chef-course-{hours}' => 'courses/advanced-professional-chef-course',
    'michelin-star-chef-course-{hours}' => 'courses/michelin-star-chef-course',
    'introduction-to-the-professional-chef-course-{hours}' => 'courses/introduction-professional-chef',
    'kosher-professional-chef-course-{hours}' => 'courses/kosher-professional-chef-course',
    'professional-patisserie-and-confectionery-course-{hours}' => 'courses/professional-patisserie',
    'hospitality-management-and-advanced-food-science-{hours}' => 'courses/hospitality-management-advanced-food-science-course',
    'halal-advanced-professional-chef-course-{hours}' => 'courses/halal-advanced-professional-chef',
    'intensive-professional-pastry-programme-{hours}' => 'courses/intensive-professional-pastry',
    'professional-chef-course-italian-cuisine-{hours}' => 'courses/professional-chef-italian-cuisine',
    'advanced-cooking-techniques-{hours}' => 'courses/advanced-cooking-techniques',
];

foreach ($oldCourseRedirects as $oldPattern => $newPath) {
    Route::get('/{locale}/cooking-courses/' . $oldPattern . '.html', function (string $locale) use ($localePath, $newPath) {
        return redirect($localePath($locale, $newPath), 301);
    })->where('locale', $locales);
}

// Deeply nested old patisserie course
Route::get('/{locale}/cooking-courses/professional-patisserie-and-confectionary-courses/professional-patisserie-and-confectionary-course-{hours}.html', function (string $locale) use ($localePath) {
    return redirect($localePath($locale, 'courses/professional-patisserie'), 301);
})->where('locale', $locales);

// 3. Old Joomla Online Course URL 301 Redirects
Route::get('/{locale}/online-courses/introduction-to-the-professional-chef-online-course.html', function (string $locale) {
    return redirect(\App\Services\OnlineCourseService::detailPath(
        \App\Services\OnlineCourseService::INTRODUCTION_SLUG,
        $locale
    ), 301);
})->where('locale', $locales);

Route::get('/{locale}/online-courses/michelin-star-advanced-techniques-online-course.html', function (string $locale) {
    return redirect(\App\Services\OnlineCourseService::detailPath(
        \App\Services\OnlineCourseService::MICHELIN_SLUG,
        $locale
    ), 301);
})->where('locale', $locales);

// 4. Old Joomla specific page redirects (with .html → different new path)
$oldPageRedirects = [
    'about-us/our-schools' => 'hospitality-development',
    'about-us/careers' => 'careers',
    'about-us/apply-now' => 'how-to-apply',
    'about-us/frequently-asked-questions' => 'faqs',
    'asic-international-students' => 'international-students',
    'gdpr' => 'privacy-policy',
    'terms-conditions' => 'terms-and-conditions',
    'complaints-policy-and-procedure' => 'complaints-policy-and-procedure',
];

foreach ($oldPageRedirects as $oldSlug => $newPath) {
    Route::get('/' . $oldSlug . '.html', fn () => redirect($localePath('en', $newPath), 301));
    Route::get('/en/' . $oldSlug . '.html', fn () => redirect($localePath('en', $newPath), 301));
}

Route::get('/site-map.html', static fn () => redirect('/sitemap.xml', 301));
Route::get('/en/site-map.html', static fn () => redirect('/sitemap.xml', 301));
Route::get('/login-en.html', static fn () => redirect('/en', 301));
Route::get('/en/login-en.html', static fn () => redirect('/en', 301));
Route::get('/newsletter-profile.html', static fn () => redirect('/en', 301));
Route::get('/en/newsletter-profile.html', static fn () => redirect('/en', 301));

// 5. Generic catch-all: strip .html extension (keep same base path under locale when present)
Route::get('/{slug}.html', function (string $slug) use ($localePath) {
    $slug = trim($slug, '/');
    if (preg_match('#^(en|fr|es|it|zh|zh-tw)(?:/|$)(.*)$#', $slug, $m)) {
        return redirect($localePath($m[1], $m[2] ?? ''), 301);
    }

    return redirect($localePath('en', $slug), 301);
})->where('slug', '[a-zA-Z0-9/\\\\-]+');

// ── SEO: Old site path 301 redirects (moved/renamed pages) ──
Route::get('/our-school', static fn () => redirect('/en/hospitality-development', 301));
Route::get('/en/our-school', static fn () => redirect('/en/hospitality-development', 301));
Route::get('/complaints-policy', static fn () => redirect('/en/complaints-policy-and-procedure', 301));
Route::get('/en/complaints-policy', static fn () => redirect('/en/complaints-policy-and-procedure', 301));
Route::get('/faq', static fn () => redirect('/en/faqs', 301));
Route::get('/about', static fn () => redirect('/en/about-us', 301));
Route::get('/contact', static fn () => redirect('/en/contact-us', 301));
Route::get('/{locale}/faq', fn (string $locale) => redirect($localePath($locale, 'faqs'), 301))->where('locale', $locales);
Route::get('/{locale}/about', fn (string $locale) => redirect($localePath($locale, 'about-us'), 301))->where('locale', $locales);
Route::get('/{locale}/contact', fn (string $locale) => redirect($localePath($locale, 'contact-us'), 301))->where('locale', $locales);

// ── Stripe Webhook (no CSRF, no web middleware) ──
Route::post('/api/webhooks/stripe', StripeWebhookController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('stripe.webhook');

// ── Locale-prefixed public site routes (/en, /it, /fr, …) ──
Route::middleware([CachePublicResponse::class, SetLocale::class])
    ->prefix('{locale}')
    ->where(['locale' => $locales])
    ->group(function () use ($onlineListingSlugs, $onlineCourseSlugs): void {
        Route::get('/', [PageController::class, 'show'])->name('home');

        Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
        Route::post('/blog/load-more', [BlogController::class, 'loadMore'])->name('blog.load-more');
        Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/course/{slug}', [CourseController::class, 'show'])->name('courses.show.wp');
        Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('courses.show');
        Route::post('/courses/enroll/{intakeId}', [CourseController::class, 'enroll'])->name('courses.enroll');
        Route::get('/course-enrollment', [CourseController::class, 'showEnrollmentForm'])->name('courses.enrollment-form');

        Route::get('/contact-us', [ContactController::class, 'show'])->name('contact');
        Route::post('/contact-us', [ContactController::class, 'submit'])->name('contact.submit');

        Route::post('/booking/ajax', [BookingController::class, 'handle'])->name('booking.ajax');
        Route::post('/booking/submit', [BookingController::class, 'submitBooking'])->name('booking.submit');
        Route::post('/booking/waitinglist', [BookingController::class, 'submitWaitingList'])->name('booking.waitinglist');
        Route::get('/booking/success', [BookingController::class, 'bookingSuccess'])->name('booking.success');
        Route::get('/booking/cancelled', [BookingController::class, 'bookingCancelled'])->name('booking.cancelled');

        Route::get('/waitlist/invite/{token}', [WaitlistInviteController::class, 'show'])->name('waitlist.invite');
        Route::post('/waitlist/invite/{token}/claim', [WaitlistInviteController::class, 'claim'])->name('waitlist.invite.claim');

        Route::get('/{listingSlug}/{courseSlug}', [OnlineCourseController::class, 'show'])
            ->where([
                'listingSlug' => $onlineListingSlugs,
                'courseSlug' => $onlineCourseSlugs,
            ])
            ->name('online-courses.show');

        Route::get('/{slug}', [PageController::class, 'show'])
            ->where('slug', '(?!admin$)[a-z0-9\-]+')
            ->name('page.show');
    });

// ── Legacy unprefixed English URLs → /en/... (SEO + bookmarks) ──
Route::get('/blog', static fn () => redirect('/en/blog', 301));
Route::get('/blog/{slug}', static fn (string $slug) => redirect('/en/blog/' . $slug, 301));
Route::get('/courses', static fn () => redirect('/en/courses', 301));
Route::get('/course/{slug}', static fn (string $slug) => redirect('/en/course/' . $slug, 301));
Route::get('/courses/{slug}', static fn (string $slug) => redirect('/en/courses/' . $slug, 301));
Route::get('/course-enrollment', static fn () => redirect('/en/course-enrollment', 301));
Route::get('/contact-us', static fn () => redirect('/en/contact-us', 301));
Route::get('/booking/success', static fn () => redirect('/en/booking/success', 301));
Route::get('/booking/cancelled', static fn () => redirect('/en/booking/cancelled', 301));
Route::get('/waitlist/invite/{token}', static fn (string $token) => redirect('/en/waitlist/invite/' . $token, 301));

Route::get('/{listingSlug}/{courseSlug}', function (string $listingSlug, string $courseSlug) {
    return redirect('/en/' . $listingSlug . '/' . $courseSlug, 301);
})->where([
    'listingSlug' => $onlineListingSlugs,
    'courseSlug' => $onlineCourseSlugs,
]);

Route::get('/{slug}', static function (string $slug) {
    return redirect('/en/' . $slug, 301);
})->where('slug', '(?!admin$|uploads$|css$|js$|assets$|api$|sitemap\\.xml$|robots\\.txt$)[a-z0-9\-]+');
