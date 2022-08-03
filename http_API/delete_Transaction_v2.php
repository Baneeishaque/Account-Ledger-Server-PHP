<?php

include_once 'config.php';

echo json_encode($con->query("DELETE FROM transactionsv2 WHERE id='" . filter_input(INPUT_POST, 'id') . "'") ? array('status' => "0") : array('status' => "1", 'error' => $con->error));
