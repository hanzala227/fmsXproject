@php
    $loadingVar = $loadingVar ?? 'isAiProcessing';
    $uploadMethod = $uploadMethod ?? 'handleAiUpload';
    $triggerMethod = $triggerMethod ?? '$refs.aiFileInput.click()';
    $fileInputRef = $fileInputRef ?? 'x-ref="aiFileInput"';
    $errorVar = $errorVar ?? 'aiError';
    $fileInputId = $fileInputId ?? 'frexoAiFileInput';
@endphp

<!-- Frexo AI Document Extraction Modal (Decent Blue Theme) -->
<div x-show="showAiModal"
     class="frexo-modal-overlay"
     style="display: none;"
     @click.self="!{{ $loadingVar }} && !aiPreviewMode ? (showAiModal = false) : null"
     x-transition:enter="frexo-transition-enter"
     x-transition:enter-start="frexo-transition-enter-start"
     x-transition:enter-end="frexo-transition-enter-end"
     x-transition:leave="frexo-transition-leave"
     x-transition:leave-start="frexo-transition-leave-start"
     x-transition:leave-end="frexo-transition-leave-end">

    <div class="frexo-modal-container" @click.stop>
        
        <!-- Top Accent Bar (Theme Blue) -->
        <div class="frexo-top-bar"></div>

        <!-- Header -->
        <div class="frexo-modal-header">
            <div class="frexo-brand-wrap">
                <div class="frexo-logo-box">
                    <i class="fa fa-bolt"></i>
                </div>
                <div class="frexo-brand-info">
                    <div class="frexo-brand-title-row">
                        <span class="frexo-brand-name">Frexo AI</span>
                        <span class="frexo-brand-badge">Copilot & Verification Engine</span>
                    </div>
                    <p class="frexo-brand-tagline">AI-assisted document parsing &bull; Verified review before form population</p>
                </div>
            </div>

            <div class="frexo-header-right">
                <!-- Live Processing Indicator -->
                <div x-show="{{ $loadingVar }}" class="frexo-status-live" style="display: none;">
                    <i class="fa fa-spinner fa-spin"></i>
                    <span>Analyzing Document...</span>
                </div>

                <!-- Close Button -->
                <button type="button" 
                        class="frexo-close-btn" 
                        @click="!{{ $loadingVar }} ? (showAiModal = false) : null"
                        :disabled="{{ $loadingVar }}"
                        title="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="frexo-modal-body" x-data="{ isDragging: false }">
            
            <!-- Error Banner -->
            <div x-show="typeof {{ $errorVar }} !== 'undefined' && {{ $errorVar }}" 
                 class="frexo-error-alert" 
                 style="display: none;">
                <div class="frexo-error-icon"><i class="fa fa-exclamation-triangle"></i></div>
                <div class="frexo-error-content">
                    <strong>Extraction Notice</strong>
                    <div x-text="{{ $errorVar }}"></div>
                </div>
                <button type="button" class="frexo-error-dismiss" @click="{{ $errorVar }} = ''">&times;</button>
            </div>

            <!-- STATE 1: Upload (When not in preview mode) -->
            <div x-show="!aiPreviewMode" class="frexo-upload-state">
                
                <!-- Info Notice -->
                <div class="frexo-info-banner">
                    <i class="fa fa-info-circle"></i>
                    <span>Upload a Bill of Lading, Air Waybill, Commercial Invoice, or Packing List. Frexo AI Copilot extracts ~90% of data in seconds for your verification review.</span>
                </div>

                <!-- Dropzone Box -->
                <label class="frexo-dropzone" 
                       :class="{ 'is-dragging': isDragging }"
                       @dragover.prevent="isDragging = true"
                       @dragleave.prevent="isDragging = false"
                       @drop.prevent="isDragging = false; 
                                      if ($event.dataTransfer && $event.dataTransfer.files.length) {
                                          const input = document.getElementById('{{ $fileInputId }}') || $refs.aiFileInput;
                                          if (input) {
                                              input.files = $event.dataTransfer.files;
                                              input.dispatchEvent(new Event('change', { bubbles: true }));
                                          }
                                      }">
                    
                    <input {!! $fileInputRef !!} 
                           id="{{ $fileInputId }}" 
                           type="file" 
                           multiple
                           style="display: none;" 
                           @change="{{ $uploadMethod }}" 
                           accept=".pdf,.txt,.csv,.jpg,.jpeg,.png,.webp">

                    <div class="frexo-dropzone-content">
                        <div class="frexo-drop-icon">
                            <i class="fa fa-cloud-upload"></i>
                        </div>
                        <h4 class="frexo-drop-title">
                            <span>Drop shipping document(s) here, or</span> 
                            <span class="frexo-browse-link">Browse files</span>
                        </h4>
                        <p class="frexo-drop-desc">Upload multiple documents in one shot (e.g. Invoice + Packing List + B/L). Supports PDF, JPG, PNG, CSV.</p>
                        
                        <div class="frexo-formats-row">
                            <span class="frexo-format-pill"><i class="fa fa-files-o"></i> Multi-Doc Single Shot</span>
                            <span class="frexo-format-pill"><i class="fa fa-file-pdf-o"></i> PDF Documents</span>
                            <span class="frexo-format-pill"><i class="fa fa-file-image-o"></i> JPG / PNG Scans</span>
                        </div>
                    </div>
                </label>

                <!-- 3 Capability Cards (Simple, Blue Theme, No Emojis) -->
                <div class="frexo-cards-row">
                    <div class="frexo-card">
                        <div class="frexo-card-icon"><i class="fa fa-bolt"></i></div>
                        <div class="frexo-card-content">
                            <strong>Instant Field Extraction</strong>
                            <span>Detects MBL, HBL, Ports, Vessels, Containers, Commodities, and Dates.</span>
                        </div>
                    </div>
                    <div class="frexo-card">
                        <div class="frexo-card-icon"><i class="fa fa-crosshairs"></i></div>
                        <div class="frexo-card-content">
                            <strong>Database Partner Matching</strong>
                            <span>Matches Customers, Shippers, Consignees, and Carriers with existing CRM records.</span>
                        </div>
                    </div>
                    <div class="frexo-card">
                        <div class="frexo-card-icon"><i class="fa fa-shield"></i></div>
                        <div class="frexo-card-content">
                            <strong>In-Memory Processing</strong>
                            <span>Your documents are processed securely in-memory with strict confidentiality.</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- STATE 2: Review (Preview Mode - Split Pane) -->
            <div x-show="aiPreviewMode" class="frexo-split-layout" style="display: none;">
                
                <!-- Left Pane: Document Preview -->
                <div class="frexo-pane-left">
                    <div class="frexo-pane-toolbar">
                        <div class="frexo-pane-title">
                            <i class="fa fa-file-text-o"></i>
                            <span>Document Preview</span>
                            <span x-show="typeof uploadedDocs !== 'undefined' && uploadedDocs && uploadedDocs.length > 1" 
                                  class="frexo-doc-count-badge"
                                  x-text="(uploadedDocs ? uploadedDocs.length : 1) + ' files loaded'"></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <!-- Prev / Next file quick navigation -->
                            <div x-show="typeof uploadedDocs !== 'undefined' && uploadedDocs && uploadedDocs.length > 1" 
                                 class="frexo-doc-nav-arrows">
                                <button type="button" 
                                        class="frexo-nav-arrow-btn" 
                                        @click="if (activeDocIndex > 0) { activeDocIndex--; documentPreviewUrl = uploadedDocs[activeDocIndex].url; }"
                                        :disabled="activeDocIndex === 0"
                                        title="Previous Document">
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                                <span class="frexo-nav-counter" x-text="(activeDocIndex + 1) + ' / ' + (uploadedDocs ? uploadedDocs.length : 1)"></span>
                                <button type="button" 
                                        class="frexo-nav-arrow-btn" 
                                        @click="if (activeDocIndex < uploadedDocs.length - 1) { activeDocIndex++; documentPreviewUrl = uploadedDocs[activeDocIndex].url; }"
                                        :disabled="!uploadedDocs || activeDocIndex >= uploadedDocs.length - 1"
                                        title="Next Document">
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>

                            <button type="button" 
                                    class="frexo-btn-subtle" 
                                    @click="aiPreviewMode = false; documentPreviewUrl = null; if(typeof uploadedDocs !== 'undefined' && uploadedDocs) uploadedDocs.forEach(d => URL.revokeObjectURL(d.url)); uploadedDocs = [];" 
                                    :disabled="{{ $loadingVar }}"
                                    title="Select different documents">
                                <i class="fa fa-repeat"></i> Change Files
                            </button>
                        </div>
                    </div>

                    <!-- Multi-document Overlapping Deck (Overlap UI/UX) -->
                    <div x-show="typeof uploadedDocs !== 'undefined' && uploadedDocs && uploadedDocs.length > 1" 
                         class="frexo-overlapping-deck-container">
                        <div class="frexo-deck-track">
                            <template x-for="(doc, idx) in (uploadedDocs || [])" :key="idx">
                                <div class="frexo-deck-card"
                                     :class="{ 'is-active': activeDocIndex === idx }"
                                     :style="'z-index: ' + (activeDocIndex === idx ? 45 : (10 + idx)) + ';'"
                                     @click="activeDocIndex = idx; documentPreviewUrl = doc.url;"
                                     :title="doc.name + ' (' + doc.size + ') - Click to view'">
                                    
                                    <div class="frexo-card-tab-accent"></div>

                                    <div class="frexo-card-inner">
                                        <span class="frexo-card-num" x-text="idx + 1"></span>

                                        <i class="frexo-card-icon fa" 
                                           :class="doc.isPdf ? 'fa-file-pdf-o is-pdf' : 'fa-file-image-o is-img'"></i>

                                        <div class="frexo-card-meta">
                                            <div class="frexo-card-filename" x-text="doc.name"></div>
                                            <div class="frexo-card-filesize" x-text="doc.size"></div>
                                        </div>

                                        <div x-show="activeDocIndex === idx" class="frexo-card-active-dot" title="Currently viewing">
                                            <i class="fa fa-eye"></i>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Active Document Info Strip -->
                    <template x-if="typeof uploadedDocs !== 'undefined' && uploadedDocs && uploadedDocs[activeDocIndex]">
                        <div class="frexo-active-doc-bar">
                            <div class="frexo-active-doc-left">
                                <i class="fa" :class="uploadedDocs[activeDocIndex]?.isPdf ? 'fa-file-pdf-o' : 'fa-file-image-o'" 
                                   :style="uploadedDocs[activeDocIndex]?.isPdf ? 'color: #dc2626;' : 'color: #0284c7;'"></i>
                                <span class="frexo-active-doc-name" x-text="uploadedDocs[activeDocIndex]?.name || ''"></span>
                                <span class="frexo-active-doc-size" x-text="uploadedDocs[activeDocIndex]?.size ? '(' + uploadedDocs[activeDocIndex].size + ')' : ''"></span>
                            </div>
                            <template x-if="documentPreviewUrl">
                                <a :href="documentPreviewUrl" target="_blank" class="frexo-open-external-btn" title="Open document in new window">
                                    <i class="fa fa-external-link"></i> Open Full
                                </a>
                            </template>
                        </div>
                    </template>

                    <div class="frexo-preview-box">
                        <template x-if="documentPreviewUrl">
                            <div style="width: 100%; height: 100%;">
                                <template x-if="typeof uploadedDocs !== 'undefined' && uploadedDocs && uploadedDocs[activeDocIndex] && !uploadedDocs[activeDocIndex].isPdf">
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #0f172a; overflow: auto; padding: 10px;">
                                        <img :src="documentPreviewUrl" style="max-width: 100%; max-height: 100%; object-fit: contain; box-shadow: 0 4px 12px rgba(0,0,0,0.4); border-radius: 4px;">
                                    </div>
                                </template>
                                <template x-if="!uploadedDocs || !uploadedDocs[activeDocIndex] || uploadedDocs[activeDocIndex].isPdf">
                                    <iframe :src="documentPreviewUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH'" 
                                            class="frexo-preview-frame"></iframe>
                                </template>
                            </div>
                        </template>

                        <template x-if="!documentPreviewUrl">
                            <div class="frexo-preview-empty">
                                <i class="fa fa-file-text-o"></i>
                                <span>No document loaded</span>
                            </div>
                        </template>

                        <!-- Blue Laser Scan Line (Active during loading) -->
                        <div x-show="{{ $loadingVar }}" class="frexo-scan-laser" style="display: none;"></div>
                        
                        <!-- Floating Analysis Badge -->
                        <div x-show="{{ $loadingVar }}" class="frexo-scan-badge" style="display: none;">
                            <div class="frexo-scan-spinner"></div>
                            <div class="frexo-scan-badge-text">
                                <div class="frexo-scan-badge-title">Frexo AI Analyzing Document...</div>
                                <div class="frexo-scan-badge-sub">Extracting fields and tabular records</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Pane: Extracted Data -->
                <div class="frexo-pane-right">
                    
                    <div class="frexo-review-toolbar">
                        <div class="frexo-review-title">
                            <span>Shipment Information</span>
                            <span class="frexo-count-pill" x-text="extractedData.length + ' fields'"></span>
                        </div>

                        <!-- Bulk Selection Shortcuts -->
                        <div class="frexo-bulk-buttons" x-show="extractedData.length > 0">
                            <button type="button" 
                                    class="frexo-btn-pill" 
                                    @click="extractedData.forEach(i => i.selected = true)"
                                    title="Select all fields">
                                <i class="fa fa-check-square-o"></i> Select All
                            </button>
                            <button type="button" 
                                    class="frexo-btn-pill is-verified" 
                                    @click="extractedData.forEach(i => {
                                        const isVerified = (i.status === 'EXPLICIT' || i.status === 'DERIVED') && !i.hasConflict && !i.hasWarning && i.status !== 'CONFLICT' && i.status !== 'AMBIGUOUS' && i.status !== 'REVIEW_REQUIRED' && i.status !== 'UNREADABLE';
                                        i.selected = isVerified;
                                    })"
                                    title="Accept only verified and calculated fields with 0 warnings">
                                <i class="fa fa-shield"></i> Accept All Verified
                            </button>
                            <button type="button" 
                                    class="frexo-btn-pill" 
                                    @click="extractedData.forEach(i => i.selected = false)"
                                    title="Deselect all fields">
                                <i class="fa fa-square-o"></i> Clear
                            </button>
                        </div>
                    </div>

                    <!-- Unmatched Documents Banner -->
                    <div x-show="($data.unmatchedDocs || []).length > 0" 
                         class="frexo-unmatched-banner" style="display: none;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <div>
                            <strong>Unmatched Documents:</strong>
                            <template x-for="(doc, uIdx) in ($data.unmatchedDocs || [])" :key="uIdx">
                                <span class="frexo-unmatched-tag" x-text="doc.filename || doc.document_id || doc"></span>
                            </template>
                            <span class="frexo-banner-sub" style="font-size: 11px; opacity: 0.8;">(Not linked to this shipment)</span>
                        </div>
                    </div>

                    <!-- Failed Documents Banner -->
                    <div x-show="($data.failedDocs || []).length > 0" 
                         class="frexo-failed-banner" style="display: none;">
                        <i class="fa fa-times-circle"></i>
                        <div>
                            <strong>Extraction Failures:</strong>
                            <template x-for="(fDoc, fIdx) in ($data.failedDocs || [])" :key="fIdx">
                                <span class="frexo-failed-tag" x-text="(fDoc.filename || fDoc.document_id) + ': ' + (fDoc.error || 'Failed')"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Fields Table -->
                    <div class="frexo-table-container">
                        <table class="frexo-table">
                            <thead>
                                <tr>
                                    <th style="width: 34%;">Field Name</th>
                                    <th style="width: 52%;">Extracted Data</th>
                                    <th style="width: 14%; text-align: center;">Select</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loading State -->
                                <template x-if="{{ $loadingVar }}">
                                    <tr>
                                        <td colspan="3" style="padding: 30px 15px; text-align: center;">
                                            <div class="frexo-table-loader">
                                                <i class="fa fa-circle-o-notch fa-spin frexo-spinner-icon"></i>
                                                <div class="frexo-loader-heading">Extracting fields from document...</div>
                                                <div class="frexo-skeleton-bar"></div>
                                                <div class="frexo-skeleton-bar" style="width: 75%;"></div>
                                                <div class="frexo-skeleton-bar" style="width: 60%;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Extracted Data Rows -->
                                <template x-if="!{{ $loadingVar }}">
                                    <template x-for="(item, index) in extractedData" :key="index">
                                        <tr class="frexo-row" :class="{ 'is-selected': item.selected }">
                                            <td class="frexo-cell-field">
                                                <div class="frexo-label-text" x-text="item.label"></div>
                                                <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap; margin-top: 2px;">
                                                    <span class="frexo-scope-tag" x-text="item.parent ? item.parent.toUpperCase() : 'SHIPMENT'"></span>
                                                    
                                                    <!-- Status Badges -->
                                                    <template x-if="item.status === 'EXPLICIT'">
                                                        <span class="frexo-badge badge-explicit" title="Explicitly found in document"><i class="fa fa-check"></i> Verified</span>
                                                    </template>
                                                    <template x-if="item.status === 'DERIVED'">
                                                        <span class="frexo-badge badge-derived" title="Derived/computed by rule"><i class="fa fa-calculator"></i> Derived</span>
                                                    </template>
                                                    <template x-if="item.status === 'REVIEW_REQUIRED'">
                                                        <span class="frexo-badge badge-review" title="Review required before applying"><i class="fa fa-eye"></i> Review Req</span>
                                                    </template>
                                                    <template x-if="item.status === 'CONFLICT'">
                                                        <span class="frexo-badge badge-conflict" title="Document conflict detected"><i class="fa fa-exclamation-circle"></i> Conflict</span>
                                                    </template>
                                                    <template x-if="item.status === 'AMBIGUOUS'">
                                                        <span class="frexo-badge badge-ambiguous" title="Multiple readings found"><i class="fa fa-question-circle"></i> Ambiguous</span>
                                                    </template>
                                                    <template x-if="item.status === 'UNREADABLE'">
                                                        <span class="frexo-badge badge-unreadable" title="Text unreadable"><i class="fa fa-ban"></i> Unreadable</span>
                                                    </template>

                                                    <!-- Jump to Document & Page Button -->
                                                    <template x-if="item.sourceDocId">
                                                        <button type="button" 
                                                                class="frexo-jump-evidence-btn" 
                                                                @click="if (typeof uploadedDocs !== 'undefined' && uploadedDocs && uploadedDocs.length) {
                                                                    let idx = uploadedDocs.findIndex(d => (d.documentId === item.sourceDocId || d.name === item.sourceDocId));
                                                                    if (idx !== -1) { activeDocIndex = idx; documentPreviewUrl = uploadedDocs[idx].url + '#page=' + (item.sourcePage || 1); }
                                                                    else if (documentPreviewUrl) { documentPreviewUrl = documentPreviewUrl.split('#')[0] + '#page=' + (item.sourcePage || 1); }
                                                                } else if (documentPreviewUrl) { documentPreviewUrl = documentPreviewUrl.split('#')[0] + '#page=' + (item.sourcePage || 1); }"
                                                                :title="'Jump to ' + item.sourceDocId + ' page ' + (item.sourcePage || 1)">
                                                            <i class="fa fa-external-link"></i> <span x-text="'p.' + (item.sourcePage || 1)"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </td>
                                            <td class="frexo-cell-value">
                                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                        <span class="frexo-val-chip" :title="item.displayValue || item.value">
                                                            <i class="fa fa-check"></i>
                                                            <span x-text="item.displayValue || item.value || '(Empty)'"></span>
                                                        </span>
                                                        <template x-if="item.rawValue && item.rawValue !== item.value">
                                                            <span class="frexo-raw-pill" :title="'Raw text printed in document: ' + item.rawValue">
                                                                Raw: <strong x-text="item.rawValue"></strong>
                                                            </span>
                                                        </template>
                                                    </div>

                                                    <!-- Candidate Selector for Conflicts or Entities -->
                                                    <template x-if="item.candidates && item.candidates.length > 0">
                                                        <div class="frexo-candidate-picker">
                                                            <span class="frexo-candidate-label"><i class="fa fa-list"></i> Select Candidate:</span>
                                                            <select class="frexo-candidate-select" 
                                                                    x-model="item.value" 
                                                                    @change="item.selected = true; if(item.nameKey && $event.target.selectedOptions[0]) { item.displayValue = $event.target.selectedOptions[0].text; }">
                                                                <option value="">-- Choose Candidate --</option>
                                                                <template x-for="(cand, cIdx) in item.candidates" :key="cIdx">
                                                                    <option :value="cand.id || cand.value" x-text="(cand.name || cand.value) + (cand.code ? ' [' + cand.code + ']' : '')"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="checkbox" class="frexo-checkbox" x-model="item.selected">
                                            </td>
                                        </tr>
                                    </template>
                                </template>

                                <!-- Empty State -->
                                <tr x-show="!{{ $loadingVar }} && extractedData.length === 0">
                                    <td colspan="3" style="text-align: center; padding: 40px 15px; color: #64748b;">
                                        <i class="fa fa-file-text-o" style="font-size: 32px; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                                        <div style="font-weight: 600; color: #334155; margin-bottom: 4px;">No Fields Extracted</div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 12px;">Could not recognize structured shipment data from this document.</div>
                                        <button type="button" class="frexo-btn-secondary" @click="aiPreviewMode = false;">Try Another File</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>

        </div>

        <!-- Footer -->
        <div class="frexo-modal-footer">
            <div class="frexo-footer-status">
                <span x-show="aiPreviewMode">
                    <i class="fa fa-info-circle" style="color: #2563eb;"></i> 
                    <strong x-text="extractedData.filter(i => i.selected).length"></strong> of <strong x-text="extractedData.length"></strong> fields selected to populate form.
                </span>
                <span x-show="!aiPreviewMode" style="color: #64748b;">
                    <i class="fa fa-shield" style="color: #2563eb;"></i> Frexo AI Document Engine &bull; Enterprise Secure
                </span>
            </div>

            <div class="frexo-footer-actions">
                <button type="button" 
                        class="frexo-btn-secondary" 
                        @click="showAiModal = false; aiPreviewMode = false;" 
                        :disabled="{{ $loadingVar }}">
                    Cancel
                </button>

                <!-- Primary Action Button -->
                <button type="button" 
                        class="frexo-btn-primary" 
                        @click="aiPreviewMode ? applyAiData() : {{ $triggerMethod }}" 
                        :disabled="{{ $loadingVar }} || (typeof isApplying !== 'undefined' && isApplying)">
                    <i class="fa" :class="(typeof isApplying !== 'undefined' && isApplying) ? 'fa-spinner fa-spin' : (aiPreviewMode ? 'fa-check' : 'fa-cogs')"></i> 
                    <span x-text="(typeof isApplying !== 'undefined' && isApplying) ? 'Applying to Shipment...' : (aiPreviewMode ? 'Apply to Shipment' : 'Extract Data')"></span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Scoped Decent Blue Theme Styles (Simple, No Emojis) -->
