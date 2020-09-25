-- Account ID 2 will be merged to account ID 1
 SET @account_id_1 = 3771;

SET @account_id_2 = 3741;

SELECT
  *
FROM
  `transactionsv2`
WHERE from_account_id = @account_id_1
  OR to_account_id = @account_id_1;

SELECT
  *
FROM
  `transactionsv2`
WHERE from_account_id = @account_id_2
  OR to_account_id = @account_id_2;

UPDATE
  transactionsv2
SET
  from_account_id = @account_id_1
WHERE from_account_id = @account_id_2;

UPDATE
  transactionsv2
SET
  to_account_id = @account_id_1
WHERE to_account_id = @account_id_2;

SELECT
  *
FROM
  `transactionsv2`
WHERE from_account_id = @account_id_1
  OR to_account_id = @account_id_1;

SELECT
  *
FROM
  `transactionsv2`
WHERE from_account_id = @account_id_2
  OR to_account_id = @account_id_2;

DELETE
FROM
  accounts
WHERE account_id = @account_id_2;

