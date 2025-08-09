<div class="relative">
    <div wire:loading.delay wire:target="mount" class="absolute inset-0 bg-white bg-opacity-75 z-40 flex items-center justify-center rounded-lg">
        <x-loading-overlay>Loading dashboard statistics...</x-loading-overlay>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ $stats['users']['total'] }}</dd>
                        <dd class="text-xs text-gray-500">{{ $stats['users']['admin'] }} admin, {{ $stats['users']['regular'] }} regular</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Client Systems</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ $stats['client_systems']['total'] }}</dd>
                        <dd class="text-xs text-gray-500">{{ $stats['client_systems']['active'] }} active, {{ $stats['client_systems']['inactive'] }} inactive</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 8A8 8 0 11-2 8a8 8 0 0116 0zm-7-3a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Logins (24h)</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ $stats['authentication']['total_logins_24h'] }}</dd>
                        <dd class="text-xs text-gray-500">{{ $stats['authentication']['success_rate'] }}% success rate</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">SSO Tokens (24h)</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ $stats['sso']['tokens_24h'] }}</dd>
                        <dd class="text-xs text-gray-500">Authentication tokens issued</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Authentication Activity</h3>
                <select wire:model.live="selectedPeriod" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                    <option value="7days">Last 7 days</option>
                    <option value="30days">Last 30 days</option>
                </select>
            </div>

            <div class="space-y-3">
                @foreach($activityData as $day)
                    <div class="flex items-center space-x-3">
                        <div class="w-16 text-xs text-gray-500">{{ $day['label'] }}</div>
                        <div class="flex-1 flex space-x-1">
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                    <span>Logins: {{ $day['logins'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $activityData ? min(100, ($day['logins'] / max(1, collect($activityData)->max('logins'))) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                    <span>SSO: {{ $day['sso_tokens'] }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $activityData ? min(100, ($day['sso_tokens'] / max(1, collect($activityData)->max('sso_tokens'))) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center space-x-4 text-xs">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-600 rounded mr-2"></div>
                    <span class="text-gray-600">Login Attempts</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-600 rounded mr-2"></div>
                    <span class="text-gray-600">SSO Tokens</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Login Success Rate (24h)</h3>

            <div class="flex items-center justify-center">
                @if($stats['authentication']['total_logins_24h'] > 0)
                    <div class="relative w-40 h-40">
                        <svg class="w-40 h-40 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-gray-200" stroke="currentColor" stroke-width="3" fill="none"
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-green-500" stroke="currentColor" stroke-width="3" fill="none"
                                  stroke-dasharray="{{ $stats['authentication']['success_rate'] }}, 100"
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ $stats['authentication']['success_rate'] }}%</div>
                                <div class="text-xs text-gray-500">Success</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-500">
                        <div class="w-40 h-40 flex items-center justify-center bg-gray-100 rounded-full">
                            <div>
                                <div class="text-lg font-medium">No Data</div>
                                <div class="text-sm">No logins in 24h</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        Successful
                    </span>
                    <span class="font-medium">{{ $stats['authentication']['successful_logins_24h'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        Failed
                    </span>
                    <span class="font-medium">{{ $stats['authentication']['failed_logins_24h'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
        </div>
        <div class="overflow-hidden">
            @if($recentActivity->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($recentActivity as $activity)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($activity['success'])
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L10 10.586l2.293-2.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ ucfirst(str_replace('_', ' ', $activity['event_type'])) }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $activity['username'] ?? 'System' }} • {{ $activity['ip_address'] }}
                                            @if($activity['client_system_name'] && $activity['client_system_name'] !== 'N/A')
                                                • {{ $activity['client_system_name'] }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $activity['created_at'] ? \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() : 'Unknown' }}
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-lg font-medium">No recent activity</p>
                        <p class="text-sm">Activity will appear here as users interact with the system</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/admin/users" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">Manage Users</div>
                    <div class="text-sm text-gray-500">Add, edit, or remove users</div>
                </div>
            </div>
        </a>

        <a href="/admin/client-systems" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">Client Systems</div>
                    <div class="text-sm text-gray-500">Configure authentication clients</div>
                </div>
            </div>
        </a>

        <a href="/admin/audit-logs" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">View Audit Logs</div>
                    <div class="text-sm text-gray-500">Monitor system activity</div>
                </div>
            </div>
        </a>
    </div>
</div>