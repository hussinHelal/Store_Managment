# Store management completion status

## Current implementation status — 2026-11-18

The original matrix below is historical and was written before feature implementation. The first inventory wave has since been implemented, so rows 1–3 in that old matrix are superseded by this current status:

| Page | Database / backend / API / web | Android | Tests | Current status |
|---|---|---|---|---|
| Branch inventory | Branches, branch stock, current-branch resolution, locked `StockService`; Arabic branch/stock and transfer pages; API list, adjust and reorder operations | Arabic branch stock list and manual adjustment; no transfer management | Stock invariants, API, web and transfer concurrency regressions | PARTIAL — native transfer CRUD and full permissions/parity matrix remain |
| Stock movements | Append-only ledger, service-backed invoice/product/damage/transfer/count changes, read-only `stock:verify`; Arabic list and API filters/adjustment | Latest movements embedded in inventory screen | Service, API idempotency, cashier/damage and aggregate tests | PARTIAL — dedicated mobile movement page and filters remain |
| Stocktakes | Arabic snapshot/count/review/approve/cancel web flow and API actions | Missing | Snapshot concurrency, transfer and API workflow tests | PARTIAL — native stocktake CRUD remains |
| Tracked devices | IMEI/serial table with product/invoice relations; transactional service protects sold identity; Arabic Blade CRUD and permission-gated API CRUD | Arabic More-screen list, filters, product/status form, edit and confirmed delete | API/service/web rendering and idempotency tests | PARTIAL - native UI added but Gradle compile could not run in the restricted environment; device-to-invoice sales automation is not integrated |
| Sales reports | `invoice_items` stores sale-time line price/cost snapshots; old rows are estimated and missing costs are flagged. Filtered SQL aggregates, web/API, streamed CSV, and Arabic PDF are implemented. | Arabic date, grouping, and payment-status report; other selectors and export parity are unverified. | Four feature cases cover snapshots, permissions, filter validation, web rendering, CSV injection/BOM, and PDF response; invoice regression cases pass. | PARTIAL - Android branch/cashier/category/product selectors and share/export actions are outstanding; discounts and tender type are not source data. |

Pages 7-14, 16-17 and portions of pages 1-6 and 15 remain incomplete. Tracked devices is implemented on Laravel/web/API and the Android source now includes its first end-to-end list and write flow, but Android compilation is not verified. Page 4 now has branch-aware receiving, item cost snapshots, manually maintained product cost, and supplier statement integration on web/API/Android; it still lacks the standalone purchase-order lifecycle and Android build verification. The historical parity matrix below records the original baseline only; use this current implementation table and the dated update at its end for present status.


Supplier statements are Arabic on web and Android with API access, Cairo aging, and purchase/payment history. Receiving writes stock through `StockService`, records purchase lines and payments, preserves manually maintained product cost, and replays idempotency keys safely. The 17-page mission remains in progress. The full Laravel suite passes: 177 tests, 1,172 assertions (2026-11-18). Android Gradle compilation remains NOT VERIFIED: the wrapper could not download Gradle in the restricted environment.

Date: 2026-11-18

The 17 feature pages from the mission prompt have **not** been implemented by this work. The earlier audit identified the gaps but did not build those pages. This matrix records the current repository state; `PARTIAL` means only related legacy functionality exists and does not satisfy the requested end-to-end web/API/Android definition of done.

