<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class WpUser extends Authenticatable
{
    protected const REMEMBER_TOKEN_META = 'laravel_remember_token';

    protected $table = 'wp_users';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'user_login', 'user_pass', 'user_nicename', 'user_email',
        'user_url', 'user_registered', 'user_activation_key', 'user_status', 'display_name',
    ];

    protected $hidden = ['user_pass', 'user_activation_key'];

    public function getAuthPasswordName(): string
    {
        return 'user_pass';
    }

    public function meta(): HasMany
    {
        return $this->hasMany(WpUserMeta::class, 'user_id', 'ID');
    }

    public function getRememberToken(): ?string
    {
        $token = DB::table('wp_usermeta')
            ->where('user_id', $this->ID)
            ->where('meta_key', static::REMEMBER_TOKEN_META)
            ->value('meta_value');

        return $token ? (string) $token : null;
    }

    public function setRememberToken($value): void
    {
        $exists = DB::table('wp_usermeta')
            ->where('user_id', $this->ID)
            ->where('meta_key', static::REMEMBER_TOKEN_META)
            ->exists();

        if ($exists) {
            DB::table('wp_usermeta')
                ->where('user_id', $this->ID)
                ->where('meta_key', static::REMEMBER_TOKEN_META)
                ->update(['meta_value' => (string) $value]);

            return;
        }

        static::syncUsermetaSequence();

        DB::table('wp_usermeta')->insert([
            'user_id' => $this->ID,
            'meta_key' => static::REMEMBER_TOKEN_META,
            'meta_value' => (string) $value,
        ]);
    }

    protected static function syncUsermetaSequence(): void
    {
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('wp_usermeta', 'umeta_id'),
                COALESCE((SELECT MAX(umeta_id) FROM wp_usermeta), 1)
            )
        ");
    }

    public function getRememberTokenName(): string
    {
        return static::REMEMBER_TOKEN_META;
    }

    public function save(array $options = []): bool
    {
        unset($this->attributes[static::REMEMBER_TOKEN_META], $this->attributes['remember_token']);

        if (!$this->isDirty()) {
            return true;
        }

        return parent::save($options);
    }

    public function isAdministrator(): bool
    {
        $raw = DB::table('wp_usermeta')
            ->where('user_id', $this->ID)
            ->whereIn('meta_key', ['wp_capabilities', 'SERVMASK_PREFIX_capabilities'])
            ->value('meta_value');

        if (!$raw) {
            return false;
        }

        $caps = @unserialize($raw, ['allowed_classes' => false]);

        return is_array($caps) && !empty($caps['administrator']);
    }
}