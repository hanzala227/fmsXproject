@props(['type' => 'admin'])
<aside class="flex-shrink-0 z-[2000] relative h-screen overflow-hidden"
       x-data="{
    activeMenu: '{{ Request::segment(1) }}',
    openMenus: [],
    isCollapsed: document.body.classList.contains('sidebar-collapsed'),
    toggleMenu(menu) {
        if (this.isCollapsed) return;
        if (this.openMenus.includes(menu)) {
            this.openMenus = this.openMenus.filter(m => m !== menu);
        } else {
            this.openMenus.push(menu);
        }
    }
}"
@sidebar-toggled.window="isCollapsed = document.body.classList.contains('sidebar-collapsed')"
x-init="if(activeMenu) { openMenus.push(activeMenu); if(activeMenu === 'settings') openMenus.push('settings-menu'); }">
    <div class="flex flex-col w-[200px] h-screen border-r border-white/10" style="background-color: #405189 !important; color: #ffffff !important;">
        <!-- Logo Area -->
        <div class="flex items-center h-[50px] px-4 border-b border-white/10 justify-between" style="background-color: #405189 !important;">
            <a href="/" class="flex items-center font-bold tracking-widest text-[11px] uppercase" style="color: #ffffff !important; text-decoration: none;">
                <span style="color: #ffffff !important;">FMS</span>
            </a>
            <button onclick="document.body.classList.remove('sidebar-mobile-open')" class="text-gray-400 hover:text-white md:hidden">
                <i class="fa fa-times text-lg"></i>
            </button>
        </div>

        <div class="flex flex-col flex-1 overflow-y-auto w-full custom-sidebar-scrollbar" style="background-color: #405189 !important;">
            <!-- Search Bar Area -->

            <nav class="flex-1 w-full text-[10px] font-medium pb-10">