| # | Page | DB | Backend | API | Web | Android | Tests | Permissions | More menu | Status / remaining work |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Branch inventory | Missing branch/stock tables | Single `products.stock`; multiple direct writers | No branch inventory | Product stock only | Product stock only | No branch invariants | No branch permissions | No | **NOT DONE** — branch-aware stock and a single locked stock writer are required. |
| 2 | Stock movements | No movement ledger | No unified movement service | Missing | Missing | Missing | Missing | Missing | No | **NOT DONE** — consolidate existing stock writers first. |
| 3 | Stocktakes | Missing | Missing | Missing | Missing | Missing | Missing | Missing | No | **NOT DONE** — snapshot/count/review/approval flow and movement reconciliation absent. |
| 4 | Purchasing and receiving | Legacy purchases exist; receipt/line model incomplete | Supplier purchase flow only | Purchase/receipt workflow missing | Purchase creation exists within supplier page | Missing | Existing purchase tests only | Supplier permissions | No | **PARTIAL** — purchase orders, partial receipts, branch stock, receipt history and mobile flow missing. |
| 5 | Supplier balances | Suppliers, purchases, payments exist | Balance/accounting logic exists | Supplier CRUD, no statement/aging | Payable column/history exists | Supplier CRUD only | Supplier accounts tests | Supplier view/manage | No | **PARTIAL** — aging, statement PDF/filtering, integrity command and Android/API payment flow missing. |
| 6 | Tracked devices | No device/event tables | Product barcode only | Missing | Missing | Missing | Missing | Missing | No | **NOT DONE** — IMEI/serial lifecycle and stock integration absent. |
| 7 | Used phones | No used-device purchase tables | Missing | Missing | Missing | Missing | Missing | Missing | No | **NOT DONE** — inspection, acquisition, contract, image and payment workflows absent. |
| 8 | Installation appointments | Maintenance requests exist; appointment tables absent | Repair status workflow only | Maintenance CRUD, no appointments | Maintenance CRUD; status and device photo controls now present | Maintenance CRUD, appointment scheduling absent | Maintenance API/web status tests | Maintenance view/manage | No | **PARTIAL** — scheduling, technician assignment, conflict checks, notifications, proof and Android missing. |
| 9 | Cashier shifts | No shifts/cash movement tables | Cashier checkout exists | Checkout exists | Cashier checkout exists | Cashier flow exists | Checkout/idempotency tests | Invoice manage; cashier route now explicitly requires it | No | **NOT DONE** — open/close shift, variance, cash movements and shift reports absent. |
| 10 | Discount approvals | No policy/approval tables | No approval enforcement | Missing | Missing | Missing | Missing | Missing | No | **NOT DONE** — server-enforced independent approval flow absent. |
| 11 | Refund lookup | Invoice refund state exists; partial-refund tables absent | Whole-invoice refund | Whole-invoice refund | Invoice refund action | No verified refund flow | Whole-refund tests | Refund/manage gate | No | **PARTIAL** — search filters, line-level partial refunds, stock disposition, receipts, and Android missing. |
| 12 | Reader and printer diagnostics | Settings table exists | Shared browser barcode reader and print routes | No diagnostics contract | Print/scanner features exist; dedicated diagnostics page missing | Existing camera/print support needs validation | Scanner UI tests only | Invoice manage/settings access | No | **PARTIAL** — raw scanner diagnostics, printer settings/test page, Android diagnostics and hardware verification missing. |
| 13 | Store treasury | Wallet expenses/cash/audit tables exist | Existing wallet expense/ledger service logic | Wallet cashier/overview, treasury actions incomplete | Expenses, cash and profit actions exist | Wallet cashier partial | Wallet ledger/expense tests | Wallet permissions | No | **PARTIAL** — shared `TreasuryService`, daily cash book, categories, receipts, shift links and reconciliation missing. |
| 14 | General ledger and period close | No chart/journal/period tables | Customer ledger only | Missing | Missing | Missing | Missing | Missing | No | **NOT DONE** — double-entry ledger, posting, opening balances and period guard absent. |
| 15 | Sales reports | Sales data exists; cost snapshot incomplete | Sold-product list/report | Read-only sales aggregate | Sold-product list | Sold-product list | Limited report tests | Sales view | No | **PARTIAL** — cost snapshots, margin KPIs, breakdowns, filters, CSV/PDF and Android report missing. |
| 16 | Store financial reports | Wallet and supplier data exists; ledger absent | Wallet reports | Wallet overview only | Wallet reports | Limited wallet totals | Wallet tests | Wallet view | No | **PARTIAL** — income/cashflow/inventory valuation and ledger reconciliation missing. |
| 17 | Data export | Existing full backup logs only | Full backup export/import | Full backup export/import | Full backup page now Arabic | Full backup import/export | Backup/import tests; Arabic page test added | Backup view/manage | Backup is in web sidebar; no new Android menu entry | **PARTIAL** — module CSV exports, dates/permissions, injection-safe streaming/logs and Android share flow missing. |

