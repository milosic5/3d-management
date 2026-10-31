# 3d-management: notes for coding agents

Laravel app for running a 3D printing business (orders, products, filaments, printers, packaging, investments, dashboard and analytics). See `README.md` for setup, roles and backups.

## Stack

- Laravel 13, PHP 8.3+, Inertia 2, Sanctum, Ziggy, maatwebsite/excel, barryvdh/laravel-dompdf, spatie/laravel-backup
- Vue 3 `<script setup>`, Tailwind v3 (`tailwind.config.js` + `@tailwind` directives, no v4), shadcn-vue components in `resources/js/Components/ui` (reka-ui), TanStack Table via `DataTable.vue`, Chart.js, vue-i18n
- There is no Livewire and no Passport.

## Commands

- `php artisan test`: Pest 4 suite with `RefreshDatabase` on SQLite `:memory:`
- `./vendor/bin/pint <files>`: run on every PHP file you change
- `npm run build`: production build (assets in `public/build` are committed)

## Database: MySQL in production, SQLite in tests

- Every migration must run on both. Raw SQL that only works on MySQL (`ALTER TABLE … MODIFY`, `DATE_FORMAT`) must be guarded by `DB::getDriverName()` or go through `App\Support\Sql` (e.g. `Sql::yearMonth($column)`).
- Migrations only go forward: never edit one that has already run in production, add a new one.
- Arithmetic differs: `SUM(x) / 60` is integer division on SQLite and decimal on MySQL.
- Raw `DB::table('orders')` queries ignore soft deletes; add `whereNull('orders.deleted_at')`.

## Access rule

Operators may delete **only orders**. Everything else (printers, maintenance, packaging, filaments, products, calibrations, investments, users) is deleted by admins only, and so are order restore and permanent delete.

- Enforce it in `app/Policies` (admins pass through `Gate::before`) and Form Request `authorize()`.
- The frontend never checks roles for actions; it reads `$page.props.auth.can.*` shared by `HandleInertiaRequests`.
- Every delete route has an operator test (403) and an admin test.

## Conventions

- Validation lives in Form Requests (`app/Http/Requests`); `authorize()` calls the policy.
- Order status and filament material are enums (`App\Enums\OrderStatus`, `App\Enums\FilamentMaterial`). Their colours are defined there and shared as `enums.*` props (use `useEnums()` in Vue). The material column is a plain string, so a new material is just a new enum case.
- Shared period/revenue/"Cepovi" logic for the dashboard and analytics is in `App\Services\FinanceReportService`.
- Every new UI text goes through i18n with a key in both `resources/js/locales/en.json` and `sr.json`.
- Follow the existing UI building blocks: `AppLayout`, `PageHeader`, `DataTable`, shadcn-vue components.
- `Model::preventSilentlyDiscardingAttributes()` is on outside production: add new columns to `$fillable`.
- Every bug fix gets a Pest test that fails before the fix.
- Do not change business logic without asking. Do not touch `.env` or run destructive database commands.
