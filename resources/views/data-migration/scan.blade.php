<x-layout>
<div style="padding: 32px; max-width: 1100px; margin: 0 auto; font-family: 'Inter', sans-serif;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0; display: flex; align-items: center; gap: 10px;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0ea5e9, #3b82f6); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.2);">
                    <i class="fa fa-search" style="font-size: 16px;"></i>
                </div>
                Database Scanner
            </h2>
            <p style="margin: 0; font-size: 14px; color: #64748b; margin-left: 50px;">Project: <strong style="color: #334155;">{{ $project->project_code }}</strong> ({{ $project->source_system ?? 'External DB' }})</p>
        </div>
        
        <!-- Premium Step Wizard -->
        <div style="display: flex; align-items: center; gap: 12px; background: white; padding: 12px 24px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; background: #22c55e; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;"><i class="fa fa-check"></i></div>
                <span style="font-size: 13px; font-weight: 600; color: #22c55e;">Connect</span>
            </div>
            <div style="width: 40px; height: 2px; background: #22c55e;"></div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; background: #0ea5e9; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; box-shadow: 0 2px 6px rgba(14, 165, 233, 0.3);">2</div>
                <span style="font-size: 13px; font-weight: 600; color: #0ea5e9;">Scan</span>
            </div>
            <div style="width: 40px; height: 2px; background: #e2e8f0;"></div>
            <div style="display: flex; align-items: center; gap: 8px; opacity: 0.5;">
                <div style="width: 28px; height: 28px; background: #f1f5f9; color: #64748b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600;">3</div>
                <span style="font-size: 13px; font-weight: 500; color: #64748b;">Map</span>
            </div>
        </div>
    </div>

    <!-- Alpine.js Application -->
    <div x-data="databaseScanner()" x-init="startScan()" style="background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.02); border: 1px solid #f1f5f9;">
        
        <div style="padding: 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #1e293b;">Discovered Data Entities</h3>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">We're analyzing your source system to find migratable tables.</p>
            </div>
            <div>
                <button @click="startScan()" :disabled="scanning" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #334155; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='#ffffff';">
                    <i class="fa fa-refresh" :class="scanning ? 'fa-spin' : ''" style="color: #0ea5e9;"></i> Rescan Source
                </button>
            </div>
        </div>

        <!-- Scanning State -->
        <div x-show="scanning" style="padding: 80px 24px; text-align: center;">
            <div style="position: relative; width: 64px; height: 64px; margin: 0 auto 24px auto;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 50%; border: 4px solid #f1f5f9;"></div>
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 50%; border: 4px solid transparent; border-top-color: #0ea5e9; animation: spin 1s linear infinite;"></div>
                <i class="fa fa-database" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #0ea5e9; font-size: 20px;"></i>
            </div>
            <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 600; color: #1e293b;">Scanning Database...</h3>
            <p style="margin: 0; font-size: 14px; color: #64748b;">Securely connecting to <span style="font-weight: 600; color: #334155;">{{ $project->connection->db_host ?? 'external source' }}</span>.</p>
            <p style="margin: 8px 0 0 0; font-size: 13px; color: #94a3b8;">This might take a moment depending on database size.</p>
        </div>

        <!-- Error State -->
        <div x-cloak x-show="!scanning && error" style="padding: 40px;">
            <div style="background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px; padding: 24px; display: flex; gap: 16px; box-shadow: 0 4px 6px rgba(239, 68, 68, 0.05);">
                <div style="width: 48px; height: 48px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fa fa-exclamation-triangle" style="color: #ef4444; font-size: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #991b1b;">Connection Refused</h4>
                    <p style="margin: 0; font-size: 14px; color: #7f1d1d; line-height: 1.6;" x-text="error"></p>
                    <div style="margin-top: 16px; display: flex; gap: 12px;">
                        <a href="{{ route('data-migration.index') }}" style="display: inline-block; padding: 8px 16px; background: #ef4444; color: white; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none;">Review Credentials</a>
                    </div>
                </div>
            </div>
            
            <!-- Fallback to Mock Data for Dev Testing -->
            <div style="margin-top: 32px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <div style="width: 32px; height: 32px; background: #e0e7ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fa fa-info"></i></div>
                    <div>
                        <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: #312e81;">Development Mode</h4>
                        <p style="margin: 2px 0 0 0; font-size: 12px; color: #4f46e5;">Showing mock schema to test UI rendering.</p>
                    </div>
                </div>
                <template x-if="tables.length > 0">
                    <div x-html="renderTable()" style="background: white; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;"></div>
                </template>
            </div>
        </div>

        <!-- Success State / Results -->
        <div x-cloak x-show="!scanning && !error && tables.length > 0">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 16px 24px;">Table Name</th>
                        <th style="padding: 16px 16px;">Records</th>
                        <th style="padding: 16px 16px;">Size</th>
                        <th style="padding: 16px 16px;">AI Suggestion</th>
                        <th style="padding: 16px 24px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(table, index) in tables" :key="table.name">
                        <tr style="border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc';" onmouseout="this.style.backgroundColor='transparent';">
                            <td style="padding: 16px 24px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-table" style="color: #64748b;"></i>
                                    </div>
                                    <div>
                                        <div x-text="table.name" style="font-weight: 600; font-family: monospace; color: #0ea5e9;"></div>
                                        <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">Source Entity</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 16px 16px;">
                                <span style="font-weight: 600; color: #334155;" x-text="table.records.toLocaleString()"></span>
                            </td>
                            <td style="padding: 16px 16px; color: #64748b;" x-text="table.size"></td>
                            <td style="padding: 16px 16px;">
                                <span x-show="table.suggested_mapping" style="background: #ecfdf5; color: #059669; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="fa fa-magic"></i> <span x-text="table.suggested_mapping"></span>
                                </span>
                                <span x-show="!table.suggested_mapping" style="color: #94a3b8; font-size: 13px; font-style: italic;">Manual mapping required</span>
                            </td>
                            <td style="padding: 16px 24px; text-align: right;">
                                <button style="padding: 8px 16px; background: #ffffff; border: 1px solid #0ea5e9; color: #0ea5e9; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#0ea5e9'; this.style.color='#fff';" onmouseout="this.style.background='#ffffff'; this.style.color='#0ea5e9';">
                                    Configure Mapping
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
[x-cloak] { display: none !important; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('databaseScanner', () => ({
        scanning: false,
        error: null,
        tables: [],

        async startScan() {
            this.scanning = true;
            this.error = null;
            
            try {
                const response = await fetch("{{ route('data-migration.execute-scan', $project->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.tables = data.tables;
                } else {
                    this.error = data.error;
                    if(data.mock_data) {
                        this.tables = data.mock_data;
                    }
                }
            } catch (err) {
                this.error = 'Failed to connect to the server. Please try again.';
            } finally {
                this.scanning = false;
            }
        },
        
        renderTable() {
            let html = `
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 16px 24px;">Table Name</th>
                        <th style="padding: 16px 16px;">Records</th>
                        <th style="padding: 16px 16px;">Size</th>
                        <th style="padding: 16px 16px;">AI Suggestion</th>
                        <th style="padding: 16px 24px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>`;
            
            this.tables.forEach(t => {
                let badge = t.suggested_mapping 
                    ? `<span style="background: #ecfdf5; color: #059669; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 6px;"><i class="fa fa-magic"></i> ${t.suggested_mapping}</span>`
                    : `<span style="color: #94a3b8; font-size: 13px; font-style: italic;">Manual mapping required</span>`;
                    
                html += `
                <tr style="border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc';" onmouseout="this.style.backgroundColor='transparent';">
                    <td style="padding: 16px 24px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-table" style="color: #64748b;"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-family: monospace; color: #0ea5e9;">${t.name}</div>
                                <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">Source Entity</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 16px 16px; font-weight: 600;">${t.records.toLocaleString()}</td>
                    <td style="padding: 16px 16px; color: #64748b;">${t.size}</td>
                    <td style="padding: 16px 16px;">${badge}</td>
                    <td style="padding: 16px 24px; text-align: right;">
                        <button style="padding: 8px 16px; background: #ffffff; border: 1px solid #0ea5e9; color: #0ea5e9; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#0ea5e9'; this.style.color='#fff';" onmouseout="this.style.background='#ffffff'; this.style.color='#0ea5e9';">Configure Mapping</button>
                    </td>
                </tr>`;
            });
            
            html += `</tbody></table>`;
            return html;
        }
    }));
});
</script>
</x-layout>ion
