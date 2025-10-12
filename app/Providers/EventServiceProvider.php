<?php

namespace App\Providers;

use App\Events\MedicalRecordCreated;
use App\Listeners\UpdateSpmDataListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MedicalRecordCreated::class => [
            UpdateSpmDataListener::class,
        ],
    ];
}