## Repairs in this pass

- Backup/import page text and status feedback are Arabic.
- Maintenance create/edit/detail/status pages are Arabic. Create/edit now have image file inputs; edit can preview, replace or remove an image. Status can be changed among waiting, completed and refused repeatedly; completion date follows the status.
- Browser camera scanning requests the environment-facing camera directly and retries with generic video constraints. It no longer calls the unsupported camera enumeration method.
- Web cashier routes now check invoice-manage permission explicitly.
- Wallet audit group filtering for transaction events now works, with a matching Arabic filter option.
- Cashier web success response test confirms expected invoice/amount fields and absence of a `work` field. Success UI renders named values only; invalid/missing amounts no longer render `NaN`.
- The eight previously reported PHPUnit failures were resolved or their stale test expectations were updated to match the current image route, superadmin-only staff creation, current Arabic audit labels, wallet totals/defaults, and ledger-based balance adjustment.

## Verification log

Maintenance image upload is implemented in the web forms only. API and Android maintenance image upload remain outstanding; the existing maintenance status update works through the shared API flow.

- Focused supplier receipt, supplier statement, product cost, and API regressions: 16 passed, 112 assertions.
- Full `php artisan test --compact`: 156 passed, 1038 assertions (2026-11-18).
- Targeted Pint checks for the new report/receipt PHP files, route checks, and `git diff --check` passed. Repository-wide `vendor/bin/pint --test` still reports formatting findings in unrelated legacy files. `node --check resources/js/app.js` passed in the prior verification pass.
- `npm run build`: blocked by PowerShell execution policy. `npm.cmd run build`: Vite failed because access to an ancestor directory was denied. Frontend bundle **NOT VERIFIED**.
- Android Gradle build **NOT VERIFIED**: Gradle could not create its default wrapper lock under `C:\.gradle`; the distribution is not present in the writable workspace. Native compile/runtime remains unverified.
- OpenAPI YAML parse **NOT VERIFIED** because PyYAML is not installed in this workspace. `git diff --check` passed; its CRLF notices are line-ending warnings, not whitespace errors.
- Physical camera, printer, emulator, live database migration, and hosted deployment **NOT VERIFIED**.
- No production database or deployment was touched.

## Feature safety notes

- `products.stock` remains as a compatibility aggregate. Current web/API invoice and damage workflows use the locked `StockService`; legacy `app/Http/Controllers/InvoiceController.php` is not routed. Backup import now rejects product quantity changes without branch-ledger data; full branch-ledger backup/restore remains an open feature.
- The branch-stock migration was not run against the configured database. Review migration and rollback on staging first; `stock:verify` is read-only but was not run against that configured DB.
- The accounting ledger was not added. Posting historical balances without a documented opening date can change financial meaning, so it requires the specified review and tests before implementation.
- Worktree is on the existing branch. The `.git` directory is read-only in this environment, so creating a feature branch was unavailable.


## Follow-up fixes - 2026-11-18

- The cashier success renderer now drops absent optional nodes before inserting DOM children, preventing literal `null` lines in successful checkout alerts. Cashier regression coverage verifies the renderer and response fields.
- The browser scanner checks secure context and `getUserMedia` availability before invoking ZXing, attempts the legacy browser camera API when available, and reports an Arabic fallback. The cashier modal provides Arabic manual barcode entry for unsupported/insecure contexts. Camera hardware behavior remains NOT VERIFIED.
- The maintenance list now displays linked device-photo thumbnails and an Arabic empty-image label.
- The web sidebar is grouped into Arabic categories and links every currently implemented new page with its permission check. Future mission modules remain absent until implemented.
- Latest verification: `php artisan test`: 167 passed, 1,092 assertions; `vendor/bin/pint --test` on changed PHP files: passed; `node --check resources/js/app.js`: passed; `git diff --check`: no whitespace errors (Git printed line-ending conversion notices).

