<!-- CONTAINER & ITEMS TAB -->
<div x-show="activeTab === 'container'" class="main-grid">
    <div class="portlet light">
        <div class="portlet-title">
            <span class="caption-subject"><i class="fa fa-cube"></i> Container List</span>
        </div>
        <div class="portlet-body">
            <div class="container-toolbar">
                <div style="display: flex; gap: 4px; align-items: center;">
                    <button type="button" @click="addContainer()" class="btn-tool" title="Add Row"><i class="fa fa-plus"></i> Add Row</button>
                    <button type="button" @click="addContainer(5)" class="btn-tool" style="background:#64748b; color:#fff;" title="Add 5 Rows"><i class="fa fa-plus"></i> Add 5 Rows</button>
                    <button type="button" @click="addBulkContainers" class="btn-tool-icon" title="Add Bulk"><i class="fa fa-plus-square"></i></button>
                    <button type="button" @click="duplicateSelectedContainers" class="btn-tool-icon" title="Duplicate"><i class="fa fa-copy"></i></button>
                    <button type="button" @click="deleteSelectedContainers" class="btn-tool-icon" style="color:red; border-color:red;" title="Delete Selected"><i class="fa fa-trash"></i></button>
                </div>
                <!-- Hidden per client instruction: Import Container, Create A/P, Copy Data from All HB/L, Container info to clipboard, purple button -->
                <div style="display: none;">
                    <button type="button" @click="$refs.importFileInput.click()" class="btn-tool"><i class="fa fa-cloud-upload"></i> Import Container</button>
                    <input type="file" x-ref="importFileInput" style="display:none;" @change="handleContainerImport" accept=".csv,.txt">
                    <button type="button" @click="createApFromContainers" class="btn-tool-outline">Create A/P</button>
                    <button type="button" @click="copyDataFromAllHbl" class="btn-tool-outline" style="color:#4b77be; border-color:#4b77be;">Copy Data from All HB/L</button>
                    <button type="button" @click="showClipboardModal = true" class="btn-tool-outline">Container info to clipboard <i class="fa fa-external-link"></i></button>
                    <button type="button" class="btn-tool-secondary" style="background: #9b59b6;" onclick="window.location.href='{{ isset($oceanImport) ? route('ocean-import.containers.export', $oceanImport->id) : '#' }}'"><i class="fa fa-sign-out"></i></button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="container-table">
                    <colgroup>
                        <col style="width: 30px;">
                        <col style="width: 30px;">
                        <col style="width: 60px;">
                        <col style="width: 160px;">
                        <col style="width: 80px;">
                        <col style="width: 100px;">
                        <col style="width: 100px;">
                        <col style="width: 100px;">
                        <col style="width: 120px;">
                        <col style="width: 100px;">
                        <col style="width: 100px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th><input type="checkbox" @change="toggleAllContainers"></th>
                            <th>#</th>
                            <th>PP/CTF</th>
                            <th>Container No.</th>
                            <th>TP/SZ</th>
                            <th>Seal No.</th>
                            <th>LFD</th>
                            <th>FDD</th>
                            <th>
                                <div class="header-split">
                                    <div class="header-top">PKG</div>
                                    <div class="header-bottom">CARTON(S)</div>
                                </div>
                            </th>
                            <th>
                                <div class="header-split">
                                    <div class="header-top">Weight</div>
                                    <div class="header-bottom">KG</div>
                                </div>
                            </th>
                            <th>
                                <div class="header-split">
                                    <div class="header-top" style="font-size:9px;">Measurement</div>
                                    <div class="header-bottom">CBM</div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <template x-for="(cont, idx) in form.containers" :key="idx">
                        <tbody style="border:none;">
                            <tr class="row-main">
                                <input type="hidden" :name="'containers['+idx+'][id]'" :value="cont.id">
                                <td style="width:30px;"><input type="checkbox" x-model="cont.selected" style="display:block; margin:auto;"></td>
                                <td style="width:30px; text-align:center;">
                                    <div class="flex items-center justify-center gap-1">
                                        <i @click.stop="cont.expanded = !cont.expanded" class="fa cursor-pointer text-gray-400 hover:text-blue-500" :class="cont.expanded ? 'fa-minus-square' : 'fa-plus-square'" style="font-size:12px; display: none;"></i>
                                        <span x-text="idx + 1" style="font-weight:bold;"></span>
                                    </div>
                                </td>
                                <td style="width:60px;"><input type="text" :name="'containers['+idx+'][pp_ctf]'" class="form-control-gf" x-model="cont.pp_ctf"></td>
                                <td style="width:160px;">
                                    <input type="text" :name="'containers['+idx+'][container_no]'" class="form-control-gf" x-model="cont.container_no">
                                </td>
                                <td style="width:80px;"><select :name="'containers['+idx+'][container_type_id]'" class="form-control-gf" x-model="cont.container_type_id"><option value="">Select...</option>@foreach($containerTypes as $ct)<option value="{{ $ct->id }}">{{ $ct->code }}</option>@endforeach</select></td>
                                <td style="width:100px;"><input type="text" :name="'containers['+idx+'][seal_no]'" class="form-control-gf" x-model="cont.seal_no"></td>
                                <td style="width:100px;"><input type="date" :name="'containers['+idx+'][lfd]'" class="form-control-gf" x-model="cont.lfd"></td>
                                <td style="width:100px;"><input type="date" :name="'containers['+idx+'][fdd]'" class="form-control-gf" x-model="cont.fdd"></td>
                                <td style="width:120px;"><input type="number" :name="'containers['+idx+'][pkg_qty]'" class="form-control-gf" x-model="cont.pkg_qty" style="text-align:right;"></td>
                                <td style="width:100px;"><input type="number" :name="'containers['+idx+'][weight_kg]'" class="form-control-gf" x-model="cont.weight_kg" step="0.01" style="text-align:right;"></td>
                                <td style="width:100px;"><input type="number" :name="'containers['+idx+'][measure_cbm]'" class="form-control-gf" x-model="cont.measure_cbm" step="0.01" style="text-align:right;"></td>
                            </tr>
                            <tr style="display: none !important;" class="expanded-row">
                                <td colspan="2" style="border-right: 1px solid #dcdcdc; background:#fff !important;"></td>
                                <td colspan="9">
                                    <div class="expanded-container">
                                        <!-- Group 1: Occupies space of Col 3-4 (60+160 = 220px) -->
                                        <div class="expanded-col" style="width: 220px;">
                                            <div class="form-group-gf"><label class="form-label-gf">Seal No2.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][seal_no2]'" class="form-control-gf" x-model="cont.seal_no2"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">Pick Up No.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][pickup_no]'" class="form-control-gf" x-model="cont.pickup_no"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">CPRS No.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][cprs_no]'" class="form-control-gf" x-model="cont.cprs_no"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">CNRU No.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][cnru_no]'" class="form-control-gf" x-model="cont.cnru_no"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">IT No.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][it_no]'" class="form-control-gf" x-model="cont.it_no"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">D.G</label><div class="form-input-container"><select :name="'containers['+idx+'][is_dg]'" class="form-control-gf" x-model="cont.is_dg"><option value="0">No</option><option value="1">Yes</option></select></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">Storage Start</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][storage_start_date]'" class="form-control-gf" x-model="cont.storage_start_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">Storage End</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][storage_end_date]'" class="form-control-gf" x-model="cont.storage_end_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">Weight LB</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][weight_lb]'" class="form-control-gf" x-model="cont.weight_lb"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf">Measure CFT</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][measure_cft]'" class="form-control-gf" x-model="cont.measure_cft"></div></div>
                                            <div class="mt-2">
                                                <div class="text-[9px] font-bold text-gray-500">Remarks</div>
                                                <textarea :name="'containers['+idx+'][remarks]'" class="form-control-gf" x-model="cont.remarks"></textarea>
                                            </div>
                                            <div class="mt-1">
                                                <div class="text-[9px] font-bold text-gray-500">Internal Remarks</div>
                                                <textarea :name="'containers['+idx+'][internal_remarks]'" class="form-control-gf" x-model="cont.internal_remarks"></textarea>
                                            </div>
                                        </div>
                                        <!-- Group 2: Occupies space of Col 5-6 (80+100 = 180px) -->
                                        <div class="expanded-col" style="width: 180px;">
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Carrier rel.</label><div class="form-input-container"><input type="checkbox" :name="'containers['+idx+'][is_carrier_release]'" x-model="cont.is_carrier_release" style="width:12px;height:12px;"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Yard Loc.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][yard_location]'" class="form-control-gf" x-model="cont.yard_location"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Unload Vessel</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][unload_vessel_date]'" class="form-control-gf" x-model="cont.unload_vessel_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Gate In</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][gate_in_date]'" class="form-control-gf" x-model="cont.gate_in_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Rail Start</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][rail_start_date]'" class="form-control-gf" x-model="cont.rail_start_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">P.O.D ETA</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][pod_eta]'" class="form-control-gf" x-model="cont.pod_eta"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Avail Pickup</label><div class="form-input-container"><input type="checkbox" :name="'containers['+idx+'][is_avail_pickup]'" x-model="cont.is_avail_pickup" style="width:12px;height:12px;"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Appt.</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][appointment_date]'" class="form-control-gf" x-model="cont.appointment_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Trucker</label><div class="form-input-container"><select :name="'containers['+idx+'][trucker_id]'" class="form-control-gf" x-model="cont.trucker_id"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Pick Up</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][pickup_date]'" class="form-control-gf" x-model="cont.pickup_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Gate Out</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][gate_out_date]'" class="form-control-gf" x-model="cont.gate_out_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">F.Dest ETA</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][fdest_eta]'" class="form-control-gf" x-model="cont.fdest_eta"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">ETA Door</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][eta_door]'" class="form-control-gf" x-model="cont.eta_door"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">ATA Door</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][ata_door]'" class="form-control-gf" x-model="cont.ata_door"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Empty Conf.</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][empty_conf_date]'" class="form-control-gf" x-model="cont.empty_conf_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Empty Ret.</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][empty_ret_date]'" class="form-control-gf" x-model="cont.empty_ret_date"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Chassis days</label><div class="form-input-container"><input type="number" :name="'containers['+idx+'][chassis_days]'" class="form-control-gf" x-model="cont.chassis_days" step="0.1"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">C.Hold</label><div class="form-input-container"><input type="checkbox" :name="'containers['+idx+'][is_customs_hold]'" value="1" x-model="cont.is_customs_hold" style="width:12px;height:12px;"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">A/N</label><div class="form-input-container" style="gap:2px;"><input type="checkbox" :name="'containers['+idx+'][is_an_sent]'" value="1" x-model="cont.is_an_sent" style="width:12px;height:12px;flex-shrink:0;"><input type="date" :name="'containers['+idx+'][an_sent_date]'" class="form-control-gf" x-model="cont.an_sent_date" style="flex:1;"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">D/O</label><div class="form-input-container" style="gap:2px;"><input type="checkbox" :name="'containers['+idx+'][is_do_sent]'" value="1" x-model="cont.is_do_sent" style="width:12px;height:12px;flex-shrink:0;"><input type="date" :name="'containers['+idx+'][do_sent_date]'" class="form-control-gf" x-model="cont.do_sent_date" style="flex:1;"></div></div>
                                            <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Complete</label><div class="form-input-container"><input type="checkbox" :name="'containers['+idx+'][is_complete]'" x-model="cont.is_complete" style="width:12px;height:12px;"></div></div>
                                        </div>
                                        <!-- Group 3: Occupies space of Col 7-11 (100+100+120+100+100 = 520px) -->
                                        <div class="expanded-col flex-1" style="background: #fff; border-right:none;">
                                            <div class="hbl-header">HB/L No.</div>
                                            <div style="border: 1px solid #eee; min-height: 50px; background: #fff; padding: 4px;">
                                                <template x-for="h in hbls" :key="h.id || h.hbl_no">
                                                    <div x-show="(h.containers || []).some(c => c.container_no === cont.container_no)" style="padding: 2px 4px; font-size: 10px; border-bottom: 1px solid #eee;">
                                                        <span x-text="h.hbl_no" style="font-weight: 600; color: #3b82f6;"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!hbls.some(h => (h.containers || []).some(c => c.container_no === cont.container_no))">
                                                    <div style="padding: 10px; text-align: center; color: #94a3b8; font-size: 10px;">No HBL assigned</div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </template>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="6" style="border:none;"></td>
                            <td colspan="2" class="total-label-cell">Total</td>
                            <td class="total-val-cell"><span x-text="calculateTotal('pkg_qty')"></span></td>
                            <td class="total-val-cell"><span x-text="calculateTotal('weight_kg').toFixed(2)"></span></td>
                            <td class="total-val-cell"><span x-text="calculateTotal('measure_cbm').toFixed(2)"></span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px; font-size: 11px;">
                <span style="color:#333;">Total</span>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 5px; align-items: center; gap: 10px;">
                <label class="form-label-gf">Display Unit</label>
                <select name="display_unit" class="form-control-gf" style="width: 150px;" x-model="form.display_unit"><option value="both">Show Both</option><option value="revenue">Revenue</option><option value="cost">Cost</option></select>
            </div>

            <div style="display: flex; gap: 20px; margin-top: 15px;">
                <div style="flex: 1;">
                    <label class="caption-subject" style="font-size: 11px; margin-bottom: 5px; display: block;">Mark</label>
                    <textarea name="mark" class="form-control-gf" style="height: 140px;" x-model="form.mark"></textarea>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <label class="caption-subject" style="font-size: 11px;">Description</label>
                        <button type="button" @click="copyDescriptionFromAllHbl" class="btn-tool" style="padding: 2px 8px; font-size: 10px;">Copy from All HB/L</button>
                    </div>
                    <textarea name="description" class="form-control-gf" style="height: 140px;" x-model="form.description"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- HBL Containers & Items Sections -->
    <template x-for="(hbl, hblIdx) in hbls" :key="hblIdx">
        <div class="portlet light" style="margin-top: 15px; border: 1px solid #f2bc00;">
            <!-- Theme Header Bar -->
            <div class="portlet-title" style="background: #f2bc00; color: #fff; cursor: pointer; min-height: 28px; padding: 4px 10px; display: flex; justify-content: space-between; align-items: center;" @click="hbl.show = !hbl.show">
                <span class="caption-subject" style="color: #fff; font-size: 12px; font-weight: bold;">
                    <i class="fa fa-cube"></i> HB/L: <span x-text="hbl.hbl_no || 'Draft HBL'"></span> | Containers & Items
                </span>
                <div class="actions" style="display: flex; gap: 10px; align-items: center;">
                    <i class="fa fa-angle-down" :class="hbl.show ? 'rotate-180' : ''" style="font-size: 14px; color: #fff; transition: transform 0.2s;"></i>
                </div>
            </div>

            <div class="portlet-body" x-show="hbl.show" x-collapse style="padding: 12px;">
                <!-- Customer Reference / P.O. No. -->
                <div class="flex justify-between items-center" style="margin-bottom: 12px; gap: 20px;">
                    <div style="flex: 1;">
                        <label class="form-label-gf" style="font-weight: bold; margin-bottom: 4px; display: block;">Customer Reference / P.O. No. <span style="font-weight: normal; color: #666;">(Please list down P.O. No. for this HB/L)</span></label>
                        <input type="text" :name="'hbls['+hblIdx+'][po_no]'" class="form-control-gf" placeholder="Add P.O. here..." x-model="hbl.po_no" style="width: 100%; height: 22px;">
                    </div>
                    <div style="width: 200px; text-align: right;">
                        <span style="font-weight: bold; font-size: 11px; display: block; margin-bottom: 4px;">P.O. Mapping</span>
                        <div class="flex gap-4 justify-end" style="font-size: 11px;">
                            <label class="flex items-center gap-1 cursor-pointer" style="font-weight: normal;">
                                <input type="radio" :name="'hbls['+hblIdx+'][po_mapping_type]'" value="container" x-model="hbl.po_mapping_type"> Container based
                            </label>
                            <label class="flex items-center gap-1 cursor-pointer" style="font-weight: normal;">
                                <input type="radio" :name="'hbls['+hblIdx+'][po_mapping_type]'" value="item" x-model="hbl.po_mapping_type"> Item based
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Container List -->
                <div style="margin-bottom: 15px;">
                    <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                        <span style="font-weight: bold; font-size: 12px; color: #333;">Container List</span>
                        <button type="button" @click="copyContainersFromMbl(hbl)" class="btn-tool-outline" style="color:#f2bc00; border-color:#f2bc00; padding: 2px 10px; font-size: 11px;">
                            <i class="fa fa-copy"></i> Copy Value from MB/L
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="container-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Container No.</th>
                                    <th style="width: 150px;">PKG</th>
                                    <th style="width: 150px;">Weight</th>
                                    <th style="width: 150px;">Measurement</th>
                                    <th x-show="hbl.po_mapping_type === 'container'">P.O. No.</th>
                                    <th style="width: 40px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(c, cIdx) in hbl.containers" :key="cIdx">
                                    <tr>
                                        <td style="text-align: center; font-weight: bold;" x-text="cIdx + 1"></td>
                                        <td>
                                            <select class="form-control-gf" x-model="c.container_no" :name="c.container_no !== 'MANUAL' ? 'hbls['+hblIdx+'][containers]['+cIdx+'][container_no]' : ''">
                                                <option value="">Select MBL Container...</option>
                                                <template x-for="mblC in form.containers">
                                                    <option :value="mblC.container_no" x-text="mblC.container_no"></option>
                                                </template>
                                                <option value="MANUAL">+ Enter Manual</option>
                                            </select>
                                            <input type="text" class="form-control-gf mt-1" placeholder="Enter Container No." x-show="c.container_no === 'MANUAL'" x-model="c.manual_container_no" :name="c.container_no === 'MANUAL' ? 'hbls['+hblIdx+'][containers]['+cIdx+'][container_no]' : ''">
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <input type="number" class="form-control-gf" style="width: 60px; text-align: right;" x-model="c.pkg_qty" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][pkg_qty]'">
                                                <select class="form-control-gf" style="flex: 1;" x-model="c.pkg_unit" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][pkg_unit]'">
                                                    @foreach($packageUnits as $pu)
                                                        <option value="{{ $pu->code }}">{{ $pu->code }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <input type="number" class="form-control-gf" style="width: 60px; text-align: right;" step="0.01" x-model="c.weight_kg" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][weight_kg]'">
                                                <select class="form-control-gf" style="flex: 1;" x-model="c.weight_unit" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][weight_unit]'">
                                                    <option value="KG">KG</option>
                                                    <option value="LBS">LBS</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <input type="number" class="form-control-gf" style="width: 60px; text-align: right;" step="0.01" x-model="c.measure_cbm" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][measure_cbm]'">
                                                <select class="form-control-gf" style="flex: 1;" x-model="c.measure_unit" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][measure_unit]'">
                                                    <option value="CBM">CBM</option>
                                                    <option value="CFT">CFT</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td x-show="hbl.po_mapping_type === 'container'">
                                            <!-- P.O. Selector for Container-based Mapping -->
                                            <select class="form-control-gf" x-model="c.po_no" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][po_no]'">
                                                <option value="">Select PO...</option>
                                                <template x-for="po in getPoList(hbl.po_no)">
                                                    <option :value="po" x-text="po"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" @click="hbl.containers.splice(cIdx, 1)" class="btn-tool-icon" style="color: red; border:none; background:none;" title="Delete row">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!hbl.containers || hbl.containers.length === 0">
                                    <tr>
                                        <td :colspan="hbl.po_mapping_type === 'container' ? 7 : 6" style="text-align: center; color: #999; padding: 10px;">No containers assigned yet. Click "Copy Value from MB/L" or add a row.</td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="total-row" style="background: #fdfaf0;">
                                    <td colspan="2" style="font-weight: bold; text-align: right;">Total</td>
                                    <td style="font-weight: bold; text-align: right;" x-text="calculateHblTotal(hbl, 'pkg_qty')"></td>
                                    <td style="font-weight: bold; text-align: right;" x-text="calculateHblTotal(hbl, 'weight_kg')"></td>
                                    <td style="font-weight: bold; text-align: right;" x-text="calculateHblTotal(hbl, 'measure_cbm')"></td>
                                    <td :colspan="hbl.po_mapping_type === 'container' ? 2 : 1"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-2">
                        <button type="button" @click="hbl.containers.push({container_no:'', pkg_qty:'', pkg_unit:'CARTON(S)', weight_kg:'', weight_unit:'KG', measure_cbm:'', measure_unit:'CBM', po_no:''})" class="btn-tool" style="background-color: #3b82f6; color: #fff; border-color: #3b82f6;" title="Add Container"><i class="fa fa-plus"></i> Add Container</button>
                    </div>
                </div>

                <!-- Commodity / Manifest Commodity -->
                <div style="margin-bottom: 15px;">
                    <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                        <span style="font-weight: bold; font-size: 12px; color: #333;">Commodity / Manifest Commodity</span>
                        <div class="flex gap-1">
                            <button type="button" @click="addHblCommodity(hbl)" class="btn-tool-icon btn-tool-icon-blue" title="Add Row"><i class="fa fa-plus"></i></button>
                            <button type="button" @click="deleteSelectedHblCommodities(hbl)" class="btn-tool-icon" style="color: red; border-color: red;" title="Delete Selected"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="container-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 30px;"><input type="checkbox" @change="toggleAllHblCommodities(hbl, $event)"></th>
                                    <th style="width: 150px;">HB/L No.</th>
                                    <th>* Commodity Description</th>
                                    <th style="width: 200px;">HTS Code</th>
                                    <th style="width: 200px;">Container</th>
                                    <th x-show="hbl.po_mapping_type === 'item'">P.O. No.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(comm, commIdx) in hbl.commodities" :key="commIdx">
                                    <tr>
                                        <td><input type="checkbox" x-model="comm.selected"></td>
                                        <td>
                                            <select :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][hbl_no]'" class="form-control-gf" x-model="comm.hbl_no">
                                                <option value="">Select HBL...</option>
                                                <template x-for="h in hbls">
                                                    <option :value="h.hbl_no" x-text="h.hbl_no"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][commodity_desc]'" class="form-control-gf" placeholder="Description..." x-model="comm.commodity_desc" required style="min-height: 80px !important; height: 80px !important; width: 100%; resize: vertical; padding: 6px;"></textarea>
                                        </td>
                                        <td>
                                            <input type="text" :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][hts_code]'" class="form-control-gf" placeholder="HTS Code..." x-model="comm.hts_code">
                                        </td>
                                        <td>
                                            <select :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][container_no]'" class="form-control-gf" x-model="comm.container_no">
                                                <option value="">Select Container...</option>
                                                <template x-for="c in hbl.containers">
                                                    <option :value="c.container_no" x-text="c.container_no"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td x-show="hbl.po_mapping_type === 'item'">
                                            <!-- P.O. Selector for Item-based Mapping -->
                                            <select :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][po_no]'" class="form-control-gf" x-model="comm.po_no">
                                                <option value="">Select PO...</option>
                                                <template x-for="po in getPoList(hbl.po_no)">
                                                    <option :value="po" x-text="po"></option>
                                                </template>
                                            </select>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!hbl.commodities || hbl.commodities.length === 0">
                                    <tr>
                                        <td :colspan="hbl.po_mapping_type === 'item' ? 5 : 4" style="text-align: center; color: #999; padding: 10px;">
                                            No commodities added yet. Click <span class="text-blue-500 cursor-pointer" @click="addHblCommodity(hbl)">here</span> to add a new row.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mark & Description -->
                <div class="form-grid-2" style="margin-bottom: 15px;">
                    <div>
                        <label class="form-label-gf" style="font-weight: bold; margin-bottom: 4px; display: block;">Mark</label>
                        <textarea :name="'hbls['+hblIdx+'][hbl_mark]'" class="form-control-gf" style="height: 140px; width:100%;" x-model="hbl.hbl_mark"></textarea>
                    </div>
                    <div>
                        <div class="flex justify-between items-center" style="margin-bottom: 4px;">
                            <label class="form-label-gf" style="font-weight: bold; margin-bottom: 0;">Description</label>
                            <div class="flex gap-1">
                                <span style="font-size:10px; color:#555; align-self: center; margin-right: 4px;">Copy:</span>
                                <button type="button" @click="copyToDescription(hbl, 'po')" class="btn-tool" style="padding: 1px 6px; font-size: 10px;">P.O.</button>
                                <button type="button" @click="copyToDescription(hbl, 'commodity')" class="btn-tool" style="padding: 1px 6px; font-size: 10px;">Commodity</button>
                                <button type="button" @click="copyToDescription(hbl, 'both')" class="btn-tool" style="padding: 1px 6px; font-size: 10px;">Commodity & HTS</button>
                            </div>
                        </div>
                        <textarea :name="'hbls['+hblIdx+'][hbl_description]'" class="form-control-gf" style="height: 140px; width:100%;" x-model="hbl.hbl_description"></textarea>
                    </div>
                </div>

                <!-- Remark: Tabs for Arrival Notice and Delivery Order -->
                <div style="margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                    <div class="flex" style="background: #f5f5f5; border-bottom: 1px solid #ddd;">
                        <button type="button" class="tab-btn" :class="hbl.remark_tab === 'arrival_notice' ? 'active-tab' : ''" @click="hbl.remark_tab = 'arrival_notice'">
                            Arrival Notice
                        </button>
                        <button type="button" class="tab-btn" :class="hbl.remark_tab === 'delivery_order' ? 'active-tab' : ''" @click="hbl.remark_tab = 'delivery_order'">
                            Delivery Order
                        </button>
                    </div>
                    <div style="padding: 10px; background: #fff;">
                        <div x-show="hbl.remark_tab === 'arrival_notice'">
                            <textarea :name="'hbls['+hblIdx+'][arrival_notice_remark]'" class="form-control-gf" style="height: 80px; width:100%;" placeholder="Arrival Notice remarks..." x-model="hbl.arrival_notice_remark"></textarea>
                        </div>
                        <div x-show="hbl.remark_tab === 'delivery_order'">
                            <textarea :name="'hbls['+hblIdx+'][delivery_order_remark]'" class="form-control-gf" style="height: 80px; width:100%;" placeholder="Delivery Order remarks..." x-model="hbl.delivery_order_remark"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Warehouse Receipt List -->
                <div>
                    <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                        <span style="font-weight: bold; font-size: 12px; color: #333;"><i class="fa fa-file-text-o"></i> Warehouse Receipt List</span>
                        <div class="flex gap-1">
                            <button type="button" @click="openWarehouseReceiptModal(hbl)" class="btn-tool" style="background:#3498db; color:#fff; border:none; font-size: 11px; padding: 2px 10px;"><i class="fa fa-download"></i> Load from Warehouse</button>
                            <button type="button" @click="createHblReceiptLink(hbl)" class="btn-tool" style="background:#2ecc71; color:#fff; border:none; font-size: 11px; padding: 2px 10px;"><i class="fa fa-link"></i> Create Item and Link</button>
                            <button type="button" @click="deleteSelectedHblReceipts(hbl)" class="btn-tool-icon" style="color: red; border-color: red;" title="Delete Selected"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="container-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 30px;"><input type="checkbox" @change="toggleAllHblReceipts(hbl, $event)"></th>
                                    <th>Receipt No.</th>
                                    <th>Vin No.</th>
                                    <th>TOTAL PCS</th>
                                    <th>Available PCS</th>
                                    <th>Allocated PCS</th>
                                    <th>Unit</th>
                                    <th>Actual Weight</th>
                                    <th>Measurement</th>
                                    <th>Remarks for Load Plan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(rec, rIdx) in hbl.receipts" :key="rIdx">
                                    <tr>
                                        <td><input type="checkbox" x-model="rec.selected"></td>
                                        <td>
                                            <span x-text="rec.receipt_no" style="font-weight:bold;"></span>
                                            <input type="hidden" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][receipt_no]'" :value="rec.receipt_no">
                                        </td>
                                        <td>
                                            <input type="text" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][vin_no]'" class="form-control-gf" x-model="rec.vin_no">
                                        </td>
                                        <td>
                                            <input type="number" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][total_pcs]'" class="form-control-gf" style="text-align: right;" x-model="rec.total_pcs" @input="if(hbl.auto_sync_receipts) syncReceiptTotalsToContainers(hbl)">
                                        </td>
                                        <td>
                                            <input type="number" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][available_pcs]'" class="form-control-gf" style="text-align: right;" x-model="rec.available_pcs">
                                        </td>
                                        <td>
                                            <input type="number" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][allocated_pcs]'" class="form-control-gf" style="text-align: right;" x-model="rec.allocated_pcs">
                                        </td>
                                        <td>
                                            <input type="text" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][unit]'" class="form-control-gf" x-model="rec.unit">
                                        </td>
                                        <td>
                                            <input type="number" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][actual_weight]'" class="form-control-gf" style="text-align: right;" step="0.01" x-model="rec.actual_weight" @input="if(hbl.auto_sync_receipts) syncReceiptTotalsToContainers(hbl)">
                                        </td>
                                        <td>
                                            <input type="number" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][measurement]'" class="form-control-gf" style="text-align: right;" step="0.01" x-model="rec.measurement" @input="if(hbl.auto_sync_receipts) syncReceiptTotalsToContainers(hbl)">
                                        </td>
                                        <td>
                                            <input type="text" :name="'hbls['+hblIdx+'][receipts]['+rIdx+'][remarks]'" class="form-control-gf" x-model="rec.remarks">
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!hbl.receipts || hbl.receipts.length === 0">
                                    <tr>
                                        <td colspan="10" style="text-align: center; color: #999; padding: 10px;">No warehouse receipts linked.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <!-- Hidden per client instruction: Auto-sync package, weight and measurements -->
                    <div class="flex items-center gap-1 mt-2" style="font-size: 11px; display: none;">
                        <input type="checkbox" :id="'sync-wr-' + hblIdx" x-model="hbl.auto_sync_receipts" @change="syncReceiptTotalsToContainers(hbl)">
                        <label :for="'sync-wr-' + hblIdx" style="font-weight:normal; color:#555; cursor:pointer;">Auto-sync package, weight and measurements</label>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
