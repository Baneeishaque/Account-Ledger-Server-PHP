<?php

include_once 'config.php';

$user_id = filter_input(INPUT_GET, 'user_id');
$parent_account_id = filter_input(INPUT_GET, 'parent_account_id');

if ($user_id > 20) {
    $get_accounts_sql = "SELECT `account_id`, `full_name`, `name`, `parent_account_id`, `account_type`, `notes`, `commodity_type`, `commodity_value`, `owner_id`, `taxable`,`place_holder` FROM `accounts` WHERE `owner_id`='$user_id' AND `parent_account_id`='$parent_account_id' ORDER BY `name`";
} else {
    $get_accounts_sql = "SELECT `account_id`, `full_name`, `name`, `parent_account_id`, `account_type`, `notes`, `commodity_type`, `commodity_value`, `owner_id`, `taxable`,`place_holder` FROM `accounts` WHERE `parent_account_id`='$parent_account_id' AND `owner_id`<21 ORDER BY `name`";
}

$accounts_result = $con->query($get_accounts_sql);

$accounts = array();
if (mysqli_num_rows($accounts_result) != 0) {

    $status = 0;
    while ($accounts_row = mysqli_fetch_assoc($accounts_result)) {

        $accounts[] = $accounts_row;
    }
} else {

    $status = 1;
}

echo json_encode(array("status" => $status, "accounts" => $accounts));