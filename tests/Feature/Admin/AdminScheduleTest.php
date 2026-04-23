<?php

use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('admin can view the schedule page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/schedule')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('App/Admin/Schedule/Index'));
});

it('schedule page returns task list', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/admin/schedule')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('tasks'));
});

it('tasks prop is an array', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/schedule');

    $response->assertInertia(function ($page) {
        $tasks = $page->toArray()['props']['tasks'];
        expect($tasks)->toBeArray();
    });
});

it('each task includes required structural fields when tasks exist', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/schedule');

    $response->assertInertia(function ($page) {
        $tasks = $page->toArray()['props']['tasks'];

        foreach ($tasks as $task) {
            expect($task)->toHaveKeys(['command', 'expression', 'description', 'timezone', 'next_run_date']);
        }
    });
});

it('non-admin cannot view the schedule page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin/schedule')
        ->assertForbidden();
});

it('guest cannot view the schedule page', function () {
    $this->get('/admin/schedule')
        ->assertRedirect('/login');
});
