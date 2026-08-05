<?php

include_once 'config.php';

$event_date_time = filter_input(INPUT_POST, 'event_date_time');
$user_id = filter_input(INPUT_POST, 'user_id');
$particulars = filter_input(INPUT_POST, 'particulars');
$amount = filter_input(INPUT_POST, 'amount');
$from_account_id = filter_input(INPUT_POST, 'from_account_id');
$to_account_id = filter_input(INPUT_POST, 'to_account_id');

$denomination_columns = array('notes_500', 'notes_200', 'notes_100', 'notes_50', 'notes_20', 'notes_10', 'notes_5', 'notes_2', 'notes_1', 'coins_20', 'coins_10', 'coins_5', 'coins_2', 'coins_1', 'coins_0_5');

$denomination_column_sql = "";
$denomination_value_sql = "";
foreach ($denomination_columns as $denomination_column) {

    $denomination_count = filter_input(INPUT_POST, $denomination_column, FILTER_VALIDATE_INT);
    $denomination_column_sql .= "`$denomination_column`, ";
    $denomination_value_sql .= $denomination_count === null ? "NULL, " : "'$denomination_count', ";
}
$denomination_column_sql = rtrim($denomination_column_sql, ', ');
$denomination_value_sql = rtrim($denomination_value_sql, ', ');

$sql = "INSERT INTO `transactionsv2`(`event_date_time`, `particulars`, `amount`, `insertion_date_time`, `inserter_id`,`from_account_id`,`to_account_id`, $denomination_column_sql) VALUES ('$event_date_time','$particulars','$amount',CONVERT_TZ(NOW(),'-05:30','+00:00'),'$user_id','$from_account_id','$to_account_id', $denomination_value_sql)";

if (!$con->query($sql)) {
    $arr = array('status' => "1", 'error' => $con->error);
} else {
    $arr = array('status' => "0", 'error' => "");
}
echo json_encode($arr);
