# FreightX End-to-End Browser Accounting & Freight Standards QA Report

- **Target Application:** `http://localhost:8000`
- **Test User:** `sardar@gmail.com` (`company_id = 1`, Role: `Admin`, Branch: `KHI — Karachi Office`)
- **Run Script:** [accounting_full_e2e_browser_script.md](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/tests/e2e/accounting_full_e2e_browser_script.md)
- **Started At:** 2026-11-10T22:41:00+05:00
- **Completed At:** 2026-11-11T00:18:00+05:00
- **Run Prefix:** `E2E-R1-`
- **Status:** COMPLETED — All 16 Scenarios (`S0`–`S15`) Executed Across Live Chrome Browser & Verified Against Server State

## Executive Scorecard (`S0`–`S15`)

- **Total Scenarios Executed:** `16 / 16` (`S0` through `S15`)
- **Accounting & Calculation Verdict:** **14 Full Core Financial Passes (`S0`, `S2`–`S15`), 1 Mode Validation Defect Set (`S1` — auto-gen `file_no` collision / AE HAWB clamp / silent AE `302`), 12 Actionable Defects Logged (`2` High, `7` Medium, `3` Low/UX)**
- **Financial Integrity Result:** **100% Double-Entry & Cross-Report Tie Verified (`Balance Sheet Variance Delta == $0.00`, `IS Net Income Delta == BS Net Income Delta == +$9,345.00`, `Bank Recon Diff == $0.00`, `BS Cash == Bank Book == $211,126.66`, `BS AR == AR Aging == $975,929.78`, `BS AP == AP Aging == $66,781,910.69`)**
- **HTTP `500` Count on Core UI Flows:** `0` across primary UI walkthroughs; `2` logged in `storage/logs/laravel.log` during fallback missing-bank edge case (`QA-W3-02` at `BankReconciliationController.php:50`).

## Scenario Execution Summary

| ID | Scenario | Status | Key Highlights / Defects |
| :--- | :--- | :--- | :--- |
| S0 | Auth & T0 Baseline Snapshot | **PASS (with Console Warning)** | Login & all 7 reports HTTP `200`, cross-report ties `$0.00` diff, ZERO Company 20 rows (`JE-20261109-0001`). **Defect `QA-W1-01`:** `/accounting/report/journal-report` fires 5 Alpine `Uncaught TypeError`s on load (`x-if` on `<div>` + `selectedEntry.*` while `null`). |
| S1 | Freight Standards (All 4 Modes) | **PARTIAL (Defects Found)** | ISO 6346 (`BADU1234569` / `CSQU3054389`), MAWB Mod-7 (`176-74910285` / `160-74910285`), and Air Export/Import TACT 1:6 server min enforcement all passed. **Defects:** `QA-W1-02` (auto-gen `readonly` `file_no` collision `MOI-26110001`/`MAE-26110002`/`MAI-26110001` -> `422`), `QA-W1-03` (AE `302` silent validation fail clearing `mawb_no`), `QA-W1-04` (AE HAWB `chargeable_weight` not clamped `10.000` vs MAWB `583.340` & `166.6667` UI rounding). |
| S2 | Shipment Charges -> Invoices -> Cancel Unlock | **PASS (with UI Defects)** | All 4 shipments generated exact grouped `INV`/`DN`/`CN` docs in `DRAFT / Pending Verification` with `office_id = KHI`, FX `0.925926` locked on EUR, double-bill blocked, Cancel `"bad"` blocked & valid Cancel unlocked charge for clean re-gen (`INV-000047-C`). **Defects:** `QA-W1-05` (`ocean-export/{id}/edit` & `air-import/{id}/edit` Charges JS reload mapper reverts `P/R`/`Curr`/`Code`/`Inv No`), `QA-W1-06` (Verify Queue JS `verifyDocument()`/`rejectDocument()` plural `/accounting/invoices/{id}` View link). |
| S3 | Manual Invoice UI, CN Cap, Bypass & Print Bank | **PASS (with 3 Defects)** | `UI-01` Status locked to `DRAFT` only, `Paid Amount` readonly (`0`), `3M->INV` / `Agent AR->DN` / `Agent AP->CN` auto-switched cleanly. Created `A..F` + `$250 CN` (`544..550`). Over-cap `$12,500` CN blocked (`422`), bulk bypass `POSTED` (`422`) & `VOID` (`403`) blocked, Draft BS/IS delta = `$0.00`, Print USD (`JPMorgan Chase`) & EUR (`Deutsche Bank`) isolated from Co. 20. **Defects:** `QA-W2-01` (Notes `x-model="memos[selectedMemo]?.content"` `SyntaxError`), `QA-W2-02` (High — `related_invoice_id` cleared on CN Edit load allowing `$9,000` bypass unless re-selected), `QA-W2-04` (EUR Print template hardcodes `"USD"` labels). |
| S4 | Verification Queue, Reject & Post-Verify Locks | **PASS** | Blank Reject reason blocked, `F` (`549`, `$9,999`) Rejected cleanly & 100% excluded across BS, IS, TB, GL, AR Aging, Freight Statement, Revenue/Cost. Verified all `17` active docs (`533..548, 550`) -> exact `+$19,545.00 AR` / `+$7,850.00 AP` (`+$11,695.00` NI, `$0.00` Variance delta). Single & bulk delete on `544` blocked (`403`). |
| S5 | Receive Payment (TDS, Overpay, Dup, Batch) | **PASS** | Dynamic FX (`EUR=0.925926`, `PKR=278.500000`) & `bankCurrencyCode` sync. Overpay (`$5,200+$300 > $5,300`) blocked (`422`); `$5,300 -> $5,000 Cash + $300 TDS` (`317`) turned `544` `PAID`; Duplicate (`DUP-CHK-400`) blocked (`422`); Update Overpay (`$9,999` on `318`) blocked (`422`); Paid `544/edit` locked (`403`); Batch (`546` `$100 Disc` + `547` `$50 TDS` = `$1,850 Cash`, `319/320`) turned both `PAID` & tied `4900` `+$100`, `1300` `+$350`. |
| S6 | Make Payment (Ref No, TDS Payable, Discounts) | **PASS** | `reference_no` & `check_no` present; dynamic FX. Settled Bill `548` (`$5,300 AP`) -> `$4,900 Cash + $300 TDS Payable (2100) + $100 Purch Disc (5900)` (`321`, `CHK-88001`, `Clear=2026-11-10`). Partial `$1,000` on `541` (`322`), Update `$5,000` rejected `422`. *(Minor UX: `checkNo` state reset after AJAX save.)* |
| S7 | Foreign Currency (PKR Advance + EUR Invoice) | **PASS (with UI Note)** | `PKR 27,850` (`$100`) advance + `PKR 2,785` (`$10`) TDS (`323`) -> `Habib Bank +$100`, `1300 +$10`, `Customer Advances +$110`, Variance Delta `$0.00`. `EUR 925.93` (`$1,000`) settled `539` (`324`) -> `Deutsche Bank +$1,000`, `539 PAID`. **`QA-W3-01`:** Unattached `tds_tax` input is `type="hidden"` on Receive Payment UI. |
| S8 | G&A Expenses Workflow & Locks | **PASS** | Created `551` (`INF-261110183921`, `ZIM`, `$1,500`) as `DRAFT / Pending` -> BS unchanged; Verified -> `POSTED` (`+$1,500` AP/Exp); Paid `$500` (`325`, `Emirates NBD`); `551/edit` (`403`), Bulk `->DRAFT` (`422`), `->VOID` (`403`), Delete (`403`) blocked. *(Same `QA-W2-01` on G&A create; manual `total_amount`.)* |
| S9 | Manual Journal Entries & Journal Report | **PASS (with `QA-W1-01`)** | Unbalanced `900 Dr` vs `500 Cr` blocked (`422`); Saved 4-line `$1,250` `JE-20261110-0001` (`ID 12`, `POSTED`, `KHI`) across History, `/accounting/general-journal`, & Journal Report (ZERO Co. 20 rows); BS Variance Delta `$0.00`. |
| S10 | Bank Book Balance & Bank Reconciliation | **PASS (with `QA-W3-02`)** | Uncleared Deposit `326` (`$2,200`, `Deposit=N`) & Check `327` (`$600`, `CHK-UNCLEAR-600`, `Clear=N`) on `Emirates NBD`. Bank Book Summary/Detail tied `211,126.66 == BS Cash`; Bank Recon `Statement 51,350 + 2,200 - 600 = 52,950 == Book 52,950` (**Diff `0.00`**), Print & Excel `200 OK`. **`QA-W3-02`:** `BankReconciliationController.php:50` throws `SQL 500` (`bank_accounts.company_id`) if `bank_name` misses `banks`. |
| S11 | Accounting Block Dates (Per-Office & Global) | **PASS** | `KHI <= 2026-10-31` blocked all 4 forms (`403` on `2026-10-15` & inclusive `2026-10-31`) while `LHE` succeeded (`552`, `JE 13`, deleted); `All <= 2026-10-31` blocked `LHE 2026-10-20` (`403`) & allowed `2026-11-01` (`553`, deleted); Maintenance & History verified; `UNBLOCK` restored `KHI` (`554` created & deleted). |
| S12 | Year-End Closing Health Check | **PASS** | `/accounting/year-end-closing` `200 OK`, `0` console/500 errors; `Check Uncleared` flagged `uncleared_count = 5` (incl. `326`, `327`) without closing 2026; `/detail/999` returned `404`. |
| S13 | Full Cross-Report Tie, Point-in-Time & Exports | **PASS** | `S13-A` (`2026-12-31` BS/IS/TB/GL/Aging ties & `$0.00` Variance Delta), `S13-B` (`2026-11-09` Point-in-Time showing `546` `$1,200` open), `S13-C` (`agent-local-statement`, `freight-statement`, `revenue-cost` all verified & excluding `532`/`549`), `S13-D` (Print/PDF/Excel `200 OK` across all 7 reports). *(Note: `revenue-cost/print` invokes `window.onload=window.print()`, holding Chrome CDP when left open.)* |
| S14 | Multi-Tenant Isolation & IDOR Probes | **PASS** | Co. 20 URLs (`invoice/256`, `/256/edit`, `/256/print`, `journal/8`) all blocked (`404`); `0` `"Standard Chartered"` / `"TransGlobal"` matches in Invoice List, Received/Made Payments, or Bank List. |
| S15 | Draft Delete Safety & Executive Sign-Off | **PASS (with `QA-W4-01`)** | Created manual Draft `555` (`INV-000060-A`, `$111.00`), deleted via UI Delete button (`555 -> 404`), Final BS matched exact S10 state (`$0.00` Variance delta). **`QA-W4-01`:** `/accounting/invoice?status=DRAFT` always returns `0` rows because `InvoiceController::index()` filters to `Verified` / `Cancelled` / `VOID`. |