<style>
/* Transitions */
.frexo-transition-enter { transition: opacity 0.2s ease-out; }
.frexo-transition-enter-start { opacity: 0; }
.frexo-transition-enter-end { opacity: 1; }
.frexo-transition-leave { transition: opacity 0.15s ease-in; }
.frexo-transition-leave-start { opacity: 1; }
.frexo-transition-leave-end { opacity: 0; }

/* Backdrop */
.frexo-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    padding: 20px;
}

/* Modal Container */
.frexo-modal-container {
    position: relative;
    width: 95vw;
    max-width: 1080px;
    max-height: 88vh;
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: frexoSlideUp 0.25s ease-out;
}

@keyframes frexoSlideUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Theme Blue Top Accent Bar */
.frexo-top-bar {
    height: 3px;
    width: 100%;
    background: #2563eb;
}

/* Header */
.frexo-modal-header {
    background: #1e3a8a;
    color: #ffffff;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #1d4ed8;
}

.frexo-brand-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}

.frexo-logo-box {
    width: 34px;
    height: 34px;
    border-radius: 6px;
    background: #2563eb;
    border: 1px solid rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 16px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.frexo-brand-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
}

.frexo-brand-name {
    font-size: 17px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.3px;
}

.frexo-brand-badge {
    background: rgba(255, 255, 255, 0.18);
    color: #eff6ff;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 4px;
}

