<?php

include_once 'config.php';

$status_sql = "SELECT `system_status`, `version_code`, `version_name` FROM `configuration`";
//echo $status_sql;

$status_result = $con->query($status_sql);
$status_row = mysqli_fetch_assoc($status_result);

$out = array();

if ($status_row['system_status'] == "1") {
    $out[] = $status_row;
} else {
    $out[] = array('system_status' => "0");
}

echo json_encode($out);
