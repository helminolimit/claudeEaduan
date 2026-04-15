# Module: Dashboard

**Description:** Role-specific home screens showing key metrics, recent activity, and quick-action shortcuts.

---

## Features

- Summary statistic cards
- Recent complaints list
- Complaint trend charts
- Quick links to pending actions
- All data scoped to the authenticated user's role

---

## Dashboard by Role

### Complainant Dashboard
| Widget | Description |
|--------|-------------|
| My Complaints Summary | Count by status (submitted, in progress, resolved) |
| Recent Submissions | Last 5 complaints with current status |
| Quick Submit | Button to start a new complaint |

### Officer Dashboard
| Widget | Description |
|--------|-------------|
| Assigned Complaints | Count of open assigned complaints |
| Pending Action | Complaints awaiting status update |
| Recent Activity | Last 10 complaints assigned to this officer |
| Priority Breakdown | Pie chart: low / medium / high / critical |

### Admin Dashboard
| Widget | Description |
|--------|-------------|
| System-wide Totals | All complaints by status |
| Officer Workload | Count per officer |
| Trend Chart | Daily/weekly/monthly submission volume |
| Unassigned Complaints | Complaints with no assigned officer |
| Quick Links | User management, reports, settings |

---

## Routes

| Method | URI | Role | Action |
|--------|-----|------|--------|
| `GET` | `/dashboard` | Complainant | Complainant dashboard |
| `GET` | `/officer/dashboard` | Officer | Officer dashboard |
| `GET` | `/admin/dashboard` | Admin | Admin dashboard |

---

## Implementation Notes

- Dashboard data is loaded via Livewire components with lazy loading for charts.
- Chart data fetched from cached query results (refreshed every 5 minutes).
- Role redirect after login: Admin → `/admin/dashboard`, Officer → `/officer/dashboard`, Complainant → `/dashboard`.
