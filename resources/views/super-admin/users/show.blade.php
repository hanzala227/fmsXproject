@extends('super-admin.layout')

@section('content')
<div style="padding: 20px 24px;" x-data="{ tab: 'profile', showResetModal: false }">

    {{-- Breadcrumb & Top Action Header --}}
    <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('super-admin.users.index') }}" 
               style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; color: #475569; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.15s ease;"
               onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';"
               onmouseout="this.style.background='#fff'; this.style.borderColor='#e2e8f0';">
                <i class="fa fa-arrow-left" style="font-size: 13px;"></i>
            </a>
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a; letter-spacing: -0.02em;">{{ $user->name }}</h1>
                    <span style="font-size: 11px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        UID #{{ $user->id }}
                    </span>
                    @if($user->status === 'Enable')
                        <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; background: #ecfdf5; color: #059669; padding: 3px 10px; border-radius: 9999px; border: 1px solid #a7f3d0;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.25);"></span>
                            Active Account
                        </span>
                    @else
                        <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; background: #fef2f2; color: #b91c1c; padding: 3px 10px; border-radius: 9999px; border: 1px solid #fecaca;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                            Inactive / Suspended
                        </span>
                    @endif
                    @if($user->company)
                        <a href="{{ route('super-admin.companies.show', $user->company) }}" 
                           style="display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: #4338ca; background: #eef2ff; padding: 3px 10px; border-radius: 9999px; border: 1px solid #c7d2fe; text-decoration: none;">
                            <i class="fa fa-building-o"></i> {{ $user->company->name }}
                        </a>
                    @endif
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                    Enterprise User Profile & RBAC Security Controls
                </div>
            </div>
        </div>

        {{-- Quick Action Buttons --}}
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            {{-- Reset Password Trigger --}}
            <button type="button" @click="showResetModal = true"
                    style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: all 0.15s ease;"
                    onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#94a3b8';"
                    onmouseout="this.style.background='#fff'; this.style.borderColor='#cbd5e1';">
                <i class="fa fa-key" style="color: #64748b;"></i> Reset Password
            </button>

            {{-- Toggle Status Form --}}
            <form method="POST" action="{{ route('super-admin.users.toggle-status', $user) }}" style="display: inline;">
                @csrf
                @method('PATCH')
                <button type="submit" 
                        onclick="return confirm('Change status for {{ $user->name }} to {{ $user->status === 'Enable' ? 'Inactive' : 'Active' }}?');"
                        style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; 
                        {{ $user->status === 'Enable' ? 'color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca;' : 'color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0;' }} border-radius: 6px; cursor: pointer; transition: all 0.15s ease;">
                    <i class="fa {{ $user->status === 'Enable' ? 'fa-ban' : 'fa-check' }}"></i> 
                    {{ $user->status === 'Enable' ? 'Suspend Account' : 'Activate Account' }}
                </button>
            </form>

            {{-- Edit Profile --}}
            <a href="{{ route('super-admin.users.edit', $user) }}" 
               style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; text-decoration: none; transition: all 0.15s ease;"
               onmouseover="this.style.background='#dbeafe';"
               onmouseout="this.style.background='#eff6ff';">
                <i class="fa fa-pencil"></i> Edit Profile
            </a>

            {{-- Login As User (Impersonation) --}}
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('super-admin.users.login-as', $user) }}" style="display: inline;">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Sign in as {{ $user->name }}? You will access their tenant environment.');"
                        style="height: 34px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #fff; background: #7c3aed; border: none; border-radius: 6px; cursor: pointer; box-shadow: 0 1px 3px rgba(124,58,237,0.3); transition: all 0.15s ease;"
                        onmouseover="this.style.background='#6d28d9';"
                        onmouseout="this.style.background='#7c3aed';">
                    <i class="fa fa-sign-in"></i> Login as User
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div style="margin-bottom: 20px; padding: 12px 18px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; color: #065f46; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-check-circle" style="font-size: 16px; color: #10b981;"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    {{-- 2-Column Responsive Layout --}}
    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 24px; align-items: start;">
        
        {{-- ======================================================== --}}
        {{-- LEFT COLUMN: USER DOSSIER & PROFILE OVERVIEW            --}}
        {{-- ======================================================== --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            {{-- User Identity Card (Clean White, No Banner) --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px 20px 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                
                {{-- Avatar Wrapper --}}
                <div style="margin-bottom: 14px; display: inline-block; position: relative;">
                    @if($user->profile_photo_path)
                        <img src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}" 
                             style="width: 88px; height: 88px; border-radius: 50%; object-fit: cover; border: 3px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: block; background: #fff;">
                    @else
                        <div style="width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 34px; font-weight: 800; border: 3px solid #e0e7ff; box-shadow: 0 2px 8px rgba(37,99,235,0.08); letter-spacing: -0.02em;">
                            {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
                        </div>
                    @endif

                    {{-- Active Status Dot Overlay on Avatar --}}
                    <span style="position: absolute; bottom: 2px; right: 2px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid #ffffff; 
                        {{ $user->status === 'Enable' ? 'background: #10b981; box-shadow: 0 0 0 1px rgba(16,185,129,0.3);' : 'background: #ef4444;' }}"
                        title="{{ $user->status === 'Enable' ? 'Account Active' : 'Account Inactive' }}">
                    </span>
                </div>

                <h2 style="margin: 0 0 4px; font-size: 18px; font-weight: 700; color: #0f172a;">{{ $user->name }}</h2>
                <a href="mailto:{{ $user->email }}" style="font-size: 12px; color: #64748b; text-decoration: none; word-break: break-all;" onmouseover="this.style.color='#0ab39c'" onmouseout="this.style.color='#64748b'">
                    {{ $user->email }}
                </a>

                    {{-- Role Badge --}}
                    <div style="margin: 14px 0 16px;">
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px;
                            {{ $user->role === 'SuperAdmin' ? 'background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;' : ($user->role === 'Admin' ? 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' : 'background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;') }}">
                            <i class="fa {{ $user->role === 'SuperAdmin' ? 'fa-shield' : ($user->role === 'Admin' ? 'fa-user-secret' : 'fa-user') }}"></i>
                            {{ $user->role ?? 'Standard Operator' }}
                        </span>
                    </div>

                    {{-- Fast Contact Buttons --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding-top: 14px; border-top: 1px solid #f1f5f9;">
                        <a href="mailto:{{ $user->email }}" 
                           style="height: 32px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px; font-weight: 600; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; transition: background 0.15s ease;"
                           onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                            <i class="fa fa-envelope-o"></i> Send Email
                        </a>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $user->email }}'); alert('Email copied to clipboard!');" 
                                style="height: 32px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px; font-weight: 600; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; transition: background 0.15s ease;"
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                            <i class="fa fa-copy"></i> Copy Email
                        </button>
                    </div>
                </div>
            </div>

            {{-- Workplace & Organizational Meta Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 12px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa fa-building" style="color: #405189; margin-right: 6px;"></i> Organization & Branch
                    </span>
                    @if($user->company)
                    <span style="font-size: 10px; font-weight: 700; color: #047857; background: #ecfdf5; padding: 2px 6px; border-radius: 4px;">TENANT</span>
                    @else
                    <span style="font-size: 10px; font-weight: 700; color: #7c3aed; background: #f3e8ff; padding: 2px 6px; border-radius: 4px;">INTERNAL</span>
                    @endif
                </div>

                <div style="padding: 16px 18px; display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Organization</div>
                        <div style="font-size: 13px; font-weight: 600; color: #0f172a;">
                            @if($user->company)
                                <a href="{{ route('super-admin.companies.show', $user->company) }}" style="color: #405189; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $user->company->name }}
                                </a>
                            @else
                                <span style="color: #64748b;">FreitX Platform Internal</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Branch / Freight Station</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            <i class="fa fa-map-marker" style="color: #0ab39c;"></i>
                            {{ $user->branch ?: ($user->office_name ?: ($user->company?->branches?->first()?->name ?: 'Not Assigned')) }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Department</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b;">
                            {{ $user->department ?: ($user->department_name ?: ($user->role === 'SuperAdmin' ? 'Platform Administration' : 'General Operations')) }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Office Code</div>
                        <div style="font-size: 12px; font-family: monospace; font-weight: 600; color: #334155; background: #f8fafc; padding: 2px 6px; border-radius: 4px; display: inline-block; border: 1px solid #e2e8f0;">
                            {{ $user->office_code ?: ($user->company?->branches?->first()?->code ?: 'N/A') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security & Lifecycle Snapshot Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; font-size: 12px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa fa-shield" style="color: #405189; margin-right: 6px;"></i> Security & Session Meta
                </div>

                <div style="padding: 16px 18px; display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Two-Factor Auth (2FA)</div>
                        <div style="font-size: 12px; font-weight: 600; color: {{ $user->mfa_enabled ? '#059669' : '#64748b' }}; display: flex; align-items: center; gap: 6px;">
                            <i class="fa {{ $user->mfa_enabled ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            {{ $user->mfa_enabled ? 'Enforced & Protected' : 'Not Configured' }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Registration Date</div>
                        <div style="font-size: 12px; font-weight: 500; color: #1e293b;">
                            {{ $user->created_at ? $user->created_at->format('M d, Y h:i A') : 'N/A' }}
                            <span style="font-size: 11px; color: #94a3b8;">({{ $user->created_at ? $user->created_at->diffForHumans() : '' }})</span>
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Last Login Activity</div>
                        <div style="font-size: 12px; font-weight: 500; color: #1e293b;">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('M d, Y h:i A') }}
                                <div style="font-size: 10px; color: #64748b; margin-top: 1px;">IP: {{ $user->last_login_ip ?? '127.0.0.1 (Local)' }}</div>
                            @else
                                <span style="color: #94a3b8;">No login recorded yet</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Access Expiration</div>
                        <div style="font-size: 12px; font-weight: 600; color: {{ $user->access_expiry && $user->access_expiry < now() ? '#ef4444' : '#059669' }};">
                            {{ $user->access_expiry ? $user->access_expiry->format('F d, Y') : 'Perpetual (No Expiry)' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ======================================================== --}}
        {{-- RIGHT COLUMN: TABBED OPERATIONS & RBAC WORKSPACE        --}}
        {{-- ======================================================== --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; display: flex; flex-direction: column;">
            
            {{-- Professional Enterprise Tabs Header Bar --}}
            <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    
                    {{-- Tab 1: Profile & Permissions --}}
                    <button type="button" @click="tab = 'profile'"
                            :style="tab === 'profile' 
                                ? 'background: #0ab39c; color: #ffffff; border-color: #0ab39c; box-shadow: 0 1px 2px rgba(0,0,0,0.06); font-weight: 600;' 
                                : 'background: #ffffff; color: #334155; border-color: #cbd5e1; font-weight: 500;'"
                            style="height: 36px; padding: 0 16px; border-radius: 6px; font-size: 13px; border-width: 1px; border-style: solid; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.15s ease;">
                        Profile & Permissions
                    </button>

                    {{-- Tab 2: Freight Modules --}}
                    <button type="button" @click="tab = 'modules'"
                            :style="tab === 'modules' 
                                ? 'background: #0ab39c; color: #ffffff; border-color: #0ab39c; box-shadow: 0 1px 2px rgba(0,0,0,0.06); font-weight: 600;' 
                                : 'background: #ffffff; color: #334155; border-color: #cbd5e1; font-weight: 500;'"
                            style="height: 36px; padding: 0 16px; border-radius: 6px; font-size: 13px; border-width: 1px; border-style: solid; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.15s ease;">
                        <span>Freight Modules</span>
                        <span :style="tab === 'modules' ? 'background: rgba(255,255,255,0.25); color: #ffffff;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;'"
                              style="font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 9999px;">
                            {{ count($freightModules) }}
                        </span>
                    </button>

                    {{-- Tab 3: Activity & Audit --}}
                    <button type="button" @click="tab = 'activity'"
                            :style="tab === 'activity' 
                                ? 'background: #0ab39c; color: #ffffff; border-color: #0ab39c; box-shadow: 0 1px 2px rgba(0,0,0,0.06); font-weight: 600;' 
                                : 'background: #ffffff; color: #334155; border-color: #cbd5e1; font-weight: 500;'"
                            style="height: 36px; padding: 0 16px; border-radius: 6px; font-size: 13px; border-width: 1px; border-style: solid; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.15s ease;">
                        <span>Activity & Audit</span>
                        <span :style="tab === 'activity' ? 'background: rgba(255,255,255,0.25); color: #ffffff;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;'"
                              style="font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 9999px;">
                            {{ $activityLogs->count() }}
                        </span>
                    </button>

                    {{-- Tab 4: Sessions & Security --}}
                    <button type="button" @click="tab = 'sessions'"
                            :style="tab === 'sessions' 
                                ? 'background: #0ab39c; color: #ffffff; border-color: #0ab39c; box-shadow: 0 1px 2px rgba(0,0,0,0.06); font-weight: 600;' 
                                : 'background: #ffffff; color: #334155; border-color: #cbd5e1; font-weight: 500;'"
                            style="height: 36px; padding: 0 16px; border-radius: 6px; font-size: 13px; border-width: 1px; border-style: solid; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.15s ease;">
                        <span>Sessions & Security</span>
                        <span :style="tab === 'sessions' ? 'background: rgba(255,255,255,0.25); color: #ffffff;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;'"
                              style="font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 9999px;">
                            {{ $userSessions->count() }}
                        </span>
                    </button>

                </div>

                {{-- Tenant Workspace Context Pill --}}
                <div style="font-size: 11px; color: #64748b; display: inline-flex; align-items: center; gap: 6px; background: #ffffff; padding: 5px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #0ab39c;"></span>
                    <span>Workspace: <strong style="color: #1e293b;">{{ $user->company?->name ?? 'Platform' }}</strong></span>
                </div>
            </div>

            {{-- Tab Contents --}}
            <div style="padding: 24px; flex: 1;">
                
                {{-- TAB 1: PROFILE & ROLES --}}
                <div x-show="tab === 'profile'" x-cloak>
                    {{-- 4 Metric KPI Cards Strip --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                            <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Assigned Role Tier</div>
                            <div style="font-size: 15px; font-weight: 700; color: #1e293b; margin-top: 4px;">{{ $user->role ?? 'Operator' }}</div>
                            <div style="font-size: 10px; color: #059669; margin-top: 2px;">
                                <i class="fa fa-check"></i> {{ $user->role === 'SuperAdmin' ? 'Root Governance' : ($user->role === 'Admin' ? 'Tenant Administrator' : 'Standard Operator') }}
                            </div>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                            <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Direct Permissions</div>
                            <div style="font-size: 15px; font-weight: 700; color: #0284c7; margin-top: 4px;">{{ $directPermissions->count() }} Rules</div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Granular RBAC active</div>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                            <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Active Sessions</div>
                            <div style="font-size: 15px; font-weight: 700; color: #7c3aed; margin-top: 4px;">{{ $userSessions->count() }} Live</div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Browser instances</div>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                            <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Account Governance</div>
                            @if($user->status !== 'Enable')
                                <div style="font-size: 15px; font-weight: 700; color: #ef4444; margin-top: 4px;">Suspended</div>
                                <div style="font-size: 10px; color: #dc2626; margin-top: 2px;">Access Restricted</div>
                            @elseif($user->access_expiry && $user->access_expiry < now())
                                <div style="font-size: 15px; font-weight: 700; color: #f59e0b; margin-top: 4px;">Expired</div>
                                <div style="font-size: 10px; color: #d97706; margin-top: 2px;">Term Elapsed</div>
                            @else
                                <div style="font-size: 15px; font-weight: 700; color: #059669; margin-top: 4px;">Compliant</div>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">{{ $user->company ? 'Multi-tenant isolated' : 'Platform Scope' }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Role Privilege Overview --}}
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3 style="margin: 0; font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa fa-key" style="color: #405189; margin-right: 6px;"></i> Role Authority & Governance Scope
                            </h3>
                            <a href="{{ route('super-admin.role-permissions.index') }}" style="font-size: 11px; font-weight: 600; color: #4338ca; text-decoration: none;">
                                View Permissions Matrix →
                            </a>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.6;">
                            @if($user->role === 'SuperAdmin')
                                <strong>Global Super Administrator:</strong> Unrestricted root authority across all tenant organizations, billing modules, API gateways, and system infrastructure.
                            @elseif($user->role === 'Admin')
                                <strong>Company Administrator:</strong> Full administrative rights across all operational freight branches, accounting journals, B/L creation, and staff user accounts belonging to <strong>{{ $user->company?->name ?? 'this tenant' }}</strong>.
                            @else
                                <strong>Operational User:</strong> Scoped access governed by role permissions for daily freight execution, documentation, and customer communications.
                            @endif
                        </div>
                    </div>

                    {{-- Granular Permissions Grid --}}
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div>
                                <h3 style="margin: 0; font-size: 13px; font-weight: 700; color: #0f172a;">Granular RBAC Permissions Assigned</h3>
                                <p style="margin: 2px 0 0; font-size: 11px; color: #64748b;">Explicit privileges granted to this user</p>
                            </div>
                            <span style="font-size: 11px; font-weight: 700; color: #0ab39c; background: #ecfdf5; padding: 2px 8px; border-radius: 9999px;">
                                {{ $directPermissions->count() }} Authorized
                            </span>
                        </div>

                        @if($directPermissions->count() > 0)
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px;">
                                @foreach($directPermissions as $perm)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fa fa-check-circle" style="color: #0ab39c; font-size: 12px;"></i>
                                        <span style="font-size: 11px; font-weight: 600; color: #1e293b;">{{ $perm->name }}</span>
                                    </div>
                                    <span style="font-size: 9px; font-weight: 700; color: #047857; background: #ecfdf5; padding: 1px 6px; border-radius: 4px;">
                                        ACTIVE
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div style="padding: 24px; text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: #64748b; font-size: 12px;">
                                <i class="fa fa-lock" style="font-size: 20px; color: #94a3b8; margin-bottom: 6px; display: block;"></i>
                                User inherits standard permissions directly from the <strong>{{ $user->role }}</strong> role group.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- TAB 2: FREIGHT MODULES MATRIX --}}
                <div x-show="tab === 'modules'" x-cloak>
                    <div style="margin-bottom: 18px;">
                        <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;">Platform Enterprise Freight Modules</h3>
                        <p style="margin: 2px 0 0; font-size: 11px; color: #64748b;">Subscribed freight capability modules available to {{ $user->name }} under {{ $user->company?->name ?? 'organization' }}</p>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
                        @foreach($freightModules as $mod)
                        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                    <div style="width: 38px; height: 38px; border-radius: 8px; background: {{ $mod['color'] }}15; color: {{ $mod['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                        <i class="fa {{ $mod['icon'] }}"></i>
                                    </div>
                                    <span style="font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 12px; {{ $mod['is_active'] ? 'color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0;' : 'color: #64748b; background: #f1f5f9; border: 1px solid #e2e8f0;' }}">
                                        {{ $mod['is_active'] ? 'ENABLED' : 'LOCKED' }}
                                    </span>
                                </div>
                                <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">{{ $mod['name'] }}</div>
                                <div style="font-size: 11px; color: #64748b; line-height: 1.5;">{{ $mod['desc'] }}</div>
                            </div>
                            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 10px; color: #94a3b8; font-weight: 600;">Category: {{ $mod['category'] }}</span>
                                <span style="font-size: 10px; font-weight: 700; color: {{ $mod['is_active'] ? '#0ab39c' : '#94a3b8' }};">
                                    <i class="fa {{ $mod['is_active'] ? 'fa-check' : 'fa-lock' }}"></i> {{ $mod['is_active'] ? 'Active Access' : 'Restricted' }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- TAB 3: ACTIVITY & AUDIT TRAIL --}}
                <div x-show="tab === 'activity'" x-cloak>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                        <div>
                            <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;">User Security Audit Trail</h3>
                            <p style="margin: 2px 0 0; font-size: 11px; color: #64748b;">Chronological event stream of administrative actions and access logs</p>
                        </div>
                        <a href="{{ route('super-admin.audit-log', ['user_id' => $user->id]) }}" 
                           style="font-size: 11px; font-weight: 600; color: #4338ca; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fa fa-external-link"></i> Full Audit Trail
                        </a>
                    </div>

                    @if($activityLogs->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($activityLogs as $log)
                            <div style="display: flex; align-items: flex-start; gap: 14px; padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa fa-info-circle"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div style="font-size: 12px; font-weight: 700; color: #1e293b;">
                                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                        </div>
                                        <span style="font-size: 11px; color: #94a3b8;">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                        {{ $log->description ?: ('Target: ' . ($log->target_type ? ucfirst($log->target_type) . ' #' . $log->target_id : 'System Resource')) }}
                                        @if($log->ip_address)
                                            • IP: <span style="font-family: monospace;">{{ $log->ip_address }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align: center; padding: 40px 20px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 20px;">
                                <i class="fa fa-history"></i>
                            </div>
                            <div style="font-size: 13px; font-weight: 600; color: #1e293b;">No Explicit Audit Events Logged</div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">User account registered on {{ $user->created_at?->format('F d, Y') }}. Future profile edits, password resets, and role assignments will stream here automatically.</div>
                        </div>
                    @endif
                </div>

                {{-- TAB 4: SESSIONS & SECURITY --}}
                <div x-show="tab === 'sessions'" x-cloak>
                    <div style="margin-bottom: 20px;">
                        <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;">Active Web Sessions & Browser Footprint</h3>
                        <p style="margin: 2px 0 0; font-size: 11px; color: #64748b;">Live authenticated browser sessions across desktop and mobile devices</p>
                    </div>

                    @if($userSessions->count() > 0)
                        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 24px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead>
                                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left;">
                                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Device & Browser</th>
                                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">IP Address</th>
                                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Last Activity</th>
                                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Session State</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userSessions as $sess)
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 12px 16px;">
                                            <div style="font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                                <i class="fa fa-desktop" style="color: #64748b; font-size: 14px;"></i>
                                                <span title="{{ $sess->user_agent }}">
                                                    {{ Str::limit($sess->user_agent, 45) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td style="padding: 12px 16px; font-family: monospace; color: #475569;">
                                            {{ $sess->ip_address }}
                                        </td>
                                        <td style="padding: 12px 16px; color: #64748b;">
                                            {{ \Carbon\Carbon::createFromTimestamp($sess->last_activity)->diffForHumans() }}
                                        </td>
                                        <td style="padding: 12px 16px;">
                                            <span style="font-size: 10px; font-weight: 700; color: #047857; background: #ecfdf5; padding: 2px 8px; border-radius: 9999px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 4px;">
                                                <span style="width: 5px; height: 5px; border-radius: 50%; background: #10b981;"></span>
                                                Active Now
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding: 24px; text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: #64748b; font-size: 12px; margin-bottom: 24px;">
                            <i class="fa fa-globe" style="font-size: 20px; color: #94a3b8; margin-bottom: 6px; display: block;"></i>
                            No active concurrent sessions stored in the sessions database.
                        </div>
                    @endif

                    {{-- Security Reset Box --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;">
                        <h4 style="margin: 0 0 6px; font-size: 13px; font-weight: 700; color: #0f172a;">Password & Credential Governance</h4>
                        <p style="margin: 0 0 16px; font-size: 11px; color: #64748b;">SuperAdmin password override utility for user {{ $user->name }}</p>
                        
                        <form method="POST" action="{{ route('super-admin.users.reset-password', $user) }}" style="display: flex; gap: 12px; max-width: 480px; align-items: flex-start; flex-direction: column;">
                            @csrf
                            <div style="width: 100%;">
                                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">New Temporary Password (min 8 chars):</label>
                                <input type="password" name="new_password" required minlength="8" placeholder="••••••••"
                                       style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;">
                            </div>
                            <div style="width: 100%;">
                                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Confirm Password:</label>
                                <input type="password" name="new_password_confirmation" required minlength="8" placeholder="••••••••"
                                       style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;">
                            </div>
                            <button type="submit" 
                                    style="height: 34px; padding: 0 16px; background: #0ab39c; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                                    onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                                <i class="fa fa-check"></i> Set New Password
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Reset Password Alpine Modal --}}
    <div x-show="showResetModal" 
         :style="showResetModal ? 'display: flex !important;' : 'display: none !important;'"
         style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(2px);">
        <div @click.away="showResetModal = false" style="background: #fff; border-radius: 12px; width: 100%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); overflow: hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">Reset Password for {{ $user->name }}</h3>
                <button @click="showResetModal = false" style="background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer;"><i class="fa fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('super-admin.users.reset-password', $user) }}" style="padding: 24px;">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 6px;">New Password</label>
                    <input type="password" name="new_password" required minlength="8" placeholder="Enter at least 8 characters"
                           style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 24px;">
                    <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 6px;">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required minlength="8" placeholder="Re-enter new password"
                           style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" @click="showResetModal = false" 
                            style="height: 36px; padding: 0 16px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="height: 36px; padding: 0 18px; background: #0ab39c; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection