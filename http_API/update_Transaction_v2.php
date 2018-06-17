<?php

include_once 'config.php';

$event_date_time = filter_input(INPUT_POST, 'event_date_time');
$particulars = filter_input(INPUT_POST, 'particulars');
$amount = filter_input(INPUT_POST, 'amount');
$to_account_id = filter_input(INPUT_POST, 'to_account_id');
$from_account_id = filter_input(INPUT_POST, 'from_account_id');
$id = filter_input(INPUT_POST, 'id');

$sql = "UPDATE `transactionsv2` SET `event_date_time`='$event_date_time', `particulars`='$particulars', `amount`='$amount', `insertion_date_time`=CONVERT_TZ(NOW(),'-05:30','+00:00'), `from_account_id`='$from_account_id', `to_account_id`='$to_account_id' WHERE `id`='$id'";

if (!$con->query($sql)) {
    $arr = array('status' => "1", 'error' => $con->error);
} else {
    $arr = array('status' => "0");
}
echo json_encode($arr);
