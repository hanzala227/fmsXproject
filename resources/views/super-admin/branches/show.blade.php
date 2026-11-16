@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px; max-width: 1100px; margin: 0 auto;">
    {{-- Header --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">{{ $branch->name }}</h1>
                <span style="font-size: 11px; font-weight: 700; font-family: monospace; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px;">
                    {{ $branch->code }}
                </span>
                @if($branch->is_head_office)
                    <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 12px;">
                        <i class="fa fa-star" style="margin-right: 3px;"></i> Company HQ
                    </span>
                @endif
                <span style="font-size: 10px; padding: 2px 8px; border-radius: 12px; font-weight: 600; {{ $branch->is_active ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;' }}">
                    {{ $branch->is_active ? 'Active & Operational' : 'Inactive' }}
                </span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 0;">
                Station Type: <strong>{{ $branch->branch_type }}</strong> | Parent Tenant: 
                @if($branch->company)
                    <a href="{{ route('super-admin.companies.show', $branch->company) }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                        {{ $branch->company->name }}
                    </a>
                @else
                    N/A
                @endif
                | Created: {{ $branch->created_at ? $branch->created_at->format('M d, Y') : 'N/A' }}
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('super-admin.branches.index') }}" style="height: 32px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; background: #fff; text-decoration: none;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-arrow-left"></i> All Branches
            </a>
            <a href="{{ route('super-admin.branches.edit', $branch) }}" style="height: 32px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; border: none; border-radius: 4px; color: #fff; background: #3b82f6; text-decoration: none;" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                <i class="fa fa-pencil"></i> Edit Station
            </a>
            <form method="POST" action="{{ route('super-admin.branches.toggle-status', $branch) }}" style="display: inline; margin: 0;">
                @csrf
                @method('PATCH')
                <button type="submit" style="height: 32px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 4px; color: {{ $branch->is_active ? '#b91c1c' : '#047857' }}; background: #fff; cursor: pointer;">
                    <i class="fa {{ $branch->is_active ? 'fa-ban' : 'fa-check' }}"></i> {{ $branch->is_active ? 'Suspend' : 'Activate' }}
                </button>
            </form>
            <form method="POST" action="{{ route('super-admin.branches.destroy', $branch) }}" style="display: inline; margin: 0;" onsubmit="return confirm('Are you sure you want to delete branch \'{{ $branch->name }}\'?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="height: 32px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: 1px solid #fca5a5; border-radius: 4px; color: #ef4444; background: #fff; cursor: pointer;">
                    <i class="fa fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Session Flash Notifications --}}
    @if(session('success'))
    <div style="margin-bottom: 16px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 12px; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    {{-- Main Grid --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Logistics & Customs Identification Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-globe" style="color: #0284c7;"></i> International Freight & Customs Specifications
                </div>
                <div style="padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">IATA Airport Code</div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; font-family: monospace;">
                            {{ $branch->iata_code ?: '— (None Assigned)' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">UN/LOCODE Port Hub</div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; font-family: monospace;">
                            {{ $branch->unlocode ?: '— (None Assigned)' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Customs Station License No</div>
                        <div style="font-size: 13px; font-weight: 600; color: #334155;">
                            {{ $branch->customs_code ?: '—' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Tax / VAT / TRN / NTN</div>
                        <div style="font-size: 13px; font-weight: 600; color: #334155;">
                            {{ $branch->tax_number ?: '—' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Operational Timezone</div>
                        <div style="font-size: 13px; font-weight: 500; color: #334155;">
                            <i class="fa fa-clock-o" style="color: #64748b; margin-right: 4px;"></i> {{ $branch->timezone }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Billing / Station Currency</div>
                        <div style="font-size: 13px; font-weight: 700; color: #059669;">
                            {{ $branch->currency }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location & Terminal Address Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-map-marker" style="color: #ef4444;"></i> Physical Terminal & Geographic Location
                </div>
                <div style="padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Country</div>
                        <div style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $branch->country ?: '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">City / Port Area</div>
                        <div style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $branch->city ?: '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">State / Province</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $branch->state_province ?: '—' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Postal / ZIP Code</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $branch->postal_code ?: '—' }}</div>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Physical Street / Terminal Address</div>
                        <div style="font-size: 13px; font-weight: 500; color: #1e293b; background: #f8fafc; padding: 10px 12px; border-radius: 6px; border: 1px solid #f1f5f9;">
                            {{ $branch->address ?: 'No address registered' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Operational Guidelines / Notes --}}
            @if($branch->notes)
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-info-circle" style="color: #6366f1;"></i> Station Guidelines & Remarks
                </div>
                <div style="padding: 18px; font-size: 12px; color: #334155; line-height: 1.6; white-space: pre-wrap;">{{ $branch->notes }}</div>
            </div>
            @endif
        </div>

        {{-- Sidebar Cards --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Management Contact Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-user" style="color: #10b981;"></i> Station Lead & Contacts
                </div>
                <div style="padding: 18px; display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Station / Branch Manager</div>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                            {{ $branch->manager_name ?: 'General Operations Lead' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Official Email</div>
                        <div style="font-size: 12px; font-weight: 500; color: #2563eb;">
                            @if($branch->email)
                                <a href="mailto:{{ $branch->email }}" style="color: #2563eb; text-decoration: none;">{{ $branch->email }}</a>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Hotline / Phone</div>
                        <div style="font-size: 12px; font-weight: 500; color: #1e293b;">
                            {{ $branch->phone ?: '—' }}
                        </div>
                    </div>
                    @if($branch->fax)
                    <div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Fax Number</div>
                        <div style="font-size: 12px; font-weight: 500; color: #1e293b;">
                            {{ $branch->fax }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Parent Tenant Summary --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); overflow: hidden;">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-building-o" style="color: #405189;"></i> Parent Tenant Summary
                </div>
                <div style="padding: 18px; display: flex; flex-direction: column; gap: 12px;">
                    @if($branch->company)
                        <div>
                            <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Company Name</div>
                            <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                                {{ $branch->company->name }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Tenant Code / Subdomain</div>
                            <div style="font-size: 12px; font-weight: 600; color: #0284c7;">
                                {{ $branch->company->code ?: 'N/A' }} | {{ $branch->company->subdomain }}.fms
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Company Subscription</div>
                            <span style="font-size: 10px; font-weight: 700; background: #d1fae5; color: #065f46; padding: 2px 7px; border-radius: 4px;">
                                {{ ucfirst($branch->company->subscription_status ?? 'Active') }}
                            </span>
                        </div>
                        <div style="padding-top: 6px;">
                            <a href="{{ route('super-admin.companies.show', $branch->company) }}" style="display: block; text-align: center; padding: 8px 12px; font-size: 11px; font-weight: 600; color: #405189; background: #f1f5f9; border-radius: 4px; text-decoration: none;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                View Full Company Profile <i class="fa fa-arrow-right" style="margin-left: 4px;"></i>
                            </a>
                        </div>
                    @else
                        <div style="font-size: 12px; color: #64748b;">No company associated.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
