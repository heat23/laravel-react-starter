<?php

// Inherited middleware: auth, verified, admin, throttle:60,1,admin:
// Permission: admin (read/write); super_admin (delete)

use App\Http\Controllers\Admin\AdminContactSubmissionsController;
use App\Http\Controllers\Admin\AdminEmailSendLogController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminNpsResponsesController;
use Illuminate\Support\Facades\Route;

// Feedback Inbox — bulk/export routes before {feedback} wildcard
Route::get('/feedback/export', [AdminFeedbackController::class, 'export'])
    ->middleware('throttle:admin-write')
    ->name('feedback.export');
Route::post('/feedback/bulk-update', [AdminFeedbackController::class, 'bulkUpdate'])
    ->middleware('throttle:admin-write')
    ->name('feedback.bulk-update');
Route::get('/feedback', [AdminFeedbackController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('feedback.index');
Route::get('/feedback/{feedback}', [AdminFeedbackController::class, 'show'])
    ->middleware('throttle:admin-read')
    ->name('feedback.show');
Route::patch('/feedback/{feedback}', [AdminFeedbackController::class, 'update'])
    ->middleware('throttle:admin-write')
    ->name('feedback.update');
Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('feedback.destroy');

// Contact Submissions — export/bulk before {contactSubmission} wildcard
Route::get('/contact-submissions/export', [AdminContactSubmissionsController::class, 'export'])
    ->middleware('throttle:admin-write')
    ->name('contact-submissions.export');
Route::post('/contact-submissions/bulk-update', [AdminContactSubmissionsController::class, 'bulkUpdate'])
    ->middleware('throttle:admin-write')
    ->name('contact-submissions.bulk-update');
Route::get('/contact-submissions', [AdminContactSubmissionsController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('contact-submissions.index');
Route::get('/contact-submissions/{contactSubmission}', [AdminContactSubmissionsController::class, 'show'])
    ->middleware('throttle:admin-read')
    ->name('contact-submissions.show');
Route::patch('/contact-submissions/{contactSubmission}', [AdminContactSubmissionsController::class, 'update'])
    ->middleware('throttle:admin-write')
    ->name('contact-submissions.update');
Route::delete('/contact-submissions/{contactSubmission}', [AdminContactSubmissionsController::class, 'destroy'])
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('contact-submissions.destroy');

// NPS Responses — export before index to avoid prefix collision
Route::get('/nps-responses/export', [AdminNpsResponsesController::class, 'export'])
    ->middleware('throttle:admin-write')
    ->name('nps-responses.export');
Route::get('/nps-responses', [AdminNpsResponsesController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('nps-responses.index');

// Email Send Logs — export before index to avoid prefix collision
Route::get('/email-send-logs/export', [AdminEmailSendLogController::class, 'export'])
    ->middleware('throttle:admin-write')
    ->name('email-send-logs.export');
Route::get('/email-send-logs', [AdminEmailSendLogController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('email-send-logs.index');
