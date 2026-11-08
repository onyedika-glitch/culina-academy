<?php

namespace App\Services;

use App\Models\Option;

class RestaurantPartnersService
{
    protected static ?array $awardIcons = null;

    public static function getRestaurants(int $postId): array
    {
        $rows = AcfFieldService::getField($postId, 'cal_rp_restaurants', []);
        if (! is_array($rows)) {
            return [];
        }

        $restaurants = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $address = trim((string) ($row['address'] ?? ''));
            if ($address !== '') {
                $lines = preg_split('/\R/u', $address) ?: [];
                $firstLine = trim((string) ($lines[0] ?? ''));
                if (strcasecmp($firstLine, $name) === 0 || stripos($firstLine, $name.' Restaurant') === 0) {
                    array_shift($lines);
                    $address = trim(implode("\n", $lines));
                }
            }

            $websiteUrl = trim((string) ($row['website_url'] ?? ''));
            $websiteLabel = trim((string) ($row['website_label'] ?? ''));
            if ($websiteLabel === '' && $websiteUrl !== '') {
                $websiteLabel = rtrim(preg_replace('#^https?://#', '', $websiteUrl), '/');
            }

            $restaurants[] = [
                'name' => $name,
                'image' => cal_image_url($row['image'] ?? ''),
                'address' => $address,
                'michelin' => (int) ($row['michelin'] ?? 0),
                'aa' => (int) ($row['aa'] ?? 0),
                'map_url' => AcfFieldService::migrateUrl(trim((string) ($row['map_url'] ?? ''))),
                'website_url' => $websiteUrl,
                'website_label' => $websiteLabel,
            ];
        }

        return $restaurants;
    }

    public static function awardIconUrl(string $type): string
    {
        $icons = static::awardIcons();
        $attachmentId = (int) ($icons[$type] ?? 0);

        return $attachmentId ? AcfFieldService::attachmentUrl($attachmentId) : '';
    }

    protected static function awardIcons(): array
    {
        if (static::$awardIcons !== null) {
            return static::$awardIcons;
        }

        $raw = Option::where('option_name', 'cal_rp_award_icons_local')->value('option_value');
        while (is_string($raw) && (str_starts_with($raw, 'a:') || str_starts_with($raw, 's:'))) {
            $unserialized = @unserialize($raw);
            if ($unserialized === false && $unserialized !== serialize(false)) {
                break;
            }
            $raw = $unserialized;
        }

        static::$awardIcons = is_array($raw) ? $raw : [];

        return static::$awardIcons;
    }
}
