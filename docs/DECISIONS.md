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