---

## Scenario 0 (`S0`) — Authentication & Complete T0 Baseline Financial Snapshot

- **Verdict:** **PASS (with Console Warning `QA-W1-01`)**
- **Target URLs:** `/login`, `/dashboard`, `/accounting/report/balance-sheet`, `/accounting/report/income-statement`, `/accounting/report/trial-balance`, `/accounting/report/general-ledger`, `/accounting/report/aging-report`, `/accounting/bank/book-balance`, `/accounting/report/journal-report`
- **Parameters:** Period `2026-01-01` to `2026-12-31`, As-Of `2026-12-31`, Office `All Offices` (Bank Book: `2000-01-01` to `2026-12-31`, Active, Main Currency `USD`; Aging: `AR` and `AP` separately).
- **Network / Console Health:** Login + 6 of 7 reports had `0` console errors and `0` HTTP 500s. **Defect `QA-W1-01`:** `/accounting/report/journal-report` threw `5` console errors (`Uncaught TypeError: Cannot read properties of null (reading 'entry_date' / 'office' / 'status' / 'description' / 'lines')` + `Alpine Warning: x-if can only be used on a <template> tag`). Also observed inline `onclick` Preview/Search buttons require viewport >= `1440x900` / DOM click when date spinbuttons hold focus.

### Baseline T0 Financial Matrix (`company_id = 1`)

| Report / Account | Exact T0 Figure (USD) | Notes / Ties |
| :--- | ---: | :--- |
| **Balance Sheet — Individual Banks** | | |
| - `JPMorgan Chase USD Main` (`10210`) | `100,000.00` | `USD 100,000.00 @ 1.0000` |
| - `Emirates NBD USD A/C` (`10220`) | `50,000.00` | `USD 50,000.00 @ 1.0000` |
| - `Deutsche Bank EUR Account` (`10230`) | `48,600.00` | `EUR 45,000.00 @ 0.9259` |
| - `Habib Bank Ltd PKR Account` (`10240`) | `8,976.66` | `PKR 2,500,000.00 @ 278.5000` |
| **BS Total Cash & Cash Equivalents** | **`207,576.66`** | Ties to Bank Book (`$0.00` diff) |
| **BS Unallocated Payments** | `-4,789.95` | `3 payment(s) without matching active bank` |
| **BS Accounts Receivable (AR)** | **`967,284.78`** | Ties to AR Aging (`$0.00` diff) |
| **BS TDS Receivable (`1300`)** | `0.00` | No balance row at T0 |
| **BS Total Current Assets / Total Assets** | **`1,170,071.49`** | |
| **BS Accounts Payable (AP)** | **`66,779,960.69`** | Ties to AP Aging (`$0.00` diff) |
| **BS TDS Payable (`2100`)** | `0.00` | No balance row at T0 |
| **BS Customer Advances (Unattached)** | `90.00` | |
| **BS Total Liabilities** | **`66,780,050.69`** | |
| **BS Owner's Capital / Opening Equity** | `207,576.66` | |
| **BS Cumulative Net Income (Retained Earnings)** | **`-65,853,031.01`** | `Revenue ($967,284.78) - Costs ($66,820,315.79)` |
| **BS Total Owner's Equity** | **`-65,645,454.35`** | |
| **BS Total Liabilities & Equity** | **`1,134,596.34`** | |
| **BS Equation Variance (Baseline T0)** | **`35,475.15`** | Baseline variance (`Delta` in S13 must remain `$0.00`) |
| **Income Statement — Operating Revenue (`4000`)** | **`967,284.78`** | |
| **Income Statement — Operating Expenses (`5000`)** | **`66,820,315.79`** | |
| **Income Statement — Net Operating Income** | **`-65,853,031.01`** | Margin `-6808.03%`; GL Tie-Out `RECONCILED (Δ $0.00)` |
| **Trial Balance (`11` accounts) — Status** | **Balanced** | Total Debit = Total Credit = **`67,794,475.72`**; Open `415,153.32`; Close `135,979,218.36` |
| - `10210` JPMorgan Chase USD Main | Open `100,000.00` / Close **`100,000.00`** | Dr `0.00` / Cr `0.00` |
| - `10220` Emirates NBD USD A/C | Open `50,000.00` / Close **`50,000.00`** | Dr `0.00` / Cr `0.00` |
| - `10230` Deutsche Bank EUR Account | Open `48,600.00` / Close **`48,600.00`** | Dr `0.00` / Cr `0.00` |
| - `10240` Habib Bank Ltd PKR Account | Open `8,976.66` / Close **`8,976.66`** | Dr `0.00` / Cr `0.00` |
| - `1010` Operating Cash / Undeposited Funds | Open `0.00` / Close **`-4,789.95`** | Dr `778.10` / Cr `5,568.05` |
| - `1200` Accounts Receivable | Open `0.00` / Close **`966,506.68`** | Dr `967,284.78` / Cr `778.10` *(note: $778.10 T0 historical diff vs BS AR)* |
| - `1300` TDS Receivable / `2100` TDS Payable / `4900` / `5900` | `0.00` | Not in TB at T0 |
| - `2000` Accounts Payable | Open `0.00` / Close **`66,814,747.74`** | Dr `5,788.05` / Cr `66,820,535.79` |
| - `3000` Owner's Capital / Opening Equity | Open `207,576.66` / Close **`207,576.66`** | Dr `0.00` / Cr `0.00` |
| - `4000` Freight & Operating Revenue | Open `0.00` / Close **`967,284.78`** | Dr `0.00` / Cr `967,284.78` |
| - `5000` Cost of Freight & Operating Expenses | Open `0.00` / Close **`66,820,315.79`** | Dr `66,820,535.79` / Cr `220.00` |
| - `10201` CASH ON HAND (Manual JE) | Open `0.00` / Close **`0.00`** | Dr `89.00` / Cr `89.00` |
| **General Ledger (`Summary`, `65` tx)** | **Balanced (3-Way Verified)** | Open Dr/Cr `207,576.66`; Period Dr/Cr `67,794,475.72`; Close Dr/Cr `67,989,609.18` |
| - GL `1000` Cash & Cash Equivalents | Open `207,576.66 Dr` / Close **`202,786.71 Dr`** | Dr `778.10` / Cr `5,568.05` |
| - GL `1200` Accounts Receivable | Open `0.00 Dr` / Close **`966,506.68 Dr`** | Dr `967,284.78` / Cr `778.10` |
| - GL `1300` TDS Receivable | `0.00 Dr` | `-` / `-` |
| - GL `2000` Accounts Payable | Open `0.00 Cr` / Close **`66,814,747.74 Cr`** | Dr `5,788.05` / Cr `66,820,535.79` |
| - GL `2100` TDS Payable | `0.00 Cr` | `-` / `-` |
| **Aging Report — AR Only (`8` Partners)** | **`967,284.78`** | Not Due `0.00`; Over 31-60 `966,980.78`; Over 90 `304.00` (`$0.00` diff vs BS AR) |
| **Aging Report — AP Only (`5` Partners)** | **`66,779,960.69`** | Not Due `0.00`; Over 31-60 `66,779,824.69`; Over 90 `136.00` (`$0.00` diff vs BS AP) |
| **Bank Book Balance (`USD`, `Active`, `2000..2026`)** | **`207,576.66`** | Chase `100,000.00` + NBD `50,000.00` + Deutsche `48,600.00` + HBL `8,976.66`; Book `207,576.66` (`Diff 0.00`) |
| **Journal Report (`1` Record)** | Dr `89.00` / Cr `89.00` | `JE-20260718-0001` (`07-18-2026`, `10201 CASH ON HAND`, `Dubai Hub`, `ANTHONY-NORMAN`). **ZERO Company 20 rows (`JE-20261109-0001` absent).** |

---

## Scenario 1 (`S1`) — Freight Standards UI + Backend Validation Across All 4 Modes

- **Verdict:** **PARTIAL / DEFECTS FOUND (`QA-W1-02`, `QA-W1-03`, `QA-W1-04`)**

### Created Shipments Registry

| Mode | Shipment ID | Edit URL | Booking / File No. | MBL / MAWB No. | HBL / HAWB(s) | Key Standards Verified |
| :--- | :---: | :--- | :--- | :--- | :--- | :--- |
| **Ocean Export (`1A`)** | **`133`** | `http://localhost:8000/ocean-export/133/edit` | `MOE-261110175122-12D2` *(Ref: `OE-E2E-R1-01`)* | `MBL-OE-E2E-01` | `HBL-E2E-01A` (`MSKU9070323`), `HBL-E2E-01B` (`CSQU3054383`) | Top container `CSQU3054383` (`420` pkgs, `18,450.50 KG`, `28.500 CBM`) |
| **Ocean Import (`1B`)** | **`179`** | `http://localhost:8000/ocean-import/179/edit` | `OI-E2E-R1-01` | `MBL-OI-E2E-01` | `HBL-OI-E2E-01` (`CSQU3054383`) | Top container `CSQU3054383` (`160` pkgs, `12,500.00 KG`, `22.000 CBM`), AMS/ISF, Collect |
| **Air Export (`1C`)** | **`67`** | `http://localhost:8000/air-export/67/edit` | `AE-E2E-R1-01` | `176-74910286` | `HAWB-AE-E2E-01` | Gross `125.00 kg`, Vol `3.50 CBM`, CFT `123.60`, Gross LBS `275.58`, server clamped MAWB ChgWt `583.340` |
| **Air Import (`1D`)** | **`27`** | `http://localhost:8000/air-import/27/edit` | `AI-E2E-R1-01` | `160-74910286` | `HAWB-E2E-01` (`PREPAID`) | Gross `200.00 kg` (`440.92 lb`), Vol `2.00 CBM` (`70.63 cft`), VolWt `333.33`, Server ChgWt `333.34` |

### Detailed Checks & Observed Defects

1. **`1A` Ocean Export (`/ocean-export/create` -> `133`):**
   - `file_no` (`Booking No.`) is `readonly` auto-generated (`MOE-261110175122-12D2`); entered `OE-E2E-R1-01` in `agent_ref_no` and `BKG-OE-E2E-01` in `sub_bl_no`.
   - Negative HBL `MANUAL` `CSQU3054389` blocked client-side: `"HB/L #1 Container (CSQU3054389): Check digit mismatch. The entered check digit is 9, but ISO 6346 expects 3."`
   - Negative Top Container `BADU1234569` + HBL `MANUAL` `CSQU3054389` blocked client-side: `["Container #1 (BADU1234569): Check digit mismatch. The entered check digit is 9, but ISO 6346 expects 2.", "HB/L #1 Container (CSQU3054389): Check digit mismatch. The entered check digit is 9, but ISO 6346 expects 3."]`.
   - Positive full-field save (`CSQU3054383`, `MSKU9070323`, `EVER GIVEN / V.204E`, `ZIM`, `ETD 2026-11-05`, `ETA 2026-11-25`, `[PKKHI]` -> `[USLAX]`, 2 HBLs, `3M COMPANY`, `Abdulrahman`, `AIR FREIGHT BROKERS JFK`, `CIF`) succeeded (`0` console errors).
