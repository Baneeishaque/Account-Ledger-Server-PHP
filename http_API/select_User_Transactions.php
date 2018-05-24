<?php

include_once 'config.php';

$user_id = filter_input(INPUT_POST, 'user_id');
$status_sql = "SELECT `id`,`event_date_time`, `particulars`, `amount`, `insertion_date_time`  FROM `transactions` WHERE `inserter_id`='$user_id' ORDER BY `transactions`.`event_date_time`";
$status_result = $con->query($status_sql);

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
