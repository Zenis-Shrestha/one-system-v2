<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IpWhitelistMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $clientIp = $this->getClientIp($request);

        if (!$this->isIpWhitelisted($clientIp)) {
            DB::table('audit_logs')->insert([
                'user_id' => null,
                'client_system_id' => null,
                'event_type' => 'security_violation',
                'action' => 'ip_not_whitelisted',
                'description' => "Unauthorized IP access attempt: {$clientIp}",
                'details' => json_encode([
                    'ip_address' => $clientIp,
                    'user_agent' => $request->userAgent(),
                    'endpoint' => $request->path(),
                    'method' => $request->method()
                ]),
                'success' => false,
                'ip_address' => $clientIp,
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'error' => 'Access denied',
                'message' => 'Your IP address is not authorized to access this service',
                'ip' => $clientIp
            ], 403);
        }

        return $next($request);
    }

    /**
     * Get the real client IP address
     */
    private function getClientIp(Request $request): string
    {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if (!empty($ip) && $ip !== 'unknown') {
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

    /**
     * Check if IP is whitelisted
     */
    private function isIpWhitelisted(string $ip): bool
    {
        try {
            $whitelistEntries = DB::table('ip_whitelist')
                ->where('status', 'active')
                ->get();

            foreach ($whitelistEntries as $entry) {
                if ($this->matchesIpRule($ip, $entry->ip_address, $entry->subnet_mask)) {
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error('IP Whitelist Check Failed', [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Check if IP matches whitelist rule (supports CIDR notation)
     */
    private function matchesIpRule(string $ip, string $whitelistIp, ?string $subnetMask): bool
    {
        if ($ip === $whitelistIp) {
            return true;
        }

        if ($subnetMask) {
            $cidr = $whitelistIp . '/' . $subnetMask;
            return $this->ipInCidr($ip, $cidr);
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);

        if (!filter_var($ip, FILTER_VALIDATE_IP) || !filter_var($subnet, FILTER_VALIDATE_IP)) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int)$mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