## Mission work still outstanding

The sidebar does not claim routes for unfinished modules. The end-to-end 17-page mission is still incomplete: used phones, installation scheduling, cashier shifts, discount approval, partial-refund workflow, hardware diagnostics, treasury features, general ledger/period close, financial reporting distinct from sales reporting, and dedicated data export remain to be implemented. Partial scopes remain on purchasing/receiving, supplier balances, stock flows, tracked devices, and sales reports, especially Android parity and complete edit/cancel lifecycle. Android compilation is still NOT VERIFIED. Deployment has not been exercised and no production database was touched.

## Continuation - 2026-11-18

- Stocktake review now calculates and persists differences before approval and allows authorized reviewers to correct counted quantities/notes while still in review. Approval reconciles against movements that occurred after the count. Tests cover review visibility and corrections.
- Removed duplicate stock success alerts; the shared alert component now has a close button and auto-dismiss behavior. Stock branch forms use aligned responsive grids.
- Browser barcode scanning previously fell back to camera photo capture; the follow-up below removes that path in favor of a direct live camera permission request.
- Added a permission-gated Arabic hardware diagnostics page to web navigation and an Android diagnostics screen for local camera/print-service checks and connected barcode-reader input.
- Verification: PHP suite 183 passed (1,214 assertions); diagnostics navigation test passed (1 test, 23 assertions); `node --check resources/js/app.js` and `git diff --check` passed. Latest Vite production build passed (306 modules); the bundle-size warning remains. Android Gradle compilation did not reach Kotlin compilation: Gradle failed while accessing its own distribution JAR (`AccessDeniedException`), so native compile/runtime remains NOT VERIFIED. Production migrations and deployment remain untouched.
- Remaining mission scope is still substantial: cashier shifts, discount approvals, line-level partial refunds, treasury extension, general ledger/period close, distinct financial reports and dedicated exports, plus complete feature parity/testing across Android and web. This continuation does not claim the 17-page mission complete.

## Android barcode input and build repair - 2026-11-18

- Fixed the stocktake screen Kotlin parser error reported by the user, then corrected adjacent Android compiler errors uncovered by a real build (inventory refresh callback, scroll modifier, product list permission argument, report empty-state expression).
- Added a dedicated manual barcode input to Android cashier. It accepts keyboard-wedge scanner input, normalizes Arabic digits, looks up the exact barcode on Enter or button tap, adds the result to the invoice, and clears the field on success. Camera scanning remains optional.
- Updated `.gitignore` to ignore Markdown files, APK/AAB artifacts, Gradle/build/cache directories, and common language/tool caches. Existing tracked Markdown files remain tracked; ignore rules affect untracked/new Markdown files.
- Verification: `:app:compileDevelopmentDebugKotlin` passed and `assembleDebug` succeeded for development, production, and staging (129 tasks, 7m28s). No device installation/runtime test was run.

## Live web camera scanner - 2026-11-18

- Replaced the web photo-capture fallback with a live `getUserMedia` stream request made when the user opens the camera scanner. It does not enumerate devices first; after the browser grants permission, ZXing decodes directly from the live stream. Camera tracks stop when a barcode is read or the modal closes. Camera permission failures have Arabic explanations; manual/keyboard-wedge scanning remains available.
- Browser camera permission is controlled by the browser security model and cannot be forced by application code. `getUserMedia` is available only in secure contexts such as HTTPS and localhost; ordinary insecure HTTP does not expose `navigator.mediaDevices`. This makes live camera use impossible on those origins, while connected scanners that type into the cashier search remain usable.
- Verification: cashier scanner feature tests passed (2 tests); `node --check resources/js/app.js` passed; Vite production build passed (306 modules; existing large-chunk warning). Camera hardware/permission behavior still needs an actual browser/device check.

## Diagnostics camera start and product form messaging - 2026-11-18

