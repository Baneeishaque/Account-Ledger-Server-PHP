SELECT
  *
FROM
  `transactionsv2`
WHERE `from_account_id` = '11'
  OR `to_account_id` = '11'
LIMIT 0, 1000;
