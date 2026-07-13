@extends('layouts.auth')

@section('title', '2FA Verification')

@section('content')
<div class="w-full max-w-sm">
        <div class="os-card os-card-pad">
            <div class="flex flex-col items-center text-center">
                <span class="os-icon-tile os-icon-tile-ink">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <h2 class="mt-5 text-2xl font-semibold tracking-tight">Two-factor authentication</h2>
                <p class="mt-1.5 text-sm text-[var(--color-muted)]">
                    Enter the 6-digit code from your authenticator app, or use a backup code.
                </p>
            </div>

            <form class="mt-7 space-y-5" method="POST" action="{{ route('auth.2fa.verify') }}">
                @csrf

                @if ($errors->any())
                    <div class="os-alert os-alert-danger">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (session('message'))
                    <div class="os-alert">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif

                <div>
                    <label for="code" class="os-label">Verification code</label>
                    <input id="code" name="code" type="text" required maxlength="8" autofocus
                           class="os-input text-center tracking-[0.5em] font-mono"
                           placeholder="000000" autocomplete="off" inputmode="numeric">
                </div>

                <button type="submit" class="os-btn os-btn-primary os-btn-block os-btn-lg">
                    <i class="fa-solid fa-lock"></i> Verify
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-[var(--color-muted)] hover:text-[var(--color-accent)]">
                        Back to login
                    </a>
                </div>
            </form>
        </div>
</div>

@endsection
