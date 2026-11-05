<x-layout>
    <div class="max-w-7xl mx-auto px-1 sm:px-6 lg:px-8" x-data="genericModule('{{ $api_endpoint }}')">
        
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest mb-1">
                    <a href="/" class="hover:text-blue-500 transition-colors">Home</a> / <span class="text-gray-600">{{ $title }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-[#1a2138] tracking-tight">{{ $title }} List</h1>
            </div>
            
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <button @click="isCreating = true" class="bg-[#1a2138] hover:bg-slate-800 text-white text-xs font-bold px-5 py-2.5 rounded shadow-sm transition-all flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    CREATE NEW
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white shadow-sm rounded border border-gray-100 overflow-hidden">
            <template x-if="loading">
                <div class="p-20 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase">Fetching Live Data...</p>
                </div>
            </template>

            <template x-if="!loading && items.length === 0">
                <div class="p-20 text-center bg-gray-50/30">
                    <svg class="mx-auto h-12 w-12 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                    <p class="mt-4 text-xs font-bold text-gray-400 tracking-widest uppercase">No records found in this module yet</p>
                </div>
            </template>

            <div class="overflow-x-auto" x-show="!loading && items.length > 0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#f8f9fa]">
                        <tr>
                            <template x-for="header in headers" :key="header">
                                <th class="px-5 py-3 text-left text-[0.6rem] font-black text-gray-500 uppercase tracking-tighter" x-text="header.replace('_', ' ')"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-blue-50/30 transition-colors cursor-pointer">
                                <template x-for="header in headers" :key="header">
                                    <td class="px-5 py-3 text-[0.7rem] font-medium text-gray-600" x-text="typeof item[header] === 'object' && item[header] !== null ? (item[header].name || item[header].code || item[header].invoice_no || item[header].title || '-') : (item[header] ?? '-')"></td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Generic Create Modal -->
        <div x-show="isCreating" style="display:none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isCreating" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="isCreating" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create New Record</h3>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">Module specific form fields will be implemented here.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="isCreating = false" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                        <button type="button" @click="isCreating = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function genericModule(endpoint) {
            return {
                items: [],
                loading: true,
                isCreating: false,
                headers: [],
                init() {
                    fetch(endpoint)
                        .then(res => {
                            if(!res.ok) throw new Error('API Error');
                            return res.json();
                        })
                        .then(data => {
                            this.items = Array.isArray(data) ? data : (data.data || []);
                            if (this.items.length > 0) {
                                this.headers = Object.keys(this.items[0]).filter(k => 
                                    !['created_at', 'updated_at', 'deleted_at', 'id', 'user_id', 'client_id'].includes(k)
                                );
                            }
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.loading = false;
                        });
                }
            }
        }
    </script>
</x-layout>
