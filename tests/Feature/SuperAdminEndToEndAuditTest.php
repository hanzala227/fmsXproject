<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Ticket;
use App\Models\CarrierApiConfig;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminEndToEndAuditTest extends TestCase
{
    protected ?User $superAdmin = null;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'freightX2.0',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
        $this->superAdmin = User::where('role', 'SuperAdmin')->first();
        if (!$this->superAdmin) {
            $this->markTestSkipped('SuperAdmin user not found in MySQL database.');
        }
    }

    // =========================================================
    // MODULE 1: DASHBOARD & REPORTS DEEP LOGIC
    // =========================================================

    public function test_superadmin_dashboard_loads_and_has_kpis()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Freight SaaS 2.0');
        $response->assertSee('Total Companies');
    }

    public function test_superadmin_dashboard_handles_date_snapshots()
    {
        // Snapshot in the past
        $pastDate = '2024-01-01';
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.dashboard', ['date' => $pastDate]));
        $response->assertStatus(200);
        $response->assertSee('Showing platform snapshot as of');

        // Invalid date string must NOT 500
        $invalidDate = 'not-a-valid-date-xyz';
        $badResponse = $this->actingAs($this->superAdmin)->get(route('super-admin.dashboard', ['date' => $invalidDate]));
        $badResponse->assertStatus(200);
    }

    public function test_superadmin_reports_and_csv_export()
    {
        $reportPage = $this->actingAs($this->superAdmin)->get(route('super-admin.dashboard.reports'));
        $reportPage->assertStatus(200);

        $csvExport = $this->actingAs($this->superAdmin)->get(route('super-admin.dashboard.export', [
            'start_date' => '2025-01-01',
            'end_date' => '2026-11-14',
        ]));
        $csvExport->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvExport->headers->get('content-type'));
    }

    // =========================================================
    // MODULE 2: COMPANY & BRANCH MANAGEMENT CRUD & REGULATORY
    // =========================================================

    public function test_company_creation_and_validation()
    {
        $code = 'QA' . rand(1000, 9999);
        $email = 'qa_' . $code . '@freightx.test';
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.companies.store'), [
            'company_name' => 'QA Logistics ' . $code,
            'company_code' => $code,
            'company_email' => $email,
            'company_status' => 'active',
            'subscription_status' => 'trial',
            'admin_name' => 'QA Master Admin',
            'admin_email' => 'admin_' . $email,
            'admin_password' => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
            'user_limit' => 10,
            'storage_limit' => 5,
            'timezone' => 'Asia/Dubai',
        ]);
        $response->assertRedirect();

        $company = Company::where('code', $code)->first();
        $this->assertNotNull($company, 'Company was not stored in database');

        // Cleanup
        User::where('email', 'admin_' . $email)->delete();
        $company->delete();
    }

    public function test_branch_crud_with_iata_and_unlocode()
    {
        $company = Company::first();
        $this->assertNotNull($company, 'Company required for branch testing');

        $bCode = 'BR' . rand(100, 999);
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.branches.store'), [
            'company_id' => $company->id,
            'name' => 'QA Air Freight Station ' . $bCode,
            'code' => $bCode,
            'branch_type' => 'Airport Station',
            'timezone' => 'Asia/Dubai',
            'currency' => 'USD',
            'iata_code' => 'DXB',
            'unlocode' => 'AEDXB',
            'customs_code' => 'CUST-' . $bCode,
            'is_active' => 1,
            'country' => 'United Arab Emirates',
            'city' => 'Dubai',
        ]);
        $response->assertRedirect();

        $branch = Branch::where('code', $bCode)->first();
        $this->assertNotNull($branch, 'Branch was not stored in database');
        $this->assertEquals('DXB', $branch->iata_code);
        $this->assertEquals('AEDXB', $branch->unlocode);

        // Toggle status (JSON endpoint / AJAX or standard redirect)
        $toggle = $this->actingAs($this->superAdmin)->patchJson(route('super-admin.branches.toggle-status', $branch));
        $toggle->assertStatus(200);

        // Cleanup
        $branch->delete();
    }

    // =========================================================
    // MODULE 3: ROLES, PERMISSIONS & SECURITY GUARDS
    // =========================================================

    public function test_role_crud_cloning_and_superadmin_protection()
    {
        $roleName = 'QA_Role_' . rand(100, 999);
        $createRes = $this->actingAs($this->superAdmin)->post(route('super-admin.roles.store'), [
            'name' => $roleName,
            'description' => 'Test Role for Automated QA',
        ]);
        $createRes->assertRedirect();

        $role = Role::where('name', $roleName)->first();
        $this->assertNotNull($role, 'Role was not created');

        // Test Clone
        $cloneRes = $this->actingAs($this->superAdmin)->post(route('super-admin.roles.clone', $role));
        $cloneRes->assertRedirect();
        $cloned = Role::where('name', 'like', $roleName . ' (Clone%')->first();
        $this->assertNotNull($cloned, 'Cloned role was not created');

        // Security Test: Attempt to delete SuperAdmin role
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        if ($superAdminRole) {
            $delRes = $this->actingAs($this->superAdmin)->delete(route('super-admin.roles.destroy', $superAdminRole));
            $this->assertTrue(Role::where('name', 'SuperAdmin')->exists(), 'CRITICAL: SuperAdmin role was deleted!');
        }

        // Cleanup
        if ($cloned) $cloned->delete();
        $role->delete();
    }

    public function test_matrix_permission_assignment()
    {
        $role = Role::firstOrCreate(['name' => 'MatrixQATest', 'guard_name' => 'web']);
        $permission = Permission::first();

        if ($permission) {
            $response = $this->actingAs($this->superAdmin)->post(route('super-admin.role-permissions.update'), [
                'role_id' => $role->id,
                'permissions' => [$permission->name],
            ]);
            $response->assertRedirect();
        }

        $role->delete();
    }

    // =========================================================
    // MODULE 4: BUG MANAGEMENT & SUPPORT CENTER
    // =========================================================

    public function test_bug_management_and_support_tickets_with_claim()
    {
        $company = Company::first();

        // View Bug List
        $bugsPage = $this->actingAs($this->superAdmin)->get(route('super-admin.bugs.index'));
        $bugsPage->assertStatus(200);

        // View Support Center
        $supportPage = $this->actingAs($this->superAdmin)->get(route('super-admin.support-center.index'));
        $supportPage->assertStatus(200);

        // Create Ticket with all required database schema fields
        $ticket = Ticket::create([
            'company_id' => $company ? $company->id : 1,
            'user_id' => $this->superAdmin->id,
            'module_name' => 'Ocean Freight Export',
            'title' => 'QA Container Gate In Failure',
            'description' => 'Automated test container tracking sync failure',
            'status' => 'Open',
            'priority' => 'High',
            'category' => 'Operations',
        ]);

        // SuperAdmin 1-Click Claim Action
        $claimRes = $this->actingAs($this->superAdmin)->patch(route('super-admin.support-center.claim', $ticket));
        $claimRes->assertRedirect();

        $ticket->refresh();
        $this->assertEquals($this->superAdmin->id, $ticket->assigned_agent_id, 'Ticket was not claimed by SuperAdmin');

        // Cleanup
        $ticket->delete();
    }

    // =========================================================
    // MODULE 5: INTEGRATION & SHIPMENTBEAT CARRIER MANAGEMENT
    // =========================================================

    public function test_carrier_tracking_shipmentbeat_exclusivity_and_update()
    {
        $carrierPage = $this->actingAs($this->superAdmin)->get(route('super-admin.carrier-tracking.index'));
        $carrierPage->assertStatus(200);

        // Verify only shipmentbeat exists in carrier_api_configs
        $nonShipmentBeat = CarrierApiConfig::where('carrier_code', '!=', 'shipmentbeat')->get();
        $this->assertEquals(0, $nonShipmentBeat->count(), 'Found non-ShipmentBeat carrier configurations in DB');

        // Update ShipmentBeat Config
        $updateRes = $this->actingAs($this->superAdmin)->put(route('super-admin.carrier-tracking.update'), [
            'carrier_code' => 'shipmentbeat',
            'name' => 'ShipmentBeat (Ocean Container Visibility)',
            'base_url' => 'https://api.shipmentbeat.com',
            'environment' => 'production',
            'is_active' => '1',
        ]);
        $updateRes->assertRedirect();

        // Clear tracking logs endpoint
        $clearLogs = $this->actingAs($this->superAdmin)->delete(route('super-admin.carrier-tracking.clear-logs'));
        $clearLogs->assertRedirect();
    }

    public function test_ai_settings_and_engine_diagnostics()
    {
        $aiPage = $this->actingAs($this->superAdmin)->get(route('super-admin.ai-settings.index'));
        $aiPage->assertStatus(200);
        $aiPage->assertSee('Google Gemini');
    }

    // =========================================================
    // MODULE 7: RELEASE & CACHE FLUSHING
    // =========================================================

    public function test_release_management_and_cache_flushing()
    {
        $releasesPage = $this->actingAs($this->superAdmin)->get(route('super-admin.releases.index'));
        $releasesPage->assertStatus(200);
        $releasesPage->assertSee('v2.4.0');

        $clearCache = $this->actingAs($this->superAdmin)->post(route('super-admin.releases.clear-cache'), [
            'type' => 'all',
        ]);
        $clearCache->assertRedirect();
    }

    // =========================================================
    // MODULE 8: AUDIT LOGS 4-STREAM FILTERING & CSV EXPORT
    // =========================================================

    public function test_audit_logs_streams_and_export()
    {
        foreach (['all', 'user', 'company', 'system'] as $stream) {
            $streamRes = $this->actingAs($this->superAdmin)->get(route('super-admin.audit-log', ['type' => $stream]));
            $streamRes->assertStatus(200);
        }

        $csvRes = $this->actingAs($this->superAdmin)->get(route('super-admin.audit-log', ['export' => 'csv']));
        $csvRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvRes->headers->get('content-type'));
    }

    // =========================================================
    // MODULE 9: BILLING, SUBSCRIPTIONS & QUICK EXTEND
    // =========================================================

    public function test_billing_index_and_subscription_update()
    {
        $billingPage = $this->actingAs($this->superAdmin)->get(route('super-admin.billing.index'));
        $billingPage->assertStatus(200);

        $company = Company::first();
        if ($company) {
            $originalExpiry = $company->subscription_ends_at;
            $newDate = now()->addDays(90)->format('Y-m-d');

            $subRes = $this->actingAs($this->superAdmin)->patch(route('super-admin.billing.update-subscription', $company), [
                'subscription_status' => 'active',
                'subscription_ends_at' => $newDate,
                'frexo_ai_unlimited' => '1',
                'frexo_ai_credits' => 5000,
            ]);
            $subRes->assertRedirect();

            $company->refresh();
            $this->assertEquals('active', $company->subscription_status);

            // Revert
            $company->update(['subscription_ends_at' => $originalExpiry]);
        }
    }

    // =========================================================
    // MODULE 10: USER DETAIL VIEW, SECURITY & IMPERSONATION
    // =========================================================

    public function test_user_detail_view_and_tabs_rendering()
    {
        $user = User::where('id', '!=', $this->superAdmin->id)->first();
        if (!$user) {
            $user = $this->superAdmin;
        }

        $userPage = $this->actingAs($this->superAdmin)->get(route('super-admin.users.show', $user));
        $userPage->assertStatus(200);
        $this->assertStringContainsString('Profile & Permissions', $userPage->getContent());
        $this->assertStringContainsString('Freight Modules', $userPage->getContent());
        $this->assertStringContainsString('Activity & Audit', $userPage->getContent());
        $this->assertStringContainsString('Sessions & Security', $userPage->getContent());
    }

    public function test_user_status_toggle_and_password_reset()
    {
        $targetUser = User::where('role', '!=', 'SuperAdmin')->first();
        if ($targetUser) {
            $initialStatus = $targetUser->status;
            $toggleRes = $this->actingAs($this->superAdmin)->patch(route('super-admin.users.toggle-status', $targetUser));
            $toggleRes->assertStatus(200);

            $targetUser->refresh();
            $this->assertNotEquals($initialStatus, $targetUser->status, 'User status did not toggle');

            // Revert back
            $targetUser->update(['status' => $initialStatus]);

            // Test password reset (valid)
            $resetRes = $this->actingAs($this->superAdmin)->post(route('super-admin.users.reset-password', $targetUser), [
                'new_password' => 'NewSecretPassword123!',
                'new_password_confirmation' => 'NewSecretPassword123!',
            ]);
            $resetRes->assertRedirect();
        }
    }

    public function test_self_impersonation_prevention()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.users.login-as', $this->superAdmin));
        $response->assertSessionHas('error', 'You cannot impersonate yourself.');
    }

    public function test_self_deletion_prevention()
    {
        $response = $this->actingAs($this->superAdmin)->delete(route('super-admin.users.destroy', $this->superAdmin));
        // Must prevent self-deletion
        $this->assertTrue(User::where('id', $this->superAdmin->id)->exists(), 'SuperAdmin account was self-deleted!');
    }
}
