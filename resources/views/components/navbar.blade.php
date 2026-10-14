@props(['type' => 'admin'])
<div class="page-header navbar navbar-fixed-top bg-white border-b border-gray-200 h-[50px] flex items-center justify-between px-3 sm:px-4 shadow-sm relative z-[1001] !overflow-visible">
    <!-- Left: Sidebar Toggler, Branch Selection & Search -->
    <div class="flex items-center space-x-2">
        <button onclick="if(window.innerWidth > 768) { document.body.classList.toggle('sidebar-collapsed'); window.dispatchEvent(new CustomEvent('sidebar-toggled')); } else { document.body.classList.toggle('sidebar-mobile-open'); }" class="text-gray-500 hover:text-gray-700 focus:outline-none p-1.5 rounded hover:bg-gray-100 flex items-center justify-center">
            <i class="fa fa-bars text-base text-gray-500"></i>
        </button>

        @if($type === 'admin')
        <!-- Branch Selection (Left Side) -->
        <div class="relative hidden sm:block" x-data="{ open: false }">
            <div @click="open = !open" class="flex items-center space-x-1.5 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded cursor-pointer hover:bg-gray-100 transition-colors h-[32px]">
                <i class="fa fa-building text-gray-400 text-[11px]"></i>
                <span class="text-[10px] font-bold text-gray-600 uppercase tracking-tight">Main Branch</span>
                <i class="fa fa-angle-down text-gray-400 text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </div>
            
            <div x-show="open" 
                 @click.away="open = false" 
                 x-transition
                 class="absolute left-0 top-[calc(100%+8px)] w-[200px] bg-white border border-gray-200 shadow-xl rounded-md py-1 z-[9999]" 
                 x-cloak>
                <div class="px-4 py-2 border-b border-gray-50 bg-gray-50/50">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Assigned Branch</p>
                </div>
                <a href="#" class="flex items-center px-4 py-2 text-[10px] font-bold text-gray-700 bg-gray-100 border-l-2 border-gray-500">
                    <i class="fa fa-check-circle mr-2 text-gray-500"></i> MAIN BRANCH
                </a>
            </div>
        </div>

        <!-- Global Command Palette & Search Bar (Compact Fixed Width) - now on the left, next to Main Branch -->
       <div class="absolute left-1/2 -translate-x-1/2 flex items-center z-[1002]"
             x-data="{
                query: '',
                selectedModule: 'All',
                moduleOpen: false,
                openDropdown: false,
                loading: false,
                selectedIndex: 0,
                debounceTimer: null,
                modules: ['All', 'Ocean Export', 'Ocean Import', 'Air Export', 'Air Import', 'Trucking', 'Warehouse', 'Accounting', 'Sales', 'Trade Partners', 'Settings'],
                pages: [
                    { name: 'Dashboard', category: 'General', icon: 'fa-dashboard', url: '/dashboard', keywords: 'dashboard home main stats profit analytics' },
                    { name: 'Action Center', category: 'General', icon: 'fa-tasks', url: '/action-center', keywords: 'action center tasks actions pending' },
                    
                    { name: 'Ocean Import - Shipment List', category: 'Ocean Import', icon: 'fa-ship', url: '/ocean-import/list', keywords: 'ocean import list shipments oi' },
                    { name: 'Ocean Import - MBL List', category: 'Ocean Import', icon: 'fa-file-text-o', url: '/ocean-import/list/mbl', keywords: 'ocean import mbl list master bl' },
                    { name: 'Ocean Import - HBL List', category: 'Ocean Import', icon: 'fa-file-o', url: '/ocean-import/list/hbl', keywords: 'ocean import hbl list house bl' },
                    { name: 'Create Ocean Import Shipment', category: 'Ocean Import', icon: 'fa-plus-circle', url: '/ocean-import/create', keywords: 'create ocean import new add' },

                    { name: 'Ocean Export - Shipment List', category: 'Ocean Export', icon: 'fa-ship', url: '/ocean-export/list', keywords: 'ocean export list shipments oe' },
                    { name: 'Ocean Export - MBL List', category: 'Ocean Export', icon: 'fa-file-text-o', url: '/ocean-export/list/mbl', keywords: 'ocean export mbl list' },
                    { name: 'Ocean Export - HBL List', category: 'Ocean Export', icon: 'fa-file-o', url: '/ocean-export/list/hbl', keywords: 'ocean export hbl list' },
                    { name: 'Create Ocean Export Shipment', category: 'Ocean Export', icon: 'fa-plus-circle', url: '/ocean-export/create', keywords: 'create ocean export new add' },
                    { name: 'Ocean Export - Vessel Schedules', category: 'Ocean Export', icon: 'fa-calendar', url: '/ocean-export/vessel-schedules', keywords: 'vessel schedule list' },
                    { name: 'Ocean Export - Booking List', category: 'Ocean Export', icon: 'fa-bookmark', url: '/ocean-export/bookings', keywords: 'ocean export booking list' },

                    { name: 'Air Import - Shipment List', category: 'Air Import', icon: 'fa-plane', url: '/air-import/list', keywords: 'air import list shipments ai' },
                    { name: 'Air Import - HBL List', category: 'Air Import', icon: 'fa-file-o', url: '/air-import/list/hbl', keywords: 'air import hawb list' },
                    { name: 'Create Air Import Shipment', category: 'Air Import', icon: 'fa-plus-circle', url: '/air-import/create', keywords: 'create air import new add' },

                    { name: 'Air Export - Shipment List', category: 'Air Export', icon: 'fa-plane', url: '/air-export/list', keywords: 'air export list shipments ae' },
                    { name: 'Air Export - MBL List', category: 'Air Export', icon: 'fa-file-text-o', url: '/air-export/list/mbl', keywords: 'air export mawb list' },
                    { name: 'Create Air Export Shipment', category: 'Air Export', icon: 'fa-plus-circle', url: '/air-export/create', keywords: 'create air export new add' },

                    { name: 'Trucking - Shipment List', category: 'Trucking', icon: 'fa-truck', url: '/truck/list', keywords: 'trucking list shipments cartage drayage' },
                    { name: 'Trucking - My Shipment List', category: 'Trucking', icon: 'fa-list-alt', url: '/truck/my-shipment-list', keywords: 'truck my shipment list' },
                    { name: 'Create Truck Shipment', category: 'Trucking', icon: 'fa-plus-circle', url: '/truck/create', keywords: 'create truck shipment new add' },

                    { name: 'Warehouse - Receipts', category: 'Warehouse', icon: 'fa-cubes', url: '/warehouse/receipts', keywords: 'warehouse receipts wr list' },
                    { name: 'Warehouse - Receiving List', category: 'Warehouse', icon: 'fa-arrow-circle-down', url: '/warehouse/receiving', keywords: 'warehouse receiving list' },
                    { name: 'Warehouse - Shipping List', category: 'Warehouse', icon: 'fa-arrow-circle-up', url: '/warehouse/shipping', keywords: 'warehouse shipping list' },
                    { name: 'Warehouse - Inventory Items', category: 'Warehouse', icon: 'fa-boxes', url: '/warehouse/items', keywords: 'inventory items warehouse stock' },
                    { name: 'Warehouse - Automobile Management', category: 'Warehouse', icon: 'fa-car', url: '/warehouse/automobile', keywords: 'automobile cars vehicles' },

                    { name: 'Accounting - Bank List', category: 'Accounting', icon: 'fa-university', url: '/accounting/bank-list', keywords: 'bank list banks check sequence cycle' },
                    { name: 'Accounting - Billing Code List', category: 'Accounting', icon: 'fa-code', url: '/accounting/billing-code-list', keywords: 'billing code list iata data mapping' },
                    { name: 'Accounting - G/L Code List', category: 'Accounting', icon: 'fa-book', url: '/accounting/gl-code-list', keywords: 'gl code list general ledger' },
                    { name: 'Accounting - Currency Table', category: 'Accounting', icon: 'fa-money', url: '/accounting/currency-table', keywords: 'currency table exchange rates fx' },
                    { name: 'Accounting - Invoice List', category: 'Accounting', icon: 'fa-file-text-o', url: '/accounting/invoices', keywords: 'invoice list invoices ar ap' },
                    { name: 'Accounting - Create Invoice', category: 'Accounting', icon: 'fa-plus', url: '/accounting/invoices/create', keywords: 'create invoice new invoice' },
                    { name: 'Accounting - G&A Expense List', category: 'Accounting', icon: 'fa-calculator', url: '/accounting/ga-expenses', keywords: 'ga expense list expenses' },
                    { name: 'Accounting - Create G&A Expense', category: 'Accounting', icon: 'fa-plus', url: '/accounting/ga-expenses/create', keywords: 'create ga expense new' },
                    { name: 'Accounting - Make Payment (AP)', category: 'Accounting', icon: 'fa-credit-card', url: '/accounting/payment-make', keywords: 'make payment vendor pay' },
                    { name: 'Accounting - Receive Payment (AR)', category: 'Accounting', icon: 'fa-download', url: '/accounting/payment-receive', keywords: 'receive payment customer pay' },
                    { name: 'Accounting - Payment Made List', category: 'Accounting', icon: 'fa-list', url: '/accounting/payment-made-list', keywords: 'payment made list vendor' },
                    { name: 'Accounting - Payment Received List', category: 'Accounting', icon: 'fa-list', url: '/accounting/payment-received-list', keywords: 'payment received list customer' },
                    { name: 'Accounting - Aging Report', category: 'Accounting', icon: 'fa-clock-o', url: '/accounting/aging-report', keywords: 'aging report ar ap aging' },
                    { name: 'Accounting - Agent Local Statement', category: 'Accounting', icon: 'fa-file-pdf-o', url: '/accounting/agent-local-statement', keywords: 'agent statement local statement soa' },
                    { name: 'Accounting - Clear Check By Excel', category: 'Accounting', icon: 'fa-file-excel-o', url: '/accounting/bank-clear-check-by-excel', keywords: 'clear check excel check cycle' },

                    { name: 'Sales - Quotation List', category: 'Sales', icon: 'fa-file-text', url: '/sales/quotations', keywords: 'sales quotation list quotes' },
                    { name: 'Sales - Create Quotation', category: 'Sales', icon: 'fa-plus', url: '/sales/quotations/create', keywords: 'create quotation new quote' },

                    { name: 'Customers - List', category: 'Customers', icon: 'fa-users', url: '/trade-partner/list', keywords: 'customers trade partners vendor shipper consignee trucker' },
                    { name: 'Customers - Create', category: 'Customers', icon: 'fa-user-plus', url: '/trade-partner/create', keywords: 'create customer new vendor trade partner' },
                    { name: 'Customers - Mapping List', category: 'Customers', icon: 'fa-exchange', url: '/trade-partner/mapping-list', keywords: 'customer mapping' },
                    { name: 'Customers - Credit Entry', category: 'Customers', icon: 'fa-shield', url: '/trade-partner/credit-entry', keywords: 'credit entry customer' },

                    { name: 'Reports - Profit Loss Report', category: 'Reports', icon: 'fa-line-chart', url: '/reports/profit-loss', keywords: 'profit loss report p&l' },
                    { name: 'Reports - Customer Intelligence', category: 'Reports', icon: 'fa-pie-chart', url: '/customers', keywords: 'customer intelligence report analytics' },
                    { name: 'Useful Links', category: 'Reports', icon: 'fa-link', url: '/useful-links', keywords: 'useful links tracking' },

                    { name: 'Settings - Freight Default Values', category: 'Settings', icon: 'fa-cogs', url: '/settings/freight-default-values', keywords: 'freight default values settings' },
                    { name: 'Settings - Shipment Memo Auto Load', category: 'Settings', icon: 'fa-sticky-note-o', url: '/settings/shipment-memo-auto-load', keywords: 'shipment memo auto load' },
                    { name: 'Settings - User Management', category: 'Settings', icon: 'fa-user-circle-o', url: '/settings/user-management', keywords: 'user management users operators roles' },
                    { name: 'Settings - AWB Management', category: 'Settings', icon: 'fa-plane', url: '/settings/awb-management', keywords: 'awb management awb stock' },
                    { name: 'Settings - Todo List Config', category: 'Settings', icon: 'fa-check-square-o', url: '/settings/todo-list', keywords: 'todo list config task settings' }
                ],
                dbResults: [],
                get filteredPages() {
                    if (!this.query.trim()) return [];
                    const q = this.query.toLowerCase().trim();
                    return this.pages.filter(p => {
                        if (this.selectedModule !== 'All' && p.category !== this.selectedModule) return false;
                        return p.name.toLowerCase().includes(q) || p.category.toLowerCase().includes(q) || p.keywords.toLowerCase().includes(q);
                    }).slice(0, 6);
                },
                get allResults() {
                    const pageItems = this.filteredPages.map(p => ({ ...p, isPage: true }));
                    const dbItems = this.dbResults.map(d => ({ ...d, isPage: false }));
                    return [...pageItems, ...dbItems];
                },
                searchDb() {
                    if (!this.query.trim()) {
                        this.dbResults = [];
                        return;
                    }
                    this.loading = true;
                    fetch(`/api/global-search?q=${encodeURIComponent(this.query)}&module=${encodeURIComponent(this.selectedModule)}`)
                        .then(res => res.json())
                        .then(data => {
                            this.dbResults = data.records || [];
                            this.loading = false;
                        })
                        .catch(() => {
                            this.dbResults = [];
                            this.loading = false;
                        });
                },
                onInput() {
                    this.openDropdown = true;
                    this.selectedIndex = 0;
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.searchDb();
                    }, 200);
                },
                navigate(url) {
                    if (!url) return;
                    this.openDropdown = false;
                    window.location.href = url;
                },
                onKeyDown(e) {
                    if (!this.openDropdown) return;
                    const total = this.allResults.length;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex + 1) % (total || 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex - 1 + total) % (total || 1);
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.allResults[this.selectedIndex]) {
                            this.navigate(this.allResults[this.selectedIndex].url);
                        }
                    } else if (e.key === 'Escape') {
                        this.openDropdown = false;
                    }
                }
             }"
             @click.away="openDropdown = false; moduleOpen = false">

            <div class="flex items-center bg-gray-50 border border-gray-300 rounded-lg px-3 h-[40px] focus-within:ring-2 focus-within:ring-gray-300 focus-within:border-gray-400 transition-all relative w-[500px] sm:w-[420px] lg:w-[560px] max-w-[45vw]">
                
                <!-- Module Dropdown Selector -->
                <div class="relative flex items-center h-full border-r border-gray-300 pr-2 mr-2 cursor-pointer select-none flex-shrink-0" @click.stop="moduleOpen = !moduleOpen">
                    <span class="text-[13px] font-bold text-gray-600 whitespace-nowrap" x-text="selectedModule"></span>
                    <i class="fa fa-angle-down ml-1.5 text-gray-400 text-[10px] transition-transform duration-200" :class="moduleOpen ? 'rotate-180' : ''"></i>
                    
                    <!-- Module Select Options -->
                    <div x-show="moduleOpen" x-cloak x-transition
                         class="absolute left-0 top-[calc(100%+88px)] w-[160px] max-h-[240px] overflow-y-auto bg-white border border-gray-200 shadow-xl rounded-md py-1 z-[10000]" style = "top:10%; ">
                        <template x-for="mod in modules" :key="mod">
                            <div @click.stop="selectedModule = mod; moduleOpen = false; searchDb()"
                                 class="px-3 py-1.5 text-[13px] font-semibold text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer flex items-center justify-between"
                                 :class="selectedModule === mod ? 'bg-gray-100 text-gray-900 font-bold' : ''">
                                <span x-text="mod"></span>
                                <i class="fa fa-check text-[10px] text-gray-500" x-show="selectedModule === mod"></i>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Search Input -->
                <input type="text" 
                       x-model="query"
                       @input="onInput()"
                       @focus="openDropdown = true"
                       @keydown="onKeyDown($event)"
                       class="bg-transparent border-none text-[13px] w-full focus:outline-none focus:ring-0 placeholder-gray-400 font-medium text-gray-700 min-w-[80px] p-0" 
                       placeholder="Search views, file #, MBL...">

                <!-- Loading / Clear / Search Icon -->
                <div class="flex items-center space-x-1 flex-shrink-0 ml-1">
                    <i x-show="loading" class="fa fa-circle-o-notch fa-spin text-gray-500 text-[13px]" x-cloak></i>
                    <button type="button" x-show="query" @click="query = ''; dbResults = []; openDropdown = false" class="text-gray-400 hover:text-gray-600 flex items-center justify-center p-0.5" x-cloak>
                        <i class="fa fa-times text-[10px] text-gray-400"></i>
                    </button>
                    <button type="button" @click="openDropdown = true; searchDb()" class="text-gray-400 hover:text-gray-600 flex items-center justify-center p-0.5">
                        <i class="fa fa-search text-[13px] text-gray-400"></i>
                    </button>
                </div>
            </div>

            <!-- Autocomplete Results Dropdown (aligned to the LEFT edge of the search bar) -->
            <div x-show="openDropdown && query.trim().length > 0" 
                 x-cloak 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="absolute left-0 top-[calc(100%+12px)] w-[90vw] sm:w-[460px] max-w-[90vw] bg-white border border-gray-200 shadow-2xl rounded-lg py-2 z-[10000] max-h-[60vh] overflow-y-auto" style = "top:250%; overflow-y: auto; max-height: 60vh; width: 460px; left: 0; right: auto;">
                
                <!-- Category 1: Views & Navigation Pages -->
                <div x-show="filteredPages.length > 0">
                    <div class="px-3 py-1 bg-gray-50 border-y border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider"><i class="fa fa-compass text-gray-400 mr-1"></i> Navigation Views & Pages</span>
                        <span class="text-[9px] font-semibold text-gray-400" x-text="filteredPages.length + ' page(s)'"></span>
                    </div>
                    <template x-for="(page, idx) in filteredPages" :key="page.url">
                        <div @click="navigate(page.url)"
                             @mouseenter="selectedIndex = idx"
                             class="px-3 py-2 flex items-center justify-between cursor-pointer border-b border-gray-50 transition-colors"
                             :class="selectedIndex === idx ? 'bg-gray-100/80 border-l-4 border-l-gray-600 pl-2.5' : 'hover:bg-gray-50'">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded bg-gray-100 text-gray-500 flex items-center justify-center flex-shrink-0">
                                    <i class="text-[12px] text-gray-500" :class="['fa', page.icon || 'fa-folder-o']"></i>
                                </div>
                                <div>
                                    <div class="text-[12px] font-bold text-gray-800" x-text="page.name"></div>
                                    <div class="text-[10px] font-semibold text-gray-400" x-text="page.category"></div>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded flex-shrink-0 ml-2">Go to View &rarr;</span>
                        </div>
                    </template>
                </div>

                <!-- Category 2: Database Records & Shipments -->
                <div x-show="dbResults.length > 0">
                    <div class="px-3 py-1 bg-gray-50 border-y border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider"><i class="fa fa-database text-gray-400 mr-1"></i> Database Records & Shipments</span>
                        <span class="text-[9px] font-semibold text-gray-400" x-text="dbResults.length + ' record(s)'"></span>
                    </div>
                    <template x-for="(record, idx) in dbResults" :key="record.url + idx">
                        <div @click="navigate(record.url)"
                             @mouseenter="selectedIndex = filteredPages.length + idx"
                             class="px-3 py-2 flex items-center justify-between cursor-pointer border-b border-gray-50 transition-colors"
                             :class="selectedIndex === (filteredPages.length + idx) ? 'bg-gray-100/80 border-l-4 border-l-gray-600 pl-2.5' : 'hover:bg-gray-50'">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                <div class="w-7 h-7 rounded bg-gray-100 text-gray-600 flex items-center justify-center flex-shrink-0">
                                    <i class="text-[12px] text-gray-600" :class="['fa', record.icon || 'fa-file-text-o']"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[12px] font-bold text-gray-900" x-text="record.title"></span>
                                        <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-gray-200 text-gray-700" x-text="record.category"></span>
                                    </div>
                                    <div class="text-[10px] font-medium text-gray-500 truncate" x-text="record.subtitle"></div>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded flex-shrink-0 ml-2">Open &rarr;</span>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && filteredPages.length === 0 && dbResults.length === 0" class="px-4 py-6 text-center text-gray-400">
                    <i class="fa fa-search text-2xl mb-2 text-gray-400 block"></i>
                    <span class="text-[12px] font-medium">No views or records found matching "<span x-text="query" class="font-bold text-gray-600"></span>"</span>
                </div>

                <!-- Footer tip -->
                <div class="px-3 py-1.5 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
                    <span>Press <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded font-mono text-[9px]">↑</kbd> <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded font-mono text-[9px]">↓</kbd> to navigate, <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded font-mono text-[9px]">Enter</kbd> to select</span>
                    <span><kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded font-mono text-[9px]">Esc</kbd> to close</span>
                </div>
            </div>
        </div>
        @elseif($type === 'superadmin')
            <span style="font-size: 13px; font-weight: 600; color: #405189; letter-spacing: 0.5px; text-transform: uppercase; margin-left: 12px;">Super Admin Panel</span>
        @elseif($type === 'customer')
            <span style="font-size: 13px; font-weight: 600; color: #405189; letter-spacing: 0.5px; text-transform: uppercase; margin-left: 12px;">Customer Portal</span>
        @endif
        
    </div>

    <!-- Right: Menus -->
    <div class="flex items-center space-x-2 sm:space-x-3 flex-1 justify-end">
        <div class="flex items-center space-x-1">
            <button class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors relative">
                <i class="fa fa-list-ul text-[13px]"></i>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-gray-400 rounded-full border-2 border-white"></span>
            </button>

            <button class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                <i class="fa fa-bell-o text-[13px]"></i>
            </button>
        </div>

        <!-- User Profile -->
        <div class="relative flex items-center pl-3 border-l border-gray-200 cursor-pointer group !overflow-visible" x-data="{ open: false }">
            <div @click="open = !open" class="flex items-center space-x-2">
                <div class="relative flex items-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=6B7280&background=F3F4F6" alt="Avatar" class="w-7 h-7 rounded-full border border-gray-200">
                </div>
                <span class="hidden md:inline text-[11px] font-bold text-gray-600 group-hover:text-gray-900 transition-colors uppercase">{{ Auth::user()->name }}</span>
                <i class="fa fa-angle-down text-gray-400 text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
            </div>

            <!-- Premium Dropdown Menu -->
            <div x-show="open" 
                 @click.away="open = false" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="absolute right-0 top-[calc(100%+12px)] w-[200px] bg-white border border-gray-200 shadow-xl rounded-md py-1 z-[9999]" 
                 x-cloak>
                <div class="px-4 py-2.5 border-b border-gray-50 bg-gray-50/50">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Operator Account</p>
                    <p class="text-[11px] font-bold text-gray-800 truncate mt-0.5">{{ Auth::user()->email }}</p>
                </div>
                
                <div class="py-1">
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-[11px] font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                        <i class="fa fa-user-circle-o w-4 text-center mr-2.5 text-gray-400"></i> PROFILE SETTINGS
                    </a>
                </div>

                <div class="border-t border-gray-100 mt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full text-left px-4 py-2.5 text-[11px] font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            <i class="fa fa-power-off w-4 text-center mr-2.5 text-gray-400"></i> LOGOUT SYSTEM
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>