<div>
    <flux:main>
        {{-- Back button --}}
        <div class="mb-6">
            @if (auth()->user()->isAdmin())
                <flux:button :href="route('admin.aduan.index')" wire:navigate variant="ghost" icon="arrow-left" size="sm">
                    {{ __('Back to Complaints') }}
                </flux:button>
            @elseif (auth()->user()->isOfficer())
                <flux:button :href="route('officer.aduan.index')" wire:navigate variant="ghost" icon="arrow-left" size="sm">
                    {{ __('Back to My Complaints') }}
                </flux:button>
            @else
                <flux:button :href="route('my.aduan.index')" wire:navigate variant="ghost" icon="arrow-left" size="sm">
                    {{ __('Back to My Complaints') }}
                </flux:button>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Left: Complaint Detail --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-sm text-zinc-500">{{ $complaint->aduan_no }}</p>
                            <flux:heading size="xl" class="mt-1">{{ $complaint->title }}</flux:heading>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <flux:badge variant="solid" color="{{ $complaint->status->color() }}">
                                {{ $complaint->status->label() }}
                            </flux:badge>
                            <flux:badge variant="outline" color="{{ $complaint->priority->color() }}">
                                {{ $complaint->priority->label() }}
                            </flux:badge>
                        </div>
                    </div>

                    <dl class="mb-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-zinc-500">{{ __('Filed By') }}</dt>
                            <dd class="mt-0.5 font-medium">{{ $complaint->user?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">{{ __('Category') }}</dt>
                            <dd class="mt-0.5 font-medium">{{ $complaint->category?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">{{ __('Location') }}</dt>
                            <dd class="mt-0.5 font-medium">{{ $complaint->location }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">{{ __('Assigned Officer') }}</dt>
                            <dd class="mt-0.5 font-medium">{{ $complaint->officer?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">{{ __('Submitted') }}</dt>
                            <dd class="mt-0.5 font-medium">{{ $complaint->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                    </dl>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">{{ __('Description') }}</p>
                        <div class="rounded-lg bg-zinc-50 p-4 text-sm leading-relaxed dark:bg-zinc-800">
                            {{ $complaint->description }}
                        </div>
                    </div>
                </div>

                {{-- Attachments --}}
                @if ($complaint->attachments->isNotEmpty())
                    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:heading size="lg" class="mb-4">{{ __('Attachments') }}</flux:heading>
                        <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($complaint->attachments as $attachment)
                                <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                    @if ($attachment->isImage())
                                        <flux:icon name="photo" class="size-5 shrink-0 text-blue-400" />
                                    @else
                                        <flux:icon name="document" class="size-5 shrink-0 text-zinc-400" />
                                    @endif
                                    <span class="min-w-0 flex-1 truncate text-sm">{{ $attachment->file_name }}</span>
                                    <span class="shrink-0 text-xs text-zinc-400">{{ $attachment->formattedSize() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Comments --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" class="mb-4">{{ __('Comments') }}</flux:heading>

                    @if ($this->comments->isEmpty())
                        <p class="text-sm text-zinc-500">{{ __('No comments yet.') }}</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($this->comments as $comment)
                                <div class="flex gap-3">
                                    <flux:avatar :name="$comment->user->name" :initials="$comment->user->initials()" size="sm" class="shrink-0" />
                                    <div class="flex-1">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-sm font-medium">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-1 text-sm">{{ $comment->body }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (auth()->user()->isAdmin() || auth()->user()->isOfficer())
                        <div class="mt-6 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <flux:textarea
                                wire:model="commentBody"
                                :label="__('Add Comment')"
                                rows="3"
                                :placeholder="__('Write a remark or internal note...')"
                            />
                            <div class="mt-3 flex justify-end">
                                <flux:modal.trigger name="confirm-comment">
                                    <flux:button variant="primary" size="sm" icon="chat-bubble-left-ellipsis">
                                        {{ __('Post Comment') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: Management Panel --}}
            <div class="space-y-4">

                @if (auth()->user()->isAdmin() || auth()->user()->isOfficer())
                    {{-- Update Status --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:heading size="sm" class="mb-3">{{ __('Update Status') }}</flux:heading>
                        <flux:select wire:model.live="newStatus" class="mb-3">
                            @foreach ($this->statuses as $status)
                                <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @if ($newStatus === 'rejected')
                            <flux:textarea
                                wire:model="rejectionReason"
                                :placeholder="__('Rejection reason...')"
                                rows="2"
                                class="mb-3"
                            />
                        @endif
                        <flux:modal.trigger name="update-status">
                            <flux:button variant="primary" size="sm" class="w-full">{{ __('Update Status') }}</flux:button>
                        </flux:modal.trigger>
                    </div>

                    {{-- Set Priority --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:heading size="sm" class="mb-3">{{ __('Set Priority') }}</flux:heading>
                        <flux:select wire:model="newPriority" class="mb-3">
                            @foreach ($this->priorities as $priority)
                                <flux:select.option value="{{ $priority->value }}">{{ $priority->label() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:modal.trigger name="update-priority">
                            <flux:button variant="outline" size="sm" class="w-full">{{ __('Save Priority') }}</flux:button>
                        </flux:modal.trigger>
                    </div>
                @endif

                @if (auth()->user()->isAdmin())
                    {{-- Assign Officer --}}
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:heading size="sm" class="mb-3">{{ __('Assign Officer') }}</flux:heading>
                        <flux:select wire:model="assignOfficerId" class="mb-3">
                            <flux:select.option value="">{{ __('Unassigned') }}</flux:select.option>
                            @foreach ($this->officers as $officer)
                                <flux:select.option value="{{ $officer->id }}">{{ $officer->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:modal.trigger name="assign-officer">
                            <flux:button variant="outline" size="sm" class="w-full">{{ __('Assign') }}</flux:button>
                        </flux:modal.trigger>
                    </div>

                    {{-- Danger Zone --}}
                    <div class="rounded-xl border border-red-200 bg-white p-4 dark:border-red-900 dark:bg-zinc-900">
                        <flux:heading size="sm" class="mb-3 text-red-600">{{ __('Danger Zone') }}</flux:heading>
                        <flux:modal.trigger name="delete-complaint">
                            <flux:button variant="danger" size="sm" icon="trash" class="w-full">{{ __('Delete Complaint') }}</flux:button>
                        </flux:modal.trigger>
                    </div>
                @endif

                {{-- Audit Log --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-3">{{ __('Activity Log') }}</flux:heading>
                    @if ($this->logs->isEmpty())
                        <p class="text-sm text-zinc-500">{{ __('No activity yet.') }}</p>
                    @else
                        <ol class="relative border-s border-zinc-200 dark:border-zinc-700">
                            @foreach ($this->logs as $log)
                                <li class="mb-4 ms-4">
                                    <div class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-zinc-300 dark:border-zinc-900 dark:bg-zinc-600"></div>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        @if ($log->old_status)
                                            <flux:badge color="{{ $log->old_status->color() }}" size="sm">{{ $log->old_status->label() }}</flux:badge>
                                            <flux:icon name="arrow-right" class="size-3 text-zinc-400" />
                                        @endif
                                        <flux:badge variant="solid" color="{{ $log->new_status->color() }}" size="sm">{{ $log->new_status->label() }}</flux:badge>
                                    </div>
                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{ $log->user?->name ?? '—' }} · {{ $log->created_at->diffForHumans() }}
                                    </p>
                                    @if ($log->notes)
                                        <p class="mt-1 text-xs italic text-zinc-400">{{ $log->notes }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </flux:main>

    {{-- Confirm Update Status --}}
    <flux:modal
        name="update-status"
        class="min-w-[22rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="updateStatus" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Confirm status update?') }}</flux:heading>
                <flux:subheading>{{ __('The complainant will be notified of this change.') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="updateStatus" @click="loading = true">{{ __('Confirm') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Confirm Update Priority --}}
    <flux:modal
        name="update-priority"
        class="min-w-[22rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="updatePriority" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Save priority?') }}</flux:heading>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="updatePriority" @click="loading = true">{{ __('Save') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Confirm Assign Officer --}}
    <flux:modal
        name="assign-officer"
        class="min-w-[22rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="assign" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Confirm assignment?') }}</flux:heading>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="assign" @click="loading = true">{{ __('Assign') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Confirm Post Comment --}}
    <flux:modal
        name="confirm-comment"
        class="min-w-[22rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="addComment" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Post this comment?') }}</flux:heading>
                <flux:subheading>{{ __('Comments are visible to all staff involved.') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="addComment" @click="loading = true">{{ __('Post') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Complaint --}}
    <flux:modal
        name="delete-complaint"
        class="min-w-[22rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="delete" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Delete this complaint?') }}</flux:heading>
                <flux:subheading>{{ __('This action cannot be undone.') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete" @click="loading = true">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