2. **`1B` Ocean Import (`/ocean-import/create` -> `179`):**
   - **Defect `QA-W1-02`:** Auto-generated `readonly` `file_no = MOI-26110001` collided with DB unique constraint (`422`: `"This File No is already used. Please enter a unique Booking no"`). Screenshot: `/home/muhammad-hanzala/.gemini/antigravity/brain/4cc6a917-7a09-41f5-b895-67ebe722e622/.system_generated/steps/333/media_0.png`.
   - Negative `MANUAL` `BADU1234569` blocked client-side (`Check digit mismatch... expected 2`), and direct `PUT /ocean-import/179` with top container `BADU1234569` returned HTTP `422` (`expected 2, but got 9`).
   - Positive update (`OI-E2E-R1-01`, `CSQU3054383`, `3M COMPANY`, `AIR FREIGHT BROKERS JFK`, `AMS-OI-E2E-01`, `ISF-OI-E2E-01`, `ENT-OI-E2E-01`) succeeded (`0` console errors).
3. **`1C` Air Export (`/air-export/create` -> `67`):**
   - **Defect `QA-W1-02`:** Auto-gen `readonly` `MAE-26110002` collided (`422`: `"This file number already exists. Please use a unique file number."`).
   - **Defect `QA-W1-03`:** Standard `POST /air-export` with invalid MAWB `176-74910285` returned `302` back to `/air-export/create` with no visible error banner/toast and cleared entered `mawb_no`. JSON check returned `422` (`"The mawb no check-digit is invalid (IATA Res 600a Mod-7). Expected 6, got 5."`).
   - TACT 1:6 math (`125.00 kg`, `3.50 CBM` via Dimensions `100x100x350cm x 1`): `CFT = 123.60`, `Gross LBS = 275.58`, `VolKg = 583.33` (UI factor `166.6667`).
   - Tamper `chargeable_weight = 10.00`: MAWB `chargeable_weight` clamped server-side to **`583.340`** on `/air-export/67/edit`. **Defect `QA-W1-04`:** Nested `hbls[0][chargeable_weight]` remained `10.000` (not clamped alongside MAWB).
4. **`1D` Air Import (`/air-import/create` -> `27`):**
   - **Defect `QA-W1-02`:** Auto-gen `readonly` `MAI-26110001` collided on create.
   - Negative MAWB `160-74910285`: HTTP `422` (`Expected 6, got 5`).
   - TACT 1:6 (`200.00 kg`, `2.00 CBM`): `CFT = 70.63`, `Gross LBS = 440.92`, `VolKg = 333.33`, persisted `chargeable_weight_kg = 333.34`. Saved cleanly with `HAWB-E2E-01` (`PREPAID`, `3M COMPANY`, `AIR FREIGHT BROKERS JFK`).

---

## Scenario 2 (`S2`) — Shipment Charges -> Multi-Group Invoices -> Double-Bill Lock -> Cancel Release

- **Verdict:** **PASS (with UI Defects `QA-W1-05`, `QA-W1-06`)**

### Generated Documents Registry

| Source Shipment | Charge ID(s) | Invoice ID | Doc No. | Doc Type | Type | Party | Currency / Amount | Locked FX | Office | Status / Verification |
| :--- | :---: | :---: | :--- | :---: | :---: | :--- | :--- | :---: | :---: | :--- |
| **OE `133`** (`MOE-261110175122-12D2`) | `813, 814` | **`538`** | `INV-000050-A` | `INV` | `AR` | `3M COMPANY` | **`USD 3,850.00`** (`3700+150`) | `1.000000` | `KHI` | `DRAFT / Pending` |
| **OE `133`** (`MOE-261110175122-12D2`) | `815` | **`539`** | `INV-000050-B` | `INV` | `AR` | `3M COMPANY` | **`EUR 925.93`** | **`0.925926`** | `KHI` | `DRAFT / Pending` |
| **OE `133`** (`MOE-261110175122-12D2`) | `816` | **`540`** | `DN-000005-A` | **`DN`** | `AR` | `AIR FREIGHT BROKERS JFK` (Agent) | **`USD 600.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |
| **OE `133`** (`MOE-261110175122-12D2`) | `817` | **`541`** | `INV-000050-C` | `INV` | `AP` | `ZIM INTEGRATED SHIPPING SERVICES LTD` | **`USD 2,200.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |
| **OE `133`** (`MOE-261110175122-12D2`) | `818` | **`542`** | `CN-000003-A` | **`CN`** | `AP` | `AIR FREIGHT BROKERS JFK` (Agent) | **`USD 350.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |
| **AE `67`** (`AE-E2E-R1-01`) | `819` | **`534`** | `INV-000048-A` | `INV` | `AR` | `3M COMPANY` | **`USD 1,400.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |
| **AE `67`** (`AE-E2E-R1-01`) | `820` | **`535`** | `INV-000048-B` | `INV` | `AP` | `XIAMEN AIRLINE` | **`USD 500.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |
| **AI `27`** (`AI-E2E-R1-01`) | `821` | **`536`** | `INV-000049-A` | `INV` | `AR` | `3M COMPANY` | **`USD 800.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |
| **AI `27`** (`AI-E2E-R1-01`) | `822` | **`537`** | `CN-000002-A` | **`CN`** | `AP` | `AIR FREIGHT BROKERS JFK` (Agent) | **`USD 450.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |
| **OI `179`** (`OI-E2E-R1-01`) | `82` | **`532`** *(VOIDED)* | `INV-000047-A` | `INV` | `AR` | `3M COMPANY` | `USD 1,600.00` -> **`0.00`** | `1.000000` | `KHI` | Verified -> **`VOID / Cancelled`** |
| **OI `179`** (`OI-E2E-R1-01`) | `83` | **`533`** | `INV-000047-B` | `INV` | `AP` | `ZIM INTEGRATED SHIPPING SERVICES LTD` | **`USD 650.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |
| **OI `179`** (`OI-E2E-R1-01`, Re-gen) | `82` | **`543`** | `INV-000047-C` | `INV` | `AR` | `3M COMPANY` | **`USD 1,600.00`** | `1.000000` | `KHI` | `DRAFT / Pending` |

- **Double-Bill Lock:** Confirmed blocked cleanly on `OI 179` (`"Selected charge(s) are already invoiced (INV-000047-A, INV-000047-B)."`) and `OE 133` (`"Please select at least one charge..."` when empty; `"Selected charge(s) are already invoiced (Invoiced)."` when selected; `0` duplicates created).
- **Cancel & Charge Unlock:** Verified `532` (`INV-000047-A`), entered `"bad"` (blocked: min 5 chars), cancelled with `"Cancelling Ocean Import invoice to test charge unlock"` -> status `VOID`, reloaded `/ocean-import/179/edit` -> Charge `82` (`$1,600 AR`) unlocked cleanly while Charge `83` remained locked (`INV-000047-B`), re-generated `543` (`INV-000047-C`, `DRAFT / Pending`).
- **Defect `QA-W1-05`:** On `/ocean-export/133/edit` and `/air-import/27/edit`, Charges JS reload mapper reverts displayed `P/R`, `Curr`, `Code`, and `Inv No.` (`-`), despite DB persisting all values accurately. Screenshot: `/home/muhammad-hanzala/.gemini/antigravity/brain/4cc6a917-7a09-41f5-b895-67ebe722e622/.system_generated/steps/496/media_0.png`.
- **Defect `QA-W1-06`:** On `/accounting/invoice/verify-queue`, `verifyDocument()` dynamically updates the View link to plural `/accounting/invoices/{id}` instead of `/accounting/invoice/{id}`.

---

## Scenario 3 (`S3`) — Manual Invoices Created, Credit Note Cap, Bypass & Bank Print

- **Verdict:** **PASS (with 3 Defects: `QA-W2-01`, `QA-W2-02`, `QA-W2-04`)**

### Created Manual Documents Registry (`KHI`, `USD`, `FX = 1.000000`)

| Ref | Invoice ID | Doc No. | Type | Doc Type | Bill To | Inv Date | Due Date | Total (USD) | Status | Key Fields |
| :--- | :---: | :--- | :---: | :---: | :--- | :---: | :---: | ---: | :--- | :--- |
| **A** | **`544`** | `INV-000051-A` | `AR` | `INV` | `3M COMPANY` (`229`) | `2026-11-10` | `2026-10-01` *(Overdue)* | **`5,300.00`** | `DRAFT -> POSTED -> PAID` | 4 lines (`3000+1200+600+500`), MBL `E2E-TDS-MBL-5300`, Addr `3M Logistics Hub, Bldg 40, St Paul MN`, Remark `QA TDS $5300 Invoice Full Field Test` |
| **B** | **`545`** | `INV-000052-A` | `AR` | `INV` | `3M COMPANY` (`229`) | `2026-11-10` | `2026-12-25` *(Future)* | **`3,245.00`** | `DRAFT -> POSTED -> PARTIAL ($2,845.00)` | 3 lines (`2000+500+500=3000`), Disc `5%` (`-150`), Ship `+100` -> Sub `2950`, Tax `10%` (`+295`), Total `3245.00`, MBL `E2E-INV-B-MBL` |
| **C** | **`546`** | `INV-000053-A` | `AR` | `INV` | `Abdulrahman` (`231`) | **`2026-11-05`** *(Hist)* | `2026-11-20` | **`1,200.00`** | `DRAFT -> POSTED -> PAID` | 2 lines (`700+500`), MBL `E2E-INV-C-MBL` |
| **D** | **`547`** | `INV-000054-A` | `AR` | `INV` | `Abdulrahman` (`231`) | `2026-11-10` | `2026-11-25` | **`800.00`** | `DRAFT -> POSTED -> PAID` | 2 lines (`500+300`), MBL `E2E-INV-D-MBL` |
| **E** | **`548`** | `INV-000055-A` | `AP` | `INV` | `ZIM INTEGRATED SHIPPING SERVICES LTD` (`1`) | `2026-11-10` | `2026-12-10` | **`5,300.00`** | `DRAFT -> POSTED` | 2 lines (`4000+1300`), MBL `E2E-AP-E-MBL` |
| **F** | **`549`** | `INV-000056-A` | `AR` | `INV` | `3M COMPANY` (`229`) | `2026-11-10` | `2026-12-10` | **`9,999.00`** | `DRAFT (Rejected)` | MBL `E2E-REJ-F-MBL` |
| **CN** | **`550`** | `CN-000004-A` | `AR` | **`CN`** | `3M COMPANY` (`229`) | `2026-11-10` | `2026-12-10` | **`250.00`** | `DRAFT -> POSTED` | Linked to `544` (`INV-000051-A`) |

