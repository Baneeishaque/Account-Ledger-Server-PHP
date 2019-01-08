<?php
/**
 * Created by PhpStorm.
 * User: Srf
 * Date: 08-01-2019
 * Time: 13:00
 */

include_once 'config.php';

$user_id = filter_input(INPUT_GET, 'user_id');
$account_id = filter_input(INPUT_GET, 'account_id');

$get_accounts_sql = "SELECT `account_id` FROM `accounts` WHERE `parent_account_id`='$account_id' ORDER BY `account_id`";

$get_accounts_sql_result = $con->query($get_accounts_sql);

$empty_array = array();

if (mysqli_num_rows($get_accounts_sql_result) != 0) {

//    array_push($empty_array, array("status" => "0"));

    while ($get_accounts_sql_result_row = mysqli_fetch_assoc($get_accounts_sql_result)) {
//        $empty_array[] = $status_row;
        //TODO : Iterate Child A/cs
    }
} else {
//    array_push($empty_array, array("status" => "1"));


    $get_transactions_sql = "SELECT `id`,`event_date_time`, `particulars`, `amount`, `insertion_date_time`,(SELECT name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_name`,(SELECT full_name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_full_name`,`from_account_id`,(SELECT name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_name`,(SELECT full_name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_full_name`,`to_account_id` FROM `transactionsv2` WHERE `inserter_id`='$user_id' AND (`from_account_id`='$account_id' OR `to_account_id`='$account_id') ORDER BY `transactionsv2`.`event_date_time`";

    $get_transactions_sql_result = $con->query($get_transactions_sql);

//    $emptyarray = array();

    if (mysqli_num_rows($get_transactions_sql_result) != 0) {

//        array_push($emptyarray, array("status" => "0"));

        while ($get_transactions_sql_result_row = mysqli_fetch_assoc($get_transactions_sql_result)) {
            $empty_array[] = $get_transactions_sql_result_row;
        }
    }
//    else {
//        array_push($emptyarray, array("status" => "2"));
//    }

}

echo json_encode($empty_array);
