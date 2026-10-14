@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b; display: flex; align-items: center;">
            <a href="{{ route('super-admin.modules.index') }}" style="color: #64748b; margin-right: 12px; text-decoration: none;"><i class="fa fa-arrow-left"></i></a>
            Module: {{ $module->name }}
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Left Column: Edit Form -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px;">
            <h3 style="font-size: 15px; font-weight: 600; color: #1e293b; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">Edit Module Details</h3>
            
            <form method="POST" action="{{ route('super-admin.modules.update', $module->id) }}">
                @csrf
                @method('PUT')
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Module Name *</label>
                        <input type="text" name="name" value="{{ $module->name }}" required style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Unique Key (Read Only)</label>
                        <input type="text" value="{{ $module->key }}" disabled style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f1f5f9; color: #64748b;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Category</label>
                        <input type="text" name="category" value="{{ $module->category }}" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Version</label>
                        <input type="text" name="version" value="{{ $module->version }}" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Status</label>
                    <select name="status" style="width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff; outline: none;">
                        <option value="Active" {{ $module->status == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Beta" {{ $module->status == 'Beta' ? 'selected' : '' }}>Beta</option>
                        <option value="Deprecated" {{ $module->status == 'Deprecated' ? 'selected' : '' }}>Deprecated</option>
                        <option value="Inactive" {{ $module->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Description</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; resize: vertical;">{{ $module->description }}</textarea>
                </div>

                <button type="submit" style="height: 36px; padding: 0 20px; font-size: 13px; font-weight: 500; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fa fa-save" style="margin-right: 6px;"></i> Update Module
                </button>
            </form>
        </div>

        <!-- Right Column: Usage Stats -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0; align-self: start;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; border-radius: 6px 6px 0 0;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 600; color: #1e293b;">Active Companies ({{ $companies->count() }})</h3>
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                @forelse($companies as $company)
                    <div style="padding: 12px 20px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #334155; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 500;">{{ $company->name }}</span>
                        <a href="{{ route('super-admin.companies.index', ['search' => $company->name]) }}" style="color: #405189; text-decoration: none; font-size: 11px;"><i class="fa fa-external-link"></i></a>
                    </div>
                @empty
                    <div style="padding: 20px; text-align: center; color: #64748b; font-size: 12px;">
                        No companies are currently using this module.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
