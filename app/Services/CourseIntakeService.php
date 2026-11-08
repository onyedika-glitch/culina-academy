<?php

namespace App\Services;

use App\Models\IntakeDate;
use App\Models\Post;
use App\Models\PostMeta;

class CourseIntakeService
{
    /** @var array<int, array<int, array{start:string,end:string,status:string}>> */
    protected static array $legacyDateRangesByEnglishCourseId = [
        // example.com/en/cooking-courses/advanced-professional-chef-course-550-hours.html
        33 => [
            ['start' => '2026-05-18', 'end' => '2026-11-06', 'status' => 'available'],
            ['start' => '2026-09-07', 'end' => '2027-03-31', 'status' => 'available'],
            ['start' => '2027-01-25', 'end' => '2027-07-16', 'status' => 'available'],
        ],
    ];

    public static function displayRanges(int $courseId): array
    {
        $ranges = AcfFieldService::getField($courseId, 'ca_display_date_ranges', []);
        if (! is_array($ranges)) {
            $ranges = [];
        }

        // Get DB intakes for this course
        $dbIntakes = IntakeDate::where('course_id', $courseId)
            ->where('intake_date', '>=', now()->startOfDay())
            ->get()->keyBy(function ($item) {
                return $item->intake_date?->format('Y-m-d');
            });

        $processedDates = [];
        foreach ($ranges as &$range) {
            $start = $range['start'] ?? '';
            if ($start) {
                $processedDates[$start] = true;
                if (isset($dbIntakes[$start])) {
                    $dbIntake = $dbIntakes[$start];
                    $range['status'] = $dbIntake->effectiveStatus();
                }
            }
        }
        unset($range);

        // Dynamically add DB intakes that are not in the ACF list
        foreach ($dbIntakes as $dateStr => $dbIntake) {
            if (! isset($processedDates[$dateStr])) {
                $ranges[] = [
                    'start' => $dateStr,
                    'end' => '',
                    'status' => $dbIntake->effectiveStatus(),
                ];
            }
        }

        return array_values(array_filter($ranges, static function ($range) {
            if (! is_array($range)) {
                return false;
            }
            if (empty($range['start']) && empty($range['end'])) {
                return false;
            }
            // Exclude ranges where the start date has already passed
            if (! empty($range['start'])) {
                $startTs = strtotime($range['start']);
                if ($startTs !== false && $startTs < strtotime('today')) {
                    return false;
                }
            }

            return true;
        }));
    }

    public static function renderModalHtml(int $courseId): string
    {
        $ranges = static::displayRanges($courseId);
        if (empty($ranges)) {
            return '<p style="color:#888;font-style:italic;padding:8px 0">No intake dates scheduled. Please contact us directly.</p>';
        }

        $html = '';
        foreach ($ranges as $range) {
            $start = (string) ($range['start'] ?? '');
            $end = (string) ($range['end'] ?? '');
            if ($start === '' && $end === '') {
                continue;
            }

            $status = (string) ($range['status'] ?? 'available');
            if (! in_array($status, ['available', 'few', 'full'], true)) {
                $status = 'available';
            }

            [$chipClass, $chipText] = match ($status) {
                'few' => ['chip-few', UiTranslationService::translate('Limited Spots')],
                'full' => ['chip-full', UiTranslationService::translate('Fully Booked')],
                default => ['chip-ok', UiTranslationService::translate('Open for Enrollment')],
            };

            $label = static::formatRangeLabel($start, $end);
            $enrollUrl = static::enrollmentUrl($courseId, $start, $status);
            $buttonText = $status === 'full' ? UiTranslationService::translate('Join Waiting List') : UiTranslationService::translate('Book Now');
            $courseUrl = AcfFieldService::migrateUrl(
                (string) AcfFieldService::getField($courseId, 'ca_course_url', '') ?: '/courses/'.(Post::find($courseId)?->post_name ?? '')
            );

            $html .= '<div class="ca-dr">';
            $html .= '<div class="ca-dl">'.esc_html($label).'</div>';
            $html .= '<div class="ca-da">';
            $html .= '<span class="ca-chip '.esc_attr($chipClass).'">'.esc_html($chipText).'</span>';
            if ($enrollUrl) {
                $html .= '<a href="'.esc_url($enrollUrl).'" class="ca-btn ca-view-btn">'.esc_html($buttonText).'</a>';
            } else {
                $html .= '<a href="'.esc_url($courseUrl).'" class="ca-btn ca-view-btn">View Course</a>';
            }
            $html .= '</div></div>';
        }

        $html .= '<p style="margin-top:12px;color:#666;font-size:.9rem">'.count($ranges).' upcoming intake(s) available.</p>';

        return $html;
    }

    public static function courseMetaLine(int $courseId): string
    {
        $hours = AcfFieldService::getField($courseId, 'ca_hours');
        $lang = PolylangService::currentLanguage();
        $duration = CourseDisplayService::duration((string) AcfFieldService::getField($courseId, 'ca_duration'), $lang);
        $location = CourseDisplayService::location(
            (string) AcfFieldService::getField($courseId, 'ca_location', 'Your City'),
            $lang,
        );
        $parts = array_filter([
            CulinaAcademy::formatCourseDuration($hours, $duration, $lang),
            $location,
        ]);

        return implode(' | ', $parts);
    }

