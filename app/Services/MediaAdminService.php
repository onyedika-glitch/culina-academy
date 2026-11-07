<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaAdminService
{
    /** @return \Illuminate\Contracts\Pagination\LengthAwarePaginator */
    public static function paginate(int $perPage = 30, ?string $search = null)
    {
        $query = Post::query()
            ->where('post_type', 'attachment')
            ->where('post_status', 'inherit')
            ->orderByDesc('ID');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('post_title', 'ilike', "%{$search}%")
                    ->orWhere('post_name', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public static function urlForAttachment(int $attachmentId): string
    {
        return AcfFieldService::attachmentUrl($attachmentId);
    }

    /** @return array{id:int,title:string,url:string,mime:string,date:?string} */
    public static function formatAttachment(Post $attachment): array
    {
        return [
            'id' => $attachment->ID,
            'title' => $attachment->post_title ?: $attachment->post_name,
            'url' => static::urlForAttachment($attachment->ID),
            'mime' => (string) $attachment->post_mime_type,
            'date' => $attachment->post_date?->format('Y-m-d'),
        ];
    }

    public static function upload(UploadedFile $file): Post
    {
        $now = now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $dir = "uploads/{$year}/{$month}";
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $ext = $file->getClientOriginalExtension() ?: $file->extension();
        $storedName = $filename . '-' . uniqid() . '.' . $ext;

        $file->move(public_path($dir), $storedName);
        $relativePath = "{$year}/{$month}/{$storedName}";
        $publicUrl = '/' . $dir . '/' . $storedName;

        $id = DB::table('wp_posts')->insertGetId([
            'post_author' => auth('admin')->id() ?: 1,
            'post_date' => $now,
            'post_date_gmt' => $now->utc(),
            'post_content' => '',
            'post_title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'post_excerpt' => '',
            'post_status' => 'inherit',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $now,
            'post_modified_gmt' => $now->utc(),
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => url($publicUrl),
            'menu_order' => 0,
            'post_type' => 'attachment',
            'post_mime_type' => $file->getMimeType(),
            'comment_count' => 0,
        ]);

        PostMetaWriter::set($id, '_wp_attached_file', $relativePath);
        PostMetaWriter::set($id, '_wp_attachment_metadata', serialize([
            'width' => 0,
            'height' => 0,
            'file' => $relativePath,
        ]));

        return Post::find($id);
    }

    public static function destroy(int $id): void
    {
        $attachment = Post::where('post_type', 'attachment')->findOrFail($id);
        $file = DB::table('wp_postmeta')
            ->where('post_id', $id)
            ->where('meta_key', '_wp_attached_file')
            ->value('meta_value');

        if ($file) {
            $path = public_path('uploads/' . ltrim($file, '/'));
            if (is_file($path)) {
                @unlink($path);
            }
        }

        DB::table('wp_postmeta')->where('post_id', $id)->delete();
        $attachment->delete();
    }
}