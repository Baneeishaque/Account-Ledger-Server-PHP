# Portability — MariaDB vs MySQL Compatibility

> **Current server**: `MariaDB 12.2.2-MariaDB-log` (probed via
> [`probe-server-flavor.py`](https://github.com/Baneeishaque/ai-suite-2/blob/main/.agents/skills/mysql-capability-probe-pymysql/scripts/probe-server-flavor.py)).
> This document records the MariaDB-specific features this codebase
> depends on, and the migration recipe to MySQL if the deployment
> ever changes.

---

## MariaDB-only features in use

### `BEGIN NOT ATOMIC` compound block at the top level

**Used by:** [`http_API/delete_account.php`](../http_API/delete_account.php)

MariaDB ≥ 10.1 (Oct 2015) permits compound-statement blocks
(`BEGIN ... DECLARE HANDLER ... END`) **outside** stored routines — as
ad-hoc client queries. MySQL parses compound blocks **only** inside
`CREATE PROCEDURE` / `CREATE FUNCTION` / `CREATE TRIGGER` /
`CREATE EVENT`; pasted top-level it fails with
`ERROR 1064 (42000): … near 'BEGIN NOT ATOMIC'`.

We use this to implement **DELETE-first with diagnostic fallback** in
one network round-trip:

```sql
BEGIN NOT ATOMIC
  DECLARE EXIT HANDLER FOR 1451  -- FK violation
    SELECT 'blocked' AS outcome,
           EXISTS(...) AS has_children,
           EXISTS(...) AS has_transactions,
           0 AS affected_rows;
  DELETE FROM accounts WHERE account_id=X;
  SELECT CASE WHEN ROW_COUNT()=1 THEN 'deleted' ELSE 'not_found' END,
         0, 0, ROW_COUNT();
END
```

Happy path runs only the DELETE; FK trip routes through the handler
which emits a structured `'blocked'` resultset enumerating BOTH
blocker flags (children, transactions). One query, one resultset, one
round-trip — but **MariaDB-locked**.

### Why we did NOT use `PREPARE`

Compound blocks cannot be prepared on MariaDB. The `account_id`
parameter is therefore validated via `FILTER_VALIDATE_INT` and
interpolated into the SQL string. This is **SQLi-safe** (an integer
that survived `FILTER_VALIDATE_INT` is by definition harmless to
interpolate) but it is NOT a substitute for prepared statements
elsewhere — keep prepared statements with bound params in every
non-compound endpoint.

---

## Migration recipe to MySQL (or any portable target)

If the deployment ever moves to MySQL 5.x / 8.x, Aurora MySQL, RDS
MySQL, PlanetScale, Vitess, or TiDB, the migration is mechanical.
**Choose one of two tiers**:

### Tier B — Stored procedure (1-round-trip, portable, adds a schema object)

1. Add a migration that creates the procedure ONCE per database:

   ```sql
   DELIMITER //
   CREATE PROCEDURE sp_delete_account(IN p_account_id INT)
   BEGIN
     DECLARE EXIT HANDLER FOR 1451
       SELECT 'blocked' AS outcome,
              EXISTS(SELECT 1 FROM accounts
                     WHERE parent_account_id=p_account_id) AS has_children,
              EXISTS(SELECT 1 FROM transactionsv2
                     WHERE from_account_id=p_account_id
                        OR to_account_id=p_account_id)     AS has_transactions,
              0 AS affected_rows;
     DELETE FROM accounts WHERE account_id=p_account_id;
     SELECT CASE WHEN ROW_COUNT()=1 THEN 'deleted' ELSE 'not_found' END AS outcome,
            0 AS has_children, 0 AS has_transactions,
            ROW_COUNT() AS affected_rows;
   END //
   DELIMITER ;
   ```

2. Grant the app user EXECUTE on the procedure:

   ```sql
   GRANT EXECUTE ON PROCEDURE baneeishaque_account_ledger.sp_delete_account
                  TO 'app_user'@'%';
   ```

3. Replace `delete_account.php`'s compound-block `$con->query(...)`
   with a prepared `CALL`:

   ```php
   $stmt = $con->prepare("CALL sp_delete_account(?)");
   $stmt->bind_param('i', $account_id);
   $stmt->execute();
   $res = $stmt->get_result();
   $row = $res ? $res->fetch_assoc() : null;
   // … same outcome switch as today
   ```

   The PHP `outcome`-switch is unchanged. The only behavioral
   difference: the param is now bound (not interpolated), so
   `FILTER_VALIDATE_INT` becomes belt-and-suspenders rather than
   strictly required.

### Tier C — App-side branching (portable, no schema object, 2 round-trips on blocked path)

If DDL permission is unavailable (some hosted DBaaS limit
`CREATE PROCEDURE` to the root user), drop the compound block and
branch in PHP:

```php
try {
    $stmt = $con->prepare("DELETE FROM accounts WHERE account_id=?");
    $stmt->bind_param('i', $account_id);
    $stmt->execute();
    if ($stmt->affected_rows === 1) {
        echo json_encode(['status'=>'0','affected_rows'=>1]); return;
    }
    echo json_encode(['status'=>'1','error'=>'Account not found.']); return;
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() !== 1451) { throw $e; }
    $stmt = $con->prepare("SELECT
        EXISTS(SELECT 1 FROM accounts WHERE parent_account_id=?) AS has_children,
        EXISTS(SELECT 1 FROM transactionsv2
               WHERE from_account_id=? OR to_account_id=?) AS has_transactions");
    $stmt->bind_param('iii', $account_id, $account_id, $account_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    // build "Account cannot be deleted : has X and Y." message
}
```

Cost: blocked path becomes 2 round-trips (~100 ms on remote DB vs
~50 ms today). Happy path stays at 1 round-trip and is actually
cheaper than today because there is no compound-block parse.

### Decision rubric

| If you are migrating to… | Use |
|---|---|
| Self-hosted MySQL with DBA access | Tier B |
| AWS RDS MySQL / Aurora MySQL with admin role | Tier B |
| Managed DB with restricted DDL (some PlanetScale / TiDB Cloud plans) | Tier C |
| Edge / serverless DB-as-a-Service that doesn't surface stored procedures | Tier C |
| Staying on MariaDB ≥ 10.1 | **No change needed** (current Tier A is correct) |

---

## Other MariaDB-friendly choices in this codebase (currently portable)

These work identically on MySQL and MariaDB — no migration needed:

- `multi_query` / `CLIENT_MULTI_STATEMENTS` (see other `http_API/*.php`
  endpoints).
- `mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)`.
- `FOREIGN KEY ... ON DELETE RESTRICT` constraints
  (`fk_accounts_parent`, `fk_txv2_from_account`, `fk_txv2_to_account`).
- `BEFORE INSERT` / `BEFORE UPDATE` triggers
  (`trg_accounts_no_self_parent_ins/_upd`) — chosen over `CHECK`
  because MariaDB error 1901 forbids referencing `AUTO_INCREMENT`
  columns from `CHECK` constraints; the migration to MySQL 8.0 would
  permit a true `CHECK` here, but the triggers also work on MySQL
  without change.
- `FILTER_VALIDATE_INT` + integer interpolation in compound-block
  contexts.

---

## Related references

- [`.agents/skills/php-mysqli-prepared-statement-modernization`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/php-mysqli-prepared-statement-modernization)
  — Step 6 documents the same 3-tier matrix at the skill level.
- [`.agents/skills/mariadb-check-autoincrement-trigger-fallback`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/mariadb-check-autoincrement-trigger-fallback)
  — why we use triggers instead of `CHECK` (MariaDB error 1901
  rationale).
- [`.agents/skills/mysql-fk-hardening-workflow`](https://github.com/Baneeishaque/ai-suite-2/tree/main/.agents/skills/mysql-fk-hardening-workflow)
  — installs the `ON DELETE RESTRICT` FKs that this DELETE-first
  pattern reacts to.