    protected static function formatRangeLabel(string $start, string $end): string
    {
        if ($start && $end) {
            return static::formatDate($start).' – '.static::formatDate($end);
        }

        return static::formatDate($start ?: $end);
    }

    public static function renderHtml(int $courseId): string
    {
        $ranges = static::displayRanges($courseId);
        if (empty($ranges)) {
            return '<p class="cal-course-no-intakes">'.UiTranslationService::translate('Please contact us for upcoming course dates.').'</p>';
        }

        $html = '<div class="ca-course-intakes">';
        foreach ($ranges as $range) {
            $start = (string) ($range['start'] ?? '');
            $end = (string) ($range['end'] ?? '');
            $status = (string) ($range['status'] ?? 'available');
            if (! in_array($status, ['available', 'few', 'full'], true)) {
                $status = 'available';
            }

            [$chipClass, $chipText] = match ($status) {
                'few' => ['chip-few', UiTranslationService::translate('Limited Spots')],
                'full' => ['chip-full', UiTranslationService::translate('Fully Booked')],
                default => ['chip-ok', UiTranslationService::translate('Open for Enrollment')],
            };

            $startLabel = $start ? static::formatDate($start) : '';
            $endLabel = $end ? static::formatDate($end) : '';
            $enrollUrl = static::enrollmentUrl($courseId, $start, $status);
            $buttonText = $status === 'full' ? UiTranslationService::translate('Join Waiting List') : UiTranslationService::translate('Book Now');

            $html .= '<div class="ca-dr">';
            if ($startLabel && $endLabel) {
                $html .= '<div class="ca-dates">';
                $html .= '<div class="ca-dl ca-dl-start"><strong>'.UiTranslationService::translate('Start:').'</strong> '.esc_html($startLabel).'</div>';
                $html .= '<div class="ca-dl ca-dl-end"><strong>'.UiTranslationService::translate('End:').'</strong> '.esc_html($endLabel).'</div>';
                $html .= '</div>';
            } else {
                $label = trim($startLabel.($startLabel && $endLabel ? ' – ' : '').$endLabel);
                $html .= '<div class="ca-dates ca-dates-single"><div class="ca-dl">'.esc_html($label).'</div></div>';
            }

            $html .= '<div class="ca-da">';
            $html .= '<span class="ca-chip '.esc_attr($chipClass).'">'.esc_html($chipText).'</span>';
            if ($enrollUrl) {
                $html .= '<a href="'.esc_url($enrollUrl).'" class="ca-btn ca-view-btn">'.esc_html($buttonText).'</a>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div>';

        return $html;
    }

    protected static function formatDate(string $date): string
    {
        $ts = strtotime($date);

        return $ts ? date('d / m / Y', $ts) : $date;
    }

    protected static function enrollmentUrl(int $courseId, string $startDate, string $status): string
    {
        $params = ['course_id' => $courseId];
        if ($startDate) {
            $params['intake_date'] = $startDate;
        }
        if ($status === 'full') {
            $params['waitlist'] = 1;
        }

        return cal_localized_path('/course-enrollment').'?'.http_build_query($params);
    }

    /** @param array<int, array{start?:string,end?:string,status?:string}> $ranges */
    public static function setDisplayRanges(int $courseId, array $ranges): bool
    {
        $normalized = [];
        foreach ($ranges as $range) {
            if (! is_array($range)) {
                continue;
            }

            $start = trim((string) ($range['start'] ?? ''));
            $end = trim((string) ($range['end'] ?? ''));
            if ($start === '' && $end === '') {
                continue;
            }

            $status = (string) ($range['status'] ?? 'available');
            if (! in_array($status, ['available', 'few', 'full'], true)) {
                $status = 'available';
            }

            $normalized[] = [
                'start' => $start,
                'end' => $end,
                'status' => $status,
            ];
        }

        $stored = serialize(serialize($normalized));
        $meta = PostMeta::query()
            ->where('post_id', $courseId)
            ->where('meta_key', 'ca_display_date_ranges')
            ->first();

        if ($meta) {
            $meta->meta_value = $stored;
            $meta->save();
        } else {
            PostMeta::query()->create([
                'post_id' => $courseId,
                'meta_key' => 'ca_display_date_ranges',
                'meta_value' => $stored,
            ]);
        }

        AcfFieldService::clearCache($courseId);

        return true;
    }

    /** @return array{updated:int, skipped:int, course_ids:array<int,int>} */
    public static function restoreLegacyAdvancedProfessionalChefDates(): array
    {
        $englishCourseId = 33;
        $ranges = static::$legacyDateRangesByEnglishCourseId[$englishCourseId] ?? [];
        if ($ranges === []) {
            return ['updated' => 0, 'skipped' => 0, 'course_ids' => []];
        }

        $courseIds = [$englishCourseId];
        foreach (PolylangService::getLanguages() as $language) {
            $code = $language['slug'];
            if ($code === 'en') {
                continue;
            }

            $translatedId = PolylangService::getTranslationId($englishCourseId, $code);
            if ($translatedId) {
                $courseIds[] = $translatedId;
            }
        }

        $updated = 0;
        $skipped = 0;

        foreach (array_unique($courseIds) as $courseId) {
            $current = static::displayRanges($courseId);
            if ($current === $ranges) {
                $skipped++;

                continue;
            }

            static::setDisplayRanges($courseId, $ranges);
            $updated++;
        }

        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'course_ids' => array_values(array_unique($courseIds)),
        ];
    }
}
