@extends('super-admin.layout')

@section('content')
<div style="padding: 24px; background: #f8fafc; min-height: calc(100vh - 65px);">

    {{-- Top Title & Actions Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: linear-gradient(135deg, #405189 0%, #2f3e6e 100%); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 6px -1px rgba(64, 81, 137, 0.25);">
                    <i class="fa fa-cubes" style="font-size: 18px;"></i>
                </div>
                <div>
                    <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                        Freight ERP Module Management
                    </h1>
                    <p style="font-size: 13px; color: #64748b; margin: 3px 0 0 0;">
                        Modular SaaS architecture directory, tenant deployment adoption, feature availability, and version releases.
                    </p>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('super-admin.modules.company') }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #405189; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.2s;"
               onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-building-o"></i> Tenant Assignments
            </a>
            <a href="{{ route('super-admin.modules.packages') }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.2s;"
               onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-archive"></i> Module Packages
            </a>
            <button onclick="document.getElementById('createModuleModal').style.display='flex'" 
                    style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 16px; background: #405189; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 4px rgba(64,81,137,0.25); transition: background 0.15s;"
                    onmouseover="this.style.background='#364574'" onmouseout="this.style.background='#405189'">
                <i class="fa fa-plus"></i> Add Module
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <i class="fa fa-check-circle" style="color: #059669; font-size: 16px;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- 4 Metric KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Card 1: Total Modules --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Registered Modules</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $stats['total_modules'] }}</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Core & specialized suites</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-puzzle-piece"></i>
            </div>
        </div>

        {{-- Card 2: Freight & Logistics --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Freight & Logistics</div>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; margin-top: 4px;">{{ $stats['freight_modules'] }}</div>
                <div style="font-size: 11px; color: #0284c7; margin-top: 2px; font-weight: 500;">Ocean, Air, Land & Customs</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-ship"></i>
            </div>
        </div>

        {{-- Card 3: Active Deployments --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Tenant Deployments</div>
                <div style="font-size: 24px; font-weight: 800; color: #059669; margin-top: 4px;">{{ $stats['active_deployments'] }}</div>
                <div style="font-size: 11px; color: #059669; margin-top: 2px; font-weight: 500;">Across {{ $stats['total_tenants'] }} tenant ERPs</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-check-circle"></i>
            </div>
        </div>

        {{-- Card 4: Platform Health --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Architecture Health</div>
                <div style="font-size: 24px; font-weight: 800; color: #7c3aed; margin-top: 4px;">100%</div>
                <div style="font-size: 11px; color: #7c3aed; margin-top: 2px; font-weight: 500;">Modular & Fully Isolated</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-microchip"></i>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        
        {{-- Navigation Tabs --}}
        <div style="display: flex; gap: 4px; padding: 12px 16px 0 16px; border-bottom: 1px solid #e2e8f0; background: #fbfcfd; overflow-x: auto;">
            @php
                $tabs = [
                    'all'        => ['label' => 'All Modules', 'count' => $counts['all'], 'icon' => 'fa-th-large'],
                    'freight'    => ['label' => 'Freight & Logistics', 'count' => $counts['freight'], 'icon' => 'fa-ship'],
                    'finance'    => ['label' => 'Finance & Billing', 'count' => $counts['finance'], 'icon' => 'fa-calculator'],
                    'commercial' => ['label' => 'Commercial & CRM', 'count' => $counts['commercial'], 'icon' => 'fa-briefcase'],
                    'platform'   => ['label' => 'Core & Platform', 'count' => $counts['platform'], 'icon' => 'fa-sliders'],
                ];
            @endphp

            @foreach($tabs as $key => $tab)
            @php $isActive = ($currentTab === $key); @endphp
            <a href="{{ route('super-admin.modules.index', array_merge(request()->except('page'), ['tab' => $key])) }}" 
               style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; font-size: 13px; font-weight: {{ $isActive ? '700' : '500' }}; color: {{ $isActive ? '#405189' : '#64748b' }}; text-decoration: none; border-bottom: 2px solid {{ $isActive ? '#405189' : 'transparent' }}; background: transparent; transition: all 0.15s; white-space: nowrap;">
                <i class="fa {{ $tab['icon'] }}" style="font-size: 13px;"></i>
                <span>{{ $tab['label'] }}</span>
                <span style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 10px; background: {{ $isActive ? '#e0e7ff' : '#f1f5f9' }}; color: {{ $isActive ? '#3730a3' : '#475569' }};">
                    {{ $tab['count'] }}
                </span>
            </a>
            @endforeach
        </div>

        {{-- Filter & Search Form --}}
        <div style="padding: 16px 20px; background: #fff; border-bottom: 1px solid #f1f5f9;">
            <form method="GET" action="{{ route('super-admin.modules.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin: 0;">
                <input type="hidden" name="tab" value="{{ $currentTab }}">

                {{-- Search Input --}}
                <div style="flex: 1 1 260px; min-width: 220px; position: relative;">
                    <i class="fa fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search module name, key (e.g. ocean_import), category..." 
                           style="width: 100%; height: 36px; padding: 0 12px 0 34px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; transition: border-color 0.2s;"
                           onfocus="this.style.borderColor='#405189';" onblur="this.style.borderColor='#cbd5e1';">
                </div>

                {{-- Category Filter --}}
                <div style="flex: 0 0 auto;">
                    <select name="category" onchange="this.form.submit()" 
                            style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; color: #334155;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div style="flex: 0 0 auto;">
                    <select name="status" onchange="this.form.submit()" 
                            style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; color: #334155;">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>🟢 Active</option>
                        <option value="Beta" {{ request('status') === 'Beta' ? 'selected' : '' }}>🟡 Beta</option>
                        <option value="Deprecated" {{ request('status') === 'Deprecated' ? 'selected' : '' }}>🟠 Deprecated</option>
                        <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>⚪ Inactive</option>
                    </select>
                </div>

                {{-- Filter & Clear Buttons --}}
                <button type="submit" style="height: 36px; padding: 0 16px; background: #405189; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-filter"></i> Filter
                </button>

                @if(request('search') || request('category') || request('status') || request('tab'))
                <a href="{{ route('super-admin.modules.index') }}" 
                   style="height: 36px; padding: 0 14px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa fa-times"></i> Clear
                </a>
                @endif
            </form>
        </div>

        {{-- Enterprise Modules Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; width: 60px;">ID</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Module & Workflow Scope</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Domain / Category</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Version / Released</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Tenant Adoption</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modules as $module)
                    @php
                        // Category pill styling
                        $catStyles = [
                            'Freight'       => ['bg' => '#e0f2fe', 'color' => '#0369a1', 'icon' => 'fa-ship'],
                            'Logistics'     => ['bg' => '#fef3c7', 'color' => '#b45309', 'icon' => 'fa-truck'],
                            'Finance'       => ['bg' => '#ccfbf1', 'color' => '#0f766e', 'icon' => 'fa-calculator'],
                            'Commercial'    => ['bg' => '#fae8ff', 'color' => '#86198f', 'icon' => 'fa-briefcase'],
                            'Configuration' => ['bg' => '#f1f5f9', 'color' => '#334155', 'icon' => 'fa-sliders'],
                            'Operations'    => ['bg' => '#e0e7ff', 'color' => '#3730a3', 'icon' => 'fa-cogs'],
                            'Core'          => ['bg' => '#f8fafc', 'color' => '#475569', 'icon' => 'fa-cube'],
                            'Platform'      => ['bg' => '#ede9fe', 'color' => '#6d28d9', 'icon' => 'fa-code-fork'],
                        ];
                        $catStyle = $catStyles[$module->category] ?? ['bg' => '#f1f5f9', 'color' => '#475569', 'icon' => 'fa-puzzle-piece'];

                        // Specific icon based on module key
                        $k = strtolower($module->key);
                        if (str_contains($k, 'ocean')) {
                            $mIcon = 'fa-ship'; $iColor = '#0284c7'; $iBg = '#e0f2fe';
                        } elseif (str_contains($k, 'air')) {
                            $mIcon = 'fa-plane'; $iColor = '#7c3aed'; $iBg = '#ede9fe';
                        } elseif (str_contains($k, 'truck')) {
                            $mIcon = 'fa-truck'; $iColor = '#d97706'; $iBg = '#fef3c7';
                        } elseif (str_contains($k, 'custom')) {
                            $mIcon = 'fa-shield'; $iColor = '#16a34a'; $iBg = '#dcfce7';
                        } elseif (str_contains($k, 'warehouse')) {
                            $mIcon = 'fa-cubes'; $iColor = '#ea580c'; $iBg = '#ffedd5';
                        } elseif (str_contains($k, 'account')) {
                            $mIcon = 'fa-calculator'; $iColor = '#0d9488'; $iBg = '#ccfbf1';
                        } elseif (str_contains($k, 'crm') || str_contains($k, 'sales')) {
                            $mIcon = 'fa-line-chart'; $iColor = '#db2777'; $iBg = '#fce7f3';
                        } elseif (str_contains($k, 'report')) {
                            $mIcon = 'fa-pie-chart'; $iColor = '#4f46e5'; $iBg = '#e0e7ff';
                        } elseif (str_contains($k, 'master') || str_contains($k, 'partner')) {
                            $mIcon = 'fa-address-book-o'; $iColor = '#2563eb'; $iBg = '#dbeafe';
                        } elseif (str_contains($k, 'setting')) {
                            $mIcon = 'fa-cog'; $iColor = '#475569'; $iBg = '#f1f5f9';
                        } elseif (str_contains($k, 'company') || str_contains($k, 'dashboard')) {
                            $mIcon = 'fa-building-o'; $iColor = '#405189'; $iBg = '#e0e7ff';
                        } else {
                            $mIcon = 'fa-puzzle-piece'; $iColor = '#64748b'; $iBg = '#f1f5f9';
                        }

                        // Status styling
                        $statStyles = [
                            'Active'     => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'dot' => '#10b981'],
                            'Beta'       => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'dot' => '#f59e0b'],
                            'Deprecated' => ['bg' => '#fff1f2', 'color' => '#be123c', 'border' => '#fecdd3', 'dot' => '#f43f5e'],
                            'Inactive'   => ['bg' => '#f8fafc', 'color' => '#64748b', 'border' => '#e2e8f0', 'dot' => '#94a3b8'],
                        ];
                        $st = $statStyles[$module->status] ?? $statStyles['Active'];

                        // Real active companies count
                        $activeCount = $module->active_companies_count;
                        $totalTenants = $stats['total_tenants'] > 0 ? $stats['total_tenants'] : 1;
                        $pct = round(($activeCount / $totalTenants) * 100);
                    @endphp

                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fbfcfd';" onmouseout="this.style.background='#fff';">
                        
                        {{-- 1. ID --}}
                        <td style="padding: 14px 18px; vertical-align: top; color: #64748b; font-weight: 700; font-family: monospace; font-size: 12px;">
                            #{{ str_pad($module->id, 2, '0', STR_PAD_LEFT) }}
                        </td>

                        {{-- 2. Module & Workflow Scope --}}
                        <td style="padding: 14px 18px; vertical-align: top; max-width: 380px;">
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: {{ $iBg }}; color: {{ $iColor }}; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    <i class="fa {{ $mIcon }}"></i>
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <a href="{{ route('super-admin.modules.show', $module->id) }}" 
                                           style="font-size: 13.5px; font-weight: 700; color: #0f172a; text-decoration: none; transition: color 0.15s;"
                                           onmouseover="this.style.color='#405189'" onmouseout="this.style.color='#0f172a'">
                                            {{ $module->name }}
                                        </a>
                                        <span style="font-family: monospace; font-size: 11px; font-weight: 600; padding: 1px 6px; border-radius: 4px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                                            {{ $module->key }}
                                        </span>
                                    </div>
                                    @if($module->description)
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 4px; line-height: 1.45;">
                                        {{ $module->description }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- 3. Domain / Category --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; background: {{ $catStyle['bg'] }}; color: {{ $catStyle['color'] }}; font-size: 11px; font-weight: 700;">
                                <i class="fa {{ $catStyle['icon'] }}"></i>
                                <span>{{ $module->category ?? 'Core' }}</span>
                            </span>
                        </td>

                        {{-- 4. Version & Released --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <div style="display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px; background: #e0e7ff; color: #3730a3; font-family: monospace;">
                                v{{ $module->version ?? '1.0.0' }}
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                {{ $module->release_date ? $module->release_date->format('M d, Y') : 'Oct 05, 2026' }}
                            </div>
                        </td>

                        {{-- 5. Tenant Adoption (Active Companies) --}}
                        <td style="padding: 14px 18px; vertical-align: top; text-align: center;">
                            <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
                                <span style="font-size: 13px; font-weight: 800; color: {{ $activeCount > 0 ? '#059669' : '#94a3b8' }};">
                                    {{ $activeCount }} {{ Str::plural('Tenant', $activeCount) }}
                                </span>
                                <span style="font-size: 10px; font-weight: 600; padding: 1px 7px; border-radius: 10px; background: {{ $activeCount > 0 ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $activeCount > 0 ? '#047857' : '#64748b' }};">
                                    {{ $pct }}% Adoption
                                </span>
                            </div>
                        </td>

                        {{-- 6. Status --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; background: {{ $st['bg'] }}; color: {{ $st['color'] }}; border: 1px solid {{ $st['border'] }}; font-size: 11px; font-weight: 700;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $st['dot'] }};"></span>
                                {{ $module->status }}
                            </span>
                        </td>

                        {{-- 7. Actions --}}
                        <td style="padding: 14px 18px; vertical-align: top; text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <a href="{{ route('super-admin.modules.show', $module->id) }}" 
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; background: #fff; border: 1px solid #cbd5e1; color: #405189; text-decoration: none; font-size: 12px; transition: all 0.15s;"
                                   title="Configure Module"
                                   onmouseover="this.style.background='#eff6ff'; this.style.borderColor='#3b82f6'" 
                                   onmouseout="this.style.background='#fff'; this.style.borderColor='#cbd5e1'">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="{{ route('super-admin.modules.company') }}" 
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; background: #fff; border: 1px solid #cbd5e1; color: #0284c7; text-decoration: none; font-size: 12px; transition: all 0.15s;"
                                   title="Manage Tenant Assignments"
                                   onmouseover="this.style.background='#f0f9ff'; this.style.borderColor='#0284c7'" 
                                   onmouseout="this.style.background='#fff'; this.style.borderColor='#cbd5e1'">
                                    <i class="fa fa-building-o"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 48px 24px; text-align: center;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                                <i class="fa fa-puzzle-piece"></i>
                            </div>
                            <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">No Modules Found</h3>
                            <p style="font-size: 12px; color: #64748b; margin: 0 auto; max-width: 400px;">
                                No system modules match your current filter query.
                            </p>
                            @if(request('search') || request('category') || request('status') || request('tab'))
                            <div style="margin-top: 14px;">
                                <a href="{{ route('super-admin.modules.index') }}" 
                                   style="display: inline-block; padding: 6px 14px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none;">
                                    Reset Filters
                                </a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Create Module Modal --}}
<div id="createModuleModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); overflow: hidden; animation: fadeIn 0.2s ease;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; border-radius: 6px; background: #e0e7ff; color: #3730a3; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                    <i class="fa fa-plus"></i>
                </div>
                <h3 style="margin: 0; font-size: 15px; color: #0f172a; font-weight: 700;">Add New Freight Module</h3>
            </div>
            <button type="button" onclick="document.getElementById('createModuleModal').style.display='none'" style="background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer; padding: 4px;">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('super-admin.modules.store') }}" style="padding: 24px;">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Module Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Customs Brokerage" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Unique Key * (lowercase)</label>
                    <input type="text" name="key" required placeholder="e.g. customs_brokerage" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box; font-family: monospace;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Category / Suite</label>
                    <select name="category" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; box-sizing: border-box;">
                        <option value="Freight">Freight Operations</option>
                        <option value="Logistics">Inland Logistics & CFS</option>
                        <option value="Finance">Finance & Billing</option>
                        <option value="Commercial">Commercial & Sales</option>
                        <option value="Configuration">Configuration & Master Data</option>
                        <option value="Operations">Operations & RBAC</option>
                        <option value="Core">Core System</option>
                        <option value="Platform">Platform & Integrations</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Version</label>
                    <input type="text" name="version" value="1.0.0" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box; font-family: monospace;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Initial Status</label>
                <select name="status" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; box-sizing: border-box;">
                    <option value="Active">Active (Available for Tenants)</option>
                    <option value="Beta">Beta (Early Access)</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Module Description & Scope</label>
                <textarea name="description" rows="3" placeholder="Describe the freight workflows, documents generated, and compliance capabilities..." style="width: 100%; padding: 10px 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; resize: vertical; box-sizing: border-box; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                <button type="button" onclick="document.getElementById('createModuleModal').style.display='none'" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">Cancel</button>
                <button type="submit" style="height: 36px; padding: 0 18px; border: none; background: #405189; color: #fff; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 700; box-shadow: 0 1px 2px rgba(64,81,137,0.25);">
                    <i class="fa fa-save" style="margin-right: 4px;"></i> Save Module
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
