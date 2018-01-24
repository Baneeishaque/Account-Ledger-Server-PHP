SET @checking_account_id=3241;
SELECT * FROM accounts WHERE account_id=@checking_account_id;
SELECT * FROM accounts WHERE parent_account_id=@checking_account_id;
SELECT * FROM transactionsv2 WHERE from_account_id=@checking_account_id OR to_account_id=@checking_account_id;

DELETE FROM accounts WHERE account_id=@checking_account_id;