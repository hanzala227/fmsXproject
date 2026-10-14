@extends('super-admin.layout')

@section('content')
<div style="padding: 16px; max-width: 1000px; margin: 0 auto;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0;">New Migration - Connect Data Source</h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;">Connect to your existing database or file to begin migration</p>
        </div>
        
        <!-- Step Wizard Indicator -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="width: 24px; height: 24px; background: #0ea5e9; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">1</div>
                <span style="font-size: 10px; color: #0ea5e9; margin-top: 4px;">Connect</span>
            </div>
            <div style="width: 32px; height: 1px; background: #cbd5e1; margin-bottom: 12px;"></div>
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="width: 24px; height: 24px; background: #f1f5f9; color: #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">2</div>
                <span style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Scan</span>
            </div>
            <div style="width: 32px; height: 1px; background: #cbd5e1; margin-bottom: 12px;"></div>
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="width: 24px; height: 24px; background: #f1f5f9; color: #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">3</div>
                <span style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Mapping</span>
            </div>
            <div style="width: 32px; height: 1px; background: #cbd5e1; margin-bottom: 12px;"></div>
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="width: 24px; height: 24px; background: #f1f5f9; color: #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">4</div>
                <span style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Validate</span>
            </div>
            <div style="width: 32px; height: 1px; background: #cbd5e1; margin-bottom: 12px;"></div>
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="width: 24px; height: 24px; background: #f1f5f9; color: #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">5</div>
                <span style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Import</span>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);" x-data="migrationWizard()">
        
        <!-- Tabs -->
        <div style="display: flex; border-bottom: 1px solid #e2e8f0; background: #f8fafc; padding: 0 16px;">
            <div @click="sourceType = 'mysql'" style="padding: 16px 24px; font-size: 14px; font-weight: 500; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s;" :style="['mysql', 'sqlsrv'].includes(sourceType) ? 'color: #0ea5e9; border-bottom-color: #0ea5e9;' : 'color: #64748b;'">
                <i class="fa fa-database" style="margin-right: 8px;"></i> Database
            </div>
            <div @click="sourceType = 'csv'" style="padding: 16px 24px; font-size: 14px; font-weight: 500; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s;" :style="sourceType === 'csv' ? 'color: #0ea5e9; border-bottom-color: #0ea5e9;' : 'color: #64748b;'">
                <i class="fa fa-file-excel-o" style="margin-right: 8px;"></i> Excel/CSV
            </div>
            <div @click="sourceType = 'api'" style="padding: 16px 24px; font-size: 14px; font-weight: 500; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s;" :style="sourceType === 'api' ? 'color: #0ea5e9; border-bottom-color: #0ea5e9;' : 'color: #64748b;'">
                <i class="fa fa-cloud" style="margin-right: 8px;"></i> API
            </div>
        </div>

        <form method="POST" action="{{ route('super-admin.data-migration.store') }}" style="display: flex; padding: 24px; gap: 32px;">
            @csrf
            
            <input type="hidden" name="source_type" :value="sourceType">
            <!-- Generate a random Project Code -->
            <input type="hidden" name="project_code" value="MIG-{{ mt_rand(100000, 999999) }}">

            <!-- Left Form Area -->
            <div style="flex: 2;">
                
                <!-- Target Company (Required for all types) -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px;">Target Customer / Tenant</label>
                    <select name="company_id" required style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                        <option value="">Select a Customer...</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px;">Source ERP System (Optional)</label>
                    <input type="text" name="source_system" placeholder="e.g. CargoSoft, Logisys, Custom ERP" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                </div>

                <!-- Database Specific Fields -->
                <div x-show="['mysql', 'sqlsrv'].includes(sourceType)">
                    <div style="margin-bottom: 16px; display: flex; gap: 16px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Database Type</label>
                            <select name="source_type" x-model="sourceType" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                                <option value="mysql">MySQL / MariaDB</option>
                                <option value="sqlsrv">Microsoft SQL Server</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px; display: flex; gap: 16px;">
                        <div style="flex: 2;">
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Host</label>
                            <input type="text" name="db_host" placeholder="203.xxx.xxx.xxx" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Port</label>
                            <input type="text" name="db_port" placeholder="3306" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Database Name</label>
                        <input type="text" name="db_database" placeholder="old_erp_db" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Username</label>
                        <input type="text" name="db_username" placeholder="freitx_migration" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;">Password</label>
                        <input type="password" name="db_password" placeholder="••••••••" style="width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>

                    <div style="display: flex; align-items: center; margin-bottom: 24px;">
                        <input type="checkbox" name="use_ssl" id="use_ssl" style="margin-right: 8px;">
                        <label for="use_ssl" style="font-size: 13px; color: #475569; cursor: pointer;">Use SSL connection</label>
                    </div>
                </div>
                
                <!-- CSV Placeholder -->
                <div x-show="sourceType === 'csv'" style="display: none;">
                    <div style="padding: 40px; border: 2px dashed #cbd5e1; border-radius: 8px; text-align: center; margin-bottom: 24px;">
                        <i class="fa fa-cloud-upload" style="font-size: 32px; color: #94a3b8; margin-bottom: 12px;"></i>
                        <p style="margin: 0; font-size: 14px; color: #475569; font-weight: 500;">Drag and drop Excel/CSV files here</p>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">You can upload multiple files later in the Mapping phase.</p>
                        <button type="button" style="margin-top: 16px; padding: 8px 16px; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; cursor: pointer;">Browse Files</button>
                    </div>
                </div>

                <button type="submit" style="width: 100%; height: 42px; font-size: 14px; font-weight: 500; color: #fff; background: #0ea5e9; border: none; border-radius: 4px; cursor: pointer;">
                    Test Connection & Save
                </button>
            </div>

            <!-- Right Info Area -->
            <div style="flex: 1;">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px;">
                    <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 600; color: #166534; display: flex; align-items: center;">
                        <i class="fa fa-shield" style="margin-right: 8px;"></i> Security Best Practices
                    </h4>
                    
                    <ul style="margin: 0; padding-left: 16px; font-size: 12px; color: #166534; line-height: 1.6;">
                        <li style="margin-bottom: 8px;">Use a temporary <strong>read-only</strong> database user for migration.</li>
                        <li style="margin-bottom: 8px;">Whitelist our migration server IPs on your firewall.</li>
                        <li style="margin-bottom: 8px;">Disable the database connection immediately after migration completes.</li>
                    </ul>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('migrationWizard', () => ({
        sourceType: 'mysql'
    }));
});
</script>
@endsection
