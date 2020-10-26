<?php

include_once 'config.php';

$selectUsersSqlResult = $con->query("SELECT `id`,`username`,`password` FROM users");

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