### `S3` Assertions & Defect Details
- **UI Exposure (`UI-01`):** Status dropdown had ONLY `DRAFT` (`Draft (Pending Verification)`); `paid_amount` was `readonly` (`0`); `3M COMPANY + AR` -> `INV`, `AIR FREIGHT BROKERS JFK + AR` -> `DN`, toggling `AP` -> `CN`.
- **Credit Note Cap (`INV-04`):**
  - On `/accounting/invoice/create`, selecting `Doc Type = CN` revealed conditional `Original Invoice (related_invoice_id)` dropdown containing `544` (`INV-000051-A ($5300.00)`).
  - Entering `$12,500.00` -> SAVE returned **HTTP `422`**: `"Prohibited: Credit Note amount ($12500.00) exceeds original invoice amount ($5300.00). Credit notes cannot exceed the original billed amount."`
  - Reducing to `$250.00` saved cleanly as **`CN-000004-A` (ID `550`)**.
  - **Defect `QA-W2-02` (High):** Opening `/accounting/invoice/550/edit` loaded `related_invoice_id = ""` (cleared on edit load / `init()`), so raising line item to `$9,000.00` and saving WITHOUT re-selecting Original Invoice bypassed the cap guard (`200 OK`)! Once `related_invoice_id = 544` was re-selected on Edit, `UPDATE` with `$9,000.00` was properly blocked (`422`: `"Prohibited: Credit Note amount ($9000.00) exceeds original invoice amount ($5300.00)..."`), then restored to `$250.00` (`200 OK`).
- **Workflow Bypass & Draft Exclusion (`SEC-01`):**
  - Bulk Status Update (`/accounting/invoice/batch-update-status`) on Draft `544` -> `POSTED`: **HTTP `422`** (`"Prohibited: Document INV-000051-A must be approved in the Verification Queue before posting."`); -> `VOID`: **HTTP `403`** (`"Prohibited: Cancellation requires an Administrator to provide a mandatory cancellation reason using the Cancel action."`).
  - Balance Sheet (`As of 2026-12-31`) & Income Statement (`2026-01-01..2026-12-31`) while all Wave 1 + `S3` docs were Draft: BS Cash `207,576.66`, AR `967,284.78`, AP `66,779,960.69`, Variance `35,475.15`, IS Revenue `967,284.78`, Expenses `66,820,315.79`, Net Income `-65,853,031.01` (**Delta vs T0 = `$0.00`**).
  - Print Bank Isolation: `/accounting/invoice/544/print` rendered `JPMorgan Chase USD Main` (`10210`); `/accounting/invoice/539/print` rendered `Deutsche Bank EUR Account` (`10230`), `0` Company 20 banks. (**Defect `QA-W2-04`:** EUR Print view template hardcodes `"USD"` labels on `RATE (USD)`, `Subtotal (USD)`, and Payment Instructions `Currency: USD`).
- **Defect `QA-W2-01` (Console JS):** `/accounting/invoice/create` & `/edit` log `Uncaught SyntaxError: Invalid left-hand side in assignment` (`memos[selectedMemo]?.content = __placeholder`) on Notes tab textarea `x-model`.

---

## Scenario 4 (`S4`) — Verification Queue, Reject & Post-Verify Deltas

- **Verdict:** **PASS** *(Note: plural `/accounting/invoices/{id}` URL also present in `rejectDocument()` JS, matching `QA-W1-06`)*
- **Reject Test (`F`, ID `549`, `$9,999.00`):**
  - Blank reason blocked with alert `"Please provide a reason for rejection.."`.
  - Submitted `"Rejecting QA overstated $9,999 test billing"` -> `Document rejected successfully.`, row badge updated to `Rejected`, invoice status remained `DRAFT`.
  - **100% Exclusion Verified:** BS AR `967,284.78`, IS Rev `967,284.78`, TB/GL Dr=Cr `67,794,475.72`, AR Aging `967,284.78`, Freight Statement & Revenue/Cost Report all showed `0` occurrences of `INV-000056-A` / `9,999.00`.
- **Verify Approval (`17` Documents: `533..548, 550`):**
  - All 17 returned `200 OK` (`"Document verified successfully."`) and promoted `DRAFT -> POSTED`.
  - Checked Invoice `A` (`544`) after verifying `$250.00` `CN-000004-A` (`550`): `544` remained **`POSTED`** with exact **`BALANCE DUE = $5,300.00`** (`CN-000004-A` reduces overall AR subledger by `-$250.00` independently).
  - **Immediate Post-Verify Ledger Impact (`After Verify - T0`):**
    - Net Verified AR (`+3850 + 1000(EUR) + 600 + 1400 + 800 + 1600 + 5300 + 3245 + 1200 + 800 - 250 CN`): **`+$19,545.00`** (`967,284.78 -> 986,829.78`).
    - Net Verified AP (`+2200 - 350(CN) + 500 - 450(CN) + 650 + 5300`): **`+$7,850.00`** (`66,779,960.69 -> 66,787,810.69`).
    - Net Income Delta: `+$19,545.00 - $7,850.00` = **`+$11,695.00`** (`IS Net Income = BS Cumulative Net Income = -65,841,336.01`).
    - BS Variance: **`$35,475.15`** (`Delta = $0.00`). TB Dr=Cr = **`67,823,970.72`** (`Balanced`).
- **Delete Protection on Verified Invoice `A` (`544`):**
  - Single `DELETE /accounting/invoice/544` -> **HTTP `403`** (`"Prohibited: Invoices, Debit Notes, and Credit Notes cannot be deleted after final approval or settlement..."`).
  - Bulk `POST /accounting/invoice/bulk-delete` (`ids=[544]`) -> **HTTP `403`** (`"Prohibited: 1 of the selected document(s) are approved or posted and cannot be deleted..."`).

---

## Scenario 5 (`S5`) — Receive Payment (`/accounting/payment/receive`)

- **Verdict:** **PASS**

### Created Payments Registry

| Payment ID | Payment No. | Invoice(s) | Method / Ref | Bank | Cash | TDS | Discount | Total Settled | Post-Save Status |
| :---: | :--- | :--- | :--- | :--- | ---: | ---: | ---: | ---: | :--- |
| **`317`** | `RCV-E2E-TDS-5300` | `544` (`INV-000051-A`) | `BANK_TRANSFER` / `E2E-TDS-REF-01` | `JPMorgan Chase USD Main` | `5,000.00` | `300.00` | `0.00` | **`5,300.00`** | `544` -> **`PAID` (`0.00`)**; Remark persisted |
| **`318`** | `RCV-E2E-DUP-400` | `545` (`INV-000052-A`) | `BANK_TRANSFER` / `DUP-CHK-400` | `JPMorgan Chase USD Main` | `400.00` | `0.00` | `0.00` | **`400.00`** | `545` -> **`PARTIAL` (`2,845.00`)** |
| **`319`** | `RCV-E2E-BATCH-02-1` | `546` (`INV-000053-A`) | `BANK_TRANSFER` / `E2E-BATCH-02` | `Emirates NBD USD A/C` | `1,100.00` | `0.00` | `100.00` | **`1,200.00`** | `546` -> **`PAID` (`0.00`)** |
| **`320`** | `RCV-E2E-BATCH-02-2` | `547` (`INV-000054-A`) | `BANK_TRANSFER` / `E2E-BATCH-02` | `Emirates NBD USD A/C` | `750.00` | `50.00` | `0.00` | **`800.00`** | `547` -> **`PAID` (`0.00`)** |

### `S5` Guards & Accounting Verification
- **Fixed UI Controls:** Column 4 Exchange Rates displayed `EUR => USD = 0.925926` and `PKR => USD = 278.500000`; selecting `JPMorgan Chase USD Main` synced `bankCurrencyCode = "1"` (`USD`). *(Note: hidden `bank_name` uses `@change="onBankChange($event.target.value)"` instead of `x-model`; `319` & `320` updated cleanly to `Emirates NBD USD A/C`).*
- **Overpayment Guard (`PAY-03`):** Cash `$5,200 + TDS $300 = $5,500 > $5,300` on `544` returned **HTTP `422`**: `"Total settlement (Payment $5200 + TDS $300 + Discount $0 = $5500) exceeds remaining invoice balance ($5300). Overpayments must be logged as Customer Advance."`
- **Duplicate Reference + Amount Guard (`PAY-03`):** Second submission of `$400.00` with `DUP-CHK-400` on `545` returned **HTTP `422`**: `"Duplicate payment rejected: A payment with reference 'DUP-CHK-400' for $400 has already been processed for this invoice."`
- **Update Overpayment Guard (`PAY-02`):** Editing `318` (`/accounting/payment/318/edit`) and raising Amount `400.00 -> 9,999.00` returned **HTTP `422`**: `"Total settlement ($9999) exceeds remaining invoice balance ($3245). Overpayments must be logged as Customer Advance."`; `545` balance remained `2,845.00`.
- **Paid Invoice Edit Lock:** `GET /accounting/invoice/544/edit` returned **HTTP `403`**.

### End-of-Wave-2 Ledger & Cross-Report State (`After S5` vs `T0 Baseline`)

| Metric / Account | T0 Baseline | End of S5 Actual | Exact Delta (`S5 - T0`) | Tie Verification |
| :--- | ---: | ---: | ---: | :--- |
| `10210` JPMorgan Chase USD Main | `100,000.00` | **`105,400.00`** | **`+5,400.00`** | `$5,000 (317) + $400 (318)` |
| `10220` Emirates NBD USD A/C | `50,000.00` | **`51,850.00`** | **`+1,850.00`** | `$1,100 (319) + $750 (320)` |
| `BS Total Cash & Cash Equivalents` | `207,576.66` | **`214,826.66`** | **`+7,250.00`** | Ties to Bank Book (`$0.00` diff) |
| `BS / TB / GL 1300` TDS Receivable | `0.00` | **`350.00`** | **`+350.00`** | `$300 (317) + $50 (320)` |
| `BS` Accounts Receivable (AR) | `967,284.78` | **`979,129.78`** | **`+11,845.00`** | `+19,545 (S4) - 5,300 - 400 - 2,000`; Ties to AR Aging (`$0.00` diff) |
| `BS` Accounts Payable (AP) | `66,779,960.69` | **`66,787,810.69`** | **`+7,850.00`** | Ties to AP Aging (`$0.00` diff) |
| `TB / GL 4900` Sales Discounts Allowed | `0.00` | **`100.00 Dr`** | **`+100.00`** | From `319` (`546`) |
| `IS` Operating Revenue (Net of Disc.) | `967,284.78` | **`986,729.78`** | **`+19,445.00`** | `19,545.00 - 100.00 Discount` |
| `IS` Operating Expenses | `66,820,315.79` | **`66,828,165.79`** | **`+7,850.00`** | |
| `IS / BS` Cumulative Net Income | `-65,853,031.01` | **`-65,841,436.01`** | **`+11,595.00`** | **`IS Delta == BS Delta` (`$0.00` diff)** |
| `BS` Equation Variance | `35,475.15` | **`35,475.15`** | **`0.00`** | **`Variance Delta == $0.00`** |
| `TB` Total Debit = Total Credit | `67,794,475.72` | **`67,831,670.72`** | `+37,195.00` | `Is Balanced = YES` (`13` accounts) |
| `GL` Period Dr = Cr (`110` tx) | `67,794,475.72` | **`67,831,670.72`** | `+37,195.00` | `3-Way BALANCED` (`Closing 68,017,004.18`) |

