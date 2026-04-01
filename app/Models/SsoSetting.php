<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SsoSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'cas_admin.sso_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_public',
        'is_editable',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_public' => 'boolean',
        'is_editable' => 'boolean',
    ];

    /**
     * Get the default settings
     */
    public static function getDefaults(): array
    {
        return [
            'token_expiry_minutes' => ['value' => 60, 'type' => 'integer', 'description' => 'Token expiry time in minutes'],
            'token_issuer' => ['value' => 'Innovative-Solution', 'type' => 'string', 'description' => 'JWT token issuer'],
            'token_audience' => ['value' => 'client-systems', 'type' => 'string', 'description' => 'JWT token audience'],
            'max_concurrent_tokens' => ['value' => 5, 'type' => 'integer', 'description' => 'Maximum concurrent tokens per user'],
            'enable_token_refresh' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable token refresh'],
            'token_refresh_threshold' => ['value' => 15, 'type' => 'integer', 'description' => 'Token refresh threshold in minutes'],
            'signature_algorithm' => ['value' => 'HS256', 'type' => 'string', 'description' => 'JWT signature algorithm'],
            'require_ip_validation' => ['value' => true, 'type' => 'boolean', 'description' => 'Require IP validation'],
            'enable_audit_logging' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable audit logging'],
            'max_failed_attempts' => ['value' => 3, 'type' => 'integer', 'description' => 'Maximum failed login attempts'],
            'lockout_duration' => ['value' => 30, 'type' => 'integer', 'description' => 'Account lockout duration in minutes'],
        ];
    }

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();

        if (!$setting) {
            $defaults = self::getDefaults();
            return $defaults[$key]['value'] ?? $default;
        }

        return self::castValue($setting->setting_value, $setting->setting_type);
    }

    /**
     * Set setting value by key
     */
    public static function setValue(string $key, $value): void
    {
        $defaults = self::getDefaults();
        $type = $defaults[$key]['type'] ?? 'string';
        $description = $defaults[$key]['description'] ?? '';

        self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => (string) $value,
                'setting_type' => $type,
                'description' => $description,
                'is_public' => true,
                'is_editable' => true,
            ]
        );
    }

    /**
     * Get all settings as an associative array
     */
    public static function getAllSettings(): array
    {
        $settings = [];
        $defaults = self::getDefaults();

        foreach ($defaults as $key => $default) {
            $settings[$key] = self::getValue($key);
        }

        return $settings;
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            default:
                return $value;
        }
    }

    /**
     * Initialize default settings if they don't exist
     */
    public static function initializeDefaults(): void
    {
        $defaults = self::getDefaults();

        foreach ($defaults as $key => $config) {
            if (!self::where('setting_key', $key)->exists()) {
                self::create([
                    'setting_key' => $key,
                    'setting_value' => (string) $config['value'],
                    'setting_type' => $config['type'],
                    'description' => $config['description'],
                    'is_public' => true,
                    'is_editable' => true,
                ]);
            }
        }
    }

    /**
     * Get the current settings object for backwards compatibility
     */
    public static function current(): object
    {
        self::initializeDefaults();

        $settings = self::getAllSettings();
        return (object) $settings;
    }

    /**
     * Scope for finding the settings record
     */
    public function scopeCurrent($query)
    {
        return $query->orderBy('id')->limit(1);
    }
}
