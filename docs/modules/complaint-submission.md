# Module: Complaint Submission

**Description:** Allows authenticated Complainants to submit complaints through a structured form with supporting evidence.

---

## Features

- Multi-step complaint form
- Category selection
- Free-text description
- Location input
- Multiple file attachments (images, documents)
- Draft saving before final submission
- Reference number issued on submission

---

## Form Fields

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| Title | Text | Yes | Short summary of the complaint |
| Category | Dropdown | Yes | Predefined categories managed by Admin |
| Description | Textarea | Yes | Full detail of the complaint |
| Location | Text | Yes | Address or area of the incident |
| Attachments | File (multi) | No | Images, PDFs — max 5 files, 2MB each |

---

## Accepted File Types

- Images: `jpg`, `jpeg`, `png`
- Documents: `pdf`, `doc`, `docx`
- Max size per file: **2MB**
- Max files per submission: **5**

---

## Complaint Status on Submission

A newly submitted complaint is assigned the status `submitted` automatically.

---

## Routes

| Method | URI | Action |
|--------|-----|--------|
| `GET` | `/aduan/create` | Show submission form |
| `POST` | `/aduan` | Store new complaint |
| `GET` | `/aduan/{id}` | View submitted complaint detail |

---

## Implementation Notes

- File uploads stored in `storage/app/private/aduan/{id}/`.
- Reference number format: `ADU-YYYYMMDD-XXXX` (e.g. `ADU-20260415-0001`).
- Complainants must be email-verified before submitting.
- Livewire component handles real-time validation and file preview.
