# Phase 0 baseline

Date: 2026-11-17 (Africa/Cairo)

## Binding constraints

1. Preserve existing route names, URLs, and API response contracts; extend additively.
2. Treat the server as authoritative for permissions, prices, totals, discounts, stock, and money.
3. Stock and money mutations must use shared transactional services, locked rows, and append-only compensating records.
4. Money uses decimal storage and integer-cent/string arithmetic; quantities are integers; day boundaries use Africa/Cairo.
5. Validate web and API writes on the server, cap pagination, enforce module permissions, and make consequential API writes idempotent.
6. Migrations must be additive, reversible, and safe with existing data; never use migrate:fresh on a non-throwaway database.
7. Product cost remains manually controlled; receipt costs are snapshots and sale costs must be snapshotted for profit reporting.
8. Keep all new user-facing UI Arabic and RTL; Android uses the existing Compose/API architecture.
9. A feature is reported DONE only with DB, backend, API, web, Android, test, permission, and navigation evidence.
10. Document repository contradictions and design decisions; do not silently repair financial or stock history.
11. Do not overwrite user-owned work or claim unverified device/emulator behavior.
12. Whole-project bug fixes need reproduction, a failing test, a smallest safe fix, and a passing test.

## Existing docs reviewed

Reviewed `ARCHITECTURE.md`, `API_DOCUMENTATION.md`, `SECURITY_AUDIT.md`, `PROJECT_AUDIT.md`, `openapi.yaml`, and `CHANGELOG.md`. These describe an existing Laravel 12 + Sanctum API and a native Android app; this differs from the mission's quick-read assertion that API routes are largely absent. `PROJECT_AUDIT.md` itself records that a prior audit was source-only and that prior worktree state must be preserved.

## Baseline commands and environment

- `php -v`: PHP 8.5.11 (CLI).
- `php artisan --version`: command emitted no version in the grouped probe; Laravel command execution is evidenced by migration status and the PHPUnit suite below.
- `php artisan migrate:status`: succeeded against the configured `.env` MySQL database `phoneStore`. Existing migrations are applied through batch 4; `2026_11_11_000001_add_catalog_and_dashboard_indexes` is Pending. This is read-only.
- `php artisan test`: completed with 117 passed, 8 failed (797 assertions; 176.02s). Baseline failures: `ResourceShowTest::test_product_listing_renders_images_from_public_storage`; `RoleAccessTest::test_staff_view_permission_allows_listing_but_not_mutation`; `RoleAccessTest::test_nested_cashier_routes_use_invoice_permissions_and_keep_create_gate`; `WalletAuditPageTest::test_wallet_audit_renders_transaction_details_in_arabic_and_filters_by_event_group`; `WalletCashierPaginationTest::test_wallet_cards_paginate_without_changing_global_balance_total`; and three `WalletCreationTest` cases (`test_wallet_can_be_created_with_blank_form_fields`, `test_wallet_balance_can_be_set_from_the_edit_form_with_a_reason`, `test_wallet_balance_edit_requires_a_reason`). These are pre-change baseline failures; do not attribute them to future work.
- Android `:app:testDevelopmentDebugUnitTest`: the default wrapper cache attempt failed because it could not create `C:\.gradle\wrapper\dists\...gradle-9.6.1-bin.zip.lck`. Retrying with `GRADLE_USER_HOME=android/.gradle-user` passed that filesystem issue, but downloading Gradle 9.6.1 failed with `java.net.SocketException: Permission denied` (network restricted). Android unit tests and build are NOT VERIFIED. Java 17.0.2 was detected by the wrapper.
- `composer install`, `.env` replacement, key generation, and `migrate --seed` were intentionally not run: dependencies are already present and the live `.env` points to MySQL `phoneStore`. Replacing `.env` or seeding/migrating that database would risk existing data. The mission's requested clean bootstrap needs a dedicated throwaway DB configuration.
- No Android SDK/emulator or hardware checks were established; these remain NOT VERIFIED.

## Worktree state observed

Branch: `main` tracking `origin/main`. At inspection, Git showed a deleted Android Kotlin compiler `.salive` session artifact and an untracked generated `storage/framework/lsp-7194248d19661da5.php`. These were left untouched. No feature code was changed in Phase 0.

## Authorization map

- `app/Http/Middleware/EnsurePagePermission.php` resolves route-name prefixes to `page.<module>.view` for GET and `page.<module>.manage` for writes (with documented exceptions for explicit gates).
- Permission creation/synchronization is implemented in `app/Console/Commands/SyncAccounts.php`; role assignment and migration history should be followed from the role controllers and `database/migrations/*grant_suppliers*`.
- API `/me` returns current roles and page permissions as documented in `API_DOCUMENTATION.md`; Android authenticates through Retrofit and receives the corresponding `Me` DTO (inspect `StoreApi.kt`, `ApiModels.kt`, and session view model before adding permissions).
- New module procedure: define the permission in the page/module catalog used by `SyncAccounts`, make the route name begin with the module key, place `page.access` on the route group, add request/controller authorization, ensure intended admin role grants are synchronized, expose permissions from `/me`, and cover allowed/denied requests in tests. Other roles should not receive new rights by default.

## Stock write map (source scan)

