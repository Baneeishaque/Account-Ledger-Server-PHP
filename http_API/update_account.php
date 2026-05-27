<?php

include_once 'config.php';

$account_id = filter_input(INPUT_POST, 'account_id');
$full_name = filter_input(INPUT_POST, 'full_name');
$name = filter_input(INPUT_POST, 'name');
$parent_account_id = filter_input(INPUT_POST, 'parent_account_id');
$account_type = filter_input(INPUT_POST, 'account_type');
$notes = filter_input(INPUT_POST, 'notes');
$commodity_type = filter_input(INPUT_POST, 'commodity_type');
$commodity_value = filter_input(INPUT_POST, 'commodity_value');
$taxable = filter_input(INPUT_POST, 'taxable');
$place_holder = filter_input(INPUT_POST, 'place_holder');

$sql = "UPDATE `accounts` SET `full_name`='$full_name', `name`='$name', `parent_account_id`='$parent_account_id', `account_type`='$account_type', `notes`='$notes', `commodity_type`='$commodity_type', `commodity_value`='$commodity_value', `taxable`='$taxable', `place_holder`='$place_holder' WHERE `account_id`='$account_id'";

if (!$con->query($sql)) {
    $arr = array('status' => "1", 'error' => $con->error);
} else {
    $arr = array('status' => "0");
}
echo json_encode($arr);
