<?php

include_once 'config.php';

$event_date_time = filter_input(INPUT_POST, 'event_date_time');
$particulars = filter_input(INPUT_POST, 'particulars');
$amount = filter_input(INPUT_POST, 'amount');
$to_account_id = filter_input(INPUT_POST, 'to_account_id');
$from_account_id = filter_input(INPUT_POST, 'from_account_id');
$id = filter_input(INPUT_POST, 'id');

$denomination_columns = array('notes_500', 'notes_200', 'notes_100', 'notes_50', 'notes_20', 'notes_10', 'notes_5', 'notes_2', 'notes_1', 'coins_20', 'coins_10', 'coins_5', 'coins_2', 'coins_1', 'coins_0_5');

$denomination_set_sql = "";
foreach ($denomination_columns as $denomination_column) {

    $denomination_count = filter_input(INPUT_POST, $denomination_column, FILTER_VALIDATE_INT);
    if ($denomination_count !== null) {

        $denomination_set_sql .= ", `$denomination_column`='$denomination_count'";
    }
}

$sql = "UPDATE `transactionsv2` SET `event_date_time`='$event_date_time', `particulars`='$particulars', `amount`='$amount', `insertion_date_time`=CONVERT_TZ(NOW(),'-05:30','+00:00'), `from_account_id`='$from_account_id', `to_account_id`='$to_account_id'$denomination_set_sql WHERE `id`='$id'";

if (!$con->query($sql)) {
    $arr = array('status' => "1", 'error' => $con->error);
} else {
    $arr = array('status' => "0");
}
echo json_encode($arr);
