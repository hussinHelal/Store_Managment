# Application Parity and Deployment Audit

Date: 2026-11-17. Scope: source and route inspection plus baseline PHPUnit run; no live user flows, production database, hosted deployment, or Android build was exercised.

**Status update:** this is the original audit snapshot. For the 2026-11-18 repairs, latest test totals, and current 17-page matrix, see [FINAL_REPORT.md](FINAL_REPORT.md) and [BUGFIX_REPORT.md](BUGFIX_REPORT.md); those reports supersede the older test and UI-fix notes below.

## Current conclusion

The product is a working multi-client Laravel application, but it is not at parity across web, API, and Android. The web app has materially more workflows than Android and the API. The 17 pages listed in the mission prompt remain mostly absent. Do not treat deployment as production-ready yet.

## Current web/API/Android coverage

| Area | Web | API | Android | Gap / status |
|---|---|---|---|---|
| Products, images | CRUD, image add/remove, print labels | CRUD, separate image upload; no image removal operation | CRUD and image selection/upload | Mostly present; image removal missing in API/mobile. Product description validation mismatch remains. |
| Categories | CRUD | CRUD | CRUD | Present; test suite covers API permissions and writes. |
| Customers | CRUD and detail/ledger display | CRUD and detail | CRUD | Core CRUD present; customer ledger UI/report parity remains partial. |
| Invoices / cashier | CRUD, payment, whole-invoice refund, PDF/print, checkout | CRUD/update, payment, whole refund, PDF, checkout | Invoice list/create/edit and cashier; no verified Android refund flow | Android refund and detailed invoice filters are missing or unverified. Refund is whole invoice only. |
| Sales | List/detail/edit operations | Read-only aggregate report | Read-only sold-product list | Android/API do not match web CRUD semantics; confirm whether legacy sales mutations are intended before adding them. |
| Supplier accounts | CRUD, purchases, payments, history and balance | Supplier CRUD/read only | Supplier CRUD | Purchase/payment API and Android workflows are absent. Supplier notes now round-trip in API/Android for manage users; view-only API responses omit them. Delete is blocked when financial history exists. |
| Installments / personal debts | Create/edit/pay/status/delete | CRUD/pay/status | Create/edit/pay/status/delete paths exist | Mostly present; full parity and permissions need a per-screen test audit. |
| Maintenance | CRUD/status, notes, image | CRUD/status and notes for manage users; no image upload | CRUD/status and notes; no image | Notes parity fixed; image upload remains web-only. |
| Damaged stock | Create/edit/void with image and reason | Create/edit/void, no image field/upload | Create/edit/void, no image field | Confirmed form-field mismatch; financial/stock actions are service-backed but direct stock paths remain elsewhere. |
| Wallets / treasury | Create/edit/toggle, ledger, expenses, reports, audit | Create/adjust/toggle, cashier transactions/settle/reverse, overview | Create/adjust/toggle plus cashier transaction flows | Expenses, full transaction history/report/audit and wallet edit/delete are absent from API/Android. Some omissions may be deliberate immutable-ledger rules. |
| Staff / roles | Staff CRUD, role and permission management | No staff or role CRUD | No staff or role management | API and Android admin parity absent. |
| Notifications | Read, mark-read, admin CRUD | User read/mark-read | User read/mark-read | Admin CRUD absent from API/Android. |
| Backup / export | Full backup export/import | Full backup export/import | Full backup import/export | Full backup parity exists; new per-module CSV export is absent. Import needs production-safe operational verification. |
| Settings | Web settings | No endpoint | No Android screen | Missing outside web. |
| 17 mission pages | Existing partials only | Most endpoints absent | Most screens absent | Branch stock, movements, stocktake, tracked devices, used phones, installations, shifts, discount approval, accounting ledger/close, and CSV export are not implemented. Purchasing, supplier balances, refund lookup, scanner hardware, treasury, and reports are partial. |

### Form field mismatches confirmed

