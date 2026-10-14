@props(['documentType', 'documentId', 'documentUrl', 'documentName', 'buttonText' => 'Email Document'])

<div x-data="emailDocumentModal('{{ $documentType }}', '{{ $documentId }}', '{{ $documentUrl }}', '{{ $documentName }}')" style="display: inline-block;">
    
    <!-- Trigger Button -->
    <button type="button" @click="openModal()" class="btn-action-round" style="background: #ffffff; border: 1px solid #e2e8f0; color: #3b82f6; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-left: 8px;">
        <i class="fa fa-envelope" style="color: #3b82f6;"></i> {{ $buttonText }}
    </button>

    <!-- Modal Overlay -->
    <div x-show="isOpen" 
         x-transition.opacity.duration.300ms
         style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; backdrop-filter: blur(4px);"
         @click.self="closeModal()">
        
        <!-- Modal Content -->
        <div x-show="isOpen" 
             x-transition.scale.80.duration.300ms
             style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; width: 100%; max-width: 600px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden;">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #1e293b, #334155); padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #ffffff; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-paper-plane" style="color: #60a5fa;"></i> Send Document via Email
                </h3>
                <button type="button" @click="closeModal()" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; font-size: 16px; transition: color 0.2s;" onmouseover="this.style.color='#fff';" onmouseout="this.style.color='#94a3b8';">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <!-- Body -->
            <div style="padding: 24px; max-height: 70vh; overflow-y: auto;">
                <div x-show="error" style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0; color: #b91c1c; font-size: 13px;" x-text="error"></p>
                </div>
                <div x-show="success" style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0; color: #047857; font-size: 13px;" x-text="success"></p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">To <span style="color: #ef4444;">*</span></label>
                    <input type="email" x-model="form.to" placeholder="client@example.com" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #1e293b; background: #fff; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'" :disabled="isSending">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">CC <span style="font-weight: 400; color: #94a3b8;">(Comma separated)</span></label>
                    <input type="text" x-model="form.cc" placeholder="manager@example.com, other@example.com" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #1e293b; background: #fff; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'" :disabled="isSending">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Subject <span style="color: #ef4444;">*</span></label>
                    <input type="text" x-model="form.subject" placeholder="Document from {{ auth()->user()->company->name ?? 'Our Company' }}" style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #1e293b; background: #fff; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'" :disabled="isSending">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Message</label>
                    <textarea x-model="form.body" rows="6" placeholder="Please find the attached document..." style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #1e293b; background: #fff; outline: none; resize: vertical; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'" :disabled="isSending"></textarea>
                </div>
                
                <!-- PDF Preview Info -->
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 16px; padding: 12px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px;">
                    <i class="fa fa-file-pdf-o" style="color: #ef4444; font-size: 18px;"></i>
                    <span style="font-size: 13px; color: #334155; font-weight: 500;" x-text="documentName + '.pdf'"></span>
                    <span style="font-size: 11px; color: #94a3b8; margin-left: auto;" x-text="pdfStatus"></span>
                </div>
            </div>

            <!-- Footer -->
            <div style="background: #f8fafc; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #e2e8f0;">
                <button type="button" @click="closeModal()" :disabled="isSending" style="padding: 8px 16px; background: white; border: 1px solid #cbd5e1; color: #475569; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s;" onmouseover="if(!this.disabled) this.style.background='#f1f5f9';" onmouseout="if(!this.disabled) this.style.background='white';">
                    Cancel
                </button>
                <button type="button" @click="sendEmail()" :disabled="isSending || !form.to || !form.subject || !form.body" style="padding: 8px 20px; background: #3b82f6; border: none; color: white; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.2s;" onmouseover="if(!this.disabled) this.style.background='#2563eb';" onmouseout="if(!this.disabled) this.style.background='#3b82f6';">
                    <span x-show="!isSending"><i class="fa fa-paper-plane"></i> Send Email</span>
                    <span x-show="isSending"><i class="fa fa-spinner fa-spin"></i> Sending...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Load html2canvas and jsPDF from CDN (only once) -->
<script>
if (!window._emailLibsLoaded) {
    window._emailLibsLoaded = true;
    
    // html2canvas
    var s1 = document.createElement('script');
    s1.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
    document.head.appendChild(s1);
    
    // jsPDF
    var s2 = document.createElement('script');
    s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
    document.head.appendChild(s2);
}
</script>

