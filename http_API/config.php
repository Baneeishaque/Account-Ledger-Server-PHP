<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

//$con = new mysqli("localhost", "root", "", "account_ledger");

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
$con = new mysqli($url["host"], $url["user"], $url["pass"], substr($url["path"], 1));
