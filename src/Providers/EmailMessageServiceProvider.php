<?php

namespace Hrnicek\FilamentEmailLog\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Mail\Events\MessageSent;
use Hrnicek\FilamentEmailLog\Listeners\FilamentEmailLogger;

class EmailMessageServiceProvider extends EventServiceProvider
{
    protected $listen = [
        MessageSent::class => [
            FilamentEmailLogger::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
