<?php

use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Complaints\AdminIndex;
use App\Livewire\Complaints\Create as ComplaintCreate;
use App\Livewire\Complaints\Index as ComplaintsIndex;
use App\Livewire\Complaints\MyComplaints;
use App\Livewire\Complaints\OfficerIndex;
use App\Livewire\Complaints\Show as ComplaintShow;
use App\Livewire\Notifications\Index as NotificationsIndex;
use App\Livewire\Reports\Dashboard as ReportsDashboard;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('categories', CategoriesIndex::class)->name('categories.index');
    Route::livewire('complaints', ComplaintsIndex::class)->name('complaints.index');

    // Complaint submission (complainant)
    Route::get('aduan/create', ComplaintCreate::class)->name('aduan.create');

    // Complainant's own complaint list
    Route::livewire('my-aduan', MyComplaints::class)->name('my.aduan.index');

    // Complaint detail — accessible to all authenticated users
    Route::get('aduan/{complaint}', ComplaintShow::class)->name('aduan.show');

    // Notifications
    Route::livewire('/notifications', NotificationsIndex::class)->name('notifications.index');
});

// Admin complaint management
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    Route::livewire('aduan', AdminIndex::class)->name('admin.aduan.index');
    Route::livewire('reports', ReportsDashboard::class)->name('admin.reports');
});

// Officer complaint management
Route::middleware(['auth', 'verified', 'role:officer'])->prefix('officer')->group(function () {
    Route::livewire('aduan', OfficerIndex::class)->name('officer.aduan.index');
});

require __DIR__.'/settings.php';
