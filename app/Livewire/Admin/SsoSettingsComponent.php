<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\SsoSetting;

class SsoSettingsComponent extends Component
{
    public $processing = false;
    public $token_expiry_minutes = 60;
    public $token_issuer = 'Innovative-Solution';
    public $token_audience = 'client-systems';
    public $max_concurrent_tokens = 5;
    public $enable_token_refresh = true;
    public $token_refresh_threshold = 15;
    public $signature_algorithm = 'HS256';
    public $require_ip_validation = true;
    public $enable_audit_logging = true;
    public $max_failed_attempts = 3;
    public $lockout_duration = 30;

    protected $rules = [
        'token_expiry_minutes' => 'required|integer|min:5|max:1440',
        'token_issuer' => 'required|string|max:100',
        'token_audience' => 'required|string|max:100',
        'max_concurrent_tokens' => 'required|integer|min:1|max:20',
        'token_refresh_threshold' => 'required|integer|min:1|max:60',
        'signature_algorithm' => 'required|in:HS256,HS384,HS512,RS256',
        'lockout_duration' => 'required|integer|min:5|max:1440',
        'max_failed_attempts' => 'required|integer|min:1|max:10'
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        try {
            SsoSetting::initializeDefaults();

            $this->token_expiry_minutes = (int) SsoSetting::getValue('token_expiry_minutes', 60);
            $this->token_issuer = (string) SsoSetting::getValue('token_issuer', 'Innovative-Solution');
            $this->token_audience = (string) SsoSetting::getValue('token_audience', 'client-systems');
            $this->max_concurrent_tokens = (int) SsoSetting::getValue('max_concurrent_tokens', 5);
            $this->enable_token_refresh = (bool) SsoSetting::getValue('enable_token_refresh', true);
            $this->token_refresh_threshold = (int) SsoSetting::getValue('token_refresh_threshold', 15);
            $this->signature_algorithm = (string) SsoSetting::getValue('signature_algorithm', 'HS256');
            $this->require_ip_validation = (bool) SsoSetting::getValue('require_ip_validation', true);
            $this->enable_audit_logging = (bool) SsoSetting::getValue('enable_audit_logging', true);
            $this->max_failed_attempts = (int) SsoSetting::getValue('max_failed_attempts', 3);
            $this->lockout_duration = (int) SsoSetting::getValue('lockout_duration', 30);
        } catch (\Exception $e) {
            $this->setDefaultValues();
        }
    }

    private function setDefaultValues()
    {
        $this->token_expiry_minutes = 60;
        $this->token_issuer = 'Innovative-Solution';
        $this->token_audience = 'client-systems';
        $this->max_concurrent_tokens = 5;
        $this->enable_token_refresh = true;
        $this->token_refresh_threshold = 15;
        $this->signature_algorithm = 'HS256';
        $this->require_ip_validation = true;
        $this->enable_audit_logging = true;
        $this->max_failed_attempts = 3;
        $this->lockout_duration = 30;
    }

    public function saveSettings()
    {
        if ($this->processing) {
            return;
        }

        $this->processing = true;

        $this->validate();

        try {
            SsoSetting::setValue('token_expiry_minutes', $this->token_expiry_minutes);
            SsoSetting::setValue('token_issuer', $this->token_issuer);
            SsoSetting::setValue('token_audience', $this->token_audience);
            SsoSetting::setValue('max_concurrent_tokens', $this->max_concurrent_tokens);
            SsoSetting::setValue('enable_token_refresh', $this->enable_token_refresh);
            SsoSetting::setValue('token_refresh_threshold', $this->token_refresh_threshold);
            SsoSetting::setValue('signature_algorithm', $this->signature_algorithm);
            SsoSetting::setValue('require_ip_validation', $this->require_ip_validation);
            SsoSetting::setValue('enable_audit_logging', $this->enable_audit_logging);
            SsoSetting::setValue('max_failed_attempts', $this->max_failed_attempts);
            SsoSetting::setValue('lockout_duration', $this->lockout_duration);

            Cache::forget('sso_settings');

            $this->dispatch('$refresh');
            session()->flash('message', 'SSO settings updated successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save settings: ' . $e->getMessage());
        } finally {
            $this->processing = false;
        }
    }

    public function resetToDefaults()
    {
        $defaults = SsoSetting::getDefaults();

        $this->token_expiry_minutes = $defaults['token_expiry_minutes']['value'];
        $this->token_issuer = $defaults['token_issuer']['value'];
        $this->token_audience = $defaults['token_audience']['value'];
        $this->max_concurrent_tokens = $defaults['max_concurrent_tokens']['value'];
        $this->enable_token_refresh = $defaults['enable_token_refresh']['value'];
        $this->token_refresh_threshold = $defaults['token_refresh_threshold']['value'];
        $this->signature_algorithm = $defaults['signature_algorithm']['value'];
        $this->require_ip_validation = $defaults['require_ip_validation']['value'];
        $this->enable_audit_logging = $defaults['enable_audit_logging']['value'];
        $this->max_failed_attempts = $defaults['max_failed_attempts']['value'];
        $this->lockout_duration = $defaults['lockout_duration']['value'];

        session()->flash('message', 'Settings reset to defaults. Click Save to apply changes.');
    }

    public function render()
    {
        return view('livewire.admin.sso-settings-component');
    }
}
