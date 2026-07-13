# Beloved Schools production security checklist

This checklist separates application changes from hosting and operational controls. Do not enter real student data until every mandatory item is confirmed in the production environment.

## Application controls implemented in this repository

- Generated temporary passwords are displayed once and are not retained in plaintext.
- Accounts created with generated credentials must change their password before using the portal.
- New student and staff passport uploads are moved to private Laravel storage.
- Private avatars are served only through an authenticated, authorized route.
- Authenticated write actions create audit records without storing submitted form fields or secrets.
- Production HTTP traffic is redirected to HTTPS and secure response headers are applied.
- Private media, exports, credentials, and environment files are excluded from Git.

## Mandatory hosting controls

- Use a managed MySQL or PostgreSQL database with no public inbound access.
- Permit database connections only from the Laravel application network.
- Store application secrets in the hosting platform's secret manager or protected environment variables.
- Use HTTPS with automatic certificate renewal.
- Encrypt database backups and private file backups.
- Keep at least one off-server backup copy.
- Test restoration procedures regularly.
- Configure scheduled Laravel tasks and queue workers where required.
- Restrict server administration access and enable MFA on the hosting provider and GitHub accounts.
- Apply Laravel, PHP, database, operating-system, and dependency security updates.

## Required before broad rollout

- Add MFA inside the application for administrators, principals, and accountants.
- Add an audit-log review screen with retention controls.
- Add automated backup jobs and documented restoration tests.
- Add monitoring for failed logins, application errors, storage capacity, and certificate expiry.
- Review authorization for every role against real school duties.
- Prepare student/parent and staff privacy notices.
- Define retention and deletion periods for records and uploaded documents.
- Establish a breach-response and account-recovery procedure.

## Deployment sequence

1. Deploy with fictitious records only.
2. Run migrations and verify private-media access.
3. Test every role and confirm unauthorized routes return 403.
4. Confirm HTTPS redirect and security headers.
5. Confirm backup creation and complete a restoration test.
6. Train a small administrative pilot group.
7. Add real records gradually while reviewing logs and errors.
8. Enable parent, teacher, and student access only after the pilot is stable.
