# FreightX End-to-End Accounting & Freight Standards — Browser Testing Agent Runbook

**Document Type:** Live Browser Testing Agent Execution Script (Markdown Instruction Protocol — NOT an automated JS script)  
**Purpose:** Strictly execute every end-to-end accounting, calculation, freight-standard, security, multi-currency, period-lock, and cross-report reconciliation flow directly through the live web UI, using large multi-field inputs and adversarial negative checks, and continuously record results into a live QA report Markdown file.  
**Execution Status:** **AWAITING USER REVIEW & GO-AHEAD** (Do NOT start executing until user approves.)

---

## 1. Environment, Credentials & Verified Test Data

### Target Application & Login
- **Base URL:** `http://localhost:8000`
- **Login URL:** `http://localhost:8000/login`
- **Primary Admin Email:** `sardar@gmail.com`
- **Primary Admin Password:** `password`
- **Account Tenant Context (Verified):** `company_id = 1`, Role = `Admin`, Branch = `KHI — Karachi Office`
- **Live Report Output Path:** `/home/muhammad-hanzala/Downloads/backups/fms2.0/tests/e2e/BROWSER_ACCOUNTING_QA_REPORT.md`

### Verified Company 1 Master Data (Use Across Huge-Data Forms)
- **Active Banks (Settings -> Bank List):**
  1. `JPMorgan Chase USD Main` — Currency: `USD`, Default Revenue & Default Cost Bank (Opening: `100,000.00 USD`)
  2. `Emirates NBD USD A/C` — Currency: `USD` (Opening: `50,000.00 USD`)
  3. `Deutsche Bank EUR Account` — Currency: `EUR` (Opening: `45,000.00 EUR`)
  4. `Habib Bank Ltd PKR Account` — Currency: `PKR` (Opening: `2,500,000.00 PKR`)
- **Active Offices:** `KHI` (`Karachi Office`), `LHE` (`Lahore Branch`), `ISB` (`Islamabad Office`), `DXB` (`Dubai Hub`), `TYO` (`Tokyo Office`)
- **Currencies & Rates:** `USD` (`1.000000`), `EUR` (`0.925926`), `GBP` (`0.790000`), `PKR` (`278.500000`), `AED` (`3.670000`), `CAD` (`1.360000`)
- **Verified Trade Partners (`company_id = 1`):**
  - **Customers (`CLIENT`):** `3M COMPANY` (`3MC2395`), `Abdulrahman` (`ABD4814`)
  - **Agents (`AGENT`):** `AIR FREIGHT BROKERS JFK` (`AIR8904`), `ALLIED MOTOR DISTRIBUTORS LTD` (`ALL2542`)
  - **Carriers (`CARRIER`):** `ZIM INTEGRATED SHIPPING SERVICES LTD` (`ZIMU`), `XIAMEN AIRLINE` (`XMN`)
- **Verified GL Accounts (`gl_accounts`):**
  - **Assets:** `10201 CASH ON HAND`, `10301 PREPAID EXPENSES`, `10302 PREPAID INSURANCE`
  - **Liabilities:** `20201 ACCRUED EXPENSES`
  - **Equity:** `30101 PAID-IN CAPITAL`
  - **Revenue:** `40101 OCEAN FREIGHT REVENUE`, `40102 AIR FREIGHT REVENUE`, `40106 HANDLING FEE REVENUE`
  - **Expenses:** `50101 OCEAN FREIGHT COST`, `50102 AIR FREIGHT COST`, `50108 OFFICE RENT`, `50114 BANK CHARGES`
- **Cross-Tenant IDOR Targets (Belongs to `company_id = 20` — MUST be blocked):**
  - Invoice ID `256` (`/accounting/invoice/256`, `/accounting/invoice/256/edit`, `/accounting/invoice/256/print`)
  - Journal Entry ID `8` (`/accounting/journal/8`, `/accounting/journal/entry?id=8`)

---

## 2. Mandatory Browser Agent Execution & Reporting Protocol

1. **Incremental Report Updates (Strict Rule):**
   - BEFORE starting Scenario `S0`, initialize `/home/muhammad-hanzala/Downloads/backups/fms2.0/tests/e2e/BROWSER_ACCOUNTING_QA_REPORT.md` with header metadata, environment URL, test user, and empty Scenario Results table.
   - **Immediately after completing EACH scenario (`S0` through `S15`)**, append a complete section to `BROWSER_ACCOUNTING_QA_REPORT.md` containing:
     - Scenario ID, Title, Target URL(s), and Verdict (`PASS` / `FAIL` / `WARNING`)
     - Exact huge test payload entered into every field (Document No., Partner, Dates, Office, Currency, FX Rate, Line Items, Tax, Shipping, TDS, Discount, Remarks, Bank)
     - Expected UI & Accounting behavior vs Actual observed UI behavior (Toast alerts, HTTP status codes in Network tab, validation error messages, button states)
     - Before/After numeric figures across ledger/report screens and exact Delta verification
     - Any JS `console.error`, uncaught exceptions, HTTP `500`, layout breakages, or missing fields (take & attach screenshot path if ANY issue occurs).
2. **Full-Field Stress Entry (No Stubs):**
   - Whenever a form has optional or extra tabs/fields (Billing Address, MBL/MAWB No., HBL/HAWB, Containers, Dimensions, Discount %, Tax %, Shipping, Internal Remarks, Memos, Office, Issued By, Reference No., Check No., Deposit/Clear Date), fill **every field** with realistic freight data + long multi-word descriptions/remarks.
   - Use a unique run prefix (e.g., `E2E-R1-`) for file numbers/references created during this run so you can trace them cleanly across lists, ledgers, and statements.
3. **Baseline-Delta Accounting Rule (`company_id = 1`):**
   - Company 1 contains historical seed/QA records from prior testing. Therefore, in `S0`, capture the exact **T0 Baseline Snapshot** of all 9 accounting reports FIRST.
   - After EVERY transaction scenario, verify BOTH **exact expected Delta (`After - T0`)** AND confirm **Balance Sheet Variance Delta stays `$0.00`** (no new imbalance introduced!) AND confirm cross-report ties (`BS AR == Aging AR == TB 1200 == GL 1200`, `BS Cash == Bank Book == TB 1000 == GL 1000`, `IS Net Income Delta == BS Net Income Delta`).
