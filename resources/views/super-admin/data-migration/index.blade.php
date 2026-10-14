@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0;">Migration Projects</h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;">Manage all customer data migration projects</p>
        </div>
        <div>
            <a href="{{ route('super-admin.data-migration.create') }}" style="display: inline-flex; align-items: center; background: #0ea5e9; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 500;">
                <i class="fa fa-plus" style="margin-right: 8px;"></i> New Migration
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 13px; display: flex; align-items: center;">
            <i class="fa fa-check-circle" style="margin-right: 8px; font-size: 16px;"></i> {{ session('success') }}
        </div>
    @endif

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        
        <!-- Toolbar -->
        <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 16px; align-items: center; background: #f8fafc;">
            <div style="position: relative; width: 300px;">
                <i class="fa fa-search" style="position: absolute; left: 12px; top: 10px; color: #94a3b8; font-size: 13px;"></i>
                <input type="text" placeholder="Search migration..." style="width: 100%; height: 34px; padding: 0 12px 0 32px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none;">
            </div>
            
            <select style="height: 34px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none; background: #fff;">
                <option>All Status</option>
                <option>Mapping</option>
                <option>Importing</option>
                <option>Completed</option>
                <option>Failed</option>
            </select>

            <select style="height: 34px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none; background: #fff;">
                <option>All Customers</option>
            </select>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; font-weight: 600;">
                    <th style="padding: 12px 16px;">#</th>
                    <th style="padding: 12px 16px;">Customer</th>
                    <th style="padding: 12px 16px;">Source</th>
                    <th style="padding: 12px 16px;">Database</th>
                    <th style="padding: 12px 16px;">Records</th>
                    <th style="padding: 12px 16px;">Status</th>
                    <th style="padding: 12px 16px;">Progress</th>
                    <th style="padding: 12px 16px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr style="border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #334155;">
                    <td style="padding: 12px 16px; color: #0ea5e9; font-weight: 500;">{{ $project->project_code }}</td>
                    <td style="padding: 12px 16px;">{{ $project->company->name ?? 'N/A' }}</td>
                    <td style="padding: 12px 16px;">{{ $project->source_system }}</td>
                    <td style="padding: 12px 16px;">
                        @if($project->source_type == 'mysql') MySQL
                        @elseif($project->source_type == 'sqlsrv') SQL Server
                        @elseif($project->source_type == 'csv') CSV/Excel
                        @else API @endif
                    </td>
                    <td style="padding: 12px 16px;">{{ number_format($project->total_records) }}</td>
                    <td style="padding: 12px 16px;">
                        @if($project->status == 'mapping')
                            <span style="background: #fef08a; color: #a16207; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Mapping</span>
                        @elseif($project->status == 'importing')
                            <span style="background: #dbeafe; color: #1d4ed8; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Importing</span>
                        @elseif($project->status == 'completed')
                            <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Completed</span>
                        @elseif($project->status == 'failed')
                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Failed</span>
                        @else
                            <span style="background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">{{ ucfirst($project->status) }}</span>
                        @endif
                    </td>
                    <td style="padding: 12px 16px; width: 150px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                @php
                                    $color = '#0ea5e9';
                                    if($project->status == 'completed') $color = '#22c55e';
                                    if($project->status == 'failed') $color = '#ef4444';
                                    if($project->status == 'importing') $color = '#22c55e'; // like screenshot
                                @endphp
                                <div style="width: {{ $project->progress }}%; height: 100%; background: {{ $color }};"></div>
                            </div>
                            <span style="font-size: 12px; color: #64748b; width: 32px; text-align: right;">{{ $project->progress }}%</span>
                        </div>
                    </td>
                    <td style="padding: 12px 16px;">
                        <a href="{{ route('super-admin.data-migration.scan', $project->id) }}" style="padding: 4px 12px; border: 1px solid #cbd5e1; border-radius: 4px; color: #0ea5e9; text-decoration: none; font-size: 12px; font-weight: 500; display: inline-block;">
                            {{ $project->status == 'completed' ? 'View' : 'Open' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 24px; text-align: center; color: #64748b; font-size: 13px;">No migration projects found. Click "New Migration" to start one.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
