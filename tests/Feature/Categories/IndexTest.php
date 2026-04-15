<?php

use App\Livewire\Categories\Index;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

test('categories page requires authentication', function () {
    $this->get(route('categories.index'))->assertRedirect(route('login'));
});

test('categories page renders for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('categories.index'))
        ->assertOk();
});

test('categories are listed', function () {
    $user = User::factory()->create();
    $categories = Category::factory()->count(3)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee($categories->first()->name);
});

test('categories can be searched', function () {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Fruits']);
    Category::factory()->create(['name' => 'Vegetables']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Fruits')
        ->assertSee('Fruits')
        ->assertDontSee('Vegetables');
});

test('a category can be created', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', 'Electronics')
        ->set('description', 'Electronic items')
        ->set('isActive', true)
        ->call('save');

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics',
        'description' => 'Electronic items',
        'is_active' => true,
    ]);
});

test('a category name is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('a category can be edited', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openEditModal', $category)
        ->assertSet('editingId', $category->id)
        ->assertSet('name', 'Old Name')
        ->set('name', 'New Name')
        ->call('save');

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
});

test('a category can be deleted', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('delete', $category);

    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});
