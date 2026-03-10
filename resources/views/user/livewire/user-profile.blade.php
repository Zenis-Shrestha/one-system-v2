<div class="min-h-screen bg-gray-50">
    <div class="bg-slate-700 shadow-xl border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white">Profile Settings</h1>
                        <p class="text-slate-200 mt-1">Manage your account, security, and connected systems</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg mb-8 overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6">
                    <button
                        wire:click="setActiveTab('profile')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'profile' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
                    >
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profile Information
                    </button>
                    <button
                        wire:click="setActiveTab('security')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'security' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
                    >
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Security Settings
                    </button>
                    <button
                        wire:click="setActiveTab('systems')"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'systems' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
                    >
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                        </svg>
                        Connected Systems
                        @if(count($linkedSystems) > 0)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                {{ count($linkedSystems) }}
                            </span>
                        @endif
                    </button>
                </nav>
            </div>

            <div class="p-6">
                @if($activeTab === 'profile')
                    <div class="space-y-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Personal Information</h3>
                                <p class="text-sm text-gray-600">Update your basic profile details</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">First Name</label>
                                <input
                                    type="text"
                                    wire:model="first_name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    placeholder="Enter your first name"
                                >
                                @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input
                                    type="text"
                                    wire:model="last_name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    placeholder="Enter your last name"
                                >
                                @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input
                                    type="email"
                                    wire:model="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    placeholder="Enter your email address"
                                >
                                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <div>
                                <p class="text-sm text-gray-600">Last updated: {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Never' }}</p>
                            </div>
                            <button
                                wire:click="updateProfile"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                            >
                                <span wire:loading.remove wire:target="updateProfile">Update Profile</span>
                                <span wire:loading wire:target="updateProfile">Updating...</span>
                            </button>
                        </div>
                    </div>

                @elseif($activeTab === 'security')
                    <div class="space-y-8">
                        <div class="bg-orange-50 rounded-xl p-6 border border-orange-200">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Password Security</h3>
                                        <p class="text-sm text-gray-600">Keep your account secure with a strong password</p>
                                    </div>
                                </div>
                                @if(!$showPasswordForm)
                                    <button
                                        wire:click="$set('showPasswordForm', true)"
                                        class="inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-orange-700 bg-white hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Change Password
                                    </button>
                                @endif
                            </div>

                            @if($showPasswordForm)
                                <div class="space-y-4" wire:key="password-form-{{ $user->id }}">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">Current Password</label>
                                            <input
                                                type="password"
                                                wire:model="currentPassword"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200"
                                                placeholder="Enter current password"
                                            >
                                            @error('currentPassword') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">New Password</label>
                                            <input
                                                type="password"
                                                wire:model="newPassword"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200"
                                                placeholder="Enter new password"
                                            >
                                            @error('newPassword') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                            <input
                                                type="password"
                                                wire:model="newPassword_confirmation"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200"
                                                placeholder="Confirm new password"
                                            >
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-4 border-t border-orange-200">
                                        <div class="text-sm text-gray-600">
                                            <p>Password requirements:</p>
                                            <ul class="list-disc list-inside text-xs text-gray-500 mt-1">
                                                <li>At least 8 characters long</li>
                                                <li>Include uppercase and lowercase letters</li>
                                                <li>Include numbers and special characters</li>
                                            </ul>
                                        </div>
                                        <div class="flex space-x-3">
                                            <button
                                                wire:click="changePassword"
                                                wire:loading.attr="disabled"
                                                wire:key="password-update-btn-{{ now() }}"
                                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                            >
                                                <span wire:loading.remove wire:target="changePassword">Update Password</span>
                                                <span wire:loading wire:target="changePassword">Updating...</span>
                                            </button>
                                            <button
                                                wire:click="$set('showPasswordForm', false)"
                                                class="inline-flex items-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-sm text-gray-600">
                                    <p>Last changed: {{ $user->password_changed_at ? $user->password_changed_at->diffForHumans() : 'Never' }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">Two-Factor Authentication</h3>
                                        <p class="text-sm text-gray-600">Add an extra layer of security to your account</p>
                                    </div>
                                </div>

                                @if($user && $user->two_factor_enabled)
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                            <span class="text-green-700 text-sm font-medium">Enabled</span>
                                        </div>
                                        <button
                                            wire:click="disable2FA"
                                            wire:confirm="Are you sure you want to disable two-factor authentication?"
                                            class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                        >
                                            Disable 2FA
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                                            <span class="text-gray-600 text-sm font-medium">Disabled</span>
                                        </div>
                                        <button
                                            wire:click="setup2FA"
                                            class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Enable 2FA
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if($show2FAForm)
                                <div class="space-y-6 border-t border-green-200 pt-6">
                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                        <h4 class="font-medium text-blue-900 mb-3">Setup Instructions:</h4>
                                        <ol class="text-sm text-blue-800 space-y-2">
                                            <li class="flex items-start space-x-2">
                                                <span class="flex-shrink-0 w-5 h-5 bg-blue-200 rounded-full text-xs font-bold flex items-center justify-center mt-0.5">1</span>
                                                <span>Install Google Authenticator or similar app on your phone</span>
                                            </li>
                                            <li class="flex items-start space-x-2">
                                                <span class="flex-shrink-0 w-5 h-5 bg-blue-200 rounded-full text-xs font-bold flex items-center justify-center mt-0.5">2</span>
                                                <span>Scan the QR code below or enter the secret key manually</span>
                                            </li>
                                            <li class="flex items-start space-x-2">
                                                <span class="flex-shrink-0 w-5 h-5 bg-blue-200 rounded-full text-xs font-bold flex items-center justify-center mt-0.5">3</span>
                                                <span>Enter the 6-digit code from your app to verify</span>
                                            </li>
                                        </ol>
                                    </div>

                                    @if($twoFactorQrCode)
                                        <div class="text-center bg-white p-6 rounded-lg border-2 border-dashed border-gray-300">
                                            <img src="{{ $twoFactorQrCode }}" alt="QR Code" class="mx-auto mb-4">
                                            <p class="text-xs text-gray-500 font-mono bg-gray-100 p-2 rounded">{{ $twoFactorSecret }}</p>
                                        </div>
                                    @endif

                                    <div class="max-w-xs mx-auto">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                                        <input
                                            type="text"
                                            wire:model="twoFactorCode"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-center text-lg font-mono"
                                            placeholder="000000"
                                            maxlength="6"
                                        >
                                    </div>

                                    <div class="flex items-center justify-center space-x-4">
                                        <button
                                            wire:click="enable2FA"
                                            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Enable 2FA
                                        </button>
                                        <button
                                            wire:click="$set('show2FAForm', false)"
                                            class="inline-flex items-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                @elseif($activeTab === 'systems')
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">Dashboard Visibility Settings</h3>
                                    <p class="text-sm text-gray-600">Control which systems appear on your dashboard</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 text-sm text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Show or hide systems regardless of link status</span>
                            </div>
                        </div>

                        @if(count($linkedSystems) > 0)
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                @foreach($linkedSystems as $system)
                                    <div class="bg-white rounded-xl shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300" wire:key="system-{{ $system['client_system_id'] }}">
                                        <div class="p-6">
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex items-start space-x-3">
                                                    <div class="w-12 h-12 bg-slate-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                                        <span class="text-white font-bold text-lg">{{ strtoupper(substr($system['name'], 0, 2)) }}</span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="text-lg font-semibold text-slate-900 truncate">{{ $system['name'] }}</h4>
                                                        @if($system['description'])
                                                            <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ $system['description'] }}</p>
                                                        @endif
                                                        <p class="text-xs text-slate-500 mt-2">{{ $system['callback_url'] }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    @if($system['is_active'])
                                                        <div class="w-3 h-3 bg-emerald-500 rounded-full" title="Active"></div>
                                                    @else
                                                        <div class="w-3 h-3 bg-slate-400 rounded-full" title="Inactive"></div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between text-sm p-3 bg-slate-50 rounded-lg">
                                                    <span class="text-slate-600 font-medium">Dashboard Visibility</span>
                                                    <div class="flex items-center space-x-2">
                                                        @if($system['show_in_dashboard'])
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                Visible
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"></path>
                                                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"></path>
                                                                </svg>
                                                                Hidden
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex justify-center pt-4 mt-4 border-t border-slate-200">
                                                <button
                                                    wire:click="toggleSystemVisibility({{ $system['id'] ?? 'null' }}, {{ $system['client_system_id'] }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="toggleSystemVisibility"
                                                    wire:key="toggle-btn-{{ $loop->index }}-{{ $system['client_system_id'] }}"
                                                    class="inline-flex items-center px-6 py-3 border {{ $system['show_in_dashboard'] ? 'border-slate-300 text-slate-700 hover:bg-slate-50' : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' }} text-sm font-medium rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                                >
                                                    <x-icon name="{{ $system['show_in_dashboard'] ? 'eye-off' : 'eye' }}" class="w-4 h-4 mr-2" />
                                                    <span wire:loading.remove wire:target="toggleSystemVisibility">{{ $system['show_in_dashboard'] ? 'Hide from Dashboard' : 'Show on Dashboard' }}</span>
                                                    <span wire:loading wire:target="toggleSystemVisibility">Processing...</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <x-icon name="desktop" class="w-12 h-12 text-slate-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">No Systems Available</h3>
                                <p class="text-slate-600 mb-6 max-w-md mx-auto">No client systems are currently configured in the database. Contact your administrator to set up systems.</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($message)
        <div class="fixed bottom-4 right-4 z-50" wire:key="message-{{ now() }}">
            <div class="shadow-xl rounded-lg pointer-events-auto overflow-hidden border-l-4 {{ $messageType === 'success' ? 'bg-green-50 border-green-400' : 'bg-red-50 border-red-400' }}" style="min-width: 320px; max-width: 400px;">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($messageType === 'success')
                                <svg class="h-6 w-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-medium {{ $messageType === 'success' ? 'text-green-800' : 'text-red-800' }}">
                                {{ $message }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button wire:click="clearMessage" class="inline-flex {{ $messageType === 'success' ? 'text-green-400 hover:text-green-600' : 'text-red-400 hover:text-red-600' }} focus:outline-none">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            setTimeout(() => {
            @this.call('clearMessage');
            }, 5000);
        </script>
    @endif
</div>
