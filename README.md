# Account Ledger Server (PHP)

The canonical PHP HTTP backend for the Account Ledger ecosystem,
backed by MariaDB. Multiple client applications consume these
endpoints — a Kotlin CLI, a Flutter desktop client (which embeds two
shared libraries), and an Android client.

> ⚠️ **Canonical source vs workflow repo.** This repository
> (`Account-Ledger-Server-PHP`) is the canonical source. The sibling
> `Account-Ledger-Server` repo is a workflow / backup repo and is
> NOT where API code is edited. If you arrived here from a misrouted
> change, see the ai-suite-2
> [`canonical-source-vs-workflow-repo-audit`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/canonical-source-vs-workflow-repo-audit)
> skill.

## Documentation index

| Document | Contents |
|---|---|
| [`docs/portability.md`](docs/portability.md) | MariaDB-vs-MySQL compatibility — what features lock us to MariaDB (`BEGIN NOT ATOMIC` in `delete_account.php`), the 2-tier migration recipe (Tier B stored procedure / Tier C app-side branching), and three efficiency-ceiling deep dives (stored-routine cache, composite covering indexes, `mysqli_report` modes). |
| [`docs/roadmap.md`](docs/roadmap.md) | Deferred non-efficiency improvements — audit log, rate limiting, soft delete, CSRF/auth audit, real HTTP status codes. Each item has scope, rationale, sketch, acceptance criteria. |

## Tech stack

| Layer | Component | Pinned |
|---|---|---|
| Language | PHP | `^8.0.12` (see `composer.json`); CI/dev uses 8.5.6 via `mise.toml` |
| DB driver | `ext-mysqli` | required (see `composer.json`) |
| Database | MariaDB | `12.2.2-MariaDB-log` (probed live; min supported = 10.1 for `BEGIN NOT ATOMIC`) |
| Toolchain pin | [`mise`](https://mise.jdx.dev) | `mise.toml` pins `github:adwinying/php` to `8.5.6` |

## Local setup

```bash
# 1. Install pinned PHP via mise
cd Account-Ledger-Server-PHP
mise install            # reads mise.toml

# 2. Install composer dependencies (none beyond ext-mysqli right now)
composer install

# 3. Configure DB connection
cp act.secrets_sample act.secrets
# edit act.secrets with DB_HOST / DB_USER / DB_PASSWORD / DB_NAME

# 4. Lint check a single endpoint
mise exec -- php -l http_API/delete_account.php
```

DB connection is centralized in `http_API/config.php`. Each endpoint
includes it and inherits the `$con` mysqli handle.

## Endpoint inventory

All endpoints live under `http_API/` and accept `POST` parameters.
The endpoints marked ✅ are modernized (prepared statements,
`mysqli_report` STRICT, structured error responses); legacy
endpoints are still on the polling pattern and on the deferred
modernization list.

### Account endpoints

| Endpoint | Method | Modernized | Notes |
|---|---|---|---|
| `insert_Account.php` | POST | partial | INSERT path; self-parent guard pairing planned (see roadmap #4). |
| `update_account.php` | POST | ✅ | Prepared statement; FILTER_VALIDATE_INT; self-parent guard cites trigger pair `trg_accounts_no_self_parent_ins/_upd`. |
| `delete_account.php` | POST | ✅ | **Tier-A DELETE-first via MariaDB `BEGIN NOT ATOMIC` + 1451 handler.** One round-trip on every path; full blocker enumeration. See [`docs/portability.md`](docs/portability.md) for tier matrix and migration recipe. |
| `getAccounts.php` | POST | legacy | Polling-mode `mysqli`. |
| `select_User_Accounts.php`, `select_User_Accounts_v2.php`, `select_User_Accounts_full.php` | POST | legacy | Read endpoints. |

### Transaction endpoints

| Endpoint | Method | Modernized | Notes |
|---|---|---|---|
| `insert_Transaction_v2.php` | POST | legacy | |
| `update_Transaction_v2.php` | POST | legacy | |
| `delete_Transaction_v2.php` | POST | ✅ | Reference implementation in the ai-suite-2 [`php-mysqli-prepared-statement-modernization`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/php-mysqli-prepared-statement-modernization) skill's `examples/`. |
| `insert_Transaction.php` | POST | legacy (v1 schema) | |
| `select_Transactions_v2m.php`, `select_User_Transactions_v2m.php`, `select_User_Transactions_v2.php`, `select_User_Transactions_v3.php`, `select_User_Transactions_After_Specified_Date.php` | POST | legacy | Read endpoints. |
| `getTransactions.php`, `getTransactionsV2.php` | POST | legacy | |

### User / configuration endpoints

| Endpoint | Method | Modernized | Notes |
|---|---|---|---|
| `select_User.php` | POST | ✅ | Reference implementation in the ai-suite-2 php-mysqli skill's `examples/`. |
| `insertUser.php` | POST | legacy | |
| `getUsers.php` | POST | legacy | |
| `select_Configuration.php`, `getConfiguration.php` | POST | legacy | |

### Shared

| File | Purpose |
|---|---|
| `http_API/config.php` | Centralized mysqli connection. |
| `http_API/common_functions.php` | Shared helpers. |

## Database hardening

The schema layer is hardened by foreign keys and triggers maintained
in [`db_scripts/`](db_scripts) and [`db_structures/`](db_structures).
Key invariants:

- `fk_accounts_parent` — `accounts.parent_account_id` references
  `accounts.account_id` with `ON DELETE RESTRICT`.
- `fk_txv2_from_account`, `fk_txv2_to_account` — `transactionsv2`
  references `accounts` with `ON DELETE RESTRICT`.
- `trg_accounts_no_self_parent_ins`,
  `trg_accounts_no_self_parent_upd` — `BEFORE INSERT`/`BEFORE UPDATE`
  triggers rejecting `parent_account_id = account_id` (the
  app-layer half lives in the modernized endpoints + the shared
  Kotlin/Dart libraries' `AccountValidationUtils`).

These were installed via the ai-suite-2
[`mysql-fk-hardening-workflow`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/mysql-fk-hardening-workflow)
and
[`mariadb-check-autoincrement-trigger-fallback`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/mariadb-check-autoincrement-trigger-fallback)
skills (MariaDB error 1901 forbids `CHECK` constraints referencing
`AUTO_INCREMENT` PKs — triggers are the documented fallback).

## Related upstream skills (ai-suite-2)

| Skill | Purpose |
|---|---|
| [`php-mysqli-prepared-statement-modernization`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/php-mysqli-prepared-statement-modernization) | The modernization pattern (Step 1–6) applied to endpoints marked ✅ above. |
| [`mysql-fk-hardening-workflow`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/mysql-fk-hardening-workflow) | Installed the FKs listed under DB hardening. |
| [`mariadb-check-autoincrement-trigger-fallback`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/mariadb-check-autoincrement-trigger-fallback) | Installed the self-parent triggers. |
| [`canonical-source-vs-workflow-repo-audit`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/canonical-source-vs-workflow-repo-audit) | Disambiguates this repo from the workflow / backup sibling. |
| [`db-backup-bracketing-protocol`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/db-backup-bracketing-protocol) | Backup discipline around destructive DB operations. |
| [`mysql-capability-probe-pymysql`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/mysql-capability-probe-pymysql) | Probes used to confirm server version / engine / capabilities before authoring DDL. |

## License

GPL-3.0-or-later (see `LICENSE`).
