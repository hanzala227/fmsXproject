<!-- Generate Invoice Preview Modal -->
<div x-show="showInvoiceModal" class="modal-overlay" style="display:none; z-index: 9999;" x-cloak x-transition>
    <div class="modal-container" style="max-width: 800px; width: 95%; background: #fff; border-radius: 4px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); overflow: hidden; border: 1px solid #cbd5e1;" @click.away="showInvoiceModal = false">
        <!-- Modal Header -->
        <div class="modal-header" style="background: #2c3e50; color: #fff; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-file-text-o" style="color: #60a5fa;"></i> Invoice Preview &amp; Generation
            </span>
            <i class="fa fa-times cursor-pointer hover:text-gray-300" style="font-size: 14px; cursor: pointer;" @click="showInvoiceModal = false"></i>
        </div>

        <!-- Modal Body -->
        <div class="modal-body" style="padding: 16px; background: #f8fafc; font-size: 11px;">
            <!-- Key Financial Fields Row -->
            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; background: #ffffff; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 14px; align-items: flex-end;">
                <div>
                    <label style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Fin. Date</label>
                    <input type="date" class="form-control-gf" style="height: 26px; font-size: 11px; padding: 2px 5px; width: 100%;" x-model="invoiceModalData.fin_date">
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Due Date</label>
                    <input type="date" class="form-control-gf" style="height: 26px; font-size: 11px; padding: 2px 5px; width: 100%;" x-model="invoiceModalData.due_date">
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Currency</label>
                    <input type="text" class="form-control-gf" style="height: 26px; font-size: 11px; padding: 2px 6px; width: 100%; background: #f1f5f9; font-weight: 600;" x-model="invoiceModalData.currency" readonly>
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Type</label>
                    <select class="form-control-gf" style="height: 26px; font-size: 11px; padding: 2px 4px; width: 100%;" x-model="invoiceModalData.type">
                        <option value="INV">INV Typ</option>
                        <option value="DRN">DRN Typ</option>
                        <option value="CRN">CRN Typ</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Manif. Amount</label>
                    <input type="text" class="form-control-gf" style="height: 26px; font-size: 11px; padding: 2px 6px; width: 100%; text-align: right; background: #f8fafc; font-weight: 600;" :value="Number(invoiceModalData.manif_amt || 0).toFixed(2)" readonly>
                </div>
                <div>
                    <label style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Loc. Amount</label>
                    <input type="text" class="form-control-gf" style="height: 26px; font-size: 11px; padding: 2px 6px; width: 100%; text-align: right; font-weight: 700; color: #1e3a8a; background: #f8fafc;" :value="Number(invoiceModalData.loc_amt || 0).toFixed(2)" readonly>
                </div>
            </div>

            <!-- Internal Ref & Details Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <!-- Bill To Party Card -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; display: flex; flex-direction: column;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa fa-building-o" style="color: #64748b;"></i> Bill To Party</span>
                        <span style="font-size: 9.5px; color: #2563eb; background: #eff6ff; padding: 1px 6px; border-radius: 3px; font-weight: 600;" x-text="invoiceModalData.charges_count + ' charge(s) selected'"></span>
                    </div>
                    <div style="font-size: 11px; color: #334155; line-height: 1.4; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 3px; padding: 8px; min-height: 65px; white-space: pre-line;" x-text="invoiceModalData.party_info || 'No customer selected'"></div>
                </div>

                <!-- Internal Ref & Remarks -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; display: flex; flex-direction: column; gap: 8px;">
                    <div>
                        <label style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Internal Ref. (e.g. DRN / Job Ref)</label>
                        <input type="text" class="form-control-gf" style="height: 25px; font-size: 11px; padding: 2px 6px; width: 100%;" placeholder="e.g. Agent's DRN" x-model="invoiceModalData.internal_ref">
                    </div>
                    <div>
                        <label style="font-size: 10px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Remarks</label>
                        <textarea class="form-control-gf" style="width: 100%; height: 42px; font-size: 10.5px; padding: 4px 6px; resize: none;" placeholder="Invoice remarks..." x-model="invoiceModalData.remarks"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer with Simple Actions -->
        <div class="modal-footer" style="padding: 10px 16px; background: #f1f5f9; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn-default-gf" style="padding: 5px 14px; font-size: 11px; cursor: pointer;" @click="showInvoiceModal = false">
                Cancel
            </button>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-freightx" style="background: #2563eb; color: #fff; padding: 5px 16px; font-size: 11px; font-weight: 600; border-radius: 3px; border: none; cursor: pointer; display: flex; align-items: center; gap: 5px;" @click="previewInvoiceModal()">
                    <i class="fa fa-eye"></i> Preview Invoice
                </button>
                <button type="button" class="btn-freightx" style="background: #16a34a; color: #fff; padding: 5px 18px; font-size: 11px; font-weight: 600; border-radius: 3px; border: none; cursor: pointer; display: flex; align-items: center; gap: 5px;" @click="processInvoiceModal()">
                    <i class="fa fa-check-circle"></i> Generate Invoice
                </button>
            </div>
        </div>
    </div>
</div>
