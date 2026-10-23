<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\ActivityLog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            \App\Models\OceanImport::class => \App\Models\OceanImport::class,
            'AppModelsOceanImport' => \App\Models\OceanImport::class,
            \App\Models\OceanExport::class => \App\Models\OceanExport::class,
            'AppModelsOceanExport' => \App\Models\OceanExport::class,
            \App\Models\AirImport::class => \App\Models\AirImport::class,
            'AppModelsAirImport' => \App\Models\AirImport::class,
            \App\Models\AirExport::class => \App\Models\AirExport::class,
            'AppModelsAirExport' => \App\Models\AirExport::class,
            \App\Models\TruckShipment::class => \App\Models\TruckShipment::class,
            'AppModelsTruckShipment' => \App\Models\TruckShipment::class,
            \App\Models\Warehouse::class => \App\Models\Warehouse::class,
            'AppModelsWarehouse' => \App\Models\Warehouse::class,
            \App\Models\HouseBillOfLading::class => \App\Models\HouseBillOfLading::class,
            'AppModelsHouseBillOfLading' => \App\Models\HouseBillOfLading::class,
            \App\Models\BillOfLading::class => \App\Models\BillOfLading::class,
            'AppModelsBillOfLading' => \App\Models\BillOfLading::class,
            \App\Models\OceanBooking::class => \App\Models\OceanBooking::class,
            'AppModelsOceanBooking' => \App\Models\OceanBooking::class,
            \App\Models\AirBooking::class => \App\Models\AirBooking::class,
            'AppModelsAirBooking' => \App\Models\AirBooking::class,
            \App\Models\Booking::class => \App\Models\Booking::class,
            'AppModelsBooking' => \App\Models\Booking::class,
            \App\Models\Quotation::class => \App\Models\Quotation::class,
            'AppModelsQuotation' => \App\Models\Quotation::class,
        ]);

        Event::listen(Login::class, function (Login $event) {
            if ($event->user) {
                ActivityLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'login',
                    'description' => 'User logged in',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'logout',
                    'description' => 'User logged out',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }
}
