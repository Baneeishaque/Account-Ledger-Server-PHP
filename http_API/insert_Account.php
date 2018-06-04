<?php

include_once 'config.php';

$full_name = filter_input(INPUT_POST, 'full_name');
$name = filter_input(INPUT_POST, 'name');
$parent_account_id = filter_input(INPUT_POST, 'parent_account_id');
$account_type = filter_input(INPUT_POST, 'account_type');
$notes = filter_input(INPUT_POST, 'notes');
$commodity_type = filter_input(INPUT_POST, 'commodity_type');
$commodity_value = filter_input(INPUT_POST, 'commodity_value');
$owner_id = filter_input(INPUT_POST, 'owner_id');
$taxable = filter_input(INPUT_POST, 'taxable');
$place_holder = filter_input(INPUT_POST, 'place_holder');

$sql="INSERT INTO `accounts`(`full_name`, `name`, `parent_account_id`, `account_type`, `notes`,`commodity_type`,`commodity_value`,`owner_id`,`taxable`,`place_holder`,`insertion_date_time`) VALUES ('$full_name','$name','$parent_account_id','$account_type','$notes','$commodity_type','$commodity_value','$owner_id','$taxable','$place_holder',CONVERT_TZ(NOW(),'-05:30','+00:00'))";

if (!$con->query($sql)) {
    $arr = array('status' => "1", 'error' => $con->error);
} else {
    $arr = array('status' => "0");
}
echo json_encode($arr);
