<?php

include_once 'config.php';

//TODO : Avoid include style code
$selectUsersSqlResult = $con->query("SELECT `id`,`username`,`password` FROM users");

$users = array();

if (mysqli_num_rows($selectUsersSqlResult) != 0) {

    $status = 0;
    while ($status_row = mysqli_fetch_assoc($selectUsersSqlResult)) {

        $users[] = $status_row;
    }
} else {

    $status = 1;
}

echo json_encode(array("status" => $status, "users" => $users));
