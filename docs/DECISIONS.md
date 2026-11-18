# Decisions

## Phase 0

### 2026-11-17 — Preserve configured database during baseline

- **Decision:** Do not replace `.env`, run `migrate --seed`, or run any destructive migration command against the configured database.
- **Alternatives:** Follow the prompt's bootstrap literally against the current `.env`; create and explicitly configure a throwaway database first.
- **Reason:** `.env` targets MySQL database `phoneStore`, while `.env.example` targets `storemanagement`. The configured database has existing migrations and likely real data. The prompt prohibits touching real data without backup. A dedicated throwaway database is required before migration testing.

### 2026-11-17 — Continue existing API and Android implementations

- **Decision:** Treat the repository as already containing a versioned API and Android app, and extend those architectures rather than scaffold duplicates.
- **Alternatives:** Follow the quick-read premise that API and native client work are largely absent.
- **Reason:** `routes/api.php`, `/api/v1` documentation/tests, and `android/` are present in the actual repository.

### 2026-11-17 — Do not begin feature waves before baseline gate

- **Decision:** Stop after documenting baseline gaps; do not add feature code until the Phase 0 gate is complete.
- **Alternatives:** Start a feature while the complete pre-change test baseline, disposable DB bootstrap, and Android test environment remain unresolved.
- **Reason:** The mission declares Phase 0 a hard gate and requires old failures to be captured before feature work.

## Audit follow-up

### 2026-11-18 — Branch stock is the stock authority

- **Decision:** Keep `products.stock` as a compatibility aggregate while `branch_stock` and append-only `stock_movements` are authoritative for mutations. New branch balances start at zero; existing aggregate balances migrate to the main branch as opening movements. Negative legacy stock aborts migration for review rather than being clamped.
- **Alternatives:** Replace `products.stock` immediately; split stock without a ledger; silently clamp negative balances.
- **Reason:** Existing web/API clients use `products.stock`, and silent stock repair would alter business data. A verified aggregate provides a compatibility bridge while branch-aware views use branch quantities.

### 2026-11-18 — Backup import cannot rewrite inventory quantities

- **Decision:** The existing catalog backup format may update product metadata, but rejects a product quantity that differs from the branch ledger. New imported products must have zero stock and receive zero branch rows. Quantity changes must use stock adjustment/count/receipt workflows.
- **Alternatives:** Allow the legacy importer to edit aggregate quantity without a movement; add movement-history backup format and replay/replace logic now.
- **Reason:** The existing backup omits branch distribution and immutable movement history. Importing its aggregate would corrupt reconciliation. A complete inventory-aware backup format needs a separate versioned migration plan.

### 2026-11-18 — Supplier aging uses invoice age and preserves opening balance visibility

- **Decision:** Age each unpaid purchase from its purchase date in `Africa/Cairo`; expose opening balance separately and report any difference between the stored supplier payable and opening balance plus open purchase balances. The statement is read-only.
- **Alternatives:** Assign the opening balance to an aging bucket; recalculate/write the stored payable while rendering the report.
- **Reason:** Opening balances have no reliable due date, and silently repairing historical supplier balances could alter the accounts. The difference is shown for review instead of being auto-corrected.

### 2026-11-17 — Preserve supplier financial history

- **Decision:** A supplier with any purchase or payment records cannot be deleted from web or API.
- **Alternatives:** Keep the cascading delete; soft-delete/inactivate the supplier; add compensating supplier ledger operations.
- **Reason:** Cascading deletion would erase financial records. There is no existing supplier active flag, and adding one would require a migration/UI change. Blocking deletion is the smallest safe fix; future archive support can be additive.

### 2026-11-17 — Keep private notes permission-scoped

- **Decision:** Supplier and maintenance notes are returned only to users with the module's manage permission; view-only resource responses omit them. Updates that omit supplier notes preserve the stored value.
- **Alternatives:** Expose notes to all viewers; omit notes for all API users.
- **Reason:** Preserve the established private-note response policy while allowing authorized mobile managers to round-trip notes without accidental erasure.

### 2026-11-17 — Make generated media disk configurable

- **Decision:** Use `MEDIA_DISK` for generated product/maintenance/damaged images, defaulting to the existing `public` disk.
- **Alternatives:** Switch all deployments to object storage; keep a hard-coded local disk.
- **Reason:** The default remains behavior-compatible. Coolify can select a persistent mounted disk; Laravel Cloud object storage requires adding the S3 adapter and migrating legacy `public/uploads` files before it can be enabled.


### 2026-11-18 - Keep sale-time costs and report missing payment facts honestly

- **Decision:** Add normalized invoice item snapshots with unit price and manually maintained unit cost. New sales capture current cost; legacy item rows are backfilled from current product cost and marked estimated. Missing or zero cost is surfaced. Existing invoices are attributed to the main branch because historical branch attribution is unavailable; new invoices store cashier and active branch.
- **Alternatives:** Derive profit from the current product cost at report time; rebuild historical invoice totals; infer payment method or discounts from paid amount.
- **Reason:** Current catalog cost may change after a sale, and neither discounts nor tender method are stored. Reports preserve reliable facts, label migrated estimates, and do not invent those missing values.


### 2026-11-18 ? Keep tracked device identity separate from aggregate product stock

- **Decision:** Store optional unique IMEI and serial numbers in an individual-device table linked to a catalog product. Do not change aggregate quantities through device CRUD; sales and receiving continue to use `StockService`. A sold device's product and identifiers cannot be changed, and sold or invoice-linked records cannot be deleted.
- **Alternatives:** Replace product stock with unit rows immediately; permit edits/deletes to all device records.
- **Reason:** Existing sales and receipts are quantity-based and have no device assignment history. The additive register supports current tracing without silently rewriting legacy stock or invoices. Automatic unit-to-invoice assignment needs a later checkout design with explicit device selection.


### 2026-11-18 - Technician appointment overlap policy

- **Decision:** Serialize appointments on each technician row and reject overlaps among scheduled, confirmed, or in-progress appointments. Completed and cancelled records do not reserve the time.
- **Alternatives:** Permit double booking; use a unique exact-start index that cannot account for durations; reserve completed/cancelled slots forever.
- **Reason:** Duration varies, so interval overlap must be checked. Locking the technician row prevents concurrent application bookings from passing the same overlap check.