4. **Zero-Tolerance UI Health Gate:**
   - Monitor browser DevTools Console & Network on every navigation and AJAX form submission:
     - **0** Console Errors / Uncaught Alpine.js ReferenceErrors (`undefined is not defined`, etc.)
     - **0** HTTP `500` responses (Validation guards MUST cleanly return `422` or `403` with clear user toast/inline messages, NEVER crash!).
     - Confirm buttons work (`SAVE`, `SAVE & NEW`, `UPDATE`, `Calculate`, `Print`, `Export Excel/CSV`).

---

## 3. End-to-End Browser Test Scenarios

### Scenario 0 (`S0`) — Authentication & Complete T0 Baseline Financial Snapshot
**Goal:** Log in and record exact pre-test live figures across all accounting screens for `company_id = 1`.
1. Navigate to `http://localhost:8000/login`.
2. Fill Email = `sardar@gmail.com`, Password = `password`, submit login. Verify redirect to `/` (dashboard loaded, `0` console errors).
3. Visit each report below, click **Calculate** (period `2026-01-01` to `2026-12-31`, As-Of Date `2026-12-31`, Office = All), and record exact numbers in `BROWSER_ACCOUNTING_QA_REPORT.md` as **Baseline T0**:
   - **Balance Sheet** (`/accounting/report/balance-sheet`): Record Total Cash & individual banks (`JPMorgan Chase USD Main`, `Emirates NBD USD A/C`, `Deutsche Bank EUR Account`, `Habib Bank Ltd PKR Account`), Unallocated, AR, TDS Receivable, AP, TDS Payable, Customer Advances, Equity Capital, Cumulative Net Income, Total Assets, Total Liabilities + Equity, Variance.
   - **Income Statement** (`/accounting/report/income-statement`): Record Total Revenue, Total Expenses, Net Income.
   - **Trial Balance** (`/accounting/report/trial-balance`): Record Total Debit, Total Credit, Is Balanced status, Opening/Closing balances of `1000`, `1200`, `1300`, `2000`, `2100`, `3000`, `4000`, `4900`, `5000`, `5900`, and any `JE` rows (verify ZERO Company 20 JEs!).
   - **General Ledger** (`/accounting/report/general-ledger`): Record Open Dr/Cr, Period Dr/Cr, Close Dr/Cr, Closing balances for `1000`, `1200`, `1300`, `2000`, `2100`.
   - **Aging Report** (`/accounting/report/aging-report`): Calculate for `AR` and `AP` separately -> record AR Grand Total and AP Grand Total.
   - **Bank Book Balance** (`/accounting/bank/book-balance`, Period `2000-01-01` to `2026-12-31`, Active, Main Currency USD): Record Grand Total Closing Balance and per-bank closing balances.
   - **Journal Report** (`/accounting/report/journal-report`): Calculate `2026-01-01` to `2026-12-31` -> confirm only `company_id = 1` entries appear (NO `JE-20261109-0001` from Company 20!).
4. **Update Report:** Write T0 Baseline Matrix table & `S0` Pass/Fail status.

---

### Scenario 1 (`S1`) — Freight Standards UI + Backend Validation Across All 4 Shipment Modes
**Goal:** Verify IATA Res 600a (Mod 7), IATA TACT 1:6 (`166.67` / `35.3147` / `2.20462`), and ISO 6346 (`NORMAL` & `MANUAL` inputs) work strictly in all 4 shipment forms with huge datasets.

#### 1A. Ocean Export (`/ocean-export/create`)
1. Open `/ocean-export/create`.
2. **Negative ISO 6346 Test (Top & HBL Container + `MANUAL`):**
   - Fill File No `OE-E2E-R1-01`, Office `KHI`, Carrier `ZIM INTEGRATED SHIPPING SERVICES LTD`, Ship Mode `FCL`, Freight Term `PREPAID`.
   - On Container #1: select/enter invalid ISO 6346 number `BADU1234569` (check digit fails Mod 11).
   - On HBL #1 -> Container: select `MANUAL` and enter invalid `CSQU3054389`.
   - Click Save -> **Assert:** Frontend/Backend rejects save (`422` / inline validation alert on invalid ISO 6346 container number); record exact error text!
3. **Positive Full-Field Create:**
   - Replace Container #1 with valid `CSQU3054383` and HBL Container with valid `MSKU9070323`.
   - Populate full shipment metadata: MBL No `MBL-OE-E2E-01`, Booking No `BKG-OE-E2E-01`, Vessel/Voyage `MV EVERGREEN / V.204E`, POL/POD, ETD `2026-11-05`, ETA `2026-11-25`, Weight `18,450.50 KG`, Measurement `28.500 CBM`, long Cargo Description, 2 HBLs (`HBL-E2E-01A`, `HBL-E2E-01B` with Shipper `3M COMPANY`, Consignee `Abdulrahman`, Overseas Agent `AIR FREIGHT BROKERS JFK`, Incoterms `CIF`).
   - Save -> **Assert:** Saved cleanly with Edit URL & zero console errors! Keep Edit page open for `S2`.

#### 1B. Ocean Import (`/ocean-import/create`)
1. Open `/ocean-import/create`.
2. **Negative ISO 6346 Test (`MANUAL` on Top-Level Container):**
   - Fill File No `OI-E2E-R1-01`, MBL `MBL-OI-E2E-01`, Office `KHI`, Ship Mode `FCL`, Freight Term `COLLECT`.
   - Set top-level Container No = `MANUAL` -> Manual Input = `BADU1234569`. Submit Save -> **Assert:** Rejected (`422` ISO 6346 validation error)!
3. **Positive Full-Field Create:**
   - Update Manual Input to valid `CSQU3054383` (or select valid ISO 6346 container), fill Customer `3M COMPANY`, Overseas Agent `AIR FREIGHT BROKERS JFK`, ETD `2026-11-01`, ETA `2026-11-18`, AMS/ISF fields, full HBL -> Save cleanly!

#### 1C. Air Export (`/air-export/create`)
1. Open `/air-export/create`.
2. **Negative MAWB Mod 7 Test:**
   - Fill File No `AE-E2E-R1-01`, Office `KHI`, MAWB No = `176-74910285` (wrong check digit; `7491028 % 7 = 6`, not `5`), Carrier `XIAMEN AIRLINE`. Try Save -> **Assert:** Blocked with Mod 7 error!
