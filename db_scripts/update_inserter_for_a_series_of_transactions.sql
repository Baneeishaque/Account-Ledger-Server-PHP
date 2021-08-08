SET @inserter_id_to_be_update = 13;
SET @inserter_id_to_update = 3;
SET @starting_transaction_id = 42759;
SET @ending_transaction_id = 42859;

SELECT
  *
FROM
  `transactionsv2`
WHERE id BETWEEN @starting_transaction_id
  AND @ending_transaction_id AND inserter_id = @inserter_id_to_be_update
ORDER BY `event_date_time`;

UPDATE
  `transactionsv2`
SET
  `inserter_id` = @inserter_id_to_update
WHERE id BETWEEN @starting_transaction_id
  AND @ending_transaction_id AND inserter_id = @inserter_id_to_be_update;
  
SELECT
  *
FROM
  `transactionsv2`
WHERE id BETWEEN @starting_transaction_id
  AND @ending_transaction_id AND inserter_id = @inserter_id_to_be_update
ORDER BY `event_date_time`;

SELECT
  *
FROM
  `transactionsv2`
WHERE id BETWEEN @starting_transaction_id
  AND @ending_transaction_id AND inserter_id = @inserter_id_to_update
ORDER BY `event_date_time`;
