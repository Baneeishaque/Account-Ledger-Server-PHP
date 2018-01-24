<?php

include_once 'config.php';

$name = filter_input(INPUT_POST, 'name');
$department = filter_input(INPUT_POST, 'department');
$semester = filter_input(INPUT_POST, 'semester');
$mobile_number = filter_input(INPUT_POST, 'mobile_number');
$gender = filter_input(INPUT_POST, 'gender');
$age = filter_input(INPUT_POST, 'age');

$sql = "INSERT INTO `members`(`name`, `department`, `semester`, `mobile_number`, `gender`, `age`, `insertion_date_time`, `inserter`) VALUES ('$name','$department','$semester','$mobile_number','$gender','$age',CONVERT_TZ(NOW(),'-05:30','+00:00'),'0')";

if (!$con->query($sql)) {
    $arr = array('status' => "1", 'error' => $con->error);
} else {
    $arr = array('status' => "0");
}
echo json_encode($arr);