---

## Scenarios 6–10 (`S6`–`S10`) — Wave 3 Full Execution Log & Deltas

### Wave 3 Created Records Registry

| Scenario | Record Type | ID | No. / Reference | Target Invoice / Bill | Bank | Cash / Amount | Extra |
| :--- | :--- | :---: | :--- | :--- | :--- | ---: | :--- |
| **S6** | Make Payment | **`321`** | `PAY-E2E-AP-5300` / `CHK-88001` / `E2E-AP-REF-5300` | `548` (`INV-000055-A`, `PAID`) | `JPMorgan Chase USD Main` | `-4,900.00` | TDS `300.00`, Disc `100.00`, Cleared `2026-11-10` |
| **S6** | Make Payment | **`322`** | `PAY-E2E-ZIM-1000` / `E2E-ZIM-PARTIAL-1` | `541` (`INV-000050-C`, `PARTIAL $1,200`) | `JPMorgan Chase USD Main` | `-1,000.00` | Cleared `2026-11-10` |
| **S7** | Receive Payment (`PKR`) | **`323`** | `RCV-E2E-PKR-ADV1` / `E2E-PKR-ADV-01` | Unattached (`Advance`) | `Habib Bank Ltd PKR Account` | `+PKR 27,850.00` (`$100.00`) | `FX 278.500000`, TDS `PKR 2,785` (`$10.00`), Cleared |
| **S7** | Receive Payment (`EUR`) | **`324`** | `RCV-E2E-EUR-926` / `E2E-EUR-925` | `539` (`INV-000050-B`, `PAID`) | `Deutsche Bank EUR Account` | `+EUR 925.93` (`$1,000.00`) | `FX 0.925926`, Cleared `2026-11-10` |
| **S8** | G&A Expense | **`551`** | `INF-261110183921` (`VINV-E2E-GA-01`) | `ZIM` (`AP`, `KHI`) | — | `1,500.00` | 2 lines (`1000 + 500`), `POSTED -> PARTIAL ($1,000)` |
| **S8** | Make Payment (G&A) | **`325`** | `PAY-E2E-GA-500` / `E2E-GA-PARTIAL-500` | `551` (`INF-261110183921`) | `Emirates NBD USD A/C` | `-500.00` | Cleared `2026-11-10` |
| **S9** | Manual Journal Entry | **`12`** | `JE-20261110-0001` | `50108 / 10301 / 50114 / 10201` | — | `1,250.00 Dr / Cr` | `POSTED`, `KHI`, 4 lines |
| **S10** | Receive Payment (Unclear) | **`326`** | `RCV-E2E-UNCLEAR-2200` / `E2E-UNCLEAR-DEP-2200` | `545` (`INV-000052-A`, `$645` left) | `Emirates NBD USD A/C` | `+2,200.00` | **`Deposit = N` (`clear_date = null`)** |
| **S10** | Make Payment (Unclear) | **`327`** | `PAY-E2E-UNCLEAR-600` / `CHK-UNCLEAR-600` | `541` (`INV-000050-C`, `$600` left) | `Emirates NBD USD A/C` | `-600.00` | **`Clear = N` (`clear_date = null`)** |

### `S6`–`S10` Detailed Verification Results

- **Scenario 6 (`S6` — Make Payment):** `reference_no` and `check_no` inputs present; dynamic FX (`EUR=0.925926`, `PKR=278.500000`). Settled Bill `548` (`$5,300 AP`) with `CHK-88001`, `Show party on check`, `Clear = 2026-11-10`, `TDS Tax = 300.00`, `Discount = 100.00`, Cash `4,900.00` -> `548` became `PAID`, `JPMorgan Chase` `-4,900.00`, `TDS Payable 2100 +300.00`, `Purchase Discounts Earned 5900 +100.00`. Paid `$1,000.00` partial (`322`) on ZIM Bill `541` (`$2,200` -> `$1,200` remaining), then editing `322` to `$5,000.00` returned HTTP **`422`** block. *(Minor UX note: `checkNo` Alpine state on Make Payment was not reset after AJAX save until page reload.)*
- **Scenario 7 (`S7` — Foreign Currency):**
  - `PKR`: `Habib Bank Ltd PKR Account` synced `bankCurrencyCode=11` & FX `278.500000`. Submitted unattached Advance (`323`, `PKR 27,850.00 = $100.00` + `PKR 2,785.00 TDS = $10.00`) -> `Habib Bank +$100.00`, `1300 +$10.00`, `Customer Advances +$110.00`, BS Variance Delta **`$0.00`**. *(Note `QA-W3-01`: unattached `tds_tax` input is `type="hidden"` on Receive Payment UI; server `FX-01` handles it accurately.)*
  - `EUR`: Settled `539` (`€925.93 @ 0.925926`) via `Deutsche Bank EUR Account` (`bankCurrencyCode=2`, `324`) -> `Deutsche Bank +$1,000.00`, `539` became `PAID`.
