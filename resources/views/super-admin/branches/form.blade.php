@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px; max-width: 1000px; margin: 0 auto;">
    {{-- Header --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">
                    {{ $branch ? 'Edit Branch: ' . $branch->name : 'Register New Freight Branch' }}
                </h1>
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">
                    {{ $branch ? $branch->code : 'International Logistics Hub' }}
                </span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">
                Configure operational parameters, IATA/UNLOCODE station affiliations, and management contacts
            </p>
        </div>
        <div>
            <a href="{{ route('super-admin.branches.index') }}" style="height: 32px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; background: #fff; text-decoration: none;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-arrow-left"></i> Back to Branches
            </a>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
    <div style="margin-bottom: 20px; padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; color: #991b1b; font-size: 12px;">
        <div style="font-weight: 700; margin-bottom: 4px;"><i class="fa fa-exclamation-triangle"></i> Please correct the following errors:</div>
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $branch ? route('super-admin.branches.update', $branch) : route('super-admin.branches.store') }}">
        @csrf
        @if($branch)
            @method('PUT')
        @endif

        {{-- Section 1: Tenant & Station Identity --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-building" style="color: #405189;"></i> Station Identity & Company Affiliation
            </div>
            <div style="padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        Parent Freight Forwarder Company <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="company_id" required style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff; color: #1e293b;">
                        <option value="">Select Company...</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ (old('company_id', $branch ? $branch->company_id : $selectedCompanyId) == $comp->id) ? 'selected' : '' }}>
                                {{ $comp->name }} (Code: {{ $comp->code ?: 'ID:'.$comp->id }}) - {{ $comp->country ?: 'Global' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        Branch / Station Code <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="code" value="{{ old('code', $branch ? $branch->code : '') }}" placeholder="e.g. DXB-01, KHI-HQ, JFK-AIR" required style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; font-family: monospace; text-transform: uppercase; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                    <span style="font-size: 10px; color: #64748b; margin-top: 2px; display: block;">Unique operational identifier within this tenant company</span>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        Branch Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $branch ? $branch->name : '') }}" placeholder="e.g. Apex Dubai Air & Ocean Hub" required style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        Station Type <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="branch_type" required style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                        @foreach($branchTypes as $type)
                            <option value="{{ $type }}" {{ (old('branch_type', $branch ? $branch->branch_type : 'Regional Hub') == $type) ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; align-items: center; padding-top: 18px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; font-weight: 600; color: #1e293b;">
                        <input type="checkbox" name="is_head_office" value="1" {{ old('is_head_office', $branch ? $branch->is_head_office : false) ? 'checked' : '' }} style="width: 16px; height: 16px; cursor: pointer;">
                        <span>Designate as Company Head Office (HQ)</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Section 2: Logistics Hub & Port Regulatory Codes --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-globe" style="color: #0284c7;"></i> International Freight Hub & Regulatory Identifiers
            </div>
            <div style="padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        IATA Airport Code (3 Chars)
                    </label>
                    <input type="text" name="iata_code" value="{{ old('iata_code', $branch ? $branch->iata_code : '') }}" maxlength="10" placeholder="e.g. DXB, KHI, JFK, LHR" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; font-family: monospace; text-transform: uppercase; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                    <span style="font-size: 10px; color: #64748b; margin-top: 2px; display: block;">Air cargo terminal & AWB flight dispatch code</span>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        UN/LOCODE Port Hub (5 Chars)
                    </label>
                    <input type="text" name="unlocode" value="{{ old('unlocode', $branch ? $branch->unlocode : '') }}" maxlength="10" placeholder="e.g. AEDXB, PKKHI, USNYC, GBLON" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; font-family: monospace; text-transform: uppercase; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                    <span style="font-size: 10px; color: #64748b; margin-top: 2px; display: block;">United Nations Code for Ocean Trade and Customs</span>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        Customs Station / Terminal License No
                    </label>
                    <input type="text" name="customs_code" value="{{ old('customs_code', $branch ? $branch->customs_code : '') }}" placeholder="e.g. CUST-DXB-9024" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        Tax / VAT / TRN / NTN Number
                    </label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $branch ? $branch->tax_number : '') }}" placeholder="e.g. 100239482900003" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>
            </div>
        </div>

        {{-- Section 3: Physical Station Geography --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-map-marker" style="color: #ef4444;"></i> Physical Station Address & Geography
            </div>
            <div style="padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Country</label>
                    <input type="text" name="country" value="{{ old('country', $branch ? $branch->country : '') }}" placeholder="e.g. United Arab Emirates, Pakistan, USA" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">City / Port Area</label>
                    <input type="text" name="city" value="{{ old('city', $branch ? $branch->city : '') }}" placeholder="e.g. Dubai, Karachi, New York" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">State / Province</label>
                    <input type="text" name="state_province" value="{{ old('state_province', $branch ? $branch->state_province : '') }}" placeholder="e.g. Dubai Emirate, Sindh, New York" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Postal / ZIP Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $branch ? $branch->postal_code : '') }}" placeholder="e.g. 74200, 10001" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Physical Terminal / Office Street Address</label>
                    <textarea name="address" rows="2" placeholder="Full operational street address, logistics park, gate number, or cargo village office..." style="width: 100%; padding: 8px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">{{ old('address', $branch ? $branch->address : '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 4: Operational Management & Communication --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-phone" style="color: #10b981;"></i> Operational Management & Dispatch Contacts
            </div>
            <div style="padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Station / Branch Manager Name</label>
                    <input type="text" name="manager_name" value="{{ old('manager_name', $branch ? $branch->manager_name : '') }}" placeholder="e.g. Tariq Mehmood" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Official Branch Email</label>
                    <input type="email" name="email" value="{{ old('email', $branch ? $branch->email : '') }}" placeholder="e.g. dxb.station@freightforwarder.com" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Direct Phone / Hotline</label>
                    <input type="text" name="phone" value="{{ old('phone', $branch ? $branch->phone : '') }}" placeholder="e.g. +971 4 299 0000" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Fax Number</label>
                    <input type="text" name="fax" value="{{ old('fax', $branch ? $branch->fax : '') }}" placeholder="e.g. +971 4 299 0001" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        Operational Timezone <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="timezone" required style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                        @foreach($timezones as $tzVal => $tzLabel)
                            <option value="{{ $tzVal }}" {{ (old('timezone', $branch ? $branch->timezone : 'UTC') == $tzVal) ? 'selected' : '' }}>
                                {{ $tzLabel }}
                            </option>
                        @endforeach
                    </select>
                    <span style="font-size: 10px; color: #64748b; margin-top: 2px; display: block;">Used for flight schedules, cut-off times, and document timestamps</span>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">
                        Operational / Station Currency <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="currency" required style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                        @foreach($currencies as $curr)
                            <option value="{{ $curr }}" {{ (old('currency', $branch ? $branch->currency : 'USD') == $curr) ? 'selected' : '' }}>
                                {{ $curr }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 5: Status & Notes --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 24px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-info-circle" style="color: #6366f1;"></i> Status & Operational Instructions
            </div>
            <div style="padding: 18px; display: flex; flex-direction: column; gap: 16px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; font-weight: 600; color: #1e293b;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch ? $branch->is_active : true) ? 'checked' : '' }} style="width: 16px; height: 16px; cursor: pointer;">
                    <span>Branch Status: Active & Operational</span>
                </label>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Internal Station Remarks / Operating Guidelines</label>
                    <textarea name="notes" rows="3" placeholder="Special terminal handling notes, customs bonded warehouse registration details, or night shift dispatch contacts..." style="width: 100%; padding: 8px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">{{ old('notes', $branch ? $branch->notes : '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Submit Bar --}}
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('super-admin.branches.index') }}" style="height: 36px; padding: 0 18px; display: inline-flex; align-items: center; font-size: 12px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; background: #fff; text-decoration: none;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                Cancel
            </a>
            <button type="submit" style="height: 36px; padding: 0 24px; font-size: 12px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 4px; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                <i class="fa fa-save" style="margin-right: 6px;"></i> {{ $branch ? 'Update Freight Branch' : 'Register Branch' }}
            </button>
        </div>
    </form>
</div>
@endsection