<script>
window.emailDocumentModal = function(docType, docId, docUrl, docName) {
    return {
        isOpen: false,
        isSending: false,
        error: null,
        success: null,
        pdfStatus: 'Auto-generated from screen',
        pdfBase64: null,
        documentType: docType,
        documentId: docId,
        documentUrl: docUrl,
        documentName: docName,
        form: {
            to: '',
            cc: '',
            subject: 'Important Document Attached',
            body: 'Dear Valued Client,\n\nPlease find the attached document for your reference.\n\nThank you,\n{{ auth()->user()->company->name ?? "Our Company" }}',
        },
        
        openModal() {
            this.isOpen = true;
            this.error = null;
            this.success = null;
            this.pdfBase64 = null;
            this.pdfStatus = 'Auto-generated from screen';
            this.form.subject = `${this.documentName} - {{ auth()->user()->company->name ?? "Company" }}`;
        },
        
        closeModal() {
            if (this.isSending) return;
            this.isOpen = false;
            setTimeout(() => {
                this.error = null;
                this.success = null;
            }, 300);
        },
        
        async generatePDF() {
            this.pdfStatus = 'Generating PDF...';
            
            // Find the tightest content element to capture (no extra margins)
            const activeTemplate = document.querySelector('.template-container.active');
            let captureTarget = null;
            
            if (activeTemplate) {
                // For HBL templates: grab the inner div (e.g. #ntg-air-template) directly
                captureTarget = activeTemplate.querySelector('[id$="-template"]') || activeTemplate.firstElementChild || activeTemplate;
            }
            if (!captureTarget) {
                captureTarget = document.getElementById('freight-invoice-template') || document.querySelector('.invoice-box') || document.getElementById('pageWrapper') || document.querySelector('.print-area') || document.querySelector('.page-wrapper') || document.body;
            }
            
            if (!captureTarget) {
                throw new Error('Could not find document content to capture.');
            }

            // Wait for libraries to load
            let attempts = 0;
            while ((!window.html2canvas || !window.jspdf) && attempts < 30) {
                await new Promise(r => setTimeout(r, 200));
                attempts++;
            }
            
            if (!window.html2canvas || !window.jspdf) {
                throw new Error('PDF libraries failed to load. Please check your internet connection and try again.');
            }

            // Temporarily hide elements that shouldn't be in the PDF (toolbars, the modal itself)
            const elementsToHide = document.querySelectorAll('.no-print, .toolbar, .top-bar');
            const hiddenStates = [];
            
            elementsToHide.forEach(el => {
                hiddenStates.push({ el: el, display: el.style.display });
                el.style.display = 'none';
            });
            
            // Also hide the modal container temporarily
            const modalOverlay = document.querySelector('[x-data^="emailDocumentModal"] > div[x-show="isOpen"]');
            let modalDisplay = '';
            if (modalOverlay) {
                modalDisplay = modalOverlay.style.display;
                modalOverlay.style.display = 'none';
            }

            // Convert inputs and textareas into plain DIVs for perfect rendering
            // html2canvas struggles with input vertical alignment and padding
            const inputs = captureTarget.querySelectorAll('input[type="text"], textarea, .inv-field, .hbl-field');
            const cloneDivs = [];
            
            inputs.forEach(input => {
                const isHidden = window.getComputedStyle(input).display === 'none';
                if (isHidden) return;
                
                const div = document.createElement('div');
                div.innerHTML = (input.value || '').replace(/\n/g, '<br>');
                
                // Copy styles perfectly
                const style = window.getComputedStyle(input);
                div.style.position = 'absolute';
                div.style.left = input.style.left || style.left;
                div.style.top = input.style.top || style.top;
                div.style.width = input.style.width || style.width;
                div.style.height = input.style.height || style.height;
                div.style.fontSize = style.fontSize;
                div.style.fontFamily = style.fontFamily;
                div.style.fontWeight = style.fontWeight;
                div.style.color = style.color;
                div.style.padding = style.padding;
                div.style.lineHeight = style.lineHeight;
                div.style.textAlign = style.textAlign;
                div.style.zIndex = style.zIndex;
                div.style.background = style.background !== 'rgba(0, 0, 0, 0)' ? style.background : 'transparent';
                
                // Flex alignment to fix clipping and handle text-align
                div.style.display = 'flex';
                if (input.tagName.toLowerCase() === 'textarea') {
                    div.style.alignItems = 'flex-start';
                    div.style.whiteSpace = 'pre-wrap';
                } else {
                    div.style.alignItems = 'center'; // Center single-line inputs vertically
                    div.style.whiteSpace = 'nowrap';
                    
                    // Map text-align to flex justify-content
                    if (style.textAlign === 'right') {
                        div.style.justifyContent = 'flex-end';
                    } else if (style.textAlign === 'center') {
                        div.style.justifyContent = 'center';
                    } else {
                        div.style.justifyContent = 'flex-start';
                    }
                }
                div.style.overflow = 'hidden';
                
                input.parentNode.insertBefore(div, input);
                
                // Save state to restore later
                cloneDivs.push({ original: input, clone: div, display: input.style.display });
                input.style.display = 'none'; // hide original
            });

            // Wait a tiny bit for the DOM to update
            await new Promise(resolve => setTimeout(resolve, 150));

            // Use html2canvas to capture the exact visual - tight to the content
            const canvas = await html2canvas(captureTarget, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                width: captureTarget.offsetWidth,
                height: captureTarget.scrollHeight
            });

            // Restore original DOM state
            cloneDivs.forEach(item => {
                item.clone.remove();
                item.original.style.display = item.display;
            });
            
            if (modalOverlay) {
                modalOverlay.style.display = modalDisplay;
            }
            elementsToHide.forEach((el, index) => {
                el.style.display = hiddenStates[index].display;
            });

            // Convert canvas to PDF using jsPDF
            const { jsPDF } = window.jspdf;
            
            const imgWidth = canvas.width;
            const imgHeight = canvas.height;
            
            // A4: 210mm x 297mm. Use portrait by default.
            const pdfWidth = 210;
            const pdfHeight = 297;
            
            // Scale image to fit page width
            const ratio = pdfWidth / imgWidth;
            const scaledHeight = imgHeight * ratio;
            
            // If the content is taller than one page, we need multiple pages
            const pdf = new jsPDF('p', 'mm', 'a4');
            
            if (scaledHeight <= pdfHeight) {
                // Single page
                pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, pdfWidth, scaledHeight);
            } else {
                // Multiple pages
                let yOffset = 0;
                let pageNum = 0;
                const pageCanvas = document.createElement('canvas');
                pageCanvas.width = canvas.width;
                const pagePixelHeight = pdfHeight / ratio;
                
                while (yOffset < canvas.height) {
                    if (pageNum > 0) pdf.addPage();
                    
                    const sliceHeight = Math.min(pagePixelHeight, canvas.height - yOffset);
                    pageCanvas.height = sliceHeight;
                    const ctx = pageCanvas.getContext('2d');
                    ctx.drawImage(canvas, 0, yOffset, canvas.width, sliceHeight, 0, 0, canvas.width, sliceHeight);
                    
                    const sliceScaledHeight = sliceHeight * ratio;
                    pdf.addImage(pageCanvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, pdfWidth, sliceScaledHeight);
                    
                    yOffset += pagePixelHeight;
                    pageNum++;
                }
            }
            
            this.pdfBase64 = pdf.output('datauristring');
            this.pdfStatus = 'PDF ready to send';
            
            return this.pdfBase64;
        },
        
        async sendEmail() {
            this.isSending = true;
            this.error = null;
            this.success = null;
            
            try {
                // Step 1: Generate PDF from the visible document
                if (!this.pdfBase64) {
                    await this.generatePDF();
                }
                
                this.pdfStatus = 'Sending email...';

                // Step 2: Send to server with the PDF
                const payload = {
                    ...this.form,
                    pdf_base64: this.pdfBase64,
                    document_type: this.documentType,
                    document_id: this.documentId,
                    document_name: this.documentName
                };
                
                const response = await fetch('/api/documents/email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    this.success = data.message || 'Email sent successfully!';
                    this.pdfStatus = 'Email sent!';
                    
                    if (typeof showToast === 'function') {
                        showToast('success', this.success);
                        this.closeModal();
                    } else {
                        setTimeout(() => this.closeModal(), 2000);
                    }
                } else {
                    if (data.errors) {
                        const errorMsgs = Object.values(data.errors).flat();
                        this.error = errorMsgs.join('\n');
                    } else {
                        this.error = data.message || 'Failed to send email. Please check your SMTP settings.';
                    }
                    this.pdfStatus = 'Failed';
                    if (typeof showToast === 'function') {
                        showToast('error', this.error);
                    }
                }
            } catch (err) {
                this.error = err.message || 'A network error occurred. Please try again.';
                this.pdfStatus = 'Error';
                console.error('Email error:', err);
                if (typeof showToast === 'function') {
                    showToast('error', this.error);
                }
            } finally {
                this.isSending = false;
            }
        }
    };
};
</script>
