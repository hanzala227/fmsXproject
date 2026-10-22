# Air Export - 100% Functional Implementation ✅

## Status: ✅ PHASE 1 COMPLETE - DATABASE & MODEL UPDATED

---

## What Was Done (Step 1):

### ✅ 1. Database Migration Created & Run
**File:** `database/migrations/2026_09_09_add_missing_air_export_fields.php`

**Added 13 New Columns:**
```sql
- route_data JSON          (Connecting flight route)
- itn_no VARCHAR(255)      (ITN Number)
- cers_no VARCHAR(255)     (CERS Number)
- reference_no VARCHAR(255) (Reference Number)
- awb_date DATE            (AWB Date)
- cargo_ready_date DATE    (Cargo Ready Date)
- issuing_carrier VARCHAR(255)
- awb_type VARCHAR(50)
- dv_carriage VARCHAR(255)
- dv_customs VARCHAR(255)
- insurance VARCHAR(255)
- wt_val VARCHAR(255)
- other_term VARCHAR(255)
```

**Migration Status:** ✅ **DONE** (217ms)

### ✅ 2. Model Updated
**File:** `app/Models/AirExport.php`

**Added to $fillable:**
- All 13 new fields

**Added to $casts:**
- `awb_date` => 'date'
- `cargo_ready_date` => 'date'
- `route_data` => 'array' (JSON)

---

## Next Steps (Remaining):

### Step 2: Update Validation (Requests)
**Files to update:**
- `app/Http/Requests/StoreAirExportRequest.php`
- `app/Http/Requests/UpdateAirExportRequest.php`

**Add validation rules:**
```php
// New fields
'route_data' => 'nullable|array',
'itn_no' => 'nullable|string|max:255',
'cers_no' => 'nullable|string|max:255',
'reference_no' => 'nullable|string|max:255',
'awb_date' => 'nullable|date',
'cargo_ready_date' => 'nullable|date',
'issuing_carrier' => 'nullable|string|max:255',
'awb_type' => 'nullable|string|max:50',
'dv_carriage' => 'nullable|string|max:255',
'dv_customs' => 'nullable|string|max:255',
'insurance' => 'nullable|string|max:255',
'wt_val' => 'nullable|string|max:255',
'other_term' => 'nullable|string|max:255',
```

### Step 3: Fix Status Tab (Remove Hardcoded Data)
**File:** `resources/views/air-export/create.blade.php`

**Current Issue:** Shows 3 static demo entries

**Fix:** Replace with dynamic data:
```javascript
// Add to Alpine.js data
statusLogs: []

// Add init method
loadStatusLogs() {
    if (!this.form.id) return;
    fetch(`/api/air-exports/${this.form.id}/status-logs`)
        .then(r => r.json())
        .then(data => { this.statusLogs = data; });
}

// Update HTML
<template x-for="log in statusLogs" :key="log.id">
    <div class="timeline-item">
        <div class="date" x-text="log.created_at"></div>
        <div class="desc" x-text="log.details"></div>
        <div class="user" x-text="log.user?.name"></div>
    </div>
</template>
```

**Create API endpoint:**
```php
// AirExportController.php
public function getStatusLogs($id) {
    $logs = ShipmentStatusLog::where('related_id', $id)
        ->where('related_type', 'App\\Models\\AirExport')
        ->with('user')
        ->orderBy('created_at', 'desc')
        ->get();
    return response()->json($logs);
}
```

**Add route:**
```php
Route::get('/api/air-exports/{id}/status-logs', [AirExportController::class, 'getStatusLogs']);
```

### Step 4: Fix Doc Center Tab (Implement Upload/List)
**File:** `resources/views/air-export/create.blade.php`

**Current:** Shows "Doc Center module will be loaded here..."

**Add:**
1. Document upload form (drag-drop + file input)
2. Document list table
3. Download/delete actions

