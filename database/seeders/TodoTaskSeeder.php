<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TodoTaskSeeder extends Seeder
{
    public function run()
    {
        $defaultConfig = [
            'startTime' => '',
            'relativeTo' => '',
            'conditions' => [[['field' => '', 'operator' => 'equals', 'value' => '']]],
            'actionType' => '',
            'emailTo' => '',
            'emailSubject' => '',
            'emailBody' => '',
            'notificationMessage' => '',
            'assignTo' => '',
            'isActive' => true
        ];

        $tasks = [
            // Ocean Import
            [
                'module' => 'ocean-import',
                'title' => 'Pre-alert',
                'description' => 'VESSEL ETD, ETA, Port of Loading, Port of Discharge, Oversea Agent, Carrier, Vessel, Voyage (HBL), Shipper, Consignee',
                'config' => json_encode(array_merge($defaultConfig, [
                    'startTime' => 'after_2_days',
                    'relativeTo' => 'etd',
                    'actionType' => 'email',
                    'emailSubject' => 'Pre-alert: Shipment Departure Notice'
                ])),
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'ocean-import',
                'title' => 'Arrival Notice',
                'description' => 'VESSEL, Arrival Notice Saved',
                'config' => json_encode(array_merge($defaultConfig, [
                    'startTime' => 'after_1_day',
                    'relativeTo' => 'eta',
                    'actionType' => 'notification',
                    'notificationMessage' => 'Vessel arriving soon - prepare arrival notice'
                ])),
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'ocean-import',
                'title' => 'Original B/L',
                'description' => 'MBL/Original B/L of Lading is not received (HBL) (Payment) (OBL received)',
                'config' => json_encode($defaultConfig),
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'ocean-import',
                'title' => 'Import',
                'description' => 'VESSEL, Brokers/Customs or Debit Note',
                'config' => json_encode($defaultConfig),
                'order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'ocean-import',
                'title' => 'Pick Up No.',
                'description' => 'VESSEL, Nepal, Container Pickup Number',
                'config' => json_encode($defaultConfig),
                'order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'ocean-import',
                'title' => 'Delivery Order',
                'description' => 'VESSEL, Due Term for Delivery Order Saved',
                'config' => json_encode($defaultConfig),
                'order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Ocean Export
            [
                'module' => 'ocean-export',
                'title' => 'Booking Confirmation',
                'description' => 'Confirm booking with carrier and shipper',
                'config' => json_encode(array_merge($defaultConfig, [
                    'startTime' => 'immediately',
                    'relativeTo' => 'booking_date',
                    'actionType' => 'assign_task',
                    'assignTo' => 'operator'
                ])),
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'ocean-export',
                'title' => 'Container Release',
                'description' => 'Arrange container pickup and delivery',
                'config' => json_encode($defaultConfig),
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'ocean-export',
                'title' => 'Export Documentation',
                'description' => 'Prepare export documents and customs clearance',
                'config' => json_encode($defaultConfig),
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Air Import
            [
                'module' => 'air-import',
                'title' => 'Flight Arrival Notice',
                'description' => 'Track flight arrival and notify consignee',
                'config' => json_encode($defaultConfig),
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'air-import',
                'title' => 'Customs Clearance',
                'description' => 'Process customs documentation and duties',
                'config' => json_encode($defaultConfig),
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Air Export
            [
                'module' => 'air-export',
                'title' => 'Booking Confirmation',
                'description' => 'Confirm air cargo booking with airline',
                'config' => json_encode($defaultConfig),
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'air-export',
                'title' => 'Export Documentation',
                'description' => 'Prepare air waybill and export Tools',
                'config' => json_encode($defaultConfig),
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Trucking
            [
                'module' => 'trucking',
                'title' => 'Dispatch Confirmation',
                'description' => 'Confirm truck dispatch and driver assignment',
                'config' => json_encode($defaultConfig),
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'module' => 'trucking',
                'title' => 'Delivery Confirmation',
                'description' => 'Confirm delivery and obtain POD',
                'config' => json_encode($defaultConfig),
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('todo_tasks')->insert($tasks);
    }
}
