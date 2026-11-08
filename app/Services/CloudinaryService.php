<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CloudinaryService
{
    /**
     * Upload a file to Cloudinary with a GDPR-compliant hashed filename.
     *
     * @param  string  $folder  The Cloudinary folder path
     * @return array{public_id: string, secure_url: string, hashed_name: string, original_name: string, mime_type: string, size: int}|null
     */
    public static function upload(UploadedFile $file, string $folder = 'culina-academy/bookings'): ?array
    {
        try {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $hashedName = Str::random(40).'_'.hash('sha256', $originalName.microtime()).'.'.$extension;

            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('services.cloudinary.cloud_name'),
                    'api_key' => config('services.cloudinary.api_key'),
                    'api_secret' => config('services.cloudinary.api_secret'),
                ],
                'url' => [
                    'secure' => true,
                ],
            ]);

            $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $folder,
                'public_id' => pathinfo($hashedName, PATHINFO_FILENAME),
                'resource_type' => 'raw', // Use raw for PDFs and non-image files
                'overwrite' => false,
                'access_mode' => 'authenticated', // Not publicly accessible
                'type' => 'private', // Stored in private directory
            ]);

            return [
                'public_id' => $result['public_id'],
                'secure_url' => $result['secure_url'],
                'hashed_name' => $hashedName,
                'original_name' => $originalName,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        } catch (\Throwable $e) {
            Log::error('Cloudinary upload failed: '.$e->getMessage(), [
                'file' => $file->getClientOriginalName(),
            ]);

            return null;
        }
    }

    /**
     * Delete a file from Cloudinary by its public ID.
     */
    public static function delete(string $publicId): bool
    {
        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('services.cloudinary.cloud_name'),
                    'api_key' => config('services.cloudinary.api_key'),
                    'api_secret' => config('services.cloudinary.api_secret'),
                ],
            ]);

            $cloudinary->uploadApi()->destroy($publicId, [
                'resource_type' => 'raw',
                'type' => 'private',
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Cloudinary delete failed: '.$e->getMessage(), [
                'public_id' => $publicId,
            ]);

            return false;
        }
    }

    /**
     * Upload a file to Cloudinary as publicly accessible (for site images/videos).
     *
     * @param  string  $folder  The Cloudinary folder path
     * @return array{public_id: string, secure_url: string, original_name: string, mime_type: string, size: int}|null
     */
    public static function uploadPublic(UploadedFile $file, string $folder = 'culina-academy/site'): ?array
    {
        try {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('services.cloudinary.cloud_name'),
                    'api_key' => config('services.cloudinary.api_key'),
                    'api_secret' => config('services.cloudinary.api_secret'),
                ],
                'url' => [
                    'secure' => true,
                ],
            ]);

            // Auto-detect resource type based on mime
            $mime = $file->getMimeType();
            $resourceType = str_starts_with($mime, 'video/') ? 'video' : 'image';

            $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => $resourceType,
                'overwrite' => false,
                'access_mode' => 'public',
            ]);

            return [
                'public_id' => $result['public_id'],
                'secure_url' => $result['secure_url'],
                'original_name' => $originalName,
                'mime_type' => $mime,
                'size' => $file->getSize(),
            ];
        } catch (\Throwable $e) {
            Log::error('Cloudinary public upload failed: '.$e->getMessage(), [
                'file' => $file->getClientOriginalName(),
            ]);

            return null;
        }
    }

    /**
     * Validate that an uploaded file meets our requirements.
     *
     * @return string|null Error message, or null if valid.
     */
    public static function validateFile(UploadedFile $file, int $maxSizeMb = 5): ?string
    {
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
        ];

        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if (! in_array($ext, $allowedExtensions, true)) {
            return "File \"{$file->getClientOriginalName()}\" has an unsupported format. Only PDF, JPG, and PNG files are allowed.";
        }

        if (! in_array($mime, $allowedMimes, true)) {
            return "File \"{$file->getClientOriginalName()}\" has an invalid MIME type ({$mime}). Only PDF, JPG, and PNG are accepted.";
        }

        $maxBytes = $maxSizeMb * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            return "File \"{$file->getClientOriginalName()}\" exceeds the {$maxSizeMb}MB size limit.";
        }

        return null;
    }
}
