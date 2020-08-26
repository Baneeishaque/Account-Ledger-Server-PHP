SELECT
  *
FROM
  `heroku_0fd2194a537b978`.`transactionsv2`
WHERE `from_account_id` = '11'
  OR `to_account_id` = '11'
LIMIT 0, 1000;
