<?php

namespace App\Http\Controllers;

use App\Services\AcfFieldService;
use App\Services\OnlineCourseService;
use App\Services\SeoService;
use Illuminate\Http\Request;

class OnlineCourseController extends Controller
{
    public function show(Request $request)
    {
        $listingSlug = rawurldecode((string) $request->route('listingSlug'));
        $courseSlug = (string) $request->route('courseSlug');

        if (! OnlineCourseService::isListingSlug($listingSlug) || ! OnlineCourseService::isCourseSlug($courseSlug)) {
            abort(404);
        }

        // Prefer canonical ASCII listing URLs for Chinese locales.
        $canonicalListing = OnlineCourseService::canonicalListingSlug();
        $normalizedListing = OnlineCourseService::normalizeListingSlug($listingSlug);
        if (
            in_array($normalizedListing, ['在线课程', '線上課程'], true)
            || str_contains(strtolower($listingSlug), '%e')
        ) {
            return redirect(OnlineCourseService::detailPath($courseSlug), 301);
        }

        $course = OnlineCourseService::resolve($courseSlug);
        if (! $course) {
            abort(404);
        }

        $listingPage = $course['listing_page'];
        $contentPost = $course['content_post'];

        if ($contentPost) {
            cal_bind_post($contentPost);
        } else {
            cal_bind_post($listingPage);
        }

        $listingHeading = AcfFieldService::getField($listingPage->ID, 'cal_online_heading') ?: $listingPage->post_title;

        $seoMeta = SeoService::forPost(($contentPost ?: $listingPage)->ID);
        $seoMeta['title'] = $course['title'];
        $seoMeta['canonical_url'] = url(OnlineCourseService::detailPath($courseSlug));

        $bodyClass = 'no-sidebar full-width-content cal-online-detail-page';

        return view('site.online-course-detail', [
            'course' => $course,
            'listingHeading' => $listingHeading,
            'listingPath' => OnlineCourseService::listingPath(),
            'bodyClass' => $bodyClass,
            'seoMeta' => $seoMeta,
            'page' => $contentPost ?: $listingPage,
        ]);
    }
}
