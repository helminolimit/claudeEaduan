# Module: Notifications

**Description:** Keeps all parties informed of complaint activity through in-app and email notifications.

---

## Features

- In-app notification bell with unread count
- Email notifications for key events
- Mark as read / mark all as read
- Notification history list
- User-configurable notification preferences (email on/off)

---

## Notification Triggers

| Event | Notified Parties |
|-------|-----------------|
| Complaint submitted | Complainant (confirmation), Admin |
| Complaint assigned to officer | Assigned Officer |
| Status changed | Complainant |
| Complaint rejected | Complainant (with reason) |
| New comment added | Complainant, assigned Officer |
| Complaint resolved | Complainant |
| Complaint closed | Complainant |

---

## Notification Channels

| Channel | Details |
|---------|---------|
| **In-app** | Stored in `notifications` table (Laravel default) |
| **Email** | Sent via Laravel Mailables using queue |

---

## In-App Notification

- Bell icon in top navigation shows unread count badge.
- Dropdown lists the 10 most recent notifications.
- Clicking a notification marks it as read and redirects to the relevant complaint.
- "View all" link opens the full notification history page.

---

## Routes

| Method | URI | Action |
|--------|-----|--------|
| `GET` | `/notifications` | List all notifications |
| `PATCH` | `/notifications/{id}/read` | Mark single as read |
| `PATCH` | `/notifications/read-all` | Mark all as read |

---

## Implementation Notes

- Uses Laravel's built-in `Notification` system with the `database` and `mail` channels.
- Notifications dispatched via queued jobs to avoid blocking the request lifecycle.
- Email templates use Blade Markdown Mailables for consistent styling.
- Users can disable email notifications from their profile settings.