- **Product:** web `SaveProductRequest` requires `description`; API `CreateProductRequest` allows null and Android DTO defaults it to null. This validation mismatch remains. Product remove-image is a web-only control.
- **Supplier:** Blade, API, and Android now include `notes` for manage users. API Resources expose it only with `page.suppliers.manage`; view-only responses keep internal notes private.
- **Maintenance:** Notes are now accepted, returned only to manage users, and editable in Android. Blade/API still differ in nullability for phone/address/description. Web image upload is not available in API or Android.
- **Damaged items:** Blade form supports an image; API request and Android DTO/form do not.
- **Wallets:** web form includes holder name, warn threshold, limits, active state and notes. Android DTO covers some wallet basics but not holder name, notes, or limits editing. API has create and balance toggle/adjust actions, not a complete wallet update contract.

The full field-level implementation matrix is not yet complete for every view and dialog; this report only calls out mismatches verified directly in the current forms, requests, resources, and DTOs.

## API quality observations

- `routes/api.php` currently exposes 73 versioned routes, with explicit Sanctum, active-user, permission and ability checks on most business endpoints; pagination and API Resources are used.
- API coverage omits important web actions (supplier purchase/payment, wallet expenses/reports/audit, staff/roles/settings/admin notification CRUD). Several write payloads have rules separate from web FormRequests, producing confirmed parity gaps above.
- Existing API supports whole invoice refund; no partial-refund model exists.
- `InvoiceCreationService` and `InvoiceInstallmentSync` contain float conversion/rounding paths; supplier API balance updates also use float arithmetic. These need targeted financial regression tests before changing.
- `products.stock` still has direct writers outside a single stock service (legacy invoice paths and current invoice/damage services). Do not add branch inventory until all writers are accounted for and tests exist.

## Deployment readiness findings

- No repository-owned Dockerfile, Compose file, Coolify Nixpacks configuration, or deployment workflow is present. No production deployment was exercised.
- `.env.example` is developer-oriented (local `APP_URL`, local Sanctum stateful domains, `SESSION_SECURE_COOKIE=false`, local MySQL). It must not be copied unchanged to production.
- Android development API base URL defaults to a LAN IP; staging/production defaults are deliberately `.invalid` placeholders and must be set through Gradle properties for real builds.
- Image storage now uses `MEDIA_DISK` (default `public`) for generated media. Set it to a persistent disk in Coolify. Laravel Cloud object storage still needs the AWS Flysystem adapter, which is not present in `composer.lock`; it cannot be enabled merely by setting `MEDIA_DISK=s3`.
- Legacy `public/uploads` images bypass Laravel disks and need migration to managed object storage or a persistent Coolify mount before ephemeral deployment.
- Production must supply `APP_KEY`, HTTPS `APP_URL`, secure cookies, restrictive trusted stateful domains, database/cache/queue settings, mail transport, and persistent/object storage. Run migrations via reviewed deploy command only after a production backup and migration review.
- Pending migration: `2026_11_11_000001_add_catalog_and_dashboard_indexes` (observed by read-only `migrate:status`).
- API docs/OpenAPI exist but require updates for missing endpoints before exposing them.

## Safe audit fix in this pass

- Added `MEDIA_DISK` (defaults to existing `public`) in `config/filesystems.php` and changed `PictureStorageService` reads, writes, URL generation, and deletes to use it. This preserves local behavior and lets a persistent Coolify volume be selected without editing PHP.
- Fixed supplier updates clearing internal notes when a client omits that unreadable field; regression test reproduces the original data loss.
- Added authorized maintenance-note API round-trip and Android edit support while preserving view-only privacy.
- Fixed supplier deletion erasing purchases/payments through cascading foreign keys. The shared delete service locks the supplier and refuses removal when any financial history exists, in both web and API.
- Added unit coverage for configured-disk URLs and deletion.

## Verification log

- Pre-change `php artisan test`: 117 passed, 8 failed, 797 assertions (176.02s). Latest full suite: 122 passed, 8 failed (815 assertions, 11.96s); failures are the same eight test cases as baseline. Focused regression suites (media, supplier, maintenance): 13 passed, 78 assertions. The existing failures cover image rendering, role/cashier UI expectations, wallet audit/pagination, and wallet form/balance-edit expectations; they still need investigation and are not counted as passing behavior.
- Android `:app:testDevelopmentDebugUnitTest`: NOT VERIFIED; Gradle distribution download was blocked by restricted network after redirecting Gradle cache into the workspace.
- `php artisan route:list --path=api/v1 --except-vendor`: 73 API routes listed successfully.
- `vendor/bin/pint --test` on changed PHP files: passed. `git diff --check`: run before handoff.

