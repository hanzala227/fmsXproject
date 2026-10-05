@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;" x-data="{ tab: 'profile' }">
    
    {{-- Top Action Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('super-admin.users.index') }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; color: #64748b; text-decoration: none;"><i class="fa fa-arrow-left"></i></a>
            <div>
                <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #1e293b;">{{ $user->name }}</h2>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">User ID: #{{ $user->id }}</div>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('super-admin.users.edit', $user) }}" style="height: 32px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #3b82f6; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; text-decoration: none;">
                <i class="fa fa-pencil"></i> Edit Profile
            </a>
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('super-admin.users.login-as', $user) }}" style="display: inline;">
                @csrf
                <button type="submit" style="height: 32px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #8b5cf6; background: #f3e8ff; border: 1px solid #ddd6fe; border-radius: 4px; cursor: pointer;">
                    <i class="fa fa-sign-in"></i> Login as User
                </button>
            </form>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 16px;">
        
        {{-- Left Sidebar: User Card --}}
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; padding: 20px; text-align: center;">
                <div style="margin-bottom: 16px;">
                    @if($user->profile_photo_path)
                        <img src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
                    @else
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 600; margin: 0 auto; border: 2px solid #cbd5e1;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <h3 style="margin: 0 0 4px; font-size: 15px; color: #1e293b;">{{ $user->name }}</h3>
                <div style="font-size: 11px; color: #3b82f6; font-weight: 600; margin-bottom: 12px;">{{ $user->role }}</div>
                
                <div style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 600;
                    {{ $user->status === 'Enable' ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;' }}">
                    <i class="fa {{ $user->status === 'Enable' ? 'fa-check' : 'fa-ban' }}" style="margin-right: 4px;"></i> {{ $user->status === 'Enable' ? 'Active' : 'Inactive' }}
                </div>
            </div>

            <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 4px;">
                <div style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #1e293b; text-transform: uppercase;">Quick Info</div>
                <div style="padding: 16px; display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <div style="font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Email</div>
                        <div style="font-size: 11px; color: #1e293b;"><a href="mailto:{{ $user->email }}" style="color: #405189; text-decoration: none;">{{ $user->email }}</a></div>
                    </div>
                    <div>
                        <div style="font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Company</div>
                        <div style="font-size: 11px; color: #1e293b;">{{ $user->company?->name ?? 'System / Internal' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Department</div>
                        <div style="font-size: 11px; color: #1e293b;">{{ $user->department ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Created Date</div>
                        <div style="font-size: 11px; color: #1e293b;">{{ $user->created_at?->format('F d, Y') }}</div>
                    </div>
                    <div>
                        <div style="font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Access Expiry</div>
                        <div style="font-size: 11px; color: {{ $user->access_expiry && $user->access_expiry < now() ? '#ef4444' : '#1e293b' }};">
                            {{ $user->access_expiry ? $user->access_expiry->format('F d, Y') : 'Never' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Content Area --}}
        <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; display: flex; flex-direction: column;">
            
            {{-- Tabs --}}
            <div style="display: flex; border-bottom: 1px solid #e2e8f0; padding: 0 16px;">
                <button @click="tab = 'profile'" :style="tab === 'profile' ? 'border-bottom: 2px solid #0ab39c; color: #0ab39c;' : 'border-bottom: 2px solid transparent; color: #64748b;'"
                    style="padding: 14px 16px; font-size: 11px; font-weight: 600; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer;">
                    <i class="fa fa-id-card-o" style="margin-right: 4px;"></i> User Profile
                </button>
                <button @click="tab = 'activity'" :style="tab === 'activity' ? 'border-bottom: 2px solid #0ab39c; color: #0ab39c;' : 'border-bottom: 2px solid transparent; color: #64748b;'"
                    style="padding: 14px 16px; font-size: 11px; font-weight: 600; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer;">
                    <i class="fa fa-history" style="margin-right: 4px;"></i> Activity
                </button>
                <button @click="tab = 'sessions'" :style="tab === 'sessions' ? 'border-bottom: 2px solid #0ab39c; color: #0ab39c;' : 'border-bottom: 2px solid transparent; color: #64748b;'"
                    style="padding: 14px 16px; font-size: 11px; font-weight: 600; background: none; border-top: none; border-left: none; border-right: none; cursor: pointer;">
                    <i class="fa fa-shield" style="margin-right: 4px;"></i> Sessions & Security
                </button>
            </div>

            {{-- Tab Content --}}
            <div style="padding: 20px; flex: 1;">
                
                {{-- Profile Tab --}}
                <div x-show="tab === 'profile'">
                    <h4 style="margin: 0 0 16px; font-size: 12px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Roles & Permissions</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                        <div>
                            <div style="font-size: 10px; color: #64748b; margin-bottom: 8px;">Assigned Role</div>
                            <div style="display: inline-block; padding: 6px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 11px; font-weight: 600; color: #333;">
                                {{ $user->role }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: #64748b; margin-bottom: 8px;">Module Access</div>
                            <div style="font-size: 11px; color: #475569;">
                                @if($user->role === 'SuperAdmin')
                                    <span style="color: #059669;"><i class="fa fa-check-circle"></i> Full SuperAdmin Access</span>
                                @elseif($user->role === 'Admin')
                                    <span style="color: #059669;"><i class="fa fa-check-circle"></i> Full Company Access</span>
                                @else
                                    <span style="color: #64748b;">Limited access based on roles/rules.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Tab --}}
                <div x-show="tab === 'activity'" style="display: none;">
                    <h4 style="margin: 0 0 16px; font-size: 12px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Recent Audit Logs</h4>
                    
                    @if($activityLogs->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($activityLogs as $log)
                            <div style="display: flex; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid #f8fafc;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa fa-info"></i>
                                </div>
                                <div>
                                    <div style="font-size: 11px; color: #1e293b; font-weight: 500;">
                                        User <span style="font-weight: 600;">{{ str_replace('_', ' ', $log->action) }}</span>
                                    </div>
                                    <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                        By {{ $log->user?->name ?? 'System' }} • {{ $log->created_at->format('M d, Y h:i A') }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align: center; padding: 30px; color: #94a3b8; font-size: 11px;">
                            <i class="fa fa-history" style="font-size: 24px; margin-bottom: 8px; opacity: 0.5;"></i>
                            <div>No activity logs found for this user.</div>
                        </div>
                    @endif
                </div>

                {{-- Sessions Tab --}}
                <div x-show="tab === 'sessions'" style="display: none;">
                    <h4 style="margin: 0 0 16px; font-size: 12px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Security Settings</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                        <div style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 4px; background: #f8fafc;">
                            <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Multi-Factor Auth (MFA)</div>
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: {{ $user->mfa_enabled ? '#059669' : '#ef4444' }};">
                                <i class="fa {{ $user->mfa_enabled ? 'fa-lock' : 'fa-unlock' }}"></i> 
                                {{ $user->mfa_enabled ? 'Enabled' : 'Disabled' }}
                            </div>
                        </div>
                        <div style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 4px; background: #f8fafc;">
                            <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Last Login IP</div>
                            <div style="font-size: 12px; font-weight: 600; color: #1e293b;">
                                {{ $user->last_login_ip ?? 'Unknown' }}
                            </div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 4px;">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : '' }}</div>
                        </div>
                    </div>

                    <h4 style="margin: 0 0 16px; font-size: 12px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Login History (Placeholder)</h4>
                    <div style="text-align: center; padding: 20px; border: 1px dashed #cbd5e1; border-radius: 4px; color: #94a3b8; font-size: 11px;">
                        Detailed session tracking would be displayed here.
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
