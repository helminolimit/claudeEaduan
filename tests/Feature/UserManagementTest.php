<?php

use App\Enums\UserRole;
use App\Livewire\Users\Index;
use App\Models\User;
use Livewire\Livewire;

test('admin can view user management page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
});

test('non-admin cannot access user management', function () {
    $user = User::factory()->complainant()->create();

    $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
});

test('admin can create a new officer', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('createName', 'New Officer')
        ->set('createEmail', 'officer@example.com')
        ->set('createRole', UserRole::Officer->value)
        ->set('createPassword', 'password123')
        ->call('createUser');

    expect(User::where('email', 'officer@example.com')->where('role', UserRole::Officer)->exists())->toBeTrue();
});

test('admin cannot create user with duplicate email', function () {
    $admin = User::factory()->admin()->create();
    $existing = User::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('createName', 'Someone')
        ->set('createEmail', 'taken@example.com')
        ->set('createRole', UserRole::Officer->value)
        ->set('createPassword', 'password123')
        ->call('createUser')
        ->assertHasErrors(['createEmail']);
});

test('admin can update a user role', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->complainant()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('openEditModal', $user->id)
        ->set('editRole', UserRole::Officer->value)
        ->call('updateUser');

    expect($user->fresh()->role)->toBe(UserRole::Officer);
});

test('admin can delete a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->complainant()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('delete', $user->id);

    expect(User::find($user->id))->toBeNull();
});

test('admin cannot delete their own account', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('delete', $admin->id);

    expect(User::find($admin->id))->not->toBeNull();
});
