<x-layout title="Edit Automobile">
    @push('styles')
    <x-form-styles />
    @endpush

    <div class="page-content" x-data="automobileForm()" x-cloak>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Warehouse <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Edit Automobile</span></li>
            </ul>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h1 class="caption-subject" style="font-size: 18px;">Edit Automobile</h1>
            <div style="display: flex; gap: 8px;">
                <button type="submit" form="automobile-form" class="btn-freightx"><i class="fa fa-save"></i> SAVE</button>
                <a href="{{ route('warehouse.automobile.index') }}" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <ul class="gf-tabs">
            <li :class="{ 'active': activeTab === 'basic' }">
                <a @click="activeTab = 'basic'">Basic</a>
            </li>
            <li :class="{ 'active': activeTab === 'gallery' }">
                <a @click="activeTab = 'gallery'">Gallery</a>
            </li>
        </ul>

        <form action="{{ route('warehouse.automobile.update', $warehouseAutomobile) }}" method="POST" id="automobile-form">
            @csrf
            @method('PUT')

            @if(session('success'))
                <div class="alert alert-success" style="background:#e8f5e9;border:1px solid #66bb6a;color:#2e7d32;padding:10px 15px;border-radius:4px;margin-bottom:15px;">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:15px;">
                    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:15px;">
                    <strong>Validation Error</strong>
                    <ul style="margin:5px 0 0 15px;padding:0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div x-show="activeTab === 'basic'">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption-subject">Automobile</div>
                        <div>
                            <button type="button" class="btn-default-gf"><i class="fa fa-cog"></i> Docs <i class="fa fa-angle-down"></i></button>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-4">
                            <!-- Column 1 -->
                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf"><span class="text-danger">*</span> Vin No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="vin_no" x-model="form.vin_no" class="form-control-gf" required>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Tag No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="tag_no" x-model="form.tag_no" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Customer</label>
                                    <div class="form-input-container">
                                        <select name="customer_id" x-model="form.customer_id" class="form-control-gf">
                                            <option value="">Select...</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="section-card" style="margin-top: 15px;">
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">Received Date</label>
                                        <div class="form-input-container">
                                            <input type="date" name="received_date" x-model="form.received_date" class="form-control-gf">
                                        </div>
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">Received By</label>
                                        <div class="form-input-container">
                                            <select name="received_by" x-model="form.received_by" class="form-control-gf">
                                                <option value="">Select...</option>
                                                @foreach($users as $u)
                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 2 -->
                            <div class="section-card">
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Maker</label>
                                    <div class="form-input-container">
                                        <input type="text" name="maker" x-model="form.maker" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Year</label>
                                    <div class="form-input-container">
                                        <input type="text" name="year" x-model="form.year" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Model</label>
                                    <div class="form-input-container">
                                        <input type="text" name="model" x-model="form.model" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Color</label>
                                    <div class="form-input-container">
                                        <input type="text" name="color" x-model="form.color" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Engine No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="engine_no" x-model="form.engine_no" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Manufacture Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="manufacture_date" x-model="form.manufacture_date" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Title Received</label>
                                    <div class="form-input-container">
                                        <input type="hidden" name="title_received" value="0">
                                        <input type="checkbox" name="title_received" x-model="form.title_received" value="1">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Office</label>
                                    <div class="form-input-container">
                                        <select name="office_id" x-model="form.office_id" class="form-control-gf">
                                            <option value="">Select...</option>
                                            @foreach($offices as $o)
                                                <option value="{{ $o->id }}">{{ $o->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Vehicle State</label>
                                    <div class="form-input-container">
                                        <input type="text" name="vehicle_state" x-model="form.vehicle_state" class="form-control-gf">
                                    </div>
                                </div>
                            </div>

                            <!-- Column 3 -->
                            <div class="section-card">
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Condition</label>
                                    <div class="form-input-container">
                                        <input type="text" name="condition" x-model="form.condition" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Key</label>
                                    <div class="form-input-container">
                                        <input type="text" name="key_number" x-model="form.key_number" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Fuel</label>
                                    <div class="form-input-container">
                                        <input type="text" name="fuel" x-model="form.fuel" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Tire Size (Front)</label>
                                    <div class="form-input-container">
                                        <input type="text" name="tire_size_front" x-model="form.tire_size_front" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Tire Size (Rear)</label>
                                    <div class="form-input-container">
                                        <input type="text" name="tire_size_rear" x-model="form.tire_size_rear" class="form-control-gf">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Mileage</label>
                                    <div class="form-input-container">
                                        <input type="text" name="mileage" x-model="form.mileage" class="form-control-gf">
                                    </div>
                                </div>
                            </div>

                            <!-- Column 4 (Radio Toggles) -->
                            <div class="section-card" style="display:flex; flex-direction:column; gap:6px;">
                                @php
                                    $features = [
                                        'w_sticker' => 'W.STICKER',
                                        'remote_control' => 'Remote Control',
                                        'headphone' => 'Headphone',
                                        'owners_manual' => 'Owner\'s Manual',
                                        'cd_player' => 'CD Player',
                                        'cd_changer' => 'CD Changer',
                                        'first_aid_kit' => 'First Aid Kit',
                                        'floor_mat' => 'Floor Mat',
                                        'cigarette_lighter' => 'Cigarette Lighter',
                                        'cargo_net' => 'Cargo Net',
                                        'ashtray' => 'Ashtray',
                                        'Tools' => 'Tools',
                                        'spare_tire' => 'Spare Tire',
                                        'sun_roof' => 'Sun Roof',
                                    ];
                                @endphp
                                @foreach($features as $key => $label)
                                    <div class="form-group-gf" style="margin-bottom:0;">
                                        <label class="form-label-gf" style="width:110px;">{{ $label }}</label>
                                        <div class="form-input-container radio-group">
                                            <label class="radio-label">
                                                <input type="radio" name="{{ $key }}" value="1" x-model="form.{{ $key }}"> YES
                                            </label>
                                            <label class="radio-label">
                                                <input type="radio" name="{{ $key }}" value="0" x-model="form.{{ $key }}"> No
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="section-card" style="grid-column: 1 / -1; margin-top: 10px;">
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Internal Remark</label>
                                    <div class="form-input-container">
                                        <textarea name="internal_remark" x-model="form.internal_remark" class="form-control-gf" rows="3" style="resize:vertical;"></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery Tab -->
            <div x-show="activeTab === 'gallery'" style="display: none;">
                <div class="portlet light">
                    <div class="gallery-header">
                        Automobile Gallery
                    </div>
                    <div class="gallery-toolbar">
                        <button type="button" class="btn-freightx" @click="uploadModalOpen = true" style="border-radius:2px; padding: 4px 10px; font-size:11px; background:#10b981;">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button type="button" class="btn-default-gf" @click="deleteSelected()" :disabled="selectedTools.length === 0">
                            <i class="fa fa-trash"></i>
                        </button>
                        <select class="form-control-gf" x-model="purposeToSet" style="width: 120px; height:24px;">
                            <option value="">Set Purpose</option>
                            <option value="Receiving">Receiving</option>
                            <option value="Loading">Loading</option>
                            <option value="Others">Others</option>
                        </select>
                        <button type="button" class="btn-freightx" @click="applyPurpose()" style="border-radius:2px; padding: 4px 10px; font-size:11px; background:#3b82f6;">Apply</button>
                        
                        <div class="gallery-filters">
                            <template x-for="f in ['All Photo', 'Receiving', 'Loading', 'Others']">
                                <button type="button" class="gallery-filter-btn" 
                                        :style="filterType === f ? 'background:#f1f5f9; font-weight:700;' : ''"
                                        @click="filterType = f" x-text="f"></button>
                            </template>
                            <a href="{{ route('warehouse.automobile.documents.download', $warehouseAutomobile) }}" class="btn-freightx" style="margin-left: 10px; border-radius:2px; padding: 4px 10px; font-size:11px; background:#14b8a6; text-decoration:none;">Download All</a>
                        </div>
                    </div>
                    <div class="portlet-body" style="padding:0;">
                        <template x-for="(Tools, purpose) in ToolsByPurpose" :key="purpose">
                            <div x-show="Tools.length > 0">
                                <div class="gallery-row" x-text="purpose"></div>
                                <div style="display:flex; flex-wrap:wrap; gap:10px; padding: 10px 15px;">
                                    <template x-for="doc in Tools" :key="doc.id">
                                        <div style="width: 120px; text-align:center; position:relative; border:1px solid #e2e8f0; padding:4px; border-radius:4px; cursor:pointer; transition:all 0.2s;" 
                                             :style="(selectedTools.includes(doc.id) || selectedTools.includes(doc.id.toString())) ? 'border-color:#3b82f6; background:#eff6ff; box-shadow: 0 0 0 2px #3b82f6;' : ''"
                                             @click="toggleSelection(doc.id)">
                                            <input type="checkbox" style="position:absolute; top:8px; left:8px; z-index:10; cursor:pointer; width:16px; height:16px;" 
                                                   :checked="selectedTools.includes(doc.id) || selectedTools.includes(doc.id.toString())" 
                                                   @click.stop="toggleSelection(doc.id)">
                                            
                                            <a :href="doc.download_url" @click.stop style="position:absolute; top:8px; right:8px; background:rgba(255,255,255,0.9); border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; color:#3b82f6; text-decoration:none; z-index:10; box-shadow:0 1px 3px rgba(0,0,0,0.2);">
                                                <i class="fa fa-eye" style="font-size:11px;"></i>
                                            </a>

                                            <img :src="doc.download_url" style="width:100%; height:80px; object-fit:cover; border-radius:2px;" onerror="this.src='https://via.placeholder.com/120x80?text=File'">
                                            
                                            <div style="font-size:9px; color:#475569; margin-top:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" x-text="doc.file_name"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredDocuments.length === 0" style="padding: 20px; text-align:center; color:#94a3b8; font-size:12px;">
                            No pictures found. Click the + button to upload.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Modal -->
            <div x-show="uploadModalOpen" style="display:none;">
                <div class="overlay open" @click.self="uploadModalOpen = false" style="position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:9990; display:flex; align-items:center; justify-content:center;">
                    <div class="modal-box" style="background:#fff; border-radius:6px; box-shadow:0 20px 50px rgba(0,0,0,0.2); width:500px; max-width:90vw;">
                        <div class="modal-header" style="background:#f8fafc; padding:10px 14px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                            <div style="font-size:12px; font-weight:700; color:#1e293b;">Upload Pictures</div>
                            <button type="button" @click="uploadModalOpen = false" style="background:none; border:none; cursor:pointer; color:#94a3b8;"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="modal-body" style="padding:14px;">
                            <div style="font-size:11px; font-weight:600; color:#475569; margin-bottom:6px;">SELECT TYPE & UPLOAD FILE</div>
                            <ul class="gf-tabs" style="margin-bottom:10px;">
                                <template x-for="t in ['Receiving', 'Loading', 'Others']">
                                    <li :class="{ 'active': uploadType === t }">
                                        <a @click="uploadType = t" x-text="t" style="padding:6px 16px;"></a>
                                    </li>
                                </template>
                            </ul>
                            
                            <div style="border:2px dashed #cbd5e1; border-radius:4px; padding:30px; text-align:center; background:#f8fafc;"
                                 @dragover.prevent="$el.style.borderColor='#3b82f6'" 
                                 @dragleave.prevent="$el.style.borderColor='#cbd5e1'" 
                                 @drop.prevent="$el.style.borderColor='#cbd5e1'; handleFiles($event.dataTransfer.files)">
                                <i class="fa fa-cloud-upload" style="font-size:32px; color:#94a3b8; margin-bottom:10px;"></i>
                                <div style="font-size:12px; color:#475569; margin-bottom:10px;">Drag and drop file(s) here...</div>
                                <input type="file" id="file-upload" multiple style="display:none" @change="handleFiles($event.target.files)">
                                <button type="button" class="btn-default-gf" @click="document.getElementById('file-upload').click()">Choose Files</button>
                            </div>

                            <div x-show="isUploading" style="margin-top:15px; font-size:11px; color:#3b82f6; text-align:center;">
                                <i class="fa fa-spinner fa-spin"></i> Uploading...
                            </div>
                        </div>
                        <div style="padding:10px 14px; background:#f8fafc; border-top:1px solid #e2e8f0; text-align:right;">
                            <button type="button" class="btn-freightx" @click="uploadModalOpen = false">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn-freightx" style="min-width: 120px; justify-content: center;" :disabled="isSaving">
                    <span x-text="isSaving ? 'Saving...' : 'Save'"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('automobileForm', () => ({
                activeTab: 'basic',
                isSaving: false,
                form: {
                    vin_no: '{{ old("vin_no", $warehouseAutomobile->vin_no) }}',
                    tag_no: '{{ old("tag_no", $warehouseAutomobile->tag_no) }}',
                    customer_id: '{{ old("customer_id", $warehouseAutomobile->customer_id) }}',
                    received_date: '{{ old("received_date", $warehouseAutomobile->received_date ? $warehouseAutomobile->received_date->format("Y-m-d") : "") }}',
                    received_by: '{{ old("received_by", $warehouseAutomobile->received_by) }}',
                    maker: '{{ old("maker", $warehouseAutomobile->maker) }}',
                    year: '{{ old("year", $warehouseAutomobile->year) }}',
                    model: '{{ old("model", $warehouseAutomobile->model) }}',
                    color: '{{ old("color", $warehouseAutomobile->color) }}',
                    engine_no: '{{ old("engine_no", $warehouseAutomobile->engine_no) }}',
                    manufacture_date: '{{ old("manufacture_date", $warehouseAutomobile->manufacture_date ? $warehouseAutomobile->manufacture_date->format("Y-m-d") : "") }}',
                    title_received: {{ old("title_received", $warehouseAutomobile->title_received) ? 'true' : 'false' }},
                    office_id: '{{ old("office_id", $warehouseAutomobile->office_id) }}',
                    condition: '{{ old("condition", $warehouseAutomobile->condition) }}',
                    key_number: '{{ old("key_number", $warehouseAutomobile->key_number) }}',
                    fuel: '{{ old("fuel", $warehouseAutomobile->fuel) }}',
                    tire_size_front: '{{ old("tire_size_front", $warehouseAutomobile->tire_size_front) }}',
                    tire_size_rear: '{{ old("tire_size_rear", $warehouseAutomobile->tire_size_rear) }}',
                    mileage: '{{ old("mileage", $warehouseAutomobile->mileage) }}',
                    vehicle_state: '{{ old("vehicle_state", $warehouseAutomobile->vehicle_state) }}',
                    internal_remark: '{{ old("internal_remark", $warehouseAutomobile->internal_remark) }}',
                    w_sticker: '{{ old("w_sticker", $warehouseAutomobile->w_sticker ?? "0") }}',
                    remote_control: '{{ old("remote_control", $warehouseAutomobile->remote_control ?? "0") }}',
                    headphone: '{{ old("headphone", $warehouseAutomobile->headphone ?? "0") }}',
                    owners_manual: '{{ old("owners_manual", $warehouseAutomobile->owners_manual ?? "0") }}',
                    cd_player: '{{ old("cd_player", $warehouseAutomobile->cd_player ?? "0") }}',
                    cd_changer: '{{ old("cd_changer", $warehouseAutomobile->cd_changer ?? "0") }}',
                    first_aid_kit: '{{ old("first_aid_kit", $warehouseAutomobile->first_aid_kit ?? "0") }}',
                    floor_mat: '{{ old("floor_mat", $warehouseAutomobile->floor_mat ?? "0") }}',
                    cigarette_lighter: '{{ old("cigarette_lighter", $warehouseAutomobile->cigarette_lighter ?? "0") }}',
                    cargo_net: '{{ old("cargo_net", $warehouseAutomobile->cargo_net ?? "0") }}',
                    ashtray: '{{ old("ashtray", $warehouseAutomobile->ashtray ?? "0") }}',
                    Tools: '{{ old("Tools", $warehouseAutomobile->Tools ?? "0") }}',
                    spare_tire: '{{ old("spare_tire", $warehouseAutomobile->spare_tire ?? "0") }}',
                    sun_roof: '{{ old("sun_roof", $warehouseAutomobile->sun_roof ?? "0") }}',
                },
                documents: [],
                selectedTools: [],
                filterType: 'All Photo',
                purposeToSet: '',
                isUploading: false,
                uploadModalOpen: false,
                uploadType: 'Receiving',

                init() {
                    const formEl = document.getElementById('automobile-form');
                    formEl.addEventListener('submit', (e) => {
                        this.isSaving = true;
                    });
                    this.fetchDocuments();
                },
                fetchDocuments() {
                    fetch(`{{ route('warehouse.automobile.documents.list', $warehouseAutomobile) }}`, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('Failed to fetch documents');
                        return r.json();
                    })
                    .then(data => {
                        if(data.success) {
                            this.documents = data.documents || [];
                            this.selectedTools = [];
                        }
                    })
                    .catch(err => {
                        console.error('Gallery fetch error:', err);
                        this.documents = [];
                    });
                },
                get filteredDocuments() {
                    if (this.filterType === 'All Photo') return this.documents;
                    return this.documents.filter(d => d.document_type === this.filterType);
                },
                get ToolsByPurpose() {
                    let groups = { 'Receiving': [], 'Loading': [], 'Others': [] };
                    this.filteredDocuments.forEach(d => {
                        if (groups[d.document_type]) groups[d.document_type].push(d);
                        else groups['Others'].push(d);
                    });
                    return groups;
                },
                toggleSelection(id) {
                    let strId = id.toString();
                    let numId = Number(id);
                    if (this.selectedTools.includes(strId) || this.selectedTools.includes(numId)) {
                        this.selectedTools = this.selectedTools.filter(i => i !== strId && i !== numId);
                    } else {
                        this.selectedTools.push(numId);
                    }
                },
                applyPurpose() {
                    if (!this.purposeToSet || this.selectedTools.length === 0) {
                        alert('Please select images and a purpose first.');
                        return;
                    }
                    let promises = this.selectedTools.map(id => {
                        return fetch(`/warehouse/automobile/documents/${id}/purpose`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ document_type: this.purposeToSet })
                        });
                    });
                    Promise.all(promises).then(() => {
                        this.purposeToSet = '';
                        this.fetchDocuments();
                    }).catch(err => console.error('Purpose update error:', err));
                },
                deleteSelected() {
                    if (this.selectedTools.length === 0) return;
                    if (!confirm('Are you sure you want to delete ' + this.selectedTools.length + ' selected image(s)?')) return;
                    let promises = this.selectedTools.map(id => {
                        return fetch(`/warehouse/automobile/documents/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                    });
                    Promise.all(promises).then(() => {
                        this.fetchDocuments();
                    }).catch(err => console.error('Delete error:', err));
                },
                handleFiles(files) {
                    if (!files.length) return;
                    this.isUploading = true;
                    let promises = Array.from(files).map(file => {
                        let fd = new FormData();
                        fd.append('file', file);
                        fd.append('document_type', this.uploadType);
                        return fetch(`{{ route('warehouse.automobile.documents.store', $warehouseAutomobile) }}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: fd
                        }).then(r => {
                            if (!r.ok) throw new Error('Upload failed for ' + file.name);
                            return r.json();
                        });
                    });
                    Promise.all(promises).then(() => {
                        this.uploadModalOpen = false;
                        this.fetchDocuments();
                    }).catch(err => {
                        console.error('Upload error:', err);
                        alert('Some files failed to upload. Please try again.');
                    }).finally(() => {
                        this.isUploading = false;
                        // Reset file input so same file can be re-selected
                        const fileInput = document.getElementById('file-upload');
                        if (fileInput) fileInput.value = '';
                    });
                }
            }));
        });
    </script>
</x-layout>
