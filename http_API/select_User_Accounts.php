<?php

include_once 'config.php';

$user_id = filter_input(INPUT_GET, 'user_id');
$parent_account_id = filter_input(INPUT_GET, 'parent_account_id');

if($user_id>12)
{
    $get_accounts_sql = "SELECT `account_id`, `full_name`, `name`, `parent_account_id`, `account_type`, `notes`, `commodity_type`, `commodity_value`, `owner_id`, `taxable`,`place_holder` FROM `accounts` WHERE `owner_id`='$user_id' AND `parent_account_id`='$parent_account_id' ORDER BY `name`";
}
else
{
    $get_accounts_sql = "SELECT `account_id`, `full_name`, `name`, `parent_account_id`, `account_type`, `notes`, `commodity_type`, `commodity_value`, `owner_id`, `taxable`,`place_holder` FROM `accounts` WHERE `parent_account_id`='$parent_account_id' AND `owner_id`<13 ORDER BY `name`";
}


$status_result = $con->query($get_accounts_sql);

$emptyarray = array();

if (mysqli_num_rows($status_result) != 0) {

    array_push($emptyarray, array("status" => "0"));

    while ($status_row = mysqli_fetch_assoc($status_result)) {
        $emptyarray[] = $status_row;
    }
} else {
    array_push($emptyarray, array("status" => "1"));
}

echo json_encode($emptyarray);
