<?php

include_once 'config.php';

$event_date_time = filter_input(INPUT_POST, 'event_date_time');
$user_id = filter_input(INPUT_POST, 'user_id');
$particulars = filter_input(INPUT_POST, 'particulars');
$amount = filter_input(INPUT_POST, 'amount');

$sql = "INSERT INTO `transactions`(`event_date_time`, `particulars`, `amount`, `insertion_date_time`, `inserter_id`) VALUES ('$event_date_time','$particulars','$amount',CONVERT_TZ(NOW(),'-05:30','+00:00'),'$user_id')";

if (!$con->query($sql)) {
    $arr = array('status' => "1", 'error' => $con->error);
} else {
    $arr = array('status' => "0");
}
echo json_encode($arr);
