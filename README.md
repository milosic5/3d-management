# 3D Management

Internal app for running a 3D printing business: orders and their history, products (with images and up to two model files), filaments and stock, printers (working hours, nozzle changes, monthly maintenance), packaging stock, investments/costs by category, a dashboard and analytics.

## Stack

- **Backend:** Laravel 13, PHP 8.3+, Inertia 2, Sanctum, Ziggy, maatwebsite/excel (Excel export), barryvdh/laravel-dompdf (order PDF), spatie/laravel-backup
- **Frontend:** Vue 3 (`<script setup>`), Tailwind CSS v3, shadcn-vue components (`resources/js/Components/ui`, built on reka-ui), TanStack Table, Chart.js, vue-i18n (English and Serbian)
- **Database:** MySQL in production; the test suite runs on in-memory SQLite

## Setup

```bash
composer install
cp .env.example .env          # then set APP_NAME, APP_URL and the DB_* connection (MySQL)
php artisan key:generate
php artisan migrate
php artisan db:seed           # optional demo data, see below
php artisan storage:link      # product images and model files live on the public disk
npm install
npm run build
```

`composer setup` runs most of these steps in one go.

The demo seeder creates `admin@printshop.local` and `operator1@printshop.local`, both with the password `password`. Change or remove them before using real data.

## Everyday commands

| Command | What it does |
| --- | --- |
| `composer dev` | Laravel server, queue listener, log tail (Pail) and Vite dev server together |
| `npm run build` | Production frontend build into `public/build` |
| `php artisan migrate` | Apply new migrations |
| `php artisan test` | Pest test suite (SQLite in memory, no setup needed) |
| `./vendor/bin/pint` | Format PHP code |

## Roles and permissions

Public registration is disabled; an admin creates users under **Users**.

| | Admin | Operator |
| --- | --- | --- |
| Orders: view, create, edit, change status | ✓ | ✓ |
| Orders: delete (moves to trash) | ✓ | ✓ |
| Order trash: view, restore, delete permanently | ✓ | — |
| Products, filaments, printers, maintenance, packaging, calibrations: view, create, edit | ✓ | ✓ |
| …the same: delete, product trash | ✓ | — |
| Dashboard | ✓ | ✓ |
| Analytics, investments, users, settings | ✓ | — |

Rules live in `app/Policies` (admins pass every check through `Gate::before` in `AppServiceProvider`) and the `role:admin` route middleware. The frontend hides actions using the `auth.can` permissions shared by `HandleInertiaRequests`.

Deleted orders are soft-deleted and are excluded from the dashboard, analytics and Excel export.

## Settings

Admins can set the app name (sidebar, browser title, order PDF) and the default language under **Settings**. A user's language toggle is remembered for their session.

## Backups and scheduler

spatie/laravel-backup is scheduled in `routes/console.php`:

- `backup:clean` daily at 01:30
- `backup:run` daily at 02:00 (database and files, stored on the `local` disk under `storage/app`)

The scheduler only runs if cron calls Laravel every minute. On the server add:

```cron
* * * * * cd /path/to/3d-management && php artisan schedule:run >> /dev/null 2>&1
```

Backups on the same disk do not survive a lost server; consider adding an off-site disk in `config/backup.php`.
