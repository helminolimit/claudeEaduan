# Module: Authentication

**Description:** Handles user identity, session management, and role-based access control across all three roles — Complainant, Officer, and Admin.

---

## Features

- User registration (Complainant self-registration)
- Login with email and password
- Role-based access control (RBAC)
- Password reset via email link
- Email verification on registration
- Two-factor authentication (2FA)
- Session management and logout

---

## Roles & Permissions

| Role | Access Level |
|------|-------------|
| `complainant` | Own profile, own complaints only |
| `officer` | Assigned complaints, status updates |
| `admin` | Full system access |

---

## Routes

| Method | URI | Action |
|--------|-----|--------|
| `GET` | `/register` | Show registration form |
| `POST` | `/register` | Create new complainant account |
| `GET` | `/login` | Show login form |
| `POST` | `/login` | Authenticate user |
| `POST` | `/logout` | End session |
| `GET` | `/forgot-password` | Show forgot password form |
| `POST` | `/forgot-password` | Send reset link |
| `GET` | `/reset-password/{token}` | Show reset form |
| `POST` | `/reset-password` | Update password |
| `GET` | `/email/verify` | Email verification notice |
| `GET` | `/email/verify/{id}/{hash}` | Verify email address |

---

## Implementation Notes

- Uses **Laravel Fortify** as the authentication backend.
- Middleware `auth`, `verified`, and `role:*` guard protected routes.
- Officers and Admins are created by Admin only — no public registration for these roles.
- Passwords must meet minimum complexity: 8+ characters.
