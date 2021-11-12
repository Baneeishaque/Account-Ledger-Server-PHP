<?php

include_once 'config.php';
include_once 'common_functions.php';

$user_id = filter_input(INPUT_GET, 'user_id');
$specified_date = filter_input(INPUT_GET, 'specified_date');

$get_transaction_after_specified_date_sql = "SELECT `id`,`event_date_time`, `particulars`, `amount`, `insertion_date_time`,(SELECT name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_name`,(SELECT full_name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_full_name`,`from_account_id`,(SELECT name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_name`,(SELECT full_name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_full_name`,`to_account_id` FROM `transactionsv2` WHERE `inserter_id`='$user_id' AND CAST(`insertion_date_time` AS DATE)>'$specified_date' ORDER BY `transactionsv2`.`event_date_time`";

executeUserTransactionsSql($con, $get_transaction_after_specified_date_sql);