3. **IATA TACT 1:6 Calculation & Server Minimum Enforcement:**
   - Set MAWB No = `176-74910286`.
   - Enter Dimensions or Volume = `3.50 CBM`, Gross Weight = `125.00 kg`.
   - **Verify UI Math:** Volume Weight auto-calculates to `3.50 * 166.67 = 583.35 kg`, CFT = `123.60` (`3.50 * 35.3147`), Gross LBS = `275.58` (`125 * 2.20462`), Chargeable Weight auto-raises to `583.35 kg`!
   - Tamper Test: Manually type Chargeable Weight = `10.00` into input and submit Save -> **Assert:** Either UI prevents or server `prepareForValidation()` enforces `chargeable_weight >= 583.35` cleanly on saved record! Verify saved `chargeable_weight` on reload = `583.35` (NOT `10.00`).

#### 1D. Air Import (`/air-import/create`)
1. Open `/air-import/create`.
2. **Negative MAWB Mod 7 Test:** Enter `160-74910285`, File No `AI-E2E-R1-01`, Office `KHI` -> Save -> **Assert:** Blocked with Mod 7 check digit failure!
3. **Positive Full-Field + TACT 1:6 Create:**
   - Update MAWB to `160-74910286`, Gross Weight = `200.00 kg`, Volume = `2.00 CBM` (`Volumetric = 333.34 kg`), add HAWB (`HAWB-E2E-01`) with `PREPAID`, Customer `3M COMPANY`, Agent `AIR FREIGHT BROKERS JFK`. Verify UI auto-populates LBS/CFT/Volumetric weight -> Save cleanly!
4. **Update Report:** Log all negative rejections, conversion factor calculations, and created shipment IDs for `S1`.

---

### Scenario 2 (`S2`) — Shipment Charges -> Multi-Group Invoice Generation -> Double-Bill Lock -> Cancel Release
**Goal:** Verify end-to-end charge-to-invoice automation across all 4 modes, Agent `DN`/`CN` vs Customer `INV`, FX lock, duplicate lock, and Cancel charge unlock.

1. **Ocean Export (`OE-E2E-R1-01`) Huge Charge Batch:**
   - Open Accounting / Charges tab on `OE-E2E-R1-01`. Add **6 detailed charges**:
     1. `AR` | `3M COMPANY` | `USD` | `OFR` Ocean Freight | Qty `2`, Rate `1,850.00` = **`$3,700.00`**
     2. `AR` | `3M COMPANY` | `USD` | `DOC` Documentation Fee | Qty `1`, Rate `150.00` = **`$150.00`**
     3. `AR` | `3M COMPANY` | `EUR` | `BAF` Bunker Surcharge | Qty `1`, Rate `925.93` = **`€925.93`** (~`$1,000.00 USD`)
     4. `AR` | `AIR FREIGHT BROKERS JFK` (Agent) | `USD` | `AGT` Agent Profit Share | Qty `1`, Rate `600.00` = **`$600.00`**
     5. `AP` | `ZIM INTEGRATED SHIPPING SERVICES LTD` (Carrier) | `USD` | `OFX` Sea Slot Cost | Qty `2`, Rate `1,100.00` = **`$2,200.00`**
     6. `AP` | `AIR FREIGHT BROKERS JFK` (Agent) | `USD` | `THC` Overseas Agent Handling | Qty `1`, Rate `350.00` = **`$350.00`**
   - Save Shipment, select all 6 charges, click **Generate Invoice**:
     - **Assert Grouping (`INV-01`/`INV-03`):** System creates **5 distinct documents** grouped by `(Party, AR/AP, Currency)`:
       - Doc 1 (`AR`, `3M COMPANY`, `USD`): `INV` for **`$3,850.00`** (`3,700 + 150`)
       - Doc 2 (`AR`, `3M COMPANY`, `EUR`): `INV` for **`€925.93`** (FX locked `0.925926`, `office_id = KHI`)
       - Doc 3 (`AR`, Agent `AIR FREIGHT BROKERS JFK`, `USD`): **`DN` (Debit Note)** for **`$600.00`**
       - Doc 4 (`AP`, Carrier `ZIM`, `USD`): **`INV` (Payable Invoice)** for **`$2,200.00`**
       - Doc 5 (`AP`, Agent `AIR FREIGHT BROKERS JFK`, `USD`): **`CN` (Credit Note)** for **`$350.00`**
     - **Assert Workflow Start (`INV-05`):** All 5 generated documents start in **`DRAFT`** status and **`Pending Verification`**!
     - **Assert Charge Lock:** Re-open charges tab -> all 6 charges show locked Invoice No & `is_invoiced` state. Click **Generate Invoice** again without selecting new unbilled charges -> verify no duplicate invoice is created (`"Charges are already invoiced"` / blocked)!
2. **Air Export (`AE-E2E-R1-01`), Air Import (`AI-E2E-R1-01`) & Ocean Import (`OI-E2E-R1-01`) Charge Generation:**
   - Add at least 1 `AR` + 1 `AP` charge on EACH of the other 3 shipments (`$1,400 AR` + `$500 AP` on Air Export; `$800 AR` + `$450 AP` on Air Import; `$1,600 AR` + `$650 AP` on Ocean Import) and click **Generate Invoice** on each -> **Assert** all generate unique sequential `DRAFT / Pending Verification` docs (`INV`, `DN`, `CN`) with `office_id = KHI`!
3. **Cancel & Charge Unlock (`INV-02`):**
   - Open the Ocean Import generated `$1,600 AR` draft/posted invoice (`/accounting/invoice/{id}`), or first verify one and test cancel -> click **Cancel**:
     - Enter 3-character reason `"bad"` -> **Assert:** Blocked (minimum 5 characters required!).
     - Enter `"Cancelling Ocean Import invoice to test charge unlock"` -> Confirm Cancel!
     - **Assert:** Invoice status = `VOID`, `verification_status = Cancelled`. Return to `OI-E2E-R1-01` Charges tab and reload page -> **Assert:** `$1,600` Ocean Import charge now shows unlocked (`invoice_no` cleared) and is eligible for re-invoicing! Re-generate it (`$1,600`) cleanly so we confirm round-trip unlock works in browser!
4. **Update Report:** Record all generated document numbers, types (`INV`/`DN`/`CN`), locked FX rates, and cancel unlock proof.

