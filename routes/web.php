<?php

use App\Http\Controllers\ClientController;

// Auto-restore silk-container-page-1.png if corrupted
if (file_exists(public_path('assets/images/hbl/silk-1.png'))) {
    $src = public_path('assets/images/hbl/silk-1.png');
    $dest = public_path('hbl-backgrounds/silk-container-page-1.png');
    if (!file_exists($dest) || filesize($dest) !== filesize($src)) {
        @copy($src, $dest);
        @copy($src, public_path('hbl-backgrounds/8.png'));
        @copy($src, public_path('hbl-backgrounds/j.png'));
    }
}

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\TradePartnerController;
use App\Http\Controllers\TradePartnerCreditController;
use App\Http\Controllers\TradePartnerMappingController;
use App\Http\Controllers\OceanImportController;
use App\Http\Controllers\OceanExportController;
use App\Http\Controllers\AirImportController;
use App\Http\Controllers\AirExportController;
use App\Http\Controllers\TruckShipmentController;
use App\Http\Controllers\WarehouseReceiptController;
use App\Http\Controllers\WarehouseReceivingController;
use App\Http\Controllers\WarehouseShippingController;
use App\Http\Controllers\WarehouseInventoryItemController;
use App\Http\Controllers\WarehouseAutomobileController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AccountingPaymentController;
use App\Http\Controllers\Accounting\BankBookBalanceController;
use App\Http\Controllers\Accounting\BankOutstandingController;
use App\Http\Controllers\Accounting\BankReconciliationController;
use App\Http\Controllers\Accounting\BankBatchProcessController;
use App\Http\Controllers\Accounting\ClearCheckByExcelController;
use App\Http\Controllers\Accounting\CheckDepositReportController;
use App\Http\Controllers\Accounting\BalanceSheetController;
use App\Http\Controllers\Accounting\TrialBalanceController;
use App\Http\Controllers\Accounting\GeneralLedgerController;
use App\Http\Controllers\Accounting\AgingReportController;
use App\Http\Controllers\Accounting\IncomeStatementController;
use App\Http\Controllers\Accounting\RevenueCostController;
use App\Http\Controllers\Accounting\AgentLocalStatementController;
use App\Http\Controllers\Accounting\FreightStatementController;
use App\Http\Controllers\Accounting\OneZeroNineNineController;
use App\Http\Controllers\Accounting\JournalReportController;
use App\Http\Controllers\Accounting\JournalEntryController;
use App\Http\Controllers\Accounting\GeneralJournalController;
use App\Http\Controllers\Accounting\AccountingBlockController;
use App\Http\Controllers\Accounting\AccountingBlockMaintenanceController;
use App\Http\Controllers\Accounting\AccountingBlockHistoryController;
use App\Http\Controllers\Accounting\YearEndClosingController;
use App\Http\Controllers\VesselScheduleController;
use App\Http\Controllers\OceanBookingController;
use App\Http\Controllers\AirBookingController;
use App\Http\Controllers\ClientReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GaExpenseController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\GlobalSearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerQuoteController;
use App\Http\Controllers\CustomerInvoiceController;
use App\Http\Controllers\SuperAdmin\CompanyController as SuperAdminCompanyController;

