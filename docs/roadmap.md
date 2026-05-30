# Roadmap — Deferred Improvements

Tracked work items that surfaced during the May 2026
`delete_account.php` efficiency-ceiling review (commits `908c60e` →
`69add3f` → `f79d57c`) and were explicitly **deferred** to a later
session. None of these are efficiency-related — they are
architectural / robustness / UX / security improvements that the
current efficiency ceiling does NOT cover.

Status legend:

- 📋 **Deferred** — scope agreed, owner not yet assigned.
- 🔄 **In flight** — under active work in a feature branch.
- ✅ **Landed** — delivered (move into a CHANGELOG / release-notes
  document and remove from this file).

---

## 1. 📋 Audit log for `delete_account.php` (and peers)

**Scope.** Capture every successful DELETE in an append-only audit
table. Minimum columns: `(audit_id, account_id, outcome, deleted_at,
deleted_by_user_id, request_ip, source_endpoint)`. Reuse the same
shape for `delete_Transaction_v2.php`, `update_account.php`, and
future destructive endpoints.

**Rationale.** Today a DELETE leaves no forensic trail beyond the
`status:'0', affected_rows:1` HTTP response. Compliance / debugging
/ multi-user accountability all benefit from a tamper-evident audit
trail. This is also a prerequisite for any "show recent deletions"
admin view.

**Implementation sketch.**

```sql
CREATE TABLE account_deletion_audit (
    audit_id          BIGINT       UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id        INT          UNSIGNED NOT NULL,
    outcome           VARCHAR(16)  NOT NULL,                  -- 'deleted' | 'blocked' | 'not_found'
    blocker_flags     VARCHAR(64)  NULL,                      -- e.g. 'children,transactions'
    deleted_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_by_user   INT          UNSIGNED NULL,
    request_ip        VARCHAR(45)  NULL,
    source_endpoint   VARCHAR(64)  NOT NULL,
    INDEX idx_audit_account (account_id),
    INDEX idx_audit_when    (deleted_at)
);
```

INSERT it inside the same compound block (after the DELETE succeeds)
so the audit row is atomic with the DELETE.

**Acceptance criteria.** Every `delete_account.php` HTTP response with
`status='0'` produces exactly one audit row; `status='1'` outcomes
optionally logged with the appropriate `outcome` value.

**Cross-refs.** `http_API/delete_account.php`; the same pattern
applies to `delete_Transaction_v2.php`.

---

## 2. 📋 Rate limiting on destructive endpoints

**Scope.** Throttle per-user (or per-IP for unauthenticated paths)
DELETE / UPDATE calls. Sliding-window or token-bucket — any
implementation is fine; start simple.

**Rationale.** A rapid-fire delete loop (accidental or malicious)
could traverse the entire account tree in seconds. The DB-side FKs
prevent data corruption but not workload abuse.

**Implementation sketch.** Most realistic options at this stack:

- PHP-side: an in-database `request_throttle` table indexed by
  `(user_id, endpoint, window_start)`; check + increment at the top
  of `delete_account.php`.
- Edge-side (preferable): nginx `limit_req_zone` if the deployment
  has nginx in front.

**Acceptance criteria.** A documented per-user-per-endpoint limit
(e.g. 60 deletes / minute) returning HTTP 429 with
`Retry-After` header when exceeded.

**Cross-refs.** Same pattern applies to every endpoint under
`http_API/`.

---

## 3. 📋 Soft-delete option for `accounts`

**Scope.** Add `deleted_at TIMESTAMP NULL` to `accounts`. Change
`delete_account.php` to default to soft delete (set `deleted_at =
NOW()` instead of physical DELETE); preserve a `physical=1` query
parameter for the existing behavior.

**Rationale.** Today a delete is irreversible. Soft delete:
- enables undo,
- preserves history for audit / reporting,
- avoids the FK-trip blocker cascade (a soft-deleted parent can
  still have referenced children without breaking the constraint).

**Implementation sketch.**

```sql
ALTER TABLE accounts ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
CREATE INDEX idx_accounts_deleted_at ON accounts (deleted_at);
```

All other endpoints that read `accounts` need a `WHERE deleted_at IS
NULL` filter — this is the bigger lift, not the schema change.