**HTML:**
```html
<div id="doc-center-content">
    <!-- Upload Section -->
    <div class="upload-area">
        <input type="file" id="doc-upload" multiple @change="uploadDocuments">
        <label>Click or drag files here</label>
    </div>

    <!-- Document List -->
    <table class="table">
        <thead>
            <tr><th>File Name</th><th>Type</th><th>Size</th><th>Uploaded</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <template x-for="doc in documents" :key="doc.id">
                <tr>
                    <td x-text="doc.file_name"></td>
                    <td x-text="doc.file_extension"></td>
                    <td x-text="doc.file_size"></td>
                    <td x-text="doc.created_at"></td>
                    <td>
                        <button @click="downloadDoc(doc.id)">Download</button>
                        <button @click="deleteDoc(doc.id)">Delete</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
```

**JavaScript methods:**
```javascript
documents: [],

loadDocuments() {
    fetch(`/api/air-exports/${this.form.id}/documents`)
        .then(r => r.json())
        .then(data => { this.documents = data; });
},

uploadDocuments(event) {
    const formData = new FormData();
    Array.from(event.target.files).forEach(file => {
        formData.append('files[]', file);
    });
    
    fetch(`/api/air-exports/${this.form.id}/documents`, {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': getCsrfToken() }
    })
    .then(r => r.json())
    .then(() => {
        this.loadDocuments();
        showToast('success', 'Documents uploaded');
    });
},

downloadDoc(id) {
    window.open(`/api/documents/${id}/download`, '_blank');
},

deleteDoc(id) {
    if (!confirm('Delete document?')) return;
    fetch(`/api/documents/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() }
    })
    .then(() => {
        this.loadDocuments();
        showToast('success', 'Document deleted');
    });
}
```

**Controller methods:**
```php
public function uploadDocuments(Request $request, $id) {
    $airExport = AirExport::findOrFail($id);
    $uploaded = [];
    
    foreach ($request->file('files', []) as $file) {
        $path = $file->store('documents/air-export/' . $id, 'public');
        $doc = $airExport->documents()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);
        $uploaded[] = $doc;
    }
    
    return response()->json($uploaded);
}

public function getDocuments($id) {
    $Tools = Document::where('documentable_type', 'App\\Models\\AirExport')
        ->where('documentable_id', $id)
        ->get();
    return response()->json($Tools);
}
```

**Routes:**
```php
Route::post('/api/air-exports/{id}/documents', [AirExportController::class, 'uploadDocuments']);
Route::get('/api/air-exports/{id}/documents', [AirExportController::class, 'getDocuments']);
Route::delete('/api/documents/{id}', [DocumentController::class, 'destroy']);
Route::get('/api/documents/{id}/download', [DocumentController::class, 'download']);
```

### Step 5: Fix Work Order Links
**File:** `resources/views/air-export/create.blade.php`

**Current Issue:** Links point to `/ocean-export/work-order/{id}/edit`

**Fix:** Update to air-export paths:
```javascript
// Change from:
window.open(\`/ocean-export/work-order/\${wo.id}/edit\`, '_blank');

// To:
window.open(\`/air-export/work-order/\${wo.id}/edit?source=air_export&source_id=\${this.form.id}\`, '_blank');
```

**Update fetchWorkOrders endpoint:**
```javascript
// Change from:
fetch(\`/api/ocean-export/\${this.form.id}/work-orders\`)

// To:
fetch(\`/api/air-exports/\${this.form.id}/work-orders\`)
```

**Create air-export work order routes:**
```php
Route::get('/air-export/work-order/create', [WorkOrderController::class, 'create'])->name('air-export.work-order.create');
Route::post('/air-export/work-order', [WorkOrderController::class, 'store'])->name('air-export.work-order.store');
Route::get('/air-export/work-order/{id}/edit', [WorkOrderController::class, 'edit'])->name('air-export.work-order.edit');
Route::put('/air-export/work-order/{id}', [WorkOrderController::class, 'update'])->name('air-export.work-order.update');
Route::get('/api/air-exports/{id}/work-orders', [WorkOrderController::class, 'getWorkOrders']);
```

### Step 6: Make Memo Functional
**File:** `resources/views/air-export/create.blade.php`

**Current:** Note section shows "No records found"

**Add memo CRUD:**
```javascript
// Alpine.js data
memos: [],
newMemo: { subject: '', content: '' },

// Methods
loadMemos() {
    fetch(`/api/air-exports/${this.form.id}/memos`)
        .then(r => r.json())
        .then(data => { this.memos = data; });
},

saveMemo() {
    fetch(`/api/air-exports/${this.form.id}/memos`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify(this.newMemo)
    })
    .then(() => {
        this.loadMemos();
        this.newMemo = { subject: '', content: '' };
        showToast('success', 'Memo saved');
    });
},

deleteMemo(id) {
    if (!confirm('Delete memo?')) return;
    fetch(`/api/air-export-memos/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() }
    })
    .then(() => {
        this.loadMemos();
        showToast('success', 'Memo deleted');
    });
}
```

**Controller methods:**
```php
public function getMemos($id) {
    $memos = AirExportMemo::where('air_export_id', $id)
        ->with('user')
        ->orderBy('created_at', 'desc')
        ->get();
    return response()->json($memos);
}