// Views routing - purely rendering blade templates.
// All data fetching must be done on the client side via the /api routes.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/frex/process', [\App\Http\Controllers\FrexAssistantController::class, 'process'])->name('frex.process');
    Route::get('/api/global-search', [GlobalSearchController::class, 'search'])->name('api.global-search');
    Route::post('/api/documents/email', [\App\Http\Controllers\DocumentEmailController::class, 'sendEmail'])->name('documents.email');
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');

    // Data Migration
    Route::prefix('data-migration')->name('data-migration.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DataMigrationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\DataMigrationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\DataMigrationController::class, 'store'])->name('store');
        Route::get('/{project}/scan', [\App\Http\Controllers\DataMigrationController::class, 'scan'])->name('scan');
        Route::post('/{project}/execute-scan', [\App\Http\Controllers\DataMigrationController::class, 'executeScan'])->name('execute-scan');
    });
    // Support Tickets (Tenant)
    Route::get('/support/tickets', [\App\Http\Controllers\Tenant\TicketController::class, 'index'])->name('support.tickets.index');
    Route::post('/support/tickets', [\App\Http\Controllers\Tenant\TicketController::class, 'store'])->name('support.tickets.store');
    Route::get('/support/tickets/{ticket}', [\App\Http\Controllers\Tenant\TicketController::class, 'show'])->name('support.tickets.show');
    Route::post('/support/tickets/{ticket}/message', [\App\Http\Controllers\Tenant\TicketController::class, 'message'])->name('support.tickets.message');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/email-settings', [ProfileController::class, 'updateEmailSettings'])->name('profile.email-settings');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ocean Operations
    // AI Document Extraction Routes
    Route::post('/api/ai/extract/ocean-import', [\App\Http\Controllers\AiExtractionController::class, 'extractOceanImport'])->name('ai.extract.ocean-import');
    Route::post('/api/ai/extract/ocean-export', [\App\Http\Controllers\AiExtractionController::class, 'extractOceanExport'])->name('ai.extract.ocean-export');
    Route::post('/api/ai/extract/air-import', [\App\Http\Controllers\AiExtractionController::class, 'extractAirImport'])->name('ai.extract.air-import');
    Route::post('/api/ai/extract/air-export', [\App\Http\Controllers\AiExtractionController::class, 'extractAirExport'])->name('ai.extract.air-export');

    Route::get('/ocean-import/list', [OceanImportController::class, 'index'])->name('ocean-import.index');
    Route::get('/ocean-import/create', [OceanImportController::class, 'create'])->name('ocean-import.create');
    Route::get('/ocean-import/create-quote', [OceanImportController::class, 'create'])->name('ocean-import.create-quote');
    Route::get('/ocean-import/export-csv', [OceanImportController::class, 'exportCsv'])->name('ocean-import.export-csv');
    Route::match(['GET', 'POST', 'DELETE'], '/ocean-import/bulk-delete', [OceanImportController::class, 'bulkDelete'])->name('ocean-import.bulk-delete');
    Route::match(['GET', 'POST', 'DELETE'], '/ocean-import/bulk-block', [OceanImportController::class, 'bulkBlock'])->name('ocean-import.bulk-block');
    Route::match(['GET', 'POST', 'DELETE'], '/ocean-import/bulk-unblock', [OceanImportController::class, 'bulkUnblock'])->name('ocean-import.bulk-unblock');
    Route::post('/ocean-import/{ocean_import}/copy', [OceanImportController::class, 'copyShipment'])->name('ocean-import.copy');
    Route::post('/ocean-import/bulk-change-op', [OceanImportController::class, 'bulkChangeOp'])->name('ocean-import.bulk-change-op');
    Route::post('/ocean-import/bulk-change-sales', [OceanImportController::class, 'bulkChangeSales'])->name('ocean-import.bulk-change-sales');
    Route::post('/ocean-import', [OceanImportController::class, 'store'])->name('ocean-import.store');
    Route::get('/ocean-import/{ocean_import}/edit', [OceanImportController::class, 'edit'])->name('ocean-import.edit');
    Route::put('/ocean-import/{ocean_import}', [OceanImportController::class, 'update'])->name('ocean-import.update');
    Route::delete('/ocean-import/{ocean_import}', [OceanImportController::class, 'destroy'])->name('ocean-import.destroy');
    Route::patch('/ocean-import/{ocean_import}/color', [OceanImportController::class, 'updateColor'])->name('ocean-import.update-color');

    // Ocean Import Sub-Routes
    // Containers
    Route::post('/ocean-import/{ocean_import}/containers/import', [OceanImportController::class, 'importContainers'])->name('ocean-import.containers.import');
    Route::post('/ocean-import/containers/import-temp', [OceanImportController::class, 'importContainers'])->name('ocean-import.containers.import-temp');
    Route::post('/ocean-import/containers/{container}/duplicate', [OceanImportController::class, 'duplicateContainer'])->name('ocean-import.containers.duplicate');
    Route::post('/ocean-import/containers/{container}/remarks', [OceanImportController::class, 'updateRemarks'])->name('ocean-import.containers.remarks');
    Route::delete('/ocean-import/containers/{container}', [OceanImportController::class, 'destroyContainer'])->name('ocean-import.containers.destroy');
    Route::post('/ocean-import/containers/batch-update', [OceanImportController::class, 'batchUpdateContainers'])->name('ocean-import.containers.batch-update');
    Route::post('/ocean-import/containers/batch-update-inline', [OceanImportController::class, 'batchUpdateInline'])->name('ocean-import.containers.batch-update-inline');
    Route::get('/ocean-import/{ocean_import}/containers/export', [OceanImportController::class, 'exportContainerList'])->name('ocean-import.containers.export');
    Route::get('/ocean-import/containers-export-csv', [OceanImportController::class, 'exportContainersCsv'])->name('ocean-import.containers-export-csv');

    // Charges
    Route::post('/ocean-import/{ocean_import}/charges/duplicate', [OceanImportController::class, 'duplicateCharges'])->name('ocean-import.charges.duplicate');
    Route::post('/ocean-import/{ocean_import}/charges/bulk-currency', [OceanImportController::class, 'bulkUpdateCurrency'])->name('ocean-import.charges.bulk-currency');
    Route::post('/ocean-import/{ocean_import}/charges/apply-vat', [OceanImportController::class, 'applyVatToAll'])->name('ocean-import.charges.apply-vat');
    Route::post('/ocean-import/{ocean_import}/charges/prorata', [OceanImportController::class, 'prorataCharges'])->name('ocean-import.charges.prorata');
    Route::post('/ocean-import/{ocean_import}/charges/template', [OceanImportController::class, 'applyChargeTemplate'])->name('ocean-import.charges.template');
    Route::post('/ocean-import/{ocean_import}/charges/copy-quote', [OceanImportController::class, 'copyChargesFromQuote'])->name('ocean-import.charges.copy-quote');
    Route::get('/ocean-import/{ocean_import}/charges/export', [OceanImportController::class, 'exportChargesToExcel'])->name('ocean-import.charges.export');
    Route::get('/ocean-import/{ocean_import}/charges/print', [OceanImportController::class, 'printCharges'])->name('ocean-import.charges.print');
    Route::post('/ocean-import/{ocean_import}/charges/invoice', [OceanImportController::class, 'createInvoiceFromCharges'])->name('ocean-import.charges.invoice');
    Route::delete('/ocean-import/{ocean_import}/charges/all', [OceanImportController::class, 'deleteAllCharges'])->name('ocean-import.charges.destroy-all');

    // Documents
    Route::post('/ocean-import/{ocean_import}/documents', [OceanImportController::class, 'uploadDocument'])->name('ocean-import.documents.store');
    Route::post('/ocean-import/documents/store-temp', [OceanImportController::class, 'uploadDocumentTemp'])->name('ocean-import.documents.store-temp');
    Route::delete('/ocean-import/documents/{document}', [OceanImportController::class, 'deleteDocument'])->name('ocean-import.documents.destroy');
    Route::get('/ocean-import/documents/{document}/download', [OceanImportController::class, 'downloadDocument'])->name('ocean-import.documents.download');

    // Memos
    Route::post('/ocean-import/{ocean_import}/memos', [OceanImportController::class, 'addMemo'])->name('ocean-import.memos.store');
    Route::put('/ocean-import/memos/{memo}', [OceanImportController::class, 'updateMemo'])->name('ocean-import.memos.update');
    Route::delete('/ocean-import/memos/{memo}', [OceanImportController::class, 'deleteMemo'])->name('ocean-import.memos.destroy');

    // Filing
    Route::put('/ocean-import/{ocean_import}/filing', [OceanImportController::class, 'updateFiling'])->name('ocean-import.filing.update');

    // Quote Loading
    Route::get('/ocean-import/quotes/search', [OceanImportController::class, 'searchQuotations'])->name('ocean-import.quotes.search');
    Route::get('/ocean-import/warehouse-receipts/search', [OceanImportController::class, 'searchWarehouseReceipts'])->name('ocean-import.warehouse-receipts.search');
    Route::post('/ocean-import/{ocean_import}/quotes/load', [OceanImportController::class, 'loadQuoteToShipment'])->name('ocean-import.quotes.load');

    // History
    Route::get('/ocean-import/{ocean_import}/history', [OceanImportController::class, 'getShipmentHistory'])->name('ocean-import.history');

    // Export PDF
    Route::get('/ocean-import/{ocean_import}/export-pdf', [OceanImportController::class, 'exportShipmentPdf'])->name('ocean-import.export-pdf');


    // MBL/HBL Specific Lists
    Route::get('/ocean-import/list/mbl', [OceanImportController::class, 'mblList'])->name('ocean-import.mbl-list');
    Route::get('/ocean-import/list/hbl', [OceanImportController::class, 'hblList'])->name('ocean-import.hbl-list');
    Route::get('/ocean-import/list/containers', [OceanImportController::class, 'containerList'])->name('ocean-import.container-list');

    // Alias for /ocean/import pattern
    Route::get('/ocean/import/shipment', [OceanImportController::class, 'create']);
    Route::get('/ocean/import/list', [OceanImportController::class, 'index']);

    Route::get('/ocean-export/list', [OceanExportController::class, 'index'])->name('ocean-export.index');
    Route::get('/ocean-export/create', [OceanExportController::class, 'create'])->name('ocean-export.create');
    Route::get('/ocean-export/create-quote', [OceanExportController::class, 'create'])->name('ocean-export.create-quote');
    Route::get('/ocean-export/export-csv', [OceanExportController::class, 'exportCsv'])->name('ocean-export.export-csv');
    Route::match(['GET', 'POST', 'DELETE'], '/ocean-export/bulk-delete', [OceanExportController::class, 'bulkDelete'])->name('ocean-export.bulk-delete');
    Route::match(['GET', 'POST', 'DELETE'], '/ocean-export/bulk-block', [OceanExportController::class, 'bulkBlock'])->name('ocean-export.bulk-block');
    Route::match(['GET', 'POST', 'DELETE'], '/ocean-export/bulk-unblock', [OceanExportController::class, 'bulkUnblock'])->name('ocean-export.bulk-unblock');
    Route::post('/ocean-export/bulk-change-op', [OceanExportController::class, 'bulkChangeOp'])->name('ocean-export.bulk-change-op');
    Route::post('/ocean-export/bulk-change-sales', [OceanExportController::class, 'bulkChangeSales'])->name('ocean-export.bulk-change-sales');
    Route::post('/ocean-export', [OceanExportController::class, 'store'])->name('ocean-export.store');
    Route::get('/ocean-export/{ocean_export}/edit', [OceanExportController::class, 'edit'])->name('ocean-export.edit');
    Route::put('/ocean-export/{ocean_export}', [OceanExportController::class, 'update'])->name('ocean-export.update');
    Route::delete('/ocean-export/{ocean_export}', [OceanExportController::class, 'destroy'])->name('ocean-export.destroy');
    Route::patch('/ocean-export/{ocean_export}/color', [OceanExportController::class, 'updateColor'])->name('ocean-export.update-color');
    Route::get('/ocean-export/list/mbl', [OceanExportController::class, 'mblList'])->name('ocean-export.mbl-list');
    Route::get('/ocean-export/list/hbl', [OceanExportController::class, 'hblList'])->name('ocean-export.hbl-list');
    Route::post('/ocean-export/hbl-bulk-delete', [OceanExportController::class, 'hblBulkDelete'])->name('ocean-export.hbl-bulk-delete');
    Route::post('/ocean-export/hbl-bulk-block', [OceanExportController::class, 'hblBulkBlock'])->name('ocean-export.hbl-bulk-block');
    Route::post('/ocean-export/hbl-bulk-unblock', [OceanExportController::class, 'hblBulkUnblock'])->name('ocean-export.hbl-bulk-unblock');
    Route::post('/ocean-export/{ocean_export}/charges/duplicate', [OceanExportController::class, 'duplicateCharges'])->name('ocean-export.charges.duplicate');
    Route::post('/ocean-export/{ocean_export}/charges/bulk-currency', [OceanExportController::class, 'bulkUpdateCurrency'])->name('ocean-export.charges.bulk-currency');
    Route::post('/ocean-export/{ocean_export}/charges/apply-vat', [OceanExportController::class, 'applyVatToAll'])->name('ocean-export.charges.apply-vat');
    Route::post('/ocean-export/{ocean_export}/charges/prorata', [OceanExportController::class, 'prorataCharges'])->name('ocean-export.charges.prorata');
    Route::post('/ocean-export/{ocean_export}/charges/template', [OceanExportController::class, 'applyChargeTemplate'])->name('ocean-export.charges.template');
    Route::post('/ocean-export/{ocean_export}/charges/copy-quote', [OceanExportController::class, 'copyChargesFromQuote'])->name('ocean-export.charges.copy-quote');
    Route::get('/ocean-export/{ocean_export}/charges/export', [OceanExportController::class, 'exportChargesToExcel'])->name('ocean-export.charges.export');
    Route::get('/ocean-export/{ocean_export}/charges/print', [OceanExportController::class, 'printCharges'])->name('ocean-export.charges.print');
    Route::post('/ocean-export/{ocean_export}/charges/invoice', [OceanExportController::class, 'createInvoiceFromCharges'])->name('ocean-export.charges.invoice');
    Route::delete('/ocean-export/{ocean_export}/charges/all', [OceanExportController::class, 'deleteAllCharges'])->name('ocean-export.charges.destroy-all');

    // Tools Dropdown Subviews
    Route::get('/ocean-export/{id}/profit-summary', [OceanExportController::class, 'profitSummaryView'])->name('ocean-export.profit-summary');
    Route::get('/ocean-export/{id}/profit-detail', [OceanExportController::class, 'profitDetailView'])->name('ocean-export.profit-detail');
    Route::get('/ocean-export/{id}/delivery-order', [OceanExportController::class, 'deliveryOrderView'])->name('ocean-export.delivery-order');
    Route::get('/ocean-export/{id}/batch-print-view', [OceanExportController::class, 'batchPrintView'])->name('ocean-export.batch-print-view');
    Route::get('/ocean-export/{id}/hbl-print/{hblIndex}', [OceanExportController::class, 'hblPrint'])->name('ocean-export.hbl-print');


    // Alias for /ocean/export pattern
    Route::get('/ocean/export/shipment', [OceanExportController::class, 'create']);
    Route::get('/ocean/export/list', [OceanExportController::class, 'index']);


    Route::get('/ocean-export/create-quote-booking', [OceanExportController::class, 'createQuoteBooking'])->name('ocean-export.create-quote-booking');

    Route::get('/ocean-export/booking/list', [OceanBookingController::class, 'index'])->name('ocean-bookings.index');
    Route::get('/ocean-export/booking/create', [OceanBookingController::class, 'create'])->name('ocean-bookings.create');
    Route::post('/ocean-export/booking', [OceanBookingController::class, 'store'])->name('ocean-bookings.store');
    Route::get('/ocean-export/booking/{id}/edit', [OceanBookingController::class, 'edit'])->name('ocean-bookings.edit');
    Route::put('/ocean-export/booking/{id}', [OceanBookingController::class, 'update'])->name('ocean-bookings.update');
    Route::delete('/ocean-export/booking/{id}', [OceanBookingController::class, 'destroy'])->name('ocean-bookings.destroy');
    Route::patch('/ocean-export/booking/{id}/color', [OceanBookingController::class, 'updateColor'])->name('ocean-bookings.color');
    Route::post('/ocean-export/bookings/bulk-delete', [OceanBookingController::class, 'bulkDelete'])->name('ocean-bookings.bulk-delete');
    Route::post('/ocean-export/bookings/bulk-change-sales', [OceanBookingController::class, 'bulkChangeSales'])->name('ocean-bookings.bulk-change-sales');
    Route::post('/ocean-export/bookings/bulk-change-op', [OceanBookingController::class, 'bulkChangeOp'])->name('ocean-bookings.bulk-change-op');
    Route::post('/ocean-export/bookings/convert-to-shipment', [OceanBookingController::class, 'convertToShipment'])->name('ocean-bookings.convert-to-shipment');
    Route::get('/ocean-export/bookings/export-csv', [OceanBookingController::class, 'exportCsv'])->name('ocean-bookings.export-csv');

    Route::get('/ocean-export/work-order/create', [WorkOrderController::class, 'create'])->name('ocean-export.work-order.create');
    Route::post('/ocean-export/work-order', [WorkOrderController::class, 'store'])->name('ocean-export.work-order.store');
    Route::get('/ocean-export/work-order/{id}/edit', [WorkOrderController::class, 'edit'])->name('ocean-export.work-order.edit');
    Route::put('/ocean-export/work-order/{id}', [WorkOrderController::class, 'update'])->name('ocean-export.work-order.update');
    Route::get('/ocean-export/work-order/{id}/export-excel', [WorkOrderController::class, 'exportExcel'])->name('ocean-export.work-order.export-excel');
    Route::get('/shipments/{type}/{id}/freight-invoice', [InvoiceController::class, 'generateFreightInvoice'])->name('shipments.freight-invoice');

    Route::get('/ocean-export/vessel-schedule/list', [VesselScheduleController::class, 'index'])->name('vessel-schedules.index');
    Route::get('/ocean-export/vessel-schedule/create', [VesselScheduleController::class, 'create'])->name('vessel-schedules.create');
    Route::post('/ocean-export/vessel-schedule', [VesselScheduleController::class, 'store'])->name('vessel-schedules.store');
    Route::get('/ocean-export/vessel-schedule/{schedule}/edit', [VesselScheduleController::class, 'edit'])->name('vessel-schedules.edit');
    Route::put('/ocean-export/vessel-schedule/{schedule}', [VesselScheduleController::class, 'update'])->name('vessel-schedules.update');
    Route::patch('/ocean-export/vessel-schedule/{schedule}/color', [VesselScheduleController::class, 'updateColor'])->name('vessel-schedules.update-color');
    Route::delete('/ocean-export/vessel-schedule/{schedule}', [VesselScheduleController::class, 'destroy'])->name('vessel-schedules.destroy');
    Route::post('/ocean-export/vessel-schedule/bulk-delete', [VesselScheduleController::class, 'bulkDelete'])->name('vessel-schedules.bulk-delete');
    Route::get('/ocean-export/vessel-schedule/export-csv', [VesselScheduleController::class, 'exportCsv'])->name('vessel-schedules.export-csv');

    // Vessel Schedule - Charges
    Route::get('/ocean-export/vessel-schedule/{schedule}/charges', [VesselScheduleController::class, 'listCharges'])->name('vessel-schedules.charges.list');
    Route::post('/ocean-export/vessel-schedule/{schedule}/charges', [VesselScheduleController::class, 'storeCharge'])->name('vessel-schedules.charges.store');
    Route::put('/ocean-export/vessel-schedule/charges/{charge}', [VesselScheduleController::class, 'updateCharge'])->name('vessel-schedules.charges.update');
    Route::delete('/ocean-export/vessel-schedule/charges/{charge}', [VesselScheduleController::class, 'destroyCharge'])->name('vessel-schedules.charges.destroy');
    Route::delete('/ocean-export/vessel-schedule/{schedule}/charges/all', [VesselScheduleController::class, 'deleteAllCharges'])->name('vessel-schedules.charges.destroy-all');
    Route::post('/ocean-export/vessel-schedule/{schedule}/charges/duplicate', [VesselScheduleController::class, 'duplicateCharges'])->name('vessel-schedules.charges.duplicate');
    Route::post('/ocean-export/vessel-schedule/{schedule}/charges/bulk-currency', [VesselScheduleController::class, 'bulkUpdateCurrency'])->name('vessel-schedules.charges.bulk-currency');
    Route::post('/ocean-export/vessel-schedule/{schedule}/charges/apply-vat', [VesselScheduleController::class, 'applyVatToAll'])->name('vessel-schedules.charges.apply-vat');
    Route::post('/ocean-export/vessel-schedule/{schedule}/charges/template', [VesselScheduleController::class, 'applyChargeTemplate'])->name('vessel-schedules.charges.template');
    Route::post('/ocean-export/vessel-schedule/{schedule}/charges/invoice', [VesselScheduleController::class, 'createInvoiceFromCharges'])->name('vessel-schedules.charges.invoice');
    Route::get('/ocean-export/vessel-schedule/{schedule}/charges/export', [VesselScheduleController::class, 'exportChargesToExcel'])->name('vessel-schedules.charges.export');
    Route::get('/ocean-export/vessel-schedule/{schedule}/charges/print', [VesselScheduleController::class, 'printCharges'])->name('vessel-schedules.charges.print');

    // Vessel Schedule - Documents
    Route::post('/ocean-export/vessel-schedule/{schedule}/documents', [VesselScheduleController::class, 'uploadDocument'])->name('vessel-schedules.documents.store');
    Route::delete('/ocean-export/vessel-schedule/documents/{document}', [VesselScheduleController::class, 'deleteDocument'])->name('vessel-schedules.documents.destroy');
    Route::get('/ocean-export/vessel-schedule/documents/{document}/download', [VesselScheduleController::class, 'downloadDocument'])->name('vessel-schedules.documents.download');

    // Vessel Schedule - Status
    Route::get('/ocean-export/vessel-schedule/{schedule}/status', [VesselScheduleController::class, 'getStatusLogs'])->name('vessel-schedules.status.list');
    Route::post('/ocean-export/vessel-schedule/{schedule}/status', [VesselScheduleController::class, 'saveStatus'])->name('vessel-schedules.status.save');

    Route::get('/ocean-export/{page?}/{subpage?}/{action?}', function ($page = 'list', $subpage = null) {
        if($page === 'list') {
            if ($subpage === 'mbl') return redirect()->route('ocean-export.mbl-list');
            if ($subpage === 'hbl') return redirect()->route('ocean-export.hbl-list');
            return redirect()->route('ocean-export.index');
        }
        if(in_array($page, ['create', 'create-quote']) && $subpage === null) {
            return view('ocean-export.index', ['page' => $page]);
        }

        $title = 'Ocean Export';
        $api = '/api/ocean-exports';

        if ($page === 'booking') { $api = '/api/bookings'; $title .= ' Booking'; }
        elseif ($page === 'schedule') { $api = '/api/schedules'; $title .= ' Schedule'; }
        elseif ($page === 'mbl') { $api = '/api/ocean-exports'; $title .= ' MBL'; }
        elseif ($page === 'hbl') { $api = '/api/house-bills-of-lading'; $title .= ' HBL'; }

        return view('generic.index', [
            'title' => $title . ($subpage ? ' - ' . ucfirst($subpage) : ''),
            'api_endpoint' => $api
        ]);
    });

    // Alias for /ocean/export pattern
    Route::get('/ocean/export/{page?}/{subpage?}', function ($page = 'list', $subpage = null) {
        if($page === 'list') {
            if ($subpage === 'mbl') return redirect()->route('ocean-export.mbl-list');
            if ($subpage === 'hbl') return redirect()->route('ocean-export.hbl-list');
            return redirect()->route('ocean-export.index');
        }
        if(in_array($page, ['shipment', 'create-quote', 'booking'])) {
            return view('ocean-export.' . ($page === 'booking' ? 'booking' : 'index'), ['page' => $page]);
        }
        return redirect("/ocean-export/$page/$subpage");
    });

    // Air Operations
    Route::redirect('/air-import/hbl-list', '/air-import/list/hbl');
    Route::redirect('/air-export/hbl-list', '/air-export/list/hbl');
    Route::get('/air-import/list', [AirImportController::class, 'index'])->name('air-import.index');
    Route::get('/air-import/create', [AirImportController::class, 'create'])->name('air-import.create');
    Route::get('/air-import/create-quote', [AirImportController::class, 'create'])->name('air-import.create-quote');
    Route::post('/air-import', [AirImportController::class, 'store'])->name('air-import.store');
    Route::get('/air-import/{air_import}/edit', [AirImportController::class, 'edit'])->name('air-import.edit');
    Route::put('/air-import/{air_import}', [AirImportController::class, 'update'])->name('air-import.update');
    Route::delete('/air-import/{air_import}', [AirImportController::class, 'destroy'])->name('air-import.destroy');
    Route::get('/air-import/list/mbl', [AirImportController::class, 'mblList'])->name('air-import.mbl-list');
    Route::get('/air-import/list/hbl', [AirImportController::class, 'hblList'])->name('air-import.hbl-list');
    Route::get('/air-import/my-shipment-list', [AirImportController::class, 'myShipmentList'])->name('air-import.my-shipment-list');
    Route::match(['GET','POST','DELETE'], '/air-import/bulk-block', [AirImportController::class, 'bulkBlock'])->name('air-import.bulk-block');
    Route::match(['GET','POST','DELETE'], '/air-import/bulk-unblock', [AirImportController::class, 'bulkUnblock'])->name('air-import.bulk-unblock');
    Route::match(['GET','POST','DELETE'], '/air-import/bulk-delete', [AirImportController::class, 'bulkDelete'])->name('air-import.bulk-delete');
    Route::post('/air-import/bulk-change-op', [AirImportController::class, 'bulkChangeOp'])->name('air-import.bulk-change-op');
    Route::post('/air-import/bulk-change-sales', [AirImportController::class, 'bulkChangeSales'])->name('air-import.bulk-change-sales');
    Route::post('/air-import/hbl-bulk-delete', [AirImportController::class, 'hblBulkDelete'])->name('air-import.hbl-bulk-delete');
    Route::post('/air-import/hbl-bulk-block', [AirImportController::class, 'hblBulkBlock'])->name('air-import.hbl-bulk-block');
    Route::post('/air-import/hbl-bulk-unblock', [AirImportController::class, 'hblBulkUnblock'])->name('air-import.hbl-bulk-unblock');
    Route::post('/air-import/hbl-bulk-change-op', [AirImportController::class, 'hblBulkChangeOp'])->name('air-import.hbl-bulk-change-op');
    Route::post('/air-import/hbl-bulk-change-sales', [AirImportController::class, 'hblBulkChangeSales'])->name('air-import.hbl-bulk-change-sales');
    // Air Import Sub-Routes (Charges, Containers, Filing, Documents, History)
    Route::post('/air-import/{air_import}/charges', [AirImportController::class, 'addCharge'])->name('air-import.charges.store');
    Route::put('/air-import/charges/{charge}', [AirImportController::class, 'updateCharge'])->name('air-import.charges.update');
    Route::delete('/air-import/charges/{charge}', [AirImportController::class, 'deleteCharge'])->name('air-import.charges.destroy');
    Route::delete('/air-import/{air_import}/charges/all', [AirImportController::class, 'deleteAllCharges'])->name('air-import.charges.destroy-all');
    Route::get('/air-import/{air_import}/charges', [AirImportController::class, 'getCharges'])->name('air-import.charges.index');
    Route::post('/air-import/{air_import}/charges/invoice', [AirImportController::class, 'createInvoiceFromCharges'])->name('air-import.charges.invoice');

    Route::post('/air-import/{air_import}/containers', [AirImportController::class, 'addContainer'])->name('air-import.containers.store');
    Route::put('/air-import/containers/{container}', [AirImportController::class, 'updateContainer'])->name('air-import.containers.update');
    Route::delete('/air-import/containers/{container}', [AirImportController::class, 'deleteContainer'])->name('air-import.containers.destroy');

    Route::put('/air-import/{air_import}/filing', [AirImportController::class, 'updateFiling'])->name('air-import.filing.update');
    Route::patch('/air-import/{air_import}/color', [AirImportController::class, 'updateColor'])->name('air-import.update-color');
    Route::get('/air-import/export-csv', [AirImportController::class, 'exportCsv'])->name('air-import.export-csv');
    Route::get('/air-import/hbl-export-csv', [AirImportController::class, 'hblExportCsv'])->name('air-import.hbl-export-csv');

    Route::get('/air-import/{id}/profit-summary', [AirImportController::class, 'profitSummaryView'])->name('air-import.profit-summary');
    Route::get('/air-import/{id}/profit-detail', [AirImportController::class, 'profitDetailView'])->name('air-import.profit-detail');

    Route::get('/air-import/{air_import}/history', [AirImportController::class, 'getHistory'])->name('air-import.history');

    Route::post('/air-import/{air_import}/documents', [AirImportController::class, 'uploadDocument'])->name('air-import.documents.store');
    Route::delete('/air-import/documents/{document}', [AirImportController::class, 'deleteDocument'])->name('air-import.documents.destroy');
    Route::get('/air-import/documents/{document}/download', [AirImportController::class, 'downloadDocument'])->name('air-import.documents.download');

    Route::get('/air-export/list', [AirExportController::class, 'index'])->name('air-export.index');
    Route::get('/air-export/create', [AirExportController::class, 'create'])->name('air-export.create');
    Route::post('/air-export', [AirExportController::class, 'store'])->name('air-export.store');
    Route::get('/air-export/{air_export}/edit', [AirExportController::class, 'edit'])->name('air-export.edit');
    Route::put('/air-export/{air_export}', [AirExportController::class, 'update'])->name('air-export.update');
    Route::delete('/air-export/{air_export}', [AirExportController::class, 'destroy'])->name('air-export.destroy');
    Route::get('/air-export/list/mbl', [AirExportController::class, 'mblList'])->name('air-export.mbl-list');
    Route::get('/air-export/list/hbl', [AirExportController::class, 'hblList'])->name('air-export.hbl-list');
    Route::get('/air-export/export-csv', [AirExportController::class, 'exportCsv'])->name('air-export.export-csv');
    Route::get('/air-export/mbl-export-csv', [AirExportController::class, 'exportCsv'])->name('air-export.mbl-export-csv');
    Route::match(['GET','POST','DELETE'], '/air-export/bulk-delete', [AirExportController::class, 'bulkDelete'])->name('air-export.bulk-delete');
    Route::match(['GET','POST','DELETE'], '/air-export/bulk-block', [AirExportController::class, 'bulkBlock'])->name('air-export.bulk-block');
    Route::match(['GET','POST','DELETE'], '/air-export/bulk-unblock', [AirExportController::class, 'bulkUnblock'])->name('air-export.bulk-unblock');
    Route::post('/air-export/bulk-change-op', [AirExportController::class, 'bulkChangeOp'])->name('air-export.bulk-change-op');
    Route::post('/air-export/bulk-change-sales', [AirExportController::class, 'bulkChangeSales'])->name('air-export.bulk-change-sales');
    Route::patch('/air-export/{air_export}/color', [AirExportController::class, 'updateColor'])->name('air-export.update-color');
    Route::post('/air-export/hbl-bulk-delete', [AirExportController::class, 'hblBulkDelete'])->name('air-export.hbl-bulk-delete');
    Route::post('/air-export/hbl-bulk-block', [AirExportController::class, 'hblBulkBlock'])->name('air-export.hbl-bulk-block');
    Route::post('/air-export/hbl-bulk-unblock', [AirExportController::class, 'hblBulkUnblock'])->name('air-export.hbl-bulk-unblock');
    Route::post('/air-export/hbl-bulk-change-sales', [AirExportController::class, 'hblBulkChangeSales'])->name('air-export.hbl-bulk-change-sales');
    Route::post('/air-export/hbl-bulk-change-op', [AirExportController::class, 'hblBulkChangeOp'])->name('air-export.hbl-bulk-change-op');
    Route::get('/air-export/hbl-export-csv', [AirExportController::class, 'hblExportCsv'])->name('air-export.hbl-export-csv');
    Route::patch('/air-export/hbl/{id}/color', [AirExportController::class, 'hblUpdateColor'])->name('air-export.hbl-update-color');
    Route::get('/air-export/{air_export}/history', [AirExportController::class, 'getHistory'])->name('air-export.history');
    Route::post('/air-export/{air_export}/internal-message', [AirExportController::class, 'saveInternalMessage'])->name('air-export.internal-message');
    Route::get('/air-export/{id}/document-package', [AirExportController::class, 'documentPackage'])->name('air-export.document-package');
    Route::get('/air-export/{id}/consolidated-manifest', [AirExportController::class, 'consolidatedManifest'])->name('air-export.consolidated-manifest');
    Route::get('/air-export/{id}/booking-confirmation', [AirExportController::class, 'bookingConfirmation'])->name('air-export.booking-confirmation');
    Route::get('/air-export/{id}/mawb-package-label', [AirExportController::class, 'mawbPackageLabel'])->name('air-export.mawb-package-label');
    Route::post('/air-export/{id}/label-description', [AirExportController::class, 'updateLabelDescription'])->name('air-export.label-description.update');
    Route::get('/air-export/{id}/package-label-list', [AirExportController::class, 'packageLabelList'])->name('air-export.package-label-list');
    Route::get('/air-export/{id}/profit-detail', [AirExportController::class, 'profitDetailView'])->name('air-export.profit-detail');
    Route::get('/air-export/{id}/profit-summary', [AirExportController::class, 'profitSummaryView'])->name('air-export.profit-summary');
    Route::get('/air-export/{id}/hawb-print/{hawbIndex}', [AirExportController::class, 'hawbPrint'])->name('air-export.hawb-print');
    
    // Charge CRUD
    Route::post('/air-export/{air_export}/charges', [AirExportController::class, 'addCharge'])->name('air-export.charges.store');
    Route::put('/air-export/charges/{charge}', [AirExportController::class, 'updateCharge'])->name('air-export.charges.update');
    Route::delete('/air-export/charges/{charge}', [AirExportController::class, 'deleteCharge'])->name('air-export.charges.destroy');
    Route::delete('/air-export/{air_export}/charges/all', [AirExportController::class, 'deleteAllCharges'])->name('air-export.charges.destroy-all');
    Route::get('/air-export/{air_export}/charges', [AirExportController::class, 'getCharges'])->name('air-export.charges.index');
    Route::post('/air-export/{air_export}/charges/invoice', [AirExportController::class, 'createInvoiceFromCharges'])->name('air-export.charges.invoice');
    Route::get('/air-export/{air_export}/charges/export', [AirExportController::class, 'exportChargesToExcel'])->name('air-export.charges.export');
    Route::get('/air-export/{air_export}/charges/print', [AirExportController::class, 'printCharges'])->name('air-export.charges.print');
    
    // Status Logs
    Route::get('/api/air-exports/{air_export}/status-logs', [AirExportController::class, 'getStatusLogs'])->name('air-export.status-logs');
    
    // Document Center
    Route::get('/api/air-exports/{air_export}/documents', [AirExportController::class, 'getDocuments'])->name('air-export.documents.index');
    Route::post('/api/air-exports/{air_export}/documents', [AirExportController::class, 'uploadDocuments'])->name('air-export.documents.upload');
    Route::get('/air-export/{airExport}/documents/{document}/download', [AirExportController::class, 'downloadDocument'])->name('air-export.documents.download');
    Route::delete('/api/air-exports/{airExport}/documents/{document}', [AirExportController::class, 'deleteDocument'])->name('air-export.documents.delete');
    
    // Memo CRUD
    Route::get('/air-export/{air_export}/memos', [AirExportController::class, 'getMemos'])->name('air-export.memos.index');
    Route::post('/air-export/{air_export}/memos', [AirExportController::class, 'addMemo'])->name('air-export.memos.store');
    Route::put('/air-export/memos/{memo}', [AirExportController::class, 'updateMemo'])->name('air-export.memos.update');
    Route::delete('/air-export/memos/{memo}', [AirExportController::class, 'deleteMemo'])->name('air-export.memos.destroy');
    
    // Email charge
    Route::post('/air-export/{air_export}/email-charge', [AirExportController::class, 'emailCharge'])->name('air-export.email-charge');
    
    Route::get('/air-export/booking/list', [AirBookingController::class, 'index'])->name('air-bookings.index');
    Route::get('/air-export/booking/entry', [AirBookingController::class, 'create'])->name('air-bookings.create');
    Route::post('/air-export/booking', [AirBookingController::class, 'store'])->name('air-bookings.store');
    Route::get('/air-export/booking/{id}/edit', [AirBookingController::class, 'edit'])->name('air-bookings.edit');
    Route::put('/air-export/booking/{id}', [AirBookingController::class, 'update'])->name('air-bookings.update');
    Route::delete('/air-export/booking/{id}', [AirBookingController::class, 'destroy'])->name('air-bookings.destroy');
    Route::match(['GET','POST','DELETE'], '/air-export/booking/bulk-delete', [AirBookingController::class, 'bulkDelete'])->name('air-bookings.bulk-delete');
    Route::post('/air-export/booking/bulk-change-sales', [AirBookingController::class, 'bulkChangeSales'])->name('air-bookings.bulk-change-sales');
    Route::post('/air-export/booking/bulk-change-op', [AirBookingController::class, 'bulkChangeOp'])->name('air-bookings.bulk-change-op');
    Route::patch('/air-export/booking/{id}/color', [AirBookingController::class, 'updateColor'])->name('air-bookings.update-color');
    Route::patch('/air-export/booking/{id}/toggle-lock', [AirBookingController::class, 'toggleLock'])->name('air-bookings.toggle-lock');
    Route::post('/air-export/booking/bulk-convert', [AirBookingController::class, 'bulkConvert'])->name('air-bookings.bulk-convert');
    Route::post('/air-export/booking/bulk-block', [AirBookingController::class, 'bulkBlock'])->name('air-bookings.bulk-block');
    Route::post('/air-export/booking/bulk-unblock', [AirBookingController::class, 'bulkUnblock'])->name('air-bookings.bulk-unblock');
    Route::get('/air-export/booking/export-csv', [AirBookingController::class, 'exportCsv'])->name('air-bookings.export-csv');

    Route::get('/air-export/booking/{id}/accounting', [AirBookingController::class, 'accounting'])->name('air-bookings.accounting');
    Route::get('/air-export/booking/{id}/status', [AirBookingController::class, 'status'])->name('air-bookings.status');
    Route::get('/air-export/booking/{id}/charges', [AirBookingController::class, 'getCharges'])->name('air-bookings.charges.index');
    Route::post('/air-export/booking/{id}/charges', [AirBookingController::class, 'addCharge'])->name('air-bookings.charges.store');
    Route::put('/air-export/booking/charges/{charge}', [AirBookingController::class, 'updateBookingCharge'])->name('air-bookings.charges.update');
    Route::delete('/air-export/booking/charges/{charge}', [AirBookingController::class, 'deleteBookingCharge'])->name('air-bookings.charges.destroy');
    Route::delete('/air-export/booking/{id}/charges/all', [AirBookingController::class, 'deleteAllBookingCharges'])->name('air-bookings.charges.destroy-all');
    Route::get('/air-export/booking/{id}/history', [AirBookingController::class, 'getBookingHistory'])->name('air-bookings.history');
    Route::post('/air-export/booking/{id}/status', [AirBookingController::class, 'saveBookingStatus'])->name('air-bookings.status.save');

    // Legacy Booking aliases
    Route::get('/air-export/booking/{subpage}', function ($subpage = 'entry') {
        if ($subpage === 'entry') return redirect()->route('air-bookings.create');
        if ($subpage === 'list') return redirect()->route('air-bookings.index');
        if ($subpage === 'accounting' || $subpage === 'status') {
            return redirect()->route('air-bookings.index')->with('info', 'Please select a booking first, then use the tabs to view Accounting or Status.');
        }
        if ($subpage === 'mawb-stock') {
            $query = \App\Models\AirExport::whereNotNull('mawb_no')->with(['carrier', 'office']);

            if ($search = request('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('mawb_no', 'like', "%{$search}%")
                      ->orWhere('file_no', 'like', "%{$search}%");
                });
            }
            if ($carrierId = request('carrier_id')) {
                $query->where('carrier_id', $carrierId);
            }
            if ($officeId = request('office_id')) {
                $query->where('office_id', $officeId);
            }
            if ($status = request('status')) {
                if ($status === 'blocked') {
                    $query->where('is_blocked', true);
                } elseif ($status === 'available') {
                    $query->whereNull('file_no');
                } elseif ($status === 'assigned') {
                    $query->whereNotNull('file_no');
                }
            }

            $stocks = $query->select('id', 'mawb_no', 'carrier_id', 'file_no', 'office_id', 'created_at', 'color', 'is_blocked')
                ->latest()->paginate(20)->withQueryString();

            $carriers = \App\Models\TradePartner::whereNotNull('name')->orderBy('name')->get();
            $offices = \App\Models\Office::where('is_active', true)->orderBy('name')->get();

            return view('air-export.mawb-stock-list', compact('stocks', 'carriers', 'offices'));
        }
    });

    // Trucking
    Route::get('/truck/list', [TruckShipmentController::class, 'index'])->name('truck.index');
    Route::get('/truck/create', [TruckShipmentController::class, 'create'])->name('truck.create');
    Route::get('/truck/create-quote', [TruckShipmentController::class, 'create'])->name('truck.create-quote');
    Route::post('/truck', [TruckShipmentController::class, 'store'])->name('truck.store');
    Route::get('/truck/{truck_shipment}/edit', [TruckShipmentController::class, 'edit'])->name('truck.edit');
    Route::put('/truck/{truck_shipment}', [TruckShipmentController::class, 'update'])->name('truck.update');
    Route::patch('/truck/{truck_shipment}/color', [TruckShipmentController::class, 'updateColor'])->name('truck.update-color');
    Route::delete('/truck/{truck_shipment}', [TruckShipmentController::class, 'destroy'])->name('truck.destroy');
    
    Route::match(['GET', 'POST', 'DELETE'], '/truck/bulk-delete', [TruckShipmentController::class, 'bulkDelete'])->name('truck.bulk-delete');
    Route::match(['GET', 'POST', 'DELETE'], '/truck/bulk-block', [TruckShipmentController::class, 'bulkBlock'])->name('truck.bulk-block');
    Route::match(['GET', 'POST', 'DELETE'], '/truck/bulk-unblock', [TruckShipmentController::class, 'bulkUnblock'])->name('truck.bulk-unblock');
    Route::get('/truck/export-csv', [TruckShipmentController::class, 'exportCsv'])->name('truck.export-csv');
    Route::get('/truck/my-shipment-list', [TruckShipmentController::class, 'myShipmentList'])->name('truck.my-shipment-list');
    Route::get('/truck/my-shipment-list-print', [TruckShipmentController::class, 'myShipmentListPrint'])->name('truck.my-shipment-list-print');
    Route::get('/truck/{truck_shipment}/pickup-delivery-order', [TruckShipmentController::class, 'pickupDeliveryOrder'])->name('truck.pickup-delivery-order');
    Route::get('/truck/{truck_shipment}/bol-print', [TruckShipmentController::class, 'bolPrint'])->name('truck.bol-print');
    Route::get('/truck/{truck_shipment}/profit-report-summary', [TruckShipmentController::class, 'profitReportSummary'])->name('truck.profit-report-summary');
    Route::get('/truck/{truck_shipment}/profit-report-detail', [TruckShipmentController::class, 'profitReportDetail'])->name('truck.profit-report-detail');
    Route::get('/truck/{truck_shipment}/cargo-manifest-status', [TruckShipmentController::class, 'cargoManifestStatus'])->name('truck.cargo-manifest-status');

    // Accounting - Currency Rates
    Route::get('/accounting/currency-table', [App\Http\Controllers\CurrencyRateController::class, 'index'])->name('accounting.currency-table');
    Route::get('/accounting/currency-table-print', [App\Http\Controllers\CurrencyRateController::class, 'print'])->name('accounting.currency-table-print');
    Route::get('/api/currency-rates', [App\Http\Controllers\CurrencyRateController::class, 'getRates'])->name('api.currency-rates.index');
    Route::post('/api/currency-rates/bulk-save', [App\Http\Controllers\CurrencyRateController::class, 'bulkSave'])->name('api.currency-rates.bulk-save');
    Route::delete('/api/currency-rates/{id}', [App\Http\Controllers\CurrencyRateController::class, 'destroy'])->name('api.currency-rates.destroy');
    Route::post('/api/currency-rates/bulk-delete', [App\Http\Controllers\CurrencyRateController::class, 'bulkDelete'])->name('api.currency-rates.bulk-delete');
    Route::get('/api/currency-rates/export', [App\Http\Controllers\CurrencyRateController::class, 'export'])->name('api.currency-rates.export');

    // Accounting - Banks
    Route::get('/accounting/bank-list', [App\Http\Controllers\BankController::class, 'index'])->name('accounting.bank-list');
    Route::get('/accounting/bank-list-print', [App\Http\Controllers\BankController::class, 'print'])->name('accounting.bank-list-print');
    Route::get('/api/banks', [App\Http\Controllers\BankController::class, 'getBanks'])->name('api.banks.index');
    Route::post('/api/banks/bulk-save', [App\Http\Controllers\BankController::class, 'bulkSave'])->name('api.banks.bulk-save');
    Route::delete('/api/banks/{id}', [App\Http\Controllers\BankController::class, 'destroy'])->name('api.banks.destroy');
    Route::post('/api/banks/bulk-delete', [App\Http\Controllers\BankController::class, 'bulkDelete'])->name('api.banks.bulk-delete');
    Route::get('/api/banks/export', [App\Http\Controllers\BankController::class, 'export'])->name('api.banks.export');
    
    // Bank Services (Production Features)
    Route::get('/api/banks/{bank}/next-check-number', [App\Http\Controllers\BankController::class, 'getNextCheckNumber'])->name('api.banks.next-check-number');
    Route::post('/api/banks/{bank}/import-check-clearing', [App\Http\Controllers\BankController::class, 'importCheckClearing'])->name('api.banks.import-check-clearing');
    Route::get('/api/banks/default-invoice', [App\Http\Controllers\BankController::class, 'getDefaultInvoiceBank'])->name('api.banks.default-invoice');
    Route::get('/api/banks/{bank}/display-info', [App\Http\Controllers\BankController::class, 'getBankDisplayInfo'])->name('api.banks.display-info');

    // Accounting - Billing Code
    Route::get('/accounting/billing-code-list', [App\Http\Controllers\BillingCodeController::class, 'index'])->name('accounting.billing-code-list');
    Route::get('/accounting/billing-code-list-print', [App\Http\Controllers\BillingCodeController::class, 'print'])->name('accounting.billing-code-list-print');
    Route::get('/api/billing-codes', [App\Http\Controllers\BillingCodeController::class, 'getCodes'])->name('api.billing-codes.index');
    Route::post('/api/billing-codes/bulk-save', [App\Http\Controllers\BillingCodeController::class, 'bulkSave'])->name('api.billing-codes.bulk-save');
    Route::delete('/api/billing-codes/{id}', [App\Http\Controllers\BillingCodeController::class, 'destroy'])->name('api.billing-codes.destroy');
    Route::post('/api/billing-codes/bulk-delete', [App\Http\Controllers\BillingCodeController::class, 'bulkDelete'])->name('api.billing-codes.bulk-delete');
    Route::get('/api/billing-codes/export', [App\Http\Controllers\BillingCodeController::class, 'export'])->name('api.billing-codes.export');
    Route::get('/api/billing-codes/mapping-data', [App\Http\Controllers\BillingCodeController::class, 'getMappingData'])->name('api.billing-codes.mapping-data');
    Route::post('/api/billing-codes/save-mappings', [App\Http\Controllers\BillingCodeController::class, 'saveMappings'])->name('api.billing-codes.save-mappings');

    // Accounting - G/L Code
    Route::get('/accounting/gl-code-list', [App\Http\Controllers\GLCodeController::class, 'index'])->name('accounting.gl-code-list');
    Route::get('/accounting/gl-code-list-print', [App\Http\Controllers\GLCodeController::class, 'print'])->name('accounting.gl-code-list-print');
    Route::get('/api/gl-codes', [App\Http\Controllers\GLCodeController::class, 'getCodes'])->name('api.gl-codes.index');
    Route::post('/api/gl-codes/bulk-save', [App\Http\Controllers\GLCodeController::class, 'bulkSave'])->name('api.gl-codes.bulk-save');
    Route::delete('/api/gl-codes/{id}', [App\Http\Controllers\GLCodeController::class, 'destroy'])->name('api.gl-codes.destroy');
    Route::post('/api/gl-codes/bulk-delete', [App\Http\Controllers\GLCodeController::class, 'bulkDelete'])->name('api.gl-codes.bulk-delete');
    Route::get('/api/gl-codes/export', [App\Http\Controllers\GLCodeController::class, 'export'])->name('api.gl-codes.export');

    // Settings - To Do List
    Route::get('/settings/todo-list', [App\Http\Controllers\TodoTaskController::class, 'index'])->name('settings.todo-list');
    Route::get('/settings/todo-list-print', [App\Http\Controllers\TodoTaskController::class, 'print'])->name('settings.todo-list-print');
    Route::get('/api/todo-tasks', [App\Http\Controllers\TodoTaskController::class, 'getTasks'])->name('api.todo-tasks.index');
    Route::post('/api/todo-tasks', [App\Http\Controllers\TodoTaskController::class, 'store'])->name('api.todo-tasks.store');
    Route::put('/api/todo-tasks/{id}', [App\Http\Controllers\TodoTaskController::class, 'update'])->name('api.todo-tasks.update');
    Route::delete('/api/todo-tasks/{id}', [App\Http\Controllers\TodoTaskController::class, 'destroy'])->name('api.todo-tasks.destroy');
    Route::post('/api/todo-tasks/bulk-save', [App\Http\Controllers\TodoTaskController::class, 'bulkSave'])->name('api.todo-tasks.bulk-save');
    Route::post('/api/todo-tasks/bulk-delete', [App\Http\Controllers\TodoTaskController::class, 'bulkDelete'])->name('api.todo-tasks.bulk-delete');

    // Settings - Container TP/SZ
    Route::get('/settings/container-types', [App\Http\Controllers\ContainerTypeController::class, 'index'])->name('settings.container-types');
    Route::get('/settings/container-types/export-csv', [App\Http\Controllers\ContainerTypeController::class, 'exportCsv'])->name('settings.container-types.export-csv');
    Route::get('/api/container-types', [App\Http\Controllers\ContainerTypeController::class, 'index'])->name('api.container-types.index');
    Route::post('/api/container-types', [App\Http\Controllers\ContainerTypeController::class, 'store'])->name('api.container-types.store');
    Route::put('/api/container-types/{id}', [App\Http\Controllers\ContainerTypeController::class, 'update'])->name('api.container-types.update');
    Route::delete('/api/container-types/{id}', [App\Http\Controllers\ContainerTypeController::class, 'destroy'])->name('api.container-types.destroy');
    Route::post('/api/container-types/bulk-save', [App\Http\Controllers\ContainerTypeController::class, 'bulkSave'])->name('api.container-types.bulk-save');

    // Settings - Freight Default Values
    Route::get('/settings/freight-default-values', [App\Http\Controllers\FreightDefaultValueController::class, 'index'])->name('settings.freight-default-values');
    Route::get('/api/freight-default-values/module', [App\Http\Controllers\FreightDefaultValueController::class, 'getByModule'])->name('api.freight-default-values.module');
    Route::post('/api/freight-default-values', [App\Http\Controllers\FreightDefaultValueController::class, 'store'])->name('api.freight-default-values.store');
    Route::put('/api/freight-default-values/{id}', [App\Http\Controllers\FreightDefaultValueController::class, 'update'])->name('api.freight-default-values.update');
    Route::delete('/api/freight-default-values/{id}', [App\Http\Controllers\FreightDefaultValueController::class, 'destroy'])->name('api.freight-default-values.destroy');
    Route::post('/api/freight-default-values/bulk-save', [App\Http\Controllers\FreightDefaultValueController::class, 'bulkSave'])->name('api.freight-default-values.bulk-save');

    // Settings - User Management
    Route::get('/settings/user-management', [App\Http\Controllers\UserManagementController::class, 'index'])->name('settings.user-management');
    Route::get('/settings/user-management/export-csv', [App\Http\Controllers\UserManagementController::class, 'exportCsv'])->name('settings.user-management.export-csv');
    Route::post('/api/users', [App\Http\Controllers\UserManagementController::class, 'store'])->name('api.users.store');
    Route::post('/api/users/bulk-save', [App\Http\Controllers\UserManagementController::class, 'bulkSave'])->name('api.users.bulk-save');
    Route::put('/api/users/{id}', [App\Http\Controllers\UserManagementController::class, 'update'])->name('api.users.update');
    Route::delete('/api/users/{id}', [App\Http\Controllers\UserManagementController::class, 'destroy'])->name('api.users.destroy');
    Route::post('/api/users/{id}/reset-password', [App\Http\Controllers\UserManagementController::class, 'resetPassword'])->name('api.users.reset-password');

    // Settings - AWB No. Management
    Route::get('/settings/awb-management', [App\Http\Controllers\AwbManagementController::class, 'index'])->name('settings.awb-management');
    Route::get('/settings/awb-management-print', [App\Http\Controllers\AwbManagementController::class, 'print'])->name('settings.awb-management.print');
    Route::get('/api/awb-management', [App\Http\Controllers\AwbManagementController::class, 'getBlocks'])->name('api.awb-management.index');
    Route::get('/api/awb-management/carrier/{carrier_id}', [App\Http\Controllers\AwbManagementController::class, 'getAvailableByCarrier'])->name('api.awb-management.carrier');
    Route::post('/api/awb-management/bulk-save', [App\Http\Controllers\AwbManagementController::class, 'bulkSave'])->name('api.awb-management.bulk-save');
    Route::post('/api/awb-management/bulk-delete', [App\Http\Controllers\AwbManagementController::class, 'bulkDelete'])->name('api.awb-management.bulk-delete');
    Route::get('/api/awb-management/export', [App\Http\Controllers\AwbManagementController::class, 'export'])->name('api.awb-management.export');
    Route::delete('/api/awb-management/{id}', [App\Http\Controllers\AwbManagementController::class, 'destroy'])->name('api.awb-management.destroy');

    // Settings - HBL Templates
    Route::get('/settings/hbl-templates', [App\Http\Controllers\HblTemplateController::class, 'index'])->name('settings.hbl-templates');
    Route::get('/settings/hbl-templates-print', [App\Http\Controllers\HblTemplateController::class, 'print'])->name('settings.hbl-templates.print');
    Route::get('/api/hbl-templates', [App\Http\Controllers\HblTemplateController::class, 'getTemplates'])->name('api.hbl-templates.index');
    Route::post('/api/hbl-templates/bulk-save', [App\Http\Controllers\HblTemplateController::class, 'bulkSave'])->name('api.hbl-templates.bulk-save');
    Route::post('/api/hbl-templates/bulk-delete', [App\Http\Controllers\HblTemplateController::class, 'bulkDelete'])->name('api.hbl-templates.bulk-delete');
    Route::get('/api/hbl-templates/export', [App\Http\Controllers\HblTemplateController::class, 'export'])->name('api.hbl-templates.export');
    Route::delete('/api/hbl-templates/{id}', [App\Http\Controllers\HblTemplateController::class, 'destroy'])->name('api.hbl-templates.destroy');

    // Settings - Shipment Memo Auto-Load
    Route::get('/settings/shipment-memo-auto-load', [App\Http\Controllers\ShipmentMemoConfigController::class, 'index'])->name('settings.shipment-memo-auto-load');
    Route::get('/api/shipment-memo-configs/module', [App\Http\Controllers\ShipmentMemoConfigController::class, 'getByModule'])->name('api.shipment-memo-configs.module');
    Route::get('/api/shipment-memo-configs/check', [App\Http\Controllers\ShipmentMemoConfigController::class, 'checkAutoLoad'])->name('api.shipment-memo-configs.check');
    Route::post('/api/shipment-memo-configs/bulk-save', [App\Http\Controllers\ShipmentMemoConfigController::class, 'bulkSave'])->name('api.shipment-memo-configs.bulk-save');

    // Supply Chain & Warehouse
    Route::get('/warehouse/receipt', [WarehouseReceiptController::class, 'index'])->name('warehouse.receipts.index');
    Route::get('/warehouse/receipt/create', [WarehouseReceiptController::class, 'create'])->name('warehouse.receipts.create');
    Route::post('/warehouse/receipt', [WarehouseReceiptController::class, 'store'])->name('warehouse.receipts.store');
    Route::get('/warehouse/receipt/generate-no', [WarehouseReceiptController::class, 'generateReceiptNo'])->name('warehouse.receipts.generate-no');
    Route::get('/warehouse/receipt/{id}/edit', [WarehouseReceiptController::class, 'edit'])->name('warehouse.receipts.edit');
    Route::put('/warehouse/receipt/{id}', [WarehouseReceiptController::class, 'update'])->name('warehouse.receipts.update');
    Route::delete('/warehouse/receipt/{id}', [WarehouseReceiptController::class, 'destroy'])->name('warehouse.receipts.destroy');
    Route::post('/warehouse/receipt/bulk-delete', [WarehouseReceiptController::class, 'bulkDelete'])->name('warehouse.receipts.bulk-delete');
    Route::patch('/warehouse/receipt/{id}/color', [WarehouseReceiptController::class, 'updateColor'])->name('warehouse.receipts.update-color');
    Route::get('/warehouse/receipt/export-csv', [WarehouseReceiptController::class, 'exportCsv'])->name('warehouse.receipts.export-csv');
    // Receipt Documents
    Route::post('/warehouse/receipt/{warehouse_receipt}/documents', [WarehouseReceiptController::class, 'uploadDocument'])->name('warehouse.receipts.documents.store');
    Route::delete('/warehouse/receipt/documents/{document}', [WarehouseReceiptController::class, 'deleteDocument'])->name('warehouse.receipts.documents.destroy');
    Route::get('/warehouse/receipt/documents/{document}/download', [WarehouseReceiptController::class, 'downloadDocument'])->name('warehouse.receipts.documents.download');

    Route::get('/warehouse/receiving', [WarehouseReceivingController::class, 'index'])->name('receiving.index');
    Route::get('/warehouse/receiving/list', [WarehouseReceivingController::class, 'index'])->name('receiving.list');
    Route::get('/warehouse/receiving/create', [WarehouseReceivingController::class, 'create'])->name('receiving.create');
    Route::post('/warehouse/receiving', [WarehouseReceivingController::class, 'store'])->name('receiving.store');
    Route::get('/warehouse/receiving/export-csv', [WarehouseReceivingController::class, 'exportCsv'])->name('receiving.export-csv');
    Route::match(['GET', 'POST', 'DELETE'], '/warehouse/receiving/bulk-delete', [WarehouseReceivingController::class, 'bulkDelete'])->name('receiving.bulk-delete');
    Route::get('/warehouse/receiving/{warehouse_receiving}', [WarehouseReceivingController::class, 'show'])->name('receiving.show');
    Route::patch('/warehouse/receiving/{warehouse_receiving}/color', [WarehouseReceivingController::class, 'updateColor'])->name('receiving.update-color');
    Route::get('/warehouse/receiving/{warehouse_receiving}/edit', [WarehouseReceivingController::class, 'edit'])->name('receiving.edit');
    Route::put('/warehouse/receiving/{warehouse_receiving}', [WarehouseReceivingController::class, 'update'])->name('receiving.update');
    Route::delete('/warehouse/receiving/{warehouse_receiving}', [WarehouseReceivingController::class, 'destroy'])->name('receiving.destroy');
    // Receiving Documents
    Route::post('/warehouse/receiving/{warehouse_receiving}/documents', [WarehouseReceivingController::class, 'uploadDocument'])->name('receiving.documents.store');
    Route::delete('/warehouse/receiving/documents/{document}', [WarehouseReceivingController::class, 'deleteDocument'])->name('receiving.documents.destroy');
    Route::get('/warehouse/receiving/documents/{document}/download', [WarehouseReceivingController::class, 'downloadDocument'])->name('receiving.documents.download');

    Route::get('/warehouse/shipping', [WarehouseShippingController::class, 'index'])->name('shipping.index');
    Route::get('/warehouse/shipping/list', [WarehouseShippingController::class, 'index'])->name('shipping.list');
    Route::get('/warehouse/shipping/create', [WarehouseShippingController::class, 'create'])->name('shipping.create');
    Route::post('/warehouse/shipping', [WarehouseShippingController::class, 'store'])->name('shipping.store');
    Route::get('/warehouse/shipping/export-csv', [WarehouseShippingController::class, 'exportCsv'])->name('shipping.export-csv');
    Route::match(['GET', 'POST', 'DELETE'], '/warehouse/shipping/bulk-delete', [WarehouseShippingController::class, 'bulkDelete'])->name('shipping.bulk-delete');
    Route::get('/warehouse/shipping/{id}/edit', [WarehouseShippingController::class, 'edit'])->name('shipping.edit');
    Route::put('/warehouse/shipping/{id}', [WarehouseShippingController::class, 'update'])->name('shipping.update');
    Route::delete('/warehouse/shipping/{id}', [WarehouseShippingController::class, 'destroy'])->name('shipping.destroy');
    Route::patch('/warehouse/shipping/{shipping}/color', [WarehouseShippingController::class, 'updateColor'])->name('shipping.update-color');
    Route::post('/warehouse/shipping/{shipping}/documents', [WarehouseShippingController::class, 'uploadDocument'])->name('shipping.documents.store');
    Route::delete('/warehouse/shipping/documents/{document}', [WarehouseShippingController::class, 'deleteDocument'])->name('shipping.documents.destroy');
    Route::get('/warehouse/shipping/documents/{document}/download', [WarehouseShippingController::class, 'downloadDocument'])->name('shipping.documents.download');

    Route::get('/warehouse/items', [WarehouseInventoryItemController::class, 'index'])->name('items.index');
    Route::post('/warehouse/items', [WarehouseInventoryItemController::class, 'store'])->name('items.store');
    Route::put('/warehouse/items/{id}', [WarehouseInventoryItemController::class, 'update'])->name('items.update');
    Route::delete('/warehouse/items/{id}', [WarehouseInventoryItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/warehouse/items/export-csv', [WarehouseInventoryItemController::class, 'exportCsv'])->name('items.export-csv');
    Route::match(['GET', 'POST', 'DELETE'], '/warehouse/items/bulk-delete', [WarehouseInventoryItemController::class, 'bulkDelete'])->name('items.bulk-delete');
    Route::patch('/warehouse/items/{item}/color', [WarehouseInventoryItemController::class, 'updateColor'])->name('items.update-color');

    Route::get('/warehouse/inventory/summary', [WarehouseInventoryItemController::class, 'summary'])->name('inventory.summary');
    Route::post('/warehouse/inventory/summary', [WarehouseInventoryItemController::class, 'summaryStore'])->name('inventory.summary.store');
    Route::put('/warehouse/inventory/summary/{id}', [WarehouseInventoryItemController::class, 'summaryUpdate'])->name('inventory.summary.update');
    Route::delete('/warehouse/inventory/summary/{id}', [WarehouseInventoryItemController::class, 'summaryDestroy'])->name('inventory.summary.destroy');
    Route::match(['GET', 'POST', 'DELETE'], '/warehouse/inventory/summary/bulk-delete', [WarehouseInventoryItemController::class, 'summaryBulkDelete'])->name('inventory.summary.bulk-delete');
    Route::get('/warehouse/inventory/summary/export-csv', [WarehouseInventoryItemController::class, 'summaryExportCsv'])->name('inventory.summary.export-csv');
    Route::get('/warehouse/inventory/detail', [WarehouseInventoryItemController::class, 'detail'])->name('inventory.detail');
    Route::post('/warehouse/inventory/detail', [WarehouseInventoryItemController::class, 'detailStore'])->name('inventory.detail.store');
    Route::put('/warehouse/inventory/detail/{id}', [WarehouseInventoryItemController::class, 'detailUpdate'])->name('inventory.detail.update');
    Route::delete('/warehouse/inventory/detail/{id}', [WarehouseInventoryItemController::class, 'detailDestroy'])->name('inventory.detail.destroy');
    Route::match(['GET', 'POST', 'DELETE'], '/warehouse/inventory/detail/bulk-delete', [WarehouseInventoryItemController::class, 'detailBulkDelete'])->name('inventory.detail.bulk-delete');
    Route::get('/warehouse/inventory/detail/export-csv', [WarehouseInventoryItemController::class, 'detailExportCsv'])->name('inventory.detail.export-csv');

    // Warehouse Automobile
    Route::get('/warehouse/automobile', [WarehouseAutomobileController::class, 'index'])->name('warehouse.automobile.index');
    Route::get('/warehouse/automobile/create', [WarehouseAutomobileController::class, 'create'])->name('warehouse.automobile.create');
    Route::post('/warehouse/automobile', [WarehouseAutomobileController::class, 'store'])->name('warehouse.automobile.store');
    // Literal routes MUST come BEFORE wildcard {warehouse_automobile} to avoid 404
    Route::match(['GET', 'POST', 'DELETE'], '/warehouse/automobile/bulk-delete', [WarehouseAutomobileController::class, 'bulkDelete'])->name('warehouse.automobile.bulk-delete');
    Route::match(['GET', 'POST', 'DELETE'], '/warehouse/automobile/bulk-block', [WarehouseAutomobileController::class, 'bulkBlock'])->name('warehouse.automobile.bulk-block');
    Route::match(['GET', 'POST', 'DELETE'], '/warehouse/automobile/bulk-unblock', [WarehouseAutomobileController::class, 'bulkUnblock'])->name('warehouse.automobile.bulk-unblock');
    Route::get('/warehouse/automobile/export-csv', [WarehouseAutomobileController::class, 'exportCsv'])->name('warehouse.automobile.export-csv');
    Route::get('/warehouse/automobile/{warehouse_automobile}', [WarehouseAutomobileController::class, 'show'])->name('warehouse.automobile.show');
    Route::get('/warehouse/automobile/{warehouse_automobile}/edit', [WarehouseAutomobileController::class, 'edit'])->name('warehouse.automobile.edit');
    Route::put('/warehouse/automobile/{warehouse_automobile}', [WarehouseAutomobileController::class, 'update'])->name('warehouse.automobile.update');
    Route::delete('/warehouse/automobile/{warehouse_automobile}', [WarehouseAutomobileController::class, 'destroy'])->name('warehouse.automobile.destroy');
    Route::patch('/warehouse/automobile/{warehouse_automobile}/color', [WarehouseAutomobileController::class, 'updateColor'])->name('warehouse.automobile.update-color');
    Route::patch('/warehouse/automobile/{warehouse_automobile}/toggle-block', [WarehouseAutomobileController::class, 'toggleBlock'])->name('warehouse.automobile.toggle-block');
    Route::get('/warehouse/automobile/{warehouse_automobile}/documents', [WarehouseAutomobileController::class, 'getDocuments'])->name('warehouse.automobile.documents.list');
    Route::post('/warehouse/automobile/{warehouse_automobile}/documents', [WarehouseAutomobileController::class, 'uploadDocument'])->name('warehouse.automobile.documents.store');
    Route::delete('/warehouse/automobile/documents/{document}', [WarehouseAutomobileController::class, 'deleteDocument'])->name('warehouse.automobile.documents.destroy');
    Route::patch('/warehouse/automobile/documents/{document}/purpose', [WarehouseAutomobileController::class, 'updateDocumentPurpose'])->name('warehouse.automobile.documents.purpose');
    Route::get('/warehouse/automobile/{warehouse_automobile}/documents/download', [WarehouseAutomobileController::class, 'downloadAllDocuments'])->name('warehouse.automobile.documents.download');


    Route::get('/warehouse/{page?}/{subpage?}/{action?}', function ($page = 'list', $subpage = null, $action = null) {
        if ($page === 'automobile') return redirect()->route('warehouse.automobile.index');

        if ($page === 'receipt') {
            return redirect()->route('warehouse.receipts.index');
        }

        if ($page === 'receiving') {
            if ($subpage === 'create') return redirect()->route('receiving.create');
            return redirect()->route('receiving.index');
        }

        if ($page === 'shipping') {
            if ($subpage === 'create') return redirect()->route('shipping.create');
            return redirect()->route('shipping.index');
        }

        if ($page === 'items') return redirect()->route('items.index');

        if ($page === 'inventory') {
            if ($subpage === 'summary') return redirect()->route('inventory.summary');
            if ($subpage === 'detail') return redirect()->route('inventory.detail');
        }

        return view('generic.index', ['title' => 'Warehouse: ' . ucfirst($subpage ?? $page), 'api_endpoint' => '/api/warehouse']);
    });

    // Accounting — Bank Book Balance
    Route::get('/accounting/bank/book-balance', [BankBookBalanceController::class, 'index'])->name('accounting.bank.book-balance');
    Route::post('/accounting/bank/book-balance/view', [BankBookBalanceController::class, 'view'])->name('accounting.bank.book-balance.view');
    Route::get('/accounting/bank/book-balance/print', [BankBookBalanceController::class, 'printReport'])->name('accounting.bank.book-balance.print');
    Route::get('/accounting/bank/book-balance/export-excel', [BankBookBalanceController::class, 'exportExcel'])->name('accounting.bank.book-balance.export-excel');

    // Accounting — Bank Outstanding
    Route::get('/accounting/bank/outstanding', [BankOutstandingController::class, 'index'])->name('accounting.bank.outstanding');
    Route::post('/accounting/bank/outstanding/view', [BankOutstandingController::class, 'view'])->name('accounting.bank.outstanding.view');
    Route::get('/accounting/bank/outstanding/print', [BankOutstandingController::class, 'printReport'])->name('accounting.bank.outstanding.print');
    Route::get('/accounting/bank/outstanding/export-excel', [BankOutstandingController::class, 'exportExcel'])->name('accounting.bank.outstanding.export-excel');

    // Accounting — Bank Reconciliation
    Route::get('/accounting/bank/reconciliation', [BankReconciliationController::class, 'index'])->name('accounting.bank.reconciliation');
    Route::post('/accounting/bank/reconciliation/view', [BankReconciliationController::class, 'view'])->name('accounting.bank.reconciliation.view');
    Route::post('/accounting/bank/reconciliation/reconcile', [BankReconciliationController::class, 'reconcile'])->name('accounting.bank.reconciliation.reconcile');
    Route::post('/accounting/bank/reconciliation/unreconcile', [BankReconciliationController::class, 'unreconcile'])->name('accounting.bank.reconciliation.unreconcile');
    Route::get('/accounting/bank/reconciliation/print', [BankReconciliationController::class, 'printReport'])->name('accounting.bank.reconciliation.print');
    Route::get('/accounting/bank/reconciliation/export-excel', [BankReconciliationController::class, 'exportExcel'])->name('accounting.bank.reconciliation.export-excel');

    // Accounting — Bank Batch Process
    Route::get('/accounting/bank/batch-process', [BankBatchProcessController::class, 'index'])->name('accounting.bank.batch-process');
    Route::post('/accounting/bank/batch-process/search', [BankBatchProcessController::class, 'search'])->name('accounting.bank.batch-process.search');
    Route::post('/accounting/bank/batch-process/execute', [BankBatchProcessController::class, 'execute'])->name('accounting.bank.batch-process.execute');
    Route::post('/accounting/bank/batch-process/log', [BankBatchProcessController::class, 'log'])->name('accounting.bank.batch-process.log');
    Route::post('/accounting/bank/batch-process/log-detail', [BankBatchProcessController::class, 'logDetail'])->name('accounting.bank.batch-process.log-detail');
    Route::get('/accounting/bank/batch-process/export-excel', [BankBatchProcessController::class, 'exportExcel'])->name('accounting.bank.batch-process.export-excel');

    // Accounting — Clear Check by Excel
    Route::get('/accounting/bank/clear-check-by-excel', [ClearCheckByExcelController::class, 'index'])->name('accounting.bank.clear-check-excel');
    Route::post('/accounting/bank/clear-check-by-excel/upload', [ClearCheckByExcelController::class, 'upload'])->name('accounting.bank.clear-check-excel.upload');
    Route::post('/accounting/bank/clear-check-by-excel/process', [ClearCheckByExcelController::class, 'process'])->name('accounting.bank.clear-check-excel.process');
    Route::post('/accounting/bank/clear-check-by-excel/history', [ClearCheckByExcelController::class, 'history'])->name('accounting.bank.clear-check-excel.history');
    Route::post('/accounting/bank/clear-check-by-excel/log-detail', [ClearCheckByExcelController::class, 'logDetail'])->name('accounting.bank.clear-check-excel.log-detail');
    Route::get('/accounting/bank/clear-check-by-excel/export-excel', [ClearCheckByExcelController::class, 'exportExcel'])->name('accounting.bank.clear-check-excel.export-excel');

    // Accounting — Check / Deposit Report
    Route::get('/accounting/bank/check-deposit-report', [CheckDepositReportController::class, 'index'])->name('accounting.bank.check-deposit-report');
    Route::post('/accounting/bank/check-deposit-report/view', [CheckDepositReportController::class, 'view'])->name('accounting.bank.check-deposit-report.view');
    Route::get('/accounting/bank/check-deposit-report/print', [CheckDepositReportController::class, 'printReport'])->name('accounting.bank.check-deposit-report.print');
    Route::get('/accounting/bank/check-deposit-report/export-excel', [CheckDepositReportController::class, 'exportExcel'])->name('accounting.bank.check-deposit-report.export-excel');

    // Accounting — Balance Sheet Report
    Route::get('/accounting/report/balance-sheet', [BalanceSheetController::class, 'index'])->name('accounting.report.balance-sheet');
    Route::post('/accounting/report/balance-sheet/view', [BalanceSheetController::class, 'view'])->name('accounting.report.balance-sheet.view');
    Route::get('/accounting/report/balance-sheet/print', [BalanceSheetController::class, 'printReport'])->name('accounting.report.balance-sheet.print');
    Route::get('/accounting/report/balance-sheet/export-excel', [BalanceSheetController::class, 'exportExcel'])->name('accounting.report.balance-sheet.export-excel');

    // Accounting — Trial Balance Report
    Route::get('/accounting/report/trial-balance', [TrialBalanceController::class, 'index'])->name('accounting.report.trial-balance');
    Route::post('/accounting/report/trial-balance/view', [TrialBalanceController::class, 'view'])->name('accounting.report.trial-balance.view');
    Route::get('/accounting/report/trial-balance/print', [TrialBalanceController::class, 'printReport'])->name('accounting.report.trial-balance.print');
    Route::get('/accounting/report/trial-balance/export-excel', [TrialBalanceController::class, 'exportExcel'])->name('accounting.report.trial-balance.export-excel');

    // Accounting — General Ledger Report
    Route::get('/accounting/report/general-ledger', [GeneralLedgerController::class, 'index'])->name('accounting.report.general-ledger');
    Route::post('/accounting/report/general-ledger/view', [GeneralLedgerController::class, 'view'])->name('accounting.report.general-ledger.view');
    Route::get('/accounting/report/general-ledger/print', [GeneralLedgerController::class, 'printReport'])->name('accounting.report.general-ledger.print');
    Route::get('/accounting/report/general-ledger/export-excel', [GeneralLedgerController::class, 'exportExcel'])->name('accounting.report.general-ledger.export-excel');

    // Accounting — Aging Report
    Route::get('/accounting/report/aging-report', [AgingReportController::class, 'index'])->name('accounting.report.aging-report');
    Route::post('/accounting/report/aging-report/view', [AgingReportController::class, 'view'])->name('accounting.report.aging-report.view');
    Route::get('/accounting/report/aging-report/print', [AgingReportController::class, 'printReport'])->name('accounting.report.aging-report.print');
    Route::get('/accounting/report/aging-report/export-excel', [AgingReportController::class, 'exportExcel'])->name('accounting.report.aging-report.export-excel');

    // Accounting — Income Statement
    Route::get('/accounting/report/income-statement', [IncomeStatementController::class, 'index'])->name('accounting.report.income-statement');
    Route::post('/accounting/report/income-statement/view', [IncomeStatementController::class, 'view'])->name('accounting.report.income-statement.view');
    Route::get('/accounting/report/income-statement/print', [IncomeStatementController::class, 'printReport'])->name('accounting.report.income-statement.print');
    Route::get('/accounting/report/income-statement/export-excel', [IncomeStatementController::class, 'exportExcel'])->name('accounting.report.income-statement.export-excel');

    // Accounting — Revenue / Cost Report
    Route::get('/accounting/report/revenue-cost', [RevenueCostController::class, 'index'])->name('accounting.report.revenue-cost');
    Route::post('/accounting/report/revenue-cost/view', [RevenueCostController::class, 'view'])->name('accounting.report.revenue-cost.view');
    Route::get('/accounting/report/revenue-cost/print', [RevenueCostController::class, 'printReport'])->name('accounting.report.revenue-cost.print');
    Route::get('/accounting/report/revenue-cost/export-excel', [RevenueCostController::class, 'exportExcel'])->name('accounting.report.revenue-cost.export-excel');

    // Accounting — Agent / Local Statement
    Route::get('/accounting/report/agent-local-statement', [AgentLocalStatementController::class, 'index'])->name('accounting.report.agent-local-statement');
    Route::post('/accounting/report/agent-local-statement/view', [AgentLocalStatementController::class, 'view'])->name('accounting.report.agent-local-statement.view');
    Route::get('/accounting/report/agent-local-statement/print', [AgentLocalStatementController::class, 'printReport'])->name('accounting.report.agent-local-statement.print');
    Route::get('/accounting/report/agent-local-statement/export-excel', [AgentLocalStatementController::class, 'exportExcel'])->name('accounting.report.agent-local-statement.export-excel');

    // Accounting — Freight Statement
    Route::get('/accounting/report/freight-statement', [FreightStatementController::class, 'index'])->name('accounting.report.freight-statement');
    Route::post('/accounting/report/freight-statement/view', [FreightStatementController::class, 'view'])->name('accounting.report.freight-statement.view');
    Route::get('/accounting/report/freight-statement/print', [FreightStatementController::class, 'printReport'])->name('accounting.report.freight-statement.print');
    Route::get('/accounting/report/freight-statement/export-excel', [FreightStatementController::class, 'exportExcel'])->name('accounting.report.freight-statement.export-excel');

    // Accounting — 1099 Report
    Route::get('/accounting/report/1099-report', [OneZeroNineNineController::class, 'index'])->name('accounting.report.1099-report');
    Route::post('/accounting/report/1099-report/view', [OneZeroNineNineController::class, 'view'])->name('accounting.report.1099-report.view');
    Route::get('/accounting/report/1099-report/print', [OneZeroNineNineController::class, 'printReport'])->name('accounting.report.1099-report.print');
    Route::get('/accounting/report/1099-report/export-excel', [OneZeroNineNineController::class, 'exportExcel'])->name('accounting.report.1099-report.export-excel');

    // Accounting — Journal Report
    Route::get('/accounting/report/journal-report', [JournalReportController::class, 'index'])->name('accounting.report.journal-report');
    Route::post('/accounting/report/journal-report/view', [JournalReportController::class, 'apiView'])->name('accounting.report.journal-report.view');
    Route::get('/accounting/report/journal-report/preview', [JournalReportController::class, 'preview'])->name('accounting.report.journal-report.preview');
    Route::get('/accounting/report/journal-report/print', [JournalReportController::class, 'printReport'])->name('accounting.report.journal-report.print');
    Route::get('/accounting/report/journal-report/export-excel', [JournalReportController::class, 'exportExcel'])->name('accounting.report.journal-report.export-excel');

    // Accounting — Journal Entry
    Route::get('/accounting/journal/entry', [JournalEntryController::class, 'index'])->name('accounting.journal.entry');
    Route::post('/accounting/journal/entry/store', [JournalEntryController::class, 'store'])->name('accounting.journal.entry.store');
    Route::put('/accounting/journal/entry/{id}', [JournalEntryController::class, 'update'])->name('accounting.journal.entry.update');
    Route::delete('/accounting/journal/entry/{id}', [JournalEntryController::class, 'destroy'])->name('accounting.journal.entry.destroy');
    Route::get('/accounting/journal/list', [JournalEntryController::class, 'list'])->name('accounting.journal.list');
    Route::get('/accounting/journal/export-excel', [JournalEntryController::class, 'exportExcel'])->name('accounting.journal.export-excel');

    // Accounting — Block / Unblock (BEFORE {id} wildcard)
    Route::get('/accounting/journal/block', [AccountingBlockController::class, 'index'])->name('accounting.journal.block');
    Route::post('/accounting/journal/block/apply', [AccountingBlockController::class, 'apply'])->name('accounting.journal.block.apply');

    // Accounting — Block Maintenance (BEFORE {id} wildcard)
    Route::get('/accounting/journal/block/maintenance', [AccountingBlockMaintenanceController::class, 'index'])->name('accounting.block-maintenance');
    Route::post('/accounting/journal/block/maintenance/search', [AccountingBlockMaintenanceController::class, 'search'])->name('accounting.block-maintenance.search');
    Route::post('/accounting/journal/block/maintenance/apply', [AccountingBlockMaintenanceController::class, 'apply'])->name('accounting.block-maintenance.apply');
    Route::get('/accounting/journal/block/maintenance/export-excel', [AccountingBlockMaintenanceController::class, 'exportExcel'])->name('accounting.block-maintenance.export-excel');

    // Accounting — Block History (BEFORE {id} wildcard)
    Route::get('/accounting/journal/block/history', [AccountingBlockHistoryController::class, 'index'])->name('accounting.block-history');
    Route::get('/accounting/journal/block/history/search', [AccountingBlockHistoryController::class, 'search'])->name('accounting.block-history.search');
    Route::get('/accounting/journal/block/history/export-excel', [AccountingBlockHistoryController::class, 'exportExcel'])->name('accounting.block-history.export-excel');

    // Accounting — Year End Closing (BEFORE {id} wildcard)
    Route::get('/accounting/year-end-closing', [YearEndClosingController::class, 'index'])->name('accounting.year-end-closing');
    Route::post('/accounting/year-end-closing/perform', [YearEndClosingController::class, 'perform'])->name('accounting.year-end-closing.perform');
    Route::post('/accounting/year-end-closing/cancel', [YearEndClosingController::class, 'cancel'])->name('accounting.year-end-closing.cancel');
    Route::get('/accounting/year-end-closing/status', [YearEndClosingController::class, 'status'])->name('accounting.year-end-closing.status');
    Route::post('/accounting/year-end-closing/check-uncleared', [YearEndClosingController::class, 'checkUncleared'])->name('accounting.year-end-closing.check-uncleared');
    Route::get('/accounting/year-end-closing/detail/{id}', [YearEndClosingController::class, 'detail'])->name('accounting.year-end-closing.detail');

    Route::get('/accounting/journal/{id}', [JournalEntryController::class, 'show'])->name('accounting.journal.show');
    Route::get('/api/gl-accounts', [JournalEntryController::class, 'getGlAccounts'])->name('accounting.gl-accounts');
    Route::get('/api/next-entry-no', [JournalEntryController::class, 'getNextEntryNo'])->name('accounting.next-entry-no');

    // Accounting — General Journal
    Route::get('/accounting/general-journal', [GeneralJournalController::class, 'index'])->name('accounting.general-journal');
    Route::get('/accounting/general-journal/print', [GeneralJournalController::class, 'printReport'])->name('accounting.general-journal.print');
    Route::get('/accounting/general-journal/export', [GeneralJournalController::class, 'exportExcel'])->name('accounting.general-journal.export');
    Route::delete('/accounting/general-journal/delete', [GeneralJournalController::class, 'destroy'])->name('accounting.general-journal.delete');

    // Accounting — specific literal routes BEFORE {invoice} wildcard
    Route::get('/accounting/invoice', [InvoiceController::class, 'index'])->name('accounting.invoices.index');
    Route::get('/accounting/invoice/create', [InvoiceController::class, 'create'])->name('accounting.invoices.create');
    Route::post('/accounting/invoice', [InvoiceController::class, 'store'])->name('accounting.invoices.store');
    Route::get('/accounting/invoice/export-csv', [InvoiceController::class, 'exportCsv'])->name('accounting.invoices.export-csv');
    Route::match(['GET', 'POST', 'DELETE'], '/accounting/invoice/bulk-delete', [InvoiceController::class, 'bulkDelete'])->name('accounting.invoices.bulk-delete');
    Route::post('/accounting/invoice/batch-update-status', [InvoiceController::class, 'batchUpdateStatus'])->name('accounting.invoices.batch-update-status');
    Route::patch('/accounting/invoice/{invoice}/color', [InvoiceController::class, 'updateColor'])->name('accounting.invoices.update-color');
    Route::get('/accounting/invoice/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('accounting.invoices.duplicate');
    // Invoice Documents
    Route::post('/accounting/invoice/{invoice}/documents', [InvoiceController::class, 'uploadDocument'])->name('accounting.invoices.documents.store');
    Route::delete('/accounting/invoice/documents/{document}', [InvoiceController::class, 'deleteDocument'])->name('accounting.invoices.documents.destroy');
    Route::get('/accounting/invoice/documents/{document}/download', [InvoiceController::class, 'downloadDocument'])->name('accounting.invoices.documents.download');
    Route::get('/accounting/invoice/verify-queue', [InvoiceController::class, 'verifyQueue'])->name('accounting.invoices.verify-queue');
    // Wildcard {invoice} routes — must come AFTER literal routes
    Route::get('/accounting/invoice/{invoice}', [InvoiceController::class, 'show'])->name('accounting.invoices.show');
    Route::get('/accounting/invoice/{invoice}/edit', [InvoiceController::class, 'edit'])->name('accounting.invoices.edit');
    Route::put('/accounting/invoice/{invoice}', [InvoiceController::class, 'update'])->name('accounting.invoices.update');
    Route::post('/accounting/invoice/{invoice}/verify', [InvoiceController::class, 'verify'])->name('accounting.invoices.verify');
    Route::post('/accounting/invoice/{invoice}/reject', [InvoiceController::class, 'reject'])->name('accounting.invoices.reject');
    Route::delete('/accounting/invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('accounting.invoices.destroy');
    // Accounting — G&A Expense (AP Invoices)
    Route::get('/accounting/ga-expense-list', [GaExpenseController::class, 'index'])->name('accounting.ga-expense.index');
    Route::get('/accounting/ga-expense/create', [GaExpenseController::class, 'create'])->name('accounting.ga-expense.create');
    Route::post('/accounting/ga-expense', [GaExpenseController::class, 'store'])->name('accounting.ga-expense.store');
    Route::get('/accounting/ga-expense/export-csv', [GaExpenseController::class, 'exportCsv'])->name('accounting.ga-expense.export-csv');
    Route::match(['GET', 'POST', 'DELETE'], '/accounting/ga-expense/bulk-delete', [GaExpenseController::class, 'bulkDelete'])->name('accounting.ga-expense.bulk-delete');
    Route::post('/accounting/ga-expense/batch-update-status', [GaExpenseController::class, 'batchUpdateStatus'])->name('accounting.ga-expense.batch-update-status');
    Route::patch('/accounting/ga-expense/{ga_expense}/color', [GaExpenseController::class, 'updateColor'])->name('accounting.ga-expense.update-color');
    Route::get('/accounting/ga-expense/{ga_expense}/duplicate', [GaExpenseController::class, 'duplicate'])->name('accounting.ga-expense.duplicate');
    Route::get('/accounting/ga-expense/{ga_expense}', [GaExpenseController::class, 'show'])->name('accounting.ga-expense.show');
    Route::get('/accounting/ga-expense/{ga_expense}/edit', [GaExpenseController::class, 'edit'])->name('accounting.ga-expense.edit');
    Route::put('/accounting/ga-expense/{ga_expense}', [GaExpenseController::class, 'update'])->name('accounting.ga-expense.update');
    Route::delete('/accounting/ga-expense/{ga_expense}', [GaExpenseController::class, 'destroy'])->name('accounting.ga-expense.destroy');

    Route::get('/accounting/payment/received-list', [AccountingPaymentController::class, 'receivedList'])->name('accounting.payment-received-list');
    Route::get('/accounting/payment/received-list/export', [AccountingPaymentController::class, 'exportReceivedList'])->name('accounting.payment-received-list.export');
    Route::match(['GET', 'POST', 'DELETE'], '/accounting/payment/received-list/bulk-delete', [AccountingPaymentController::class, 'bulkDeleteReceived'])->name('accounting.payment-received-list.bulk-delete');
    Route::patch('/accounting/payment/received-list/{payment}/color', [AccountingPaymentController::class, 'updateColor'])->name('accounting.payment-received-list.update-color');
    Route::get('/accounting/payment/made-list', [AccountingPaymentController::class, 'madeList'])->name('accounting.payment-made-list');
    Route::get('/accounting/payment/made-list/export', [AccountingPaymentController::class, 'exportMadeList'])->name('accounting.payment-made-list.export');
    Route::match(['GET', 'POST', 'DELETE'], '/accounting/payment/made-list/bulk-delete', [AccountingPaymentController::class, 'bulkDeleteMade'])->name('accounting.payment-made-list.bulk-delete');
    Route::patch('/accounting/payment/made-list/{payment}/color', [AccountingPaymentController::class, 'updateColor'])->name('accounting.payment-made-list.update-color');
    Route::get('/accounting/ar-payment-list', function () { return redirect()->route('accounting.payment-received-list'); })->name('accounting.ar-payment-list');
    Route::get('/accounting/ap-payment-list', function () { return redirect()->route('accounting.payment-made-list'); })->name('accounting.ap-payment-list');
    Route::get('/accounting/payment/receive', [AccountingPaymentController::class, 'create'])->name('accounting.payment-receive');
    Route::get('/accounting/payment/make', [AccountingPaymentController::class, 'create'])->name('accounting.payment-make');
    Route::post('/accounting/payment', [AccountingPaymentController::class, 'store'])->name('accounting.payment.store');
    Route::get('/accounting/payment/{payment}/edit', [AccountingPaymentController::class, 'edit'])->name('accounting.payment.edit');
    Route::put('/accounting/payment/{payment}', [AccountingPaymentController::class, 'update'])->name('accounting.payment.update');

    // Payment Documents
    Route::post('/accounting/payment/{payment}/documents', [AccountingPaymentController::class, 'uploadDocument'])->name('accounting.payment.documents.store');
    Route::delete('/accounting/payment/documents/{document}', [AccountingPaymentController::class, 'deleteDocument'])->name('accounting.payment.documents.destroy');
    Route::get('/accounting/payment/documents/{document}/download', [AccountingPaymentController::class, 'downloadDocument'])->name('accounting.payment.documents.download');

    // Payment Memos
    Route::post('/accounting/payment/{payment}/memos', [AccountingPaymentController::class, 'addMemo'])->name('accounting.payment.memos.store');
    Route::put('/accounting/payment/memos/{memo}', [AccountingPaymentController::class, 'updateMemo'])->name('accounting.payment.memos.update');
    Route::delete('/accounting/payment/memos/{memo}', [AccountingPaymentController::class, 'deleteMemo'])->name('accounting.payment.memos.destroy');

    Route::get('/accounting/{page?}/{subpage?}/{action?}', function ($page = 'report', $subpage = null, $action = null) {
        if ($page === 'ar-payment-list') return redirect()->route('accounting.payment-received-list');
        if ($page === 'ap-payment-list') return redirect()->route('accounting.payment-made-list');
        if ($page === 'invoice') return redirect()->route('accounting.invoices.index');
        if ($page === 'ga-expense-list') return redirect()->route('accounting.ga-expense.index');
        if ($page === 'ga-expense' && $subpage === 'create') return redirect()->route('accounting.ga-expense.create');
        if ($page === 'ga-invoice' && $subpage === 'create') {
            $tradePartners = \App\Models\TradePartner::orderBy('name')->get();
            $currencies = \App\Models\Currency::all();
            $offices = \App\Models\Office::where('is_active', true)->get();
            $users = \App\Models\User::all();
            return view('accounting.create-ga-invoice', compact('tradePartners', 'currencies', 'offices', 'users'));
        }
        if ($page === 'payment' && $subpage === 'receive') return redirect()->route('accounting.payment-receive');
        if ($page === 'payment' && $subpage === 'make') return redirect()->route('accounting.payment-make');
        if ($page === 'payment' && $subpage === 'received-list') return redirect()->route('accounting.payment-received-list');
        if ($page === 'payment' && $subpage === 'made-list') return redirect()->route('accounting.payment-made-list');
        if ($page === 'bank' && $subpage === 'book-balance') return redirect()->route('accounting.bank.book-balance');
        if ($page === 'bank' && $subpage === 'outstanding') return redirect()->route('accounting.bank.outstanding');
        if ($page === 'bank' && $subpage === 'reconciliation') return redirect()->route('accounting.bank.reconciliation');
        if ($page === 'bank' && $subpage === 'batch-process') return redirect()->route('accounting.bank.batch-process');
        if ($page === 'bank' && $subpage === 'clear-check-by-excel') return redirect()->route('accounting.bank.clear-check-excel');
        if ($page === 'bank' && $subpage === 'check-deposit-report') return redirect()->route('accounting.bank.check-deposit-report');
        return view('generic.index', ['title' => 'Accounting: ' . ucfirst($page), 'api_endpoint' => '/api/accounting-payments']);
    });

    // Reports
    Route::prefix('report')->group(function () {
        Route::get('/advanced', [\App\Http\Controllers\ReportController::class, 'advancedReport'])->name('report.advanced');
        Route::get('/advanced/data', [\App\Http\Controllers\ReportController::class, 'advancedReportData'])->name('report.advanced.data');
        Route::get('/volume-profit', [\App\Http\Controllers\ReportController::class, 'volumeProfit'])->name('report.volume-profit');
        Route::get('/volume-profit/data', [\App\Http\Controllers\ReportController::class, 'volumeProfitData'])->name('report.volume-profit.data');
        Route::get('/volume-profit-chart', [\App\Http\Controllers\ReportController::class, 'volumeProfitChart'])->name('report.volume-profit-chart');
        Route::get('/volume-profit-chart/data', [\App\Http\Controllers\ReportController::class, 'volumeProfitChartData'])->name('report.volume-profit-chart.data');
        Route::get('/employee-performance', [\App\Http\Controllers\ReportController::class, 'employeePerformance'])->name('report.employee-performance');
        Route::get('/employee-performance/data', [\App\Http\Controllers\ReportController::class, 'employeePerformanceData'])->name('report.employee-performance.data');
        Route::get('/user-log', [\App\Http\Controllers\ReportController::class, 'userLog'])->name('report.user-log');
        Route::get('/user-log/data', [\App\Http\Controllers\ReportController::class, 'userLogData'])->name('report.user-log.data');
        Route::get('/user-log/print', [\App\Http\Controllers\ReportController::class, 'userLogPrint'])->name('report.user-log.print');
        Route::get('/shipment', [\App\Http\Controllers\ReportController::class, 'shipmentReport'])->name('report.shipment');
        Route::get('/shipment/data', [\App\Http\Controllers\ReportController::class, 'shipmentReportData'])->name('report.shipment.data');
        Route::get('/shipment/download', [\App\Http\Controllers\ReportController::class, 'shipmentReportDownload'])->name('report.shipment.download');
        Route::get('/container-storage', [\App\Http\Controllers\ReportController::class, 'containerStorage']);
        Route::get('/container-storage/print', [\App\Http\Controllers\ReportController::class, 'containerStoragePrint']);

        Route::get('/{page?}', function ($page = 'advanced') {
            return view('generic.index', ['title' => 'Report: ' . ucfirst($page), 'api_endpoint' => '/api/shipments']);
        });
    });

    // System Settings & Configs
    Route::get('/settings/{page?}', function ($page = 'accounting') {
        return view('generic.index', ['title' => 'Settings: ' . ucfirst($page), 'api_endpoint' => '/api/offices']);
    });
    Route::get('/useful-links/{page?}', function ($page = 'pier-pass') {
        return view('generic.index', ['title' => 'Links: ' . ucfirst($page), 'api_endpoint' => '/api/tools']);
    });
    Route::get('/edi-import/{page?}', function ($page = 'fast-pro') {
        return view('generic.index', ['title' => 'EDI Import: ' . ucfirst($page), 'api_endpoint' => '/api/tools']);
    });
    Route::get('/import-update/{page?}', function ($page = 'shipment') {
        return view('generic.index', ['title' => 'Data Import: ' . ucfirst($page), 'api_endpoint' => '/api/tools']);
    });
    Route::get('/operations/{page?}', function ($page = 'list') {
        return view('generic.index', ['title' => 'Miscellaneous Operations: ' . ucfirst($page), 'api_endpoint' => '/api/operations']);
    });

    // Business Management
    Route::get('/action-center', function () {
        return view('generic.index', ['title' => 'Action Center Tasks', 'api_endpoint' => '/api/action-center']);
    });

    Route::prefix('sales')->group(function () {
        Route::get('/', function () { return view('sales.index'); });
        Route::get('/leads', [LeadController::class, 'index']);
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
        Route::put('/leads/{id}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');

        Route::get('/pipeline', [LeadController::class, 'pipeline']);

        Route::get('/quotations', [QuotationController::class, 'index']);
        Route::get('/quotation', [QuotationController::class, 'create']);
        Route::get('/quotation/create', [QuotationController::class, 'create'])->name('sales.quotations.create');
        Route::get('/quotation/{id}/edit', [QuotationController::class, 'edit'])->name('sales.quotations.edit');
        Route::get('/quotation/list', [QuotationController::class, 'index'])->name('sales.quotations.list');

        Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
        Route::put('/quotations/{id}', [QuotationController::class, 'update'])->name('quotations.update');
        Route::delete('/quotations/{id}', [QuotationController::class, 'destroy'])->name('quotations.destroy');

        Route::post('/quotations/{id}/documents', [QuotationController::class, 'uploadDocument']);
        Route::delete('/quotations/documents/{document}', [QuotationController::class, 'deleteDocument']);
        Route::get('/quotations/documents/{document}/download', [QuotationController::class, 'downloadDocument']);
    });

    Route::get('/vessel/create', function() {
        return view('generic.index', ['title' => 'Create Vessel', 'api_endpoint' => '/api/vessels']);
    })->name('vessel.create');

    Route::get('/port/create', function() {
        return view('generic.index', ['title' => 'Create Port', 'api_endpoint' => '/api/ports']);
    })->name('port.create');

    Route::get('/trade-partner/list', [TradePartnerController::class, 'index'])->name('trade-partner.index');
    Route::get('/trade-partner/create', [TradePartnerController::class, 'create'])->name('trade-partner.create');
    Route::post('/trade-partner', [TradePartnerController::class, 'store'])->name('trade-partner.store');
    Route::get('/trade-partner/{trade_partner}/edit', [TradePartnerController::class, 'edit'])->name('trade-partner.edit');
    Route::put('/trade-partner/{trade_partner}', [TradePartnerController::class, 'update'])->name('trade-partner.update');
    Route::delete('/trade-partner/{trade_partner}', [TradePartnerController::class, 'destroy'])->name('trade-partner.destroy');

    Route::post('/trade-partner/{trade_partner}/documents', [TradePartnerController::class, 'uploadDocument']);
    Route::delete('/trade-partner/{trade_partner}/documents/{document}', [TradePartnerController::class, 'deleteDocument']);
    Route::post('/trade-partner/{trade_partner}/documents/{document}/email', [TradePartnerController::class, 'emailDocument']);
    Route::get('/trade-partner/{trade_partner}/activity-logs', [TradePartnerController::class, 'activityLogs']);
    Route::post('/trade-partner/{trade_partner}/check-bond', [TradePartnerController::class, 'checkBondStatus']);
    Route::post('/trade-partner/{trade_partner}/grant-access', [TradePartnerController::class, 'grantAccess'])->name('trade-partner.grant-access');

    Route::post('/trade-partner/bulk-delete', [TradePartnerController::class, 'bulkDelete'])->name('trade-partner.bulk-delete');
    Route::post('/trade-partner/bulk-restore', [TradePartnerController::class, 'bulkRestore'])->name('trade-partner.bulk-restore');
    Route::get('/trade-partner/export-csv', [TradePartnerController::class, 'exportCsv'])->name('trade-partner.export-csv');

    Route::get('/trade-partner/credit-entry', [TradePartnerCreditController::class, 'index'])->name('trade-partner.credit-entry');
    Route::get('/trade-partner/credit-entry/export-csv', [TradePartnerCreditController::class, 'exportCsv'])->name('trade-partner.credit-entry.export-csv');
    Route::post('/trade-partner/credit-entry/save', [TradePartnerCreditController::class, 'saveCreditEntries'])->name('trade-partner.credit-entry.save');
    Route::get('/trade-partner/mapping-list', [TradePartnerMappingController::class, 'index'])->name('trade-partner.mapping-list');
    Route::post('/trade-partner/mapping/store', [TradePartnerMappingController::class, 'store'])->name('trade-partner.mapping.store');
    Route::put('/trade-partner/mapping/{trade_partner_mapping}', [TradePartnerMappingController::class, 'update'])->name('trade-partner.mapping.update')->whereNumber('trade_partner_mapping');
    Route::delete('/trade-partner/mapping/{trade_partner_mapping}', [TradePartnerMappingController::class, 'destroy'])->name('trade-partner.mapping.destroy')->whereNumber('trade_partner_mapping');
    Route::post('/trade-partner/mapping/bulk-delete', [TradePartnerMappingController::class, 'bulkDelete'])->name('trade-partner.mapping.bulk-delete');

    // Credit Limit Groups CRUD (via TradePartnerCreditController)
    Route::get('/trade-partner/credit-limit-groups', [TradePartnerCreditController::class, 'listGroups'])->name('trade-partner.credit-limit-groups.list');
    Route::post('/trade-partner/credit-limit-groups', [TradePartnerCreditController::class, 'storeGroup'])->name('trade-partner.credit-limit-groups.store');
    Route::put('/trade-partner/credit-limit-groups/{group}', [TradePartnerCreditController::class, 'updateGroup'])->name('trade-partner.credit-limit-groups.update');
    Route::delete('/trade-partner/credit-limit-groups/{group}', [TradePartnerCreditController::class, 'destroyGroup'])->name('trade-partner.credit-limit-groups.destroy');
    Route::post('/trade-partner/credit-limit-groups/bulk-delete', [TradePartnerCreditController::class, 'bulkDeleteGroups'])->name('trade-partner.credit-limit-groups.bulk-delete');


    Route::prefix('crm')->group(function () {
        Route::get('/', function() { return view('crm.index'); });
        Route::post('/', [TradePartnerController::class, 'store'])->name('crm.store');
        Route::put('/{id}', [TradePartnerController::class, 'update'])->name('crm.update');
        Route::delete('/{id}', [TradePartnerController::class, 'destroy'])->name('crm.destroy');
        Route::get('/clients/{id}', function ($id) { return view('crm.client.show', ['id' => $id]); });
    });

    Route::prefix('shipments')->group(function () {
        Route::get('/', function () { return view('shipments.index'); });
        Route::get('/{id}', function ($id) { return view('shipments.show', ['id' => $id]); });
    });

    Route::prefix('bookings')->group(function () {
        Route::get('/', function () { return view('bookings.index'); });
    });

    // Financials & Admin
    Route::get('/accounting', function () { return view('generic.index', ['title' => 'Accounting & Finance', 'api_endpoint' => '/api/accounting']); });
    Route::get('/reports', function () { return view('generic.index', ['title' => 'System Reports', 'api_endpoint' => '/api/reports']); });
    Route::get('/settings', function () { return view('generic.index', ['title' => 'Global Settings', 'api_endpoint' => '/api/settings']); });
    Route::get('/tools', function () { return view('generic.index', ['title' => 'System Tools & EDI', 'api_endpoint' => '/api/tools']); });

    // Quotation bulk & inline operations
    Route::post('/quotations/bulk-delete', [QuotationController::class, 'bulkDelete'])->name('quotations.bulk-delete');
    Route::post('/quotations/bulk-status', [QuotationController::class, 'bulkUpdateStatus'])->name('quotations.bulk-status');
    Route::patch('/quotations/{id}/status', [QuotationController::class, 'updateSingleStatus'])->name('quotations.update-status');
});

// ============================================
// Super Admin Routes
// ============================================
Route::middleware(['auth', 'verified', 'super-admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'reports'])->name('dashboard.reports');
    Route::get('/system-status', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'systemStatus'])->name('dashboard.system-status');
    
    // Company Management
    Route::get('/companies', [SuperAdminCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [SuperAdminCompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [SuperAdminCompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/export-csv', [SuperAdminCompanyController::class, 'exportCsv'])->name('companies.export-csv');
    Route::get('/companies/{company}', [SuperAdminCompanyController::class, 'show'])->name('companies.show');
    Route::get('/companies/{company}/edit', [SuperAdminCompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [SuperAdminCompanyController::class, 'update'])->name('companies.update');
    Route::patch('/companies/{company}/toggle-status', [SuperAdminCompanyController::class, 'toggleStatus'])->name('companies.toggle-status');
    Route::post('/companies/{company}/reset-password', [SuperAdminCompanyController::class, 'resetPassword'])->name('companies.reset-password');

    // Users (Accounts & Access)
    Route::resource('users', App\Http\Controllers\SuperAdmin\SuperAdminUserController::class);
    Route::patch('/users/{user}/toggle-status', [App\Http\Controllers\SuperAdmin\SuperAdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/reset-password', [App\Http\Controllers\SuperAdmin\SuperAdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/login-as', [App\Http\Controllers\SuperAdmin\SuperAdminUserController::class, 'loginAs'])->name('users.login-as');

    // Roles & Rules
    Route::resource('roles', App\Http\Controllers\SuperAdmin\SuperAdminRoleController::class);
    Route::post('/roles/{role}/clone', [App\Http\Controllers\SuperAdmin\SuperAdminRoleController::class, 'clone'])->name('roles.clone');
    Route::resource('permissions', App\Http\Controllers\SuperAdmin\SuperAdminPermissionController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/role-permissions', [App\Http\Controllers\SuperAdmin\SuperAdminMatrixController::class, 'index'])->name('role-permissions.index');
    Route::post('/role-permissions', [App\Http\Controllers\SuperAdmin\SuperAdminMatrixController::class, 'update'])->name('role-permissions.update');
    
    // Audit Log
    Route::get('/audit-log', [SuperAdminCompanyController::class, 'auditLog'])->name('audit-log');

    // Bugs & Issues
    Route::get('/bugs', [\App\Http\Controllers\SuperAdmin\BugController::class, 'index'])->name('bugs.index');
    Route::get('/bugs/{ticket}', [\App\Http\Controllers\SuperAdmin\BugController::class, 'show'])->name('bugs.show');
    Route::patch('/bugs/{ticket}/status', [\App\Http\Controllers\SuperAdmin\BugController::class, 'updateStatus'])->name('bugs.update-status');
    Route::post('/bugs/{ticket}/message', [\App\Http\Controllers\SuperAdmin\BugController::class, 'message'])->name('bugs.message');

    // Support Center
    Route::prefix('support-center')->name('support-center.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'store'])->name('store');
        Route::get('/my-tickets', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'myTickets'])->name('my-tickets');
        Route::get('/sla-management', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'slaManagement'])->name('sla-management');
        Route::get('/knowledge-base', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'knowledgeBase'])->name('knowledge-base');
        Route::get('/{ticket}', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'show'])->name('show');
        Route::put('/{ticket}', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'update'])->name('update');
        Route::post('/{ticket}/reply', [\App\Http\Controllers\SuperAdmin\SupportCenterController::class, 'reply'])->name('reply');
    });

    // Module Management
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'store'])->name('store');
        Route::get('/packages', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'packages'])->name('packages');
        Route::post('/packages', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'storePackage'])->name('packages.store');
        Route::put('/packages/{package}', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'updatePackage'])->name('packages.update');
        Route::get('/company', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'companyModules'])->name('company');
        Route::post('/company/{company}/assign', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'assignToCompany'])->name('company.assign');
        Route::post('/company/{company}/apply-package', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'applyPackageToCompany'])->name('company.apply-package');
        Route::get('/feature-flags', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'featureFlags'])->name('feature-flags');
        Route::post('/feature-flags', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'storeFeatureFlag'])->name('feature-flags.store');
        Route::post('/feature-flags/{flag}/toggle', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'toggleFeatureFlag'])->name('feature-flags.toggle');
        Route::get('/{module}', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'show'])->name('show');
        Route::put('/{module}', [\App\Http\Controllers\SuperAdmin\ModuleManagementController::class, 'update'])->name('update');
    });

    // Universal AI Engine
    Route::prefix('ai-settings')->name('ai-settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdmin\AiIntegrationController::class, 'settings'])->name('index');
        Route::post('/update', [\App\Http\Controllers\SuperAdmin\AiIntegrationController::class, 'updateSettings'])->name('update');
        Route::post('/test', [\App\Http\Controllers\SuperAdmin\AiIntegrationController::class, 'testConnection'])->name('test');
    });
});

