<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the company email settings.
     */
    public function updateEmailSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'smtp_username' => 'required|email',
            'smtp_password' => 'nullable|string'
        ]);

        $company = $request->user()->company;
        
        // Fallback for Super Admin: Allow them to configure the primary tenant's email for testing
        if (!$company && $request->user()->isSuperAdmin()) {
            $company = \App\Models\Company::first();
        }
        if ($company) {
            $company->smtp_host = $request->input('smtp_host', 'smtp.gmail.com');
            $company->smtp_port = $request->input('smtp_port', '587');
            $company->smtp_username = $request->input('smtp_username');
            
            if ($request->filled('smtp_password')) {
                $company->smtp_password = \Illuminate\Support\Facades\Crypt::encryptString($request->input('smtp_password'));
            }
            
            $company->smtp_encryption = $request->input('smtp_encryption', 'tls');
            $company->save();
        }

        return Redirect::route('profile.edit')->with('status', 'email-settings-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
