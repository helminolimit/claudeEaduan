---
paths:
  - "app/Livewire/**/*.php"
  - "resources/views/livewire/**/*.blade.php"
---

# Form Action Confirmation Pattern

Every action that modifies data (delete, update, submit) must show a confirmation dialog before proceeding. Do not allow destructive or irreversible actions without user confirmation.

## When to Require Confirmation

- **Delete** — deleting any record
- **Submit/Save** — submitting forms that create or update records
- **Bulk actions** — any action affecting multiple records at once
- **Status changes** — changing status that triggers side effects (e.g. resolved, rejected)

## Implementation with Flux UI Modal

Use `flux:modal` with a trigger button. Do not use browser `confirm()` or `alert()`.

### Delete Confirmation

```blade
<flux:modal.trigger name="delete-{{ $record->id }}">
    <flux:menu.item variant="danger" icon="trash">Padam</flux:menu.item>
</flux:modal.trigger>

<flux:modal
    name="delete-{{ $record->id }}"
    class="min-w-[22rem]"
    :closable="false"
    x-data="{ loading: false }"
    x-on:cancel="loading && $event.preventDefault()"
    x-on:livewire:commit.window="loading = false"
>
    <div class="relative space-y-6">
        <div
            wire:loading
            wire:target="delete({{ $record->id }})"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80"
        >
            <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
        </div>
        <div>
            <flux:heading size="lg">Padam rekod?</flux:heading>
            <flux:subheading>Tindakan ini tidak boleh dibatalkan.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="delete({{ $record->id }})" @click="loading = true">Padam</flux:button>
        </div>
    </div>
</flux:modal>
```

### Save/Submit Confirmation

```blade
<flux:modal.trigger name="confirm-save">
    <flux:button variant="primary">Simpan</flux:button>
</flux:modal.trigger>

<flux:modal
    name="confirm-save"
    class="min-w-[22rem]"
    :closable="false"
    x-data="{ loading: false }"
    x-on:cancel="loading && $event.preventDefault()"
    x-on:livewire:commit.window="loading = false"
>
    <div class="relative space-y-6">
        <div
            wire:loading
            wire:target="save"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80"
        >
            <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
        </div>
        <div>
            <flux:heading size="lg">Sahkan perubahan?</flux:heading>
            <flux:subheading>Semak semula maklumat sebelum menyimpan.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" wire:click="save" @click="loading = true">Simpan</flux:button>
        </div>
    </div>
</flux:modal>
```

## Livewire Delete Method Pattern

Handle delete inside the Livewire component — do not use separate HTTP controller form POST for delete actions.

```php
public function delete(int $id): void
{
    $record = Record::findOrFail($id);

    $record->delete();

    Flux::modals()->close();
    Flux::toast(variant: 'success', text: 'Rekod berjaya dipadam.');
}
```

## Loading & Blocking State (SweetAlert-like)

Three layers work together to completely lock the modal — nothing is clickable until the server responds:

1. **`:closable="false"`** — removes the X button (use Batal button instead)
2. **`x-on:cancel="loading && $event.preventDefault()"`** — blocks ESC key and backdrop click when `loading` is true
3. **Visual overlay** (`absolute inset-0 z-10`) — covers all modal content including the Batal button

`@click="loading = true"` on the confirm button activates the lock immediately. `x-on:livewire:commit.window="loading = false"` resets it when the server responds (handles validation failure case).

Livewire components that **do not redirect** after success must call `Flux::modals()->close()` to close the modal from PHP.

## Rules

- Never use `onsubmit="return confirm(...)"` or browser `confirm()` — always use `flux:modal`.
- Each destructive action must have its own named modal (e.g. `delete-{{ $record->id }}`).
- Confirmation modals must include a clear heading, a short description of consequence, a cancel button, and a confirm button.
- The confirm button must use `variant="danger"` for delete actions and `variant="primary"` for save/update actions.
- Every confirmation modal must have `:closable="false"`, `x-data="{ loading: false }"`, `x-on:cancel`, `x-on:livewire:commit.window`, a visual overlay, and `@click="loading = true"` on the confirm button.
- Delete actions must be handled in the Livewire component, not via a separate HTTP controller form POST.
