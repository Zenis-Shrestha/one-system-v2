@if($showLinkModal)
<div class="fixed inset-0 z-50 overflow-y-auto" wire:key="edit-modal-{{ $selectedSystemId }}">
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-[var(--color-ink)]/40 transition-opacity" wire:click="closeLinkModal"></div>

        <div class="inline-block w-full transform overflow-hidden rounded-[var(--radius-lg)] border border-[var(--color-line)] bg-[var(--color-surface)] text-left align-bottom transition-all sm:my-8 sm:max-w-lg sm:align-middle" style="box-shadow: var(--shadow-lg);">
            <div class="flex items-start justify-between gap-4 border-b border-[var(--color-line)] px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="os-icon-tile"><x-icon name="key" class="w-5 h-5" /></span>
                    <div>
                        <h3 class="text-base font-semibold text-[var(--color-ink)]">
                            {{ $selectedSystemName ? 'Update credentials' : 'Link system' }}
                        </h3>
                        <p class="mt-0.5 text-sm text-[var(--color-muted)]">
                            {{ $selectedSystemName }}
                        </p>
                    </div>
                </div>
                <button wire:click="closeLinkModal" class="text-[var(--color-faint)] transition-colors hover:text-[var(--color-ink-2)]">
                    <x-icon name="times" class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="saveCredentials">
                <div class="space-y-6 px-6 py-6">
                    <div>
                        <label for="modalUsername" class="os-label">Username or email</label>
                        <input type="text"
                               id="modalUsername"
                               wire:model.defer="modalUsername"
                               class="os-input"
                               placeholder="Enter your username or email for this system"
                               autocomplete="username"
                               required>
                        <p class="mt-1 text-xs text-[var(--color-faint)]">Use whichever identity you use to sign in to this system.</p>
                        @error('modalUsername')
                            <span class="mt-1 flex items-center gap-1 text-sm text-[var(--color-danger)]">
                                <x-icon name="exclamation" class="w-4 h-4" />{{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div x-data="{ showPassword: false }">
                        <label for="modalPassword" class="os-label">Password <span class="font-normal text-[var(--color-faint)]">— optional for SSO apps</span></label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'"
                                   id="modalPassword"
                                   wire:model.defer="modalPassword"
                                   class="os-input pr-11"
                                   placeholder="Leave blank for single sign-on">
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--color-faint)] hover:text-[var(--color-ink-2)]">
                                <x-icon name="eye" class="w-4 h-4" x-show="!showPassword" />
                                <x-icon name="eye-off" class="w-4 h-4" x-show="showPassword" style="display: none;" />
                            </button>
                        </div>
                        @error('modalPassword')
                            <span class="mt-1 flex items-center gap-1 text-sm text-[var(--color-danger)]">
                                <x-icon name="exclamation" class="w-4 h-4" />{{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="rounded-[var(--radius-md)] border border-[var(--color-accent-line)] bg-[var(--color-accent-soft)] p-4">
                        <div class="flex items-start gap-3">
                            <x-icon name="shield" class="w-5 h-5 text-[var(--color-accent)] mt-0.5" />
                            <div>
                                <h4 class="mb-1 text-sm font-semibold text-[var(--color-ink)]">How linking works</h4>
                                <p class="text-sm text-[var(--color-ink-2)]">
                                    For single sign-on apps, leave the password blank — your CAS identity is used automatically. For apps with their own accounts, enter those credentials; they're encrypted and validated before storage.
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($message)
                        <div class="os-alert {{ $messageType === 'error' ? 'os-alert-danger' : 'os-alert-success' }}">
                            <x-icon name="{{ $messageType === 'error' ? 'exclamation' : 'shield' }}" class="w-5 h-5" />
                            <span>{{ $message }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-2 border-t border-[var(--color-line)] bg-[var(--color-surface-2)] px-6 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:space-y-0">
                    <button type="button"
                            wire:click="closeLinkModal"
                            class="os-btn os-btn-secondary w-full sm:w-auto">
                        <x-icon name="times" class="w-4 h-4" />Cancel
                    </button>

                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveCredentials"
                            class="os-btn os-btn-primary w-full disabled:opacity-50 sm:w-auto">

                        <span wire:loading.remove wire:target="saveCredentials" class="flex items-center gap-2">
                            <x-icon name="save" class="w-4 h-4" />
                            {{ $selectedSystemName && collect($this->clientSystems)->firstWhere('id', $selectedSystemId)['is_linked'] ? 'Update Credentials' : 'Link System' }}
                        </span>

                        <span wire:loading wire:target="saveCredentials" class="flex items-center gap-2 whitespace-nowrap">
                            <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-b-white"></span>
                            <span>Processing...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endif