---

### Scenario 3 (`S3`) — Manual Invoice UI, Document Types, Credit Note Cap, Workflow Bypass Block & Print Bank Check
**Goal:** Test `/accounting/invoice/create` UI exposure (`UI-01`), Credit Note over-cap guard (`INV-04`), verification bypass attempts, and Invoice Print bank isolation (`SEC-01`).

1. Open `/accounting/invoice/create`.
2. **Verify UI Exposure (`UI-01`):**
   - Inspect **Status** dropdown: **Assert** ONLY **`DRAFT` (`Draft (Pending Verification)`)** is selectable on create (NO `POSTED` / `PAID` bypass option!).
   - Inspect **Paid Amount** input: **Assert** `readonly` (`0`) on create.
   - Select **Bill To** = `3M COMPANY`, **Type** = `AR`: **Assert** `Document Type` (`document_type`) stays `INV`.
   - Switch **Bill To** = `AIR FREIGHT BROKERS JFK` (Agent): **Assert** `Document Type` automatically changes to `DN` (`AR`), and when **Type** toggles to `AP`, auto-changes to `CN`!
3. **Create Master Customer TDS Test Invoice (`$5,300.00` AR — Exact Report Walkthrough Case):**
   - Set Type = `AR`, Bill To = `3M COMPANY`, Doc Type = `INV`, Currency = `USD` (FX Rate = `1.000000`), Invoice Date = `2026-11-10`, Due Date = `2026-10-01` *(intentionally past due for overdue aging check later!)*, Office = `KHI`, MBL No `E2E-TDS-MBL-5300`, Billing Address `3M Logistics Hub, Bldg 40, St Paul MN`, Internal Remark `QA TDS $5300 Invoice Full Field Test`.
   - Switch to **Line Items** tab -> add **4 huge line items** summing to `$5,300.00` (e.g., `$3,000 + $1,200 + $600 + $500`, Discount `0%`, Tax `0%`, Shipping `0.00`).
   - Click **SAVE** -> **Assert:** Created with `INV-xxxxxx-A`, `status = DRAFT`, `verification_status = Pending Verification`!
4. **Create Additional Huge Manual Multi-Line Invoices (for Multi-Invoice Payment, Carrier AP TDS & Reject Testing):**
   - Create Invoice `B` (`AR`, `3M COMPANY`, `USD`, **Discount `5%`**, **Shipping `$100`**, **Tax `10%`**, 3 lines, total computed carefully, Due Date `2026-12-25` *future due!*).
   - Create Invoice `C` (`AR`, `Abdulrahman`, `USD`, **Invoice Date = `2026-11-05`**, Due Date = `2026-11-20`, `$1,200.00` — intentionally dated before `2026-11-10` settlement so `S13` can verify historical `as_of_date = 2026-11-09`!) AND Invoice `D` (`AR`, `Abdulrahman`, `USD`, `2026-11-10`, `$800.00`) for multi-invoice batch payment.
   - Create Invoice `E` (`AP`, `ZIM INTEGRATED SHIPPING SERVICES LTD`, `USD`, **`$5,300.00`**) for Vendor TDS Payable testing.
   - Create Invoice `F` (`AR`, `3M COMPANY`, `USD`, **`$9,999.00`**) solely to test **Rejection**.
5. **Credit Note Amount Cap Check (`INV-04`):**
   - Open `/accounting/invoice/create`, set Type = `AR`, Bill To = `3M COMPANY`, switch Doc Type = **`CN` (`Credit Note`)**.
   - **Assert:** Conditional **Original Invoice (`related_invoice_id`)** dropdown appears! Select Invoice `A` (`$5,300.00`).
   - Add line item for **`$12,500.00`** (> `$5,300.00`) -> Click **SAVE** -> **Assert:** Blocked with HTTP `422` error toast (`exceeds original invoice amount`)!
   - Reduce line item to **`$250.00`** -> Click **SAVE** -> **Assert:** Saved cleanly as `CN-xxxxxx-A` in `DRAFT / Pending Verification`!
   - Edit that Draft CN (`/accounting/invoice/{id}/edit`), try changing line total up to **`$9,000.00`** and click **UPDATE** -> **Assert:** Cap guard ALSO blocks over-amount update (`422`), restore `$250.00`!
6. **Workflow Bypass Check & Print Bank Isolation (`SEC-01`):**
   - Before verifying, open Invoice List (`/accounting/invoice`), check Draft Invoice `A`, select **`POSTED`** in Bulk Status Update dropdown and apply -> **Assert:** Rejected (`422`: must verify via Verification Queue)!
   - Try **`VOID`** via Bulk Status Update -> **Assert:** Blocked (`403`: must use Administrator Cancel with reason)!
   - Check Reports (`Balance Sheet` / `Income Statement`) NOW while all new docs are unverified Drafts -> **Assert:** Delta vs T0 Baseline is **`$0.00`** (unverified docs MUST NOT pollute reports!).
   - Open Print view (`/accounting/invoice/{A_id}/print` and Euro Ocean Export invoice `/accounting/invoice/{EUR_id}/print`): **Assert:** Company 1 bank (`JPMorgan Chase USD Main` for USD, `Deutsche Bank EUR Account` for EUR) renders — **ZERO Company 20 bank names!**
7. **Update Report:** Log Credit Note cap block, Draft report exclusion, bulk bypass rejection, and Bank Print output.

---

### Scenario 4 (`S4`) — Verification Queue, Reject w/ Reason, Verify Promotion & Post-Verify Locks
**Goal:** Verify `/accounting/invoice/verify-queue` promotion and delete/edit protection.
1. Open `/accounting/invoice/verify-queue`.
2. Verify all created Draft documents appear (`Pending Verification`) with document badges (`INV`, `DN`, `CN`).
3. **Reject Test:** On Invoice `F` (`$9,999.00`), click **Reject**:
   - First try submitting Reject modal with blank reason -> **Assert:** Blocked (`reject_reason` required)!
   - Enter reason `"Rejecting QA overstated $9,999 test billing"` -> Submit -> **Assert:** Row status updates to `Rejected`, and in DB/List its status is `DRAFT` (`Pending -> Rejected`).
   - Check Income Statement, Balance Sheet, Trial Balance, General Ledger, Aging, Freight Statement, Revenue/Cost -> **Assert:** Rejected `$9,999.00` invoice is **100% excluded** from ALL of them!
