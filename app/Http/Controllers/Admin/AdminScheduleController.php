<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Console\Scheduling\Schedule;
use Inertia\Inertia;
use Inertia\Response;

class AdminScheduleController extends Controller
{
    public function __construct(private Schedule $schedule) {}

    public function __invoke(): Response
    {
        $tasks = collect($this->schedule->events())
            ->map(function ($event) {
                try {
                    $nextRun = $event->nextRunDate()->toISOString();
                } catch (\Throwable) {
                    $nextRun = null;
                }

                return [
                    'command' => $event->command ?? $event->description ?? 'Unknown',
                    'expression' => $event->expression,
                    'description' => $event->description,
                    'timezone' => $event->timezone,
                    'next_run_date' => $nextRun,
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('App/Admin/Schedule/Index', [
            'tasks' => $tasks,
        ]);
    }
}
