<?php

include_once 'config.php';

$event_date_time = filter_input(INPUT_POST, 'event_date_time');
$member_id = filter_input(INPUT_POST, 'member_id');
$fund_id = filter_input(INPUT_POST, 'fund_id');
$amount = filter_input(INPUT_POST, 'amount');

$sql = "INSERT INTO `transactions`(`event_date_time`, `member_id`, `fund_id`, `amount`,`insertion_date_time`, `inserter`) VALUES ('$event_date_time','1','1','$amount',CONVERT_TZ(NOW(),'-05:30','+00:00'),'0')";

if (!$con->query($sql)) {
    $arr = array('status' => "1", 'error' => $con->error);
} else {
    $arr = array('status' => "0");
}
echo json_encode($arr);
