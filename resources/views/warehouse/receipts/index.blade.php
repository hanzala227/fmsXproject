<x-layout>
    @push('styles')
    <x-form-styles />
    @endpush

    <div class="page-content" x-data="receiptsModule()">

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Warehouse <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Warehouse Receipt / New</span></li>
            </ul>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h1 class="caption-subject" style="font-size: 18px;">Warehouse Receipt Entry</h1>
            <div style="display: flex; gap: 8px;">
                <button @click="saveReceipt" class="btn-freightx"><i class="fa fa-save"></i> SAVE RECEIPT</button>
                <a href="/warehouse/receipts" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 10px;">
            
            <div class="space-y-5">
                <!-- Transaction Header -->
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-file-text-o"></i> Transaction Header</span>
                    </div>
                    <div class="portlet-body">
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label class="form-label-gf">WH Receipt No <span style="color:red;">*</span></label>
                                <input type="text" x-model="header.receipt_no" class="form-control-gf" style="color: #4b77be; font-weight: 700; background: #f9fafb;" placeholder="AUTO-GEN">
                            </div>
                            <div>
                                <label class="form-label-gf">Received Date/Time <span style="color:red;">*</span></label>
                                <input type="datetime-local" x-model="header.received_at" class="form-control-gf">
                            </div>
                            <div>
                                <label class="form-label-gf">Received By</label>
                                <select x-model="header.received_by" class="form-control-gf">
                                    <option>Admin User</option>
                                    <option>Warehouse Staff A</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label class="form-label-gf">Truck B/L No.</label>
                                <input type="text" x-model="header.truck_bl_no" class="form-control-gf">
                            </div>
                            <div>
                                <label class="form-label-gf">Warehouse Location</label>
                                <input type="text" x-model="header.location" class="form-control-gf" placeholder="Zone A-01">
                            </div>
                            <div>
                                <label class="form-label-gf">Maker (Customer)</label>
                                <select x-model="header.maker" class="form-control-gf">
                                    <option>Please Select...</option>
                                    <option>Toyoto Global</option>
                                    <option>Honda Logistics</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label class="form-label-gf">Shipper</label>
                                <input type="text" x-model="header.shipper" class="form-control-gf" placeholder="Search Customer...">
                            </div>
                            <div>
                                <label class="form-label-gf">Consignee</label>
                                <input type="text" x-model="header.consignee" class="form-control-gf" placeholder="Search Customer...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cargo Items -->
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-cubes"></i> Cargo Dimension & Weight Items</span>
                        <button @click="addItem" class="btn-freightx" style="padding: 2px 10px; font-size: 10px;"><i class="fa fa-plus"></i> ADD ROW</button>
                    </div>
                    <div class="portlet-body" style="padding: 0;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Marks & Nos.</th>
                                    <th>Description (SKU)</th>
                                    <th style="width: 60px;">Qty</th>
                                    <th style="width: 80px;">Unit</th>
                                    <th style="width: 50px;">L</th>
                                    <th style="width: 50px;">W</th>
                                    <th style="width: 50px;">H</th>
                                    <th style="width: 100px;">Volume</th>
                                    <th style="width: 80px;">G.W.</th>
                                    <th style="width: 30px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td><input type="text" x-model="item.marks" class="form-control-gf"></td>
                                        <td><input type="text" x-model="item.description" class="form-control-gf"></td>
                                        <td><input type="number" x-model="item.qty" class="form-control-gf" style="text-align: center;"></td>
                                        <td>
                                            <select x-model="item.unit" class="form-control-gf">
                                                <option>PCS</option><option>PLT</option><option>BOX</option>
                                            </select>
                                        </td>
                                        <td><input type="number" x-model="item.l" @input="calculateVol(index)" class="form-control-gf" style="text-align: center;"></td>
                                        <td><input type="number" x-model="item.w" @input="calculateVol(index)" class="form-control-gf" style="text-align: center;"></td>
                                        <td><input type="number" x-model="item.h" @input="calculateVol(index)" class="form-control-gf" style="text-align: center;"></td>
                                        <td style="font-weight: 700; color: #4b77be;" x-text="item.vol"></td>
                                        <td><input type="number" x-model="item.gw" class="form-control-gf" style="text-align: right;"></td>
                                        <td style="text-align: center;"><i @click="removeItem(index)" class="fa fa-times" style="color:#888; cursor:pointer;"></i></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <!-- Freight Billing -->
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-money"></i> Freight Billing</span>
                    </div>
                    <div class="portlet-body">
                        <div style="margin-bottom: 12px;">
                            <label class="form-label-gf">Charge Status</label>
                            <div style="display: flex; gap: 5px;">
                                <button @click="header.freight_charge_type = 'Prepaid'" :style="header.freight_charge_type === 'Prepaid' ? 'background:#4b77be; color:#fff;' : ''" class="btn-default-gf" style="flex:1;">PREPAID</button>
                                <button @click="header.freight_charge_type = 'Collect'" :style="header.freight_charge_type === 'Collect' ? 'background:#4b77be; color:#fff;' : ''" class="btn-default-gf" style="flex:1;">COLLECT</button>
                            </div>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label class="form-label-gf">Amount</label>
                            <input type="text" x-model="header.amount" class="form-control-gf" style="color: #36c6d3; font-weight: 700; font-size: 14px; height: 32px;" placeholder="$ 0.00">
                        </div>
                        <div>
                            <label class="form-label-gf">Check #</label>
                            <input type="text" x-model="header.check_no" class="form-control-gf" placeholder="Check Number">
                        </div>
                    </div>
                </div>

                <!-- Classification -->
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-shield"></i> Compliance</span>
                    </div>
                    <div class="portlet-body">
                        <div style="margin-bottom: 15px;">
                            <label class="form-label-gf">Cargo Category</label>
                            <select x-model="header.cargo_type" class="form-control-gf">
                                <option>General Cargo</option>
                                <option>Automobile</option>
                                <option>Perishable</option>
                            </select>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 11px;">
                                <input type="checkbox" x-model="header.hazardous"> <span style="font-weight:700; color:#d05454;">Hazardous Goods (DG)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 11px;">
                                <input type="checkbox" x-model="header.heat_treated"> <span>Heat Treated Pallets</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function receiptsModule() {
            return {
                header: {
                    receipt_no: '',
                    received_at: '',
                    received_by: 'Admin User',
                    truck_bl_no: '',
                    location: '',
                    maker: '',
                    shipper: '',
                    consignee: '',
                    freight_charge_type: 'Prepaid',
                    amount: '',
                    check_no: '',
                    cargo_type: 'General Cargo',
                    hazardous: false,
                    heat_treated: true
                },
                items: [
                    { marks: 'AUTO/9021', description: 'CAR PARTS - ENGINE', qty: 1, unit: 'PCS', l: 120, w: 120, h: 80, vol: '1.152 CBM', gw: 450 }
                ],
                addItem() {
                    this.items.push({ marks: '', description: '', qty: 1, unit: 'PCS', l: 0, w: 0, h: 0, vol: '0.000 CBM', gw: 0 });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                calculateVol(index) {
                    const item = this.items[index];
                    if (item.l && item.w && item.h) {
                        const total = (item.l * item.w * item.h) / 1000000;
                        item.vol = total.toFixed(3) + ' CBM';
                    }
                },
                saveReceipt() {
                    alert('Warehouse Receipt Saved Successfully!');
                }
            }
        }
    </script>
</x-layout>
