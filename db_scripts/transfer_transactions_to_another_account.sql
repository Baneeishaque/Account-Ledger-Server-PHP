SET @from_account_id = 9055;

SET @to_account_id = 9082;

SELECT *
FROM transactionsv2
WHERE from_account_id = @from_account_id;

UPDATE
  transactionsv2
SET
  from_account_id = @to_account_id
WHERE from_account_id = @from_account_id;

SELECT *
FROM transactionsv2
WHERE from_account_id = @from_account_id;

SELECT *
FROM transactionsv2
WHERE from_account_id = @to_account_id;

SELECT *
FROM transactionsv2
WHERE to_account_id = @from_account_id;

UPDATE
  transactionsv2
SET
  to_account_id = @to_account_id
WHERE to_account_id = @from_account_id;

SELECT *
FROM transactionsv2
WHERE to_account_id = @from_account_id;

SELECT *
FROM transactionsv2
WHERE to_account_id = @to_account_id;
