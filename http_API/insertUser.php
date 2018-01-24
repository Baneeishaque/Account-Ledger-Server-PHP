<?php

include_once 'config.php';

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'passcode');

$sql = "SELECT `username` FROM `users` WHERE username='$username'";

$result = $con->query($sql);

if (mysqli_num_rows($result) == 0) {

    $sql = "INSERT INTO `users`(`username`, `password`) VALUES ('$username','$password')";

    if (!$con->query($sql)) {

        $arr = array('status' => "1", 'error' => $con->error);

    } else {

        $sql = "INSERT INTO `accounts`(`full_name`, `name`, `parent_account_id`, `account_type`, `notes`,`commodity_type`,`commodity_value`,`owner_id`,`taxable`,`place_holder`,`insertion_date_time`) VALUES ('Assets', 'Assets', 0, 'ASSET', '', 'CURRENCY', 'INR', $con->insert_id, 'F', 'T', CONVERT_TZ(NOW(),'-05:30','+00:00')),('Equity', 'Equity', 0, 'EQUITY', '', 'CURRENCY', 'INR', $con->insert_id, 'F', 'T', CONVERT_TZ(NOW(),'-05:30','+00:00')),('Expenses', 'Expenses', 0, 'EXPENSE', '', 'CURRENCY', 'INR', $con->insert_id, 'F', 'T', CONVERT_TZ(NOW(),'-05:30','+00:00')),('Income', 'Income', 0, 'INCOME', '', 'CURRENCY', 'INR', $con->insert_id, 'F', 'T', CONVERT_TZ(NOW(),'-05:30','+00:00')),('Liabilities', 'Liabilities', 0, 'LIABILITY', '', 'CURRENCY', 'INR', $con->insert_id, 'F', 'T', CONVERT_TZ(NOW(),'-05:30','+00:00'))";

//        echo $sql;

        if (!$con->query($sql)) {

            $arr = array('status' => "1", 'error' => $con->error);

        } else {

            $arr = array('status' => "0");
        }

    }

} else {

    $arr = array('status' => "1", 'error' => 'Username already exists...');
}

echo json_encode($arr);