// ============================================
// Customer Portal Routes
// ============================================
Route::middleware(['auth', 'verified', 'check-status', 'customer-access'])->prefix('portal')->name('customer.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\CustomerPortal\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/shipments', [\App\Http\Controllers\CustomerPortal\ShipmentTrackingController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{id}', [\App\Http\Controllers\CustomerPortal\ShipmentTrackingController::class, 'show'])->name('shipments.show');
});

require __DIR__.'/auth.php';
Route::post('/ocean-import/{ocean_import}/send-batch-email', [App\Http\Controllers\OceanImportController::class, 'sendBatchEmail'])->name('ocean-import.send-batch-email');
Route::post('/ocean-import/{ocean_import}/send-manifest-email', [App\Http\Controllers\OceanImportController::class, 'sendManifestEmail'])->name('ocean-import.send-manifest-email');

Route::get('/ocean-import/{id}/batch-print-view', [App\Http\Controllers\OceanImportController::class, 'batchPrintView'])->name('ocean-import.batch-print-view');

Route::get('/ocean-import/{id}/dev-seg', [App\Http\Controllers\OceanImportController::class, 'devSegView'])->name('ocean-import.dev-seg');

Route::get('/ocean-import/{id}/delivery-order', [App\Http\Controllers\OceanImportController::class, 'deliveryOrderView'])->name('ocean-import.delivery-order');

