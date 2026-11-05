<x-layout>
    <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 py-6 h-[calc(100vh-2rem)] flex flex-col">
        
        <!-- Header Section -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 shrink-0">
            <div>
                <nav class="flex items-center space-x-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                    <a href="/" class="hover:text-blue-600 transition-colors">Home</a>
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    <a href="/sales" class="hover:text-blue-600 transition-colors text-slate-400">Sales Control</a>
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    <span class="text-slate-800">Pipeline Board</span>
                </nav>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight uppercase">Opportunities Board</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <button class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-[10px] font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-2 tracking-widest uppercase">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Board Configuration
                </button>
                <a href="/sales/leads" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-blue-500/10 flex items-center gap-2 tracking-widest uppercase cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    Add Lead
                </a>
            </div>
        </div>

        <!-- Kanban Board Area -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden custom-scrollbar pb-4" id="kanban-board">
            <div class="flex h-full gap-5 min-w-max items-start">
                
                <!-- Stage 1: New inbound -->
                <div class="w-[320px] bg-slate-50 border border-slate-200 rounded-2xl flex flex-col h-full shadow-sm kanban-column" data-status="new">
                    <!-- Column Header -->
                    <div class="px-5 py-4 border-b border-slate-200 bg-white rounded-t-2xl flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-widest">Inbound Leads</h3>
                            <span class="bg-slate-100 text-slate-500 text-[9px] font-bold px-2 py-0.5 rounded-md">{{ count($pipeline['new'] ?? []) }}</span>
                        </div>
                    </div>
                    
                    <!-- Column Items Scrollable Area -->
                    <div class="p-4 overflow-y-auto flex-1 custom-scrollbar space-y-4 kanban-dropzone">
                        @foreach($pipeline['new'] ?? [] as $lead)
                        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-blue-300 transition-all cursor-grab active:cursor-grabbing group kanban-card relative overflow-hidden" draggable="true" data-id="{{ $lead->id }}">
                            <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                            
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-tight mb-1 group-hover:text-blue-600 transition-colors">{{ $lead->company_name }}</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Volume: {{ $lead->potential_volume ?? 'UNCALCULATED' }}</p>
                            
                            <div class="flex items-center justify-between border-t border-slate-50 pt-3">
                                <div>
                                    @if($lead->follow_up_date)
                                    <span class="text-[9px] font-bold px-2 py-1 rounded-md uppercase tracking-wider {{ $lead->follow_up_date < now() ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }}">
                                        Action: {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('M d') }}
                                    </span>
                                    @else
                                    <span class="text-[9px] font-bold px-2 py-1 bg-slate-100 text-slate-400 rounded-md uppercase tracking-wider border border-slate-200">No Action</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('sales.quotations.create', ['lead_id' => $lead->id]) }}" class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 rounded text-[9px] font-bold uppercase tracking-wider flex items-center gap-1" title="Create Freight Quotation">
                                        <i class="fa fa-file-text-o"></i> Quote
                                    </a>
                                    <div class="w-6 h-6 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-[9px] font-bold text-blue-700">
                                        {{ strtoupper(substr($lead->contact_person, 0, 2)) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Stage 2: Qualified -->
                <div class="w-[320px] bg-slate-50 border border-slate-200 rounded-2xl flex flex-col h-full shadow-sm kanban-column" data-status="qualified">
                    <div class="px-5 py-4 border-b border-slate-200 bg-white rounded-t-2xl flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-widest">Qualified</h3>
                            <span class="bg-blue-50 text-blue-600 text-[9px] font-bold px-2 py-0.5 rounded-md border border-blue-100">{{ count($pipeline['qualified'] ?? []) }}</span>
                        </div>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1 custom-scrollbar space-y-4 kanban-dropzone">
                        @foreach($pipeline['qualified'] ?? [] as $lead)
                        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all cursor-grab group kanban-card relative overflow-hidden" draggable="true" data-id="{{ $lead->id }}">
                            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                            
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-tight mb-1 group-hover:text-indigo-600 transition-colors">{{ $lead->company_name }}</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Req: {{ $lead->service_required }}</p>
                            
                            <div class="flex items-center justify-between border-t border-slate-50 pt-3">
                                <span class="text-[9px] font-bold px-2 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-md uppercase tracking-wider">Validating</span>
                                <a href="{{ route('sales.quotations.create', ['lead_id' => $lead->id]) }}" class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 rounded text-[9px] font-bold uppercase tracking-wider flex items-center gap-1" title="Create Freight Quotation">
                                    <i class="fa fa-file-text-o"></i> Quote
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Stage 3: Proposal Sent -->
                <div class="w-[320px] bg-slate-50 border border-slate-200 rounded-2xl flex flex-col h-full shadow-sm kanban-column" data-status="proposal_sent">
                    <div class="px-5 py-4 border-b border-slate-200 bg-white rounded-t-2xl flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-widest">Proposal Output</h3>
                            <span class="bg-purple-50 text-purple-600 text-[9px] font-bold px-2 py-0.5 rounded-md border border-purple-100">{{ count($pipeline['proposal_sent'] ?? []) }}</span>
                        </div>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1 custom-scrollbar space-y-4 kanban-dropzone">
                        @foreach($pipeline['proposal_sent'] ?? [] as $lead)
                        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-purple-300 transition-all cursor-grab group kanban-card relative overflow-hidden" draggable="true" data-id="{{ $lead->id }}">
                            <div class="absolute top-0 left-0 w-1 h-full bg-purple-500"></div>
                            
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-tight mb-1 group-hover:text-purple-600 transition-colors">{{ $lead->company_name }}</h4>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-3">Awaiting Decision</p>
                            
                            <div class="flex items-center justify-between border-t border-slate-50 pt-3">
                                <span class="text-[9px] font-bold px-2 py-1 bg-purple-50 text-purple-600 border border-purple-100 rounded-md uppercase tracking-wider">Negotiation Phase</span>
                                <a href="{{ route('sales.quotations.create', ['lead_id' => $lead->id]) }}" class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 rounded text-[9px] font-bold uppercase tracking-wider flex items-center gap-1" title="Create Freight Quotation">
                                    <i class="fa fa-file-text-o"></i> Quote
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Stage 4: Won / Closed -->
                <div class="w-[320px] bg-emerald-50/50 border border-emerald-200 rounded-2xl flex flex-col h-full shadow-sm kanban-column" data-status="won">
                    <div class="px-5 py-4 border-b border-emerald-200 bg-emerald-50 rounded-t-2xl flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[11px] font-bold text-emerald-800 uppercase tracking-widest">Closed / Won</h3>
                            <span class="bg-emerald-200 text-emerald-800 text-[9px] font-bold px-2 py-0.5 rounded-md">{{ count($pipeline['won'] ?? []) }}</span>
                        </div>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1 custom-scrollbar space-y-4 kanban-dropzone {{ count($pipeline['won'] ?? []) == 0 ? 'flex items-center justify-center' : '' }}">
                        @foreach($pipeline['won'] ?? [] as $lead)
                        <div class="bg-white border border-emerald-200 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-emerald-400 transition-all cursor-grab group kanban-card relative overflow-hidden" draggable="true" data-id="{{ $lead->id }}">
                            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                            
                            <h4 class="text-xs font-bold text-emerald-700 uppercase tracking-tight mb-1">{{ $lead->company_name }}</h4>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-3">Converted Entity</p>
                        </div>
                        @endforeach

                        @if(count($pipeline['won'] ?? []) == 0)
                        <div class="text-center p-6 border-2 border-dashed border-emerald-200 rounded-xl bg-white/50 w-full">
                            <svg class="w-10 h-10 text-emerald-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="block text-[10px] font-bold text-emerald-600 uppercase leading-relaxed tracking-widest">Drop Leads Here To Formally Convert</span>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Add script for basic drag and drop logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.kanban-card');
            const dropzones = document.querySelectorAll('.kanban-dropzone');
            
            cards.forEach(card => {
                card.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', card.dataset.id);
                    card.classList.add('opacity-50', 'scale-95');
                });
                
                card.addEventListener('dragend', () => {
                    card.classList.remove('opacity-50', 'scale-95');
                });
            });

            dropzones.forEach(zone => {
                zone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    zone.classList.add('bg-blue-50/50');
                });

                zone.addEventListener('dragleave', () => {
                    zone.classList.remove('bg-blue-50/50');
                });

                zone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    zone.classList.remove('bg-blue-50/50');
                    
                    const leadId = e.dataTransfer.getData('text/plain');
                    const card = document.querySelector(`.kanban-card[data-id="${leadId}"]`);
                    
                    if (card && zone !== card.parentElement) {
                        zone.appendChild(card);
                        const newStatus = zone.closest('.kanban-column').dataset.status;
                        
                        console.log(`Lead ${leadId} moved to ${newStatus}`);
                        fetch(`/sales/leads/${leadId}`, {
                            method: 'PUT',
                            body: JSON.stringify({ status: newStatus }),
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Failed to update status.');
                            }
                        })
                        .catch(err => console.error('Error:', err));
                    }
                });
            });
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    </style>
</x-layout>
