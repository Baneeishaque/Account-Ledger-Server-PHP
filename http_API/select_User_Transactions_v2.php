<?php

include_once 'config.php';

$user_id = filter_input(INPUT_GET, 'user_id');
$account_id = filter_input(INPUT_GET, 'account_id');

$status_sql = "SELECT `id`,`event_date_time`, `particulars`, `amount`, `insertion_date_time`,(SELECT name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_name`,`from_account_id`,(SELECT name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_name`,`to_account_id` FROM `transactionsv2` WHERE `inserter_id`='$user_id' AND (`from_account_id`='$account_id' OR `to_account_id`='$account_id') ORDER BY `transactionsv2`.`event_date_time`";
$status_result = $con->query($status_sql);

$emptyarray = array();

if (mysqli_num_rows($status_result) != 0) {

    array_push($emptyarray, array("status" => "0"));

    while ($status_row = mysqli_fetch_assoc($status_result)) {
        $emptyarray[] = $status_row;
    }
} else {
    array_push($emptyarray, array("status" => "2"));
}

echo json_encode($emptyarray);
