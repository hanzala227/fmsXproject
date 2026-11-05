@extends('super-admin.layout')

@section('content')
<div style="padding: 24px; background: #f8fafc; min-height: calc(100vh - 65px);">

    {{-- Top Breadcrumb & Quick Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('super-admin.modules.index') }}" 
               style="width: 36px; height: 36px; border-radius: 8px; background: #fff; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; color: #475569; text-decoration: none; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.15s;"
               onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='#fff';">
                <i class="fa fa-arrow-left"></i>
            </a>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-family: monospace; font-size: 12px; font-weight: 700; color: #405189; background: #e0e7ff; padding: 2px 8px; border-radius: 4px;">
                        {{ $module->key }}
                    </span>
                    <h1 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">
                        {{ $module->name }}
                    </h1>
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                    Category: <strong>{{ $module->category }}</strong> • Version: <strong>v{{ $module->version }}</strong>
                </div>
            </div>
        </div>

        {{-- Status Pill --}}
        @php
            $statStyles = [
                'Active'     => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'dot' => '#10b981'],
                'Beta'       => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'dot' => '#f59e0b'],
                'Deprecated' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3', 'dot' => '#f43f5e'],
                'Inactive'   => ['bg' => '#f8fafc', 'color' => '#64748b', 'border' => '#e2e8f0', 'dot' => '#94a3b8'],
            ];
            $st = $statStyles[$module->status] ?? $statStyles['Active'];
        @endphp
        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; background: {{ $st['bg'] }}; color: {{ $st['color'] }}; border: 1px solid {{ $st['border'] }}; font-size: 12px; font-weight: 700;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $st['dot'] }};"></span>
            {{ $module->status }}
        </span>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <i class="fa fa-check-circle" style="color: #059669; font-size: 16px;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Two Column Layout --}}
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">
        
        {{-- LEFT COLUMN: Edit Form --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa fa-pencil-square-o" style="color: #405189; margin-right: 4px;"></i> Configure Module Specifications
                </span>
            </div>

            <form method="POST" action="{{ route('super-admin.modules.update', $module->id) }}" style="padding: 24px;">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Module Name *</label>
                        <input type="text" name="name" value="{{ $module->name }}" required style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Unique Identifier (Read-Only)</label>
                        <input type="text" value="{{ $module->key }}" disabled style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 6px; background: #f8fafc; color: #64748b; font-family: monospace; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Category Suite</label>
                        <select name="category" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; box-sizing: border-box;">
                            <option value="Freight" {{ $module->category === 'Freight' ? 'selected' : '' }}>Freight Operations</option>
                            <option value="Logistics" {{ $module->category === 'Logistics' ? 'selected' : '' }}>Inland Logistics & CFS</option>
                            <option value="Finance" {{ $module->category === 'Finance' ? 'selected' : '' }}>Finance & Billing</option>
                            <option value="Commercial" {{ $module->category === 'Commercial' ? 'selected' : '' }}>Commercial & CRM</option>
                            <option value="Configuration" {{ $module->category === 'Configuration' ? 'selected' : '' }}>Configuration & Master Data</option>
                            <option value="Operations" {{ $module->category === 'Operations' ? 'selected' : '' }}>Operations & RBAC</option>
                            <option value="Core" {{ $module->category === 'Core' ? 'selected' : '' }}>Core System</option>
                            <option value="Platform" {{ $module->category === 'Platform' ? 'selected' : '' }}>Platform & Integrations</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Version</label>
                        <input type="text" name="version" value="{{ $module->version }}" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box; font-family: monospace;">
                    </div>
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Release Status</label>
                    <select name="status" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; box-sizing: border-box;">
                        <option value="Active" {{ $module->status === 'Active' ? 'selected' : '' }}>🟢 Active (Production General Availability)</option>
                        <option value="Beta" {{ $module->status === 'Beta' ? 'selected' : '' }}>🟡 Beta (Preview / Early Adopters)</option>
                        <option value="Deprecated" {{ $module->status === 'Deprecated' ? 'selected' : '' }}>🟠 Deprecated (Scheduled for Sunset)</option>
                        <option value="Inactive" {{ $module->status === 'Inactive' ? 'selected' : '' }}>⚪ Inactive (Disabled)</option>
                    </select>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Workflow Description & Scope</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; resize: vertical; box-sizing: border-box; font-family: inherit; line-height: 1.5;">{{ $module->description }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                    <a href="{{ route('super-admin.modules.index') }}" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center;">Cancel</a>
                    <button type="submit" style="height: 36px; padding: 0 18px; border: none; background: #405189; color: #fff; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 700; box-shadow: 0 1px 2px rgba(64,81,137,0.25);">
                        <i class="fa fa-save" style="margin-right: 4px;"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- RIGHT COLUMN: Active Companies & Adoption --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fa fa-building-o" style="color: #405189; margin-right: 4px;"></i> Active Tenants ({{ $companies->count() }})
                    </span>
                    <a href="{{ route('super-admin.modules.company') }}" style="font-size: 11px; color: #405189; text-decoration: none; font-weight: 600;">
                        Assign <i class="fa fa-arrow-right"></i>
                    </a>
                </div>

                <div style="padding: 12px 16px; max-height: 440px; overflow-y: auto;">
                    @forelse($companies as $company)
                    <div style="padding: 10px 12px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 24px; height: 24px; border-radius: 4px; background: #e0e7ff; color: #3730a3; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center;">
                                {{ strtoupper(substr($company->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size: 12px; font-weight: 700; color: #1e293b;">
                                    {{ $company->name }}
                                </div>
                                @if($company->code)
                                <div style="font-size: 10px; color: #64748b; font-family: monospace;">
                                    {{ $company->code }}
                                </div>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('super-admin.companies.index', ['search' => $company->name]) }}" 
                           style="color: #0284c7; font-size: 11px; text-decoration: none;" title="View Company">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </div>
                    @empty
                    <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12px;">
                        <i class="fa fa-info-circle" style="font-size: 20px; display: block; margin-bottom: 6px; opacity: 0.5;"></i>
                        No tenant companies currently have this module active.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
