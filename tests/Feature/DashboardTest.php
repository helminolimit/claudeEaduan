<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('complainant sees their dashboard', function () {
    $user = User::factory()->complainant()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

test('officer sees officer dashboard', function () {
    $officer = User::factory()->officer()->create();

    $this->actingAs($officer)->get(route('officer.dashboard'))->assertOk();
});

test('admin sees admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
});

test('complainant cannot access admin dashboard', function () {
    $user = User::factory()->complainant()->create();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

test('officer cannot access admin dashboard', function () {
    $officer = User::factory()->officer()->create();

    $this->actingAs($officer)->get(route('admin.dashboard'))->assertForbidden();
});

test('complainant cannot access officer dashboard', function () {
    $user = User::factory()->complainant()->create();

    $this->actingAs($user)->get(route('officer.dashboard'))->assertForbidden();
});

test('login redirects admin to admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

test('login redirects officer to officer dashboard', function () {
    $officer = User::factory()->officer()->create();

    $this->post(route('login'), [
        'email' => $officer->email,
        'password' => 'password',
    ])->assertRedirect(route('officer.dashboard'));
});

test('login redirects complainant to dashboard', function () {
    $user = User::factory()->complainant()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));
});
