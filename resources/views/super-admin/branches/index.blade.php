@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Header --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Branch Management</h1>
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Freight Stations</span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">Multi-tenant operational branches, air/ocean port stations, and regional hubs</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('super-admin.companies.index') }}" style="height: 32px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; background: #fff; text-decoration: none;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-building"></i> View Companies
            </a>
            <a href="{{ route('super-admin.branches.create') }}" style="height: 32px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #fff; background: #0ab39c; text-decoration: none; border-radius: 4px; transition: background 0.15s ease;" onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                <i class="fa fa-plus"></i> New Branch
            </a>
        </div>
    </div>

    {{-- Session Flash Notifications --}}
    @if(session('success'))
    <div style="margin-bottom: 16px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 12px; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Total Branches</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $kpis['total'] }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Across all freight tenants</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-sitemap"></i>
            </div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Active Stations</div>
                <div style="font-size: 24px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ $kpis['active'] }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Fully operational hubs</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-check-circle"></i>
            </div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Head Offices</div>
                <div style="font-size: 24px; font-weight: 800; color: #6366f1; margin-top: 4px;">{{ $kpis['head_offices'] }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Primary company HQs</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #eef2ff; color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-building-o"></i>
            </div>
        </div>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Air / Sea Stations</div>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; margin-top: 4px;">{{ $kpis['air_stations'] + $kpis['sea_stations'] }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">{{ $kpis['air_stations'] }} Air | {{ $kpis['sea_stations'] }} Ocean</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #f0f9ff; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-plane"></i>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
        <form method="GET" action="{{ route('super-admin.branches.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin: 0;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, city, IATA, UN/LOCODE, or manager..." style="width: 100%; height: 34px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
            </div>
            <div style="min-width: 180px;">
                <select name="company_id" style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff; color: #334155;">
                    <option value="">All Companies</option>
                    @foreach($companies as $comp)
                        <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>
                            {{ $comp->name }} ({{ $comp->code ?: 'ID:'.$comp->id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 160px;">
                <select name="branch_type" style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff; color: #334155;">
                    <option value="">All Station Types</option>
                    @foreach($branchTypes as $bType)
                        <option value="{{ $bType }}" {{ request('branch_type') == $bType ? 'selected' : '' }}>{{ $bType }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 130px;">
                <select name="status" style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff; color: #334155;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>
            <div style="display: flex; gap: 6px;">
                <button type="submit" style="height: 34px; padding: 0 16px; font-size: 12px; font-weight: 600; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer;">
                    <i class="fa fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'company_id', 'branch_type', 'status']))
                <a href="{{ route('super-admin.branches.index') }}" style="height: 34px; padding: 0 12px; font-size: 12px; font-weight: 500; color: #ef4444; background: #fff; border: 1px solid #fca5a5; border-radius: 4px; display: inline-flex; align-items: center; text-decoration: none;" title="Clear all filters">
                    <i class="fa fa-times"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Branches Table --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Station / Branch</th>
                        <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Parent Company</th>
                        <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Type & Codes</th>
                        <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Location</th>
                        <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Manager & Contact</th>
                        <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Timezone / Currency</th>
                        <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Status</th>
                        <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $b)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        {{-- Branch Code & Name --}}
                        <td style="padding: 12px 14px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ $b->is_head_office ? '#e0e7ff' : '#f1f5f9' }}; color: {{ $b->is_head_office ? '#4338ca' : '#475569' }}; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                                    <i class="fa {{ $b->is_head_office ? 'fa-building' : ($b->branch_type === 'Airport Station' ? 'fa-plane' : ($b->branch_type === 'Seaport Station' ? 'fa-ship' : 'fa-code-fork')) }}"></i>
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <a href="{{ route('super-admin.branches.show', $b) }}" style="font-size: 12px; font-weight: 700; color: #1e293b; text-decoration: none;" onmouseover="this.style.color='#0ab39c'" onmouseout="this.style.color='#1e293b'">
                                            {{ $b->name }}
                                        </a>
                                        @if($b->is_head_office)
                                            <span style="font-size: 9px; font-weight: 700; background: #e0e7ff; color: #3730a3; padding: 1px 6px; border-radius: 4px;">HQ</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 11px; font-family: monospace; color: #0284c7; font-weight: 600; margin-top: 2px;">
                                        {{ $b->code }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Parent Company --}}
                        <td style="padding: 12px 14px;">
                            @if($b->company)
                                <a href="{{ route('super-admin.companies.show', $b->company) }}" style="font-size: 12px; font-weight: 600; color: #2563eb; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $b->company->name }}
                                </a>
                                <div style="font-size: 10px; color: #64748b;">
                                    {{ $b->company->subdomain ? $b->company->subdomain . '.fms' : 'Code: ' . ($b->company->code ?? 'N/A') }}
                                </div>
                            @else
                                <span style="font-size: 11px; color: #94a3b8;">Unassigned</span>
                            @endif
                        </td>

                        {{-- Type & Regulatory Codes --}}
                        <td style="padding: 12px 14px;">
                            <span style="font-size: 10px; font-weight: 600; background: #f1f5f9; color: #334155; padding: 2px 7px; border-radius: 4px;">
                                {{ $b->branch_type }}
                            </span>
                            <div style="display: flex; gap: 6px; margin-top: 4px; font-size: 10px;">
                                @if($b->iata_code)
                                    <span style="background: #fef3c7; color: #92400e; padding: 1px 5px; border-radius: 3px; font-weight: 600;" title="IATA Airport Station">IATA: {{ $b->iata_code }}</span>
                                @endif
                                @if($b->unlocode)
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 1px 5px; border-radius: 3px; font-weight: 600;" title="UN/LOCODE Port Hub">UN: {{ $b->unlocode }}</span>
                                @endif
                            </div>
                        </td>

                        {{-- Location --}}
                        <td style="padding: 12px 14px;">
                            <div style="font-size: 12px; font-weight: 500; color: #1e293b;">
                                {{ $b->city ?: '—' }}{{ $b->country ? ', ' . $b->country : '' }}
                            </div>
                            <div style="font-size: 10px; color: #64748b; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $b->address }}">
                                {{ $b->address ?: 'No address specified' }}
                            </div>
                        </td>

                        {{-- Manager & Contact --}}
                        <td style="padding: 12px 14px;">
                            <div style="font-size: 12px; font-weight: 500; color: #1e293b;">
                                {{ $b->manager_name ?: 'Station Lead' }}
                            </div>
                            <div style="font-size: 10px; color: #64748b;">
                                {{ $b->email ?: ($b->phone ?: '—') }}
                            </div>
                        </td>

                        {{-- Timezone / Currency --}}
                        <td style="padding: 12px 14px;">
                            <div style="font-size: 11px; font-weight: 500; color: #334155;">
                                <i class="fa fa-clock-o" style="color: #64748b; margin-right: 3px;"></i> {{ $b->timezone }}
                            </div>
                            <div style="font-size: 10px; font-weight: 600; color: #059669; margin-top: 2px;">
                                Currency: {{ $b->currency }}
                            </div>
                        </td>

                        {{-- Status with quick toggle --}}
                        <td style="padding: 12px 14px;">
                            <form method="POST" action="{{ route('super-admin.branches.toggle-status', $b) }}" style="margin: 0;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer;" title="Click to toggle status">
                                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 9999px; {{ $b->is_active ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;' }}">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $b->is_active ? '#10b981' : '#ef4444' }};"></span>
                                        {{ $b->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </button>
                            </form>
                        </td>

                        {{-- Actions --}}
                        <td style="padding: 12px 14px; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 4px;">
                                <a href="{{ route('super-admin.branches.show', $b) }}" style="width: 28px; height: 28px; border-radius: 4px; background: #f1f5f9; color: #475569; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-size: 11px;" title="View Details">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.branches.edit', $b) }}" style="width: 28px; height: 28px; border-radius: 4px; background: #e0e7ff; color: #4338ca; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-size: 11px;" title="Edit Branch">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('super-admin.branches.destroy', $b) }}" style="display: inline; margin: 0;" onsubmit="return confirm('Are you sure you want to delete branch \'{{ $b->name }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="width: 28px; height: 28px; border-radius: 4px; background: #fee2e2; color: #ef4444; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; font-size: 11px;" title="Delete Branch">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 40px; text-align: center; color: #64748b; font-size: 13px;">
                            <i class="fa fa-sitemap" style="font-size: 32px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                            No freight branches found matching your filter criteria.
                            <div style="margin-top: 10px;">
                                <a href="{{ route('super-admin.branches.create') }}" style="color: #0ab39c; font-weight: 600; text-decoration: none;">+ Create First Branch</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($branches->hasPages())
        <div style="padding: 12px 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
            <div style="font-size: 11px; color: #64748b;">
                Showing {{ $branches->firstItem() }} to {{ $branches->lastItem() }} of {{ $branches->total() }} branches
            </div>
            <div>
                {{ $branches->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
