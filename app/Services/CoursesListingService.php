<?php

namespace App\Services;

use App\Models\Post;

class CoursesListingService
{
    public static function render(): string
    {
        $lang = PolylangService::currentLanguage();

        $courses = Post::with('meta')
            ->where('post_type', 'ca_course')
            ->published()
            ->forLanguage($lang)
            ->orderBy('menu_order')
            ->orderBy('post_title')
            ->get();

        if ($courses->isEmpty()) {
            return '<div class="ca-wrap"><p>' . esc_html(cal_i18n_text('No courses available right now.')) . '</p></div>';
        }

        foreach ($courses as $course) {
            AcfFieldService::hydratePostMeta($course);
        }

        ob_start();
        ?>
        <div class="ca-wrap">
            <main class="ca-grid">
                <?php foreach ($courses as $course):
                    $id = $course->ID;
                    // Always use this language's course permalink (ignore cross-language ca_course_url).
                    $url = PolylangService::buildPostUrl((int) $id, $lang);
                    $location = CourseDisplayService::location((string) AcfFieldService::getField($id, 'ca_location'), $lang);
                    $hours = AcfFieldService::getField($id, 'ca_hours');
                    $duration = CourseDisplayService::duration((string) AcfFieldService::getField($id, 'ca_duration'), $lang);
                    $image = cal_image_url(AcfFieldService::getField($id, 'ca_image_url'));
                    $badge = CourseDisplayService::badge((string) AcfFieldService::getField($id, 'ca_badge'), $lang);
                    $price = CulinaAcademy::formatPrice($id);
                    $durationLine = CulinaAcademy::formatCourseDuration($hours, $duration, $lang);
                ?>
                <article class="ca-card">
                    <?php if ($badge): ?><div class="ca-badge"><?= esc_html($badge) ?></div><?php endif; ?>
                    <div class="ca-ci">
                        <?php if ($image): ?><img src="<?= esc_url($image) ?>" alt="<?= esc_attr($course->post_title) ?>" loading="lazy"><?php endif; ?>
                    </div>
                    <div class="ca-cb">
                        <h2 class="ca-ct"><a href="<?= esc_url($url) ?>"><?= esc_html($course->post_title) ?></a></h2>
                        <div class="ca-cm">
                            <?php if ($location): ?><span><?= esc_html(cal_i18n_text('Location:')) ?> <?= esc_html($location) ?></span><?php endif; ?>
                            <?php if ($durationLine): ?><span><?= esc_html(cal_i18n_text('Duration:')) ?> <?= esc_html($durationLine) ?></span><?php endif; ?>
                        </div>
                        <div class="ca-cf">
                            <div class="ca-price"><?= $price ?></div>
                            <button class="ca-btn ca-modal-trigger" type="button" data-cid="<?= (int) $id ?>"><?= esc_html(cal_i18n_text('Check Dates')) ?></button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </main>
        </div>
        <div id="caOv" class="ca-ov" role="dialog" aria-modal="true">
            <div class="ca-mo">
                <button class="ca-mx" type="button" onclick="caClose()" aria-label="<?= esc_attr(cal_i18n_text('Close')) ?>">&times;</button>
                <div class="ca-ml">
                    <div class="ca-mi"><img id="mImg" src="" alt=""></div>
                    <div class="ca-md">
                        <h2 id="mTitle"></h2>
                        <p id="mMeta" class="ca-mme"></p>
                        <div id="mPrice" class="ca-mpr"></div>
                        <div class="ca-dh"><?= esc_html(cal_i18n_text('Upcoming Intakes')) ?></div>
                        <div id="mDates"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}