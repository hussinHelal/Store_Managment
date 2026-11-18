# Audit loop findings and fixes

## 2026-11-18 follow-up

- **Product stock edits used the global total as a branch delta.** A decrease could fail when the current branch did not contain the entire difference, or could target the wrong branch. Product updates now set the compatibility aggregate through `StockService`, consuming stock from the current branch first and then other branches with one append-only movement per branch. The `StockServiceTest::test_aggregate_stock_reduction_is_distributed_across_branches_with_movements` regression verifies aggregate and movement reconciliation.
- **Cashier/damaged lookup exposed store-wide quantity to a branch user.** Lookups now report branch quantity. API regression confirms stock held only at another branch is returned as zero for the current user's branch.
- **Catalog import could overwrite stock without a ledger.** Product quantities that differ from the branch ledger are now rejected; zero-stock newly imported products receive zero branch rows. Full branch-ledger backup/restore remains a separate feature.

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

## Follow-up repair pass (2026-11-18)

- Localized the backup/import page and its status feedback in Arabic.
- Reworked maintenance create/edit/detail/status screens in Arabic. Added a multipart image field, existing-image preview, replace/remove controls, and status updates among waiting/completed/refused. The status form remains available after a status change, and completion date follows the selected status.
- Changed the barcode scanner to request the environment-facing camera directly, then fall back to a generic camera constraint. It no longer requires `enumerateDevices`, which caused the reported unsupported-method error.
- Added explicit `page.invoices.manage` middleware to the web cashier routes so invoice view-only users cannot use cashier actions.
- Fixed wallet audit event-group filtering: `transaction.` is accepted and filters all transaction events; the Arabic dropdown exposes that group.
- Added a web checkout response test. The current success response has named amount/invoice fields and no `work` property; the UI only renders those named fields. No server-side `work: null` field is present in this path.
- Reconciled the eight prior test failures: updated stale image URL, staff-admin, audit label/filter, wallet total label/default, and ledger-adjustment expectations; added the expected camera controls and prohibited direct wallet-balance writes through metadata updates. The wallet balance continues to change through a reasoned ledger adjustment.
- Focused tests: 31 passed, 222 assertions. Full suite: 135 passed, 891 assertions (2026-11-18).
- `git diff --check`: passed. Pint check and `node --check resources/js/app.js` passed.
- Frontend build: NOT VERIFIED. `npm` was blocked by PowerShell execution policy. `npm.cmd run build` ran but Vite failed with access denied while reading an ancestor directory/config. Android build and actual camera-device behavior remain NOT VERIFIED.
- No live database, production deployment, or device was touched.

## Remaining failing test contracts

None in the current full PHPUnit run. The 135 passing tests include the previously failing test areas, with expectations aligned to the current route, UI, and ledger behavior.
