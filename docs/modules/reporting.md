# Module: Reporting

**Description:** Provides Admins with complaint statistics, analytical charts, and data export capabilities.

---

## Features

- Summary report by date range, category, status, and department
- Visual charts (bar, pie, line)
- Complaint volume trends (daily / weekly / monthly)
- Average resolution time by category and officer
- Export reports to PDF and Excel
- Data scope respects role (Admin sees all; Officer sees own assignments)

---

## Report Types

| Report | Description |
|--------|-------------|
| Summary Report | Total complaints grouped by status and category |
| Trend Report | Submission volume over a selected time period |
| Resolution Report | Average time from submission to resolved/closed |
| Officer Performance | Complaints handled per officer with resolution times |
| Export | Raw filtered data export to Excel (`.xlsx`) or PDF |

---

## Filter Options

| Filter | Options |
|--------|---------|
| Date Range | Start date – End date |
| Status | All, Submitted, In Progress, Resolved, Closed, Rejected |
| Category | All or specific category |
| Priority | All, Low, Medium, High, Critical |
| Officer | All or specific officer (Admin only) |

---

## Routes

| Method | URI | Role | Action |
|--------|-----|------|--------|
| `GET` | `/admin/reports` | Admin | Report dashboard |
| `GET` | `/admin/reports/summary` | Admin | Summary report view |
| `GET` | `/admin/reports/trend` | Admin | Trend chart view |
| `GET` | `/admin/reports/export` | Admin | Export filtered data |

---

## Export Formats

| Format | Library |
|--------|---------|
| Excel (`.xlsx`) | `maatwebsite/excel` |
| PDF | `barryvdh/laravel-dompdf` |

---

## Implementation Notes

- All report queries are cached for 10 minutes to reduce database load.
- Exports are generated as queued jobs for large datasets and made available for download.
- Charts rendered client-side using Chart.js fed with JSON data from Livewire components.
- Report access is restricted to `admin` role only.
