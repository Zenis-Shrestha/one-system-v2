<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SsoConfigService
{
    /**
     * Get SSO settings with caching
     */
    public static function getSettings()
    {
        return Cache::remember('sso_settings', 3600, function () {
            $settings = DB::table('cas_admin.sso_settings')->first();

            if (!$settings) {
                return (object) [
                    'token_expiry_minutes' => 60,
                    'token_issuer' => 'Innovative-Solution',
                    'token_audience' => 'client-systems',
                    'max_concurrent_tokens' => 5,
                    'enable_token_refresh' => true,
                    'token_refresh_threshold' => 15,
                    'signature_algorithm' => 'HS256',
                    'require_ip_validation' => true,
                    'enable_audit_logging' => true,
                    'max_failed_attempts' => 3,
                    'lockout_duration' => 30
                ];
            }

            return $settings;
        });
    }

    /**
     * Get token expiry in seconds
     */
    public static function getTokenExpirySeconds()
    {
        $settings = self::getSettings();
        return $settings->token_expiry_minutes * 60;
    }

    /**
     * Get JWT payload data
     */
    public static function getJwtPayloadData($userId, $username, $clientSystemId)
    {
        $settings = self::getSettings();
        $now = time();

        return [
            'iss' => $settings->token_issuer,
            'aud' => $settings->token_audience,
            'iat' => $now,
            'exp' => $now + self::getTokenExpirySeconds(),
            'sub' => $userId,
            'username' => $username,
            'client_system_id' => $clientSystemId,
            'scope' => 'sso_access',
            'jti' => uniqid('sso_', true)
        ];
    }

    /**
     * Check if token refresh is enabled and needed
     */
    public static function shouldRefreshToken($tokenExp)
    {
        $settings = self::getSettings();

        if (!$settings->enable_token_refresh) {
            return false;
        }

        $now = time();
        $timeRemaining = $tokenExp - $now;
        $refreshThreshold = $settings->token_refresh_threshold * 60;

        return $timeRemaining <= $refreshThreshold;
    }

    /**
     * Get signature algorithm
     */
    public static function getSignatureAlgorithm()
    {
        $settings = self::getSettings();
        return $settings->signature_algorithm ?? 'HS256';
    }

    /**
     * Check if IP validation is required
     */
    public static function requiresIpValidation()
    {
        $settings = self::getSettings();
        return (bool) $settings->require_ip_validation;
    }

    /**
     * Check if audit logging is enabled
     */
    public static function isAuditLoggingEnabled()
    {
        $settings = self::getSettings();
        return (bool) $settings->enable_audit_logging;
    }

    /**
     * Get security limits
     */
    public static function getSecurityLimits()
    {
        $settings = self::getSettings();

        return [
            'max_concurrent_tokens' => $settings->max_concurrent_tokens,
            'max_failed_attempts' => $settings->max_failed_attempts,
            'lockout_duration' => $settings->lockout_duration
        ];
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        Cache::forget('sso_settings');
    }
}