**Acceptance criteria.**
- `DELETE` defaults to UPDATE setting `deleted_at`.
- Every read endpoint scopes to non-deleted rows by default.
- An explicit `physical=1` param performs the old physical DELETE
  (still subject to FK / trigger guards).

**Cross-refs.** Touches every `http_API/select_*` and `getAccounts.php`.
Architectural change — schedule its own design review before
implementation.

---

## 4. 📋 CSRF + auth check at endpoint boundary

**Scope.** Verify (or add) CSRF token validation and authenticated-
session enforcement at the top of every `http_API/*.php` endpoint,
or in a shared `auth.php` included by `config.php`.

**Rationale.** None of the `http_API/*.php` files visibly call an
auth gate today. If an authentication layer exists upstream
(e.g. nginx + LDAP, or an unseen `config.php` include), it MUST be
explicitly documented. If it doesn't, every endpoint is an open
hole.

**Implementation sketch.**

1. Audit current state: confirm whether `config.php` (or any
   included file) actually performs auth — and on what signal
   (session cookie, JWT, basic auth, IP allowlist).
2. If auth exists: document it in `README.md` + add a one-line
   `// auth enforced upstream by <X>` comment to every endpoint.
3. If auth does NOT exist: add CSRF (synchronizer-token pattern)
   and session validation before any DB call.

**Acceptance criteria.** Every endpoint is provably gated; an
unauthenticated POST returns HTTP 401 with no DB side effects.

**Cross-refs.** All `http_API/*.php`; `config.php`.

---

## 5. 📋 Real HTTP status codes on `http_API/*`

**Scope.** Replace the convention `HTTP 200 + status:'1' + error:<msg>`
with proper HTTP semantics:

| Outcome                  | Today                  | Should be                          |
|---|---|---|
| Success                  | 200 + status:'0'       | 200 + status:'0' (unchanged)       |
| Bad input (invalid id)   | 200 + status:'1'       | 400 Bad Request                    |
| Resource not found       | 200 + status:'1'       | 404 Not Found                      |
| FK / business-rule block | 200 + status:'1'       | 409 Conflict                       |
| Server-side failure      | 200 + status:'1'       | 500 Internal Server Error          |
| Rate-limited (see #2)    | n/a                    | 429 Too Many Requests              |
| Unauthenticated (see #4) | n/a                    | 401 Unauthorized                   |

**Rationale.** Clients (curl, monitoring tools, load balancers,
service meshes) treat HTTP status codes as the primary signal.
Returning 200 for failures defeats every layer that does
status-based retry / alerting / circuit-breaking.

**Wire-compat concern.** Existing clients (the Kotlin CLI + the
Flutter desktop) parse the `status` JSON field. Changing HTTP codes
would not break them PROVIDED the JSON body is unchanged (HTTP
status and response body are independent). Add the HTTP code as an
additive change, not a replacement — keep the `status` JSON field
for backward compatibility, mark it deprecated, and remove in a
future major version.

**Implementation sketch.** A tiny helper in `common_functions.php`:

```php
function respond(int $http_code, array $body): void {
    http_response_code($http_code);
    header('Content-Type: application/json');
    echo json_encode($body);
}
```

Then `delete_account.php` switch becomes:

```php
case 'deleted':   respond(200, ['status'=>'0','affected_rows'=>1]); return;
case 'not_found': respond(404, ['status'=>'1','error'=>'Account not found.']); return;
case 'blocked':   respond(409, ['status'=>'1','error'=>$msg]);      return;
```

**Acceptance criteria.** Every endpoint sets an HTTP code matching
its outcome category; existing JSON shape preserved for at least
one major version.

**Cross-refs.** All `http_API/*.php`; `common_functions.php`.

---

## Cross-references

- [`docs/portability.md`](portability.md) — the MariaDB vs MySQL
  decision matrix that documents the **current** efficiency
  ceiling. The items in this roadmap are explicitly orthogonal to
  the efficiency work captured there.
- [`http_API/delete_account.php`](../http_API/delete_account.php) —
  the canonical reference implementation that items 1, 2, 3, 4, 5
  would each touch.
- ai-suite-2 skill
  [`php-mysqli-prepared-statement-modernization`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/php-mysqli-prepared-statement-modernization)
  — the upstream modernization pattern; any item here should be
  written so a future application of that skill incorporates it
  naturally.
