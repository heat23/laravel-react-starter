<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin', 'throttle:60,1,admin:'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        require __DIR__.'/admin/users.php';
        require __DIR__.'/admin/feedback.php';
        require __DIR__.'/admin/billing.php';
        require __DIR__.'/admin/webhooks.php';
        require __DIR__.'/admin/content.php';
        require __DIR__.'/admin/system.php';
    });
