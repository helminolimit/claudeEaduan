<?php

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Livewire\Complaints\Index;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Livewire\Livewire;

test('complaints page requires authentication', function () {
    $this->get(route('complaints.index'))->assertRedirect(route('login'));
});

test('complaints page renders for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('complaints.index'))
        ->assertOk();
});

test('complaints are listed', function () {
    $user = User::factory()->create();
    $complaint = Complaint::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee($complaint->title);
});

test('complaints can be searched by title', function () {
    $user = User::factory()->create();
    Complaint::factory()->create(['user_id' => $user->id, 'title' => 'Road pothole near school']);
    Complaint::factory()->create(['user_id' => $user->id, 'title' => 'Broken streetlight']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'pothole')
        ->assertSee('Road pothole near school')
        ->assertDontSee('Broken streetlight');
});

test('complaints can be filtered by status', function () {
    $user = User::factory()->create();
    Complaint::factory()->pending()->create(['user_id' => $user->id, 'title' => 'Pending complaint']);
    Complaint::factory()->resolved()->create(['user_id' => $user->id, 'title' => 'Resolved complaint']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('filterStatus', ComplaintStatus::Pending->value)
        ->assertSee('Pending complaint')
        ->assertDontSee('Resolved complaint');
});

test('a complaint can be created', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('title', 'Water pipe burst')
        ->set('description', 'Main water pipe has burst near junction')
        ->set('location', 'Jalan Ampang, KL')
        ->set('categoryId', $category->id)
        ->set('status', ComplaintStatus::Pending->value)
        ->set('priority', ComplaintPriority::High->value)
        ->call('save');

    $this->assertDatabaseHas('complaints', [
        'title' => 'Water pipe burst',
        'user_id' => $user->id,
        'category_id' => $category->id,
        'status' => 'pending',
        'priority' => 'high',
    ]);
});

test('a complaint title is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

test('a complaint can be edited', function () {
    $user = User::factory()->create();
    $complaint = Complaint::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openEditModal', $complaint)
        ->assertSet('editingId', $complaint->id)
        ->set('title', 'Updated title')
        ->set('status', ComplaintStatus::InProgress->value)
        ->call('save');

    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'title' => 'Updated title',
        'status' => 'in_progress',
    ]);
});

test('a complaint can be deleted', function () {
    $user = User::factory()->create();
    $complaint = Complaint::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('delete', $complaint);

    $this->assertSoftDeleted('complaints', ['id' => $complaint->id]);
});

test('aduan_no is auto-generated on create', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['is_active' => true]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('title', 'Test complaint')
        ->set('description', 'Test description')
        ->set('location', 'Test location')
        ->set('categoryId', $category->id)
        ->set('status', ComplaintStatus::Pending->value)
        ->set('priority', ComplaintPriority::Medium->value)
        ->call('save');

    $complaint = Complaint::first();
    expect($complaint->aduan_no)->toStartWith('ADU-');
});
