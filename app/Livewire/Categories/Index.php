<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Categories')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        Flux::modal('category-form')->show();
    }

    public function openEditModal(Category $category): void
    {
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->isActive = $category->is_active;
        Flux::modal('category-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'isActive' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            Flux::toast(variant: 'success', text: __('Category updated.'));
        } else {
            Category::create($data);
            Flux::toast(variant: 'success', text: __('Category created.'));
        }

        Flux::modal('category-form')->close();
        $this->resetForm();
    }

    public function delete(Category $category): void
    {
        $category->delete();
        Flux::toast(variant: 'success', text: __('Category deleted.'));
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.categories.index', compact('categories'));
    }
}
