# Modernize 3D Print Management App

This plan addresses the full-stack development tasks requested for the 3D Print Management App, specifically focusing on Filament and Product management modules alongside comprehensive internal i18n support.

## Proposed Changes

### Task 1: Filament Form Updates

#### [MODIFY] `package.json`
- Install `@simonwep/pickr` via `npm install @simonwep/pickr`.

#### [NEW] `resources/js/composables/usePickr.js`
- Create a composable that initializes `Pickr` on an element reference with the specified theme (`classic`) and interaction config.
- Listen for `save` events to update the reactive `colorHex`.
- Handle onMounted/onUnmounted lifecycle hooks.

#### [MODIFY] `resources/js/Pages/Filaments/Create.vue` & `resources/js/Pages/Filaments/Edit.vue`
- Import and use the new `usePickr` composable to replace the default `<input type="color">`.
- Render the new swatch trigger `div` and text output `span`.
- Add the `empty_spool_weight_grams` field with internationalized labels and an (optional) tag.

#### [NEW] Migration `database/migrations/xxxx_xx_xx_xxxxxx_add_empty_spool_weight_grams_to_filaments_table.php`
- Add the numeric column `$table->decimal('empty_spool_weight_grams', 8, 2)->nullable()->after('price_per_kg');` to the `filaments` table.

#### [MODIFY] `app/Models/Filament.php`
- Add `empty_spool_weight_grams` to `$fillable`.

#### [MODIFY] `app/Http/Controllers/FilamentController.php` (if in-controller validation) or Requests
- Validate `empty_spool_weight_grams` -> `nullable|numeric|min:0|max:9999` in the respective `store` and `update` methods.

---

### Task 2: Product Form Unused Variables Removal

#### [MODIFY] `resources/js/Pages/Products/Create.vue` & `resources/js/Pages/Products/Edit.vue`
- Delete markup related to Material, Default Filament Template / Recommended Filament, Color Name, and Color Hex.

#### [MODIFY] `app/Http/Controllers/ProductController.php`
- Remove `material`, `filament_id`, `color_name`, and `color_hex` from the `validate()` rules in the `store()` and `update()` methods.
- Make them no longer written to the `Product` models during storage.

---

### Task 3: Products List Columns & Toggle Toggle

#### [NEW] `resources/js/composables/useColumnVisibility.js`
- A composable accepting a table ID and storing/retrieving visibility values from `localStorage`. Returns `columnVisibility` and `setColumnVisibility`.

#### [MODIFY] `resources/js/Pages/Products/Index.vue`
- Import shadcn-vue `DropdownMenu` and icons for column toggling.
- Implement the column visibility logic in TanStack Table configurations along with `useColumnVisibility` using state handlers.
- Remove `material` and `color` columns from `columns` array.
- Add `created_at` and `updated_at` formatted date columns.

---

### Task 4: I18n Translations Audit

#### [MODIFY] `resources/js/locales/en.json` & `resources/js/locales/sr.json`
- Add all required and missing keys for:
  - shared/common UI components (actions, table generic texts)
  - `investments` namespace (Titles, forms, filters, confirmation dialogues)
  - `users` namespace (Roles, actions, tables, validation messages)
  - `settings` namespace
  - `table` namespace (column toggle)
  - `filaments` (empty spool weights, etc.)

#### [MODIFY] Vue Pages (Investments, Users, Settings)
- Audit Vue files under `resources/js/Pages/Investments/`, `Users/`, and `Settings/` replacing raw strings with their corresponding i18n `$t` counterparts to ensure complete Serbian translation coverage.

## Verification Plan

### Automated Steps
- Run `npm install` and `npm run build` to ensure type-safety and syntax validity.
- Run `php artisan migrate` to execute new DB changes.

### Manual Verification
- Access Filament form directly using an active server session.
- Add/Edit a Filament using the new `@simonwep/pickr` UI layout. Validate DB write updates.
- Create Product forms: Inspect lack of removed forms properties and ensure it still creates valid base items.
- Products List: Refresh to see column changes, use the Dropdown to change visibility, and then reload page to confirm persistence.
- Test i18n: Enable `SR` language setting for users in the respective session. Ensure all listed components and notifications utilize appropriate Serbian texts.
