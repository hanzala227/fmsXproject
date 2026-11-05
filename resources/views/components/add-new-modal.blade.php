<div x-data="addNewModal()" 
     x-show="isOpen" 
     style="display: none;" 
     class="modal-overlay">
    
    <div class="modal-container" @click.away="closeModal()">
        <div class="modal-header">
            <span x-text="title">Add New Record</span>
            <button type="button" @click="closeModal()" style="background:transparent; border:none; cursor:pointer; font-size:16px;">&times;</button>
        </div>
        
        <form @submit.prevent="submitForm">
            <div class="modal-body" style="padding: 15px; background: #fff;">
                
                <!-- Customer Fields -->
                <template x-if="module === 'trade-partner'">
                    <div>
                        <div class="form-group-gf" style="margin-bottom: 10px;">
                            <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px;">Type</label>
                            <select x-model="formData.type" class="form-control-gf" required style="height:24px; font-size:11px;">
                                <option value="CS">Customer</option>
                                <option value="FA">Forwarding Agent</option>
                                <option value="CR">Carrier</option>
                                <option value="PR">Oversea Agent</option>
                            </select>
                        </div>
                        <div class="form-group-gf" style="margin-bottom: 10px;">
                            <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px;">Name</label>
                            <input type="text" x-model="formData.name" class="form-control-gf" required style="height:24px; font-size:11px;">
                        </div>
                        <div class="form-group-gf" style="margin-bottom: 10px;">
                            <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px;">Address</label>
                            <input type="text" x-model="formData.local_address" class="form-control-gf" style="height:24px; font-size:11px;">
                        </div>
                    </div>
                </template>

                <!-- Port Fields -->
                <template x-if="module === 'port'">
                    <div>
                        <div class="form-group-gf" style="margin-bottom: 10px;">
                            <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px;">Port Name</label>
                            <input type="text" x-model="formData.name" class="form-control-gf" required style="height:24px; font-size:11px;">
                        </div>
                        <div class="form-group-gf" style="margin-bottom: 10px;">
                            <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px;">Port Code</label>
                            <input type="text" x-model="formData.code" class="form-control-gf" style="height:24px; font-size:11px;">
                        </div>
                    </div>
                </template>

                <!-- Vessel Fields -->
                <template x-if="module === 'vessel'">
                    <div>
                        <div class="form-group-gf" style="margin-bottom: 10px;">
                            <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px;">Vessel Name</label>
                            <input type="text" x-model="formData.name" class="form-control-gf" required style="height:24px; font-size:11px;">
                        </div>
                        <div class="form-group-gf" style="margin-bottom: 10px;">
                            <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px;">IMO / Call Sign</label>
                            <input type="text" x-model="formData.imo" class="form-control-gf" style="height:24px; font-size:11px;">
                        </div>
                    </div>
                </template>
                
                <div x-show="errorMessage" x-text="errorMessage" style="color:red; font-size:11px; margin-top:10px;"></div>
            </div>
            
            <div class="modal-footer" style="padding: 10px 15px; text-align: right; border-top: 1px solid #eee; background: #f9f9f9;">
                <button type="button" @click="closeModal()" class="btn-default-gf" style="padding:4px 12px; margin-right:5px;">Cancel</button>
                <button type="submit" class="btn-freightx" :disabled="isLoading" style="padding:4px 12px;">
                    <span x-show="!isLoading">Save</span>
                    <span x-show="isLoading">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
window.dynamicOptions = window.dynamicOptions || {};

function addNewModal() {
    return {
        isOpen: false,
        module: '',
        targetModel: '',
        targetSelect: '',
        title: '',
        isLoading: false,
        errorMessage: '',
        formData: {},
        
        init() {
            window.addEventListener('open-add-new-modal', (e) => {
                this.module = e.detail.module;
                this.targetModel = e.detail.targetModel;
                this.targetSelect = e.detail.targetSelect;
                this.title = 'Add New ' + (this.module === 'trade-partner' ? 'Customer' : this.module.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()));
                this.formData = {};
                this.errorMessage = '';
                this.isOpen = true;
                
                // Initialize default arrays
                if (!window.dynamicOptions[this.module]) {
                    window.dynamicOptions[this.module] = [];
                }
            });
        },
        
        closeModal() {
            this.isOpen = false;
        },
        
        async submitForm() {
            this.isLoading = true;
            this.errorMessage = '';
            
            let endpoint = '';
            if (this.module === 'trade-partner') {
                endpoint = '/api/trade-partners';
            } else if (this.module === 'port') {
                endpoint = '/api/ports';
            } else if (this.module === 'vessel') {
                endpoint = '/api/vessels';
            }
            
            try {
                // Ensure CSRF token is sent
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // It should return the new record ID and Name
                    const newId = data.id || (data.record && data.record.id);
                    const newName = data.name || (data.record && data.record.name);
                    
                    if (newId) {
                        // Add to dynamic options so the select shows it
                        window.dynamicOptions[this.module].push({ id: newId, name: newName });
                        
                        // Try to update x-model directly if it's accessible via window/eval (tricky but we can trigger a custom event back)
                        window.dispatchEvent(new CustomEvent('new-record-created', {
                            detail: {
                                module: this.module,
                                model: this.targetModel,
                                selectName: this.targetSelect,
                                id: newId,
                                name: newName
                            }
                        }));
                        
                        // Auto update the specific select element if needed
                        setTimeout(() => {
                            const selectEl = document.querySelector(`select[name="${this.targetSelect}"]`);
                            if (selectEl && selectEl.__x) {
                                // Let Alpine know the value changed
                                selectEl.value = newId;
                                selectEl.dispatchEvent(new Event('change'));
                            }
                        }, 50);
                    }
                    
                    this.closeModal();
                } else {
                    this.errorMessage = data.message || 'An error occurred during save.';
                }
            } catch (err) {
                console.error(err);
                this.errorMessage = 'Network error. Please try again.';
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
