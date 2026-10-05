@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b;">
            <i class="fa fa-puzzle-piece" style="color: #405189; margin-right: 6px;"></i> All Modules
        </div>
        
        <button onclick="document.getElementById('createModuleModal').style.display='flex'" style="height: 32px; padding: 0 16px; font-size: 12px; font-weight: 500; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center;">
            <i class="fa fa-plus" style="margin-right: 6px;"></i> Add Module
        </button>
    </div>

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 60px;">ID</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Module Name & Key</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Category</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Version / Released</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569;">Active Companies</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 100px;">Status</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569; width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($modules as $module)
                <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding: 12px 16px; color: #1e293b; font-weight: 500;">
                        {{ $module->id }}
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">{{ $module->name }}</div>
                        <div style="font-size: 11px; color: #64748b; font-family: monospace;">{{ $module->key }}</div>
                    </td>
                    <td style="padding: 12px 16px; color: #1e293b; font-weight: 500;">
                        {{ $module->category ?? 'Uncategorized' }}
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        <div style="font-weight: 500; color: #1e293b;">v{{ $module->version }}</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $module->release_date ? $module->release_date->format('M d, Y') : '-' }}</div>
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        <span style="background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 12px; font-weight: 600; font-size: 11px;">
                            {{ $module->companies()->count() }}
                        </span>
                    </td>
                    <td style="padding: 12px 16px;">
                        @php
                            $sColors = [
                                'Active' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                'Beta' => ['bg' => '#fef9c3', 'text' => '#854d0e'],
                                'Deprecated' => ['bg' => '#ffedd5', 'text' => '#9a3412'],
                                'Inactive' => ['bg' => '#f1f5f9', 'text' => '#475569']
                            ];
                            $c = $sColors[$module->status] ?? $sColors['Inactive'];
                        @endphp
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; background: {{ $c['bg'] }}; color: {{ $c['text'] }}; font-size: 11px; font-weight: 600;">
                            {{ $module->status }}
                        </span>
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        <a href="{{ route('super-admin.modules.show', $module->id) }}" style="color: #405189; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #f1f5f9; border-radius: 4px; text-decoration: none;" title="Edit Module">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 30px; text-align: center; color: #64748b;">
                        <i class="fa fa-puzzle-piece" style="font-size: 24px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                        No modules found. Please run the seeder or add a new module.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Module Modal -->
<div id="createModuleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; width: 400px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="margin: 0; font-size: 15px; color: #1e293b; font-weight: 600;">Add New Module</h3>
            <button onclick="document.getElementById('createModuleModal').style.display='none'" style="background: none; border: none; font-size: 16px; color: #64748b; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('super-admin.modules.store') }}" style="padding: 20px;">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Module Name *</label>
                <input type="text" name="name" required placeholder="e.g. Ocean Import" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Unique Key * (lowercase, no spaces)</label>
                <input type="text" name="key" required placeholder="e.g. ocean_import" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Category</label>
                <input type="text" name="category" placeholder="e.g. Freight, Logistics, Core" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Version</label>
                <input type="text" name="version" value="1.0.0" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="document.getElementById('createModuleModal').style.display='none'" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Cancel</button>
                <button type="submit" style="height: 36px; padding: 0 16px; border: none; background: #405189; color: #fff; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Save Module</button>
            </div>
        </form>
    </div>
</div>
@endsection
