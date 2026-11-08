<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMeta;

class CourseDisplayService
{
    /** @var array<string, string> */
    protected static array $badgeToEnglish = [
        'Muy solicitado' => 'Highly sought',
        'Cours court' => 'Short course',
        'Profesional' => 'Professional',
        'Élite' => 'Elite',
        'Spécialiste' => 'Specialist',
        'Nouveau cours' => 'New course',
        'Cocina halal' => 'Halal cuisine',
        'Cocina kosher' => 'Kosher cuisine',
        'Curso corto' => 'Short course',
        'Corso breve' => 'Short course',
        'Especialista' => 'Specialist',
        'Specialista' => 'Specialist',
        'Nuevo curso' => 'New course',
        'Nuovo corso' => 'New course',
        '新课程' => 'New course',
        '新課程' => 'New course',
        '专业方向' => 'Specialist',
        '專業方向' => 'Specialist',
        '短期课程' => 'Short course',
        '短期課程' => 'Short course',
    ];

    /** @var array<string, string> */
    protected static array $durationToEnglish = [
        '1 mes' => '1 month',
        '1 mois' => '1 month',
        '1 mese' => '1 month',
        '3 meses' => '3 months',
        '3 mois' => '3 months',
        '3 mesi' => '3 months',
        '4 meses' => '4 months',
        '4 mois' => '4 months',
        '4 mesi' => '4 months',
        '6 meses' => '6 months',
        '6 mois' => '6 months',
        '6 mesi' => '6 months',
        '12 meses' => '12 months',
        '12 mois' => '12 months',
        '12 mesi' => '12 months',
        '2 semanas' => '2 weeks',
        '2 semaines' => '2 weeks',
        '2 settimane' => '2 weeks',
        '2 semanas - de lunes a viernes, de 9:00 a 17:00' => '2 weeks - Monday to Friday, 9:00 to 17:00',
        '2 semaines - du lundi au vendredi, de 9h00 à 17h00' => '2 weeks - Monday to Friday, 9:00 to 17:00',
        '2 settimane - dal lunedì al venerdì, dalle 9:00 alle 17:00' => '2 weeks - Monday to Friday, 9:00 to 17:00',
    ];

    /** @var array<string, string> */
    protected static array $locationToEnglish = [
        'Your City, Reino Unido' => 'Your City',
        'Your City, Royaume-Uni' => 'Your City',
        'Your City, Regno Unito' => 'Your City',
        'Your City，英国' => 'Your City',
        'Your City，英國' => 'Your City',
    ];

