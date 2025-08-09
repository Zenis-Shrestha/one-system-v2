<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardComponent extends Component
{
    public $selectedPeriod = '7days';

    public function render()
    {
        $stats = $this->getSystemStats();

        $activityData = $this->getActivityData();

        $recentActivity = $this->getRecentActivity();

        return view('livewire.admin.dashboard-component', [
            'stats' => $stats,
            'activityData' => $activityData,
            'recentActivity' => $recentActivity
        ]);
    }

    private function getSystemStats()
    {
        try {
            // User statistics
            $totalUsers = DB::table('users')->count();
            $adminUsers = DB::table('users')->where('role', 'admin')->count();
            $regularUsers = DB::table('users')->where('role', 'user')->count();

            // Client system statistics
            $totalClientSystems = DB::table('client_systems')->count();
            $activeClientSystems = DB::table('client_systems')->where('is_active', true)->count();

            // Authentication statistics (last 24 hours)
            $totalLogins24h = DB::table('audit_logs')
                ->where('event_type', 'login')
                ->where('created_at', '>=', now()->subDay())
                ->count();

            $successfulLogins24h = DB::table('audit_logs')
                ->where('event_type', 'login')
                ->where('success', true)
                ->where('created_at', '>=', now()->subDay())
                ->count();

            $failedLogins24h = $totalLogins24h - $successfulLogins24h;

            // SSO token statistics (last 24 hours)
            $ssoTokens24h = DB::table('audit_logs')
                ->where('event_type', 'sso_token_generated')
                ->where('created_at', '>=', now()->subDay())
                ->count();

            // IP whitelist entries
            $ipWhitelistEntries = DB::table('ip_whitelist')->count();

            return [
                'users' => [
                    'total' => $totalUsers,
                    'admin' => $adminUsers,
                    'regular' => $regularUsers,
                ],
                'client_systems' => [
                    'total' => $totalClientSystems,
                    'active' => $activeClientSystems,
                    'inactive' => $totalClientSystems - $activeClientSystems,
                ],
                'authentication' => [
                    'total_logins_24h' => $totalLogins24h,
                    'successful_logins_24h' => $successfulLogins24h,
                    'failed_logins_24h' => $failedLogins24h,
                    'success_rate' => $totalLogins24h > 0 ? round(($successfulLogins24h / $totalLogins24h) * 100, 1) : 0,
                ],
                'sso' => [
                    'tokens_24h' => $ssoTokens24h,
                ],
                'security' => [
                    'ip_whitelist_entries' => $ipWhitelistEntries,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'users' => ['total' => 0, 'admin' => 0, 'regular' => 0],
                'client_systems' => ['total' => 0, 'active' => 0, 'inactive' => 0],
                'authentication' => ['total_logins_24h' => 0, 'successful_logins_24h' => 0, 'failed_logins_24h' => 0, 'success_rate' => 0],
                'sso' => ['tokens_24h' => 0],
                'security' => ['ip_whitelist_entries' => 0]
            ];
        }
    }

    private function getActivityData()
    {
        try {
            $days = $this->selectedPeriod === '7days' ? 7 : ($this->selectedPeriod === '30days' ? 30 : 1);

            $activityData = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateStr = $date->format('Y-m-d');

                $logins = DB::table('audit_logs')
                    ->where('event_type', 'login')
                    ->whereDate('created_at', $date)
                    ->count();

                $ssoTokens = DB::table('audit_logs')
                    ->where('event_type', 'sso_token_generated')
                    ->whereDate('created_at', $date)
                    ->count();

                $activityData[] = [
                    'date' => $dateStr,
                    'label' => $date->format('M d'),
                    'logins' => $logins,
                    'sso_tokens' => $ssoTokens,
                ];
            }

            return $activityData;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getRecentActivity()
    {
        try {
            return DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->leftJoin('client_systems', 'audit_logs.client_system_id', '=', 'client_systems.id')
                ->select([
                    'audit_logs.*',
                    'users.username',
                    'client_systems.name as client_system_name'
                ])
                ->orderBy('audit_logs.created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public function updatedSelectedPeriod()
    {
        $this->render();
    }
}