4. **Verify Approval Test:** Click **Verify** on:
   - Ocean Export invoices (`$3,850` AR, `€925.93` AR EUR, `$600` Agent DN, `$2,200` Carrier AP, `$350` Agent AP CN)
   - Air Export, Air Import & Ocean Import generated invoices (including Air Import Agent AP `CN`)
   - Manual invoices `A` (`$5,300` AR), `B` (taxed/discounted AR), `C` (`$1,200` AR), `D` (`$800` AR), `E` (`$5,300` AP), and the `$250` `CN`!
   - **Assert:** Every verified Draft automatically promotes `DRAFT -> POSTED` and immediately increments Balance Sheet / Income Statement / Trial Balance / Aging by the exact net amount!
5. **Permanent Record Deletion Protection:**
   - Open `/accounting/invoice`, select verified Invoice `A` (`POSTED`) and try Delete / Bulk Delete -> **Assert:** Blocked (`403`: permanent deletion prohibited; only Cancel allowed)!
6. **Update Report:** Record Verification Queue results, Rejected exclusion proof, and financial delta post-verify.

---

### Scenario 5 (`S5`) — Receive Payment (`/accounting/payment/receive`) — TDS `5,300 -> 5,000 + 300`, Overpayment, Duplicate Guard & Multi-Invoice Batch Allocation
**Goal:** Validate every Receive Payment requirement in the UI including `5,300 -> 5,000 Cash + 300 TDS`, Discounts, Multi-Invoice `allocations[]`, Dynamic FX, and `update()` Overpayment guard.

1. Open `/accounting/payment/receive`.
2. **Verify Fixed UI Controls:**
   - Check **Exchange Rates** boxes in Column 4: **Assert** `EUR => USD` displays `0.925926`, `PKR => USD` displays `278.500000` (NOT hardcoded `1`!).
   - Select Bank `JPMorgan Chase USD Main`: **Assert** Bank Currency automatically syncs to `USD` (`bankCurrencyCode`).
3. **Overpayment Guard Test (`PAY-03`):**
   - Select `Received From` = `3M COMPANY`, select Invoice `A` (`$5,300.00`).
   - Set Cash `Amount = 5,100.00`, table row `TDS Tax = 300.00` (or `5,050 + 300 TDS = 5,350 > 5,300`) — also test bypassing client clamp if needed by typing Cash `Amount = 5,200` directly in Column 1 top `Amount` field -> click **SAVE**.
   - **Assert:** Server rejects (`422` validation toast stating total settlement exceeds remaining balance)! Invoice `A` stays `POSTED`, balance `$5,300.00`.
4. **Exact Report Walkthrough — TDS Receivable (`$5,300 = $5,000 Cash + $300 TDS Tax`):**
   - Set Payment No (auto/unique `RCV-E2E-TDS-5300`), Post Date = `2026-11-10`, Method = `BANK_TRANSFER`, Reference No = `E2E-TDS-REF-01`, Currency = `USD`, Bank = `JPMorgan Chase USD Main`, Deposit = Checked (`2026-11-10`), Remark = `Customer withheld $300 TDS on $5300 invoice`.
   - On Invoice `A` table row: enter `TDS Tax = 300.00`, verify `Payment (USD)` auto-balances to `5,000.00`, `Total Settlement = 5,300.00`!
   - Click **SAVE** -> **Assert:** Saved cleanly, redirects/updates edit page (`isSaved`), Remark persists properly after reload!
   - Open Invoice List & BS / TB / GL: **Assert** Invoice `A` status = **`PAID`**, Balance = **`0.00`**, `JPMorgan Chase USD Main` increased by **`+$5,000.00`**, **`Withholding Tax / TDS Receivable` (`1300`)** increased by **`+$300.00`**!
5. **Duplicate Reference + Amount Guard (`PAY-03`) & Update Overpayment Guard (`PAY-02`):**
   - Open new Receive Payment for `3M COMPANY` -> Invoice `B`:
     - Pay partial `$400.00` cash, `Reference No = DUP-CHK-400` -> Click **SAVE** -> Succeeds!
     - Open another new Receive Payment for SAME Invoice `B`, enter SAME `Amount = 400.00`, SAME `Reference No = DUP-CHK-400` -> Click **SAVE** -> **Assert:** Rejected (`422` Duplicate payment rejected)!
     - Open that first `$400.00` payment in Edit mode (`/accounting/payment/{id}/edit`): raise Amount from `400.00` to **`9,999.00`** -> Click **UPDATE** -> **Assert:** Blocked (`422` Overpayment guard on Update!), Invoice `B` balance stays untouched!
     - Try opening `/accounting/invoice/{A_id}/edit` (which is now `PAID`): **Assert:** Strictly blocked (`403` locked due to active settlements — even for Admin `sardar@gmail.com`!)!
6. **Multi-Invoice Batch Settlement + Sales Discount + Dynamic Currency (`UI-01`):**
   - Open `/accounting/payment/receive`, select `Received From = Abdulrahman` (Invoices `C = $1,200` and `D = $800`).
   - Check BOTH checkboxes (`C` and `D`) in the Invoice List table (`2 items selected`!).
   - On Row `C`: enter `Discount = 100.00` -> verify Payment auto-adjusts to `1,100.00`.
   - On Row `D`: enter `TDS Tax = 50.00` -> verify Payment auto-adjusts to `750.00`.
   - Top summary shows `Payment = 1,850.00`, `TDS = 50.00`, `Discount = 100.00`, `Total Settlement = 2,000.00`.
   - Reference No = `E2E-BATCH-02`, Bank = `Emirates NBD USD A/C` -> Click **SAVE**!
   - **Assert:** Message confirms payment recorded across 2 invoices, **BOTH Invoices `C` & `D` automatically become `PAID` with Balance `0.00`**, `Sales Discounts Allowed (4900)` reflects `+$100.00` (decreasing Net Income on both IS and BS equally!), and Cash/TDS balance!
7. **Update Report:** Record every toast/validation check, Invoice statuses, Payment IDs, and cash/TDS/discount deltas.

---