<br><br>
                <!-- Main Items -->
                <a href="/" class="sidebar-nav-item flex items-center transition-all duration-200 {{ request()->is('/') ? 'active-link' : 'nav-link' }}" style="text-decoration: none;" :class="isCollapsed ? 'justify-center px-0' : 'px-5'">
                    <div class="flex items-center" :class="isCollapsed ? 'justify-center w-full' : ''">
                        <i class="fa fa-dashboard text-center text-[12px]" :class="isCollapsed ? '!mr-0 !w-auto' : 'mr-3 w-5'"></i>
                        <span x-show="!isCollapsed" class="uppercase tracking-widest font-bold">Dashboard</span>
                    </div>
                </a>

                @if($type === 'admin')
                <a href="/action-center" class="sidebar-nav-item flex items-center transition-all duration-200 {{ request()->is('action-center') ? 'active-link' : 'nav-link' }}" style="text-decoration: none;" :class="isCollapsed ? 'justify-center px-0' : 'px-5'">
                    <div class="flex items-center" :class="isCollapsed ? 'justify-center w-full' : ''">
                        <i class="fa fa-rocket text-center text-[12px]" :class="isCollapsed ? '!mr-0 !w-auto' : 'mr-3 w-5'"></i>
                        <span x-show="!isCollapsed" class="uppercase tracking-widest font-bold">Action Center</span>
                    </div>
                </a>
                @endif

                <!-- Modules Header -->
                <div x-show="!isCollapsed" class="pt-6 pb-2 text-[8px] font-black uppercase tracking-widest border-b border-white/10 mb-1" style="color: #c3cbe4 !important; padding-left: 20px !important;">
                    {{ $type === 'superadmin' ? 'Management' : 'Modules' }}
                </div>

                @php
                    if ($type === 'superadmin') {
                        $menus = [
                            ['id' => 'companies', 'label' => 'Companies', 'icon' => 'fa-building', 'url' => '/super-admin/companies', 'sub' => []],
                            ['id' => 'audit-log', 'label' => 'Audit Log', 'icon' => 'fa-history', 'url' => '/super-admin/audit-log', 'sub' => []],
                        ];
                    } elseif ($type === 'customer') {
                        $menus = [
                            ['id' => 'shipments', 'label' => 'Shipments', 'icon' => 'fa-ship', 'sub' => [
                                ['url' => '/customer-portal/shipments/ocean-import', 'label' => 'Ocean Import'],
                                ['url' => '/customer-portal/shipments/ocean-export', 'label' => 'Ocean Export'],
                                ['url' => '/customer-portal/shipments/air-import', 'label' => 'Air Import'],
                                ['url' => '/customer-portal/shipments/air-export', 'label' => 'Air Export'],
                                ['url' => '/customer-portal/shipments/truck', 'label' => 'Trucking'],
                            ]],
                            ['id' => 'quotes', 'label' => 'Quotes', 'icon' => 'fa-file-text-o', 'url' => '/customer-portal/quotes', 'sub' => []],
                            ['id' => 'invoices', 'label' => 'Invoices', 'icon' => 'fa-calculator', 'url' => '/customer-portal/invoices', 'sub' => []],
                        ];
                    } else {
                        $menus = [
                            ['id' => 'ocean-import', 'label' => 'Ocean Import', 'icon' => 'fa-ship', 'sub' => [
                                ['url' => '/ocean-import/create', 'label' => 'New Shipment'],
                                // ['url' => '/ocean-import/create-quote', 'label' => 'New Shipment from Quote'],
                                ['url' => '/ocean-import/list', 'label' => 'My Shipment List'],
                                // ['url' => '/ocean-import/list/mbl', 'label' => 'Master B/L List'],
                                ['url' => '/ocean-import/list/hbl', 'label' => 'House B/L List'],
                            ]],
                            ['id' => 'ocean-export', 'label' => 'Ocean Export', 'icon' => 'fa-anchor', 'sub' => [
                                ['url' => '/ocean-export/create', 'label' => 'New Shipment'],
                                ['url' => '/ocean-export/create-quote', 'label' => 'New Shipment from Quote'],
                                ['url' => '/ocean-export/list', 'label' => 'My Shipment List'],
                                ['url' => '/ocean-export/list/mbl', 'label' => 'Master B/L List'],
                                ['url' => '/ocean-export/list/hbl', 'label' => 'House B/L List'],
                                // ['url' => '/ocean-export/booking/create', 'label' => 'New Booking'],
                                // ['url' => '/ocean-export/create-quote-booking', 'label' => 'New Booking from Quote'],
                                // ['url' => '/ocean-export/booking/list', 'label' => 'Booking List'],
                                // ['url' => '/ocean-export/vessel-schedule/create', 'label' => 'New Vessel Schedule'],
                                // ['url' => '/ocean-export/vessel-schedule/list', 'label' => 'Vessel Schedule List'],
                            ]],
                            ['id' => 'air-import', 'label' => 'Air Import', 'icon' => 'fa-plane', 'sub' => [
                                ['url' => '/air-import/create', 'label' => 'New Shipment'],
                                ['url' => '/air-import/create-quote', 'label' => 'New Shipment from Quote'],
                                ['url' => '/air-import/my-shipment-list', 'label' => 'My Shipment List'],
                                ['url' => '/air-import/list', 'label' => 'MAWB List'],
                                ['url' => '/air-import/list/hbl', 'label' => 'HAWB List'],
                            ]],
                            ['id' => 'air-export', 'label' => 'Air Export', 'icon' => 'fa-plane', 'sub' => [
                                ['url' => '/air-export/create', 'label' => 'New Shipment'],
                                ['url' => '/air-export/create?load_from_quotation=true', 'label' => 'New Shipment from Quote'],
                                ['url' => '/air-export/list', 'label' => 'My Shipment List'],
                                ['url' => '/air-export/list/mbl', 'label' => 'MAWB List'],
                                ['url' => '/air-export/list/hbl', 'label' => 'HAWB List'],
                                ['url' => '/air-export/booking/entry', 'label' => 'New Booking'],
                                ['url' => '/air-export/booking/entry?load_from_quotation=true', 'label' => 'New Booking from Quote'],
                                ['url' => '/air-export/booking/list', 'label' => 'Booking List'],
                                ['url' => '/air-export/booking/mawb-stock', 'label' => 'MAWB Stock List'],
                            ]],
                            ['id' => 'truck', 'label' => 'Trucking', 'icon' => 'fa-truck', 'sub' => [
                                ['url' => '/truck/create', 'label' => 'New Shipment'],
                                ['url' => '/truck/create-quote', 'label' => 'New Shipment from Quote'],
                                ['url' => '/truck/my-shipment-list', 'label' => 'My Shipment List'],
                                ['url' => '/truck/list', 'label' => 'Master B/L List'],
                            ]],
                            ['id' => 'warehouse', 'label' => 'Warehouse', 'icon' => 'fa-building', 'sub' => [
                                ['label' => 'Receipt', 'type' => 'group', 'items' => [
                                    ['url' => '/warehouse/receipt/create', 'label' => 'New Receipt'],
                                    ['url' => '/warehouse/receipt/list', 'label' => 'Receipt List'],
                                ]],
                                ['label' => 'Receiving & Shipping', 'type' => 'group', 'items' => [
                                    ['url' => '/warehouse/receiving/create', 'label' => 'New Receiving'],
                                    ['url' => '/warehouse/receiving/list', 'label' => 'Receiving List'],
                                    ['url' => '/warehouse/shipping/create', 'label' => 'New Shipping'],
                                    ['url' => '/warehouse/shipping/list', 'label' => 'Shipping List'],
                                    ['url' => '/warehouse/items', 'label' => 'Item List'],
                                    ['url' => '/warehouse/inventory/summary', 'label' => 'Inventory Summary'],
                                    ['url' => '/warehouse/inventory/detail', 'label' => 'Inventory Detail'],
                                ]],
                                ['url' => '/warehouse/automobile', 'label' => 'Automobile List'],
                            ]],
                            ['id' => 'accounting', 'label' => 'Accounting', 'icon' => 'fa-calculator', 'sub' => [
                                ['label' => 'Invoice/Cost', 'type' => 'group', 'items' => [
                                    ['url' => '/accounting/invoice', 'label' => 'Invoice / Cost List'],
                                    ['url' => '/accounting/ga-expense-list', 'label' => 'G&A Invoice / Expense List'],
                                    ['url' => '/accounting/ga-expense/create', 'label' => 'Create G&A Expense'],
                                    ['url' => '/accounting/ga-invoice/create', 'label' => 'Create G&A Invoice'],
                                ]],
                                ['label' => 'Payment', 'type' => 'group', 'items' => [
                                    ['url' => '/accounting/payment/receive', 'label' => 'Receive Payment'],
                                    ['url' => '/accounting/payment/make', 'label' => 'Make Payment'],
                                    ['url' => '/accounting/payment/received-list', 'label' => 'Payment Received List (A/R)'],
                                    ['url' => '/accounting/payment/made-list', 'label' => 'Payment Made List (A/P)'],
                                ]],
                                ['label' => 'Bank', 'type' => 'group', 'items' => [
                                    ['url' => '/accounting/bank/book-balance', 'label' => 'Bank Book Balance'],
                                    ['url' => '/accounting/bank/outstanding', 'label' => 'Bank Outstanding'],
                                    ['url' => '/accounting/bank/reconciliation', 'label' => 'Bank Reconciliation'],
                                    ['url' => '/accounting/bank/batch-process', 'label' => 'Batch Process'],
                                    ['url' => '/accounting/bank/clear-check-by-excel', 'label' => 'Clear Check by Excel'],
                                    ['url' => '/accounting/bank/check-deposit-report', 'label' => 'Check/Deposit Report'],
                                ]],
                                ['label' => 'Journal', 'type' => 'group', 'items' => [
                                    ['url' => '/accounting/journal/entry', 'label' => 'Journal Entry'],
                                    ['url' => '/accounting/general-journal', 'label' => 'General Journal'],
                                    ['url' => '/accounting/journal/block', 'label' => 'Accounting Block / Unblock'],
                                    ['url' => '/accounting/journal/block/maintenance', 'label' => 'Accounting Block Maintenance'],
                                    ['url' => '/accounting/journal/block/history', 'label' => 'Accounting Block History'],
                                    ['url' => '/accounting/year-end-closing', 'label' => 'Year End Closing'],
                                ]],
                                ['label' => 'Report', 'type' => 'group', 'items' => [
                                    ['url' => '/accounting/report/balance-sheet', 'label' => 'Balance Sheet'],
                                    ['url' => '/accounting/report/trial-balance', 'label' => 'Trial Balance'],
                                    ['url' => '/accounting/report/general-ledger', 'label' => 'General Ledger Report'],
                                    ['url' => '/accounting/report/aging-report', 'label' => 'Aging Report'],
                                    ['url' => '/accounting/report/income-statement', 'label' => 'Income Statement'],
                                    ['url' => '/accounting/report/revenue-cost', 'label' => 'Revenue / Cost Report'],
                                    ['url' => '/accounting/report/agent-local-statement', 'label' => 'Agent / Local Statement'],
                                    ['url' => '/accounting/report/freight-statement', 'label' => 'Freight Statement'],
                                    ['url' => '/accounting/report/1099-report', 'label' => '1099 Report'],
                                    ['url' => '/accounting/report/journal-report', 'label' => 'Journal Report'],
                                ]],
                            ]],
                            ['id' => 'sales', 'label' => 'Sales', 'icon' => 'fa-briefcase', 'sub' => [
                                ['url' => '/sales/quotation/create', 'label' => 'New Quotation'],
                                ['url' => '/sales/quotation/list', 'label' => 'Quotation List'],
                            ]],
                            ['id' => 'trade-partners', 'label' => 'Customers', 'icon' => 'fa-handshake-o', 'sub' => [
                                ['url' => '/trade-partner/create', 'label' => 'New Customer'],
                                ['url' => '/trade-partner/credit-entry', 'label' => 'Customer Credit Entry'],
                                ['url' => '/trade-partner/list', 'label' => 'Customer List'],
                                ['url' => '/trade-partner/mapping-list', 'label' => 'Customer Mapping List'],
                            ]],
                            ['id' => 'reports', 'label' => 'Report', 'icon' => 'fa-bar-chart', 'sub' => [
                                ['url' => '/report/advanced', 'label' => 'Advanced Report'],
                                ['url' => '/report/volume-profit', 'label' => 'Volume & Profit Report'],
                                ['url' => '/report/volume-profit-chart', 'label' => 'Volume & Profit Chart'],
                                ['url' => '/report/employee-performance', 'label' => 'Employee Performance Report'],
                                ['url' => '/report/user-log', 'label' => 'User Log In/Out Active Report'],
                                ['url' => '/report/shipment', 'label' => 'Shipment Report'],
                                ['url' => '/report/container-storage', 'label' => 'Container Storage Report'],
                            ]],
                        ];
                    }
                @endphp

                @php
                    if (auth()->check() && !auth()->user()->isSuperAdmin() && auth()->user()->company) {
                        $allowedModules = auth()->user()->company->moduleKeys();
                        $menus = array_filter($menus, fn($m) => in_array($m['id'], $allowedModules));
                    }
                @endphp

                @foreach($menus as $menu)
                <div x-data="{ open: openMenus.includes('{{ $menu['id'] }}'), hovered: false, isCollapsed: document.body.classList.contains('sidebar-collapsed') }"
                     @sidebar-toggled.window="isCollapsed = document.body.classList.contains('sidebar-collapsed')"
                     @mouseenter="if(isCollapsed) hovered = true"
                     @mouseleave="hovered = false"
                     class="relative">
                    <button @click="open = !open; if(open && !openMenus.includes('{{ $menu['id'] }}')) openMenus.push('{{ $menu['id'] }}'); else if(!open) openMenus = openMenus.filter(m => m !== '{{ $menu['id'] }}')"
                            class="sidebar-nav-item w-full flex items-center transition-all duration-200 nav-link"
                            :class="isCollapsed ? 'justify-center px-0' : 'justify-between px-5'">
                        <div class="flex items-center" :class="isCollapsed ? 'justify-center w-full' : ''">
                            <i class="fa {{ $menu['icon'] }} text-center text-[12px] opacity-70" :class="isCollapsed ? '!mr-0 !w-auto' : 'mr-3 w-5'"></i>
                            <span x-show="!isCollapsed" class="uppercase tracking-widest font-bold">{{ $menu['label'] }}</span>
                        </div>
                    </button>
                    <div x-show="(!isCollapsed && open) || (isCollapsed && hovered)"
                         :class="isCollapsed ? ('collapsed-floating-submenu ' + ({{ $loop->index }} >= 5 ? 'collapsed-floating-submenu-bottom' : '')) : ''"
                         style="background-color: #3b4b7a !important; border-top: 1px solid rgba(255,255,255,0.05); z-index: 99999;">
                        @foreach($menu['sub'] as $sub)
                            @if(isset($sub['type']) && $sub['type'] === 'group')
                                @php
                                    $subOpen = false;
                                    foreach($sub['items'] ?? [] as $si) {
                                        if(Request::is(trim($si['url'], '/'))) { $subOpen = true; break; }
                                    }
                                @endphp
                                <div x-data="{ subOpen: {{ $subOpen ? 'true' : 'false' }} }" class="border-b border-white/5 last:border-none">
                                    <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between text-[8.5px] uppercase tracking-widest text-white/60 hover:text-white transition-all duration-200" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; padding-right: 20px !important;">
                                        <span class="font-bold">{{ $sub['label'] }}</span>
                                    </button>
                                    <div x-show="subOpen" class="bg-black/10">
                                        @foreach($sub['items'] as $item)
                                            <a href="{{ $item['url'] }}" class="block text-[8px] transition-all duration-200 {{ Request::is(trim($item['url'], '/')) ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest" style="padding-left: 68px !important; padding-top: 8px !important; padding-bottom: 8px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                                <i class="fa fa-circle-o mr-2 opacity-30" style="font-size: 6px;"></i> {{ $item['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ $sub['url'] }}" class="block text-[9px] transition-all duration-200 {{ Request::is(trim($sub['url'], '/')) ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest border-b border-white/5 last:border-none" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                    {{ $sub['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if($type === 'admin')
                <!-- Intelligence Section -->
                <div x-show="!isCollapsed" class="pt-6 pb-2 text-[8px] font-black uppercase tracking-widest border-b border-white/10 mb-1" style="color: #c3cbe4 !important; padding-left: 20px !important;">Intelligence</div>

                <a href="/crm" class="sidebar-nav-item flex items-center transition-all duration-200 {{ request()->is('crm*') ? 'active-link' : 'nav-link' }}" style="text-decoration: none;" :class="isCollapsed ? 'justify-center px-0' : 'px-5'">
                    <div class="flex items-center" :class="isCollapsed ? 'justify-center w-full' : ''">
                        <i class="fa fa-users text-center text-[12px] opacity-70" :class="isCollapsed ? '!mr-0 !w-auto' : 'mr-3 w-5'"></i>
                        <span x-show="!isCollapsed" class="uppercase tracking-widest font-bold">Customers</span>
                    </div>
                </a>

                <!-- Settings Menu with Accounting Submenu -->
                <div x-data="{ open: openMenus.includes('settings-menu'), hovered: false, isCollapsed: document.body.classList.contains('sidebar-collapsed') }"
                     @sidebar-toggled.window="isCollapsed = document.body.classList.contains('sidebar-collapsed')"
                     @mouseenter="if(isCollapsed) hovered = true"
                     @mouseleave="hovered = false"
                     class="relative">
                    <button @click="open = !open; if(open && !openMenus.includes('settings-menu')) openMenus.push('settings-menu'); else if(!open) openMenus = openMenus.filter(m => m !== 'settings-menu')"
                            class="sidebar-nav-item w-full flex items-center transition-all duration-200 nav-link"
                            :class="isCollapsed ? 'justify-center px-0' : 'justify-between px-5'">
                        <div class="flex items-center" :class="isCollapsed ? 'justify-center w-full' : ''">
                            <i class="fa fa-cogs text-center text-[12px] opacity-70" :class="isCollapsed ? '!mr-0 !w-auto' : 'mr-3 w-5'"></i>
                            <span x-show="!isCollapsed" class="uppercase tracking-widest font-bold">Settings</span>
                        </div>
                    </button>
                    <div x-show="(!isCollapsed && open) || (isCollapsed && hovered)"
                         :class="isCollapsed ? 'collapsed-floating-submenu collapsed-floating-submenu-bottom' : ''"
                         style="background-color: #3b4b7a !important; border-top: 1px solid rgba(255,255,255,0.05); z-index: 99999;">
                        
                        <!-- Accounting Nested Submenu -->
                        @php
                            $accountingOpen = Request::is('accounting/currency-table') || Request::is('accounting/bank-list') || Request::is('accounting/billing-code-list') || Request::is('accounting/gl-code-list');
                        @endphp
                        <div x-data="{ subOpen: {{ $accountingOpen ? 'true' : 'false' }} }" class="border-b border-white/5 last:border-none">
                            <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between text-[8.5px] uppercase tracking-widest text-white/60 hover:text-white transition-all duration-200" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; padding-right: 20px !important;">
                                <span class="font-bold">Accounting</span>
                            </button>
                            <div x-show="subOpen" class="bg-black/10">
                                <a href="/accounting/currency-table" class="block text-[8px] transition-all duration-200 {{ Request::is('accounting/currency-table') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest" style="padding-left: 68px !important; padding-top: 8px !important; padding-bottom: 8px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                    <i class="fa fa-circle-o mr-2 opacity-30" style="font-size: 6px;"></i> Currency Table
                                </a>
                                <a href="/accounting/bank-list" class="block text-[8px] transition-all duration-200 {{ Request::is('accounting/bank-list') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest" style="padding-left: 68px !important; padding-top: 8px !important; padding-bottom: 8px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                    <i class="fa fa-circle-o mr-2 opacity-30" style="font-size: 6px;"></i> Bank List
                                </a>
                                <a href="/accounting/billing-code-list" class="block text-[8px] transition-all duration-200 {{ Request::is('accounting/billing-code-list') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest" style="padding-left: 68px !important; padding-top: 8px !important; padding-bottom: 8px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                    <i class="fa fa-circle-o mr-2 opacity-30" style="font-size: 6px;"></i> Billing Code
                                </a>
                                <a href="/accounting/gl-code-list" class="block text-[8px] transition-all duration-200 {{ Request::is('accounting/gl-code-list') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest" style="padding-left: 68px !important; padding-top: 8px !important; padding-bottom: 8px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                    <i class="fa fa-circle-o mr-2 opacity-30" style="font-size: 6px;"></i> G/L Code
                                </a>
                            </div>
                        </div>
                        
                        <!-- To Do List Link -->
                        <a href="/settings/todo-list" class="block text-[9px] transition-all duration-200 {{ Request::is('settings/todo-list*') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest border-b border-white/5 last:border-none" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                            <i class="fa fa-check-square-o mr-2" style="font-size: 8px;"></i> To Do List
                        </a>
                        
                        <!-- Container TP/SZ Link -->
                        <a href="/settings/container-types" class="block text-[9px] transition-all duration-200 {{ Request::is('settings/container-types*') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest border-b border-white/5 last:border-none" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                            <i class="fa fa-cube mr-2" style="font-size: 8px;"></i> Container TP/SZ
                        </a>
                        
                        <!-- Freight Default Values Link -->
                        <a href="/settings/freight-default-values" class="block text-[9px] transition-all duration-200 {{ Request::is('settings/freight-default-values*') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest border-b border-white/5 last:border-none" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                            <i class="fa fa-calculator mr-2" style="font-size: 8px;"></i> Freight Default Value
                        </a>
                        
                        <!-- User Management Link -->
                        <a href="/settings/user-management" class="block text-[9px] transition-all duration-200 {{ Request::is('settings/user-management*') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest border-b border-white/5 last:border-none" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                            <i class="fa fa-users mr-2" style="font-size: 8px;"></i> User Management
                        </a>
                        
                        <!-- Shipment Memo Auto-Load Link -->
                        <a href="/settings/shipment-memo-auto-load" class="block text-[9px] transition-all duration-200 {{ Request::is('settings/shipment-memo-auto-load*') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest border-b border-white/5 last:border-none" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                            <i class="fa fa-file-text-o mr-2" style="font-size: 8px;"></i> Shipment Memo Auto-Load
                        </a>
                        
                        <!-- AWB No. Management Link -->
                        <a href="/settings/awb-management" class="block text-[9px] transition-all duration-200 {{ Request::is('settings/awb-management*') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest border-b border-white/5 last:border-none" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                            <i class="fa fa-barcode mr-2" style="font-size: 8px;"></i> AWB No. Management
                        </a>
                        
                        <!-- HBL Templates Link -->
                        <a href="/settings/hbl-templates" class="block text-[9px] transition-all duration-200 {{ Request::is('settings/hbl-templates*') ? 'active-sub-link' : 'sub-link' }} uppercase tracking-widest border-b border-white/5 last:border-none" style="padding-left: 52px !important; padding-top: 10px !important; padding-bottom: 10px !important; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                            <i class="fa fa-file-text-o mr-2" style="font-size: 8px;"></i> HBL Templates
                        </a>
                        
                    </div>
                </div>

                <a href="/useful-links" class="sidebar-nav-item flex items-center transition-all duration-200 {{ request()->is('useful-links*') ? 'active-link' : 'nav-link' }}" style="text-decoration: none;" :class="isCollapsed ? 'justify-center px-0' : 'px-5'">
                    <div class="flex items-center" :class="isCollapsed ? 'justify-center w-full' : ''">
                        <i class="fa fa-link text-center text-[12px] opacity-70" :class="isCollapsed ? '!mr-0 !w-auto' : 'mr-3 w-5'"></i>
                        <span x-show="!isCollapsed" class="uppercase tracking-widest font-bold">Useful Links</span>
                    </div>
                </a>
                @endif

            </nav>
        </div>
    </div>
</aside>

<style>
.sidebar-nav-item {
    padding-left: 20px !important;
    padding-right: 20px !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}
.nav-link {
    color: #c3cbe4 !important;
}
.nav-link:hover {
    background-color: rgba(255,255,255,0.1) !important;
    color: #ffffff !important;
}
.active-link {
    background-color: rgba(255,255,255,0.15) !important;
    color: #ffffff !important;
    border-left: 2px solid #ffffff !important;
}
.sub-link {
    color: #a0a8c1 !important;
}
.sub-link:hover {
    color: #ffffff !important;
    background-color: rgba(255,255,255,0.05) !important;
}
.active-sub-link {
    color: #ffffff !important;
    font-weight: 900 !important;
    background-color: rgba(255,255,255,0.1) !important;
}
.custom-sidebar-scrollbar::-webkit-scrollbar {
    display: none;
}
.custom-sidebar-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
