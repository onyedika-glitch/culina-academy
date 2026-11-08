<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PolylangService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request)
    {
        $locale = $request->route('locale');
        $slug = $request->route('slug');

        $lang = PolylangService::currentLanguage();

        if (! $slug) {
            $page = PolylangService::getFrontPage($lang);
        } else {
            // Support legacy percent-encoded Chinese slugs and normal ASCII slugs.
            $slug = rawurldecode(urldecode(trim($slug, '/')));
            $page = PolylangService::findPageBySlug($slug, $lang);
        }

        if (! $page) {
            abort(404);
        }

        cal_bind_post($page);

        $template = $page->getMeta('_wp_page_template', '');
        $bladeView = $this->resolveTemplate($template);
        $bodyClass = $this->resolveBodyClass($template, $bladeView);

        $viewData = compact('page', 'bodyClass');

        if ($bladeView === 'site.course-enrollment') {
            $viewData['courses'] = Post::with('meta')
                ->where('post_type', 'ca_course')
                ->published()
                ->forLanguage()
                ->orderBy('menu_order')
                ->get();
        }

        if (view()->exists($bladeView)) {
            return view($bladeView, $viewData);
        }

        return view('site.default-page', $viewData);
    }

    private function resolveBodyClass(?string $template, string $bladeView): string
    {
        $classes = ['no-sidebar', 'full-width-content'];

        $templateClasses = [
            'site.home' => ['home', 'cal-home-template-page', 'page-template-template-home'],
            'site.about' => ['cal-about-template-page', 'page-template-template-about'],
            'site.online-courses' => ['cal-online-page', 'page-template-template-online-courses'],
            'site.careers' => ['cal-careers-template-page', 'page-template-template-careers'],
            'site.our-school' => ['cal-our-school-template-page', 'page-template-template-our-school'],
            'site.how-to-apply' => ['cal-policy-page', 'page-template-template-how-to-apply'],
            'site.faq' => ['cal-faq-template-page', 'page-template-template-faq'],
            'site.international-students' => ['cal-international-students-template-page', 'page-template-template-international-students'],
            'site.restaurant-partners' => ['cal-rp-template-page', 'page-template-template-restaurant-partners'],
            'site.reviews' => ['cal-reviews-template-page', 'page-template-template-reviews'],
            'site.cooking-courses' => ['cal-cooking-courses-template-page', 'page-template-template-cooking-courses'],
            'site.course-enrollment' => ['cal-course-enrollment-template-page', 'page-template-template-course-enrollment'],
            'site.blog' => ['cal-blog-template-page', 'page-template-template-blog'],
            'site.privacy-policy' => ['cal-policy-page', 'page-template-template-privacy-policy'],
            'site.terms-conditions' => ['cal-policy-page', 'page-template-template-terms-conditions'],
            'site.cookies-policy' => ['cal-policy-page', 'page-template-template-cookies-policy'],
            'site.complaints-policy' => ['cal-policy-page', 'page-template-template-complaints-policy'],
            'site.attendance-policy' => ['cal-policy-page', 'page-template-template-attendance-policy'],
        ];

        if (isset($templateClasses[$bladeView])) {
            $classes = array_merge($classes, $templateClasses[$bladeView]);
        } elseif ($template) {
            $slug = preg_replace('/\.php$/', '', str_replace('template-', '', $template));
            $classes[] = 'page-template-template-'.$slug;
        }

        if (! PolylangService::isDefaultLanguage()) {
            $classes[] = 'lang-'.PolylangService::currentLanguage();
        }

        return implode(' ', array_unique($classes));
    }

    private function resolveTemplate(?string $template): string
    {
        $map = [
            'template-home.php' => 'site.home',
            'template-about.php' => 'site.about',
            'template-blog.php' => 'site.blog',
            'template-careers.php' => 'site.careers',
            'template-contact-us.php' => 'site.contact',
            'template-cooking-courses.php' => 'site.cooking-courses',
            'template-course-enrollment.php' => 'site.course-enrollment',
            'template-faq.php' => 'site.faq',
            'template-how-to-apply.php' => 'site.how-to-apply',
            'template-international-students.php' => 'site.international-students',
            'template-online-courses.php' => 'site.online-courses',
            'template-our-school.php' => 'site.our-school',
            'template-restaurant-partners.php' => 'site.restaurant-partners',
            'template-reviews.php' => 'site.reviews',
            'template-privacy-policy.php' => 'site.privacy-policy',
            'template-terms-conditions.php' => 'site.terms-conditions',
            'template-cookies-policy.php' => 'site.cookies-policy',
            'template-complaints-policy.php' => 'site.complaints-policy',
            'template-attendance-policy.php' => 'site.attendance-policy',
        ];

        if ($template && isset($map[$template])) {
            return $map[$template];
        }

        return 'site.default-page';
    }
}
