# Hastakshar Project Doc

## Overview

This is a local PHP + MySQL membership management app for a community organization. It supports:

- public landing page and member onboarding
- member registration with rules acceptance
- member login using mobile number and PIN
- pending / approved / rejected member flow
- payment screenshot upload for membership fee proof
- member-side voting with time-window control
- admin-side member approval, search, edits, vote enablement, and voting results

The project is currently placed at `E:\xampp\htdocs\hastakshr` and is configured for local MySQL through `config/db.php`.

## Main Modules

### Public / Member Side

- `index.php`: landing page with register and login entry points
- `rules.php` + `rules_process.php`: terms/rules acceptance before registration
- `register.php` + `register_process.php`: member registration form and member creation
- `login.php` + `login_process.php`: member authentication
- `forgot_pin.php` + `forgot_pin_process.php`: PIN reset request flow via WhatsApp/SMS to admin
- `pending.php`: shown when member approval is still pending
- `dashboard.php`: approved member dashboard
- `payment.php`, `payment_upload.php`, `upload_payment.php`: fee payment QR and screenshot upload
- `vote.php`, `vote_submit.php`, `vote_results.php`: voting flow and result confirmation for members

### Admin Side

- `admin/login.php` + `admin/login_process.php`: admin authentication
- `admin/dashboard.php`: admin summary, search, approval actions, quick links
- `admin/members.php`: paginated member listing by status
- `admin/member_action.php`: approve / reject member status updates
- `admin/member_toggle_vote.php`: enable voting for approved members
- `admin/member_detail.php`, `admin/member_edit.php`: member inspection and edit flow
- `admin/member_add.php`, `admin/member_add_process.php`: admin-side member creation
- `admin/voting_settings.php`: voting start/end window activation
- `admin/vote_results.php`: aggregated vote counts
- `admin/logs.php`: admin login / approval related logs

### Shared / Support Files

- `config/db.php`: MySQL connection
- `includes/*.php`: shared layout and dropdown data
- `assets/images/*`: banner and payment QR images
- `uploads/payments/*`: uploaded payment proofs
- `290126/`: older snapshot / backup copy of an earlier version

## Likely Database Tables

Based on the code, the project appears to rely on at least these tables:

- `members`
- `admins`
- `votes`
- `voting_settings`
- `admin_login_logs`
- `member_approval_logs`

Important member fields used in code include:

- `id`, `name`, `nivasi`, `gotra`, `mobile`
- `pin`, `security_question`, `security_answer`
- `status`
- `payment_screenshot`, `payment_status`
- `is_canvote`
- `created_at`

## Current Workflow

1. User opens `index.php`
2. User accepts rules
3. User registers with personal details, mobile, and PIN
4. User uploads payment screenshot
5. Admin reviews and approves or rejects the member
6. Approved member logs in and uses dashboard
7. If voting is active and allowed, member can cast one vote
8. Admin can monitor results and control the voting window

## Current Observations

- The app is functional and organized around direct PHP page handlers instead of a framework.
- Security is mixed:
  - Good: password hashing is used for member PINs and admin passwords.
  - Risk: some pages still build SQL using raw request values and should be parameterized.
- Payment upload is implemented in two slightly different ways:
  - `payment_upload.php`
  - `upload_payment.php`
  This may be intentional from two different flows, but it is worth consolidating.
- The `290126` folder looks like an archived version and may confuse maintenance if both copies continue evolving.
- Hindi text appears garbled in terminal output, which suggests an encoding issue in files or console rendering. The browser may still render correctly depending on file encoding.

## Suggested To-Do

- Add a proper SQL schema dump for local setup.
- Create a `README.md` with setup steps for XAMPP, Apache, and MySQL.
- Audit all SQL queries and convert raw interpolated queries to prepared statements.
- Standardize payment upload to one code path and one stored file format.
- Add validation for file uploads, file size, and MIME type.
- Verify all redirects and status transitions for pending, approved, rejected, and payment review states.
- Confirm the exact database structure and seed at least one local admin user.
- Decide whether `290126/` should be kept as backup or moved outside the web root.
- Add basic error logging instead of generic `die()` messages.

## Local Setup Note

Current local DB settings:

- host: `localhost`
- database: `hastakshar`
- username: `root`
- password: blank

