# Beloved Schools production-readiness audit

Date: 13 July 2026

## Audit scope

This audit reviewed the Laravel routes, authentication flow, role middleware, payment callbacks and webhooks, student/parent portal access, CBT submission rules, private media, settings handling, report printing contracts, responsive CSS, and the automated test suite.

Automated verification covers public page availability, login variants, role separation, inactive accounts, private media, password replacement, result printing, payment verification, sensitive settings, expired CBT attempts, mobile CSS contracts, and frontend production compilation.

This is a source-code and automated-behaviour audit. A local address such as `127.0.0.1` is not reachable outside the owner's computer, so final pixel-level browser verification still requires a public staging URL or screenshots from the running system.

## Critical findings corrected during the audit

### 1. Unverified PalmPay success data could mark payments as paid

The PalmPay callback previously trusted `status=success` from public query parameters, and the PalmPay webhook accepted unsigned payloads. This created a direct payment-integrity risk.

Current behaviour now fails closed:

- Public query parameters cannot complete a PalmPay payment.
- A callback provider must match the provider used to create the payment.
- PalmPay payments remain pending until official server-to-server verification is implemented.
- Unverified PalmPay webhooks return an unavailable response and do not update finance records.

### 2. Paystack completion did not verify the expected amount and currency

A successful Paystack response is now accepted only when the reference, amount, currency, and status match the locally created payment. Paystack webhook processing applies the same amount and currency checks.

### 3. Public self-registration allowed unmanaged accounts

The public registration endpoints created users without a school-assigned role and immediately logged them in. School portal accounts must be created and controlled by authorized school staff, so public self-registration is now disabled.

### 4. Deactivated users could authenticate or retain an active session

Inactive accounts are now rejected using the same generic login failure as invalid credentials. If an already authenticated account is deactivated, its session is invalidated on the next request.

### 5. Payment and mail secrets were stored and shared as ordinary settings

Sensitive values are now encrypted before database storage. They are decrypted only for server-side use and are removed from globally shared view data. Admin settings forms receive blank secret fields, and submitting a blank field preserves the existing configured secret.

### 6. Expired CBT attempts could still reach the submission controller

CBT submissions now require an active assessment, matching class, enabled school CBT setting, valid assessment window, in-progress attempt, and unexpired attempt timer. Expired attempts are marked as expired without creating a submission.

### 7. Public forms lacked sufficient request throttling

Rate limits were added to contact submissions, result-checker lookups, password reset requests, password updates, authentication endpoints, payment callbacks, and webhook endpoints.

## Areas that passed automated checks

- Public homepage, About, Admissions, Contact, result checker, and login pages render.
- Student, staff, and general login routes remain available.
- Password Show/Hide controls exist on login, reset, confirmation, and profile password forms.
- Student, teacher, accountant, principal, and administrator role boundaries are enforced at route level.
- Students cannot enter administrative finance workspaces.
- Teachers and accountants cannot enter the administrator People Hub.
- Student portal access is limited to the authenticated student or a parent's linked children.
- Temporary passwords are not retained in plaintext and must be changed before normal access.
- New passport uploads use private storage and authorized delivery routes.
- Authenticated write operations create audit records without recording request bodies or passwords.
- Modern and classic reports retain their A4 portrait, one-page print contracts.
- Frontend assets compile through the production build.

## Remaining high-priority work

### PalmPay integration

PalmPay is intentionally unable to confirm payments until its official API verification and webhook-signature process is implemented and tested with a real merchant sandbox. The PalmPay option should remain unavailable to users until then.

### Multi-factor authentication

Administrators, principals, and accountants still need MFA. This is required before broad access to real student, staff, and finance data.

### Large-dataset performance

Several administrative workspaces load complete collections into PHP memory before filtering or summarizing them. The main examples are student management, report directories, and finance records. These should be converted to database-level searching, aggregation, and pagination before the database grows to hundreds or thousands of students.

### Teacher authorization policy

The present model distinguishes administrators/principals, class teachers, and teachers who created particular content. A teacher without a managed class can see broad class selections in parts of the teaching workspace. The school must define whether subject teachers may work across all classes or only specifically assigned classes and subjects; the code should then enforce that policy explicitly.

### Assignment deadlines

Student assignment submissions validate class ownership, but the backend does not currently block submission or replacement after the assignment due date. The school needs a clear late-submission policy and corresponding enforcement.

### Result-checker PIN strength

Checker PINs currently allow 4–12 digits. Request throttling reduces brute-force attempts, but production use should prefer at least six digits and consider attempt logging or temporary lockout per admission number and report.

### Backup and recovery operations

The repository cannot prove hosting-level backup reliability. Production launch still requires encrypted daily database and private-file backups, multiple retained versions, an off-server copy, and documented restoration tests.

### Monitoring and incident response

Production still needs failed-login monitoring, application-error alerts, storage and database capacity monitoring, SSL expiry alerts, audit-log review, and a documented incident-response process.

## UI and UX findings

The supplied mobile screenshots were used to correct the shared mobile shell, page gutters, dashboard card density, report search height, student-name wrapping, class navigation, and bottom-navigation behaviour.

The following still require live rendered verification:

- Finance desk and finance-record pages on 320–412px phones.
- Teacher forms, file upload controls, CBT authoring, and CBT exam screens.
- Student and parent portal sections with long names, many invoices, long lesson text, and empty states.
- Modal focus, keyboard appearance, dropdowns, date inputs, and destructive-action confirmations.
- Firefox and Safari-compatible behaviour.
- Actual print-preview output with unusually long remarks and maximum subject counts.

Recommended viewport matrix: 320, 360, 375, 390, 412, 768, 1024, 1280, 1366, 1440, and 1920 pixels.

## Launch assessment

The application is substantially safer after the audit corrections, but it should remain in controlled pilot mode. Broad production rollout should wait until PalmPay is either fully verified or removed from user-facing checkout, MFA is enabled for sensitive roles, backup restoration is proven, teacher permissions are formally defined, and large-data workspaces use database pagination.
