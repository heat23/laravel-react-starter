<?php

// Inherited middleware: auth, verified, admin, throttle:60,1,admin:
// Permission: admin (read); super_admin (write mutations on user identity)

use App\Http\Controllers\Admin\AdminSessionsController;
use App\Http\Controllers\Admin\Users\AdminUserBulkController;
use App\Http\Controllers\Admin\Users\AdminUserIdentityController;
use App\Http\Controllers\Admin\Users\AdminUserPasswordResetController;
use App\Http\Controllers\Admin\Users\AdminUserPrivilegeController;
use Illuminate\Support\Facades\Route;

// Users Management
Route::get('/users/export', [AdminUserBulkController::class, 'export'])
    ->middleware('throttle:admin-write')
    ->name('users.export');
Route::get('/users/create', [AdminUserIdentityController::class, 'create'])
    ->middleware('throttle:admin-read')
    ->name('users.create');
Route::post('/users', [AdminUserIdentityController::class, 'store'])
    ->middleware('throttle:admin-write')
    ->name('users.store');
Route::get('/users', [AdminUserIdentityController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('users.index');
Route::get('/users/{user}', [AdminUserIdentityController::class, 'show'])
    ->withTrashed()
    ->middleware('throttle:admin-read')
    ->name('users.show');
Route::patch('/users/{user}', [AdminUserIdentityController::class, 'update'])
    ->withTrashed()
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('users.update');
Route::patch('/users/{user}/toggle-admin', [AdminUserPrivilegeController::class, 'toggleAdmin'])
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('users.toggle-admin');
Route::patch('/users/{user}/toggle-active', [AdminUserPrivilegeController::class, 'toggleActive'])
    ->withTrashed()
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('users.toggle-active');
Route::post('/users/{user}/send-password-reset', [AdminUserPasswordResetController::class, 'sendPasswordReset'])
    ->middleware('throttle:admin-sensitive')
    ->name('users.send-password-reset');

// Bulk Actions
Route::post('/users/bulk-deactivate', [AdminUserBulkController::class, 'bulkDeactivate'])
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('users.bulk-deactivate');
Route::post('/users/bulk-restore', [AdminUserBulkController::class, 'bulkRestore'])
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('users.bulk-restore');

// Session Manager
Route::get('/sessions', [AdminSessionsController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('sessions.index');
Route::delete('/sessions/{userId}', [AdminSessionsController::class, 'destroy'])
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('sessions.destroy');