- Added explicit start/stop live scanning controls and a video preview to the diagnostics page. The start button calls the shared scanner from a user gesture and reports secure-context status.
- The shared scanner now starts the `getUserMedia` permission request before dynamically loading ZXing modules, preserving the browser's click activation for the camera permission prompt. Pending streams are stopped if the scanner closes while permission is being resolved.
- Product create/edit pages now show the actual Arabic camera permission/platform error instead of replacing every error with “camera unavailable.”
- Verification: cashier/resource/diagnostics focused tests passed (8 tests, 83 assertions); JS syntax check and diff whitespace check passed. Vite production rebuild passed (306 modules; large-chunk warning remains).

## Cashier product pagination and stock alignment - 2026-11-18

- Cashier product search now returns 15 products per server page and includes pagination metadata. The cashier requests the next page only when selected, preserving lightweight initial loads and exact barcode lookup behavior.
- Product result cards use a fixed-width right-aligned price/stock column so the stock value stays aligned across varying product names and quantities.
- The branch inventory sidebar item is active only on the branch inventory page; opening the transfers route highlights the transfers item alone.


## Sidebar navigation follow-up - 2026-11-18

Navigation categories now use native, keyboard-accessible collapsible groups; the group containing the current route opens automatically. Added the existing stock transfer page to the inventory group. The sidebar already exposes implemented branch inventory, stock movements, stocktakes, tracked devices, and sales reports with permission checks. Unimplemented mission modules still have no route and therefore no link; do not treat this as completion of the full 17-page scope. `SidebarNavigationTest` verifies the disclosure markup and these routes.


## Used phones module - initial web and API implementation

Added `used_phones` records with reversible schema, permission grants, locked service operations, Arabic search/filter/pagination, full create/edit/delete controls, seller and inspection data, unique identifiers, purchase/asking prices, status, and optional managed image storage. API list/create/update/delete endpoints use page permissions and idempotency keys for mutations. Sold records are protected from deletion. The web sidebar now links the module under inventory.

The module is not yet complete against the full mission definition: purchase payment/contract posting, branch stock integration, Android list/detail/forms and Android build verification remain. Do not use this module as an accounting source until those controls are implemented. Focused tests cover API idempotency, web CRUD, field rendering, and sold-record delete protection.


## Product cost schema incident - 2026-11-18

The configured `phoneStore` MySQL database reports the branch-stock, product-cost/purchase-receipt, invoice-item snapshot, tracked-device, and used-phone migrations as pending. Laravel logs confirm the product edit 500 was `Unknown column 'cost_price'`; an earlier product creation also failed because `branches` does not exist. Product cost labels and currency output now use Arabic/EGP. Web cost inputs are disabled with migration guidance when the column is absent; API cost writes return a validation response instead of a database 500. API product listing and supplier receipt-product lookup omit the missing column safely. No migration or data change was run against the configured database, per `docs/DECISIONS.md`. A backup and reviewed migration execution on a throwaway/staging database remain required before those capabilities can be enabled locally or in production.


## Installation appointments - initial web/API wave

Added a new appointment table with customer/device/service details, Cairo-local schedule fields, duration, technician, status, address, notes, and completion timestamp. Arabic web CRUD includes every editable field, filtering, pagination, and delete. The permission-gated API provides paginated filters and idempotent create/update/delete. A transactional service serializes technician bookings and rejects overlapping active appointments; completion timestamps are managed server-side. Focused tests exercise web CRUD, API idempotency, Arabic fields, and overlap rejection. Android appointment screens are not implemented yet.


## Installation appointment module - 2026-11-18

The appointment module now has an Arabic responsive CRUD page, searchable/date/status filters, full edit/create forms, technician assignment, duration, completion timestamps, and permission-gated idempotent API mutations. Technician time conflicts are checked transactionally, serializing concurrent bookings on the technician row. Tests cover CRUD, API replay, and overlapping schedules. Android appointment parity remains outstanding. Its two new migrations are pending on the configured `phoneStore` database; this environment did not alter that database.
