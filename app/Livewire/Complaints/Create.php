<?php

namespace App\Livewire\Complaints;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Models\Category;
use App\Models\Complaint;
use App\Services\NotificationService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('Submit Complaint')]
class Create extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public string $title = '';

    public string $description = '';

    public string $location = '';

    public int|string $categoryId = '';

    /** @var array<int, TemporaryUploadedFile> */
    #[Validate(['attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'])]
    public array $attachments = [];

    public function mount(): void
    {
        $draft = session('aduan_draft', []);

        $this->title = $draft['title'] ?? '';
        $this->description = $draft['description'] ?? '';
        $this->location = $draft['location'] ?? '';
        $this->categoryId = $draft['categoryId'] ?? '';
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'title' => ['required', 'string', 'max:255'],
                'categoryId' => ['required', 'integer', 'exists:categories,id'],
                'description' => ['required', 'string'],
                'location' => ['required', 'string', 'max:500'],
            ]);

            $this->saveDraft();
        }

        if ($this->step === 2) {
            if (count($this->attachments) > 5) {
                $this->addError('attachments', 'You may upload a maximum of 5 files.');

                return;
            }

            if (! empty($this->attachments)) {
                $this->validateOnly('attachments.*');
            }
        }

        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step--;
    }

    public function removeAttachment(int $index): void
    {
        array_splice($this->attachments, $index, 1);
    }

    public function saveDraft(): void
    {
        session(['aduan_draft' => [
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'categoryId' => $this->categoryId,
        ]]);
    }

    public function submit(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:500'],
        ]);

        $complaint = Complaint::create([
            'aduan_no' => Complaint::generateAduanNo(),
            'user_id' => auth()->id(),
            'category_id' => $this->categoryId,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'status' => ComplaintStatus::Pending,
            'priority' => ComplaintPriority::Medium,
        ]);

        foreach ($this->attachments as $file) {
            $fileName = $file->getClientOriginalName();
            $path = $file->storeAs("aduan/{$complaint->id}", $fileName);

            $complaint->attachments()->create([
                'file_path' => $path,
                'file_name' => $fileName,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        session()->forget('aduan_draft');

        app(NotificationService::class)->complaintSubmitted($complaint);

        Flux::modal('confirm-submit')->close();

        $this->redirect(route('aduan.show', $complaint), navigate: true);
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function selectedCategory(): ?Category
    {
        return $this->categoryId ? Category::find($this->categoryId) : null;
    }

    public function render()
    {
        return view('livewire.complaints.create');
    }
}
