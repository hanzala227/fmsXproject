@extends('super-admin.layout')

@section('content')
<div style="padding: 16px; max-width: 1100px; margin: 0 auto;">
    
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-bolt" style="color: #7c3aed;"></i> Frexo AI Credits & Pricing Engine
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;">
                Dynamically customize credit packages, per-credit pricing values (USD), and treasury bank wire accounts.
            </p>
        </div>
    </div>

    {{-- Sub-Navigation Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 24px;">
        <a href="{{ route('super-admin.carrier-tracking.index') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            <i class="fa fa-ship" style="margin-right: 4px;"></i> Carrier Tracking (ShipmentBeat)
        </a>
        <a href="{{ route('super-admin.ai-settings.index') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            <i class="fa fa-microchip" style="margin-right: 4px;"></i> AI Engine API (Gemini)
        </a>
        <a href="{{ route('super-admin.ai-settings.credits') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #7c3aed; border-bottom: 2px solid #7c3aed;">
            <i class="fa fa-bolt" style="margin-right: 4px; color: #7c3aed;"></i> AI Credits & Pricing Engine
        </a>
        <a href="{{ route('super-admin.integrations.api-keys') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            <i class="fa fa-key" style="margin-right: 4px;"></i> API Keys & Webhooks Console
        </a>
    </div>

    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 13px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-check-circle" style="font-size: 16px;"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #166534; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-exclamation-circle" style="font-size: 16px;"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #991b1b; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
    @endif

    @if ($errors->any())
        <div style="margin-bottom: 20px; padding: 12px 16px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; font-size: 13px;">
            <div style="font-weight: 600; margin-bottom: 4px;">Please review the following configuration errors:</div>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('super-admin.ai-settings.credits.update') }}">
        @csrf

        {{-- SECTION 1: Valuation & Global Currency Settings --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-sliders" style="color: #7c3aed;"></i> Global Credit Valuation & Top-Up Limits (USD)
                </h3>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748b;">
                    Controls per-credit top-up multipliers, custom limits, and complimentary welcome credits (all in USD).
                </p>
            </div>
            <div style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Per Credit USD Rate ($/Credit) *
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 10px; top: 8px; color: #94a3b8; font-size: 12px; font-weight: 600;">$</span>
                            <input type="number" step="0.0001" min="0.0001" max="10" name="per_credit_usd" value="{{ old('per_credit_usd', $creditSettings->per_credit_usd) }}" required
                                   style="width: 100%; height: 36px; padding-left: 24px; padding-right: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; box-sizing: border-box;">
                        </div>
                        <span style="font-size: 10px; color: #64748b; margin-top: 3px; display: block;">Default: $0.0450 (e.g. 1,000 credits = $45.00 USD)</span>
                    </div>

                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Min Custom Credit Top-Up *
                        </label>
                        <input type="number" min="1" step="10" name="min_custom_credits" value="{{ old('min_custom_credits', $creditSettings->min_custom_credits) }}" required
                               style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; box-sizing: border-box;">
                        <span style="font-size: 10px; color: #64748b; margin-top: 3px; display: block;">Minimum allowable credits in custom field</span>
                    </div>

                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            New Tenant Welcome Bonus *
                        </label>
                        <input type="number" step="1" min="0" name="initial_free_credits" value="{{ old('initial_free_credits', $creditSettings->initial_free_credits) }}" required
                               style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; box-sizing: border-box;">
                        <span style="font-size: 10px; color: #64748b; margin-top: 3px; display: block;">Complimentary credits granted upon tenant onboarding</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: Dynamic Pricing Packages Manager --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-cubes" style="color: #7c3aed;"></i> Dynamic AI Credit Packages Manager
                    </h3>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #64748b;">
                        Configure tiers displayed in tenant ERP Settings. Add, modify, or mark packages as popular choice.
                    </p>
                </div>
                <button type="button" onclick="window.addNewPackageRow()" style="height: 32px; padding: 0 12px; background: #7c3aed; color: #fff; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-plus"></i> Add Package
                </button>
            </div>

            <div style="padding: 20px;" id="packagesContainer">
                @php
                    $packages = old('packages', $creditSettings->packages ?? []);
                @endphp

                @foreach($packages as $idx => $pkg)
                <div class="package-card" id="pkg-card-{{ $idx }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin-bottom: 16px; position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                            <span class="pkg-index-badge" style="width: 22px; height: 22px; border-radius: 50%; background: #7c3aed; color: #fff; font-size: 11px; display: inline-flex; align-items: center; justify-content: center;">{{ $loop->iteration }}</span>
                            <span class="pkg-title-preview">{{ $pkg['name'] ?? 'Package' }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="checkbox" name="packages[{{ $idx }}][popular]" value="1" {{ !empty($pkg['popular']) ? 'checked' : '' }} style="accent-color: #7c3aed;">
                                <span>Most Popular Choice</span>
                            </label>
                            @if(count($packages) > 1)
                            <button type="button" onclick="window.removePackageRow('pkg-card-{{ $idx }}')" style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 4px; padding: 3px 8px; font-size: 11px; cursor: pointer;">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                            @endif
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 14px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Package ID *</label>
                            <input type="text" name="packages[{{ $idx }}][id]" value="{{ $pkg['id'] ?? '' }}" required placeholder="e.g. starter, growth"
                                   style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Display Name *</label>
                            <input type="text" name="packages[{{ $idx }}][name]" value="{{ $pkg['name'] ?? '' }}" required placeholder="e.g. Growth Pack"
                                   style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">AI Credits *</label>
                            <input type="number" name="packages[{{ $idx }}][credits]" value="{{ $pkg['credits'] ?? '' }}" required min="1" step="50"
                                   style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Price USD ($) *</label>
                            <input type="number" name="packages[{{ $idx }}][price_usd]" value="{{ $pkg['price_usd'] ?? '' }}" required min="0" step="0.5"
                                   style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Short Description</label>
                            <input type="text" name="packages[{{ $idx }}][desc]" value="{{ $pkg['desc'] ?? '' }}" placeholder="One sentence summary"
                                   style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Feature List (One per line)</label>
                            <textarea name="packages[{{ $idx }}][features]" rows="2" placeholder="~50 Document Extractions&#10;Priority Job Queue"
                                      style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; background: #fff; box-sizing: border-box;">{{ is_array($pkg['features'] ?? null) ? implode("\n", $pkg['features']) : ($pkg['features'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- SECTION 3: Bank Wire Transfer Remittance Configuration --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 24px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-university" style="color: #2563eb;"></i> Bank Wire Transfer Beneficiary Settings
                </h3>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748b;">
                    Bank account title, IBAN, and remittance instructions presented to tenants when uploading wire transfer advice.
                </p>
            </div>
            <div style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Beneficiary Bank Name *</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $creditSettings->bank_name) }}" required
                               style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Beneficiary Account Title *</label>
                        <input type="text" name="bank_account_title" value="{{ old('bank_account_title', $creditSettings->bank_account_title) }}" required
                               style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">IBAN / Account Number *</label>
                        <input type="text" name="bank_iban" value="{{ old('bank_iban', $creditSettings->bank_iban) }}" required
                               style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-family: monospace; font-weight: 600; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">SWIFT / BIC Code</label>
                        <input type="text" name="bank_swift" value="{{ old('bank_swift', $creditSettings->bank_swift) }}"
                               style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-family: monospace; box-sizing: border-box;">
                    </div>
                </div>

                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Remittance Note / Instructions</label>
                    <textarea name="bank_instructions" rows="2" placeholder="Special guidance for remitting foreign freight operators"
                              style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">{{ old('bank_instructions', $creditSettings->bank_instructions) }}</textarea>
                </div>
            </div>

            <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                <button type="submit" style="height: 38px; padding: 0 24px; background: #7c3aed; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(124,58,237,0.3);">
                    <i class="fa fa-save"></i> Save All Dynamic Settings
                </button>
            </div>
        </div>
    </form>

    {{-- SECTION 4: Live Treasury Audit - Pending Bank Wire Requests --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-clock-o" style="color: #f59e0b;"></i> Pending Bank Wire Transfer Requests
                </h3>
                <p style="margin: 2px 0 0; font-size: 12px; color: #64748b;">
                    Tenant wire advice pending physical clearance. Credits are released to tenant balance upon SuperAdmin approval.
                </p>
            </div>
            <div style="font-size: 11px; font-weight: 700; color: #b45309; background: #fef3c7; padding: 3px 8px; border-radius: 9999px; border: 1px solid #fde68a;">
                {{ $pendingWireRequests->where('credits_amount', 0)->count() }} Pending Approvals
            </div>
        </div>

        @if($pendingWireRequests->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Company / Tenant</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Operator</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Requested Credits</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Bank & Reference</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Deposit Slip</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Date & Status</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingWireRequests as $wire)
                    @php
                        $meta = is_array($wire->metadata) ? $wire->metadata : (json_decode($wire->metadata, true) ?? []);
                        $status = $meta['status'] ?? ($wire->credits_amount > 0 ? 'approved' : 'pending_verification');
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">
                            {{ $wire->company->name ?? 'Tenant #' . $wire->company_id }}
                        </td>
                        <td style="padding: 12px 16px; color: #475569;">
                            {{ $wire->user->name ?? 'System' }}
                            <div style="font-size: 10px; color: #94a3b8;">{{ $wire->user->email ?? '' }}</div>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 800; font-family: monospace; color: #7c3aed;">
                            {{ number_format($meta['requested_credits'] ?? 0) }} Credits
                        </td>
                        <td style="padding: 12px 16px; color: #334155;">
                            <div style="font-weight: 600;">{{ $meta['bank_name'] ?? 'Bank Wire' }}</div>
                            <div style="font-size: 10px; color: #64748b; font-family: monospace;">Ref: {{ $wire->reference_doc }}</div>
                        </td>
                        <td style="padding: 12px 16px;">
                            @if(!empty($meta['slip_path']))
                                <a href="{{ asset('storage/' . $meta['slip_path']) }}" target="_blank" style="color: #2563eb; font-weight: 600; text-decoration: none; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-file-text-o"></i> View Slip
                                </a>
                            @else
                                <span style="color: #94a3b8; font-size: 11px;">None</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px;">
                            <div style="font-size: 11px; color: #64748b;">{{ $wire->created_at->format('M d, Y h:i A') }}</div>
                            @if($status === 'approved')
                                <span style="font-size: 10px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 6px; border-radius: 4px;">APPROVED</span>
                            @elseif($status === 'rejected')
                                <span style="font-size: 10px; font-weight: 700; color: #dc2626; background: #fef2f2; padding: 2px 6px; border-radius: 4px;">REJECTED</span>
                            @else
                                <span style="font-size: 10px; font-weight: 700; color: #d97706; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">PENDING</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; text-align: right;">
                            @if($status === 'pending_verification')
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <form method="POST" action="{{ route('super-admin.ai-settings.wire-requests.approve', $wire->id) }}" onsubmit="return confirm('Confirm receipt of wire funds and release credits to tenant?');">
                                    @csrf
                                    <button type="submit" style="height: 28px; padding: 0 10px; background: #059669; color: #fff; border: none; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('super-admin.ai-settings.wire-requests.reject', $wire->id) }}" onsubmit="return confirm('Reject this wire transfer request?');">
                                    @csrf
                                    <button type="submit" style="height: 28px; padding: 0 10px; background: #f1f5f9; color: #dc2626; border: 1px solid #fecaca; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                            @else
                                <span style="font-size: 11px; color: #94a3b8;">Processed</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="padding: 32px 20px; text-align: center; color: #64748b;">
            <i class="fa fa-university" style="font-size: 24px; color: #cbd5e1; margin-bottom: 6px; display: block;"></i>
            <div style="font-size: 13px; font-weight: 600; color: #1e293b;">No pending bank wire requests</div>
            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">When tenant operators submit bank wire proofs, they stream here for treasury audit.</div>
        </div>
        @endif
    </div>

</div>

<script>
window.nextPackageIndex = {{ count($packages) }};

window.addNewPackageRow = function() {
    var idx = window.nextPackageIndex++;
    var html = `
    <div class="package-card" id="pkg-card-${idx}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin-bottom: 16px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
            <div style="font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <span class="pkg-index-badge" style="width: 22px; height: 22px; border-radius: 50%; background: #7c3aed; color: #fff; font-size: 11px; display: inline-flex; align-items: center; justify-content: center;">+</span>
                <span class="pkg-title-preview">New Custom Package</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <label style="font-size: 11px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    <input type="checkbox" name="packages[${idx}][popular]" value="1" style="accent-color: #7c3aed;">
                    <span>Most Popular Choice</span>
                </label>
                <button type="button" onclick="window.removePackageRow('pkg-card-${idx}')" style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 4px; padding: 3px 8px; font-size: 11px; cursor: pointer;">
                    <i class="fa fa-trash"></i> Remove
                </button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 14px;">
            <div>
                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Package ID *</label>
                <input type="text" name="packages[${idx}][id]" value="custom_${idx}" required placeholder="e.g. startup_pack"
                       style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Display Name *</label>
                <input type="text" name="packages[${idx}][name]" value="Custom Scale Pack" required placeholder="Display Name"
                       style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">AI Credits *</label>
                <input type="number" name="packages[${idx}][credits]" value="5000" required min="1" step="50"
                       style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Price USD ($) *</label>
                <input type="number" name="packages[${idx}][price_usd]" value="190.00" required min="0" step="0.5"
                       style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div>
                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Short Description</label>
                <input type="text" name="packages[${idx}][desc]" value="Custom volume processing tier." placeholder="One sentence summary"
                       style="width: 100%; height: 34px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; background: #fff; box-sizing: border-box;">
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Feature List (One per line)</label>
                <textarea name="packages[${idx}][features]" rows="2" placeholder="Feature 1&#10;Feature 2"
                          style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; background: #fff; box-sizing: border-box;">~500 Document Extractions&#10;Priority Queue&#10;Multi-Page Support</textarea>
            </div>
        </div>
    </div>`;

    document.getElementById('packagesContainer').insertAdjacentHTML('beforeend', html);
};

window.removePackageRow = function(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
};
</script>
@endsection
