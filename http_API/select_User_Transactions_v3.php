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

$empty_array = array();

function recursive_select_user_transactions($local_account_id)
{
    $get_accounts_sql = "SELECT `account_id` FROM `accounts` WHERE `parent_account_id`='$local_account_id' ORDER BY `account_id`";

    $get_accounts_sql_result = $GLOBALS['con']->query($get_accounts_sql);

    if (mysqli_num_rows($get_accounts_sql_result) != 0) {

        while ($get_accounts_sql_result_row = mysqli_fetch_assoc($get_accounts_sql_result)) {

            recursive_select_user_transactions($get_accounts_sql_result_row['account_id']);
        }
    }

    $get_transactions_sql = "SELECT `id`,`event_date_time`, `particulars`, `amount`, `insertion_date_time`,(SELECT name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_name`,(SELECT full_name FROM accounts WHERE accounts.account_id=from_account_id) AS `from_account_full_name`,`from_account_id`,(SELECT name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_name`,(SELECT full_name FROM accounts WHERE accounts.account_id=to_account_id) AS `to_account_full_name`,`to_account_id` FROM `transactionsv2` WHERE `inserter_id`='" . $GLOBALS['user_id'] . "' AND (`from_account_id`='$local_account_id' OR `to_account_id`='$local_account_id') ORDER BY `transactionsv2`.`event_date_time`";

    $get_transactions_sql_result = $GLOBALS['con']->query($get_transactions_sql);

    if (mysqli_num_rows($get_transactions_sql_result) != 0) {

        while ($get_transactions_sql_result_row = mysqli_fetch_assoc($get_transactions_sql_result)) {
            $get_transactions_sql_result_row['parent_account_id'] = $local_account_id;
            $GLOBALS['empty_array'][] = $get_transactions_sql_result_row;
        }
    }
}

recursive_select_user_transactions($account_id);
var_dump($empty_array);
array_unshift($empty_array,array("status"=>"0"));
var_dump($empty_array);
// if(empty($empty_array))
// {
	// array_unshift($empty_array,"status"=>"2");
	// echo json_encode($empty_array);
// }
// else
// {
	// array_unshift($empty_array,"status"=>"0");
	// echo json_encode($empty_array);
// }