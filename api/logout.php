<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';

use App\User;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User(null);
    $result = $user->logout();
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается.'
    ]);
}

