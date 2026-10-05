@extends('super-admin.layout')

@section('content')
<div style="padding: 16px; max-width: 800px; margin: 0 auto;">
    <div style="font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 20px;">
        <i class="fa fa-plus-circle" style="color: #405189; margin-right: 6px;"></i> Create New Ticket
    </div>

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #ef4444; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 13px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('super-admin.support-center.store') }}" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px;">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Subject *</label>
                <input type="text" name="title" value="{{ old('title') }}" required style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Module Name *</label>
                <select name="module_name" required style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                    <option value="">Select Module</option>
                    @foreach(['Ocean Import', 'Ocean Export', 'Air Import', 'Air Export', 'Trucking', 'Warehouse', 'Accounting', 'Master Data', 'System'] as $mod)
                        <option value="{{ $mod }}" {{ old('module_name') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Category</label>
                <select name="category" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                    <option value="">Select Category</option>
                    @foreach(['Bug', 'Feature Request', 'Training/How-To', 'Billing', 'Other'] as $cat)
                        <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Priority *</label>
                <select name="priority" required style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                    <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                    <option value="Medium" {{ old('priority', 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                    <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                    <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Assign To</label>
            <select name="assigned_agent_id" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                <option value="">Unassigned</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ old('assigned_agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }} ({{ $agent->role }})</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Description *</label>
            <textarea name="description" required rows="6" style="width: 100%; padding: 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; resize: vertical;">{{ old('description') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('super-admin.support-center.index') }}" style="height: 36px; padding: 0 20px; font-size: 13px; font-weight: 500; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                Cancel
            </a>
            <button type="submit" style="height: 36px; padding: 0 20px; font-size: 13px; font-weight: 500; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fa fa-save" style="margin-right: 6px;"></i> Create Ticket
            </button>
        </div>
    </form>
</div>
@endsection
