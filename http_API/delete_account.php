<?php

include_once 'config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$account_id = filter_input(INPUT_POST, 'account_id', FILTER_VALIDATE_INT);
if ($account_id === false || $account_id === null) {
    echo json_encode(array('status' => "1", 'error' => "Invalid or missing account_id."));
    return;
}

// MariaDB compound block (BEGIN NOT ATOMIC + EXIT HANDLER FOR 1451):
//   Happy path : 1 DELETE only.
//   Row absent : 1 no-op DELETE.
//   FK trips   : handler runs the diagnostic EXISTS pair and returns
//                a 'blocked' resultset with both blocker flags set.
// All paths cost ONE network round-trip and ONE resultset.
$sql = "BEGIN NOT ATOMIC
            DECLARE EXIT HANDLER FOR 1451
                SELECT 'blocked' AS outcome,
                       EXISTS(SELECT 1 FROM accounts
                              WHERE parent_account_id=$account_id) AS has_children,
                       EXISTS(SELECT 1 FROM transactionsv2
                              WHERE from_account_id=$account_id
                                 OR to_account_id=$account_id)     AS has_transactions,
                       0 AS affected_rows;
            DELETE FROM accounts WHERE account_id=$account_id;
            SELECT CASE WHEN ROW_COUNT()=1 THEN 'deleted' ELSE 'not_found' END AS outcome,
                   0 AS has_children,
                   0 AS has_transactions,
                   ROW_COUNT() AS affected_rows;
        END";

try {
    $res = $con->query($sql);
} catch (mysqli_sql_exception $e) {
    echo json_encode(array('status' => "1", 'error' => $e->getMessage()));
    return;
}

$row = $res ? $res->fetch_assoc() : null;
if ($res) { $res->free(); }
if ($row === null) {
    echo json_encode(array('status' => "1", 'error' => "No result returned."));
    return;
}

switch ($row['outcome']) {
    case 'deleted':
        echo json_encode(array('status' => "0", 'affected_rows' => (int) $row['affected_rows']));
        return;
    case 'not_found':
        echo json_encode(array('status' => "1", 'error' => "Account not found."));
        return;
    case 'blocked':
        $reasons = array();
        if ((int) $row['has_children']     === 1) { $reasons[] = "child accounts"; }
        if ((int) $row['has_transactions'] === 1) { $reasons[] = "transactions"; }
        echo json_encode(array('status' => "1",
                               'error'  => "Account cannot be deleted : has " . implode(" and ", $reasons) . "."));
        return;
    default:
        echo json_encode(array('status' => "1", 'error' => "Unexpected outcome: " . $row['outcome']));
        return;
}
