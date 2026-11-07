<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostAdminService
{
    public static function pageTemplates(): array
    {
        return [
            '' => 'Default',
            'template-home.php' => 'Home',
            'template-about.php' => 'About',
            'template-blog.php' => 'Blog',
            'template-careers.php' => 'Careers',
            'template-contact-us.php' => 'Contact Us',
            'template-cooking-courses.php' => 'Cooking Courses',
            'template-course-enrollment.php' => 'Course Enrollment',
            'template-faq.php' => 'FAQ',
            'template-how-to-apply.php' => 'How to Apply',
            'template-international-students.php' => 'International Students',
            'template-online-courses.php' => 'Online Courses',
            'template-our-school.php' => 'Our School',
            'template-restaurant-partners.php' => 'Restaurant Partners',
            'template-reviews.php' => 'Reviews',
            'template-privacy-policy.php' => 'Privacy Policy',
            'template-terms-conditions.php' => 'Terms & Conditions',
            'template-cookies-policy.php' => 'Cookies Policy',
            'template-complaints-policy.php' => 'Complaints Policy',
            'template-attendance-policy.php' => 'Attendance Policy',
        ];
    }

    public static function statuses(): array
    {
        return ['publish', 'draft', 'pending', 'private'];
    }

    /** @return array<string, mixed> */
    public static function getEditableAcfFields(int $postId): array
    {
        $meta = DB::table('wp_postmeta')
            ->where('post_id', $postId)
            ->where(function ($q) {
                $q->where('meta_key', 'like', 'cal_%')
                    ->orWhere('meta_key', 'like', 'ca_%');
            })
            ->orderBy('meta_key')
            ->get();

        $repeaters = [];
        $scalars = [];

        foreach ($meta as $row) {
            $key = $row->meta_key;

            if (preg_match('/^(.+)_(\d+)_(.+)$/', $key, $m)) {
                $repeaters[$m[1]][(int) $m[2]][$m[3]] = AcfFieldService::parseValuePublic($row->meta_value);
                continue;
            }

            if (preg_match('/_\d+_/', $key)) {
                continue;
            }

            $value = AcfFieldService::parseValuePublic($row->meta_value);

            if (is_numeric($value) && static::hasRepeaterRows($postId, $key)) {
                $repeaters[$key] = AcfFieldService::getRepeater($postId, $key);
            } else {
                $scalars[$key] = $value;
            }
        }

        foreach ($repeaters as $name => $rows) {
            if (isset($scalars[$name])) {
                unset($scalars[$name]);
            }
            if (!array_is_list($rows)) {
                ksort($rows);
                $repeaters[$name] = array_values($rows);
            }
        }

        return ['scalars' => $scalars, 'repeaters' => $repeaters];
    }

    /** @return array<string, mixed> */
    public static function getEditableOptionFields(string $prefix = 'cal_'): array
    {
        $options = DB::table('wp_options')
            ->where('option_name', 'like', 'options_' . $prefix . '%')
            ->orderBy('option_name')
            ->get();

        $repeaters = [];
        $scalars = [];

        foreach ($options as $row) {
            $key = str_replace('options_', '', $row->option_name);

            if (preg_match('/^(.+)_(\d+)_(.+)$/', $key, $m)) {
                $repeaters[$m[1]][(int) $m[2]][$m[3]] = AcfFieldService::parseValuePublic($row->option_value);
                continue;
            }

            if (preg_match('/_\d+_/', $key)) {
                continue;
            }

            $value = AcfFieldService::parseValuePublic($row->option_value);

            if (is_numeric($value) && OptionWriter::hasRepeaterRows($key)) {
                $repeaters[$key] = AcfFieldService::getOptionRepeater($key);
            } else {
                $scalars[$key] = $value;
            }
        }

        foreach ($repeaters as $name => $rows) {
            if (isset($scalars[$name])) {
                unset($scalars[$name]);
            }
            if (!array_is_list($rows)) {
                ksort($rows);
                $repeaters[$name] = array_values($rows);
            }
        }

        return ['scalars' => $scalars, 'repeaters' => $repeaters];
    }

    public static function create(array $data): Post
    {
        $now = now();
        $slug = $data['post_name'] ?: Str::slug($data['post_title']);

        $id = DB::table('wp_posts')->insertGetId([
            'post_author' => auth('admin')->id() ?: 1,
            'post_date' => $now,
            'post_date_gmt' => $now->utc(),
            'post_content' => $data['post_content'] ?? '',
            'post_title' => $data['post_title'],
            'post_excerpt' => $data['post_excerpt'] ?? '',
            'post_status' => $data['post_status'] ?? 'draft',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => $slug,
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $now,
            'post_modified_gmt' => $now->utc(),
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => '',
            'menu_order' => (int) ($data['menu_order'] ?? 0),
            'post_type' => $data['post_type'],
            'post_mime_type' => '',
            'comment_count' => 0,
        ]);

        DB::table('wp_posts')->where('ID', $id)->update([
            'guid' => url('/?p=' . $id),
        ]);

        $post = Post::with('meta')->find($id);

        static::applyMeta($post, $data);

        if (!empty($data['language'])) {
            PolylangAdminService::setPostLanguage($id, $data['language']);
        }

        return $post;
    }

    public static function update(Post $post, array $data): Post
    {
        $now = now();
        $slug = $data['post_name'] ?? $post->post_name;

        DB::table('wp_posts')->where('ID', $post->ID)->update([
            'post_title' => $data['post_title'] ?? $post->post_title,
            'post_name' => $slug,
            'post_content' => $data['post_content'] ?? $post->post_content,
            'post_excerpt' => $data['post_excerpt'] ?? $post->post_excerpt,
            'post_status' => $data['post_status'] ?? $post->post_status,
            'menu_order' => (int) ($data['menu_order'] ?? $post->menu_order),
            'post_modified' => $now,
            'post_modified_gmt' => $now->utc(),
        ]);

        static::applyMeta($post, $data);

        if (!empty($data['language'])) {
            PolylangAdminService::setPostLanguage($post->ID, $data['language']);
        }

        if (!empty($data['translations']) && is_array($data['translations'])) {
            $group = [];
            $group[PolylangService::getPostLanguage($post->ID)] = $post->ID;
            foreach ($data['translations'] as $lang => $id) {
                if ((int) $id > 0) {
                    $group[PolylangService::normalizeCode($lang)] = (int) $id;
                }
            }
            PolylangAdminService::setTranslationGroup($group);
        }

        return Post::with('meta')->find($post->ID);
    }

    protected static function applyMeta(Post $post, array $data): void
    {
        if (isset($data['page_template'])) {
            PostMetaWriter::set($post->ID, '_wp_page_template', $data['page_template']);
        }

        if (!empty($data['acf_scalars']) && is_array($data['acf_scalars'])) {
            foreach ($data['acf_scalars'] as $key => $value) {
                if (is_string($key) && (str_starts_with($key, 'cal_') || str_starts_with($key, 'ca_'))) {
                    PostMetaWriter::set($post->ID, $key, $value);
                }
            }
        }

        if (!empty($data['acf_repeaters']) && is_array($data['acf_repeaters'])) {
            foreach ($data['acf_repeaters'] as $key => $rows) {
                if (is_string($key) && (str_starts_with($key, 'cal_') || str_starts_with($key, 'ca_'))) {
                    PostMetaWriter::setRepeater($post->ID, $key, is_array($rows) ? $rows : []);
                }
            }
        }
    }

    protected static function hasRepeaterRows(int $postId, string $fieldName): bool
    {
        return DB::table('wp_postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', 'like', $fieldName . '_0_%')
            ->exists();
    }
}