### Scenario 6 (`S6`) — Make Payment (`/accounting/payment/make`) — Reference No., Check No., TDS Payable & Purchase Discount
**Goal:** Verify Vendor AP bill settlement, `Reference No.` input on payment-make, TDS Payable (`2100`), Purchase Discounts (`5900`), and Make Payment guards.
1. Open `/accounting/payment/make`.
2. **Verify Fixed UI Controls:**
   - **Assert** `Reference No.` input (`name="reference_no"`) is visible alongside `Check No.`!
   - **Assert** Dynamic Exchange Rates grid reflects DB rates!
3. **Full-Field Vendor Payment w/ TDS Withholding + Discount (`$5,300 AP -> $4,900 Cash + $300 TDS + $100 Discount`):**
   - Select `Payable To = ZIM INTEGRATED SHIPPING SERVICES LTD` -> select Bill `E` (`$5,300.00`).
   - Enter Payment Method = `Check`, Check No = `CHK-88001`, Reference No = `E2E-AP-REF-5300`, check `Show party on check`, check `Clear` (`2026-11-10`), Bank = `JPMorgan Chase USD Main`, Remark = `Withholding $300 vendor TDS & $100 early pay discount`.
   - On Bill `E` row: enter `TDS Tax = 300.00`, `Discount = 100.00` -> verify Payment auto-adjusts to `4,900.00`!
   - Click **SAVE** -> **Assert:** Saved cleanly (`200`), Bill `E` status becomes **`PAID`**, Bank decreases by `-$4,900.00`, **`Withholding Tax / TDS Payable` (`2100`)** increases by **`+$300.00`**, and **`Purchase Discounts Earned` (`5900`)** increases Net Income by `+$100.00` across IS, BS, TB, GL!
4. **Update Overpayment Test on Make Payment:**
   - On Carrier `ZIM`'s Ocean Export AP bill (`$2,200.00`), pay `$1,000.00` (`Ref = E2E-ZIM-PARTIAL-1`), then edit and try raising to `$5,000.00` -> **Assert:** Rejected (`422`)!
5. **Update Report:** Document `reference_no` presence, TDS Payable `2100`, Discount `5900`, and Bill status updates.

---

### Scenario 7 (`S7`) — Foreign Currency Payments (`PKR` & `EUR`) + Unattached On-Account Advances & Locked FX Preservation
**Goal:** Verify `FX-01` (multi-currency unattached TDS in PKR -> USD) and `EUR` receipt into `Deutsche Bank EUR Account`.
1. Open `/accounting/payment/receive`:
   - Select Customer `3M COMPANY`, Currency = **`PKR`**, Bank = **`Habib Bank Ltd PKR Account`** (verify Bank Currency auto-syncs to `PKR` and Exchange Rate shows `278.500000`).
   - Leave Invoice unselected (`invoice_id = ""` — On-Account Advance!), Reference No = `E2E-PKR-ADV-01`, Payment Method = `BANK_TRANSFER`, Deposit = Checked (`2026-11-10`).
   - Enter Amount = **`27,850.00`** (`PKR`, = `$100.00 USD`), and test/submit `TDS Tax = 2,785.00` (`$10.00 USD`) if unattached input or check via Customer Advance flow -> Click **SAVE**!
   - **Assert:** In Balance Sheet (`Main Currency USD`), `Habib Bank Ltd PKR Account` increases by `$100.00 USD`, and Balance Sheet Equation Variance delta remains **`$0.00`** (Assets + Customer Advances Liabilities match)!
2. Open `/accounting/payment/receive` for the Verified **Ocean Export EUR Invoice (`€925.93`)**:
   - Currency = `EUR`, Bank = `Deutsche Bank EUR Account`, settle full **`€925.93`** `@ 0.925926` (`$1,000.00 USD`).
   - **Assert:** `Deutsche Bank EUR Account` increases by `+€925.93` (`+$1,000.00 USD`), Euro Invoice turns `PAID` (`$0` AR balance)!
3. **Update Report:** Document PKR & EUR FX conversions and Balance Sheet variance delta (`$0.00`).

---

### Scenario 8 (`S8`) — G&A Expenses (`/accounting/ga-expense-list` & `/create`)
**Goal:** Verify `GA-01` full flow: creation -> verification -> partial payment -> edit/downgrade/delete locks.
1. Open `/accounting/ga-expense/create`: create full-field AP Expense (`ZIM`, Date `2026-11-10`, Office `KHI`, Currency `USD`, multiple lines `$1,000 + $500 = $1,500`, tax/discount `0`, long remark).
   - **Assert:** Saves as `DRAFT / Pending Verification` (excluded from IS/BS until verified!).
2. Verify it via Verification Queue (`/accounting/invoice/verify-queue`) -> **Assert:** Promotes to `POSTED` and increments Expenses + AP by `$1,500.00`.
3. Pay `$500.00` against it via Make Payment (`Emirates NBD USD A/C`).
4. Navigate to `/accounting/ga-expense/{id}/edit`: **Assert** blocked **`403`** because active settlement exists!
5. In `/accounting/ga-expense-list`: try Bulk Status `-> DRAFT` (`422` blocked!), `-> VOID` (`403` blocked!), and Delete (`403` blocked!).
6. **Update Report:** Record G&A expense workflow & lock test outcomes.

---

### Scenario 9 (`S9`) — Manual Journal Entries (`/accounting/journal/entry`) & Journal Report
**Goal:** Verify double-entry Dr=Cr guard, company scoping, GL account mapping, and reflection across BS/IS/TB/GL/Journal Report.
1. Open `/accounting/journal/entry`:
   - **Negative Unbalanced Test:** Entry Date `2026-11-10`, Office `KHI`, Status `POSTED`, Description `Unbalanced QA Test`.
     - Line 1: GL Account `50108 OFFICE RENT`, Debit `900.00`, Credit `0.00`.
     - Line 2: GL Account `10201 CASH ON HAND`, Debit `0.00`, Credit `500.00`.
     - Click Save -> **Assert:** Blocked (`Total Debit must equal Total Credit`, HTTP `422`), NOT persisted!
2. **Compound Balanced 4-Line Journal Entry (`$1,250.00`):**
   - Description = `QA End-to-End Compound Accrual & Expense Adjustment`, Remark = `Reconciling Prepaid & Bank Fees`.
   - Line 1: `50108 OFFICE RENT` (EXPENSE) — Debit `750.00`
   - Line 2: `10301 PREPAID EXPENSES` (ASSET) — Debit `400.00`
   - Line 3: `50114 BANK CHARGES` (EXPENSE) — Debit `100.00`
   - Line 4: `10201 CASH ON HAND` (ASSET) — Credit `1,250.00`
   - Click Save -> **Assert:** Saved (`POSTED`, Total Dr `1,250.00 == Total Cr 1,250.00`), appears in Journal Entries History tab & `/accounting/general-journal`!
