<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('User Management')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterRole = '';

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    public int $perPage = 10;

    // Create form
    public string $createName = '';

    public string $createEmail = '';

    public string $createRole = '';

    public string $createPassword = '';

    // Edit form
    public ?int $editUserId = null;

    public string $editName = '';

    public string $editEmail = '';

    public string $editRole = '';

    // Reset password form
    public ?int $resetPasswordUserId = null;

    public string $resetPasswordNew = '';

    public string $resetPasswordNew_confirmation = '';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function createUser(): void
    {
        $this->validate([
            'createName' => ['required', 'string', 'max:255'],
            'createEmail' => ['required', 'email', 'unique:users,email'],
            'createRole' => ['required', 'in:'.implode(',', array_column(UserRole::cases(), 'value'))],
            'createPassword' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $this->createName,
            'email' => $this->createEmail,
            'role' => $this->createRole,
            'password' => Hash::make($this->createPassword),
            'email_verified_at' => now(),
        ]);

        $this->reset(['createName', 'createEmail', 'createRole', 'createPassword']);
        Flux::modal('create-user')->close();
        Flux::toast(variant: 'success', text: 'User created successfully.');
    }

    public function openEditModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->role->value;
        Flux::modal('edit-user')->show();
    }

    public function updateUser(): void
    {
        $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editEmail' => ['required', 'email', "unique:users,email,{$this->editUserId}"],
            'editRole' => ['required', 'in:'.implode(',', array_column(UserRole::cases(), 'value'))],
        ]);

        User::findOrFail($this->editUserId)->update([
            'name' => $this->editName,
            'email' => $this->editEmail,
            'role' => $this->editRole,
        ]);

        Flux::modal('edit-user')->close();
        Flux::toast(variant: 'success', text: 'User updated successfully.');
    }

    public function openResetPasswordModal(int $id): void
    {
        $this->resetPasswordUserId = $id;
        $this->resetPasswordNew = '';
        $this->resetPasswordNew_confirmation = '';
        Flux::modal('reset-password')->show();
    }

    public function resetUserPassword(): void
    {
        $this->validate([
            'resetPasswordNew' => ['required', 'string', 'min:8', 'confirmed'],
            'resetPasswordNew_confirmation' => ['required'],
        ], [], [
            'resetPasswordNew' => 'new password',
            'resetPasswordNew_confirmation' => 'password confirmation',
        ]);

        User::findOrFail($this->resetPasswordUserId)->update([
            'password' => Hash::make($this->resetPasswordNew),
        ]);

        $this->reset(['resetPasswordUserId', 'resetPasswordNew', 'resetPasswordNew_confirmation']);
        Flux::modal('reset-password')->close();
        Flux::toast(variant: 'success', text: 'Password updated successfully.');
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            Flux::toast(variant: 'warning', text: 'You cannot delete your own account.');

            return;
        }

        User::findOrFail($id)->delete();
        Flux::modals()->close();
        Flux::toast(variant: 'success', text: 'User deleted.');
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function roles(): array
    {
        return UserRole::cases();
    }

    public function render()
    {
        return view('livewire.users.index');
    }
}
