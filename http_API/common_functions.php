<?php
/**
 * @param mysqli $con
 * @param string $get_transactions_sql
 * @return void
 */
function executeUserTransactionsSql(mysqli $con, string $get_transactions_sql): void
{
    $get_transactions_result = $con->query($get_transactions_sql);

    $userTransactions = array();

    if (mysqli_num_rows($get_transactions_result) != 0) {

        $status = 0;
        while ($userTransactionRow = mysqli_fetch_assoc($get_transactions_result)) {

            $userTransactions[] = $userTransactionRow;
        }
    } else {

        $status = 1;
    }

    echo json_encode(array("status" => $status, "transactions" => $userTransactions));
}
