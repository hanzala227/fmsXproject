<!-- BASIC / MAIN TAB (MBL & HBL INFORMATION) -->
<div x-show="activeTab === 'basic'" class="main-grid">
    <div class="portlet light">
        <div @click="showMblSection = !showMblSection" class="portlet-title" style="cursor: pointer; background: #f9fafb;">
            <span class="caption-subject"><i class="fa" :class="showMblSection ? 'fa-minus-square-o' : 'fa-plus-square-o'"></i> MB/L</span>
            <div class="actions">
                <i class="fa fa-angle-down transition-transform" :class="showMblSection ? 'rotate-180' : ''"></i>
            </div>
        </div>
        <div class="portlet-body" x-show="showMblSection" x-collapse>
            <!-- Reminder / Notes Section for MBL (Hidden per client instruction) -->
            <div class="memo-section" style="margin-bottom: 10px; display: none;">
                <div class="memo-header" @click="showMblMemo = !showMblMemo">
                    <span>Note</span>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" class="btn-memo-doc" @click.stop="showDocumentModal = true">Document (<span x-text="documents.length"></span>) <i class="fa fa-external-link"></i></button>
                        <i class="fa" :class="showMblMemo ? 'fa-angle-up' : 'fa-angle-down'"></i>
                    </div>
                </div>
                <div class="memo-body" x-show="showMblMemo" x-collapse>
                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 2;">
                            <table class="memo-table">
                                <thead>
                                    <tr>
                                        <th style="width: 30px; background: #32c5d2; border: none; text-align: center; cursor: pointer;" @click="addMemo"><i class="fa fa-plus"></i></th>
                                        <th><i class="fa fa-bell"></i> Subject</th>
                                        <th>Last Modified</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(memo, idx) in form.memos" :key="idx">
                                        <tr :style="selectedMemoIndex === idx ? 'background: #f1f5f9; font-weight: bold;' : ''" @click="selectMemo(idx)" style="cursor: pointer;">
                                            <td style="text-align: center;">
                                                <i class="fa fa-sticky-note-o" style="font-size: 10px; color: #32c5d2;"></i>
                                            </td>
                                            <td x-text="memo.subject"></td>
                                            <td x-text="memo.updated_at ? memo.updated_at.substring(0,10) : ''"></td>
                                            <td style="text-align: center;">
                                                <button type="button" @click.stop="deleteMemo(idx)" class="btn-tool-icon" style="color:red; border:none; background:none; padding:0; cursor:pointer;"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="form.memos.length === 0">
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: #999; padding: 10px;">No notes found. Click + to add one.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <template x-if="selectedMemoIndex !== null">
                        <div style="flex: 1;">
                            <div class="flex flex-col gap-1">
                                <input type="text" class="form-control-gf" placeholder="Subject..." x-model="form.memos[selectedMemoIndex].subject" style="margin-bottom: 5px; font-weight: bold;">
                                <textarea :name="'memos['+selectedMemoIndex+'][content]'" class="memo-content-area" placeholder="Note content..." x-model="form.memos[selectedMemoIndex].content" style="height: 100px;"></textarea>
                                <input type="hidden" :name="'memos['+selectedMemoIndex+'][id]'" :value="form.memos[selectedMemoIndex].id">
                                <input type="hidden" :name="'memos['+selectedMemoIndex+'][subject]'" :value="form.memos[selectedMemoIndex].subject">
                            </div>
                        </div>
                        </template>
                        <div style="flex: 1; display: flex; align-items: center; justify-content: center; border: 1px dashed #cbd5e1; border-radius: 4px; padding: 10px; color: #64748b;" x-show="selectedMemoIndex === null">
                            Select a note to view/edit content.
                        </div>
                    </div>
                </div>
            </div>

            <!-- MBL CORE FIELDS GRID -->
            <div class="form-grid-4">
                <!-- Column 1 -->
                <div class="flex flex-col">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Booking No.</label>
                        <div class="form-input-container">
                            <input type="text" name="file_no" class="form-control-gf" x-model="form.file_no" readonly style="background:#f5f5f5;">
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Post Date</label>
                        <div class="form-input-container">
                            <input type="date" name="post_date" class="form-control-gf" x-model="form.post_date">
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Forwarding Agent</label>
                        <div class="form-input-container">
                            <x-inline-select name="forwarding_agent_id" :options="$agents" module="trade-partner" type="agent" x-model="form.forwarding_agent_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'forwarding_agent_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">OP</label>
                        <div class="form-input-container">
                            <select name="op_id" class="form-control-gf" x-model="form.op_id">
                                <option value="">Select Operator...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Customer Ref.</label>
                        <div class="form-input-container">
                            <input type="text" name="agent_ref_no" class="form-control-gf" x-model="form.agent_ref_no">
                        </div>
                    </div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Customer</label>
                        <div class="form-input-container">
                            <x-inline-select name="dm_customer_id" :options="$agents" module="trade-partner" type="customer" x-model="form.dm_customer_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_customer_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Sales</label>
                        <div class="form-input-container">
                            <select name="dm_sales_person_id" class="form-control-gf" x-model="form.dm_sales_person_id">
                                <option value="">Select Salesperson...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Column 2 -->
                <div class="flex flex-col">
                    <div class="form-group-gf">
                        <label class="form-label-gf" style="color:red;">* MB/L No.</label>
                        <div class="form-input-container">
                            <input type="text" name="mbl_no" class="form-control-gf" x-model="form.mbl_no" required>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Oversea Agent</label>
                        <div class="form-input-container">
                            <x-inline-select name="oversea_agent_id" :options="$agents" module="trade-partner" type="agent" x-model="form.oversea_agent_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'oversea_agent_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Co-loader</label>
                        <div class="form-input-container">
                            <x-inline-select name="co_loader_id" :options="$agents" module="trade-partner" type="agent" x-model="form.co_loader_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'co_loader_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Contract No.</label>
                        <div class="form-input-container">
                            <input type="text" name="contract_no" class="form-control-gf" x-model="form.contract_no">
                        </div>
                    </div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Shipper</label>
                        <div class="form-input-container">
                            <x-inline-select name="dm_shipper_id" :options="$agents" module="trade-partner" type="shipper" x-model="form.dm_shipper_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_shipper_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Bill To</label>
                        <div class="form-input-container">
                            <x-inline-select name="dm_bill_to_id" :options="$agents" module="trade-partner" type="customer" x-model="form.dm_bill_to_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_bill_to_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Column 3 -->
                <div class="flex flex-col">
                    <div class="form-group-gf">
                        <label class="form-label-gf" style="color:red;">* Office</label>
                        <div class="form-input-container">
                            <select name="office_id" class="form-control-gf" x-model="form.office_id" required>
                                <option value="">Select Office...</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->code }} - {{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Carrier</label>
                        <div class="form-input-container">
                            <x-inline-select name="carrier_id" :options="$agents" module="trade-partner" type="carrier" x-model="form.carrier_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'carrier_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Agent Ref No.</label>
                        <div class="form-input-container">
                            <input type="text" name="agent_ref_no" class="form-control-gf" x-model="form.agent_ref_no">
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Direct Master</label>
                        <div class="form-input-container">
                            <input type="checkbox" name="is_direct_master" value="1" x-model="isDirectMaster">
                        </div>
                    </div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Consignee</label>
                        <div class="form-input-container">
                            <x-inline-select name="dm_consignee_id" :options="$agents" module="trade-partner" type="consignee" x-model="form.dm_consignee_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_consignee_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Sales Type</label>
                        <div class="form-input-container">
                            <select name="sales_type" class="form-control-gf" x-model="form.sales_type">
                                <option value="">Select...</option>
                                <option value="NORMAL">NORMAL</option>
                                <option value="CO-LOAD">CO-LOAD</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Column 4 -->
                <div class="flex flex-col">
                    <div class="form-group-gf">
                        <label class="form-label-gf">B/L Type</label>
                        <div class="form-input-container">
                            <select name="bl_type" class="form-control-gf" x-model="form.bl_type">
                                <option value="">Select...</option>
                                <option value="NORMAL">NORMAL</option>
                                <option value="MEMO">MEMO</option>
                                <option value="SEA WAYBILL">SEA WAYBILL</option>
                                <option value="SURRENDERED">SURRENDERED</option>
                                <option value="TELEX">TELEX</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Acct. Carrier</label>
                        <div class="form-input-container">
                            <x-inline-select name="acct_carrier_id" :options="$agents" module="trade-partner" type="carrier" x-model="form.acct_carrier_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'acct_carrier_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Sub B/L No.</label>
                        <div class="form-input-container">
                            <input type="text" name="sub_bl_no" class="form-control-gf" x-model="form.sub_bl_no">
                        </div>
                    </div>
                    <div class="form-group-gf" style="height: 19px;"></div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Notify</label>
                        <div class="form-input-container">
                            <x-inline-select name="dm_notify_id" :options="$agents" module="trade-partner" type="notify" x-model="form.dm_notify_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_notify_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf" x-show="isDirectMaster">
                        <label class="form-label-gf">Cargo Type</label>
                        <div class="form-input-container">
                            <select name="cargo_type" class="form-control-gf" x-model="form.cargo_type">
                                <option value="">Select...</option>
                                <option value="GENERAL CARGO">GENERAL CARGO</option>
                                <option value="HAZARDOUS">HAZARDOUS</option>
                                <option value="REEFER">REEFER</option>
                                <option value="DANGEROUS">DANGEROUS</option>
                                <option value="OVERSIZE">OVERSIZE</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div style="height: 15px;"></div>

            <!-- LOGISTICS & ROUTING GRID -->
            <div class="form-grid-4">
                <!-- Column 1 -->
                <div class="flex flex-col">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Vessel</label>
                        <div class="form-input-container">
                            <select name="vessel_id" class="form-control-gf" x-model="form.vessel_id">
                                <option value="">Select Vessel...</option>
                                @foreach($vessels as $vessel)
                                    <option value="{{ $vessel->id }}">{{ $vessel->name ?? '' }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px; margin-left:4px;" @click="openAddNewModal('vessel', 'vessel_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Port of Loading</label>
                        <div class="form-input-container">
                            <x-inline-select name="pol_id" :options="$ports" module="port" x-model="form.pol_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'pol_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Place of Delivery</label>
                        <div class="form-input-container">
                            <x-inline-select name="del_id" :options="$ports" module="port" x-model="form.del_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'del_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>

                    <!-- Trans Shipment Section (Dynamic Connected with DB) -->
                    <div class="form-group-gf" style="flex-direction: column; align-items: stretch; margin-top: 6px; border-top: 1px dashed #cbd5e1; padding-top: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label class="form-label-gf" style="font-weight: 600; color: #1e40af; width: auto;">
                                <i class="fa fa-ship" style="margin-right: 3px; font-size: 10px;"></i> TRANS SHIPMENT
                            </label>
                            <button type="button" class="btn-default-gf" @click="addTransShipment()" style="height: 20px; padding: 0 6px; font-size: 10px; font-weight: 600; background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 3px; cursor: pointer;">
                                <i class="fa fa-plus" style="font-size: 9px; margin-right: 3px;"></i> TRANS SHIPMENT
                            </button>
                        </div>

                        <!-- Dynamic Trans Shipment List -->
                        <template x-for="(ts, tsIdx) in form.trans_shipments" :key="tsIdx">
                            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px; margin-bottom: 6px; position: relative;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-size: 10px; font-weight: 600; color: #475569;" x-text="'Trans Shipment Port #' + (tsIdx + 1)"></span>
                                    <button type="button" @click="removeTransShipment(tsIdx)" style="background: none; border: none; color: #ef4444; font-size: 11px; cursor: pointer; padding: 0 2px;" title="Remove Trans Shipment">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div class="form-input-container">
                                        <select :name="'trans_shipments[' + tsIdx + '][port_id]'" x-model="ts.port_id" class="form-control-gf" style="width: 100%;">
                                            <option value="">Select Trans Shipment Port...</option>
                                            @foreach($ports as $port)
                                                <option value="{{ $port->id }}">{{ $port->code ? $port->code . ' - ' : '' }}{{ $port->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
                                        <div>
                                            <span style="font-size: 9px; color: #64748b; display: block;">ETD</span>
                                            <input type="date" :name="'trans_shipments[' + tsIdx + '][etd]'" x-model="ts.etd" class="form-control-gf" style="width: 100%; font-size: 10px; padding: 1px 3px;">
                                        </div>
                                        <div>
                                            <span style="font-size: 9px; color: #64748b; display: block;">ETA</span>
                                            <input type="date" :name="'trans_shipments[' + tsIdx + '][eta]'" x-model="ts.eta" class="form-control-gf" style="width: 100%; font-size: 10px; padding: 1px 3px;">
                                        </div>
                                    </div>
                                    <div>
                                        <span style="font-size: 9px; color: #64748b; display: block;">Vessel / Voyage</span>
                                        <input type="text" :name="'trans_shipments[' + tsIdx + '][vessel_voyage]'" x-model="ts.vessel_voyage" placeholder="Vessel / Voyage..." class="form-control-gf" style="width: 100%; font-size: 10px; padding: 1px 3px;">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="!form.trans_shipments || form.trans_shipments.length === 0" style="font-size: 9px; color: #94a3b8; font-style: italic; margin-top: 2px;">
                            No trans shipment added. Click "+ TRANS SHIPMENT" to add intermediate ports.
                        </div>
                    </div>
                </div>

                <!-- Column 2 -->
                <div class="flex flex-col">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Voyage</label>
                        <div class="form-input-container">
                            <input type="text" name="voyage" class="form-control-gf" x-model="form.voyage">
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">ETD</label>
                        <div class="form-input-container">
                            <input type="date" name="etd" class="form-control-gf" x-model="form.etd">
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf" style="color:red;">* ETA</label>
                        <div class="form-input-container">
                            <input type="date" name="eta" class="form-control-gf" x-model="form.eta" required>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">ATD</label>
                        <div class="form-input-container">
                            <input type="date" name="atd" class="form-control-gf" x-model="form.atd">
                        </div>
                    </div>
                </div>

                <!-- Column 3 -->
                <div class="flex flex-col">
                    <div class="form-group-gf">
                        <label class="form-label-gf">CY Location</label>
                        <div class="form-input-container">
                            <x-inline-select name="cy_location_id" :options="$agents" module="trade-partner" type="location" x-model="form.cy_location_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'cy_location_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Port of Discharge</label>
                        <div class="form-input-container">
                            <x-inline-select name="pod_id" :options="$ports" module="port" x-model="form.pod_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'pod_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Final Destination</label>
                        <div class="form-input-container">
                            <x-inline-select name="fdest_id" :options="$ports" module="port" x-model="form.fdest_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'fdest_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">ATA</label>
                        <div class="form-input-container">
                            <input type="date" name="ata" class="form-control-gf" x-model="form.ata">
                        </div>
                    </div>
                </div>

                <!-- Column 4 -->
                <div class="flex flex-col">
                    <div class="form-group-gf">
                        <label class="form-label-gf">CFS Location</label>
                        <div class="form-input-container">
                            <x-inline-select name="cfs_location_id" :options="$agents" module="trade-partner" type="location" x-model="form.cfs_location_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'cfs_location_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf" style="height: 19px;"></div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Final ETA</label>
                        <div class="form-input-container">
                            <input type="date" name="final_eta" class="form-control-gf" x-model="form.final_eta">
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">ETB</label>
                        <div class="form-input-container">
                            <input type="date" name="etb" class="form-control-gf" x-model="form.etb">
                        </div>
                    </div>
                </div>
            </div>

            <div style="height: 15px;"></div>

            <!-- FREIGHT & SERVICE TERMS GRID -->
            <div class="form-grid-4">
                <!-- Column 1 -->
                <div class="space-y-[4px]">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Freight</label>
                        <div class="form-input-container">
                            <select name="freight_term" class="form-control-gf" x-model="form.freight_term">
                                <option value="Prepaid">Prepaid</option>
                                <option value="Collect">Collect</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">OB/L Type</label>
                        <div class="form-input-container">
                            <select name="obl_type" class="form-control-gf" x-model="form.obl_type">
                                <option value="ORIGINAL BILL OF LADING">ORIGINAL BILL OF LADING</option>
                                <option value="SEA WAYBILL">SEA WAYBILL</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Latest Gate In</label>
                        <div class="form-input-container">
                            <input type="date" name="latest_gate_in" class="form-control-gf" x-model="form.latest_gate_in">
                            <i class="fa fa-calendar text-[10px] text-gray-400 cursor-pointer" @click="$el.previousElementSibling.showPicker()"></i>
                        </div>
                    </div>
                </div>

                <!-- Column 2 -->
                <div class="space-y-[4px]">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Ship Mode</label>
                        <div class="form-input-container">
                            <select name="ship_mode" class="form-control-gf" x-model="form.ship_mode">
                                <option value="FCL">FCL</option>
                                <option value="LCL">LCL</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">
                            <input type="checkbox" name="is_obl_received" value="1" x-model="form.is_obl_received" class="mr-1"> OB/L Received
                        </label>
                        <div class="form-input-container">
                            <input type="date" name="obl_received_date" class="form-control-gf" x-model="form.obl_received_date">
                            <i class="fa fa-calendar text-[10px] text-gray-400 cursor-pointer" @click="$el.previousElementSibling.showPicker()"></i>
                        </div>
                    </div>
                </div>

                <!-- Column 3 -->
                <div class="space-y-[4px]">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Service Term</label>
                        <div class="form-input-container">
                            <select name="service_term_from_id" class="form-control-gf" style="width: 45%;" x-model="form.service_term_from_id">
                                <option value="">Select...</option>
                                @foreach($serviceTerms as $st)
                                    <option value="{{ $st->id }}">{{ $st->code }}</option>
                                @endforeach
                            </select>
                            <span class="mx-1">~</span>
                            <select name="service_term_to_id" class="form-control-gf" style="width: 45%;" x-model="form.service_term_to_id">
                                <option value="">Select...</option>
                                @foreach($serviceTerms as $st)
                                    <option value="{{ $st->id }}">{{ $st->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                   <div class="form-group-gf">
    <label class="form-label-gf">
        <input
            type="checkbox"
            name="is_released"
            value="1"
            x-model="form.is_released"
            class="mr-1"
        >
        Released Date
    </label>

    <div class="form-input-container">
        <input
            type="date"
            name="released_date"
            class="form-control-gf"
            x-model="form.released_date"
            :disabled="!form.is_released"
        >

        <i
            class="fa fa-calendar text-[10px] text-gray-400 cursor-pointer"
            @click="if (form.is_released) $el.previousElementSibling.showPicker()"
        ></i>
    </div>
</div>
                </div>

                <!-- Column 4 -->
                <div class="space-y-[4px]">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Container/Qty</label>
                        <div class="form-input-container">
                            <input type="text" class="form-control-gf" :value="form.containers.length + ' Container(s)'" readonly>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Business Referred By</label>
                        <div class="form-input-container">
                            <x-inline-select name="business_referred_by_id" :options="$agents" module="trade-partner" type="customer" x-model="form.business_referred_by_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'business_referred_by_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden per client instruction: More toggle, Place of Receipt, Place of Receipt ETD, Return Location, E-Commerce, Internal Remarks -->
            <div style="display: none;">
                <div style="margin-bottom: 10px;">
                    <button type="button" @click="showMore = !showMore" class="btn-default-gf" style="border:none; color:#4b77be; font-weight:700;">
                        <span x-text="showMore ? 'More [-]' : 'More [+]'"></span>
                    </button>
                </div>

                <!-- MORE SECTION (EXPANDABLE) -->
                <div class="form-grid-4" x-show="showMore" x-transition>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Place of Receipt</label>
                        <div class="form-input-container">
                            <x-inline-select name="receipt_id" :options="$ports" module="port" x-model="form.receipt_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'receipt_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Place of Receipt ETD</label>
                        <div class="form-input-container">
                            <input type="date" name="receipt_etd" class="form-control-gf" x-model="form.receipt_etd">
                            <i class="fa fa-calendar text-[10px] text-gray-400 cursor-pointer" @click="$el.previousElementSibling.showPicker()"></i>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Return Location</label>
                        <div class="form-input-container">
                            <x-inline-select name="return_location_id" :options="$agents" module="trade-partner" type="location" x-model="form.return_location_id" class="form-control-gf" />
                            <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'return_location_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">E-Commerce</label>
                        <div class="form-input-container" style="justify-content: flex-start;">
                            <input type="checkbox" name="is_ecommerce" value="1" x-model="form.is_ecommerce" style="width: 14px; height: 14px;">
                        </div>
                    </div>
                    <div class="form-group-gf" style="grid-column: span 4;">
                        <label class="form-label-gf">Internal Remarks</label>
                        <div class="form-input-container">
                            <textarea name="internal_remark" class="form-control-gf" x-model="form.internal_remark" style="height: 50px; resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- House B/L (HB/L) SECTION -->
    <template x-for="(hbl, index) in hbls" :key="index">
        <div class="portlet light" style="margin-top: 5px;">
            <div class="portlet-title" style="background: #f2bc00; color: #fff; cursor: pointer; min-height: 24px; padding: 2px 10px;" @click="hbl.show = !hbl.show">
                <span class="caption-subject" style="color: #fff; font-size: 11px;"><i class="fa fa-file-text-o"></i> HB/L Information <small style="color:rgba(255,255,255,0.8); margin-left: 10px; font-weight: normal;">OP : <span x-text="getUserName(form.op_id)"></span></small></span>
                <div class="actions" style="display: flex; gap: 8px; align-items: center;" @click.stop>
                    <!-- HBL Tools Inline -->
                    <button type="button" @click="toolsAction('hbl_print', index)"
                        style="display: flex; align-items: center; gap: 4px; background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(0,0,0,0.1); border-radius: 3px; padding: 2px 8px; font-size: 11px; font-weight: 600; color: #374151; cursor: pointer; height: 22px;">
                        <i class="fa fa-print" style="color: #4b5563; font-size: 10px;"></i> HBL Print
                    </button>
                    <i @click="removeHbl(index)" class="fa fa-times" style="font-size: 12px; opacity: 0.8; cursor: pointer;" title="Remove HB/L"></i>
                    <i class="fa fa-angle-down transition-transform" :class="hbl.show ? 'rotate-180' : ''" style="font-size: 12px;" @click="hbl.show = !hbl.show"></i>
                </div>
            </div>
            <div class="portlet-body" x-show="hbl.show" x-collapse>
                <!-- Reminder Section for HBL (Hidden per client instruction) -->
                <div class="memo-section" style="margin-bottom: 10px; display: none;">
                    <div class="memo-header" @click="hbl.showMemo = !hbl.showMemo">
                        <span>HBL Note / Remark</span>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <button type="button" class="btn-memo-doc" @click.stop="showDocumentModal = true">Document (<span x-text="documents.length"></span>) <i class="fa fa-external-link"></i></button>
                            <i class="fa" :class="hbl.showMemo ? 'fa-angle-up' : 'fa-angle-down'"></i>
                        </div>
                    </div>
                    <div class="memo-body" x-show="hbl.showMemo" x-collapse>
                        <div class="flex flex-col">
                            <textarea :name="'hbls['+index+'][hbl_remark]'" class="memo-content-area" placeholder="HBL remark..." x-model="hbl.hbl_remark" style="height: 80px; width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 8px; font-family: sans-serif; font-size: 11px;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-grid-4">
                    <!-- Column 1: Basic -->
                    <div class="flex flex-col">
                        <input type="hidden" :name="'hbls['+index+'][id]'" :value="hbl.id">
                        <div class="form-group-gf">
                            <label class="form-label-gf">HB/L No.</label>
                            <div class="form-input-container">
                                <input type="text" :name="'hbls['+index+'][hbl_no]'" class="form-control-gf" x-model="hbl.hbl_no">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Quotation No.</label>
                            <div class="form-input-container">
                                <select :name="'hbls['+index+'][quotation_no]'" class="form-control-gf" x-model="hbl.quotation_no" @change="loadQuoteToHbl(index)">
                                    <option value="">Select...</option>
                                    @foreach($quotations as $q)
                                        <option value="{{ $q->quote_no }}">{{ $q->quote_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Customer</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][customer_id]'" :options="$agents" module="trade-partner" type="customer" x-model="hbl.customer_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][customer_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Sales</label>
                            <div class="form-input-container">
                                <select :name="'hbls['+index+'][sales_person_id]'" class="form-control-gf" x-model="hbl.sales_person_id">
                                    <option value="">Select...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div style="height: 5px;"></div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Customs Broker</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][customs_broker_id]'" :options="$agents" module="trade-partner" type="agent" x-model="hbl.customs_broker_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][customs_broker_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Place of Delivery</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][del_id]'" :options="$ports" module="port" x-model="hbl.del_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'hbls['+index+'][del_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Delivery Loc.</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][delivery_location_id]'" :options="$agents" module="trade-partner" type="cfs" x-model="hbl.delivery_location_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][delivery_location_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Rail</label>
                            <div class="form-input-container">
                                <input type="checkbox" :name="'hbls['+index+'][is_rail]'" value="1" x-model="hbl.is_rail">
                                <select :name="'hbls['+index+'][pre_carriage_by]'" class="form-control-gf" x-model="hbl.pre_carriage_by">
                                    <option value="">Select...</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->name }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Shipper Context -->
                    <div class="flex flex-col">
                        <div class="form-group-gf">
                            <label class="form-label-gf">Shipper</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][shipper_id]'" :options="$agents" module="trade-partner" type="shipper" x-model="hbl.shipper_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][shipper_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Date of Issue</label>
                            <div class="form-input-container">
                                <input type="date" :name="'hbls['+index+'][date_of_issue]'" class="form-control-gf" x-model="hbl.date_of_issue">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Port of Discharge</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][pod_id]'" :options="$ports" module="port" x-model="hbl.pod_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'hbls['+index+'][pod_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf" style="margin-right: 15px;">PRE-CARRIAGE BY</label>
                            <div class="form-input-container">
                                <input type="text" :name="'hbls['+index+'][pre_carriage_by]'" class="form-control-gf" x-model="hbl.pre_carriage_by">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">VESSEL</label>
                            <div class="form-input-container">
                                <input type="text" :name="'hbls['+index+'][vessel_name]'" class="form-control-gf" x-model="hbl.vessel_name">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Service Term</label>
                            <div class="form-input-container">
                                <select :name="'hbls['+index+'][service_term]'" class="form-control-gf" x-model="hbl.service_term">
                                    <option value="">Select...</option>
                                    @foreach($serviceTerms as $st)
                                        <option value="{{ $st->code }}">{{ $st->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">SHIP TYPE</label>
                            <div class="form-input-container">
                                <select :name="'hbls['+index+'][ship_type]'" class="form-control-gf" x-model="hbl.ship_type">
                                    <option value="">Select...</option>
                                    <option value="FCL">FCL</option>
                                    <option value="LCL">LCL</option>
                                    <option value="FCL/LCL">FCL/LCL</option>
                                    <option value="LCL/FCL">LCL/FCL</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Released By</label>
                            <div class="form-input-container">
                                <select :name="'hbls['+index+'][freight_released_by_id]'" class="form-control-gf" x-model="hbl.freight_released_by_id">
                                    <option value="">Select...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Consignee Context -->
                    <div class="flex flex-col">
                        <div class="form-group-gf">
                            <label class="form-label-gf">Consignee</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][consignee_id]'" :options="$agents" module="trade-partner" type="consignee" x-model="hbl.consignee_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][consignee_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Port of Loading</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][pol_id]'" :options="$ports" module="port" x-model="hbl.pol_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'hbls['+index+'][pol_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Final Destination</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][fdest_id]'" :options="$ports" module="port" x-model="hbl.fdest_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'hbls['+index+'][fdest_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">VOYAGE NO</label>
                            <div class="form-input-container">
                                <input type="text" :name="'hbls['+index+'][voyage_no]'" class="form-control-gf" x-model="hbl.voyage_no">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">L/C No</label>
                            <div class="form-input-container">
                                <input type="text" :name="'hbls['+index+'][lc_no]'" class="form-control-gf" x-model="hbl.lc_no">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">CARGO TYPE</label>
                            <div class="form-input-container">
                                <select :name="'hbls['+index+'][cargo_type]'" class="form-control-gf" x-model="hbl.cargo_type">
                                    <option value="">Select...</option>
                                    <option value="GENERAL">GENERAL</option>
                                    <option value="HAZARDOUS">HAZARDOUS</option>
                                    <option value="REEFER">REEFER</option>
                                    <option value="DANGEROUS">DANGEROUS</option>
                                    <option value="OVERSIZE">OVERSIZE</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">DO Sent</label>
                            <div class="form-input-container">
                                <input type="checkbox" :name="'hbls['+index+'][is_do_sent]'" value="1" x-model="hbl.is_do_sent">
                                <input type="date" :name="'hbls['+index+'][do_sent_date]'" class="form-control-gf" x-model="hbl.do_sent_date">
                            </div>
                        </div>
                    </div>

                    <!-- Column 4: Notify Party Context -->
                    <div class="flex flex-col">
                        <div class="form-group-gf">
                            <label class="form-label-gf">Notify Party</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][notify_party_id]'" :options="$agents" module="trade-partner" type="notify" x-model="hbl.notify_party_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][notify_party_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Place of Receipt</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][receipt_id]'" :options="$ports" module="port" x-model="hbl.receipt_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'hbls['+index+'][receipt_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Final Destination</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][fdest_id]'" :options="$ports" module="port" x-model="hbl.fdest_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'hbls['+index+'][fdest_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">FREIGHT PAYABLE AT</label>
                            <div class="form-input-container">
                                <input type="text" :name="'hbls['+index+'][freight_payable_at]'" class="form-control-gf" x-model="hbl.freight_payable_at">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">INCOTERMS</label>
                            <div class="form-input-container">
                                <select :name="'hbls['+index+'][incoterms_id]'" class="form-control-gf" x-model="hbl.incoterms_id">
                                    <option value="">Select...</option>
                                    @foreach($incoterms as $inco)
                                        <option value="{{ $inco->code }}">{{ $inco->code }} - {{ $inco->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">S/C No</label>
                            <div class="form-input-container">
                                <input type="text" :name="'hbls['+index+'][sc_no]'" class="form-control-gf" x-model="hbl.sc_no">
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">SHIP MODE</label>
                            <div class="form-input-container">
                                <select :name="'hbls['+index+'][ship_mode]'" class="form-control-gf" x-model="hbl.ship_mode">
                                    <option value="">Select...</option>
                                    <option value="FCL">FCL</option>
                                    <option value="LCL">LCL</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">CY/CFS Loc.</label>
                            <div class="form-input-container">
                                <x-inline-select name="" x-bind:name="'hbls['+index+'][cfs_location_id]'" :options="$agents" module="trade-partner" type="cfs" x-model="hbl.cfs_location_id" class="form-control-gf" />
                                <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][cfs_location_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="height: 15px;"></div>

                <!-- Hidden per client instruction: Express B/L, Door Move, Referred By, OB/L Recv, FR Released, AN Sent, More section -->
                <div style="display: none;">
                    <div class="form-grid-4">
                        <!-- Column 1 -->
                        <div class="flex flex-col">
                            <div class="form-group-gf">
                                <label class="form-label-gf">Express B/L</label>
                                <div class="form-input-container" style="font-size:9px;">
                                    <input type="radio" :name="'hbls['+index+'][is_express_bl]'" value="1" x-model="hbl.is_express_bl"> Yes
                                    <input type="radio" :name="'hbls['+index+'][is_express_bl]'" value="0" x-model="hbl.is_express_bl"> No
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Door Move</label>
                                <div class="form-input-container" style="font-size:9px;">
                                    <input type="checkbox" :name="'hbls['+index+'][is_door_move]'" value="1" x-model="hbl.is_door_move"> Door Move &nbsp;
                                    <input type="checkbox" :name="'hbls['+index+'][is_customs_clear]'" value="1" x-model="hbl.is_customs_clear"> C.Clear &nbsp;
                                    <input type="checkbox" :name="'hbls['+index+'][is_customs_hold]'" value="1" x-model="hbl.is_customs_hold"> C.Hold
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Referred By</label>
                                <div class="form-input-container">
                                    <x-inline-select name="" x-bind:name="'hbls['+index+'][referred_by_id]'" :options="$agents" module="trade-partner" type="customer" x-model="hbl.referred_by_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][referred_by_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">
                                    <input type="checkbox" :name="'hbls['+index+'][is_obl_received]'" value="1" x-model="hbl.is_obl_received"> OB/L Recv.
                                </label>
                                <div class="form-input-container">
                                    <input type="date" :name="'hbls['+index+'][obl_received_date]'" class="form-control-gf" x-model="hbl.obl_received_date">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">
                                    <input type="checkbox" :name="'hbls['+index+'][is_fr_released]'" value="1" x-model="hbl.is_fr_released"> FR Released
                                </label>
                                <div class="form-input-container">
                                    <input type="date" :name="'hbls['+index+'][fr_released_date]'" class="form-control-gf" x-model="hbl.fr_released_date">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">
                                    <input type="checkbox" :name="'hbls['+index+'][is_an_sent]'" value="1" x-model="hbl.is_an_sent"> AN Sent
                                </label>
                                <div class="form-input-container">
                                    <input type="date" :name="'hbls['+index+'][an_sent_date]'" class="form-control-gf" x-model="hbl.an_sent_date">
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col"></div>
                        <div class="flex flex-col"></div>
                        <div class="flex flex-col">
                            <div style="flex-grow: 1;"></div>
                            <div class="form-group-gf" style="justify-content: flex-end; margin-top: 10px;">
                                <button type="button" @click="hbl.showMore = !hbl.showMore" class="btn-default-gf" style="border:none; color:#00827f; font-weight:700; height:18px; padding:0;">
                                    More <i class="fa" :class="hbl.showMore ? 'fa-minus-square' : 'fa-plus-square'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- More Section for HBL -->
                    <div x-show="hbl.showMore" x-transition style="margin-top: 5px; padding-top: 5px; border-top: 1px solid #eee;">
                        <div class="form-grid-4">
                            <div class="form-group-gf">
                                <label class="form-label-gf">Name Account</label>
                                <div class="form-input-container">
                                    <input type="text" :name="'hbls['+index+'][name_account]'" class="form-control-gf" x-model="hbl.name_account">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Group Comm</label>
                                <div class="form-input-container">
                                    <input type="text" :name="'hbls['+index+'][group_comm]'" class="form-control-gf" x-model="hbl.group_comm">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Line Code</label>
                                <div class="form-input-container">
                                    <input type="text" :name="'hbls['+index+'][line_code]'" class="form-control-gf" x-model="hbl.line_code">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">E-Commerce</label>
                                <div class="form-input-container">
                                    <input type="checkbox" :name="'hbls['+index+'][is_ecommerce]'" value="1" x-model="hbl.is_ecommerce">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Customs Doc</label>
                                <div class="form-input-container">
                                    <input type="checkbox" :name="'hbls['+index+'][is_customs_doc]'" value="1" x-model="hbl.is_customs_doc">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div class="flex justify-end" style="margin-top: 5px;">
        <button type="button" @click="addHbl" class="btn-freightx" style="background:#f2bc00; padding: 4px 15px; font-size: 11px; border-radius: 2px;"><i class="fa fa-plus"></i> ADD HB/L</button>
    </div>
</div>
