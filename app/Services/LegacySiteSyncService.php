<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Support\Facades\Http;

class LegacySiteSyncService
{
    public const LEGACY_BASE_URL = 'https://www.example.com';

    /** @var array<string, int> Legacy Joomla cooking-course path => English course post ID */
    public const LEGACY_COURSE_PATHS = [
        'hospitality-management-and-advanced-food-science-600-hours' => 32,
        'advanced-professional-chef-course-550-hours' => 33,
        'michelin-star-chef-course-300-hours' => 34,
        'professional-chef-course-325-hours' => 35,
        'kosher-professional-chef-course-400-hours' => 36,
        'halal-advanced-professional-chef-course-550-hours' => 37,
        'professional-chef-course-italian-cuisine-325-hours' => 38,
        'professional-patisserie-and-confectionery-course-840-hours' => 39,
        'introduction-to-the-professional-chef-course-80-hours' => 40,
        'advanced-cooking-techniques-80-hours' => 41,
        'intensive-professional-pastry-programme-80-hours' => 42,
    ];

    public static function fetchLegacyCourseHtml(string $legacyPath): ?string
    {
        $url = static::LEGACY_BASE_URL.'/en/cooking-courses/'.trim($legacyPath, '/').'.html';

        $response = Http::timeout(30)->get($url);
        if (! $response->successful()) {
            return null;
        }

        return $response->body();
    }

    public static function parseLegacyCourseDescription(string $html): string
    {
        return JoomlaContentSyncService::parseLegacyPublicHtml($html);
    }

