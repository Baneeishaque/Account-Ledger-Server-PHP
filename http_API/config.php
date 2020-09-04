<?php
ini_set('display_errors', '1');

//$con = new mysqli("localhost", "edcccet_ndk", "9895204814", "edcccet_lottery");
//$con = new mysqli("localhost", "vfmobo6d_ndk", "9895204814", "vfmobo6d_account_ledger");
//$con = new mysqli("localhost", "root", "aA!9895204814", "account_ledger");
//$con = new mysqli("fdb20.awardspace.net", "3140855_account", "aA!9895204814", "3140855_account");
//$con = new mysqli("sql205.byethost.com", "b14_24405831", "w5qUz@F7WgaPNqF", "b14_24405831_account_ledger");
//$con = new mysqli("localhost", "root", "", "account_ledger");

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
//$server = $url["host"];
//$username = $url["user"];
//$password = $url["pass"];
//$db = substr($url["path"], 1);
$con = new mysqli($url["host"], $url["user"], $url["pass"], substr($url["path"], 1));
