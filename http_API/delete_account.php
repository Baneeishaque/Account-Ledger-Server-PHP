<?php

include_once 'config.php';

echo json_encode($con->query("DELETE FROM accounts WHERE account_id='" . filter_input(INPUT_POST, 'account_id') . "'") ? array('status' => "0") : array('status' => "1", 'error' => $con->error));
