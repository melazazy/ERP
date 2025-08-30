<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\InventoryService;
use App\Services\ReceivingService;
use App\Services\RequisitionService;
use App\Services\TrustService;
use App\Services\TransferService;
use App\Services\ReportingService;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService();
        });

        $this->app->singleton(ReceivingService::class, function ($app) {
            return new ReceivingService();
        });

        $this->app->singleton(RequisitionService::class, function ($app) {
            return new RequisitionService();
        });

        $this->app->singleton(TrustService::class, function ($app) {
            return new TrustService();
        });

        $this->app->singleton(TransferService::class, function ($app) {
            return new TransferService();
        });

        $this->app->singleton(ReportingService::class, function ($app) {
            return new ReportingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