public function storeMemo(Request $request, $id) {
    $memo = AirExportMemo::create([
        'air_export_id' => $id,
        'subject' => $request->subject,
        'content' => $request->content,
        'created_by' => auth()->id(),
    ]);
    return response()->json($memo);
}

public function deleteMemo($id) {
    AirExportMemo::findOrFail($id)->delete();
    return response()->json(['success' => true]);
}
```

**Routes:**
```php
Route::get('/api/air-exports/{id}/memos', [AirExportController::class, 'getMemos']);
Route::post('/api/air-exports/{id}/memos', [AirExportController::class, 'storeMemo']);
Route::delete('/api/air-export-memos/{id}', [AirExportController::class, 'deleteMemo']);
```

---

## Summary of Changes Needed:

### ✅ DONE:
1. Database migration (13 new columns) ✅
2. Model fillable & casts updated ✅

### 📝 TODO:
3. Update validation requests (add new fields)
4. Fix Status tab (remove hardcoded data, add AJAX)
5. Implement Doc Center tab (upload/list/delete)
6. Fix Work Order links (change ocean-export to air-export)
7. Implement Memo CRUD (list/create/delete)

---

## Testing Checklist:

### Basic Tab:
- [ ] All fields save to database (including new 13 fields)
- [ ] Route/Connecting Flight data saves as JSON
- [ ] ITN No, CERS No, Reference No persist
- [ ] AWB Date, Cargo Ready Date save correctly
- [ ] Direct Master mode works
- [ ] HAWBs create/update/delete properly

### Charges Tab:
- [ ] Add/edit/delete charges
- [ ] Calculations correct
- [ ] Invoice generation works
- [ ] Excel export works
- [ ] Print view works

### Doc Center Tab:
- [ ] Upload documents
- [ ] View document list
- [ ] Download documents
- [ ] Delete documents

### Work Order Tab:
- [ ] Create work order (air-export path)
- [ ] Edit work order opens correctly
- [ ] Delete work order works
- [ ] Work orders link to air_export correctly

### Status Tab:
- [ ] Shows real status logs from database
- [ ] New logs appear after save
- [ ] User names display correctly
- [ ] Dates format correctly

### Memo Section:
- [ ] Add memo with subject/content
- [ ] View memo list
- [ ] Delete memo
- [ ] Memos persist in database

---

**Current Progress:** 20% Complete (Database & Model done)
**Next Step:** Update validation requests
**Est. Time to 100%:** 2-3 hours of focused development

