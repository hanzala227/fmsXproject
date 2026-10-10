@php
    $loadingVar = $loadingVar ?? 'isAiProcessing';
    $uploadMethod = $uploadMethod ?? 'handleAiUpload';
    $triggerMethod = $triggerMethod ?? '$refs.aiFileInput.click()';
    $fileInputRef = $fileInputRef ?? 'x-ref="aiFileInput"';
    $errorVar = $errorVar ?? 'aiError';
    $fileInputId = $fileInputId ?? '';
@endphp

        <!-- AI Auto-Fill Modal (Advanced Split Pane) -->
        <div x-show="showAiModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;" @click.self="!{{ $loadingVar }} && !aiPreviewMode ? showAiModal = false : null" x-transition>
            <div class="modal-container" style="max-width: 1100px; width: 95vw; padding: 0; display: flex; flex-direction: column; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div class="modal-header" style="background:#337ab7; color:white; border-radius: 8px 8px 0 0; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight:700; font-size: 16px;"><i class="fa fa-file-text-o"></i> AI Document Extraction</span>
                    <i class="fa fa-times cursor-pointer" @click="!{{ $loadingVar }} ? showAiModal = false : null" style="color:white; font-size: 16px;"></i>
                </div>
                
                <div class="modal-body" style="padding: 20px; background: #fff;" x-data="{ fileName: '' }">
                    <div x-show="typeof {{ $errorVar }} !== 'undefined' && {{ $errorVar }}" style="background: #fef2f2; color: #dc2626; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px; border: 1px solid #f87171;">
                        <i class="fa fa-exclamation-triangle"></i> <span x-text="{{ $errorVar }}"></span>
                    </div>

                    <!-- State 1: Upload (Not Preview Mode) -->
                    <div x-show="!aiPreviewMode">
                        <p style="margin-bottom: 15px; font-size: 14px; color: #555;">Upload a Bill of Lading, Invoice, or Packing List (PDF, Image, CSV). Our AI will analyze the document and automatically fill the form.</p>
                        <label class="ai-dropzone">
                            <i class="fa fa-cloud-upload ai-dropzone-icon"></i>
                            <div class="ai-dropzone-title">Click to browse or drag file here</div>
                            <div class="ai-dropzone-desc">Supports PDF, PNG, JPG, CSV, TXT</div>
                            <input {!! $fileInputRef !!} {!! $fileInputId ? 'id="'.$fileInputId.'"' : '' !!} type="file" style="display: none;" @change="{{ $uploadMethod }}" accept=".pdf,.txt,.csv,.jpg,.jpeg,.png">
                        </label>
                    </div>
                    
                    <!-- State 2: Review (Preview Mode) -->
                    <div x-show="aiPreviewMode" class="ai-split-layout" style="display: none;">
                        <!-- Left Pane: Document Preview -->
                        <div class="ai-pane-left" style="padding:0; border:1px solid #cbd5e1; background: #334155; overflow: hidden;">
                            <div style="width: 100%; height: 100%; position: relative; display: flex; align-items: center; justify-content: center;">
                                <template x-if="documentPreviewUrl">
                                    <iframe :src="documentPreviewUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH'" style="width: 100%; height: 100%; border: none; pointer-events: none;"></iframe>
                                </template>
                                <template x-if="!documentPreviewUrl">
                                    <div style="display:flex; align-items:center; justify-content:center; height:100%; background: url('https://www.transparenttextures.com/patterns/cubes.png') #f8fafc; width: 100%;">
                                        <i class="fa fa-file-text-o" style="font-size: 80px; color: #cbd5e1;"></i>
                                    </div>
                                </template>
                                
                                <div x-show="{{ $loadingVar }}" class="ai-scanner-line-large" style="display: none;"></div>
                                <div x-show="{{ $loadingVar }}" class="ai-scanner-badge" style="display: none;">
                                    <i class="fa fa-magic"></i> AI Analyzing...
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Pane: Extracted Data Review -->
                        <div class="ai-pane-right">
                            <div class="ai-table-header">
                                <span>Shipment Information</span>
                            </div>
                            
                            <div class="ai-table-container">
                                <table class="ai-data-table">
                                    <thead>
                                        <tr>
                                            <th>Field Name</th>
                                            <th>Extracted Data</th>
                                            <th style="text-align: center; width: 60px;">Select</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Loading State -->
                                        <tr x-show="{{ $loadingVar }}">
                                            <td colspan="3" style="text-align: center; padding: 40px; color: #64748b;">
                                                <i class="fa fa-circle-o-notch fa-spin" style="font-size: 24px; margin-bottom: 10px; color: #3b82f6;"></i>
                                                <div>Extracting fields from document...</div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Done State -->
                                        <template x-for="(item, index) in extractedData" :key="index">
                                            <tr>
                                                <td x-text="item.label" style="font-weight: 500;"></td>
                                                <td><span class="ai-extracted-value" x-text="item.displayValue"></span></td>
                                                <td style="text-align: center;">
                                                    <input type="checkbox" class="ai-checkbox" x-model="item.selected">
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="!{{ $loadingVar }} && extractedData.length === 0">
                                            <td colspan="3" style="text-align: center; color: #94a3b8; padding: 20px;">No fields extracted.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer" style="padding: 15px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 8px 8px;">
                    <button type="button" class="btn-default-gf" @click="showAiModal = false; aiPreviewMode = false;" :disabled="{{ $loadingVar }}">Cancel</button>
                    <button type="button" class="btn-freightx" style="background:#337ab7; color:white; border-color:#286090;" @click="aiPreviewMode ? applyAiData() : {{ $triggerMethod }}" :disabled="{{ $loadingVar }}">
                        <i class="fa" :class="aiPreviewMode ? 'fa-check' : 'fa-cogs'"></i> <span x-text="aiPreviewMode ? 'Create Shipment' : 'Extract Data'"></span>
                    </button>
                </div>
            </div>
        </div>
