<?php

include_once 'config.php';

//TODO : Avoid include style code
$selectUsersSqlResult = $con->query("SELECT account_id, full_name, name, parent_account_id, account_type, notes, commodity_type, commodity_value, owner_id, taxable, place_holder, insertion_date_time FROM accounts");

$out = array();

if (mysqli_num_rows($selectUsersSqlResult) != 0) {

    array_push($out, array("status" => "0"));

    while ($status_row = mysqli_fetch_assoc($selectUsersSqlResult)) {

        $out[] = $status_row;
    }
} else {

    array_push($out, array("status" => "1"));
}

echo json_encode($out);
