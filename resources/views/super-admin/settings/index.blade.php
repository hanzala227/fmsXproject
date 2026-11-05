@extends('super-admin.layout')

@section('content')
<div style="padding: 24px; background: #f8fafc; min-height: calc(100vh - 65px);">

    {{-- Top Title Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 38px; height: 38px; border-radius: 8px; background: linear-gradient(135deg, #405189 0%, #2f3e6e 100%); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 6px -1px rgba(64, 81, 137, 0.25);">
                <i class="fa fa-sliders" style="font-size: 18px;"></i>
            </div>
            <div>
                <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                    System Settings & Security Compliance
                </h1>
                <p style="font-size: 13px; color: #64748b; margin: 3px 0 0 0;">
                    Global multi-tenant platform configurations, freight security protocols, currency exchange rules, and disaster recovery.
                </p>
            </div>
        </div>

        <a href="{{ route('super-admin.audit-log') }}" 
           style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
            <i class="fa fa-history"></i> View Audit Log
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class="fa fa-check-circle" style="color: #059669; font-size: 16px;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Two Column Layout --}}
    <form method="POST" action="{{ route('super-admin.settings.update') }}">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start;">
            
            {{-- LEFT COLUMN: Main Configurations --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">

                {{-- 1. General Platform Defaults --}}
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-globe" style="color: #405189; margin-right: 4px;"></i> Global SaaS Platform Profile
                        </span>
                    </div>

                    <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Platform Name</label>
                            <input type="text" name="platform_name" value="{{ $settings['platform_name'] }}" required style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Central Support Email</label>
                            <input type="email" name="support_email" value="{{ $settings['support_email'] }}" required style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Base System Currency (Per-Admin)</label>
                            <select name="base_currency" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; box-sizing: border-box;">
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr->code }}" {{ $settings['base_currency'] === $curr->code ? 'selected' : '' }}>
                                        {{ $curr->code }} - {{ $curr->name }} ({{ $curr->symbol }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Default System Timezone</label>
                            <select name="default_timezone" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; box-sizing: border-box;">
                                <option value="Asia/Karachi" selected>Asia/Karachi (GMT+5)</option>
                                <option value="Asia/Dubai">Asia/Dubai (GMT+4)</option>
                                <option value="UTC">UTC (Universal Coordinated Time)</option>
                                <option value="Europe/London">Europe/London (GMT+0/1)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 2. Freight Security & Compliance --}}
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-shield" style="color: #059669; margin-right: 4px;"></i> Enterprise Freight Security Protocols
                        </span>
                    </div>

                    <div style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
                            <div>
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Session Inactivity Timeout</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Automatically invalidate operator sessions after idle period</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <input type="number" name="session_timeout_mins" value="{{ $settings['session_timeout_mins'] }}" min="15" max="480" style="width: 70px; height: 32px; padding: 0 8px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; text-align: center;">
                                <span style="font-size: 11px; color: #64748b;">Minutes</span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
                            <div>
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Carrier API Sandbox Isolation</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Prevent accidental live calls to Maersk & Hapag-Lloyd while in testing</div>
                            </div>
                            <span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 12px; background: #ecfdf5; color: #047857;">
                                <i class="fa fa-check-circle"></i> Enforced Sandbox
                            </span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Automated Database Backups</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Daily automated snapshots stored in isolated encrypted volume</div>
                            </div>
                            <span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 12px; background: #e0f2fe; color: #0369a1;">
                                <i class="fa fa-clock-o"></i> Daily at 02:00 AM
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" style="height: 38px; padding: 0 24px; background: #405189; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 4px rgba(64,81,137,0.25);">
                        <i class="fa fa-save" style="margin-right: 6px;"></i> Save Platform Settings
                    </button>
                </div>

            </div>

            {{-- RIGHT COLUMN: Status & Health Card --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-heartbeat" style="color: #ef4444; margin-right: 4px;"></i> Infrastructure Status
                        </span>
                    </div>

                    <div style="padding: 18px; font-size: 12px;">
                        <div style="margin-bottom: 12px;">
                            <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Platform Architecture</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">FreitX Cloud Enterprise Node</div>
                        </div>

                        <div style="margin-bottom: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                            <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Database Cluster</div>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">High-Availability Primary Cluster</div>
                        </div>

                        <div style="margin-bottom: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                            <div style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Active Multi-Tenants</div>
                            <div style="font-weight: 600; color: #059669; margin-top: 2px;">7 Isolated Organizations</div>
                        </div>

                        <div style="padding-top: 12px; border-top: 1px solid #f1f5f9;">
                            <a href="{{ route('super-admin.dashboard.system-status') }}" 
                               style="display: flex; align-items: center; justify-content: center; height: 34px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600;">
                                Full Infrastructure Health <i class="fa fa-arrow-right" style="margin-left: 6px; font-size: 10px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

