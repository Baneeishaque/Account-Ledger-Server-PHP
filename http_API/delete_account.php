<?php

include_once 'config.php';

$account_id = (int) filter_input(INPUT_POST, 'account_id');

$sql = "SELECT
            EXISTS(SELECT 1 FROM accounts WHERE account_id=$account_id),
            EXISTS(SELECT 1 FROM accounts WHERE parent_account_id=$account_id),
            EXISTS(SELECT 1 FROM transactionsv2 WHERE from_account_id=$account_id)
                OR EXISTS(SELECT 1 FROM transactionsv2 WHERE to_account_id=$account_id)
        INTO @ae, @hc, @ht;
        DELETE FROM accounts WHERE account_id=$account_id AND @hc=0 AND @ht=0;
        SELECT @ae AS account_exists, @hc AS has_children, @ht AS has_transactions;";

if (!$con->multi_query($sql)) {
    echo json_encode(array('status' => "1", 'error' => $con->error));
    return;
}

$row = null;
do {
    if ($res = $con->store_result()) {
        $row = $res->fetch_assoc();
        $res->free();
    }
} while ($con->more_results() && $con->next_result());

if ($row === null) {
    echo json_encode(array('status' => "1", 'error' => "No result returned."));
    return;
}

if ((int) $row['account_exists'] === 0) {
    echo json_encode(array('status' => "1", 'error' => "Account not found."));
    return;
}
if ((int) $row['has_children'] === 0 && (int) $row['has_transactions'] === 0) {
    echo json_encode(array('status' => "0"));
    return;
}

$reasons = array();
if ((int) $row['has_children'] === 1)     { $reasons[] = "child account(s)"; }
if ((int) $row['has_transactions'] === 1) { $reasons[] = "transaction(s)"; }
echo json_encode(array('status' => "1", 'error' => "Account cannot be deleted : referenced by " . implode(" and ", $reasons) . "."));
