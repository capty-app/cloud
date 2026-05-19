# User roles & access

The app has two roles and three access modes for galleries.

## Roles

| Role | Capabilities |
| --- | --- |
| **Admin** | Manage galleries (create, edit, delete, rotate token). Manage users (create, edit, delete). Delete any item or comment. Can do everything a user can do. |
| **User** | View galleries they have access to. Post and delete their own comments (where comments are enabled). |

There is **no public signup**. Only admins can create users. The very first admin is created on the `/setup` page on first run.

## Gallery visibility

When you create or edit a gallery, you choose one of:

- **Public** — anyone with the gallery link or any item's short link can view it. No login needed.
- **Private** — only signed-in users (any role) can view. Anonymous visitors are redirected to `/login`.

Both modes use the same short URLs (`/g/{slug}`, `/s/{code}`, `/f/{code}`). The only difference is the access check on every request.

## Comments

Each gallery has a **Comments enabled** toggle.

- When **on**: signed-in users can post comments on items. Public visitors can still view but cannot comment.
- When **off**: no one can post comments. Existing comments remain visible.

A user can delete their own comments. Admins can delete any comment.

## Upload tokens vs. user logins

The two auth mechanisms are independent:

- **User login (session cookie)** — for browsing the dashboard and viewing private galleries.
- **Gallery API token (`Authorization: Bearer …`)** — for the upload endpoint only.

This means you can give external services (cron jobs, mobile apps, etc.) an upload token without giving them any access to view or browse — and vice versa.

## Last-admin safety

The app prevents you from accidentally locking yourself out:

- You cannot demote the last admin to a regular user.
- You cannot delete the last admin.
- You cannot delete yourself.
