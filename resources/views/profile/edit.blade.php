<x-layout>
    @push('styles')
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .profile-header {
            margin-bottom: 25px;
        }
        .profile-card {
            background: #fff;
            border: 1px solid #e7ecf1;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .profile-card-header {
            padding: 15px 20px;
            background: #fafbfc;
            border-bottom: 1px solid #eef1f5;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .profile-card-header h3 {
            font-size: 14px;
            font-weight: 800;
            color: #2d3748;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .profile-card-body {
            padding: 25px 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #4a5568;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }
        .form-input {
            width: 100%;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #4b77be;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(75, 119, 190, 0.1);
        }
        .btn-profile {
            background: #4b77be;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-profile:hover {
            background: #3a62a4;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-danger {
            background: #d05454;
        }
        .btn-danger:hover {
            background: #b04444;
        }
        .status-message {
            font-size: 13px;
            color: #2ab4a5;
            font-weight: 600;
            margin-top: 10px;
        }
    </style>
    @endpush

    <div class="profile-container">
        <div class="profile-header">
            <h1 style="font-family: 'Oswald', sans-serif; font-size: 28px; font-weight: 700; color: #1a202c; text-transform: uppercase; letter-spacing: -0.5px;">Account Profile</h1>
            <p style="color: #718096; font-size: 14px;">Manage your account information and security settings.</p>
        </div>

        <!-- Profile Information -->
        <div class="profile-card">
            <div class="profile-card-header">
                <i class="fa fa-user" style="color: #4b77be;"></i>
                <h3>Profile Information</h3>
            </div>
            <div class="profile-card-body">
                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-profile">Save Changes</button>
                        @if (session('status') === 'profile-updated')
                            <p class="status-message">Saved successfully.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Password -->
        <div class="profile-card">
            <div class="profile-card-header">
                <i class="fa fa-lock" style="color: #4b77be;"></i>
                <h3>Update Password</h3>
            </div>
            <div class="profile-card-body">
                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="form-group">
                        <label class="form-label" for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-input" autocomplete="current-password">
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" id="password" name="password" class="form-input" autocomplete="new-password">
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password">
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-profile">Update Password</button>
                        @if (session('status') === 'password-updated')
                            <p class="status-message">Password updated successfully.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

    <!-- Company Email Configuration -->
        <div class="profile-card">
            <div class="profile-card-header">
                <i class="fa fa-envelope" style="color: #4b77be;"></i>
                <h3>Company Email Configuration</h3>
            </div>
            <div class="profile-card-body">
                <p style="font-size: 13px; color: #718096; margin-bottom: 20px;">Configure the email account used to send documents (Invoices, HBL, etc.) on behalf of your company. We use Gmail's secure servers by default.</p>
                <form method="post" action="{{ route('profile.email-settings') }}">
                    @csrf
                    @method('patch')

                    <!-- Hidden default fields for SMTP -->
                    <input type="hidden" name="smtp_host" value="smtp.gmail.com">
                    <input type="hidden" name="smtp_port" value="587">
                    <input type="hidden" name="smtp_encryption" value="tls">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label" for="smtp_username">Email Address</label>
                            @php
                                $company = $user->company ?? ($user->isSuperAdmin() ? \App\Models\Company::first() : null);
                            @endphp
                            <input type="email" id="smtp_username" name="smtp_username" class="form-input" value="{{ old('smtp_username', $company->smtp_username ?? '') }}" placeholder="youremail@gmail.com" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="smtp_password">App Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" class="form-input" placeholder="Leave blank to keep current password">
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-profile">Save Email Settings</button>
                        @if (session('status') === 'email-settings-updated')
                            <p class="status-message">Email settings saved successfully.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Account -->
        <div class="profile-card" style="border-top: 3px solid #d05454;">
            <div class="profile-card-header" style="background: rgba(208, 84, 84, 0.05);">
                <i class="fa fa-exclamation-triangle" style="color: #d05454;"></i>
                <h3 style="color: #d05454;">Delete Account</h3>
            </div>
            <div class="profile-card-body">
                <p style="font-size: 13px; color: #718096; margin-bottom: 20px;">Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.</p>
                
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="form-group">
                        <label class="form-label" for="del_password">Password</label>
                        <input type="password" id="del_password" name="password" class="form-input" placeholder="Current Password">
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>

                    <button type="submit" class="btn-profile btn-danger">Permanently Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</x-layout>
