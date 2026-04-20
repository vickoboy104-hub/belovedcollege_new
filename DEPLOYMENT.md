# Deployment Guide

This project is configured for automatic deployment to:

- GitHub account: `github.com/vickoboy`
- Production domain: `https://belovedschool.10001mb.com`
- Hosting folder: `/home/vol14_8/byethost31.com/b31_41186941/belovedschool.10001mb.com`
- Web root: `/home/vol14_8/byethost31.com/b31_41186941/belovedschool.10001mb.com/htdocs`

## Deployment model

This host is treated as FTP-only shared hosting, and deployment is limited to this web root:

`/home/vol14_8/byethost31.com/b31_41186941/belovedschool.10001mb.com/htdocs`

GitHub Actions will:

1. install Composer dependencies
2. install Node dependencies
3. build Vite assets
4. upload the entire Laravel project into the allowed `htdocs` folder
5. place a root `.htaccess` file that routes requests into `/public`
6. block direct web access to sensitive Laravel folders and files

## One-time server setup

Inside the `htdocs` folder, make sure these remain writable by PHP:

- `storage`
- `bootstrap/cache`

## Production .env

Create this file on the server manually and never commit it:

`/home/vol14_8/byethost31.com/b31_41186941/belovedschool.10001mb.com/htdocs/.env`

Start with:

```env
APP_NAME="Beloved School"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://belovedschool.10001mb.com
APP_KEY=base64:GENERATE_THIS_LOCALLY

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=sql110.byethost31.com
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=b31_41186941
DB_PASSWORD=your_hosting_password
```

Generate the app key locally with:

```bash
php artisan key:generate --show
```

Then paste that value into the server `.env`.

## GitHub Actions secrets

In GitHub, open:

`Repository -> Settings -> Secrets and variables -> Actions`

Add these repository secrets:

- `FTP_SERVER` = `ftp.byethost31.com`
- `FTP_USERNAME` = `b31_41186941`
- `FTP_PASSWORD` = your ByetHost / VistaPanel password

If `ftp.byethost31.com` does not connect for your account, test `ftpupload.net` manually and replace the secret value.

## GitHub branch rule

Production deploys when code is pushed to:

- `main`

Recommended workflow:

1. create a feature branch locally
2. test locally
3. merge into `main`
4. push `main`
5. GitHub Actions deploys automatically

## Local commands before pushing

Run these locally before you push:

```bash
php artisan test
cmd /c npm run build
```

If your project uses seeders or manual checks, run those too before merging to `main`.

## First deployment checklist

Before the first automatic deploy:

1. Create the MySQL database in your hosting panel.
2. Upload or create the production `.env` file on the server.
3. Ensure `storage` and `bootstrap/cache` are writable.
4. Push this repository to GitHub under `vickoboy`.
5. Add the FTP secrets in GitHub.
6. Push to `main`.
7. Confirm that the uploaded root `.htaccess` is active and that the domain serves the app correctly.

## Important limitations of this host

This setup does not assume SSH access.

That means:

- database migrations may need to be handled manually if CLI access is unavailable
- queue workers and scheduler features may be limited
- server-side Composer or Node commands are not part of deployment
- this layout is less ideal than a true Laravel `public` document root, so the root `.htaccess` matters for security

If you later move to a VPS, switch to SSH-based deployment for a cleaner Laravel production workflow.
