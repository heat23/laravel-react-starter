<?php

use App\Models\User;

it('admin can view the schedule page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/schedule')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Schedule/Index'));
});

it('schedule page returns task list', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/admin/schedule')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('tasks'));
});