.frexo-brand-tagline {
    margin: 0;
    font-size: 11px;
    color: #bfdbfe;
}

.frexo-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.frexo-status-live {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #eff6ff;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.frexo-close-btn {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s ease;
    font-size: 13px;
}

.frexo-close-btn:hover {
    background: rgba(239, 68, 68, 0.85);
    border-color: #ef4444;
}

/* Body */
.frexo-modal-body {
    padding: 18px 20px;
    background: #ffffff;
    overflow-y: auto;
    flex: 1;
}

/* Error Banner */
.frexo-error-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 10px 14px;
    border-radius: 6px;
    margin-bottom: 14px;
    font-size: 12px;
}

.frexo-error-icon { font-size: 16px; color: #dc2626; }
.frexo-error-content { flex: 1; line-height: 1.4; }
.frexo-error-dismiss { background: none; border: none; font-size: 16px; color: #dc2626; cursor: pointer; }

/* Upload State */
.frexo-upload-state {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* Info Banner */
.frexo-info-banner {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
    padding: 9px 12px;
    border-radius: 6px;
    font-size: 12px;
    line-height: 1.4;
}

.frexo-info-banner i { color: #2563eb; }

/* Dropzone */
.frexo-dropzone {
    border: 2px dashed #93c5fd;
    border-radius: 8px;
    background: #f8fafc;
    padding: 34px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    display: block;
}

.frexo-dropzone:hover,
.frexo-dropzone.is-dragging {
    border-color: #2563eb;
    background: #f0f7ff;
}

.frexo-dropzone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.frexo-drop-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    font-size: 22px;
    margin-bottom: 2px;
}

.frexo-drop-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.frexo-browse-link {
    color: #2563eb;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.frexo-drop-desc {
    font-size: 12px;
    color: #64748b;
    margin: 0;
    max-width: 480px;
}

.frexo-formats-row {
    display: flex;
    gap: 8px;
    margin-top: 6px;
    flex-wrap: wrap;
    justify-content: center;
}

.frexo-format-pill {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
}

.frexo-format-pill i { color: #2563eb; }

/* 3 Cards Row */
.frexo-cards-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 12px;
    margin-top: 4px;
}

.frexo-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px 14px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.frexo-card-icon {
    width: 28px;
    height: 28px;
    border-radius: 4px;
    background: #eff6ff;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.frexo-card-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.frexo-card-content strong {
    font-size: 12px;
    color: #1e293b;
}

.frexo-card-content span {
    font-size: 11px;
    color: #64748b;
    line-height: 1.35;
}

/* STATE 2: Split Review Layout */
.frexo-split-layout {
    display: flex;
    width: 100%;
    height: 500px;
    gap: 16px;
}

.frexo-pane-left {
    flex: 1;
    background: #0f172a;
    border-radius: 6px;
    border: 1px solid #1e293b;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.frexo-pane-toolbar {
    background: #1e293b;
    padding: 8px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.frexo-pane-title {
    color: #f8fafc;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.frexo-btn-subtle {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    font-size: 10px;
    font-weight: 500;
    padding: 2px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.frexo-btn-subtle:hover {
    background: rgba(255, 255, 255, 0.2);
}

.frexo-preview-box {
    flex: 1;
    position: relative;
    background: #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.frexo-preview-frame {
    width: 100%;
    height: 100%;
    border: none;
}

.frexo-preview-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #64748b;
    font-size: 12px;
}

.frexo-preview-empty i { font-size: 40px; color: #475569; }

/* Laser Scan Sweep (Theme Blue) */
.frexo-scan-laser {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background: #3b82f6;
    box-shadow: 0 0 12px #3b82f6, 0 0 24px #60a5fa;
    z-index: 10;
    animation: frexoScanLaser 2.4s ease-in-out infinite alternate;
}

@keyframes frexoScanLaser {
    0% { top: 2%; opacity: 0.9; }
    50% { opacity: 1; }
    100% { top: 98%; opacity: 0.9; }
}

/* Floating Scanning Badge */
.frexo-scan-badge {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(15, 23, 42, 0.92);
    border: 1px solid #3b82f6;
    padding: 12px 18px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 20;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.frexo-scan-spinner {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid rgba(59, 130, 246, 0.3);
    border-top-color: #3b82f6;
    animation: frexoSpin 0.8s linear infinite;
}

@keyframes frexoSpin {
    to { transform: rotate(360deg); }
}

.frexo-scan-badge-title {
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
}

.frexo-scan-badge-sub {
    color: #94a3b8;
    font-size: 10px;
}

/* Right Pane: Review Data */
.frexo-pane-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    overflow: hidden;
}

.frexo-review-toolbar {
    padding: 8px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.frexo-review-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.frexo-count-pill {
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 10px;
    border: 1px solid #bfdbfe;
    white-space: nowrap;
}

.frexo-bulk-buttons {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
}

.frexo-btn-pill {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.frexo-btn-pill:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}

.frexo-btn-pill.is-verified {
    background: #f0fdf4;
    border-color: #86efac;
    color: #15803d;
    font-weight: 700;
}

.frexo-btn-pill.is-verified:hover {
    background: #dcfce7;
    border-color: #4ade80;
    color: #166534;
}

.frexo-link-action {
    background: none;
    border: none;
    color: #2563eb;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
}

.frexo-link-action:hover {
    text-decoration: underline;
    color: #1d4ed8;
}

/* Table */
.frexo-table-container {
    flex: 1;
    overflow-y: auto;
}

.frexo-table {
    width: 100%;
    border-collapse: collapse;
}

.frexo-table th {
    position: sticky;
    top: 0;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    padding: 8px 12px;
    border-bottom: 1px solid #cbd5e1;
    text-align: left;
    z-index: 5;
}

.frexo-row {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.12s ease;
}

.frexo-row:hover {
    background: #f8fafc;
}

.frexo-row.is-selected {
    background: #fafcff;
}

.frexo-cell-field {
    padding: 8px 12px;
}

.frexo-label-text {
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
}

.frexo-scope-tag {
    font-size: 9px;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    padding: 1px 4px;
    border-radius: 3px;
    display: inline-block;
}

.frexo-cell-value {
    padding: 8px 12px;
}

/* Value Chip (Decent Blue Style) */
.frexo-val-chip {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
    border-radius: 4px;
    padding: 3px 7px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.frexo-val-chip i {
    color: #2563eb;
    font-size: 10px;
}

.frexo-checkbox {
    width: 16px;
    height: 16px;
    accent-color: #2563eb;
    cursor: pointer;
}

/* Skeleton Loader in Table */
.frexo-table-loader {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.frexo-spinner-icon {
    font-size: 24px;
    color: #2563eb;
    margin-bottom: 4px;
}

.frexo-loader-heading {
    font-size: 12px;
    font-weight: 600;
    color: #334155;
}

.frexo-skeleton-bar {
    height: 10px;
    width: 85%;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    border-radius: 4px;
    animation: frexoSkeleton 1.4s infinite;
}

@keyframes frexoSkeleton {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Footer */
.frexo-modal-footer {
    padding: 12px 20px;
    background: #f8fafc;
    border-top: 1px solid #cbd5e1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.frexo-footer-status {
    font-size: 12px;
    color: #475569;
}

.frexo-footer-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.frexo-btn-secondary {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    padding: 6px 14px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.frexo-btn-secondary:hover:not(:disabled) {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}

/* Primary Button (Corporate Blue) */
.frexo-btn-primary {
    background: #2563eb;
    border: 1px solid #1d4ed8;
    color: #ffffff;
    padding: 6px 16px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    transition: background 0.15s ease;
}

.frexo-btn-primary:hover:not(:disabled) {
    background: #1d4ed8;
}

.frexo-btn-primary:disabled,
.frexo-btn-secondary:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

/* Global Trigger Button for Top Toolbar */
.frexo-trigger-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #2563eb;
    border: 1px solid #1d4ed8;
    color: #ffffff !important;
    padding: 4px 11px;
    height: 28px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    transition: background 0.15s ease;
    text-decoration: none !important;
}

.frexo-trigger-btn:hover {
    background: #1d4ed8;
}

.frexo-spark-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 11px;
}

/* Frexo AI Verification Badges */
.frexo-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.badge-explicit { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.badge-derived { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.badge-review { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.badge-conflict { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
.badge-ambiguous { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.badge-unreadable { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

.frexo-jump-evidence-btn {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #2563eb;
    border-radius: 3px;
    padding: 0 4px;
    font-size: 9px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 2px;
}
.frexo-jump-evidence-btn:hover {
    background: #eff6ff;
    border-color: #2563eb;
}

.frexo-raw-pill {
    font-size: 10px;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 2px 6px;
    border-radius: 3px;
}

.frexo-candidate-picker {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 3px;
    font-size: 11px;
}
.frexo-candidate-label {
    font-weight: 600;
    color: #b45309;
    white-space: nowrap;
}
.frexo-candidate-select {
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid #f59e0b;
    background: #fffbeb;
    font-size: 11px;
    color: #1e293b;
    max-width: 250px;
}

/* Banner Alerts */
.frexo-unmatched-banner {
    margin: 0 12px 10px 12px;
    padding: 8px 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    font-size: 12px;
    color: #92400e;
    display: flex;
    align-items: center;
    gap: 8px;
}
.frexo-failed-banner {
    margin: 0 12px 10px 12px;
    padding: 8px 12px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 6px;
    font-size: 12px;
    color: #991b1b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.frexo-unmatched-tag, .frexo-failed-tag {
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.15);
    padding: 1px 6px;
    border-radius: 3px;
    font-weight: 600;
    margin: 0 3px;
}

/* ==========================================================================
   Multi-Document Overlapping Deck Styles (Modern Paper Stack UI/UX)
   ========================================================================== */
.frexo-doc-count-badge {
    font-size: 11px;
    color: #2563eb;
    background: #eff6ff;
    padding: 2px 8px;
    border-radius: 12px;
    margin-left: 6px;
    font-weight: 700;
    border: 1px solid #bfdbfe;
}

.frexo-overlapping-deck-container {
    padding: 8px 14px 10px 14px;
    background: linear-gradient(180deg, #f8fafc 0%, #edf2f7 100%);
    border-bottom: 1px solid #cbd5e1;
    overflow-x: auto;
    overflow-y: visible;
    scrollbar-width: thin;
}

.frexo-deck-track {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px 6px 4px;
    min-width: 100%;
}

/* Overlapping Document Card */
.frexo-deck-card {
    position: relative;
    width: 175px;
    min-width: 155px;
    max-width: 195px;
    height: 48px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    cursor: pointer;
    margin-left: -26px; /* Elegant overlapping stack */
    box-shadow: -4px 3px 9px rgba(15, 23, 42, 0.10);
    transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: flex;
    flex-direction: column;
    justify-content: center;
    user-select: none;
}

.frexo-deck-card:first-child {
    margin-left: 0;
}

/* Hover: Lift card upwards with elevated shadow */
.frexo-deck-card:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.20), -4px 4px 10px rgba(15, 23, 42, 0.12);
    z-index: 50 !important;
    border-color: #93c5fd;
    background: #ffffff;
}

/* Active card: highlighted, in front */
.frexo-deck-card.is-active {
    background: #ffffff;
    border: 2px solid #2563eb;
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.24), -4px 4px 10px rgba(15, 23, 42, 0.12);
    transform: translateY(-2px);
}

.frexo-card-tab-accent {
    position: absolute;
    top: 0;
    left: 8px;
    right: 8px;
    height: 3px;
    background: transparent;
    border-radius: 3px 3px 0 0;
}

.frexo-deck-card.is-active .frexo-card-tab-accent {
    background: #2563eb;
}

.frexo-card-inner {
    padding: 6px 10px;
    display: flex;
    align-items: center;
    gap: 7px;
    overflow: hidden;
}

.frexo-card-num {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.frexo-deck-card.is-active .frexo-card-num {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}

.frexo-card-icon {
    font-size: 15px;
    flex-shrink: 0;
}

.frexo-card-icon.is-pdf {
    color: #dc2626;
}

.frexo-card-icon.is-img {
    color: #0284c7;
}

.frexo-card-meta {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.frexo-card-filename {
    font-size: 11px;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

.frexo-deck-card.is-active .frexo-card-filename {
    color: #1d4ed8;
    font-weight: 700;
}

.frexo-card-filesize {
    font-size: 9px;
    color: #64748b;
    margin-top: 1px;
}

.frexo-card-active-dot {
    font-size: 10px;
    color: #2563eb;
    flex-shrink: 0;
}

/* Active Document Info Strip */
.frexo-active-doc-bar {
    padding: 6px 14px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
}

.frexo-active-doc-left {
    display: flex;
    align-items: center;
    gap: 7px;
    min-width: 0;
    overflow: hidden;
}

.frexo-active-doc-name {
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.frexo-active-doc-size {
    color: #64748b;
    font-size: 10px;
    white-space: nowrap;
}

.frexo-open-external-btn {
    font-size: 10px;
    color: #2563eb;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 7px;
    border-radius: 4px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    transition: all 0.15s ease;
    white-space: nowrap;
    font-weight: 600;
}

.frexo-open-external-btn:hover {
    background: #dbeafe;
    color: #1d4ed8;
}

/* Quick Navigation Arrows */
.frexo-doc-nav-arrows {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 2px 5px;
}

.frexo-nav-arrow-btn {
    background: transparent;
    border: none;
    color: #475569;
    font-size: 10px;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.frexo-nav-arrow-btn:hover:not(:disabled) {
    background: #e2e8f0;
    color: #1e293b;
}

.frexo-nav-arrow-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.frexo-nav-counter {
    font-size: 10px;
    font-weight: 700;
    color: #334155;
    padding: 0 4px;
}
</style>