## Deployment paths

- **Laravel Cloud:** use the Laravel native app builder; set build command to `composer install --no-dev --prefer-dist --optimize-autoloader && npm ci && npm run build`, and a reviewed deploy command `php artisan migrate --force`. Attach managed database/cache/queue/object storage as required. Avoid assuming the local disk persists.
- **Coolify:** choose a supported Laravel build path, expose the actual HTTP listener, configure a readiness check, persist or externalize uploads, and run queue/scheduler processes as separate managed processes or documented workers. Do not use `php artisan serve` as a long-term production server. Verify PHP extensions (GD, PDO MySQL, fileinfo, mbstring, exif, zip) and build assets in the image.

## External deployment references checked

- [Laravel Cloud deployment guide](https://laravel.com/cloud/deploy-a-laravel-app) (native detection, runtime, build/deploy command and managed resources).
- [Coolify Laravel example](https://coolify.io/docs/applications/framework-examples/php/laravel) (Nixpacks/Dockerfile choices, exposed port, worker considerations).
- [Coolify persistent storage](https://coolify.io/docs/applications/configuration/persistent-storage) (container data is replaceable unless mounted).
- [Coolify health checks](https://coolify.io/docs/applications/configuration/health-checks) (readiness configuration and container checks).



## Implementation update ? 2026-11-18

The opening table above is a historical baseline, not the current state. Pages 1?5 and 15 now have substantial backend/web/API work and partially implemented Android screens; see `docs/FINAL_REPORT.md` for precise gaps. Page 6 now has a tracked-device migration, permission grant, transactional CRUD service, Arabic web page, paginated permission-gated/idempotent API, OpenAPI entry, and Arabic Android source flow. Product deletion checks tracked-device history in web and API handlers. Targeted device tests pass. The full Laravel suite passes (177 tests / 1,172 assertions). Android compilation remains NOT VERIFIED because Gradle distribution download failed under network restrictions. Pages 7?14, 16, and 17, Android stocktake/transfers, and the whole-project hidden-bug audit remain outstanding.


### Cashier, camera and navigation follow-up - 2026-11-18

Cashier optional success rows are filtered before DOM insertion; barcode camera capability is checked before ZXing and the Arabic cashier modal accepts manual barcode input. The scanner still needs physical-device verification over HTTPS. Maintenance list rows now include the device image thumbnail, and the sidebar groups implemented pages under Arabic categories. Full Laravel verification is now 177 passed / 1,172 assertions; Android compilation and production deployment remain unverified. The mission page matrix remains authoritative for incomplete modules.


## Product cost schema incident - 2026-11-18

The configured `phoneStore` MySQL database reports the branch-stock, product-cost/purchase-receipt, invoice-item snapshot, tracked-device, and used-phone migrations as pending. Laravel logs confirm the product edit 500 was `Unknown column 'cost_price'`; an earlier product creation also failed because `branches` does not exist. Product cost labels and currency output now use Arabic/EGP. Web cost inputs are disabled with migration guidance when the column is absent; API cost writes return a validation response instead of a database 500. API product listing and supplier receipt-product lookup omit the missing column safely. No migration or data change was run against the configured database, per `docs/DECISIONS.md`. A backup and reviewed migration execution on a throwaway/staging database remain required before those capabilities can be enabled locally or in production.


## Installation appointment module - 2026-11-18

The appointment module now has an Arabic responsive CRUD page, searchable/date/status filters, full edit/create forms, technician assignment, duration, completion timestamps, and permission-gated idempotent API mutations. Technician time conflicts are checked transactionally, serializing concurrent bookings on the technician row. Tests cover CRUD, API replay, and overlapping schedules. Android appointment parity remains outstanding. Its two new migrations are pending on the configured `phoneStore` database; this environment did not alter that database.
