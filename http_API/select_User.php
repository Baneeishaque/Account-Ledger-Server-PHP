<?php

include_once 'config.php';

$username = filter_input(INPUT_GET, 'username') ?? '';
$password = filter_input(INPUT_GET, 'password') ?? '';

$stmt = $con->prepare("SELECT `id` FROM `users` WHERE `username` = ? AND `password` = ? LIMIT 1");
$stmt->bind_param('ss', $username, $password);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'user_count' => $row ? 1 : 0,
    'id'         => $row['id'] ?? null,
]);
