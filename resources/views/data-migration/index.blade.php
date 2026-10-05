<x-layout>
<div style="padding: 32px; max-width: 1100px; margin: 0 auto; font-family: 'Inter', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0; display: flex; align-items: center; gap: 10px;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0ea5e9, #3b82f6); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.2);">
                    <i class="fa fa-database" style="font-size: 16px;"></i>
                </div>
                Data Migration Projects
            </h2>
            <p style="margin: 0; font-size: 14px; color: #64748b; margin-left: 50px;">Manage, monitor, and execute legacy data imports.</p>
        </div>
        <div>
            <a href="{{ route('data-migration.create') }}" style="display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(to right, #0ea5e9, #2563eb); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(14, 165, 233, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(14, 165, 233, 0.3)';">
                <i class="fa fa-plus" style="margin-right: 8px;"></i> New Migration
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom: 24px; padding: 16px 20px; background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981; border-radius: 8px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.1); display: flex; align-items: center; gap: 12px; font-weight: 500;">
            <i class="fa fa-check-circle" style="font-size: 20px; color: #10b981;"></i> {{ session('success') }}
        </div>
    @endif

    <div style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.02);">
        
        <!-- Toolbar -->
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; gap: 16px; align-items: center; background: #f8fafc;">
            <div style="position: relative; width: 320px;">
                <i class="fa fa-search" style="position: absolute; left: 16px; top: 12px; color: #94a3b8; font-size: 14px;"></i>
                <input type="text" placeholder="Search projects by code or system..." style="width: 100%; height: 40px; padding: 0 16px 0 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; transition: all 0.2s; color: #334155;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14, 165, 233, 0.1)';" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
            </div>
            
            <select style="height: 40px; padding: 0 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; background: #fff; color: #334155; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='#0ea5e9';" onblur="this.style.borderColor='#cbd5e1';">
                <option>All Statuses</option>
                <option>Connecting</option>
                <option>Mapping</option>
                <option>Validating</option>
                <option>Importing</option>
                <option>Completed</option>
                <option>Failed</option>
            </select>
            
            <div style="flex: 1;"></div>
            
            <button style="height: 40px; padding: 0 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background: #fff; color: #475569; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='#fff';">
                <i class="fa fa-filter"></i> Filters
            </button>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #ffffff; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="padding: 16px 24px;">Project ID</th>
                    <th style="padding: 16px 20px;">Source System</th>
                    <th style="padding: 16px 20px;">Type</th>
                    <th style="padding: 16px 20px;">Records</th>
                    <th style="padding: 16px 20px;">Status</th>
                    <th style="padding: 16px 20px; width: 180px;">Progress</th>
                    <th style="padding: 16px 24px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc';" onmouseout="this.style.backgroundColor='transparent';">
                    <td style="padding: 16px 24px;">
                        <div style="font-size: 14px; font-weight: 600; color: #0ea5e9;">{{ $project->project_code }}</div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">{{ $project->created_at->format('M d, Y') }}</div>
                    </td>
                    <td style="padding: 16px 20px; font-size: 13px; font-weight: 500; color: #334155;">{{ $project->source_system ?: 'Unknown System' }}</td>
                    <td style="padding: 16px 20px;">
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #475569;">
                            @if($project->source_type == 'mysql') <i class="fa fa-database" style="color: #0ea5e9;"></i> MySQL
                            @elseif($project->source_type == 'sqlsrv') <i class="fa fa-database" style="color: #6366f1;"></i> SQL Server
                            @elseif($project->source_type == 'csv') <i class="fa fa-file-excel-o" style="color: #10b981;"></i> CSV/Excel
                            @else <i class="fa fa-cloud" style="color: #8b5cf6;"></i> API @endif
                        </div>
                    </td>
                    <td style="padding: 16px 20px; font-size: 13px; font-weight: 500; color: #334155;">{{ number_format($project->total_records) }}</td>
                    <td style="padding: 16px 20px;">
                        @if($project->status == 'connecting')
                            <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #e2e8f0;"><i class="fa fa-plug" style="margin-right: 4px;"></i> CONNECTING</span>
                        @elseif($project->status == 'mapping')
                            <span style="background: #fef9c3; color: #ca8a04; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #fde047;"><i class="fa fa-sitemap" style="margin-right: 4px;"></i> MAPPING</span>
                        @elseif($project->status == 'importing')
                            <span style="background: #dbeafe; color: #2563eb; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #bfdbfe;"><i class="fa fa-refresh fa-spin" style="margin-right: 4px;"></i> IMPORTING</span>
                        @elseif($project->status == 'completed')
                            <span style="background: #dcfce7; color: #16a34a; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #bbf7d0;"><i class="fa fa-check" style="margin-right: 4px;"></i> COMPLETED</span>
                        @elseif($project->status == 'failed')
                            <span style="background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #fecaca;"><i class="fa fa-times" style="margin-right: 4px;"></i> FAILED</span>
                        @else
                            <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #e2e8f0;">{{ strtoupper($project->status) }}</span>
                        @endif
                    </td>
                    <td style="padding: 16px 20px;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 600; color: #64748b;">
                                <span>Progress</span>
                                <span>{{ $project->progress }}%</span>
                            </div>
                            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                                @php
                                    $color = '#0ea5e9';
                                    if($project->status == 'completed') $color = '#10b981';
                                    if($project->status == 'failed') $color = '#ef4444';
                                    if($project->status == 'mapping') $color = '#f59e0b';
                                @endphp
                                <div style="width: {{ $project->progress }}%; height: 100%; background: {{ $color }}; border-radius: 3px;"></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 16px 24px; text-align: right;">
                        <a href="{{ route('data-migration.scan', $project->id) }}" style="padding: 6px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; color: #334155; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block; transition: all 0.2s;" onmouseover="this.style.borderColor='#0ea5e9'; this.style.color='#0ea5e9';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.color='#334155';">
                            {{ $project->status == 'completed' ? 'Report' : 'Continue' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 48px; text-align: center;">
                        <div style="width: 64px; height: 64px; background: #f1f5f9; color: #cbd5e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px auto;">
                            <i class="fa fa-folder-open-o"></i>
                        </div>
                        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #1e293b;">No Migration Projects Found</h3>
                        <p style="margin: 0 0 20px 0; font-size: 14px; color: #64748b;">You haven't started any data migrations yet.</p>
                        <a href="{{ route('data-migration.create') }}" style="display: inline-block; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                            Start New Migration
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-layout>
