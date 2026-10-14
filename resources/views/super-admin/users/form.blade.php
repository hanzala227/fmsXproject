@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    <div style="max-width: 800px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <div style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 14px; font-weight: 600; color: #1e293b;">
                <i class="fa fa-{{ $user ? 'pencil' : 'user-plus' }}" style="color: #405189; margin-right: 6px;"></i>
                {{ $user ? 'Edit User: ' . $user->name : 'Add New User' }}
            </div>
            <a href="{{ route('super-admin.users.index') }}" style="color: #64748b; text-decoration: none; font-size: 11px;"><i class="fa fa-arrow-left"></i> Back to list</a>
        </div>

        <form action="{{ $user ? route('super-admin.users.update', $user) : route('super-admin.users.store') }}" method="POST" enctype="multipart/form-data" style="padding: 20px;">
            @csrf
            @if($user) @method('PUT') @endif

            @if($errors->any())
            <div style="margin-bottom: 16px; padding: 10px 12px; background: #fef2f2; border: 1px solid #f87171; border-radius: 4px; color: #991b1b; font-size: 11px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                {{-- Name --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                        style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                        @focus="$el.style.borderColor='#405189'" @blur="$el.style.borderColor='#cbd5e1'">
                </div>

                {{-- Email --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                        style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                        @focus="$el.style.borderColor='#405189'" @blur="$el.style.borderColor='#cbd5e1'">
                </div>
            </div>

            @if(!$user)
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                {{-- Password --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Password *</label>
                    <input type="password" name="password" required minlength="8"
                        style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                        style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
                </div>
            </div>
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                {{-- Role --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Role *</label>
                    <select name="role" required style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
                        <option value="">Select Role</option>
                        @foreach(['SuperAdmin', 'Admin', 'Customer', 'Operation', 'Support', 'Developer'] as $role)
                        <option value="{{ $role }}" {{ old('role', $user->role ?? '') === $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department ?? '') }}"
                        style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                {{-- Company --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Company (Leave blank for internal staff)</label>
                    <select name="company_id" style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
                        <option value="">-- No Company (Internal) --</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $user->company_id ?? '') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Status *</label>
                    <select name="status" required style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
                        <option value="Enable" {{ old('status', $user->status ?? 'Enable') === 'Enable' ? 'selected' : '' }}>Active (Enable)</option>
                        <option value="Disable" {{ old('status', $user->status ?? '') === 'Disable' ? 'selected' : '' }}>Inactive (Disable)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                {{-- Profile Photo --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Profile Photo</label>
                    <input type="file" name="profile_photo" accept="image/*"
                        style="width: 100%; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px; padding: 4px;">
                    @if($user && $user->profile_photo_path)
                        <div style="margin-top: 4px; font-size: 10px; color: #64748b;">Current photo exists. Uploading new one will replace it.</div>
                    @endif
                </div>

                {{-- Access Expiry --}}
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Access Expiry (Optional)</label>
                    <input type="date" name="access_expiry" value="{{ old('access_expiry', $user?->access_expiry?->format('Y-m-d') ?? '') }}"
                        style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
                </div>
            </div>

            {{-- MFA Toggle --}}
            <div style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; color: #475569; cursor: pointer;">
                    <input type="checkbox" name="mfa_enabled" value="1" {{ old('mfa_enabled', $user->mfa_enabled ?? false) ? 'checked' : '' }} style="cursor: pointer;">
                    Require Multi-Factor Authentication (MFA)
                </label>
            </div>

            {{-- Actions --}}
            <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('super-admin.users.index') }}" style="height: 32px; padding: 0 16px; display: inline-flex; align-items: center; font-size: 11px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; text-decoration: none; background: #fff;">Cancel</a>
                <button type="submit" style="height: 32px; padding: 0 20px; font-size: 11px; font-weight: 600; border: none; border-radius: 3px; background: #405189; color: #fff; cursor: pointer;">
                    <i class="fa fa-save" style="margin-right: 6px;"></i> {{ $user ? 'Update User' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
