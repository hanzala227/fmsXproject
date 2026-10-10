<?php
$content = file_get_contents('resources/views/accounting/invoice-list.blade.php');

$search = <<<EOD
                    <span class="summary-stats">
                        Invoice: <span class="val" id="stat-inv-total">{{ number_format(\$totalInvoiceAmount ?? 0, 2) }}</span>
                        Paid: <span class="val paid" id="stat-paid-total">{{ number_format(\$totalPaidAmount ?? 0, 2) }}</span>
                        Balance: <span class="val" id="stat-bal-total">{{ number_format(\$totalBalanceAmount ?? 0, 2) }}</span>
                    </span>
EOD;
// Wait, the original had $totalInvoiceAmount, without ?? 0.
$search = <<<EOD
                    <span class="summary-stats">
                        Invoice: <span class="val" id="stat-inv-total">{{ number_format(\$totalInvoiceAmount, 2) }}</span>
                        Paid: <span class="val paid" id="stat-paid-total">{{ number_format(\$totalPaidAmount, 2) }}</span>
                        Balance: <span class="val" id="stat-bal-total">{{ number_format(\$totalBalanceAmount, 2) }}</span>
                    </span>
EOD;

$replace = <<<EOD
                    <div class="summary-stats" style="display:flex;align-items:center;gap:12px;">
                        <div class="grand-totals" style="background:#f1f5f9;padding:2px 8px;border-radius:4px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:8px;font-size:11px;">
                            <strong>Grand Total:</strong> 
                            <form action="{{ request()->url() }}" method="GET" style="display:inline-block;margin:0;">
                                @foreach(request()->except(['base_currency', 'page']) as \$key => \$value)
                                    <input type="hidden" name="{{ \$key }}" value="{{ \$value }}">
                                @endforeach
                                <select name="base_currency" onchange="this.form.submit()" style="border:none;background:transparent;font-weight:bold;color:#3b82f6;cursor:pointer;outline:none;padding:0;">
                                    @foreach(\$availableCurrencies as \$cur)
                                        <option value="{{ \$cur->code }}" {{ \$baseCurrencyCode === \$cur->code ? 'selected' : '' }}>{{ \$cur->code }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <span>Inv: <span class="val" id="stat-inv-total" style="color:#0f172a;font-weight:600;">{{ number_format(\$grandTotalInvoice, 2) }}</span></span>
                            <span>Paid: <span class="val paid" id="stat-paid-total" style="color:#10b981;font-weight:600;">{{ number_format(\$grandTotalPaid, 2) }}</span></span>
                            <span>Bal: <span class="val" id="stat-bal-total" style="color:#ef4444;font-weight:600;">{{ number_format(\$grandTotalBalance, 2) }}</span></span>
                        </div>
                        <div class="currency-groups" style="display:flex;gap:12px;font-size:10px;color:#64748b;max-width:400px;overflow-x:auto;white-space:nowrap;padding-bottom:2px;">
                            @foreach(\$totalsByCurrency as \$code => \$totals)
                                <span title="Inv: {{ number_format(\$totals['invoice'], 2) }} | Paid: {{ number_format(\$totals['paid'], 2) }} | Bal: {{ number_format(\$totals['balance'], 2) }}">
                                    <strong>{{ \$code }}:</strong> {{ number_format(\$totals['invoice'], 2) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
EOD;

$newContent = str_replace($search, $replace, $content);
file_put_contents('resources/views/accounting/invoice-list.blade.php', $newContent);
echo "Replaced in invoice-list.blade.php\n";
