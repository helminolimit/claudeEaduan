<div>
    <flux:main class="max-w-2xl">

        {{-- Header --}}
        <div class="mb-6 flex items-center gap-4">
            <flux:button :href="route('my.aduan.index')" wire:navigate variant="ghost" icon="arrow-left" size="sm">
                {{ __('Back') }}
            </flux:button>
            <flux:heading size="xl">{{ __('Submit a Complaint') }}</flux:heading>
        </div>

        {{-- Step Indicator --}}
        <div class="mb-8 flex items-center gap-3">
            @foreach ([1 => __('Details'), 2 => __('Attachments'), 3 => __('Review')] as $num => $label)
                <div class="flex items-center gap-2">
                    <div @class([
                        'flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold',
                        'bg-blue-600 text-white' => $step >= $num,
                        'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $step < $num,
                    ])>{{ $num }}</div>
                    <span @class([
                        'text-sm',
                        'font-medium text-zinc-800 dark:text-zinc-100' => $step >= $num,
                        'text-zinc-400' => $step < $num,
                    ])>{{ $label }}</span>
                </div>
                @if ($num < 3)
                    <div @class([
                        'h-px flex-1',
                        'bg-blue-400' => $step > $num,
                        'bg-zinc-200 dark:bg-zinc-700' => $step <= $num,
                    ])></div>
                @endif
            @endforeach
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">

            {{-- ── Step 1: Details ── --}}
            @if ($step === 1)
                <flux:heading size="lg" class="mb-6">{{ __('Complaint Details') }}</flux:heading>

                <div class="space-y-5">
                    <flux:input
                        wire:model="title"
                        :label="__('Title')"
                        :placeholder="__('Brief summary of your complaint')"
                        required
                        autofocus
                    />

                    <flux:select wire:model="categoryId" :label="__('Category')" required>
                        <flux:select.option value="">{{ __('Select a category') }}</flux:select.option>
                        @foreach ($this->categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:textarea
                        wire:model="description"
                        :label="__('Description')"
                        :placeholder="__('Describe the issue in detail...')"
                        rows="5"
                        required
                    />

                    <flux:input
                        wire:model="location"
                        :label="__('Location')"
                        :placeholder="__('Address or area of the incident')"
                        icon="map-pin"
                        required
                    />
                </div>

                <div class="mt-6 flex justify-between">
                    <flux:button variant="ghost" wire:click="saveDraft" icon="bookmark">
                        {{ __('Save Draft') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="nextStep" icon-trailing="arrow-right">
                        {{ __('Next: Attachments') }}
                    </flux:button>
                </div>
            @endif

            {{-- ── Step 2: Attachments ── --}}
            @if ($step === 2)
                <flux:heading size="lg" class="mb-2">{{ __('Attach Supporting Files') }}</flux:heading>
                <flux:subheading class="mb-6">
                    {{ __('Optional. Up to 5 files — images (JPG, PNG) or documents (PDF, DOC, DOCX). Max 2 MB each.') }}
                </flux:subheading>

                {{-- Upload input --}}
                <div
                    x-data="{ uploading: false, progress: 0 }"
                    x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    class="mb-4"
                >
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('Files') }}
                    </label>
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 p-6 transition hover:border-blue-400 dark:border-zinc-600 dark:hover:border-blue-500">
                        <flux:icon name="arrow-up-tray" class="mb-2 size-8 text-zinc-400" />
                        <span class="text-sm text-zinc-500">{{ __('Click to browse or drag & drop') }}</span>
                        <span class="mt-1 text-xs text-zinc-400">JPG, PNG, PDF, DOC, DOCX · max 2 MB each</span>
                        <input type="file" wire:model="attachments" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="hidden" />
                    </label>

                    {{-- Upload progress --}}
                    <div x-show="uploading" class="mt-3">
                        <div class="flex items-center justify-between text-xs text-zinc-500">
                            <span>{{ __('Uploading...') }}</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-zinc-200">
                            <div class="h-full rounded-full bg-blue-500 transition-all" x-bind:style="'width:' + progress + '%'"></div>
                        </div>
                    </div>
                </div>

                @error('attachments') <p class="mb-3 text-sm text-red-500">{{ $message }}</p> @enderror
                @error('attachments.*') <p class="mb-3 text-sm text-red-500">{{ $message }}</p> @enderror

                {{-- File list --}}
                @if (count($attachments) > 0)
                    <ul class="mb-4 divide-y divide-zinc-100 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                        @foreach ($attachments as $index => $file)
                            <li class="flex items-center gap-3 px-4 py-3">
                                @if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']))
                                    <img src="{{ $file->temporaryUrl() }}" class="h-10 w-10 rounded object-cover" alt="">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon name="document" class="size-5 text-zinc-400" />
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">{{ $file->getClientOriginalName() }}</p>
                                    <p class="text-xs text-zinc-400">{{ round($file->getSize() / 1024, 1) }} KB</p>
                                </div>
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="x-mark"
                                    wire:click="removeAttachment({{ $index }})"
                                    wire:key="remove-{{ $index }}"
                                />
                            </li>
                        @endforeach
                    </ul>
                    <p class="mb-4 text-xs text-zinc-400">{{ count($attachments) }}/5 {{ __('files selected') }}</p>
                @endif

                <div class="mt-6 flex justify-between">
                    <flux:button variant="ghost" icon="arrow-left" wire:click="prevStep">
                        {{ __('Back') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="nextStep" icon-trailing="arrow-right">
                        {{ __('Next: Review') }}
                    </flux:button>
                </div>
            @endif

            {{-- ── Step 3: Review ── --}}
            @if ($step === 3)
                <flux:heading size="lg" class="mb-6">{{ __('Review & Submit') }}</flux:heading>

                <dl class="space-y-4 text-sm">
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="text-zinc-500">{{ __('Title') }}</dt>
                        <dd class="col-span-2 font-medium">{{ $title }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="text-zinc-500">{{ __('Category') }}</dt>
                        <dd class="col-span-2 font-medium">{{ $this->selectedCategory?->name ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="text-zinc-500">{{ __('Location') }}</dt>
                        <dd class="col-span-2 font-medium">{{ $location }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="text-zinc-500">{{ __('Description') }}</dt>
                        <dd class="col-span-2 leading-relaxed">{{ $description }}</dd>
                    </div>
                    @if (count($attachments) > 0)
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-zinc-500">{{ __('Attachments') }}</dt>
                            <dd class="col-span-2">
                                <ul class="space-y-1">
                                    @foreach ($attachments as $file)
                                        <li class="flex items-center gap-2 text-zinc-700 dark:text-zinc-300">
                                            <flux:icon name="paper-clip" class="size-3.5 text-zinc-400" />
                                            {{ $file->getClientOriginalName() }}
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-6 rounded-lg bg-blue-50 p-4 text-sm text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                    {{ __('Once submitted, your complaint will be assigned a reference number and reviewed by our officers.') }}
                </div>

                <div class="mt-6 flex justify-between">
                    <flux:button variant="ghost" icon="arrow-left" wire:click="prevStep">
                        {{ __('Back') }}
                    </flux:button>

                    <flux:modal.trigger name="confirm-submit">
                        <flux:button variant="primary" icon="paper-airplane">
                            {{ __('Submit Complaint') }}
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            @endif

        </div>
    </flux:main>

    {{-- Confirm Submit Modal --}}
    <flux:modal
        name="confirm-submit"
        class="min-w-[22rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="submit" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Submit this complaint?') }}</flux:heading>
                <flux:subheading>{{ __('A reference number will be generated and officers will be notified.') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="submit" @click="loading = true">
                    {{ __('Submit') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
