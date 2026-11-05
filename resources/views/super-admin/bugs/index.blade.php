@extends('super-admin.layout')

@section('content')
<div style="padding: 24px; background: #f8fafc; min-height: calc(100vh - 65px);">

    {{-- Top Title & Actions Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: linear-gradient(135deg, #405189 0%, #2f3e6e 100%); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 6px -1px rgba(64, 81, 137, 0.25);">
                    <i class="fa fa-bug" style="font-size: 18px;"></i>
                </div>
                <div>
                    <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                        Freight Issues & Bug Tracking
                    </h1>
                    <p style="font-size: 13px; color: #64748b; margin: 3px 0 0 0;">
                        Enterprise triage console for operational shipment exceptions, EDI feeds, customs compliance, and platform defects.
                    </p>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('super-admin.bugs.export-csv', request()->all()) }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #059669; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.2s;"
               onmouseover="this.style.borderColor='#10b981'; this.style.background='#ecfdf5';"
               onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#fff';">
                <i class="fa fa-file-excel-o"></i> Export CSV
            </a>
            <a href="{{ route('super-admin.bugs.index') }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04);"
               title="Refresh Issue Feed">
                <i class="fa fa-refresh"></i> Refresh
            </a>
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
        {{-- Card 1: Total Issues --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Logged Issues</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $stats['total'] }}</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Across all freight tenants</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-ticket"></i>
            </div>
        </div>

        {{-- Card 2: Active Triage --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Active In Triage</div>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; margin-top: 4px;">{{ $stats['active'] }}</div>
                <div style="font-size: 11px; color: #0284c7; margin-top: 2px; font-weight: 500;">Open & In-Progress</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-hourglass-half"></i>
            </div>
        </div>

        {{-- Card 3: Urgent / High SLA --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Urgent SLA Blockers</div>
                <div style="font-size: 24px; font-weight: 800; color: #e11d48; margin-top: 4px;">{{ $stats['urgent_sla'] }}</div>
                <div style="font-size: 11px; color: #e11d48; margin-top: 2px; font-weight: 500;">High / Critical Impact</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #ffe4e6; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
        </div>

        {{-- Card 4: Resolution Rate --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Resolution SLA Rate</div>
                <div style="font-size: 24px; font-weight: 800; color: #059669; margin-top: 4px;">{{ $stats['resolved_rate'] }}%</div>
                <div style="font-size: 11px; color: #059669; margin-top: 2px; font-weight: 500;">Resolved or Closed</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-check-circle"></i>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        
        {{-- Navigation Tabs --}}
        <div style="display: flex; gap: 4px; padding: 12px 16px 0 16px; border-bottom: 1px solid #e2e8f0; background: #fbfcfd; overflow-x: auto;">
            @php
                $tabs = [
                    'all'         => ['label' => 'All Issues', 'count' => $counts['all'], 'icon' => 'fa-list'],
                    'open'        => ['label' => 'Open', 'count' => $counts['open'], 'icon' => 'fa-folder-open-o'],
                    'in_progress' => ['label' => 'In Progress', 'count' => $counts['in_progress'], 'icon' => 'fa-cogs'],
                    'urgent'      => ['label' => 'Urgent & High SLA', 'count' => $counts['urgent'], 'icon' => 'fa-bolt'],
                    'resolved'    => ['label' => 'Resolved / Closed', 'count' => $counts['resolved'], 'icon' => 'fa-check-circle-o'],
                ];
            @endphp

            @foreach($tabs as $key => $tab)
            @php $isActive = ($currentTab === $key); @endphp
            <a href="{{ route('super-admin.bugs.index', array_merge(request()->except('page'), ['tab' => $key])) }}" 
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
            <form method="GET" action="{{ route('super-admin.bugs.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin: 0;">
                <input type="hidden" name="tab" value="{{ $currentTab }}">

                {{-- Search Input --}}
                <div style="flex: 1 1 240px; min-width: 200px; position: relative;">
                    <i class="fa fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search Ticket ID (#00001), subject, module, company..." 
                           style="width: 100%; height: 36px; padding: 0 12px 0 34px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; transition: border-color 0.2s;"
                           onfocus="this.style.borderColor='#405189';" onblur="this.style.borderColor='#cbd5e1';">
                </div>

                {{-- Status Filter --}}
                <div style="flex: 0 0 auto;">
                    <select name="status" onchange="this.form.submit()" 
                            style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; color: #334155;">
                        <option value="">All Statuses</option>
                        <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Resolved" {{ request('status') === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                {{-- Priority Filter --}}
                <div style="flex: 0 0 auto;">
                    <select name="priority" onchange="this.form.submit()" 
                            style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; color: #334155;">
                        <option value="">All Priorities</option>
                        <option value="Urgent" {{ request('priority') === 'Urgent' ? 'selected' : '' }}>⚡ Urgent (Blocker)</option>
                        <option value="High" {{ request('priority') === 'High' ? 'selected' : '' }}>🔺 High (SLA 12h)</option>
                        <option value="Medium" {{ request('priority') === 'Medium' ? 'selected' : '' }}>🔸 Medium (SLA 24h)</option>
                        <option value="Low" {{ request('priority') === 'Low' ? 'selected' : '' }}>🔹 Low (SLA 48h)</option>
                    </select>
                </div>

                {{-- Freight Module Filter --}}
                <div style="flex: 0 0 auto;">
                    <select name="module_name" onchange="this.form.submit()" 
                            style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; color: #334155;">
                        <option value="">All Freight Modules</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" {{ request('module_name') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tenant Company Filter --}}
                <div style="flex: 0 0 auto;">
                    <select name="company_id" onchange="this.form.submit()" 
                            style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; color: #334155;">
                        <option value="">All Tenant Companies</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }} {{ $comp->code ? '(' . $comp->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit & Clear Buttons --}}
                <button type="submit" style="height: 36px; padding: 0 16px; background: #405189; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-filter"></i> Filter
                </button>

                @if(request('search') || request('status') || request('priority') || request('module_name') || request('company_id') || request('tab'))
                <a href="{{ route('super-admin.bugs.index') }}" 
                   style="height: 36px; padding: 0 14px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa fa-times"></i> Clear
                </a>
                @endif
            </form>
        </div>

        {{-- Enterprise Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Ticket ID</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Tenant / Reporter</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Freight Module & Domain</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Issue & Summary</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Severity & SLA</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Reported On</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody style="divide-y: 1px solid #f1f5f9;">
                    @forelse($tickets as $ticket)
                    @php
                        // Priority styling
                        $priorities = [
                            'Urgent' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#fecaca', 'icon' => 'fa-bolt', 'label' => 'Urgent Blocker'],
                            'High'   => ['bg' => '#ffedd5', 'color' => '#9a3412', 'border' => '#fed7aa', 'icon' => 'fa-arrow-up', 'label' => 'High Priority'],
                            'Medium' => ['bg' => '#fef9c3', 'color' => '#854d0e', 'border' => '#fef08a', 'icon' => 'fa-minus', 'label' => 'Medium'],
                            'Low'    => ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#e2e8f0', 'icon' => 'fa-arrow-down', 'label' => 'Low'],
                        ];
                        $priStyle = $priorities[$ticket->priority] ?? $priorities['Medium'];

                        // Status styling
                        $statuses = [
                            'Open'        => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'dot' => '#f59e0b'],
                            'In Progress' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'border' => '#bfdbfe', 'dot' => '#3b82f6'],
                            'Resolved'    => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'dot' => '#10b981'],
                            'Closed'      => ['bg' => '#f8fafc', 'color' => '#64748b', 'border' => '#e2e8f0', 'dot' => '#94a3b8'],
                        ];
                        $statStyle = $statuses[$ticket->status] ?? $statuses['Open'];

                        // Module Icon & color
                        $mod = strtolower($ticket->module_name ?? '');
                        if (str_contains($mod, 'ocean')) {
                            $modIcon = 'fa-ship'; $modBg = '#e0f2fe'; $modColor = '#0369a1';
                        } elseif (str_contains($mod, 'air')) {
                            $modIcon = 'fa-plane'; $modBg = '#ede9fe'; $modColor = '#6d28d9';
                        } elseif (str_contains($mod, 'truck') || str_contains($mod, 'inland')) {
                            $modIcon = 'fa-truck'; $modBg = '#fef3c7'; $modColor = '#b45309';
                        } elseif (str_contains($mod, 'custom')) {
                            $modIcon = 'fa-shield'; $modBg = '#dcfce7'; $modColor = '#15803d';
                        } elseif (str_contains($mod, 'account') || str_contains($mod, 'invoice')) {
                            $modIcon = 'fa-calculator'; $modBg = '#ccfbf1'; $modColor = '#0f766e';
                        } elseif (str_contains($mod, 'crm') || str_contains($mod, 'sales')) {
                            $modIcon = 'fa-briefcase'; $modBg = '#fae8ff'; $modColor = '#a21caf';
                        } elseif (str_contains($mod, 'warehouse')) {
                            $modIcon = 'fa-cubes'; $modBg = '#ffedd5'; $modColor = '#c2410c';
                        } else {
                            $modIcon = 'fa-cube'; $modBg = '#f1f5f9'; $modColor = '#475569';
                        }

                        // Company Initial
                        $compName = optional($ticket->company)->name ?? 'System Tenant';
                        $firstLetter = strtoupper(substr($compName, 0, 1));
                    @endphp

                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fbfcfd';" onmouseout="this.style.background='#fff';">
                        
                        {{-- 1. Ticket ID --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <div style="font-family: monospace; font-size: 13px; font-weight: 700; color: #405189;">
                                #TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                            </div>
                            <div style="font-size: 10px; color: #94a3b8; margin-top: 3px;">
                                {{ $ticket->created_at->diffForHumans() }}
                            </div>
                        </td>

                        {{-- 2. Tenant / Reporter --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 6px; background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    {{ $firstLetter }}
                                </div>
                                <div>
                                    <div style="font-size: 12px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                        <span>{{ $compName }}</span>
                                        @if(optional($ticket->company)->code)
                                        <span style="font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 4px; background: #f1f5f9; color: #475569; letter-spacing: 0.5px;">
                                            {{ $ticket->company->code }}
                                        </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        {{ optional($ticket->user)->name ?? 'Tenant Operator' }}
                                        @if(optional($ticket->user)->email)
                                            <span style="color: #94a3b8; font-size: 10px;">({{ $ticket->user->email }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- 3. Freight Module & Category --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <div style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 8px; border-radius: 6px; background: {{ $modBg }}; color: {{ $modColor }}; font-size: 11px; font-weight: 700;">
                                <i class="fa {{ $modIcon }}"></i>
                                <span>{{ $ticket->module_name ?? 'General Freight' }}</span>
                            </div>
                            @if($ticket->category)
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px; font-weight: 500;">
                                <i class="fa fa-tag" style="font-size: 9px; color: #94a3b8;"></i> {{ $ticket->category }}
                            </div>
                            @endif
                        </td>

                        {{-- 4. Issue & Summary --}}
                        <td style="padding: 14px 18px; vertical-align: top; max-width: 380px;">
                            <a href="{{ route('super-admin.bugs.show', $ticket->id) }}" 
                               style="font-size: 13px; font-weight: 700; color: #1e293b; text-decoration: none; display: block; line-height: 1.35; transition: color 0.15s;"
                               onmouseover="this.style.color='#405189'" onmouseout="this.style.color='#1e293b'">
                                {{ $ticket->title }}
                            </a>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px; line-height: 1.4; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                {{ Str::limit($ticket->description, 130) }}
                            </div>

                            <div style="display: flex; gap: 8px; align-items: center; margin-top: 6px;">
                                @if($ticket->attachments->count() > 0)
                                <span style="font-size: 10px; font-weight: 600; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-paperclip"></i> {{ $ticket->attachments->count() }} {{ Str::plural('Attachment', $ticket->attachments->count()) }}
                                </span>
                                @endif

                                @if($ticket->messages_count > 0)
                                <span style="font-size: 10px; font-weight: 600; color: #7c3aed; background: #f5f3ff; border: 1px solid #ddd6fe; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-commenting-o"></i> {{ $ticket->messages_count }} {{ Str::plural('Reply', $ticket->messages_count) }}
                                </span>
                                @endif
                            </div>
                        </td>

                        {{-- 5. Severity & SLA --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <div style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 12px; background: {{ $priStyle['bg'] }}; color: {{ $priStyle['color'] }}; border: 1px solid {{ $priStyle['border'] }}; font-size: 11px; font-weight: 700;">
                                <i class="fa {{ $priStyle['icon'] }}"></i>
                                <span>{{ $priStyle['label'] }}</span>
                            </div>

                            @if($ticket->sla_due_at)
                                @php
                                    $isBreached = \Carbon\Carbon::parse($ticket->sla_due_at)->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']);
                                @endphp
                                <div style="margin-top: 5px; font-size: 10px; font-weight: 600; display: flex; align-items: center; gap: 4px; color: {{ $isBreached ? '#dc2626' : '#059669' }};">
                                    <i class="fa {{ $isBreached ? 'fa-clock-o' : 'fa-hourglass-start' }}"></i>
                                    <span>
                                        {{ $isBreached ? 'SLA Breached' : 'Due: ' . \Carbon\Carbon::parse($ticket->sla_due_at)->diffForHumans() }}
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- 6. Status --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; background: {{ $statStyle['bg'] }}; color: {{ $statStyle['color'] }}; border: 1px solid {{ $statStyle['border'] }}; font-size: 11px; font-weight: 700;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statStyle['dot'] }};"></span>
                                {{ $ticket->status }}
                            </span>
                        </td>

                        {{-- 7. Reported On --}}
                        <td style="padding: 14px 18px; vertical-align: top; font-size: 11px; color: #64748b;">
                            <div style="font-weight: 600; color: #334155;">{{ $ticket->created_at->format('M d, Y') }}</div>
                            <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">{{ $ticket->created_at->format('h:i A') }}</div>
                        </td>

                        {{-- 8. Actions --}}
                        <td style="padding: 14px 18px; vertical-align: top; text-align: center;">
                            <a href="{{ route('super-admin.bugs.show', $ticket->id) }}" 
                               style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #405189; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(64,81,137,0.2); transition: all 0.15s;"
                               onmouseover="this.style.background='#364574';" onmouseout="this.style.background='#405189';">
                                <span>Manage</span>
                                <i class="fa fa-arrow-right" style="font-size: 10px;"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 48px 24px; text-align: center;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #f0fdf4; color: #16a34a; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                                <i class="fa fa-check-circle-o"></i>
                            </div>
                            <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">No Freight Issues Found</h3>
                            <p style="font-size: 12px; color: #64748b; margin: 0 auto; max-width: 400px;">
                                No bugs or tickets match your current filter criteria. All freight operations and tenant instances are running stably.
                            </p>
                            @if(request('search') || request('status') || request('priority') || request('module_name') || request('company_id') || request('tab'))
                            <div style="margin-top: 14px;">
                                <a href="{{ route('super-admin.bugs.index') }}" 
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

        {{-- Pagination Bar --}}
        @if($tickets->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b;">
            <div>
                Showing <strong>{{ $tickets->firstItem() }}</strong> to <strong>{{ $tickets->lastItem() }}</strong> of <strong>{{ $tickets->total() }}</strong> issues
            </div>
            <div>
                {{ $tickets->links('pagination::simple-tailwind') }}
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
