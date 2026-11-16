<x-layout>
    <div style="padding: 24px; max-width: 1300px; margin: 0 auto;">

        {{-- Page Header --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 10px rgba(124, 58, 237, 0.25);">
                        <i class="fa fa-bolt" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <h1 style="font-size: 22px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                            Frexo AI Credits & Document Billing
                        </h1>
                        <p style="font-size: 13px; color: #64748b; margin: 3px 0 0;">
                            Manage multimodal document extraction credits, automated top-ups, and organization usage quotas for <strong>{{ $company->name }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <button type="button" onclick="window.openPayModal('custom', 1000, 45)"
                        style="height: 36px; padding: 0 16px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: all 0.15s ease;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <i class="fa fa-sliders"></i> Custom Credit Amount
                </button>

                <button type="button" onclick="window.openBankModal()"
                        style="height: 36px; padding: 0 16px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); transition: all 0.15s ease;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <i class="fa fa-university"></i> Bank Wire Transfer
                </button>

                <button type="button" onclick="window.openPayModal('growth', 2500, 100)"
                        style="height: 36px; padding: 0 18px; background: #7c3aed; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(124,58,237,0.3); transition: background 0.15s ease;"
                        onmouseover="this.style.background='#6d28d9'" onmouseout="this.style.background='#7c3aed'">
                    <i class="fa fa-credit-card"></i> Pay Now / Top Up Credits
                </button>
            </div>
        </div>

        {{-- Flash Alerts --}}
        @if(session('success'))
        <div style="margin-bottom: 24px; padding: 14px 18px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; color: #065f46; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-check-circle" style="font-size: 18px; color: #10b981;"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div style="margin-bottom: 24px; padding: 14px 18px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b; font-size: 13px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-exclamation-triangle" style="font-size: 18px; color: #ef4444;"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #991b1b; cursor: pointer;"><i class="fa fa-times"></i></button>
        </div>
        @endif

        {{-- 4 Metric Telemetry KPI Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;">
            {{-- Balance Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); position: relative; overflow: hidden;">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Current AI Credit Balance</div>
                <div style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 6px; letter-spacing: -0.02em;">
                    @if($isUnlimited)
                        <span style="color: #7c3aed;">Unlimited</span>
                    @else
                        {{ number_format($balance, 2) }}
                    @endif
                </div>
                <div style="font-size: 11px; margin-top: 4px; font-weight: 600; color: {{ $isUnlimited ? '#7c3aed' : ($balance < 50 ? '#dc2626' : '#059669') }};">
                    <i class="fa {{ $isUnlimited ? 'fa-infinity' : ($balance < 50 ? 'fa-warning' : 'fa-check') }}"></i>
                    {{ $isUnlimited ? 'Enterprise Unlimited Active' : ($balance < 50 ? 'Low Balance — Top-up needed' : 'Sufficient Quota Available') }}
                </div>
            </div>

            {{-- Monthly Usage --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">This Month's Consumption</div>
                <div style="font-size: 26px; font-weight: 800; color: #dc2626; margin-top: 6px; letter-spacing: -0.02em;">
                    {{ number_format($monthlyCreditsUsed, 2) }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Credits burned since {{ now()->startOfMonth()->format('M 01') }}</div>
            </div>

            {{-- Documents Processed --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">AI Documents Parsed</div>
                <div style="font-size: 26px; font-weight: 800; color: #2563eb; margin-top: 6px; letter-spacing: -0.02em;">
                    {{ number_format($monthlyDocsProcessed) }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">HBLs, MAWBs & Commercial Invoices</div>
            </div>

            {{-- Total Lifetime Recharged --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Lifetime Credits Added</div>
                <div style="font-size: 26px; font-weight: 800; color: #059669; margin-top: 6px; letter-spacing: -0.02em;">
                    {{ number_format($totalLifetimeRecharged, 0) }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">All-time organization top-ups</div>
            </div>
        </div>

        {{-- Tiered Top-Up Pricing Packages --}}
        <div style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px;">
                <div>
                    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Instant AI Credit Top-Up Packages</h2>
                    <p style="font-size: 12px; color: #64748b; margin: 2px 0 0;">Credits never expire and are shared across all operators in {{ $company->name }}</p>
                </div>
                <span style="font-size: 11px; font-weight: 600; color: #059669; background: #ecfdf5; padding: 4px 10px; border-radius: 9999px; border: 1px solid #a7f3d0;">
                    <i class="fa fa-lock"></i> 256-Bit SSL Encrypted Checkout
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                @foreach($packages as $pkg)
                <div style="background: #fff; border: {{ $pkg['popular'] ? '2px solid #7c3aed' : '1px solid #e2e8f0' }}; border-radius: 14px; padding: 24px; position: relative; box-shadow: {{ $pkg['popular'] ? '0 10px 25px -5px rgba(124,58,237,0.1)' : '0 1px 3px rgba(0,0,0,0.04)' }}; display: flex; flex-direction: column; justify-content: space-between;">
                    @if($pkg['popular'])
                        <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #7c3aed; color: #fff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 3px 12px; border-radius: 9999px; box-shadow: 0 2px 4px rgba(124,58,237,0.3);">
                            Most Popular Choice
                        </div>
                    @endif

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: baseline;">
                            <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">{{ $pkg['name'] }}</h3>
                            <span style="font-size: 11px; font-weight: 700; color: #7c3aed; background: #f3e8ff; padding: 3px 8px; border-radius: 6px;">
                                {{ number_format($pkg['credits']) }} CREDITS
                            </span>
                        </div>

                        <p style="font-size: 12px; color: #64748b; margin: 8px 0 16px; line-height: 1.4;">{{ $pkg['desc'] }}</p>

                        <div style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: baseline; gap: 4px;">
                                <span style="font-size: 32px; font-weight: 800; color: #0f172a;">${{ number_format($pkg['price_usd'], 2) }}</span>
                                <span style="font-size: 12px; color: #64748b; font-weight: 500;">USD</span>
                            </div>
                        </div>

                        <ul style="list-style: none; padding: 0; margin: 0 0 24px; display: flex; flex-direction: column; gap: 10px;">
                            @foreach($pkg['features'] as $feat)
                            <li style="font-size: 12px; color: #334155; display: flex; align-items: center; gap: 8px;">
                                <i class="fa fa-check" style="color: #10b981; font-size: 12px;"></i>
                                <span>{{ $feat }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <button type="button" 
                            onclick="window.openPayModal('{{ $pkg['id'] }}', {{ $pkg['credits'] }}, {{ $pkg['price_usd'] }})"
                            style="width: 100%; height: 42px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.15s ease;
                            {{ $pkg['popular'] ? 'background: #7c3aed; color: #fff; border: none; box-shadow: 0 4px 6px -1px rgba(124,58,237,0.25);' : 'background: #f8fafc; color: #1e293b; border: 1px solid #cbd5e1;' }}"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <i class="fa fa-shopping-cart"></i> Pay Now (${{ number_format($pkg['price_usd'], 0) }})
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Credit Usage & Billing Transaction History Table --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-history" style="color: #64748b;"></i> Credit Transactions & Extraction Logs
                    </h3>
                    <p style="margin: 2px 0 0; font-size: 11px; color: #64748b;">Complete audit trail of all purchases, top-ups, and AI document OCR extractions</p>
                </div>
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">
                    Showing {{ $transactions->count() }} of {{ $transactions->total() }} records
                </div>
            </div>

            @if($transactions->count() > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Date & Time</th>
                                <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Type</th>
                                <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Amount</th>
                                <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Balance After</th>
                                <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Module / Document</th>
                                <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 16px; color: #64748b; white-space: nowrap;">
                                    {{ $tx->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td style="padding: 12px 16px;">
                                    @if($tx->type === 'BANK_WIRE_PENDING' || ($tx->credits_amount == 0 && in_array($tx->type, ['BANK_WIRE_PENDING', 'ADMIN_ADJUSTMENT'])))
                                        <span style="font-size: 10px; font-weight: 700; color: #b45309; background: #fef3c7; padding: 2px 8px; border-radius: 9999px; border: 1px solid #fde68a; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa fa-clock-o"></i> PENDING AUDIT
                                        </span>
                                    @elseif(in_array($tx->type, ['RECHARGE', 'ADMIN_ADJUSTMENT']) && $tx->credits_amount > 0)
                                        <span style="font-size: 10px; font-weight: 700; color: #047857; background: #ecfdf5; padding: 2px 8px; border-radius: 9999px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa fa-arrow-down"></i> TOP-UP
                                        </span>
                                    @elseif($tx->type === 'USAGE')
                                        <span style="font-size: 10px; font-weight: 700; color: #b91c1c; background: #fef2f2; padding: 2px 8px; border-radius: 9999px; border: 1px solid #fecaca; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa fa-arrow-up"></i> USAGE
                                        </span>
                                    @else
                                        <span style="font-size: 10px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 2px 8px; border-radius: 9999px; border: 1px solid #e2e8f0;">
                                            {{ $tx->type }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; font-weight: 700; font-family: monospace;">
                                    @php
                                        $meta = is_array($tx->metadata) ? $tx->metadata : json_decode($tx->metadata, true);
                                    @endphp
                                    @if($tx->type === 'BANK_WIRE_PENDING' || ($tx->credits_amount == 0 && isset($meta['requested_credits'])))
                                        <span style="color: #d97706; font-size: 11px;">Pending (+{{ number_format($meta['requested_credits'] ?? 0) }})</span>
                                    @elseif($tx->credits_amount > 0)
                                        <span style="color: #059669;">+{{ number_format($tx->credits_amount, 2) }}</span>
                                    @else
                                        <span style="color: #dc2626;">{{ number_format($tx->credits_amount, 2) }}</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; font-weight: 600; color: #0f172a; font-family: monospace;">
                                    {{ number_format($tx->balance_after, 2) }}
                                </td>
                                <td style="padding: 12px 16px; color: #334155;">
                                    <div style="font-weight: 600;">{{ $tx->module ? ucwords(str_replace('_', ' ', $tx->module)) : 'Frexo AI Engine' }}</div>
                                    @if($tx->reference_doc)
                                        <div style="font-size: 10px; color: #64748b; font-family: monospace;">Doc: {{ $tx->reference_doc }}</div>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; color: #475569; font-size: 11px;">
                                    {{ $tx->description ?: 'AI document extraction tokens processed' }}
                                    @if($tx->tokens_used > 0)
                                        <span style="color: #94a3b8;">({{ number_format($tx->tokens_used) }} tokens)</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                    {{ $transactions->links() }}
                </div>
            @else
                <div style="padding: 40px 20px; text-align: center; color: #64748b;">
                    <i class="fa fa-file-text-o" style="font-size: 28px; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                    <div style="font-size: 13px; font-weight: 600; color: #1e293b;">No AI transactions recorded yet</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Transactions will stream here in real time as your operators upload documents or recharge credits.</div>
                </div>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL 1: PAY NOW & GATEWAY SELECTOR (VANILLA DOM)        --}}
        {{-- ======================================================== --}}
        <div id="aiPayModal" 
             onclick="if(event.target === this) window.closePayModal();"
             style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.7); align-items: center; justify-content: center; z-index: 999999; backdrop-filter: blur(4px); padding: 16px;">
            <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; position: relative;">
                
                {{-- Modal Header --}}
                <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-lock" style="color: #10b981; font-size: 16px;"></i>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Secure Checkout & Credit Top-Up</h3>
                    </div>
                    <button type="button" onclick="window.closePayModal()" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer; padding: 4px;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('settings.ai-credits.checkout') }}" style="padding: 24px;">
                    @csrf
                    <input type="hidden" name="package_id" id="modalPackageId" value="growth">

                    {{-- Order Summary Card --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 600; color: #64748b;">Selected Package:</span>
                            <span id="summaryPkgName" style="font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase;">GROWTH PACK</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 600; color: #64748b;">Credits to Add:</span>
                            <span id="summaryCredits" style="font-size: 14px; font-weight: 800; color: #7c3aed;">2,500 AI CREDITS</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px dashed #cbd5e1;">
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">Total Payable:</span>
                            <div style="text-align: right;">
                                <span id="summaryPrice" style="font-size: 20px; font-weight: 800; color: #059669;">$100.00 USD</span>
                            </div>
                        </div>
                    </div>

                    {{-- Custom Credit Input wrapper --}}
                    <div id="customInputWrapper" style="display: none; margin-bottom: 20px;">
                        <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 6px;">Specify Custom Credits (min 100):</label>
                        <input type="number" name="custom_credits" id="modalCustomCredits" min="100" step="50" value="1000" oninput="window.updateCustomCredits(this.value)"
                               style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                    </div>

                    {{-- Developer Sandbox Quick Fill Pills --}}
                    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 11px; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 5px;">
                                <i class="fa fa-code" style="color: #7c3aed;"></i> Developer Test API Sandbox
                            </span>
                            <span style="font-size: 10px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 1px 6px; border-radius: 4px;">ACTIVE</span>
                        </div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" onclick="window.fillTestCard('success')" style="background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 10px; font-weight: 600; color: #1e293b; cursor: pointer;">
                                <i class="fa fa-check" style="color: #059669;"></i> Fill Standard Test Card (4242)
                            </button>
                            <button type="button" onclick="window.fillTestCard('decline')" style="background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 10px; font-weight: 600; color: #64748b; cursor: pointer;">
                                <i class="fa fa-times" style="color: #dc2626;"></i> Simulate Decline (0002)
                            </button>
                            <button type="button" onclick="window.fillTestCard('expired')" style="background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 10px; font-weight: 600; color: #64748b; cursor: pointer;">
                                <i class="fa fa-clock-o" style="color: #d97706;"></i> Simulate Expired
                            </button>
                        </div>
                    </div>

                    {{-- Integrated Card Details --}}
                    <div style="margin-bottom: 20px;">
                        <div style="margin-bottom: 12px;">
                            <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Name on Card:</label>
                            <input type="text" name="cardholder_name" id="cardholder_name" value="FreightX Developer" placeholder="Full name on card" required
                                   style="width: 100%; height: 36px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <label style="font-size: 11px; font-weight: 700; color: #334155;">Card Number:</label>
                                <div style="display: flex; gap: 4px; font-size: 15px;">
                                    <i class="fa fa-cc-visa" style="color: #1a1f71;"></i>
                                    <i class="fa fa-cc-mastercard" style="color: #eb001b;"></i>
                                    <i class="fa fa-cc-amex" style="color: #007bc1;"></i>
                                </div>
                            </div>
                            <div style="position: relative;">
                                <input type="text" name="card_number" id="card_number" value="4242 4242 4242 4242" placeholder="4242 4242 4242 4242" required maxlength="23"
                                       oninput="window.formatCardNumber(this)"
                                       style="width: 100%; height: 38px; padding: 0 12px; padding-left: 36px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: monospace; font-weight: 600; box-sizing: border-box;">
                                <i class="fa fa-credit-card" style="position: absolute; left: 12px; top: 12px; color: #94a3b8; font-size: 14px;"></i>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Expiry Date (MM/YY):</label>
                                <input type="text" name="card_expiry" id="card_expiry" value="12/28" placeholder="MM/YY" maxlength="5" required
                                       oninput="window.formatCardExpiry(this)"
                                       style="width: 100%; height: 36px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-family: monospace; box-sizing: border-box;">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">CVC / Security Code:</label>
                                <div style="position: relative;">
                                    <input type="password" name="card_cvc" id="card_cvc" value="123" placeholder="123" maxlength="4" required
                                           style="width: 100%; height: 36px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-family: monospace; box-sizing: border-box;">
                                    <i class="fa fa-lock" style="position: absolute; right: 12px; top: 10px; color: #94a3b8;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" onclick="window.closePayModal()" 
                                style="height: 38px; padding: 0 16px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                            Cancel
                        </button>
                        <button type="submit" 
                                style="height: 38px; padding: 0 22px; background: #059669; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(5,150,105,0.3); transition: background 0.15s ease;"
                                onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                            <i class="fa fa-lock"></i> Authorize & Pay (<span id="btnPayAmount">$100.00</span>)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL 2: BANK WIRE TRANSFER MANUAL REQUEST               --}}
        {{-- ======================================================== --}}
        <div id="aiBankModal"
             onclick="if(event.target === this) window.closeBankModal();"
             style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.7); align-items: center; justify-content: center; z-index: 999999; backdrop-filter: blur(4px); padding: 16px;">
            <div style="background: #fff; border-radius: 14px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; position: relative;">
                
                <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-university" style="color: #2563eb; font-size: 16px;"></i>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Bank Wire Transfer / Manual Top-Up</h3>
                    </div>
                    <button type="button" onclick="window.closeBankModal()" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer; padding: 4px;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('settings.ai-credits.bank-transfer') }}" enctype="multipart/form-data" style="padding: 24px;">
                    @csrf
                    
                    {{-- Company Bank Details --}}
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin-bottom: 18px; font-size: 12px; color: #1e40af;">
                        <div style="font-weight: 700; margin-bottom: 4px;">FreightX Platform Wire Beneficiary:</div>
                        <div>Bank: <strong>{{ $creditSettings->bank_name ?? 'JPMorgan Chase Bank / Habib Bank Limited' }}</strong></div>
                        <div>Account Title: <strong>{{ $creditSettings->bank_account_title ?? 'FreightX Technologies International' }}</strong></div>
                        <div>IBAN: <code style="background: #dbeafe; padding: 1px 4px; border-radius: 3px;">{{ $creditSettings->bank_iban ?? 'PK36HABB0000001234567890' }}</code></div>
                        @if(!empty($creditSettings->bank_swift))
                        <div>SWIFT/BIC: <code style="background: #dbeafe; padding: 1px 4px; border-radius: 3px;">{{ $creditSettings->bank_swift }}</code></div>
                        @endif
                        <div style="margin-top: 4px; font-size: 11px; color: #3b82f6;">{{ $creditSettings->bank_instructions ?: 'Please attach deposit slip / wire advice below for instant approval.' }}</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Credits Requested *</label>
                            <input type="number" name="amount_credits" min="500" value="2500" required
                                   style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Remitting Bank Name *</label>
                            <input type="text" name="bank_name" placeholder="e.g. Emirates NBD, HBL, Meezan" required
                                   style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 11px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Wire Reference / Deposit Slip No. *</label>
                        <input type="text" name="deposit_reference" placeholder="e.g. FT-20261114-8849" required
                               style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="font-size: 11px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Attach Proof (PDF or Image)</label>
                        <input type="file" name="deposit_slip" accept=".jpg,.jpeg,.png,.pdf"
                               style="width: 100%; font-size: 11px; color: #475569;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 11px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Notes for Accounting Team</label>
                        <textarea name="notes" rows="2" placeholder="Optional reference notes"
                                  style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; box-sizing: border-box;"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" onclick="window.closeBankModal()" 
                                style="height: 36px; padding: 0 16px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                            Cancel
                        </button>
                        <button type="submit" 
                                style="height: 36px; padding: 0 20px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                            Submit Wire Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Interactive Modal Controller Script (Works flawlessly with Turbo and Pure DOM) --}}
    <script>
        (function() {
            window.perCreditRate = {{ $creditSettings->per_credit_usd ?? 0.045 }};

            window.openPayModal = function(pkgId, credits, price) {
                var modal = document.getElementById('aiPayModal');
                if (!modal) return;

                var pkgInput = document.getElementById('modalPackageId');
                var customWrapper = document.getElementById('customInputWrapper');
                var customInput = document.getElementById('modalCustomCredits');

                var rate = window.perCreditRate || 0.045;

                if (pkgId === 'custom') {
                    if (pkgInput) pkgInput.value = '';
                    if (customWrapper) customWrapper.style.display = 'block';
                    var customVal = customInput ? (parseFloat(customInput.value) || 1000) : 1000;
                    credits = customVal;
                    price = (customVal * rate).toFixed(2);
                    document.getElementById('summaryPkgName').textContent = 'CUSTOM CREDITS';
                } else {
                    if (pkgInput) pkgInput.value = pkgId;
                    if (customWrapper) customWrapper.style.display = 'none';
                    document.getElementById('summaryPkgName').textContent = pkgId.toUpperCase() + ' PACK';
                }

                document.getElementById('summaryCredits').textContent = Number(credits).toLocaleString() + ' AI CREDITS';
                document.getElementById('summaryPrice').textContent = '$' + Number(price).toFixed(2) + ' USD';
                
                var btnAmount = document.getElementById('btnPayAmount');
                if (btnAmount) btnAmount.textContent = '$' + Number(price).toFixed(2);

                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            };

            window.closePayModal = function() {
                var modal = document.getElementById('aiPayModal');
                if (modal) modal.style.display = 'none';
                document.body.style.overflow = '';
            };

            window.updateCustomCredits = function(val) {
                var credits = parseFloat(val) || 0;
                var rate = window.perCreditRate || 0.045;
                var price = (credits * rate).toFixed(2);
                document.getElementById('summaryCredits').textContent = Number(credits).toLocaleString() + ' AI CREDITS';
                document.getElementById('summaryPrice').textContent = '$' + Number(price).toFixed(2) + ' USD';
                
                var btnAmount = document.getElementById('btnPayAmount');
                if (btnAmount) btnAmount.textContent = '$' + Number(price).toFixed(2);
            };

            window.formatCardNumber = function(input) {
                var val = input.value.replace(/\D/g, '').substring(0, 16);
                var formatted = val.match(/.{1,4}/g);
                input.value = formatted ? formatted.join(' ') : val;
            };

            window.formatCardExpiry = function(input) {
                var val = input.value.replace(/\D/g, '').substring(0, 4);
                if (val.length >= 3) {
                    input.value = val.substring(0, 2) + '/' + val.substring(2);
                } else {
                    input.value = val;
                }
            };

            window.fillTestCard = function(type) {
                var name = document.getElementById('cardholder_name');
                var card = document.getElementById('card_number');
                var exp = document.getElementById('card_expiry');
                var cvc = document.getElementById('card_cvc');
                
                if (type === 'success') {
                    if (name) name.value = 'FreightX Developer';
                    if (card) card.value = '4242 4242 4242 4242';
                    if (exp) exp.value = '12/28';
                    if (cvc) cvc.value = '123';
                } else if (type === 'decline') {
                    if (name) name.value = 'Declined Test User';
                    if (card) card.value = '4000 0000 0000 0002';
                    if (exp) exp.value = '12/28';
                    if (cvc) cvc.value = '123';
                } else if (type === 'expired') {
                    if (name) name.value = 'Expired Card User';
                    if (card) card.value = '4242 4242 4242 4242';
                    if (exp) exp.value = '01/20';
                    if (cvc) cvc.value = '123';
                }
            };

            window.openBankModal = function() {
                var modal = document.getElementById('aiBankModal');
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            };

            window.closeBankModal = function() {
                var modal = document.getElementById('aiBankModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            };

            // Esc key listener to close active modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    window.closePayModal();
                    window.closeBankModal();
                }
            });
        })();
    </script>
</x-layout>
