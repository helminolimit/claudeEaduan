# Module: Complaint Management

**Description:** Provides Officers and Admins with tools to review, assign, prioritise, and resolve complaints.

---

## Features

- List all complaints with filter and search
- Assign complaint to an Officer
- Update complaint status
- Set and change priority level
- Add internal comments / remarks
- Upload additional evidence
- Full audit trail of all status changes
- Reject complaints with a reason

---

## Status Flow

```
Submitted → Under Review → In Progress → Resolved → Closed
                                       ↘ Rejected
```

| Status | Description |
|--------|-------------|
| `submitted` | Newly submitted, awaiting review |
| `under_review` | Officer has viewed and is assessing |
| `in_progress` | Active work has started |
| `resolved` | Issue has been addressed |
| `closed` | Confirmed closed by Admin |
| `rejected` | Complaint rejected with reason |

---

## Priority Levels

| Level | Description |
|-------|-------------|
| `low` | Non-urgent, routine |
| `medium` | Standard SLA applies |
| `high` | Requires fast response |
| `critical` | Immediate action required |

---

## Routes

| Method | URI | Role | Action |
|--------|-----|------|--------|
| `GET` | `/admin/aduan` | Admin | List all complaints |
| `GET` | `/officer/aduan` | Officer | List assigned complaints |
| `GET` | `/aduan/{id}` | All | View complaint detail |
| `PATCH` | `/aduan/{id}/status` | Officer, Admin | Update status |
| `PATCH` | `/aduan/{id}/assign` | Admin | Assign to officer |
| `PATCH` | `/aduan/{id}/priority` | Officer, Admin | Set priority |
| `POST` | `/aduan/{id}/comments` | Officer, Admin | Add comment |
| `DELETE` | `/aduan/{id}` | Admin | Delete complaint |

---

## Implementation Notes

- Status changes are logged to an `aduan_logs` table with user, old status, new status, and timestamp.
- Comments are stored in a `aduan_comments` table linked to the complaint.
- Complainants are notified automatically on every status change.
- Only Admin can assign, close, or delete complaints.