- `app/Services/InvoiceCreationService.php`: invoice create/update/refund adjusts `products.stock` after locking.
- `app/Services/DamagedStockService.php`: records, edits, and voids damaged items while changing stock transactionally.
- `app/Http/Controllers/InvoiceController.php`: legacy create/refund/edit flows directly decrement/increment stock.
- `app/Http/Controllers/InvoiceOperationsController.php`: refund paths directly increment stock.
- Product create/update requests accept stock values (`SaveProductRequest`, API create/update product requests); these are also stock writers to account for in the consolidation.
- `app/Console/Commands/BackupDatabase.php` and `app/Http/Controllers/BackupController.php` serialize stock; backup import is a potential writer requiring inspection.
- Cashier routes delegate invoice creation to `InvoiceCreationService`; maintenance did not appear as a stock writer in this scan. The initial scan is a search-based inventory, not a proof of exhaustiveness.

## Money and totals map (initial source scan)

- `InvoiceCreationService` calculates invoice totals using integer cents but currently converts database/request values through `(float)` and `round()`.
- `InvoiceInstallmentSync` accepts `float` totals and uses floating-point rounding.
- Wallet balance writes are centralized in `app/Services/Wallets/WalletLedgerService.php` using integer cents and locks; its public signatures still accept `string|float` values.
- Supplier, installment, and legacy invoice flows contain additional balance/total calculations; a complete audit of each write path remains outstanding before feature work.

## Existing module blueprint

- Damaged stock: migration `2026_10_06_000004_create_damaged_items_table.php`, `DamagedItem`, `DamagedStockService`, web/API controllers and FormRequests, routes loaded by `routes/damaged.php`, Blade views under damaged views, and unit/feature tests. Records are voided rather than deleted; service locks product rows and uses idempotency keys.
- Wallets: wallet migrations in the `2026_10_06*` and `2026_10_07*` series, `Wallet` / `WalletTransaction` models, `WalletLedgerService`, wallet controllers, requests, routes in `routes/wallets.php`, Blade wallet/report views, wallet API endpoints, and reconciliation command. Money arithmetic is in `app/Support/Money.php` and balance writes are service-owned.
- Follow Laravel route grouping, explicit FormRequests, service-owned transactions, Arabic Blade conventions, audit records, throttles, API Resources, and focused PHPUnit tests. Confirm exact individual paths when implementing a specific module.

## Android architecture map

- Compose navigation and More screen are in `android/app/src/main/java/com/storemanagement/mobile/ui/StoreManagementApp.kt` and associated screen files.
- Existing ViewModels are in `ui/StoreViewModels.kt`; repository in `data/StoreRepository.kt`; Retrofit/API DTOs in `core/network/StoreApi.kt` and `ApiModels.kt`; outcomes through `ApiResult` and `ApiCallExecutor`.
- Existing write idempotency and connection UI patterns are in repository/API methods and `ui/ConnectionUi.kt`.
- Arabic currency/date formatting is in `ui/ArabicDisplayFormatters.kt`; strings are under `android/app/src/main/res/values`.
- The task requested new screens under `ui/<module>/`; current screen/navigation files should be extended through small registrations rather than growing the existing large files.

## Risk register

| Risk | Prevention / required evidence |
|---|---|
| Concurrent sales, damage, refunds, or transfers produce negative or unlogged stock | Central StockService; transaction + row lock; append-only movement; parallel decrement tests and stock reconciliation command |
| Existing `products.stock` writers bypass a new ledger | Inventory every direct write including legacy invoice CRUD, product edit, backup import, and old API paths; migrate all writers before enabling branch stock |
| Duplicate/retried checkout or payment mutates twice | DB-backed idempotency scoped to user/operation/key with payload hash; concurrent duplicate tests |
| Floating-point totals disagree across invoice/payment/report paths | Integer-cent/string arithmetic, shared calculation service, boundary and rounding tests |
| Supplier/customer/wallet balances diverge from their logs | Shared locked services and read-only reconciliation commands; no automatic repair of history |
| Permission prefix mismatch exposes writes or hides reads | Route-prefix + middleware + explicit request/gate checks; allowed/denied feature tests and `/me` permission verification |
| Migration applies to existing MySQL data unexpectedly | Separate throwaway baseline DB; backup before any real DB change; reversible additive migrations and populated-legacy migration tests |
| Android retries, stale permissions, or offline writes cause duplicate/unauthorized changes | Reuse one idempotency key per attempt; server authorization; connection requirement for money/stock writes; Android unit/build verification |

## Decisions / deviations

- Mission bootstrap instructions are not safe against the current working database: `.env.example` and `.env` point at different MySQL database names (`storemanagement` and `phoneStore`), while `.env` is already present. Do not replace it or run migrations/seeds against it. Establish a throwaway database explicitly before continuing.
- The repository already has a versioned API, OpenAPI contract, and Android implementation. Continue the existing implementation instead of creating duplicate API or Android scaffolding.
- The requested branch discipline cannot be applied without first resolving the existing worktree state; preserve `main` and the two observed non-clean status entries until their ownership is understood.

## Phase 0 gate

NOT PASSED. Full baseline test completion, safe fresh-database bootstrap, Android unit-test/build verification, exhaustive money/stock writer mapping, and several detailed permission/module inspections remain outstanding. No feature code should be started until those gaps are addressed.


