<?php

use App\Livewire\Users\Index;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('admin can open reset password modal', function () {
    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('openResetPasswordModal', $user->id)
        ->assertSet('resetPasswordUserId', $user->id)
        ->assertSet('resetPasswordNew', '')
        ->assertSet('resetPasswordNew_confirmation', '');
});

test('admin can reset another user password', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    Livewire::test(Index::class)
        ->call('openResetPasswordModal', $user->id)
        ->set('resetPasswordNew', 'new-password1')
        ->set('resetPasswordNew_confirmation', 'new-password1')
        ->call('resetUserPassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password1', $user->fresh()->password))->toBeTrue();
});

test('password must be at least 8 characters', function () {
    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('openResetPasswordModal', $user->id)
        ->set('resetPasswordNew', 'short')
        ->set('resetPasswordNew_confirmation', 'short')
        ->call('resetUserPassword')
        ->assertHasErrors(['resetPasswordNew']);
});

test('password confirmation must match', function () {
    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('openResetPasswordModal', $user->id)
        ->set('resetPasswordNew', 'new-password1')
        ->set('resetPasswordNew_confirmation', 'different-password')
        ->call('resetUserPassword')
        ->assertHasErrors(['resetPasswordNew']);
});

test('non-admin cannot access user management', function () {
    $complainant = User::factory()->complainant()->create();

    $this->actingAs($complainant)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});
