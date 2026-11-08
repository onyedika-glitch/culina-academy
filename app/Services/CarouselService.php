<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMeta;

class CarouselService
{
    public static function render(): string
    {
        $lang = PolylangService::currentLanguage();

        return CalCache::remember(
            "cal.carousel.html.{$lang}",
            now()->addHour(),
            static fn () => static::buildHtml($lang),
        );
    }

    protected static function buildHtml(string $lang): string
    {
        $courses = Post::with('meta')
            ->where('post_type', 'ca_course')
            ->published()
            ->forLanguage($lang)
            ->orderBy('menu_order')
            ->orderBy('post_title')
            ->get();

        if ($courses->isEmpty()) {
            return '';
        }

        $eyebrow = UiTranslationService::translate('Our Courses', $lang);
        $title = UiTranslationService::translate('Learn the art of <em>professional cooking.</em>', $lang);
        $viewCourse = UiTranslationService::translate('View Course', $lang);
        $browseMore = UiTranslationService::translate('Browse More Courses', $lang);
        // Use the cooking-courses page in this language only (no English ID fallback).
        $cookingCoursesPageId = PolylangService::getTranslationId(44, $lang);
        if (! $cookingCoursesPageId && PolylangService::getPostLanguage(44) === $lang) {
            $cookingCoursesPageId = 44;
        }
        $moreCoursesUrl = $cookingCoursesPageId
            ? PolylangService::buildPostUrl($cookingCoursesPageId, $lang)
            : PolylangService::localizedPath('/cooking-courses', $lang);

        $attachmentUrls = static::attachmentUrlsForCourses($courses);
        $uid = 'cacx_'.uniqid();
        ob_start();
        ?>
        <section class="cacx-section" id="<?= $uid ?>" data-autoplay="4500">
            <div class="cacx-header">
                <div class="cacx-header-left">
                    <span class="cacx-eyebrow"><?= esc_html($eyebrow) ?></span>
                    <h2 class="cacx-title"><?= $title ?></h2>
                </div>
                <div class="cacx-nav">
                    <button class="cacx-arrow cacx-prev" aria-label="Previous">&#8592;</button>
                    <button class="cacx-arrow cacx-next" aria-label="Next">&#8594;</button>
                </div>
            </div>
            <div class="cacx-track-wrap">
                <div class="cacx-track">
                <?php foreach ($courses as $course) {
                    $id = $course->ID;
                    $imageMeta = AcfFieldService::parseValuePublic($course->getMeta('ca_image') ?: $course->getMeta('_thumbnail_id'));
                    $imageData = static::resolveCourseImageData($imageMeta, $attachmentUrls);
                    $image = $imageData['url'] ?? '';
                    // Fallback placeholder when course has no featured image
                    if (! $image) {
                        $image = 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"><rect fill="#e8e8e8" width="800" height="600"/><text x="400" y="310" text-anchor="middle" fill="#999" font-family="sans-serif" font-size="22">'.htmlspecialchars($course->post_title, ENT_QUOTES, 'UTF-8').'</text></svg>');
                    }
                    $badge = CourseDisplayService::badge($course->getMeta('ca_badge', ''), $lang);
                    $hours = $course->getMeta('ca_hours', '');
                    $dur = CourseDisplayService::duration($course->getMeta('ca_duration', ''), $lang);
                    $durationLine = CulinaAcademy::formatCourseDuration($hours, $dur, $lang);
                    // Always link to this language's course URL (ignore cross-language ca_course_url).
                    $url = PolylangService::buildPostUrl((int) $id, $lang);
                    $price = CulinaAcademy::formatPrice(AcfFieldService::parseValuePublic($course->getMeta('ca_price', '')));
                    ?>
                <div class="cacx-slide">
                    <a href="<?= esc_url($url) ?>" class="cacx-card">
                        <div class="cacx-img-wrap">
                            <?php if ($image) { ?>
                            <img data-src="<?= esc_url($image) ?>"
                                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 3'%3E%3C/svg%3E"
                                 alt="<?= esc_attr($course->post_title) ?>"
                                 <?php if (! empty($imageData['width']) && ! empty($imageData['height'])) { ?>
                                 width="<?= (int) $imageData['width'] ?>" height="<?= (int) $imageData['height'] ?>"
                                 <?php } ?>
                                 class="cacx-lazy-img"
                                 loading="lazy"
                                 decoding="async"
                                 fetchpriority="low">
                            <?php } ?>
                            <div class="cacx-img-overlay"></div>
                            <?php if ($badge) { ?>
                            <span class="cacx-badge"><?= esc_html($badge) ?></span>
                            <?php } ?>
                        </div>
                        <div class="cacx-body">
                            <h3 class="cacx-name"><?= esc_html($course->post_title) ?></h3>
                            <div class="cacx-meta">
                                <?php if ($durationLine) { ?><span><?= esc_html($durationLine) ?></span><?php } ?>
                            </div>
                            <div class="cacx-footer">
                                <?php if ($price) { ?><span class="cacx-price"><?= $price ?></span><?php } ?>
                                <span class="cacx-cta"><?= esc_html($viewCourse) ?> &#8594;</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php } ?>
                </div>
            </div>
            <div class="cacx-dots" aria-label="Course carousel navigation"></div>
            <div class="cacx-btn-wrap">
                <a href="<?= esc_url($moreCoursesUrl) ?>" class="cacx-more-btn"><?= esc_html($browseMore) ?> <span class="cacx-arrow-icon">&#8594;</span></a>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    /** @return array<int, string> */
    protected static function attachmentUrlsForCourses($courses): array
    {
        $ids = [];

        foreach ($courses as $course) {
            foreach (['ca_image', '_thumbnail_id'] as $key) {
                $value = AcfFieldService::parseValuePublic($course->getMeta($key));
                if (is_array($value) && ! empty($value['ID'])) {
                    $ids[] = (int) $value['ID'];
                } elseif (is_numeric($value)) {
                    $ids[] = (int) $value;
                }
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return [];
        }

        $files = PostMeta::query()
            ->whereIn('post_id', $ids)
            ->where('meta_key', '_wp_attached_file')
            ->pluck('meta_value', 'post_id');

        $urls = [];
        foreach ($files as $postId => $file) {
            $urls[(int) $postId] = AcfFieldService::webpWhenAvailable('/uploads/'.ltrim((string) $file, '/'));
        }

        return $urls;
    }

    /** @return array{url:string,width:int,height:int} */
    protected static function resolveCourseImageData(mixed $imageMeta, array $attachmentUrls): array
    {
        $src = AcfFieldService::attachmentSrc($imageMeta, 'medium');
        if ($src !== null && $src['url'] !== '') {
            return $src;
        }

        if (is_array($imageMeta)) {
            if (! empty($imageMeta['url'])) {
                return [
                    'url' => AcfFieldService::webpWhenAvailable(AcfFieldService::migrateUrl((string) $imageMeta['url'])),
                    'width' => 0,
                    'height' => 0,
                ];
            }

            $attachmentId = (int) ($imageMeta['ID'] ?? $imageMeta['id'] ?? 0);
            if ($attachmentId && isset($attachmentUrls[$attachmentId])) {
                return ['url' => $attachmentUrls[$attachmentId], 'width' => 0, 'height' => 0];
            }
        }

        if (is_numeric($imageMeta)) {
            $attachmentId = (int) $imageMeta;
            if ($attachmentId && isset($attachmentUrls[$attachmentId])) {
                return ['url' => $attachmentUrls[$attachmentId], 'width' => 0, 'height' => 0];
            }
        }

        if (is_string($imageMeta) && $imageMeta !== '') {
            if (str_starts_with($imageMeta, 'http') || str_starts_with($imageMeta, '/')) {
                return [
                    'url' => AcfFieldService::webpWhenAvailable(AcfFieldService::migrateUrl($imageMeta)),
                    'width' => 0,
                    'height' => 0,
                ];
            }
        }

        return [
            'url' => AcfFieldService::imageUrl($imageMeta),
            'width' => 0,
            'height' => 0,
        ];
    }
}
