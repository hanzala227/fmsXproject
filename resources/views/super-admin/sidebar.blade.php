<aside class="flex-shrink-0 h-screen" style="background: #405189; width: 220px;">
    <div class="flex flex-col h-screen" style="background: #405189; color: #fff;">
        {{-- Logo Area --}}
        <div style="height: 50px; display: flex; align-items: center; padding: 0 16px; border-bottom: 1px solid rgba(255,255,255,0.1); justify-content: space-between;">
            <a href="{{ route('super-admin.dashboard') }}" style="color: #fff; text-decoration: none; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">
                FMS <span style="color: #0ab39c; font-size: 8px; vertical-align: super;">ADMIN</span>
            </a>
            <button onclick="document.body.classList.remove('sidebar-mobile-open')" class="md:hidden" style="background: none; border: none; color: rgba(255,255,255,0.6); font-size: 14px; cursor: pointer;">
                <i class="fa fa-times"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto custom-scrollbar" style="padding: 12px 0;">
            @php
                $menuGroups = [
                    'Core' => [
                        [
                            'label' => 'Dashboard', 'icon' => 'fa-dashboard', 'route' => 'super-admin.dashboard', 'match' => 'super-admin',
                            'sub' => [
                                ['label' => 'Overview', 'route' => 'super-admin.dashboard'],
                                ['label' => 'Reports', 'route' => '#'],
                                ['label' => 'System Status', 'route' => '#'],
                            ]
                        ],
                        [
                            'label' => 'Companies', 'icon' => 'fa-building', 'route' => 'super-admin.companies.index', 'match' => 'super-admin/companies',
                            'sub' => [
                                ['label' => 'All Companies', 'route' => 'super-admin.companies.index', 'params' => []],
                                ['label' => 'Active Companies', 'route' => 'super-admin.companies.index', 'params' => ['status' => 'active']],
                                ['label' => 'Trial / Suspended', 'route' => 'super-admin.companies.index', 'params' => ['status' => 'trial']],
                                ['label' => 'Add Company', 'route' => 'super-admin.companies.create', 'params' => []],
                            ]
                        ],
                        [
                            'label' => 'Accounts & Access', 'icon' => 'fa-users', 'route' => 'super-admin.users.index', 'match' => 'super-admin/users',
                            'sub' => [
                                ['label' => 'All Users', 'route' => 'super-admin.users.index', 'params' => []],
                                ['label' => 'Support Team', 'route' => 'super-admin.users.index', 'params' => ['role' => 'Support Team']],
                                ['label' => 'Developers', 'route' => 'super-admin.users.index', 'params' => ['role' => 'Developers']],
                                ['label' => 'Administrators', 'route' => 'super-admin.users.index', 'params' => ['role' => 'Administrators']],
                            ]
                        ],
                    ],
                    'Operations' => [
                        [
                            'label' => 'Roles & Rules', 'icon' => 'fa-shield', 'route' => 'super-admin.roles.index', 'match' => 'super-admin/role',
                            'sub' => [
                                ['label' => 'Roles', 'route' => 'super-admin.roles.index'],
                                ['label' => 'Permissions', 'route' => 'super-admin.permissions.index'],
                                ['label' => 'Role Permissions', 'route' => 'super-admin.role-permissions.index'],
                                ['label' => 'Company Permissions', 'route' => '#'],
                            ]
                        ],
                        [
                            'label' => 'Bugs & Issues', 'icon' => 'fa-bug', 'route' => '#', 'match' => 'super-admin/bugs',
                            'sub' => [
                                ['label' => 'All Bugs & Issues', 'route' => 'super-admin.bugs.index'],
                            ]
                        ],
                        [
                            'label' => 'Support Center', 'icon' => 'fa-life-ring', 'route' => 'super-admin.support-center.index', 'match' => 'super-admin/support-center',
                            'sub' => [
                                ['label' => 'All Tickets', 'route' => 'super-admin.support-center.index'],
                                ['label' => 'New Ticket', 'route' => 'super-admin.support-center.create'],
                                ['label' => 'My Tickets', 'route' => 'super-admin.support-center.my-tickets'],
                                ['label' => 'SLA Management', 'route' => 'super-admin.support-center.sla-management'],
                                ['label' => 'Knowledge Base', 'route' => 'super-admin.support-center.knowledge-base'],
                            ]
                        ],
                    ],
                    'Platform' => [
                        [
                            'label' => 'Module Management', 'icon' => 'fa-puzzle-piece', 'route' => 'super-admin.modules.index', 'match' => 'super-admin/modules',
                            'sub' => [
                                ['label' => 'All Modules', 'route' => 'super-admin.modules.index'],
                                ['label' => 'Module Packages', 'route' => 'super-admin.modules.packages'],
                                ['label' => 'Company Modules', 'route' => 'super-admin.modules.company'],
                                ['label' => 'Feature Flags', 'route' => 'super-admin.modules.feature-flags'],
                            ]
                        ],
                        [
                            'label' => 'Integrations', 'icon' => 'fa-plug', 'route' => '#', 'match' => 'super-admin/integrations',
                            'sub' => [
                                ['label' => 'All Integrations', 'route' => '#'],
                                ['label' => 'API Integrations', 'route' => '#'],
                                ['label' => 'Carrier Tracking APIs', 'route' => 'super-admin.carrier-tracking.index'],
                                ['label' => 'Integration Logs', 'route' => '#'],
                                ['label' => 'API Keys', 'route' => '#'],
                                ['label' => 'AI Engine Settings', 'route' => 'super-admin.ai-settings.index'],
                            ]
                        ],
                        [
                            'label' => 'Releases & Updates', 'icon' => 'fa-rocket', 'route' => '#', 'match' => 'super-admin/releases',
                            'sub' => [
                                ['label' => 'Release Dashboard', 'route' => '#'],
                                ['label' => 'All Releases', 'route' => '#'],
                                ['label' => 'Release Notes', 'route' => '#'],
                                ['label' => 'Deployment Logs', 'route' => '#'],
                            ]
                        ],
                    ],
                    'System' => [
                        [
                            'label' => 'System Health', 'icon' => 'fa-heartbeat', 'route' => '#', 'match' => 'super-admin/health',
                            'sub' => [
                                ['label' => 'System Overview', 'route' => '#'],
                                ['label' => 'Servers', 'route' => '#'],
                                ['label' => 'Database / Queue', 'route' => '#'],
                                ['label' => 'Backup', 'route' => '#'],
                            ]
                        ],
                        [
                            'label' => 'Audit Log', 'icon' => 'fa-history', 'route' => 'super-admin.audit-log', 'match' => 'super-admin/audit-log',
                            'sub' => [
                                ['label' => 'All Activity', 'route' => 'super-admin.audit-log'],
                                ['label' => 'User Activity', 'route' => '#'],
                                ['label' => 'Company Activity', 'route' => '#'],
                                ['label' => 'System Events', 'route' => '#'],
                            ]
                        ],
                        [
                            'label' => 'Billing', 'icon' => 'fa-credit-card', 'route' => '#', 'match' => 'super-admin/billing',
                            'sub' => [
                                ['label' => 'Plans', 'route' => '#'],
                                ['label' => 'Subscriptions', 'route' => '#'],
                                ['label' => 'Invoices', 'route' => '#'],
                                ['label' => 'Payments', 'route' => '#'],
                                ['label' => 'Usage & Limits', 'route' => '#'],
                            ]
                        ],
                        [
                            'label' => 'Settings', 'icon' => 'fa-cog', 'route' => '#', 'match' => 'super-admin/settings',
                            'sub' => [
                                ['label' => 'General Settings', 'route' => '#'],
                                ['label' => 'Security Settings', 'route' => '#'],
                                ['label' => 'Email / Notification', 'route' => '#'],
                                ['label' => 'Notification Rules', 'route' => '#'],
                                ['label' => 'System Config', 'route' => '#'],
                                ['label' => 'Backup Settings', 'route' => '#'],
                            ]
                        ],
                    ]
                ];
            @endphp

            @foreach($menuGroups as $groupName => $items)
                <div style="padding: 12px 12px 4px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(195,203,228,0.7); margin-top: {{ $loop->first ? '0' : '8px' }};">
                    {{ $groupName }}
                </div>

                @foreach($items as $item)
                    @php
                        $isActive = $item['match'] === 'super-admin'
                            ? request()->is('super-admin') && !request()->is('super-admin/*')
                            : request()->is($item['match'] . '*');
                    @endphp
                    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" style="margin: 2px 8px;">
                        <a href="{{ $item['route'] !== '#' ? route($item['route']) : '#' }}"
                           @if($item['sub']) @click.prevent="open = !open" @endif
                           style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; text-decoration: none; transition: all 0.2s;
                           {{ $isActive ? 'background: rgba(255,255,255,0.12); color: #fff; border-left: 3px solid #0ab39c;' : 'color: rgba(195,203,228,0.85); border-left: 3px solid transparent;' }}"
                           onmouseover="if(!{{ $isActive ? 'true' : 'false' }}) { this.style.background='rgba(255,255,255,0.06)'; this.style.color='#fff'; }"
                           onmouseout="if(!{{ $isActive ? 'true' : 'false' }}) { this.style.background='transparent'; this.style.color='rgba(195,203,228,0.85)'; }">
                            <div style="display: flex; align-items: center;">
                                <i class="fa {{ $item['icon'] }}" style="width: 20px; text-align: center; font-size: 13px; margin-right: 10px; opacity: 0.9;"></i>
                                {{ $item['label'] }}
                            </div>
                            @if(count($item['sub']) > 0)
                                <i class="fa fa-angle-down" style="font-size: 14px; transition: transform 0.2s;" :style="open ? 'transform: rotate(180deg);' : ''"></i>
                            @endif
                        </a>
                        
                        @if(count($item['sub']) > 0)
                            <div x-show="open" x-collapse x-cloak style="padding-left: 32px; padding-top: 4px; padding-bottom: 4px; display: none;" :style="open ? 'display: block;' : 'display: none;'">
                                @foreach($item['sub'] as $sub)
                                    @php
                                        // A simple check if the current sub is active based on route and query param
                                        $isSubActive = false;
                                        if ($sub['route'] !== '#') {
                                            $isSubActive = request()->routeIs($sub['route']);
                                            if ($isSubActive && isset($sub['params']) && !empty($sub['params'])) {
                                                foreach($sub['params'] as $key => $val) {
                                                    if(request()->query($key) !== $val) $isSubActive = false;
                                                }
                                            } elseif ($isSubActive && !empty(request()->query())) {
                                                // If we're on the "All" link (no params specified in the sidebar item) 
                                                // but the URL has params (e.g. status=active), it's not active.
                                                if (empty($sub['params'])) {
                                                    $isSubActive = false;
                                                }
                                            }
                                        }
                                    @endphp
                                    <a href="{{ $sub['route'] !== '#' ? route($sub['route'], $sub['params'] ?? []) : '#' }}" 
                                       style="display: block; padding: 8px 12px; font-size: 10px; text-decoration: none; transition: color 0.2s;
                                       {{ $isSubActive ? 'color: #fff; font-weight: 600;' : 'color: rgba(195,203,228,0.7);' }}"
                                       onmouseover="this.style.color='#fff';"
                                       onmouseout="if(!{{ $isSubActive ? 'true' : 'false' }}) this.style.color='rgba(195,203,228,0.7)';">
                                        {{ $sub['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            @endforeach

            {{-- Divider --}}
            <div style="margin: 16px 16px 8px; border-top: 1px solid rgba(255,255,255,0.08);"></div>
            <div style="padding: 0 12px 8px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(195,203,228,0.7);">
                Quick Links
            </div>

            <a href="/"
               style="display: flex; align-items: center; padding: 10px 16px; margin: 1px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; text-decoration: none; color: rgba(195,203,228,0.85); transition: all 0.2s; border-left: 3px solid transparent;"
               onmouseover="this.style.background='rgba(255,255,255,0.06)'; this.style.color='#fff';"
               onmouseout="this.style.background='transparent'; this.style.color='rgba(195,203,228,0.85)';">
                <i class="fa fa-external-link" style="width: 20px; text-align: center; font-size: 13px; margin-right: 10px; opacity: 0.9;"></i>
                ERP Dashboard
            </a>
            
            <div style="height: 30px;"></div>
        </nav>

        {{-- Footer --}}
        <div style="padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 9px; color: rgba(195,203,228,0.5); text-align: center; text-transform: uppercase; letter-spacing: 1px;">
            FreitX SaaS v2.0
        </div>
    </div>
</aside>
