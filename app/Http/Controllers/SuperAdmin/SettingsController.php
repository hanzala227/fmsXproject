<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuperAdminAuditLog;
use App\Models\Currency;

class SettingsController extends Controller
{
    /**
     * Display Super Admin Global Platform & Freight Security Settings.
     * Scoped strictly to the authenticated Super Admin.
     */
    public function index()
    {
        $user = auth()->user();

        $settings = [
            'platform_name'          => $user?->getSetting('platform_name', config('app.name') !== 'Laravel' ? config('app.name') : 'FreitX Freight SaaS v2.0'),
            'support_email'          => $user?->getSetting('support_email', 'support@freitx.com'),
            'base_currency'          => $user?->getSetting('base_currency', 'USD'),
            'default_timezone'       => $user?->getSetting('default_timezone', 'Asia/Karachi'),
            'session_timeout_mins'   => (int) $user?->getSetting('session_timeout_mins', 60),
            'auto_fx_rates'          => true,
            'daily_backups'          => true,
            'backup_retention_days'  => 30,
            'enforce_carrier_sandbox'=> true,
        ];

        $currencies = Currency::orderBy('code')->get();

        return view('super-admin.settings.index', compact('settings', 'currencies'));
    }

    /**
     * Save personalized Super Admin platform & currency configurations.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'platform_name'        => 'required|string|max:100',
            'support_email'        => 'required|email',
            'base_currency'        => 'required|string|exists:currencies,code',
            'default_timezone'     => 'required|string',
            'session_timeout_mins' => 'required|integer|min:15|max:480',
        ]);

        $user = auth()->user();
        if ($user) {
            foreach ($validated as $key => $value) {
                $user->setSetting($key, $value);
            }

            if (class_exists(SuperAdminAuditLog::class)) {
                SuperAdminAuditLog::create([
                    'user_id'     => $user->id,
                    'action'      => 'update_personal_settings',
                    'target_type' => 'UserSetting',
                    'target_id'   => $user->id,
                    'details'     => $validated,
                    'ip_address'  => $request->ip(),
                ]);
            }
        }

        $currency = Currency::where('code', $validated['base_currency'])->first();
        $currDisplay = $currency ? "{$currency->code} ({$currency->symbol})" : $validated['base_currency'];

        return back()->with('success', "Personalized Super Admin preferences saved. Dashboard metrics and financial figures are now calculated in {$currDisplay}.");
    }
}