    /** @return array{ranges:array<int,array{start:string,end:string,status:string}>,hours:?string,duration:?string,price:?string,location:?string}|null */
    public static function parseLegacyCourseHtml(string $html): ?array
    {
        $snippet = $html;
        if (preg_match('/Start:.*?Fees:\s*(?:£|&pound;)?\s*[0-9,]+/su', $html, $block)) {
            $snippet = $block[0];
        } elseif (preg_match('/Start:.*?Duration:/su', $html, $block)) {
            $snippet = $block[0];
        }

        $text = html_entity_decode(strip_tags($snippet), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        if (! preg_match_all('/Start:\s*(\d{2}\s*\/\s*\d{2}\s*\/\s*\d{4})\s*-\s*End:\s*(\d{2}\s*\/\s*\d{2}\s*\/\s*\d{4})/u', $text, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $ranges = [];
        foreach ($matches as $match) {
            $start = static::normalizeLegacyDate($match[1]);
            $end = static::normalizeLegacyDate($match[2]);
            if ($start === '' || $end === '') {
                continue;
            }

            $ranges[] = [
                'start' => $start,
                'end' => $end,
                'status' => 'available',
            ];
        }

        if ($ranges === []) {
            return null;
        }

        $hours = null;
        $duration = null;
        if (preg_match('/Duration:\s*([0-9]+)\s*hours(?:\s*\/\s*([^£]+?))?(?:\s*Fees:|\s*Location:)/u', $text, $durationMatch)) {
            $hours = trim($durationMatch[1]);
            $duration = isset($durationMatch[2]) ? trim($durationMatch[2]) : null;
        }

        $price = null;
        if (preg_match('/Fees:\s*(?:£|&pound;)?\s*([0-9,]+)/u', $text, $feeMatch)) {
            $price = '£'.str_replace(',', '', $feeMatch[1]);
        }

        $location = null;
        if (preg_match('/Location:\s*([^D]+?)(?:\s*Duration:)/u', $text, $locationMatch)) {
            $location = trim($locationMatch[1]);
        }

        return [
            'ranges' => $ranges,
            'hours' => $hours,
            'duration' => $duration,
            'price' => $price,
            'location' => $location,
        ];
    }

    public static function normalizeLegacyDate(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';
        if (! preg_match('/^(\d{2})\s*\/\s*(\d{2})\s*\/\s*(\d{4})$/', $value, $match)) {
            return '';
        }

        return sprintf('%04d-%02d-%02d', (int) $match[3], (int) $match[2], (int) $match[1]);
    }

    /** @return array<int, int> */
    public static function translationIdsForCourse(int $englishCourseId): array
    {
        $ids = [$englishCourseId];

        foreach (PolylangService::getLanguages() as $language) {
            $code = $language['slug'];
            if ($code === 'en') {
                continue;
            }

            $translatedId = PolylangService::getTranslationId($englishCourseId, $code);
            if ($translatedId) {
                $ids[] = $translatedId;
            }
        }

        return array_values(array_unique($ids));
    }

    public static function setCourseMeta(int $courseId, string $key, string $value): void
    {
        if ($value === '') {
            return;
        }

        $meta = PostMeta::query()
            ->where('post_id', $courseId)
            ->where('meta_key', $key)
            ->first();

        if ($meta) {
            $meta->meta_value = $value;
            $meta->save();
        } else {
            PostMeta::query()->create([
                'post_id' => $courseId,
                'meta_key' => $key,
                'meta_value' => $value,
            ]);
        }

        AcfFieldService::clearCache($courseId);
    }

    /** @return array{path:string,course_id:int,updated:bool,skipped:bool,error:?string,ranges:int} */
    public static function syncLegacyCoursePath(string $legacyPath, int $englishCourseId): array
    {
        $html = static::fetchLegacyCourseHtml($legacyPath);
        if ($html === null) {
            return [
                'path' => $legacyPath,
                'course_id' => $englishCourseId,
                'updated' => false,
                'skipped' => false,
                'error' => 'Failed to fetch legacy page',
                'ranges' => 0,
            ];
        }

        $parsed = static::parseLegacyCourseHtml($html);
        if ($parsed === null) {
            return [
                'path' => $legacyPath,
                'course_id' => $englishCourseId,
                'updated' => false,
                'skipped' => false,
                'error' => 'No intake dates found on legacy page',
                'ranges' => 0,
            ];
        }

        $changed = false;
        $courseIds = static::translationIdsForCourse($englishCourseId);

        foreach ($courseIds as $courseId) {
            $lang = PolylangService::getPostLanguage($courseId);
            $currentRanges = CourseIntakeService::displayRanges($courseId);

            if ($currentRanges !== $parsed['ranges']) {
                CourseIntakeService::setDisplayRanges($courseId, $parsed['ranges']);
                $changed = true;
            }

            if ($parsed['hours'] !== null) {
                static::setCourseMeta($courseId, 'ca_hours', $parsed['hours']);
                $changed = true;
            }

            if ($parsed['price'] !== null) {
                static::setCourseMeta($courseId, 'ca_price', $parsed['price']);
                $changed = true;
            }

            if ($parsed['duration'] !== null) {
                $duration = $lang === 'en'
                    ? CourseDisplayService::duration($parsed['duration'], 'en') ?: $parsed['duration']
                    : $parsed['duration'];
                static::setCourseMeta($courseId, 'ca_duration', $duration);
                $changed = true;
            }

            if ($parsed['location'] !== null) {
                $location = $lang === 'en'
                    ? CourseDisplayService::location($parsed['location'], 'en') ?: $parsed['location']
                    : $parsed['location'];
                static::setCourseMeta($courseId, 'ca_location', $location);
                $changed = true;
            }
        }

        return [
            'path' => $legacyPath,
            'course_id' => $englishCourseId,
            'updated' => $changed,
            'skipped' => ! $changed,
            'error' => null,
            'ranges' => count($parsed['ranges']),
        ];
    }

    /** @return array{updated:int,skipped:int,failed:int,results:array<int,array<string,mixed>>} */
    public static function syncAllLegacyCourses(): array
    {
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $results = [];

        foreach (static::LEGACY_COURSE_PATHS as $legacyPath => $englishCourseId) {
            $result = static::syncLegacyCoursePath($legacyPath, $englishCourseId);
            $results[] = $result;

            if (! empty($result['error'])) {
                $failed++;
            } elseif ($result['updated']) {
                $updated++;
            } else {
                $skipped++;
            }
        }

        return compact('updated', 'skipped', 'failed', 'results');
    }

    /** @return array{steps:array<int,array<string,mixed>>} */
    public static function runFullSync(bool $includeJoomlaExtract = false, bool $dryRunJoomlaImport = false): array
    {
        $steps = [];

        $courseResult = static::syncAllLegacyCourses();
        $steps[] = [
            'name' => 'legacy_courses',
            'status' => $courseResult['failed'] > 0 ? 'partial' : 'ok',
            'details' => $courseResult,
        ];

        $metaResult = CourseDisplayService::fixEnglishCourseMeta();
        $steps[] = [
            'name' => 'english_course_meta',
            'status' => 'ok',
            'details' => $metaResult,
        ];

        if ($includeJoomlaExtract) {
            $steps[] = [
                'name' => 'joomla_extract',
                'status' => 'manual',
                'details' => ['message' => 'Run php artisan app:extract-joomla separately (requires Joomla admin access).'],
            ];
        }

        if ($dryRunJoomlaImport) {
            $steps[] = [
                'name' => 'joomla_import_dry_run',
                'status' => 'manual',
                'details' => ['message' => 'Run php artisan app:import-joomla --dry-run after extract completes.'],
            ];
        }

        SiteCacheService::clearResponseCache();

        return ['steps' => $steps];
    }
}