3. Open `/accounting/report/journal-report`: filter `2026-01-01` to `2026-12-31` -> **Assert:** Shows exact 4 lines (`Dr=1250`, `Cr=1250`) and **ZERO Company 20 journals**!
4. **Update Report:** Record JE number, 4-line Dr/Cr tie, and impact expected on BS (`10301 +400`, `10201 -1250`, Expenses `+850`, Net Income `-850`, Assets net `-850 == Equity net -850` -> **`0.00` variance delta!**).

---

### Scenario 10 (`S10`) — Bank Book Balance & Bank Reconciliation (Uncleared Deposits / Checks & 0.00 Diff)
**Goal:** Verify uncleared deposit/check tracking in `/accounting/bank/reconciliation` and cash tie with `/accounting/bank/book-balance`.
1. Create 1 **Uncleared Deposit** (`$2,200.00`, Customer `3M COMPANY`, Invoice `B` partial `$400` or on-account, Bank `Emirates NBD USD A/C`, **`Deposit` checkbox UNCHECKED**!) and 1 **Uncleared Check** (`$600.00`, Vendor `ZIM`, Open AP bill, Bank `Emirates NBD USD A/C`, Method `Check`, Check No `CHK-UNCLEAR-600`, **`Clear` checkbox UNCHECKED**!).
2. Open `/accounting/bank/book-balance`:
   - Calculate **Summary** (`USD`) and **Detail** (`USD`, `2000-01-01` to `2026-12-31`): verify running balances & totals match, Print & Excel Export return clean HTTP `200`.
3. Open `/accounting/bank/reconciliation`:
   - Select Bank = `Emirates NBD USD A/C`, Period = today (`2026-11-10`) -> click **Calculate**!
   - **Assert:**
     - `Outstanding Detail` -> **Deposit & Credit** table lists our uncleared `$2,200.00` deposit (`Deposit = N`)!
     - `Outstanding Detail` -> **Checks & Debit** table lists our uncleared `$600.00` check (`CHK-UNCLEAR-600`, `Clear = N`)!
     - `Statement Balance` shows ONLY cleared transactions; `Actual Ending = Statement Ending + Outstanding Deposits - Outstanding Checks`; `Book Ending = Opening + Total Deposits - Total Checks`; **`Bank & Book Difference = 0.00`**!
     - Click Print & Export Excel -> verify clean downloads/pages!
4. **Update Report:** Record Statement Ending, Outstanding Deposits/Checks, Book Ending, and `0.00` Difference!

---

### Scenario 11 (`S11`) — Accounting Block Dates (`/accounting/journal/block`) — Per-Office, Inclusive `<=`, All-Offices & Cross-Module Locks
**Goal:** Confirm closed periods enforce hard stops across all accounting creation/edit/verify/cancel screens.
1. Open `/accounting/journal/block`:
   - **Test 1 — Office-Specific Block (`KHI` on or before `2026-10-31`):**
     - Select Office `KHI`, Action `Block`, Date `2026-10-31` -> Click **Apply** -> **Assert** `Last Block Date` table updates to `KHI | 10-31-2026 | BLOCKED`.
     - Verify inclusive blocking on **`2026-10-31`** and backdated **`2026-10-15`** in Office `KHI` across ALL 4 forms:
       1. Create Invoice (`invoice_date = 2026-10-15`, `office_id = KHI`) -> **Assert:** `403` blocked!
       2. Create Payment (`payment_date = 2026-10-31`, `office_id = KHI`) -> **Assert:** `403` blocked!
       3. Create G&A Expense (`invoice_date = 2026-10-15`, `office_id = KHI`) -> **Assert:** `403` blocked!
       4. Create Manual JE (`entry_date = 2026-10-15`, `office_id = KHI`) -> **Assert:** `403` blocked!
     - Verify non-blocked office isolation: create & save a small Draft JE or Invoice in **`LHE` (`Lahore Branch`)** dated `2026-10-20` -> **Assert:** Succeeds cleanly (delete Draft / keep noted)!
   - **Test 2 — All-Offices Global Block (`All` on or before `2026-10-31`):**
     - Return to `/accounting/journal/block`, select Office = **`All`**, Action = `Block`, Date = `2026-10-31` -> Apply!
     - Retry Invoice/Payment/JE in **`LHE`** on `2026-10-20` -> **Assert:** NOW blocked (`403`)!
     - Verify creating Invoice in `LHE` on **`2026-11-01`** (1 day AFTER block date) -> **Assert:** Succeeds!
   - **Cleanup Unblock (Leave period open cleanly or verify Block Maintenance & History):**
     - Visit `/accounting/journal/block/maintenance` and `/accounting/journal/block/history` -> verify block audit logs recorded properly!
     - On `/accounting/journal/block`, apply `UNBLOCK` for `All` (`2026-10-31`) and `KHI` (`2026-10-31`) if needed before historical tests, OR verify `UNBLOCK` lifts block cleanly.
2. **Update Report:** Log all `403` blocked tests, `LHE` vs `KHI` isolation, inclusive boundary `2026-10-31`, and Block History entries.

---

### Scenario 12 (`S12`) — Year-End Closing Screen Health Check (`/accounting/year-end-closing`)
**Goal:** Confirm fixed PHP controller & UI load cleanly without parse/500 errors (`ACC-01`).
1. Open `/accounting/year-end-closing`.
2. **Assert:** HTTP `200`, `0` PHP/500/console errors, status banner (`statusMsg`) & `Change Log` render properly.
3. Click **Check Uncleared** or view Change Log `More Detail` modal -> **Assert:** Works smoothly cleanly! *(Do NOT permanently close current active fiscal year 2026 unless also immediately testing `Cancel Year-End Closing` — if Year-End Closing radio for a completed prior year is harmless/cancellable, test Perform -> Cancel and confirm Change Log captures both `CLOSE` and `CANCEL`!)*
4. **Update Report:** Record Year-End Closing health check results.

---

