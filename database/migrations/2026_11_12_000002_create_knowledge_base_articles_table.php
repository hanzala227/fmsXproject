<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('knowledge_base_articles')) {
            Schema::create('knowledge_base_articles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('category', 100)->default('Operations');
                $table->string('title', 255);
                $table->string('slug', 255)->unique();
                $table->text('summary')->nullable();
                $table->longText('content');
                $table->string('target_audience', 50)->default('All');
                $table->unsignedInteger('views_count')->default(0);
                $table->boolean('is_published')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['category', 'is_published']);
            });

            // Seed initial realistic freight forwarding SOP and troubleshooting articles
            $adminId = DB::table('users')->where('role', 'SuperAdmin')->value('id') ?: 1;
            $now = now();

            $articles = [
                [
                    'category' => 'Ocean Freight & B/L',
                    'title' => 'SOP: Master Bill of Lading (MBL) & House B/L Manifest Workflow',
                    'summary' => 'Comprehensive operational procedure for issuing Ocean MBLs, container stuffing manifests, and issuing House B/Ls across ocean export corridors.',
                    'content' => "### Overview\nEvery ocean export movement requires accurate mapping between the Carrier Master Bill of Lading (MBL) and downstream House Bills of Lading (HBL) issued to shippers.\n\n### Critical Compliance Checkpoints\n1. **Container & Seal Numbers**: Verify ISO 6346 container check-digit validation before saving shipping instructions.\n2. **Incoterms Specification**: Ensure accurate selection between FOB, CIF, CFR, or DDP on ocean freight charges.\n3. **Shipper & Consignee Parties**: Match trade partner tax registration and consignee notify parties.\n\n### Troubleshooting Seal Mismatches\nWhen carrier shipping instructions indicate seal discrepancies, navigate to Ocean Export -> Container Details, click 'Re-verify Container Seal', and regenerate the combined PDF manifest.",
                    'target_audience' => 'All',
                    'views_count' => 142,
                    'is_published' => true,
                ],
                [
                    'category' => 'Air Cargo & AWB',
                    'title' => 'IATA Chargeable Weight vs Gross Weight Volumetric Calculation Guide',
                    'summary' => 'Formula and compliance standards for calculating volumetric weight (1:6000 standard) for Master Air Waybill (MAWB) bookings.',
                    'content' => "### Standard Air Cargo Formula\nIn international air freight, carriers charge based on whichever is greater: actual Gross Weight (kg) or Volumetric Weight (kg).\n\n$$\\text{Volumetric Weight (kg)} = \\frac{\\text{Length (cm)} \\times \\text{Width (cm)} \\times \\text{Height (cm)}}{6000}$$\n\n### Best Practices in FreightX 2.0\n- When entering package dimensions in Air Booking, the system automatically computes total volume in CBM and cubic kilograms.\n- If the airline EDI feed reports a discrepancy exceeding 5%, the system flags an automated audit alert on the Air Export dashboard.",
                    'target_audience' => 'Operators',
                    'views_count' => 98,
                    'is_published' => true,
                ],
                [
                    'category' => 'Customs & Demurrage',
                    'title' => 'Demurrage & Detention Free-Days Calculator Configuration',
                    'summary' => 'Configuring terminal storage tariffs, shipping line free-time windows, and national holiday pause rules.',
                    'content' => "### Demurrage vs Detention Definitions\n- **Demurrage**: Storage fees assessed by the ocean terminal while the loaded container remains inside port custody after vessel discharge.\n- **Detention**: Charges incurred from the shipping line while the empty container is held by the consignee beyond the allowed equipment turn-around window.\n\n### Port Holiday Exclusions\nConfigure public port holidays under Platform Settings -> Terminal Tariffs to ensure automated demurrage counters pause during gazetted customs closures.",
                    'target_audience' => 'All',
                    'views_count' => 76,
                    'is_published' => true,
                ],
                [
                    'category' => 'API Integrations',
                    'title' => 'Carrier Tracking Webhooks & Milestone Event Synchronization',
                    'summary' => 'Technical specifications for real-time AIS vessel telemetry, container gate-in triggers, and DCSA milestone event logs.',
                    'content' => "### Integration Pipeline Architecture\nThe FreightX Carrier Tracking Engine queries ShipmentBeat and direct carrier gateways using ISO 6346 container numbers.\n\n### Supported Tracking Milestones\n1. `BOOKING_CONFIRMED`: Carrier booking allocation approved.\n2. `GATE_IN`: Container entered origin terminal.\n3. `LOADED`: Container loaded onto mother vessel.\n4. `IN_TRANSIT`: Ocean voyage underway with live coordinates.\n5. `DISCHARGED`: Container offloaded at destination port.\n6. `DELIVERED`: Final delivery proof verified.",
                    'target_audience' => 'Tenant Admin',
                    'views_count' => 115,
                    'is_published' => true,
                ],
                [
                    'category' => 'Accounting & Billing',
                    'title' => 'Multi-Currency Freight Invoice & Credit Note Generation Workflow',
                    'summary' => 'Standard operating guidelines for issuing freight debit notes, exchange rate conversions, and accounting block freeze windows.',
                    'content' => "### Invoicing Workflow\n1. Open shipment file and navigate to Charges & Accounting tab.\n2. Generate Freight Invoice with appropriate operational currency.\n3. The system converts local currency charges using the authenticated tenant exchange rate vault.\n\n### Month-End Financial Period Lock\nTo prevent unauthorized retroactive modifications to accounting ledgers, financial controllers must activate 'Accounting Period Block' at the end of each fiscal month.",
                    'target_audience' => 'Tenant Admin',
                    'views_count' => 88,
                    'is_published' => true,
                ],
                [
                    'category' => 'Platform Security',
                    'title' => 'Multi-Factor Authentication (MFA) & Role Permissions Matrix Guide',
                    'summary' => 'Admin security guide for managing operational staff roles, password expiry policies, and granular module access flags.',
                    'content' => "### Role Permissions Matrix\nSuperAdmins can grant or revoke specific operational permissions (e.g., `invoices.create`, `shipments.delete`, `rates.view`) directly from Operations -> Roles & Rules -> Role Permissions Matrix.\n\n### MFA Enforcement\nEnable mandatory Time-based One-Time Password (TOTP) enforcement under Platform & Security Settings to protect freight tenant financial vaults.",
                    'target_audience' => 'SuperAdmin',
                    'views_count' => 64,
                    'is_published' => true,
                ],
            ];

            foreach ($articles as $art) {
                DB::table('knowledge_base_articles')->insert([
                    'author_id' => $adminId,
                    'category' => $art['category'],
                    'title' => $art['title'],
                    'slug' => Str::slug($art['title']),
                    'summary' => $art['summary'],
                    'content' => $art['content'],
                    'target_audience' => $art['target_audience'],
                    'views_count' => $art['views_count'],
                    'is_published' => $art['is_published'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_articles');
    }
};
