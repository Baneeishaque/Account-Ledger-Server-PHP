<?php

include_once 'config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$account_id        = filter_input(INPUT_POST, 'account_id', FILTER_VALIDATE_INT);
$full_name         = filter_input(INPUT_POST, 'full_name')         ?? '';
$name              = filter_input(INPUT_POST, 'name')              ?? '';
$parent_account_id = filter_input(INPUT_POST, 'parent_account_id', FILTER_VALIDATE_INT);
$account_type      = filter_input(INPUT_POST, 'account_type')      ?? '';
$notes             = filter_input(INPUT_POST, 'notes')             ?? '';
$commodity_type    = filter_input(INPUT_POST, 'commodity_type')    ?? '';
$commodity_value   = filter_input(INPUT_POST, 'commodity_value')   ?? '';
$taxable           = filter_input(INPUT_POST, 'taxable')           ?? '';
$place_holder      = filter_input(INPUT_POST, 'place_holder')      ?? '';

if ($account_id === false || $account_id === null) {
    echo json_encode(['status' => '1', 'error' => 'invalid or missing account_id']);
    return;
}

// Self-Parent Guard (app-layer; defense-in-depth with DB triggers
// trg_accounts_no_self_parent_ins / _upd installed on the accounts table).
if ($parent_account_id !== null && $parent_account_id !== false
    && (int)$parent_account_id === (int)$account_id) {
    echo json_encode([
        'status' => '1',
        'error'  => 'parent_account_id must not equal account_id (self-reference forbidden)',
    ]);
    return;
}

$con->begin_transaction();
try {
    $stmt = $con->prepare(
        "UPDATE `accounts` SET "
        . "`full_name` = ?, `name` = ?, `parent_account_id` = ?, "
        . "`account_type` = ?, `notes` = ?, `commodity_type` = ?, "
        . "`commodity_value` = ?, `taxable` = ?, `place_holder` = ? "
        . "WHERE `account_id` = ?"
    );
    $stmt->bind_param(
        'ssissssssi',
        $full_name, $name, $parent_account_id,
        $account_type, $notes, $commodity_type,
        $commodity_value, $taxable, $place_holder,
        $account_id
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $con->commit();
    echo json_encode(['status' => '0', 'affected_rows' => $affected]);
} catch (mysqli_sql_exception $e) {
    $con->rollback();
    echo json_encode(['status' => '1', 'error' => $e->getMessage()]);
}
