-- SELECT
--   *
-- FROM
--   `transactionsv2`
-- WHERE id > 8161
--   AND `id` < 8201
--   AND `inserter_id` = 13
-- ORDER BY `event_date_time`;

SET @starting_transaction_id = 44109;

SET @ending_transaction_id = 44159;

SELECT
  *
FROM
  `transactionsv2`
WHERE id BETWEEN @starting_transaction_id
  AND @ending_transaction_id
ORDER BY `event_date_time`;

-- UPDATE
--   `transactionsv2`
-- SET
--   `event_date_time` = `event_date_time` + INTERVAL 6 DAY
-- WHERE id BETWEEN @starting_transaction_id
--   AND @ending_transaction_id;

UPDATE
  `transactionsv2`
SET
  `event_date_time` = `event_date_time` + INTERVAL 1 DAY
WHERE id BETWEEN @starting_transaction_id
  AND @ending_transaction_id;

SELECT
  *
FROM
  `transactionsv2`
WHERE id BETWEEN @starting_transaction_id
  AND @ending_transaction_id
ORDER BY `event_date_time`;

