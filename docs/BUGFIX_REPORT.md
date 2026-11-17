# Audit loop findings and fixes

Date: 2026-11-17

## Confirmed defects fixed

1. **Supplier edits could erase private notes.** The API omitted notes for users without supplier-manage permission, but the update path treated an omitted field as an instruction to clear it. Updates now preserve the stored value when `notes` is absent; managers can read and update the field. Regression coverage verifies the behavior.
2. **Supplier deletion could erase financial history.** The supplier foreign keys cascade into purchases and supplier payments. Web and API deletes now share a locked, transactional guard that refuses deletion when either history exists. API and web regression tests cover the refusal.
3. **Maintenance notes were missing from API/mobile.** The API accepts and returns notes for users with maintenance-manage permission, and Android create/edit payloads now include them. Read-only users do not receive the private note. API tests cover the round trip and read privacy.
4. **Generated-image storage was fixed to the local public disk.** The media service now uses configurable `MEDIA_DISK`, defaulting to the existing `public` disk. Unit coverage verifies URL generation and deletion using the selected disk.

## Verification

- Full PHPUnit suite: 122 passed, 8 failed (815 assertions). All eight failures are present in the documented pre-change baseline; this pass did not add a new failing test. The failures still require investigation and are not claimed as passing behavior.
- Focused media, supplier, and maintenance regression suites: 13 passed (78 assertions).
- Pint check on changed PHP files: passed.
- Android Gradle build: not verified because the wrapper distribution download was blocked by the restricted network.
- OpenAPI YAML parser check: not available in the installed toolchain; schema changes were reviewed but not machine-validated.
- No live database migration, production deployment, or user database write was performed.

## Important open defects and scope gaps

- Android/API are not at parity with the web app. The separate parity report lists confirmed missing pages, CRUD actions, and form fields. In particular, purchasing/supplier payments, wallet expenses/reports/audit, staff/roles, settings, damaged-item images, maintenance images, and several mission pages are absent or partial.
- Product description validation differs between web and API. Product image removal is unavailable in API/Android.
- Wallet edit forms and API/mobile do not cover all web wallet fields and actions.
- Stock has multiple direct writers; do not add inventory flows until all writers are consolidated or individually accounted for with regression coverage.
- Financial paths still contain floating-point arithmetic and need targeted decimal/invariant review before financial behavior is refactored.
- Laravel Cloud object storage is not ready: the AWS Flysystem adapter is absent, and legacy `public/uploads` files bypass the configurable media disk. Coolify needs a persistent mount or an object-storage adapter.
- Android production API URLs and deployment secrets/settings remain operator configuration work. No deployable production setup has been exercised.

## Existing failing test contracts

The full suite failures are in `ResourceShowTest`, `RoleAccessTest` (two cases), `WalletAuditPageTest`, `WalletCashierPaginationTest`, and `WalletCreationTest` (three cases). They cover product image URL/rendering, role/cashier UI behavior, wallet audit/pagination, and wallet form/balance-edit behavior. These fail both before and after this audit pass. Their expected behavior needs product/security review before changing application behavior to satisfy them.
