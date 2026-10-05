<x-layout>
<div style="padding: 32px; max-width: 1100px; margin: 0 auto; font-family: 'Inter', sans-serif;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0; display: flex; align-items: center; gap: 10px;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0ea5e9, #3b82f6); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.2);">
                    <i class="fa fa-plug" style="font-size: 16px;"></i>
                </div>
                Connect Data Source
            </h2>
            <p style="margin: 0; font-size: 14px; color: #64748b; margin-left: 50px;">Establish a secure connection to your legacy ERP database.</p>
        </div>
        
        <!-- Premium Step Wizard -->
        <div style="display: flex; align-items: center; gap: 12px; background: white; padding: 12px 24px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; background: #0ea5e9; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; box-shadow: 0 2px 6px rgba(14, 165, 233, 0.3);">1</div>
                <span style="font-size: 13px; font-weight: 600; color: #0ea5e9;">Connect</span>
            </div>
            <div style="width: 40px; height: 2px; background: #e2e8f0;"></div>
            <div style="display: flex; align-items: center; gap: 8px; opacity: 0.5;">
                <div style="width: 28px; height: 28px; background: #f1f5f9; color: #64748b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600;">2</div>
                <span style="font-size: 13px; font-weight: 500; color: #64748b;">Scan</span>
            </div>
            <div style="width: 40px; height: 2px; background: #e2e8f0;"></div>
            <div style="display: flex; align-items: center; gap: 8px; opacity: 0.5;">
                <div style="width: 28px; height: 28px; background: #f1f5f9; color: #64748b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600;">3</div>
                <span style="font-size: 13px; font-weight: 500; color: #64748b;">Map</span>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div style="margin-bottom: 24px; padding: 16px 20px; background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; border-radius: 8px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);">
            <div style="font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-exclamation-circle text-red-500"></i> Please fix the following errors:
            </div>
            <ul style="margin: 0; padding-left: 24px; font-size: 13px;">
                @foreach ($errors->all() as $error)
                    <li style="margin-bottom: 4px;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.02); border: 1px solid #f1f5f9;" x-data="{ sourceType: 'mysql' }">
        
        <!-- Source Selection Tabs -->
        <div style="display: flex; border-bottom: 1px solid #f1f5f9; background: #f8fafc; padding: 0 24px;">
            <div @click="sourceType = 'mysql'" 
                 style="padding: 20px 32px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; position: relative;" 
                 :style="sourceType === 'mysql' || sourceType === 'sqlsrv' ? 'color: #0ea5e9;' : 'color: #64748b;'">
                <i class="fa fa-database" style="margin-right: 8px;"></i> Relational Database
                <div x-show="sourceType === 'mysql' || sourceType === 'sqlsrv'" style="position: absolute; bottom: -1px; left: 0; right: 0; height: 3px; background: #0ea5e9; border-top-left-radius: 3px; border-top-right-radius: 3px;"></div>
            </div>
            
            <div @click="sourceType = 'csv'" 
                 style="padding: 20px 32px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; position: relative;" 
                 :style="sourceType === 'csv' ? 'color: #10b981;' : 'color: #64748b;'">
                <i class="fa fa-file-excel-o" style="margin-right: 8px;"></i> Flat Files (CSV/Excel)
                <div x-show="sourceType === 'csv'" style="position: absolute; bottom: -1px; left: 0; right: 0; height: 3px; background: #10b981; border-top-left-radius: 3px; border-top-right-radius: 3px;"></div>
            </div>
            
            <div @click="sourceType = 'api'" 
                 style="padding: 20px 32px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; position: relative;" 
                 :style="sourceType === 'api' ? 'color: #8b5cf6;' : 'color: #64748b;'">
                <i class="fa fa-cloud" style="margin-right: 8px;"></i> API Integration
                <div x-show="sourceType === 'api'" style="position: absolute; bottom: -1px; left: 0; right: 0; height: 3px; background: #8b5cf6; border-top-left-radius: 3px; border-top-right-radius: 3px;"></div>
            </div>
        </div>

        <form method="POST" action="{{ route('data-migration.store') }}" style="display: flex; padding: 40px; gap: 48px; background: #ffffff;">
            @csrf
            
            <input type="hidden" name="source_type" :value="sourceType">
            <input type="hidden" name="project_code" value="MIG-{{ mt_rand(100000, 999999) }}">

            <!-- Left Form Area -->
            <div style="flex: 2;">
                
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Source ERP System (Optional)</label>
                    <input type="text" name="source_system" placeholder="e.g. CargoSoft, Logisys, Custom ERP" style="width: 100%; height: 44px; padding: 0 16px; font-size: 14px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s, box-shadow 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                </div>

                <!-- Database Specific Fields -->
                <div x-show="sourceType === 'mysql' || sourceType === 'sqlsrv'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                    
                    <div style="margin-bottom: 24px; display: flex; gap: 20px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Database Type <span style="color: #ef4444;">*</span></label>
                            <select name="source_type" x-model="sourceType" style="width: 100%; height: 44px; padding: 0 16px; font-size: 14px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; background: #f8fafc; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                                <option value="mysql">MySQL / MariaDB</option>
                                <option value="sqlsrv">Microsoft SQL Server</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 24px; display: flex; gap: 20px;">
                        <div style="flex: 2;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Host Address <span style="color: #ef4444;">*</span></label>
                            <div style="position: relative;">
                                <i class="fa fa-server" style="position: absolute; left: 16px; top: 14px; color: #94a3b8;"></i>
                                <input type="text" name="db_host" placeholder="e.g. 192.168.1.100 or aws.rds.amazon.com" style="width: 100%; height: 44px; padding: 0 16px 0 44px; font-size: 14px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Port <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="db_port" placeholder="3306" value="3306" style="width: 100%; height: 44px; padding: 0 16px; font-size: 14px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                        </div>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Database Name <span style="color: #ef4444;">*</span></label>
                        <div style="position: relative;">
                            <i class="fa fa-database" style="position: absolute; left: 16px; top: 14px; color: #94a3b8;"></i>
                            <input type="text" name="db_database" placeholder="e.g. legacy_erp_db" style="width: 100%; height: 44px; padding: 0 16px 0 44px; font-size: 14px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                        </div>
                    </div>

                    <div style="display: flex; gap: 20px; margin-bottom: 32px;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Username <span style="color: #ef4444;">*</span></label>
                            <div style="position: relative;">
                                <i class="fa fa-user" style="position: absolute; left: 16px; top: 14px; color: #94a3b8;"></i>
                                <input type="text" name="db_username" placeholder="migration_user" style="width: 100%; height: 44px; padding: 0 16px 0 44px; font-size: 14px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Password <span style="color: #ef4444;">*</span></label>
                            <div style="position: relative;">
                                <i class="fa fa-lock" style="position: absolute; left: 16px; top: 14px; color: #94a3b8;"></i>
                                <input type="password" name="db_password" placeholder="••••••••" style="width: 100%; height: 44px; padding: 0 16px 0 44px; font-size: 14px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; margin-bottom: 32px; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <input type="checkbox" name="use_ssl" id="use_ssl" style="width: 18px; height: 18px; margin-right: 12px; cursor: pointer; accent-color: #0ea5e9;">
                        <label for="use_ssl" style="font-size: 14px; font-weight: 500; color: #475569; cursor: pointer;">Require SSL Encryption for this connection</label>
                    </div>
                </div>
                
                <!-- CSV Placeholder -->
                <div x-cloak x-show="sourceType === 'csv'" style="display: none;">
                    <div style="padding: 60px 40px; border: 2px dashed #cbd5e1; border-radius: 12px; text-align: center; margin-bottom: 32px; background: #f8fafc; transition: all 0.2s;" ondragover="this.style.borderColor='#10b981'; this.style.background='#ecfdf5';" ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                        <div style="width: 64px; height: 64px; background: #d1fae5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px auto;">
                            <i class="fa fa-cloud-upload"></i>
                        </div>
                        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #1e293b;">Upload Data Files</h3>
                        <p style="margin: 0; font-size: 14px; color: #64748b; max-width: 300px; margin: 0 auto;">Drag and drop your Excel (.xlsx) or CSV files here, or click to browse.</p>
                        <button type="button" style="margin-top: 24px; padding: 10px 24px; background: white; color: #10b981; border: 1px solid #10b981; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.1);">Browse Files</button>
                    </div>
                </div>

                <button type="submit" style="width: 100%; height: 48px; font-size: 15px; font-weight: 600; color: white; background: linear-gradient(to right, #0ea5e9, #2563eb); border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(14, 165, 233, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(14, 165, 233, 0.3)';">
                    <i class="fa fa-link"></i> Test Connection & Save
                </button>
            </div>

            <!-- Right Info Area -->
            <div style="flex: 1;">
                <div style="background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%); border: 1px solid #bbf7d0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(22, 101, 52, 0.02);">
                    <div style="width: 48px; height: 48px; background: #dcfce7; color: #166534; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px;">
                        <i class="fa fa-shield-check" class="fa fa-shield"></i>
                    </div>
                    
                    <h4 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: #166534;">Connection Security</h4>
                    
                    <p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 20px;">
                        Your database credentials are encrypted at rest and in transit. We use them strictly for the migration process.
                    </p>
                    
                    <h5 style="margin: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">Best Practices</h5>
                    
                    <ul style="margin: 0; padding-left: 0; list-style: none;">
                        <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fa fa-check-circle" style="color: #22c55e; margin-top: 2px;"></i>
                            <span style="font-size: 13px; color: #475569; line-height: 1.5;">Create a dedicated <strong>read-only</strong> database user for migration to prevent accidental data modification.</span>
                        </li>
                        <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fa fa-check-circle" style="color: #22c55e; margin-top: 2px;"></i>
                            <span style="font-size: 13px; color: #475569; line-height: 1.5;">Whitelist our server IP <code>192.168.1.55</code> in your database firewall rules.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fa fa-check-circle" style="color: #22c55e; margin-top: 2px;"></i>
                            <span style="font-size: 13px; color: #475569; line-height: 1.5;">Revoke the database user access immediately after migration completes.</span>
                        </li>
                    </ul>
                </div>
            </div>

        </form>
    </div>
</div>
</x-layout>