    public static function badge(string $value, ?string $lang = null): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());
        if ($lang !== 'en') {
            return $value;
        }

        return static::$badgeToEnglish[$value] ?? $value;
    }

    public static function duration(string $value, ?string $lang = null): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());
        if ($lang !== 'en') {
            return $value;
        }

        return static::$durationToEnglish[$value] ?? $value;
    }

    public static function location(string $value, ?string $lang = null): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());
        if ($lang !== 'en') {
            return $value;
        }

        return static::$locationToEnglish[$value] ?? $value;
    }

    public static function isMetadataOnlyDescription(string $html): bool
    {
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
        if ($plain === '') {
            return false;
        }

        $hasFacts = str_contains($plain, 'Location:')
            && str_contains($plain, 'Duration:')
            && (str_contains($plain, 'Fees:') || str_contains($plain, 'Fee:'));

        if ($hasFacts && strlen($plain) < 350) {
            return true;
        }

        return str_contains($plain, 'See course details')
            && strlen($plain) < 200;
    }

    public static function plainTextLength(string $html): int
    {
        return strlen(trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? ''));
    }

    public static function resolveCourseDescription(Post $course): string
    {
        $description = trim((string) AcfFieldService::getField($course->ID, 'ca_course_description'));
        $content = trim((string) ($course->post_content ?? ''));

        if ($description !== '' && ! static::isMetadataOnlyDescription($description)) {
            return $description;
        }

        if ($content !== '' && static::plainTextLength($content) > static::plainTextLength($description)) {
            return $content;
        }

        return $description !== '' ? $description : $content;
    }

    /** @return array{updated:int,skipped:int,failed:int,results:array<int,array<string,mixed>>} */
    public static function fixCourseDescriptions(): array
    {
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $results = [];

        $courses = Post::query()
            ->where('post_type', 'ca_course')
            ->published()
            ->get();

        foreach ($courses as $course) {
            $lang = PolylangService::getPostLanguage($course->ID);
            $current = trim((string) AcfFieldService::getField($course->ID, 'ca_course_description'));
            $content = trim((string) ($course->post_content ?? ''));

            if ($current !== '' && ! static::isMetadataOnlyDescription($current) && static::plainTextLength($current) >= 200) {
                $skipped++;

                continue;
            }

            $replacement = '';
            $source = null;

            if ($content !== '' && static::plainTextLength($content) >= 120) {
                $replacement = $content;
                $source = 'post_content';
            } elseif ($lang === 'en') {
                $legacyPath = array_search($course->ID, LegacySiteSyncService::LEGACY_COURSE_PATHS, true);
                if (is_string($legacyPath)) {
                    $html = LegacySiteSyncService::fetchLegacyCourseHtml($legacyPath);
                    if ($html !== null) {
                        $parsed = LegacySiteSyncService::parseLegacyCourseDescription($html);
                        if (static::plainTextLength($parsed) >= 120) {
                            $replacement = $parsed;
                            $source = 'legacy';
                        }
                    }
                }
            }

            if ($replacement === '') {
                $results[] = [
                    'course_id' => $course->ID,
                    'slug' => $course->post_name,
                    'lang' => $lang,
                    'updated' => false,
                    'error' => 'No replacement content found',
                ];
                $failed++;

                continue;
            }

            if ($current === $replacement) {
                $skipped++;

                continue;
            }

            static::setCourseDescription($course->ID, $replacement);
            $results[] = [
                'course_id' => $course->ID,
                'slug' => $course->post_name,
                'lang' => $lang,
                'updated' => true,
                'source' => $source,
                'error' => null,
            ];
            $updated++;
        }

        SiteCacheService::clearResponseCache();

        return compact('updated', 'skipped', 'failed', 'results');
    }

    public static function setCourseDescription(int $courseId, string $content): void
    {
        $content = JoomlaContentSyncService::normalizeContentUrls($content);

        $meta = PostMeta::query()
            ->where('post_id', $courseId)
            ->where('meta_key', 'ca_course_description')
            ->first();

        if ($meta) {
            $meta->meta_value = $content;
            $meta->save();
        } else {
            PostMeta::query()->create([
                'post_id' => $courseId,
                'meta_key' => 'ca_course_description',
                'meta_value' => $content,
            ]);
        }

        AcfFieldService::clearCache($courseId);
    }

    /** @return array{updated:int, skipped:int} */
    public static function fixEnglishCourseMeta(): array
    {
        $courses = Post::query()
            ->where('post_type', 'ca_course')
            ->published()
            ->forLanguage('en')
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($courses as $course) {
            $fields = [
                'ca_badge' => static::badge((string) AcfFieldService::getField($course->ID, 'ca_badge'), 'en'),
                'ca_location' => static::location((string) AcfFieldService::getField($course->ID, 'ca_location'), 'en'),
                'ca_duration' => static::duration((string) AcfFieldService::getField($course->ID, 'ca_duration'), 'en'),
            ];

            $changed = false;

            foreach ($fields as $key => $value) {
                if ($value === '') {
                    continue;
                }

                $current = (string) AcfFieldService::getField($course->ID, $key);
                if ($current === $value) {
                    continue;
                }

                $meta = PostMeta::query()
                    ->where('post_id', $course->ID)
                    ->where('meta_key', $key)
                    ->first();

                if ($meta) {
                    $meta->meta_value = $value;
                    $meta->save();
                } else {
                    PostMeta::query()->create([
                        'post_id' => $course->ID,
                        'meta_key' => $key,
                        'meta_value' => $value,
                    ]);
                }

                $changed = true;
            }

            if ($changed) {
                $updated++;
            } else {
                $skipped++;
            }
        }

        return ['updated' => $updated, 'skipped' => $skipped];
    }
}