Route::get('/ocean-import/{id}/profit-summary', [App\Http\Controllers\OceanImportController::class, 'profitSummaryView'])->name('ocean-import.profit-summary');

Route::get('/ocean-import/{id}/profit-detail', [App\Http\Controllers\OceanImportController::class, 'profitDetailView'])->name('ocean-import.profit-detail');


Route::prefix('client-portal')
    ->middleware(['auth', 'client'])
    ->group(function () {

        Route::get('/client', [ClientController::class, 'index'])->name('client.index');
   
        Route::get('/shipments', [ClientController::class, 'shipments'])->name('client.shipments');

        Route::get('/tracking', [ClientController::class, 'tracking']);

        Route::get('/bookings', [ClientController::class, 'bookings']);

        Route::get('/documents', [ClientController::class, 'documents']);

        Route::get('/requests', [ClientController::class, 'requests']);

        Route::get('/messages', [ClientController::class, 'messages']);

        Route::get('/notifications', [ClientController::class, 'notifications']);

        Route::get('/reports', [ClientReportController::class, 'index']);

        Route::get('/company', [ClientController::class, 'company']);

        Route::get('/settings', [ClientController::class, 'settings']);

        Route::get('quotes', [CustomerQuoteController::class, 'index'])->name('cp.quotes.index');

        Route::get('quotes/{id}', [CustomerQuoteController::class, 'show'])->name('cp.quotes.show');

        Route::get('invoices', [CustomerInvoiceController::class, 'index'])->name('cp.invoices.index');

        Route::get('invoices/{id}', [CustomerInvoiceController::class, 'show'])->name('cp.invoices.show');

    });

