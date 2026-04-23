<?php

use App\Http\Controllers\Admin\Users\AdminUserBulkController;
use App\Http\Controllers\Admin\Users\AdminUserIdentityController;
use App\Http\Controllers\Admin\Users\AdminUserPasswordResetController;
use App\Http\Controllers\Admin\Users\AdminUserPrivilegeController;

beforeEach(function () {
    registerAdminRoutes();
});

it('admin.users.index resolves to AdminUserIdentityController@index', function () {
    expect(route('admin.users.index'))->not->toBeEmpty();
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.index');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserIdentityController::class);
    expect($route->getActionMethod())->toBe('index');
});

it('admin.users.show resolves to AdminUserIdentityController@show', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.show');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserIdentityController::class);
    expect($route->getActionMethod())->toBe('show');
});

it('admin.users.create resolves to AdminUserIdentityController@create', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.create');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserIdentityController::class);
    expect($route->getActionMethod())->toBe('create');
});

it('admin.users.store resolves to AdminUserIdentityController@store', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.store');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserIdentityController::class);
    expect($route->getActionMethod())->toBe('store');
});

it('admin.users.update resolves to AdminUserIdentityController@update', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.update');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserIdentityController::class);
    expect($route->getActionMethod())->toBe('update');
});

it('admin.users.toggle-admin resolves to AdminUserPrivilegeController@toggleAdmin', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.toggle-admin');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserPrivilegeController::class);
    expect($route->getActionMethod())->toBe('toggleAdmin');
});

it('admin.users.toggle-active resolves to AdminUserPrivilegeController@toggleActive', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.toggle-active');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserPrivilegeController::class);
    expect($route->getActionMethod())->toBe('toggleActive');
});

it('admin.users.bulk-deactivate resolves to AdminUserBulkController@bulkDeactivate', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.bulk-deactivate');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserBulkController::class);
    expect($route->getActionMethod())->toBe('bulkDeactivate');
});

it('admin.users.bulk-restore resolves to AdminUserBulkController@bulkRestore', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.bulk-restore');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserBulkController::class);
    expect($route->getActionMethod())->toBe('bulkRestore');
});

it('admin.users.export resolves to AdminUserBulkController@export', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.export');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserBulkController::class);
    expect($route->getActionMethod())->toBe('export');
});

it('admin.users.send-password-reset resolves to AdminUserPasswordResetController@sendPasswordReset', function () {
    $routes = app('router')->getRoutes();
    $route = $routes->getByName('admin.users.send-password-reset');
    expect($route)->not->toBeNull();
    expect($route->getControllerClass())->toBe(AdminUserPasswordResetController::class);
    expect($route->getActionMethod())->toBe('sendPasswordReset');
});