### Scenario 13 (`S13`) — Complete Final Cross-Report Tie, Point-in-Time Historical `as_of_date`, Statements & Export Stress Test
**Goal:** Verify EVERY financial statement, sub-ledger, historical date, and shipment/agent statement matches exact mathematical deltas against T0 Baseline with **`$0.00` Variance Delta**.

1. **Full Current-Date Reconciliation (`As of 2026-12-31`, Period `2026-01-01 .. 2026-12-31`):**
   - Run **Balance Sheet**, **Income Statement**, **Trial Balance**, **General Ledger**, **Aging Report (`ar` & `ap`)**, **Bank Book Balance**:
   - **Assert ALL Exact Equations Hold (Record Exact Numbers & `After - T0` Deltas in Table):**
     - Balance Sheet `Total Assets` vs `Total Liabilities + Equity`: **`Variance Delta vs T0 == $0.00`**!
     - **`BS Total Cash & Cash Equivalents == Bank Book Grand Total Closing USD == sum(TB 1000 Cash) == GL 1000 Cash`**!
     - **`BS Accounts Receivable (AR) == Aging AR Grand Total == TB 1200 AR == GL 1200 AR`**!
     - **`BS Withholding Tax / TDS Receivable == TB 1300 == GL 1300`** (must include `+$300` from `S5` Customer TDS + `+$50` Batch TDS + `PKR 2,785` = `$10` if attached/recorded)!
     - **`BS Accounts Payable (AP) == Aging AP Grand Total == TB 2000 AP == GL 2000 AP`**!
     - **`BS Withholding Tax / TDS Payable == TB 2100 == GL 2100`** (must include `+$300` from `S6` Vendor TDS)!
     - **`(IS Net Income - T0) == (BS Cumulative Net Income - T0)`** (`Variance = $0.00` — confirming Revenue, COGS, G&A Expense, AR/AP DN & CN, Sales Discounts, Purchase Discounts, and JE Revenue/Expense ALL match!)
     - **`Trial Balance Total Debit == Total Credit`** (`Is Balanced = YES`, JE lines `50108`/`10301`/`50114`/`10201` map to real GL account names!).
     - **`General Ledger` Opening Dr=Cr, Period Dr=Cr, Closing Dr=Cr** (`3-Way Balanced`)!
2. **Historical Point-in-Time `as_of_date` Test:**
   - First verify we have at least one invoice dated earlier (or `2026-11-01`..`2026-11-08`) paid on `2026-11-10` — run **Balance Sheet** & **Aging Report** with `as_of_date = 2026-11-09` (before `2026-11-10` payments!):
   - **Assert:** Any Verified invoice dated `<= 2026-11-09` that was settled on `2026-11-10` STILL shows as unpaid Receivable/Payable in BOTH Balance Sheet and Aging as of `2026-11-09` (no premature zeroing!), and `BS Variance` stays unchanged!
3. **Operational Statements (`/accounting/report/agent-local-statement`, `freight-statement`, `revenue-cost`):**
   - Open `/accounting/report/agent-local-statement` (`as_of_date = 2026-11-10`):
     - **Assert:** Invoice `A` (`$5,300` TDS-settled) shows `Balance = 0.00` (if Show Balanced checked) or is settled (`Paid`); Cancelled/Void & Rejected `$9,999` invoices do NOT appear; any overdue invoice with Due Date `2026-10-01` sits in an **`Overdue`** bucket (NOT `Current`!); Future-due Invoice `B` (`2026-12-25`) sits in **`Current`**!
   - Open `/accounting/report/freight-statement` & `/accounting/report/revenue-cost`:
     - **Assert:** Verified Agent `AP CN` / `AP DN` rows reflect negative cost/balance reduction cleanly, TDS-settled `$5,300` shows `Paid / Balance = 0.00`, Rejected `$9,999` is excluded!
4. **Print & Excel Export Buttons Across Reports:**
   - Click Print & Export Excel/CSV on BS, IS, TB, GL, Aging, Bank Book, Bank Recon -> **Assert** HTTP `200`, non-empty output, zero server 500 errors!
5. **Update Report:** Append full Reconciliation Comparison Matrix Table (T0 vs Final Expected vs Actual vs Variance) to `BROWSER_ACCOUNTING_QA_REPORT.md`.

---

### Scenario 14 (`S14`) — Multi-Tenant Isolation & Direct URL IDOR Probe
**Goal:** Prove strict tenant boundary for `sardar@gmail.com` (`company_id = 1`) against `company_id = 20` data in the live browser.
1. Directly open the following `company_id = 20` URLs in browser tabs:
   - `http://localhost:8000/accounting/invoice/256`
   - `http://localhost:8000/accounting/invoice/256/edit`
   - `http://localhost:8000/accounting/invoice/256/print`
   - `http://localhost:8000/accounting/journal/8`
   - `http://localhost:8000/accounting/journal/entry?id=8`
2. **Assert:** EVERY single one returns **`403 Forbidden` or `404 Not Found`** (NEVER displays `INV-TGF-001-A` or `Standard Chartered Bank` or any Company 20 data!).
3. Search Invoice List (`/accounting/invoice`), Payment Lists (`/accounting/payment/received-list`, `made-list`), and Bank List (`/accounting/bank-list`) for `"Standard Chartered"` or `"TransGlobal"` -> **Assert:** `0` matches!
4. **Update Report:** Record all IDOR probes and results.

---

### Scenario 15 (`S15`) — Final Cleanup Safety Test, Summary Dashboard & Executive Verdict
1. Create 1 final manual `DRAFT` Invoice (`$111.00`), then delete it via Invoice List Delete button -> **Assert:** Draft deletes cleanly (`200` / redirect success).
2. Finalize `/home/muhammad-hanzala/Downloads/backups/fms2.0/tests/e2e/BROWSER_ACCOUNTING_QA_REPORT.md` with:
   - **Executive Summary Scorecard:** Total Scenarios (`16`: `S0`–`S15`), Pass count, Fail count, Console/HTTP 500 count, UI/UX observations.
   - **Accounting Flow & Freight Standards Checklist Verification Matrix:** Explicit `[PASS]` / `[FAIL]` row for every paragraph/claim in the Accounting Flow & Freight Standards Report.
   - **Reproducible Defect Tickets (if any FAILs occurred):** Exact Steps, Input, Expected, Actual, Network Payload/Response, Screenshot path.
