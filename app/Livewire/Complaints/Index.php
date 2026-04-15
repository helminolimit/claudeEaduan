<?php

namespace App\Livewire\Complaints;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Complaints')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterPriority = '';

    public ?int $editingId = null;

    // Form fields
    public string $title = '';

    public string $description = '';

    public string $location = '';

    public int|string $categoryId = '';

    public int|string $officerId = '';

    public string $status = ComplaintStatus::Pending->value;

    public string $priority = ComplaintPriority::Medium->value;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPriority(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        Flux::modal('complaint-form')->show();
    }

    public function openEditModal(Complaint $complaint): void
    {
        $this->editingId = $complaint->id;
        $this->title = $complaint->title;
        $this->description = $complaint->description;
        $this->location = $complaint->location;
        $this->categoryId = $complaint->category_id;
        $this->officerId = $complaint->officer_id ?? '';
        $this->status = $complaint->status->value;
        $this->priority = $complaint->priority->value;
        Flux::modal('complaint-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:500'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'officerId' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(ComplaintStatus::cases(), 'value'))],
            'priority' => ['required', 'string', 'in:'.implode(',', array_column(ComplaintPriority::cases(), 'value'))],
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'category_id' => $validated['categoryId'],
            'officer_id' => $validated['officerId'] ?: null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
        ];

        if ($this->editingId) {
            Complaint::findOrFail($this->editingId)->update($data);
            Flux::toast(variant: 'success', text: __('Complaint updated.'));
        } else {
            Complaint::create([
                ...$data,
                'aduan_no' => Complaint::generateAduanNo(),
                'user_id' => auth()->id(),
            ]);
            Flux::toast(variant: 'success', text: __('Complaint created.'));
        }

        Flux::modal('complaint-form')->close();
        $this->resetForm();
    }

    public function delete(Complaint $complaint): void
    {
        $complaint->delete();
        Flux::toast(variant: 'success', text: __('Complaint deleted.'));
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function officers(): Collection
    {
        return User::orderBy('name')->get();
    }

    #[Computed]
    public function statuses(): array
    {
        return ComplaintStatus::cases();
    }

    #[Computed]
    public function priorities(): array
    {
        return ComplaintPriority::cases();
    }

    private function resetForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->location = '';
        $this->categoryId = '';
        $this->officerId = '';
        $this->status = ComplaintStatus::Pending->value;
        $this->priority = ComplaintPriority::Medium->value;
        $this->resetValidation();
    }

    public function render()
    {
        $complaints = Complaint::query()
            ->with(['user', 'category' => fn ($q) => $q->withTrashed(), 'officer'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('aduan_no', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn ($q) => $q->where('priority', $this->filterPriority))
            ->latest()
            ->paginate(10);

        return view('livewire.complaints.index', compact('complaints'));
    }
}
