@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b;">
            <i class="fa fa-flag" style="color: #405189; margin-right: 6px;"></i> Feature Flags
        </div>
        
        <button onclick="document.getElementById('createFlagModal').style.display='flex'" style="height: 32px; padding: 0 16px; font-size: 12px; font-weight: 500; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center;">
            <i class="fa fa-plus" style="margin-right: 6px;"></i> Add Feature Flag
        </button>
    </div>

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569; width: 60px;">ID</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Feature Name & Key</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #475569;">Description</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569; width: 120px;">Status</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569; width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($flags as $flag)
                <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding: 12px 16px; color: #1e293b; font-weight: 500;">
                        {{ $flag->id }}
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        <div style="font-weight: 600; color: #1e293b; font-size: 13px;">{{ $flag->name }}</div>
                        <div style="font-size: 11px; color: #64748b; font-family: monospace;">{{ $flag->key }}</div>
                    </td>
                    <td style="padding: 12px 16px; color: #475569;">
                        {{ $flag->description ?? 'No description' }}
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        @if($flag->is_active)
                            <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;"><i class="fa fa-circle" style="font-size: 8px; margin-right: 4px;"></i> ENABLED</span>
                        @else
                            <span style="background: #f1f5f9; color: #64748b; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;"><i class="fa fa-circle" style="font-size: 8px; margin-right: 4px;"></i> DISABLED</span>
                        @endif
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                        <form method="POST" action="{{ route('super-admin.modules.feature-flags.toggle', $flag->id) }}" style="margin: 0;">
                            @csrf
                            @if($flag->is_active)
                                <button type="submit" style="height: 28px; padding: 0 10px; font-size: 11px; font-weight: 500; color: #fff; background: #ef4444; border: none; border-radius: 4px; cursor: pointer;">
                                    Disable
                                </button>
                            @else
                                <button type="submit" style="height: 28px; padding: 0 10px; font-size: 11px; font-weight: 500; color: #fff; background: #22c55e; border: none; border-radius: 4px; cursor: pointer;">
                                    Enable
                                </button>
                            @endif
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #64748b;">
                        <i class="fa fa-flag" style="font-size: 24px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                        No feature flags found. Add a feature flag to toggle new features.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Flag Modal -->
<div id="createFlagModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; width: 400px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="margin: 0; font-size: 15px; color: #1e293b; font-weight: 600;">Add Feature Flag</h3>
            <button onclick="document.getElementById('createFlagModal').style.display='none'" style="background: none; border: none; font-size: 16px; color: #64748b; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('super-admin.modules.feature-flags.store') }}" style="padding: 20px;">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Feature Name *</label>
                <input type="text" name="name" required placeholder="e.g. New AI Dashboard" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Unique Key * (e.g. feature_ai_dash)</label>
                <input type="text" name="key" required placeholder="feature_name" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Description</label>
                <input type="text" name="description" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
            </div>
            <div style="margin-bottom: 24px; padding-top: 8px;">
                <label style="display: flex; align-items: center; font-size: 13px; color: #334155; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" style="margin-right: 8px;"> Enable Immediately
                </label>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="document.getElementById('createFlagModal').style.display='none'" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Cancel</button>
                <button type="submit" style="height: 36px; padding: 0 16px; border: none; background: #405189; color: #fff; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">Save Flag</button>
            </div>
        </form>
    </div>
</div>
@endsection
