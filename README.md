# NeuKai

NeuKai is a web application designed to facilitate charity donations and management. It provides functionalities for donors to contribute to various causes and for administrators to manage donations and charity events.

## Features

- Donor registration and authentication
- Charity listings with details
- Donation management and history tracking
- Admin dashboard for managing users, events, and donations
- Responsive design for desktop and mobile

## Technologies used

- PHP 8.1+ (mysqli, no framework)
- MySQL / MariaDB
- PHPMailer (vendored in `vendor/phpmailer/phpmailer`)
- HTML, CSS, JavaScript (Tailwind CDN)
- Node.js is only used for local formatting tooling (`package.json`)

## Project structure

```
.
├── app/                      Application code – MUST NOT be web-reachable
│   ├── bootstrap.php         Single entry include: env, session, headers, DB
│   ├── config/               env loader, helpers, database, mail settings
│   ├── includes/             Shared nav / footer / background fragments
│   └── services/mail/        PHPMailer wrapper + per-role mail templates
├── public/                   Web root – the only publicly served folder
│   ├── assets/               css, js, images
│   ├── admin/                Admin pages (guarded)
│   ├── charity/              Charity pages (guarded)
│   ├── donor/                Donor pages (guarded)
│   ├── uploads/              Public donation images (PHP execution disabled)
│   └── index.php             Landing page
├── storage/                  Private files – never web-served
│   └── id_verifications/     Password-reset ID images (admin-only proxy)
├── vendor/                   Composer-style dependencies (committed)
├── .env                      Local secrets – NOT committed
├── .env.example              Template for .env
└── .htaccess                 Rewrites into public/ when the repo root is the docroot
```

## Setup

1. **Clone and install nothing else** – PHPMailer is already vendored, so Composer is not required.

2. **Create the environment file**

   ```bash
   cp .env.example .env
   ```

   Fill in `DB_*` with your MySQL credentials and `MAIL_*` with a Gmail
   [app password](https://myaccount.google.com/apppasswords) (never the account password).

3. **Import the schema**

   Import the database dump that ships with the project, then create the
   database user used in `.env`.

4. **Create the writable folders** (they are gitignored on purpose)

   ```
   storage/id_verifications/
   public/uploads/donations/
   ```

   On Linux/macOS also make sure the web server user can write to them:

   ```bash
   chmod -R 775 storage public/uploads
   ```

## Running locally

### Recommended: serve `public/` directly

```bash
php -S localhost:8000 -t public
```

Then open <http://localhost:8000/>.

### With Apache + the repository root as document root

The root `.htaccess` forwards every request into `public/`, so
`http://localhost/Neukai/` keeps working without extra configuration
(`mod_rewrite` must be enabled).

### Production

Point the virtual host `DocumentRoot` at the `public/` folder:

```apache
<VirtualHost *:80>
    ServerName neukai.example
    DocumentRoot /var/www/neukai/public

    <Directory /var/www/neukai/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Defence in depth – these folders are already denied by their .htaccess
    <Directory /var/www/neukai/app>
        Require all denied
    </Directory>
    <Directory /var/www/neukai/vendor>
        Require all denied
    </Directory>
    <Directory /var/www/neukai/storage>
        Require all denied
    </Directory>
</VirtualHost>
```

Set `APP_ENV=production` so PHP errors are logged instead of printed, and
serve the site over HTTPS.

## Security notes

- All database access uses prepared statements; output is escaped through `e()`.
- Every state-changing form carries a CSRF token (`csrf_field()` / `csrf_verify()`).
- Destructive actions (approve / decline / delete) are POST-only.
- Sessions are started through `secure_session_start()` (HttpOnly, SameSite, strict mode)
  and the session ID is regenerated on login.
- Uploaded files are validated by real MIME type and stored under a random filename.
- ID images for password resets live in `storage/` and are streamed through
  `public/admin/id_image.php` after an admin check.
- `public/uploads/.htaccess` disables PHP execution for uploaded content.

## Operational checklist

- [ ] Rotate the Gmail app password that was previously committed to git history.
- [ ] Change every account that ever used the old default passwords.
- [ ] Keep `.env`, database dumps and `NeuKai Assets/` out of version control.
- [ ] Never commit `storage/` or `public/uploads/` contents.

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request.
