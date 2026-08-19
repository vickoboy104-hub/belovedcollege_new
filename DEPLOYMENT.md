# Production Deployment Guide

This repository belongs to:

- GitHub repository: `vickoboy104-hub/belovedcollege_new`
- Production domain currently documented for the project: `https://belovedschool.10001mb.com`
- Shared-hosting web root currently documented for the project: `/home/vol14_8/byethost31.com/b31_41186941/belovedschool.10001mb.com/htdocs`

Verify the domain, FTP host, and remote directory in the hosting panel before the first real deployment.

## Deployment model

The project includes a **manual-only** GitHub Actions workflow:

`.github/workflows/manual-production-deploy.yml`

A push or merge to `main` does **not** deploy the website automatically.

The workflow must be started intentionally from the GitHub Actions tab. Its `dry_run` option defaults to `true`, so the first run only previews FTP changes.

The deployment workflow:

1. checks out the selected repository revision
2. installs production Composer dependencies with `--no-dev`
3. installs Node dependencies
4. builds Vite production assets
5. verifies required FTP secrets exist
6. synchronizes the prepared Laravel project to the configured FTP directory
7. preserves the server `.env` and excludes development/test/runtime files

The workflow does **not** run database migrations on the production server.

## Web-root security

The preferred Laravel hosting layout is to point the domain document root directly to the repository's `/public` directory.

If the shared host forces the entire Laravel project to live inside `htdocs`, this repository now includes a root `.htaccess` file that:

- routes normal web requests into `/public`
- blocks direct requests to `app`, `bootstrap`, `config`, `database`, `resources`, `routes`, `storage`, `tests`, `vendor`, and `node_modules`
- blocks environment files and common project metadata/CLI files
- leaves `/.well-known` available for hosting/SSL verification

The standard Laravel `public/.htaccess` remains the final application front controller.

## Required GitHub production secrets

In GitHub, open the repository and configure the `production` environment (recommended) or repository Actions secrets.

Required secrets:

- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`
- `FTP_SERVER_DIR`

`FTP_SERVER_DIR` must be the FTP-visible directory that corresponds to the site's web root and should end with `/` when required by the FTP account layout.

Optional GitHub Actions variables:

- `FTP_PROTOCOL` - `ftp`, `ftps`, or `ftps-legacy`; defaults to `ftp`
- `FTP_PORT` - defaults to `21`

Prefer `ftps` whenever the hosting account supports it so credentials and file transfers are encrypted.

Never commit FTP credentials, database credentials, API secrets, or the production `.env`.

## Production `.env`

Create the production `.env` manually on the server and keep it outside Git.

Example starting point:

```env
APP_NAME="Beloved School"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://belovedschool.10001mb.com
APP_TIMEZONE=Africa/Lagos
APP_KEY=base64:GENERATE_A_REAL_KEY

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=YOUR_MYSQL_HOST
DB_PORT=3306
DB_DATABASE=YOUR_DATABASE_NAME
DB_USERNAME=YOUR_DATABASE_USERNAME
DB_PASSWORD=YOUR_DATABASE_PASSWORD

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS=YOUR_SCHOOL_EMAIL
MAIL_FROM_NAME="Beloved School"
```

Generate the application key locally with:

```bash
php artisan key:generate --show
```

Paste only the generated value into the production `.env`.

For real contact-email delivery, configure SMTP either through the production environment or the protected mail settings in the administrator portal. Until real mail is configured, contact messages are still stored in the portal database.

## Writable directories

PHP must be able to write to:

- `storage`
- `bootstrap/cache`

Student and staff passport/avatar files are stored privately under Laravel storage and served through authenticated application routes, so `storage` must remain writable and must not be publicly exposed.

## Production database initialization

The default `DatabaseSeeder` contains demo school data and demo accounts. Destructive/demo database commands are blocked when `APP_ENV=production`.

Do **not** use these against the live database:

- `php artisan db:seed`
- `php artisan db:wipe`
- `php artisan migrate:fresh`
- `php artisan migrate:refresh`
- `php artisan migrate:reset`
- `php artisan migrate:rollback`

Normal forward migrations remain allowed:

```bash
php artisan migrate --force
```

If the hosting package has no SSH/terminal access, apply the production schema through the hosting database tools (for example, its database administration/import facility) as a separate controlled deployment step. Do not point a local destructive migration command at the live database.

## First FTP deployment

1. Confirm the production database exists.
2. Confirm the production `.env` exists on the server and contains a real `APP_KEY`.
3. Confirm `storage` and `bootstrap/cache` are writable.
4. Configure the GitHub `production` environment secrets.
5. Set `FTP_PROTOCOL=ftps` if the hosting account supports explicit FTPS.
6. In GitHub Actions, run **Manual production FTP deploy** with `dry_run=true`.
7. Review the dry-run log carefully. The server `.env` must not appear as a file to delete or upload.
8. When the preview is correct, run the workflow again with `dry_run=false`.
9. Apply any required forward database migrations separately.
10. Verify the production home page, login pages, contact form, student portal, staff/admin portal, private avatars, result checker, and payment configuration.

## Before a production release

A production release should only proceed after the Quality Checks workflow is green and the production branch has been reviewed.

Recommended checks:

```bash
composer test
npm run build
php artisan route:list --except-vendor
```

Do not deploy from an untested local working tree.

## Shared-hosting limitations

This hosting model does not assume SSH access. As a result:

- migrations may require a separate database administration step
- long-running queue workers are not assumed; production should use `QUEUE_CONNECTION=sync` unless the host provides a worker mechanism
- scheduler/cron features require explicit hosting-panel configuration if the application later depends on them
- the entire-project-in-`htdocs` layout is less ideal than a true `/public` document root, which is why the root `.htaccess` protection is required

If the project moves to a VPS or managed Laravel platform later, prefer an SSH-based deployment with the web root pointed directly at `/public`.