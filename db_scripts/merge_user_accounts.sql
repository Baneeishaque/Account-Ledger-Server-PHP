-- user ID - 14 to be merged to user ID - 13
 SET @merge_account_1 = 14;

SET @merge_account_2 = 13;

SELECT
  *
FROM
  `transactionsv2`
WHERE `inserter_id` = @merge_account_1;

-- SELECT
--   *
-- FROM
--   `transactions`
-- WHERE `inserter_id` = @merge_account_1;

 SELECT
  *
FROM
  `accounts`
WHERE `owner_id` = @merge_account_1;

-- Delete Opening Balance Transactions
 SELECT
  *
FROM
  `transactionsv2`
WHERE `inserter_id` = @merge_account_1
  AND `from_account_id` = 483;

DELETE
FROM
  `transactionsv2`
WHERE `inserter_id` = @merge_account_1
  AND `from_account_id` = 483;

-- Merge process
 UPDATE
  accounts
SET
  owner_id = @merge_account_2
WHERE owner_id = @merge_account_1;

UPDATE
  transactionsv2
SET
  inserter_id = @merge_account_2
WHERE inserter_id = @merge_account_1;

-- UPDATE
--   transactions
-- SET
--   inserter_id = 14
-- WHERE inserter_id = 15;

 SELECT
  *
FROM
  `users`;

DELETE
FROM
  `users`
WHERE `id` = @merge_account_1;

