@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Page Header --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">{{ $company->name }}</h1>
                <span style="font-size: 10px; padding: 2px 8px; border-radius: 12px; font-weight: 600;
                    {{ $company->status === 'active' ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;' }}">
                    {{ ucfirst($company->status) }}
                </span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 0;">Code: {{ $company->code ?? 'N/A' }} | Created: {{ $company->created_at->format('M d, Y') }}</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('super-admin.companies.index') }}" style="height: 32px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; background: #fff; text-decoration: none;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('super-admin.companies.edit', $company) }}" style="height: 32px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: none; border-radius: 4px; color: #fff; background: #3b82f6; text-decoration: none;" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                <i class="fa fa-pencil"></i> Edit Profile
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Company Information --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-building" style="color: #405189; margin-right: 6px;"></i> Company Profile
                </div>
                <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Legal Name</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $company->legal_name ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Country</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $company->country ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Email</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $company->email ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Phone</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $company->phone ?? '—' }}</div>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Address</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $company->address ?? '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Module Access --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-th-large" style="color: #405189; margin-right: 6px;"></i> Active Modules ({{ $company->modules->count() }})
                </div>
                <div style="padding: 20px; display: flex; flex-wrap: wrap; gap: 8px;">
                    @forelse($company->modules as $mod)
                    <div style="padding: 8px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa {{ config('modules.' . $mod->module_key . '.icon', 'fa-cube') }}" style="color: #3b82f6;"></i>
                        <span style="font-size: 11px; font-weight: 600; color: #1e40af;">{{ config('modules.' . $mod->module_key . '.label', $mod->module_key) }}</span>
                    </div>
                    @empty
                    <div style="font-size: 12px; color: #64748b;">No modules enabled.</div>
                    @endforelse
                </div>
            </div>

            {{-- Users --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #1e293b; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa fa-users" style="color: #405189; margin-right: 6px;"></i> Users ({{ $company->users->count() }} / {{ $company->user_limit }})</span>
                </div>
                <div style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569;">Name</th>
                                <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569;">Email</th>
                                <th style="padding: 10px 20px; text-align: left; font-weight: 600; color: #475569;">Role</th>
                                <th style="padding: 10px 20px; text-align: center; font-weight: 600; color: #475569;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($company->users as $user)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 20px; font-weight: 500; color: #1e293b;">
                                    {{ $user->name }}
                                    @if($user->id === $adminUser?->id)
                                    <span style="font-size: 9px; margin-left: 4px; padding: 1px 4px; background: #f1f5f9; color: #64748b; border-radius: 4px;">Primary Admin</span>
                                    @endif
                                </td>
                                <td style="padding: 10px 20px; color: #475569;">{{ $user->email }}</td>
                                <td style="padding: 10px 20px; color: #475569;">{{ $user->role }}</td>
                                <td style="padding: 10px 20px; text-align: center;">
                                    <span style="font-size: 9px; padding: 2px 8px; border-radius: 10px; font-weight: 600;
                                        {{ $user->status === 'Enable' ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;' }}">
                                        {{ $user->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Subscription Details --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-credit-card" style="color: #405189; margin-right: 6px;"></i> Subscription
                </div>
                <div style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <span style="font-size: 12px; color: #64748b;">Status</span>
                        <span style="font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 600; text-transform: uppercase;
                            {{ match($company->subscription_status) {
                                'active' => 'background: #dbeafe; color: #1e40af;',
                                'trial' => 'background: #fef3c7; color: #b45309;',
                                'suspended', 'expired' => 'background: #fee2e2; color: #991b1b;',
                                default => 'background: #f1f5f9; color: #475569;'
                            } }}">
                            {{ $company->subscription_status ?? 'N/A' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                        <span style="font-size: 12px; color: #64748b;">Storage Limit</span>
                        <span style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $company->storage_limit }} GB</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                        <span style="font-size: 12px; color: #64748b;">User Limit</span>
                        <span style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $company->user_limit }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                        <span style="font-size: 12px; color: #64748b;">Timezone</span>
                        <span style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $company->timezone ?? 'UTC' }}</span>
                    </div>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-history" style="color: #405189; margin-right: 6px;"></i> Recent Activity
                </div>
                <div style="padding: 0;">
                    @forelse($recentActivity as $log)
                    <div style="padding: 12px 20px; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 11px;">
                            <i class="fa fa-circle" style="color: #cbd5e1; font-size: 6px;"></i>
                            <span style="font-weight: 600; color: #334155;">{{ str_replace('_', ' ', ucfirst($log->action)) }}</span>
                        </div>
                        <div style="margin-top: 4px; padding-left: 14px; font-size: 10px; color: #64748b;">
                            By {{ $log->user->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @empty
                    <div style="padding: 24px 20px; text-align: center; color: #94a3b8; font-size: 11px;">No recent activity</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
