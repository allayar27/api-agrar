<?php

namespace App\Providers;

use App\Events\StudentAttendanceCreated;
use App\Listeners\RecordStudentStatistics;
use App\Events\TeacherAttendanceCreated;
use App\Listeners\RecordTeacherStatistics;
use App\Listeners\SendShipmentNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        StudentAttendanceCreated::class => [
            RecordStudentStatistics::class,
        ],
        TeacherAttendanceCreated::class => [
            RecordTeacherStatistics::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