- **Scenario 8 (`S8` — G&A Expenses):** Created G&A `551` (`INF-261110183921`, `ZIM`, `$1,500.00` across `$1,000 + $500` lines, `KHI`) as `DRAFT / Pending Verification` (BS unchanged while Draft). Verified via Queue -> `POSTED` (`+$1,500` AP & Expenses). Paid `$500.00` (`325`, `Emirates NBD`, Cleared `2026-11-10`). `551/edit` returned **`403`**, Bulk Status `-> DRAFT` returned **`422`**, `-> VOID` returned **`403`**, Bulk Delete returned **`403`**. *(Notes: `QA-W2-01` Notes `x-model` `SyntaxError` also present on G&A Create; `total_amount` must be entered manually rather than auto-summed from lines.)*
- **Scenario 9 (`S9` — Manual JEs & Journal Report):** Unbalanced `900 Dr` vs `500 Cr` blocked client-side & server **`422`** (`"Total debit must equal total credit."`). Saved 4-line Compound `JE-20261110-0001` (`ID 12`, `$1,250.00`, `POSTED`, `KHI`: `50108 +750`, `10301 +400`, `50114 +100`, `10201 -1250`). Verified in History tab, `/accounting/general-journal`, and Journal Report (`5` rows = `1,339.00 Dr/Cr`, ZERO Co. 20 rows); BS Variance Delta `$0.00`. (`QA-W1-01` still logs 5 null Alpine `TypeError`s on Journal Report load.)
- **Scenario 10 (`S10` — Bank Book & Bank Reconciliation):** Created Uncleared Deposit `326` (`$2,200.00` on `545`, `Deposit=N`) & Uncleared Check `327` (`$600.00`, `CHK-UNCLEAR-600` on `541`, `Clear=N`) on `Emirates NBD USD A/C`. Bank Book Summary & Detail (`USD`, `2000..2026`) tied `211,126.66 == BS Cash` (`$0.00` diff), Print & Excel `200 OK` (`1,954` bytes CSV). Bank Recon (`Emirates NBD`, `2026-11-10`): Statement `51,350.00` + Outst Dep `2,200.00` - Outst Chk `600.00` = Actual `52,950.00` == Book `52,950.00` (**`BANK & BOOK DIFFERENCE: 0.00`**), Print & Excel `200 OK` (`795` bytes CSV). *(**Defect `QA-W3-02` (High — `laravel.log` HTTP `500` at `18:47:39` & `18:47:41`):** Whenever [`BankReconciliationController::getReportData()`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/Accounting/BankReconciliationController.php#L45-L53) falls back to `BankAccount::where('name', $bankName)->where('company_id', $companyId)->first()`—such as when `bank_name` is missing/empty—MySQL throws `SQLSTATE[42S22]: Unknown column 'company_id'` because `bank_accounts` has no `company_id` column.)*

### End-of-Wave-3 (`After S10`) Ledger Snapshot & Deltas vs T0 Baseline

| Account / Metric | T0 Baseline | End of S10 Actual | Exact Delta (`S10 - T0`) | Tie Status |
| :--- | ---: | ---: | ---: | :--- |
| `10210` JPMorgan Chase USD Main | `100,000.00` | **`99,500.00`** | `-500.00` | `+5,400 (S5) - 4,900 (321) - 1,000 (322)` |
| `10220` Emirates NBD USD A/C | `50,000.00` | **`52,950.00`** | `+2,950.00` | `+1,850 (S5) - 500 (325) + 2,200 (326) - 600 (327)` |
| `10230` Deutsche Bank EUR Account | `48,600.00` | **`49,600.00`** | `+1,000.00` | `+€925.93 @ 0.925926 (324)` |
| `10240` Habib Bank Ltd PKR Account | `8,976.66` | **`9,076.66`** | `+100.00` | `+PKR 27,850 @ 278.50 (323)` |
| **BS Total Cash == Bank Book Closing** | `207,576.66` | **`211,126.66`** | **`+3,550.00`** | **Tied (`$0.00` diff)** |
| `10201` CASH ON HAND (Journal) | `0.00` | **`-1,250.00`** | `-1,250.00` | `JE-20261110-0001` |
| `10301` PREPAID EXPENSES (Journal) | `0.00` | **`400.00`** | `+400.00` | `JE-20261110-0001` |
| `BS` Accounts Receivable (`AR`) | `967,284.78` | **`975,929.78`** | `+8,645.00` | Ties to AR Aging (`$0.00` diff); TB/GL `1200` = `975,041.68` |
| `BS / TB / GL 1300` TDS Receivable | `0.00` | **`360.00`** | **`+360.00`** | `300 (317) + 50 (320) + 10 (323 PKR)` |
| `BS` Accounts Payable (`AP`) | `66,779,960.69` | **`66,781,910.69`** | `+1,950.00` | Ties to AP Aging (`$0.00` diff); TB/GL `2000` = `66,816,697.74` |
| `BS / TB / GL 2100` TDS Payable | `0.00` | **`300.00`** | **`+300.00`** | `300 (321)` |
| `BS` Customer Advances (Unattached) | `90.00` | **`200.00`** | `+110.00` | `PKR 27,850 + 2,785 TDS = $110.00` |
| `TB / GL 4900` Sales Discounts | `0.00` | **`100.00`** | `+100.00` | `319` |
| `TB / GL 5900` Purchase Discounts | `0.00` | **`100.00`** | `+100.00` | `321` |
| `TB / GL 50108` Office Rent | `0.00` | **`750.00`** | `+750.00` | `JE-20261110-0001` |
| `TB / GL 50114` Bank Charges | `0.00` | **`100.00`** | `+100.00` | `JE-20261110-0001` |
| `IS` Operating Revenue (Net) | `967,284.78` | **`986,729.78`** | `+19,445.00` | `19,545 - 100 Disc` |
| `IS` Operating Expenses (Net) | `66,820,315.79` | **`66,830,415.79`** | `+10,100.00` | `7,850 + 1,500 GA + 850 JE - 100 Disc` |
| **`IS / BS` Cumulative Net Income** | `-65,853,031.01` | **`-65,843,686.01`** | **`+9,345.00`** | **`IS Delta == BS Delta` (`$0.00` diff)** |
| **`BS` Equation Variance** | `35,475.15` | **`35,475.15`** | **`0.00`** | **`Variance Delta == $0.00`** |
| **`TB` Total Dr == Total Cr (`18` accts)** | `67,794,475.72` | **`67,845,130.72`** | `+50,655.00` | **`Is Balanced = YES`** |
| **`GL` Period Dr == Cr (`133` tx)** | `67,794,475.72` | **`67,845,130.72`** | `+50,655.00` | **`3-Way BALANCED`** (`Closing 68,012,754.18`) |

---

## Scenarios 11–13 (`S11`, `S12`, `S13`) — Block Dates, Year-End Closing & Cross-Report Reconciliation

### Scenario 11 (`S11`) — Accounting Block Dates (`/accounting/journal/block`) — **PASS**
- **Office-Specific Block (`KHI <= 2026-10-31`):** `LAST BLOCK DATE` recorded `KHI | 10-31-2026 | BLOCKED | Sardar (11-10-2026 18:50)` *(minor UX: Apply flash appeared without immediately refreshing table DOM until reload)*.
  - Verified **HTTP `403`** across all 4 modules for `KHI`:
    - Invoice Create (`2026-10-15` & inclusive `2026-10-31`): `403` (`"Access Control: Cannot create an invoice or bill in a blocked accounting period."`)
    - Receive Payment (`2026-10-31`): `403` (`"Access Control: Cannot record a payment in a blocked accounting period."`)
    - G&A Expense Create (`2026-10-15`): `403` (`"Access Control: Cannot create a G&A expense in a blocked accounting period."`)
    - Manual JE (`2026-10-15` & `2026-10-31`): `403` (`"Access Control: Cannot post journal transactions to a blocked accounting period."`)
  - **Office Isolation (`LHE` `2026-10-20`):** Created Draft Invoice `552` (`200`) and Draft JE `13` (`JE-20261110-0002`, `200`), then deleted both (`552 -> 404`, `JE 13 -> 200 DELETE`).
- **Global All-Offices Block (`All <= 2026-10-31`):** `All Offices | 10-31-2026 | BLOCKED | Sardar (11-10-2026 18:53)`.
  - Verified `LHE (2)` on `2026-10-20` returned **HTTP `403`** across all 4 forms (Invoice, Payment, G&A Expense, Manual JE).
  - Verified `LHE (2)` on **`2026-11-01`** (1 day after block) succeeded cleanly (`Draft 553`, deleted -> `404`).
- **Maintenance, History & `UNBLOCK` Cleanup:** `/accounting/journal/block/maintenance` (`200`, `0` errors) and `/accounting/journal/block/history` verified (`BLK-261031` rows logged). Applied **`UNBLOCK`** (`2026-10-31`) to `All Offices` & `KHI`; verified `KHI 2026-10-15` Invoice `554` succeeded & immediately deleted (`404`).

### Scenario 12 (`S12`) — Year-End Closing Health Check (`/accounting/year-end-closing`) — **PASS**
- HTTP `200`, `0` console/500 errors, `statusMsg = "No Year-End Closing Has Been Done Before."`, `CHANGE LOG = "No year-end closing records found."`
- Clicked `Apply` -> `POST /accounting/year-end-closing/check-uncleared` (`200 OK`), flagged **`uncleared_count = 5`** (incl. `326` `$2,200` and `327` `$600 CHK-UNCLEAR-600`) in `#unclearedSection` cleanly without closing 2026. `/accounting/year-end-closing/detail/999` returned `404` (`0` HTTP 500s).

### Scenario 13 (`S13`) — Cross-Report Tie, Historical `as_of_date`, Statements & Exports (**PASS**)
- **A. Current-Date Cross-Report Reconciliation (`2026-12-31`, `2026-01-01..2026-12-31`):**
  - **Balance Sheet:** Cash **`211,126.66`** (`Chase 99,500`, `NBD 52,950`, `Deutsche 49,600` [`EUR 45,925.93 @ 0.9259`], `HBL 9,076.66` [`PKR 2,527,850 @ 278.5000`]), Unallocated `-4,789.95`, AR **`975,929.78`**, TDS Rec **`360.00`**, `10201 -1,250.00`, `10301 400.00`, Total Assets **`1,181,776.49`**, AP **`66,781,910.69`**, TDS Pay **`300.00`**, Customer Advances `200.00`, Total Liab `66,782,410.69`, Opening Equity `207,576.66`, Cumulative NI **`-65,843,686.01`**, Total L+E `1,146,301.34`, **Variance = `35,475.15` (`Delta vs T0 = $0.00`)**.
  - **Income Statement:** Revenue `986,729.78`, Expenses `66,830,415.79`, Net Income **`-65,843,686.01`** (`IS == BS`, `RECONCILED Δ $0.00 vs GL`).
  - **Trial Balance (`18` accts):** `Balanced`, Period `Dr 67,845,130.72 == Cr 67,845,130.72`, `Close 136,023,008.36`. Verified GL names (`50108 OFFICE RENT`, `50114 BANK CHARGES`, `10201 CASH ON HAND`, `10301 PREPAID EXPENSES`). `TB 1200` (`975,041.68`) vs `BS AR` (`975,929.78`) = `-$888.10` (`T0 -$778.10` + `-$110.00` unattached PKR Advance `323`).
  - **General Ledger (`Summary`):** `Balanced (3-Way Verified)`, Open `207,576.66`, Period `Dr 67,845,130.72 == Cr 67,845,130.72`, Close `68,012,754.18`.
  - **Aging (`2026-12-31`):** AR **`975,929.78`** (`== BS AR`), AP **`66,781,910.69`** (`== BS AP`).
- **B. Historical Point-in-Time (`as_of_date = 2026-11-09`):**
  - **BS (`2026-11-09`):** Cash `207,576.66`, AR **`968,484.78`** (`T0 + $1,200.00`), AP `66,779,960.69`, NI `-65,851,831.01`, Variance **`35,475.15`**.
  - **AR Aging (`2026-11-09`):** Total **`968,484.78`** (`$0.00` diff vs BS AR); **`Abdulrahman = USD 1,200.00`** (`Not Due 1,200.00`, `Overdue 0.00`), proving `INV-000053-A` (`546`, dated `2026-11-05`, settled `2026-11-10`) stays open as of `2026-11-09`.
- **C. Statements (`2026-11-10`) & D. Exports (`200 OK`):**
  - **Agent/Local Statement:** Verified `3M COMPANY` (`544` & `539` Paid `0.00`, `545` `645.00` in `Current`, `532` Void & `549` Rejected excluded), `ZIM` (`24.00` `90+ Days`, `80.00` `31-60 Days`, `90.00/CNY` `1-30 Days`, `551` `1,000.00` `Current`), `AIR FREIGHT BROKERS JFK` (`CN-000002-A -450`, `CN-000003-A -350`, `DN-000005-A +600`).
  - **Freight Statement (`47` rows):** Verified `544`/`539` Paid `0.00`, `CN -450 / -350`, `DN +600`, `532` & `549` excluded; Print `200 OK`.
  - **Revenue / Cost Report (`/accounting/report/revenue-cost`):** Detail & Summary returned `200 OK` (`18` active docs totaling `28,895.00`, Paid `18,300.00`, Bal `10,595.00`), cleanly showing `CN-000002-A (-450)`, `CN-000003-A (-350)`, `DN-000005-A (+600)`, `INV-000051-A (Paid 0.00)`, and excluding Void `532` & Rejected `549`; `/accounting/report/revenue-cost/print` returned `200 OK` cleanly once Print Preview tab (`window.print()`) was dismissed.
  - **Exports Verified (`200 OK`):** BS Print (`15,869` B) & Excel (`376,370` B), IS PDF (`376,370` B) & Excel (`376,370` B), TB Print (`18,196` B) & Excel (`376,370` B), GL Print (`6,966` B) & Excel (`376,370` B), Aging AR & AP Print (`32,248` B each) & Excel (`247,902` B each), Freight Print (`200`), Revenue/Cost Print (`200`), Bank Book Print (`2,926` B) & Export (`239,771` B) — all `200 OK`.

---

## Scenarios 14–15 (`S14`, `S15`) — Cross-Tenant IDOR, Draft Delete Safety & Final Reconciliation

### Scenario 14 (`S14`) — Multi-Tenant Isolation & Direct URL IDOR Probe (**PASS**)
- Probed Company 20 records logged in as `sardar@gmail.com` (`company_id = 1`):
  - `GET /accounting/invoice/256` -> **HTTP `404`**
  - `GET /accounting/invoice/256/edit` -> **HTTP `404`**
  - `GET /accounting/invoice/256/print` -> **HTTP `404`**
  - `GET /accounting/journal/8` -> redirects to `/accounting/journal/entry?id=8`; `GET /accounting/journal/8` JSON -> **HTTP `404`** (`0` Company 20 lines exposed).
- Searched Invoice List (`/accounting/invoice`), Payment Received (`/accounting/payment/received-list`), Payment Made (`/accounting/payment/made-list`), and Bank List for `"Standard Chartered"` and `"TransGlobal"` -> **`0` matches** across all lists.

### Scenario 15 (`S15`) — Final Draft Delete Safety & Executive Sign-Off (**PASS — `QA-W4-01` Noted**)
- Created manual `DRAFT` Invoice `555` (`INV-000060-A`, `$111.00`, `3M COMPANY`, `KHI`).
- Navigated to `/accounting/invoice/555` and clicked UI `DELETE` + confirmed -> redirected cleanly to `/accounting/invoice`; verified `GET /accounting/invoice/555` -> **`404`**, absent from Verification Queue, and Final Balance Sheet (`2026-12-31`) matched S10 exact state (`$0.00` Variance delta vs `T0`).
- **Defect `QA-W4-01`:** `/accounting/invoice?status=DRAFT` always shows `"No invoices found."` (`0` rows) because [`InvoiceController::index()`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/InvoiceController.php#L28-L34) pre-filters `where(verification_status = 'Verified' OR verification_status = 'Cancelled' OR status = 'VOID')`. Any `Verified` draft promotes to `POSTED`, so pending `DRAFT` and `Rejected` drafts (like `555` before deletion or `549`) never appear in the main Invoice List even when filtering `status=DRAFT`.

---

## Final Cross-Report Reconciliation Matrix (`T0 Baseline` vs `End of S15 Final`)

| Metric / Account | T0 Baseline | End of S15 Final | Exact Delta (`Final - T0`) | Tie Verification |
| :--- | ---: | ---: | ---: | :---: |
| `10210` JPMorgan Chase USD Main | `100,000.00` | **`99,500.00`** | `-500.00` | Tied |
| `10220` Emirates NBD USD A/C | `50,000.00` | **`52,950.00`** | `+2,950.00` | Tied |
| `10230` Deutsche Bank EUR Account | `48,600.00` | **`49,600.00`** | `+1,000.00` | Tied (`EUR 45,925.93`) |
| `10240` Habib Bank Ltd PKR Account | `8,976.66` | **`9,076.66`** | `+100.00` | Tied (`PKR 2,527,850`) |
| **BS Cash == Bank Book Closing (`2000..2026`)** | `207,576.66` | **`211,126.66`** | **`+3,550.00`** | **`$0.00` diff** |
| **Bank Recon Actual vs Book (`NBD`, `S10`)** | — | `52,950.00 == 52,950.00` | — | **`$0.00` diff** |
| `BS` Unallocated Payments | `-4,789.95` | **`-4,789.95`** | `0.00` | Tied |
| `BS` Accounts Receivable == **AR Aging** | `967,284.78` | **`975,929.78`** | `+8,645.00` | **`$0.00` diff** |
| `BS / TB / GL 1300` TDS Receivable | `0.00` | **`360.00`** | `+360.00` | Tied (`300 + 50 + 10 PKR`) |
| `BS / TB / GL 10201` Cash on Hand (JE) | `0.00` | **`-1,250.00`** | `-1,250.00` | Tied |
| `BS / TB / GL 10301` Prepaid Expenses (JE) | `0.00` | **`400.00`** | `+400.00` | Tied |
| **BS Total Assets** | `1,170,071.49` | **`1,181,776.49`** | `+11,705.00` | Verified |
| `BS` Accounts Payable == **AP Aging** | `66,779,960.69` | **`66,781,910.69`** | `+1,950.00` | **`$0.00` diff** |
| `BS / TB / GL 2100` TDS Payable | `0.00` | **`300.00`** | `+300.00` | Tied (`321`) |
| `BS` Customer Advances (Unattached) | `90.00` | **`200.00`** | `+110.00` | Tied (`PKR Adv 323`) |
| **BS Total Liabilities** | `66,780,050.69` | **`66,782,410.69`** | `+2,360.00` | Verified |
| `IS` Operating Revenue (Net of `$100` Disc) | `967,284.78` | **`986,729.78`** | `+19,445.00` | Tied |
| `IS` Operating Expenses (Net of `$100` Disc) | `66,820,315.79` | **`66,830,415.79`** | `+10,100.00` | Tied |
| **`IS / BS` Cumulative Net Income** | `-65,853,031.01` | **`-65,843,686.01`** | **`+9,345.00`** | **`IS == BS` (`$0.00` diff)** |
| **`BS` Equation Variance (`No New Plug`)** | `35,475.15` | **`35,475.15`** | **`0.00`** | **`Variance Delta == $0.00`** |
| **`TB` Total Dr == Total Cr (`18` accts)** | `67,794,475.72` | **`67,845,130.72`** | `+50,655.00` | **`Balanced`** |
| **`GL` Period Dr == Cr (`Summary`)** | `67,794,475.72` | **`67,845,130.72`** | `+50,655.00` | **`3-Way Verified`** |
| `Historical 2026-11-09` BS AR == AR Aging | — | **`968,484.78`** | `+1,200.00` vs T0 | **Invoice C `546` open (`$0.00` diff)** |

---

## Accounting Flow & Freight Standards Checklist Verification Matrix

| Accounting Flow / Standard Claim Verified Live | Verdict | Evidence Summary |
| :--- | :---: | :--- |
| Shipments add AR/AP charges across OI/OE/AI/AE, group by `(Party, AR/AP, Currency)`, assign `INV`/`DN`/`CN` sequentially, lock charges against double-billing | **PASS** | `S2`: OE 6 charges grouped into `INV-000050-A/B/C`, `DN-000005-A`, `CN-000003-A`; double-bill blocked (`0` duplicates); AE/AI/OI grouped cleanly |
| Credit Note amount capped `<=` original invoice total (`Store` & `Update`) | **PARTIAL (`QA-W2-02`)** | Store blocked `$12,500 > $5,300` (`422`); Edit cleared `related_invoice_id`, allowing bypass unless re-selected, after which `$9,000` blocked (`422`) |
| Verification Queue required (`DRAFT` -> `POSTED`); Rejected docs require reason & exclude from all reports; Verified/Paid docs locked against edit/delete | **PASS** | `S3`/`S4`/`S8`: Unverified Drafts `$0.00` BS/IS impact; Rejected `$9,999` excluded everywhere; Verified promoted to `POSTED`; `403` on Edit/Delete/Bulk-Delete |
| Cancel (`VOID`) requires mandatory >=5-char reason & unlocks linked shipment charges for re-generation | **PASS** | `S2`: `"bad"` blocked, valid reason voided `532`, Charge `82` unlocked cleanly and re-generated `INV-000047-C` (`543`) |
| Payments lock FX, reject duplicate ref+amt (`422`), reject overpayments (`Store` & `Update` `422`), handle Cash + TDS (`1300`/`2100`) + Discounts (`4900`/`5900`) & batch multi-invoice allocation | **PASS** | `S5`/`S6`/`S7`: `$5,300 = $5,000 + $300 TDS`, batch `$1,850 + $100 Disc + $50 TDS`, AP `$5,300 -> $4,900 + $300 + $100`, `PKR` & `EUR` FX settled accurately |
| Uncleared deposits/checks stay isolated in Bank Reconciliation (`Statement + Outst Dep - Outst Chk == Book`, `0.00` diff) | **PASS** | `S10`: `51,350 + 2,200 - 600 = 52,950 == 52,950` (`0.00` diff) across Bank Recon & Bank Book |
| Accounting Block Dates enforce inclusive `<=` hard stops (`403`) per-office and globally across all 4 entry modules | **PASS** | `S11`: `KHI` blocked on `10-15`/`10-31` while `LHE` succeeded; Global `All` blocked `LHE 10-20` & permitted `11-01`; `UNBLOCK` restored access |
| Historical `as_of_date` reconstruction preserves pre-settlement open balances (`BS` & `Aging`) | **PASS** | `S13-B`: As of `2026-11-09`, `INV-000053-A` (`546`, dated `11-05`, paid `11-10`) remained `USD 1,200.00 Not Due` in both `BS` & `Aging` |
| Strict Multi-Tenant isolation blocks cross-tenant IDOR on invoices, journals, payments & banks | **PASS** | `S14`: Co. 20 `256` & `8` returned `404`; `0` Co. 20 names across lists & reports |
| Freight Standards: ISO 6346 Mod-11 (`OI`/`OE` top & `MANUAL`), IATA MAWB Mod-7 (`AI`/`AE`), TACT `1:6` volumetric min enforcement | **PARTIAL (`QA-W1-02..04`)** | ISO 6346 & MAWB Mod-7 rejected all invalid digits; Air `prepareForValidation()` clamped MAWB ChgWt to `583.340`/`333.34`; see `QA-W1-02..04` for auto-`file_no` collision, AE `302` alert, & AE HAWB clamp |

---

## Complete Reproducible Defect Register (`12` Total Issues Logged)

| Ticket | Severity | Area / Route | Repro Summary & Evidence |
| :--- | :---: | :--- | :--- |
| **`QA-W2-02`** | **High** | Manual Invoice Edit (`/accounting/invoice/{cn_id}/edit`) | Opening Draft Credit Note `550` cleared conditional `related_invoice_id` (`""`), allowing `$9,000` `UPDATE` (`200 OK`) against `$5,300` parent `544` unless manually re-selected (`422` enforced only when `related_invoice_id` selected). |
| **`QA-W3-02`** | **High** | Bank Reconciliation (`BankReconciliationController.php:45-53`) | When `bank_name` is missing/empty (not matched in `banks`), fallback queries `BankAccount::where('name', $bankName)->where('company_id', $companyId)` -> crashes `HTTP 500` (`SQLSTATE[42S22]: Unknown column 'company_id'` in `laravel.log` at `18:47:39` & `18:47:41`). |
| **`QA-W1-02`** | **Medium** | Ocean Import / Air Export / Air Import `/create` | Pre-filled `readonly` auto-gen `file_no` (`MOI-26110001`, `MAE-26110002`, `MAI-26110001`) collided with existing DB rows (`HTTP 422`), blocking normal UI save without unlocking `readonly`. Screenshot: `steps/333/media_0.png`. |
| **`QA-W1-03`** | **Medium** | Air Export `/air-export/create` | Standard `POST` with validation failure (`176-74910285`) returned `302` without visible error alert/toast and wiped entered `mawb_no`. |
| **`QA-W1-04`** | **Medium** | Air Export `/air-export` (`prepareForValidation()` + UI JS) | Server clamped MAWB `chargeable_weight` (`10.00 -> 583.340`), but nested `hbls[0][chargeable_weight]` remained `10.000`; UI uses `166.6667` (`583.33` / `333.33`) while backend uses `583.34` / `333.34`. |
| **`QA-W1-05`** | **Medium** | Ocean Export & Air Import `/edit` Charges Tab | After saving `Pay` (`AP`), `EUR`, `chrg_code`, and generating invoices, reloading Edit page causes Alpine `chargesList.map(...)` to revert displayed `P/R` (`Pay`->`Rec`), `Curr` (`EUR`->`USD`), `Code` (`""`), and `Inv No.` (`-`), though DB persisted accurately. Screenshot: `steps/496/media_0.png`. |
| **`QA-W1-01`** | **Medium** | Journal Report (`/accounting/report/journal-report`) | Fires `5` console errors on page load (`Uncaught TypeError: Cannot read properties of null (reading 'entry_date'/'office'/'status'/'description'/'lines')` + Alpine `x-if` `<template>` warning). |
| **`QA-W2-01`** | **Medium** | Invoice & G&A Create/Edit (`/accounting/invoice/create`, `/ga-expense/create`) | Console logs `Uncaught SyntaxError: Invalid left-hand side in assignment` from `x-model="memos[selectedMemo]?.content"` on Notes tab textarea. |
| **`QA-W2-04`** | **Medium** | Multi-Currency Invoice Print (`/accounting/invoice/{eur_id}/print`) | Correctly renders EUR bank (`Deutsche Bank EUR Account`), but hardcodes `"USD"` column headers & `"Currency: USD"` in payment instructions on `EUR` invoice `539`. |
| **`QA-W4-01`** | **Medium** | Main Invoice List (`/accounting/invoice?status=DRAFT`) | `InvoiceController::index()` (`lines 28-34`) restricts query to `Verified`, `Cancelled`, or `VOID`; since verified Drafts auto-promote to `POSTED`, selecting `?status=DRAFT` always returns `"No invoices found."` and hides Pending/Rejected Drafts (`549`, `555`) from the main list. |
| **`QA-W1-06`** | **Low** | Verification Queue (`/accounting/invoice/verify-queue`) | `verifyDocument()` & `rejectDocument()` dynamically write View links using plural `/accounting/invoices/{id}` instead of `/accounting/invoice/{id}`. |
| **`QA-W3-01`** | **Low / UX** | Receive Payment Unattached TDS (`/accounting/payment/receive`) | `FX-01` server logic accurately handles unattached PKR TDS (`$10.00` on `323`), but the unattached `tds_tax` input is `type="hidden"` in the UI. Also minor UX items: `checkNo` on Make Payment didn't reset until reload, `Apply` Block didn't auto-refresh table DOM until reload, and `revenue-cost/print` invokes auto `window.print()`. |

---

## Post-Fix Verification Run (`12 / 12` Defects Resolved & Live-Verified)

- **Verified At:** `2026-11-11T00:56:00+05:00`
- **Verification Layers Executed:** Live Chrome Browser (`http://localhost:8000`, `sardar@gmail.com`), Laravel Integration Verification, Blade View Compilation (`php artisan view:clear && php artisan view:cache`), and Unit Suite (`php artisan test tests/Unit` -> `62 passed, 1803 assertions`).
- **Overall Result:** **`12 / 12` Logged Defects Verified Fixed (`0` Console JS/Alpine Errors, `0` HTTP `500`s, `100%` Ledger Tie Preserved)**.

### Verified Remediation Summary

| Ticket | Verdict | Key Fix Implemented | Post-Fix Live Chrome Verification Evidence |
| :--- | :---: | :--- | :--- |
| **`QA-W2-02`** | **FIXED** | Added `related_invoice_id` column via migration `2026_11_11_000001_add_related_invoice_id_to_invoices_table.php` + [`Invoice`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Models/Invoice.php) fillable/relations; enforced CN cap inside transaction both pre-update & post-`syncInvoiceTotals()` in [`InvoiceController::update()`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/InvoiceController.php) (preserving saved `related_invoice_id` if omitted); fixed `<template x-for>` option `:selected` + `$refs.relatedInvoiceSelect.value` sync in [invoice-create.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/invoice-create.blade.php) & parent ref on [invoice-print.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/invoice-print.blade.php). | Temp Draft CN `559` (`$200`) linked to `544` (`$5,300`): initial `/accounting/invoice/559/edit` load showed `<select name="related_invoice_id">.value === "544"` (`"INV-000051-A ($5300.00)"`); UI `UPDATE` `$9,000` -> **HTTP `422`** block alert; direct `PUT` with `related_invoice_id: ""` & `$9,000` on `557` -> **HTTP `422`** & DB stayed `$200.00`. Temp drafts deleted (`404`). |
| **`QA-W3-02`** | **FIXED** | Guarded missing/unmatched `bank_name` in [`BankReconciliationController`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/Accounting/BankReconciliationController.php) and removed broken legacy `bank_accounts.company_id` query. | `/accounting/bank/reconciliation` loaded with `0` console errors; `GET /accounting/bank/reconciliation/export-excel?bank_name=` returned **HTTP `200`** (`641` bytes) and `Emirates NBD USD A/C` returned **HTTP `200`** (`795` bytes); zero new SQL errors in `storage/logs/laravel.log`. |
| **`QA-W1-02`** | **FIXED** | Switched [`DocumentSequenceService::generateBookingNo()`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Services/DocumentSequenceService.php) to `withoutGlobalScopes()->withTrashed()` so generated `file_no` skips other companies' & soft-deleted numbers globally. | Live `/create` pre-filled `file_no`s: `/ocean-import/create` -> `MOI-26110002`, `/air-import/create` -> `MAI-26110002`, `/air-export/create` -> `MAE-26110003` (zero collisions on save). |
| **`QA-W1-06`** | **FIXED** | Updated `verifyDocument()` & `rejectDocument()` in [verify.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/verify.blade.php) from plural `/accounting/invoices/{id}` to singular `/accounting/invoice/{id}` and fixed [navbar.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/components/navbar.blade.php) create href. | Navbar links verified as `/accounting/invoice/create`; rejecting temp `$111` Draft `558` updated `#actions-558` View href to `/accounting/invoice/558` (singular) -> click opened **`200 OK`** (`verifyDocument` JS also uses `/accounting/invoice/${id}`). Deleted `558` (`404`). |
| **`QA-W1-04`** | **FIXED** | Added shared [`AirTactWeight`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Support/AirTactWeight.php) (`0.5 kg` ceiling helper), applied across `Store`/`Update` Air Export & Air Import requests including nested `hbls[*][chargeable_weight]`, and aligned UI JS `roundUpHalfKg` in Air Export & Air Import views. | `calcRowVolKg` on `3.50 CBM` computed `583.5` kg; saving `/air-export` `68` with tampered MAWB & `hbls[0][chargeable_weight]` `= 10.00` clamped BOTH server-side to **`583.500`** on reload. Deleted `68` (`404`). |
| **`QA-W4-01`** | **FIXED** | Removed blanket Verified/VOID filter in [`InvoiceController::index()`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/InvoiceController.php) while keeping KPI summary `$totalsQuery` Verified-only unless `?status=DRAFT` is filtered; added `DRAFT (PENDING)` / `DRAFT (REJECTED)` badges in [invoice-list.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/invoice-list.blade.php). | Both `/accounting/invoice?limit=100` (`76` rows) and `/accounting/invoice?status=DRAFT` (`1 of 1`) listed rejected Draft `549` (`INV-000056-A`, `DRAFT (REJECTED)`), plus temporary `DRAFT (PENDING)` CN `557` prior to UI Delete (`404`). |
| **`QA-W2-04`** | **FIXED** | Dynamically passed `$currencyCode` in [`InvoiceController::printDocument()`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/InvoiceController.php) to invoice print headers, subtotal, grand total, and payment instructions. | `/accounting/invoice/539/print` rendered `Currency: EUR`, `RATE (EUR)`, `AMOUNT (EUR)`, `Subtotal (EUR)`, `GRAND TOTAL (EUR)` with `0` `RATE (USD)`/`Subtotal (USD)`/`Currency: USD` labels. |
| **`QA-W1-03`** | **FIXED** | Hydrated `old()` fields & `hawbs` and added visible `.alert-danger` validation/session banners on [air-export/create.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/air-export/create.blade.php). | Standard `POST /air-export` with invalid MAWB `176-74910285` redirected with visible red `.alert-danger` banner (`expected 6, got 5`) AND preserved `mawb_no = "176-74910285"`. |
| **`QA-W1-05`** | **FIXED** | Fixed string/object `$c->currency` handling in [`OceanExportController::edit()`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/OceanExportController.php) & [`AirImportController::edit()`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/AirImportController.php) and updated JS charges reload mappers across OE/AI/OI views. | `/ocean-export/133/edit` (`815 BAF EUR Rec INV-000050-B`, `817 OFX USD Pay INV-000050-C`, `818 THC USD Pay CN-000003-A`) and `/air-import/27/edit` (`822 AGT USD Pay CN-000002-A`) rendered persisted values accurately. |
| **`QA-W3-01`** | **FIXED** | Exposed editable `Advance TDS Tax` (`unattachedTds`) when unattached + wired bank currency watchers in [payment-receive.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/payment-receive.blade.php); added `checkNo` reset & bank watch in [payment-make.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/payment-make.blade.php); live table DOM refresh in [accounting-block.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/accounting-block.blade.php) & [`AccountingBlockController`](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/app/Http/Controllers/Accounting/AccountingBlockController.php). | Receive Payment rendered editable `Advance TDS Tax` when unattached (`0` console errors); bank selection updated `bankCurrencyCode` on both pages; Block/Unblock `KHI 2026-10-31` updated `#blockRecordsBody` DOM immediately without page reload (`403` confirmed during block, then unblocked). |
| **`QA-W1-01`** | **FIXED** | Wrapped modal content inside `<template x-if="selectedEntry">` with optional chaining on [journal-report.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/journal-report.blade.php). | `/accounting/report/journal-report` load & `JE-20261110-0001` modal inspect passed with **`0` console errors / `0` Alpine warnings**. |
| **`QA-W2-01`** | **FIXED** | Replaced optional-chained `x-model` in Notes tab across [invoice-create.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/invoice-create.blade.php) & [create-ga-expense.blade.php](file:///home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/accounting/create-ga-expense.blade.php) (`syncTotalFromLines()` auto-sum also added on G&A Create). | `/accounting/invoice/create`, `/accounting/invoice/550/edit`, and `/accounting/ga-expense/create` loaded with **`0` `SyntaxError`s**; entering G&A line `qty=3, rate=250` live-updated `total_amount` to `750.00`. |

### Post-Fix Reconciled Baseline Confirmation (`company_id = 1`, `As of 2026-12-31`, Clean DB State)

- **Balance Sheet:** Cash `211,126.66`, AR `975,929.78`, AP `66,781,910.69`, Cumulative Net Income `-65,843,686.01`, Equation Variance `35,475.15` (`Variance Delta == $0.00`).
- **Income Statement:** Revenue `986,729.78`, Expenses `66,830,415.79`, Net Income `-65,843,686.01` (`RECONCILED`).
- **Trial Balance:** Period `Dr == Cr = 67,845,130.72` (`Balanced`, `18` accounts).
- **IDOR Security Probes:** `/accounting/invoice/256` -> `404`; `/accounting/journal/8` -> `404`.
- **Temp QA Artifacts Cleaned:** Invoices `557`, `558`, `559` -> `404`; Shipment `ae68` -> `404`; `KHI 2026-10-31` -> `UNBLOCKED`.
