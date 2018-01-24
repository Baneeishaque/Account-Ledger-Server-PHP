<?php
include_once 'config.php';
include_once 'common_functions.php';

$user_id = filter_input(INPUT_GET, 'user_id');

$get_user_transactions_sql = "SELECT `id`,`event_date_time`, `particulars`, `amount`, `insertion_date_time`,(SELECT name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_name`,(SELECT full_name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_full_name`,`from_account_id`,(SELECT name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_name`,(SELECT full_name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_full_name`,`to_account_id` FROM `transactionsv2` WHERE `inserter_id`='$user_id' ORDER BY `transactionsv2`.`event_date_time` DESC LIMIT 0,10";

executeUserTransactionsSql($con, $get_user_transactions_sql);
