<?php

use App\Models\Complaint;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;

it('notifies complainant and admins when complaint is submitted', function () {
    $complainant = User::factory()->complainant()->create();
    $admin = User::factory()->admin()->create();
    $complaint = Complaint::factory()->pending()->create(['user_id' => $complainant->id]);

    app(NotificationService::class)->complaintSubmitted($complaint);

    expect(Notification::where('user_id', $complainant->id)->where('type', 'complaint_submitted')->exists())->toBeTrue();
    expect(Notification::where('user_id', $admin->id)->where('type', 'new_complaint')->exists())->toBeTrue();
});

it('notifies officer when complaint is assigned', function () {
    $officer = User::factory()->officer()->create();
    $complaint = Complaint::factory()->create(['officer_id' => $officer->id]);

    app(NotificationService::class)->complaintAssigned($complaint);

    expect(Notification::where('user_id', $officer->id)->where('type', 'complaint_assigned')->exists())->toBeTrue();
});

it('does not create notification when complaint has no officer', function () {
    $complaint = Complaint::factory()->create(['officer_id' => null]);

    app(NotificationService::class)->complaintAssigned($complaint);

    expect(Notification::where('type', 'complaint_assigned')->count())->toBe(0);
});

it('notifies complainant when status changes', function () {
    $complainant = User::factory()->complainant()->create();
    $complaint = Complaint::factory()->resolved()->create(['user_id' => $complainant->id]);

    app(NotificationService::class)->statusChanged($complaint);

    $notification = Notification::where('user_id', $complainant->id)
        ->where('type', 'status_changed')
        ->first();

    expect($notification)->not->toBeNull();
    expect($notification->message)->toContain($complaint->aduan_no);
});

it('notifies complainant and officer when comment is added by another party', function () {
    $complainant = User::factory()->complainant()->create();
    $officer = User::factory()->officer()->create();
    $commenter = User::factory()->create();
    $complaint = Complaint::factory()->create([
        'user_id' => $complainant->id,
        'officer_id' => $officer->id,
    ]);

    app(NotificationService::class)->commentAdded($complaint, $commenter->id);

    expect(Notification::where('user_id', $complainant->id)->where('type', 'comment_added')->exists())->toBeTrue();
    expect(Notification::where('user_id', $officer->id)->where('type', 'comment_added')->exists())->toBeTrue();
});

it('does not notify the commenter themselves', function () {
    $complainant = User::factory()->complainant()->create();
    $complaint = Complaint::factory()->create([
        'user_id' => $complainant->id,
        'officer_id' => null,
    ]);

    app(NotificationService::class)->commentAdded($complaint, $complainant->id);

    expect(Notification::where('user_id', $complainant->id)->where('type', 'comment_added')->count())->toBe(0);
});

it('marks a notification as read', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'user_id' => $user->id,
        'type' => 'test',
        'message' => 'Test notification',
        'is_read' => false,
    ]);

    $notification->markAsRead();

    expect($notification->fresh()->is_read)->toBeTrue();
});
