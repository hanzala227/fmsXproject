<x-layout title="New Automobile">
    @push('styles')
    <x-form-styles />
    @endpush

    <div class="page-content" x-data="automobileForm()" x-cloak>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Warehouse <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">New Automobile</span></li>
            </ul>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h1 class="caption-subject" style="font-size: 18px;">New Automobile</h1>
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

        <form action="{{ route('warehouse.automobile.store') }}" method="POST" id="automobile-form">
            @csrf

            @if(isset($copyFrom))
            <input type="hidden" name="copy_from" value="{{ $copyFrom->id }}">
            @endif

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
                    <div class="portlet-body" style="padding: 40px; text-align: center; color: #475569;">
                        <i class="fa fa-info-circle" style="font-size: 24px; color: #3b82f6; margin-bottom: 10px; display: block;"></i>
                        <p style="font-size: 14px; font-weight: 500;">Please save the Automobile first.</p>
                        <p style="font-size: 12px; color: #64748b;">You can upload and manage pictures after creating the record.</p>
                        <button type="submit" class="btn-freightx" style="margin-top: 15px; border-radius: 2px; background: #3b82f6;">
                            Save Automobile
                        </button>
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
                    vin_no: '{{ old("vin_no", $copyFrom?->vin_no  ?? "") }}',
                    tag_no: '{{ old("tag_no", $copyFrom?->tag_no  ?? "") }}',
                    customer_id: '{{ old("customer_id", $copyFrom?->customer_id  ?? "") }}',
                    received_date: '{{ old("received_date", $copyFrom?->received_date?->format("Y-m-d") ?? "") }}',
                    received_by: '{{ old("received_by", $copyFrom?->received_by  ?? auth()->id()) }}',
                    maker: '{{ old("maker", $copyFrom?->maker  ?? "") }}',
                    year: '{{ old("year", $copyFrom?->year  ?? "") }}',
                    model: '{{ old("model", $copyFrom?->model  ?? "") }}',
                    color: '{{ old("color", $copyFrom?->color  ?? "") }}',
                    engine_no: '{{ old("engine_no", $copyFrom?->engine_no  ?? "") }}',
                    manufacture_date: '{{ old("manufacture_date", $copyFrom?->manufacture_date?->format("Y-m-d") ?? "") }}',
                    title_received: {{ old("title_received") !== null ? (old("title_received") ? 'true' : 'false') : (isset($copyFrom) && $copyFrom?->title_received  ? 'true' : 'false') }},
                    office_id: '{{ old("office_id", $copyFrom?->office_id  ?? "") }}',
                    condition: '{{ old("condition", $copyFrom?->condition  ?? "") }}',
                    key_number: '{{ old("key_number", $copyFrom?->key_number  ?? "") }}',
                    fuel: '{{ old("fuel", $copyFrom?->fuel  ?? "") }}',
                    tire_size_front: '{{ old("tire_size_front", $copyFrom?->tire_size_front  ?? "") }}',
                    tire_size_rear: '{{ old("tire_size_rear", $copyFrom?->tire_size_rear  ?? "") }}',
                    mileage: '{{ old("mileage", $copyFrom?->mileage  ?? "") }}',
                    vehicle_state: '{{ old("vehicle_state", $copyFrom?->vehicle_state  ?? "") }}',
                    internal_remark: '{{ old("internal_remark", $copyFrom?->internal_remark  ?? "") }}',
                    w_sticker: '{{ old("w_sticker", $copyFrom?->w_sticker  ?? "0") }}',
                    remote_control: '{{ old("remote_control", $copyFrom?->remote_control  ?? "0") }}',
                    headphone: '{{ old("headphone", $copyFrom?->headphone  ?? "0") }}',
                    owners_manual: '{{ old("owners_manual", $copyFrom?->owners_manual  ?? "0") }}',
                    cd_player: '{{ old("cd_player", $copyFrom?->cd_player  ?? "0") }}',
                    cd_changer: '{{ old("cd_changer", $copyFrom?->cd_changer  ?? "0") }}',
                    first_aid_kit: '{{ old("first_aid_kit", $copyFrom?->first_aid_kit  ?? "0") }}',
                    floor_mat: '{{ old("floor_mat", $copyFrom?->floor_mat  ?? "0") }}',
                    cigarette_lighter: '{{ old("cigarette_lighter", $copyFrom?->cigarette_lighter  ?? "0") }}',
                    cargo_net: '{{ old("cargo_net", $copyFrom?->cargo_net  ?? "0") }}',
                    ashtray: '{{ old("ashtray", $copyFrom?->ashtray  ?? "0") }}',
                    Tools: '{{ old("Tools", $copyFrom?->Tools  ?? "0") }}',
                    spare_tire: '{{ old("spare_tire", $copyFrom?->spare_tire  ?? "0") }}',
                    sun_roof: '{{ old("sun_roof", $copyFrom?->sun_roof  ?? "0") }}',
                },
                init() {
                    const formEl = document.getElementById('automobile-form');
                    formEl.addEventListener('submit', (e) => {
                        this.isSaving = true;
                    });
                }
            }));
        });
    </script>
</x-layout>
