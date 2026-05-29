<?php

include_once 'config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    echo json_encode(['status' => '1', 'error' => 'invalid or missing id']);
    return;
}

$con->begin_transaction();
try {
    $stmt = $con->prepare("DELETE FROM `transactionsv2` WHERE `id` = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $con->commit();
    echo json_encode(['status' => '0', 'affected_rows' => $affected]);
} catch (mysqli_sql_exception $e) {
    $con->rollback();
    echo json_encode(['status' => '1', 'error' => $e->getMessage()]);
}
