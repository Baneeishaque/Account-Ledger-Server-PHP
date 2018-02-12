<?php

include_once 'config.php';

$status_sql = "SELECT `system_status`, `version_code`, `version_name` FROM `configuration`";
$status_result = $con->query($status_sql);
$status_row = mysqli_fetch_assoc($status_result);

$emptyarray = array();

if ($status_row['system_status'] == "1") {
    $emptyarray[] = $status_row;
} else {
    $emptyarray[] = array('system_status' => "0");
}

echo json_encode($emptyarray);
//echo $status_sql;


