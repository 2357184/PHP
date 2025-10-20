<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';

use App\User;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (User::isLoggedIn()) {
        $user = User::getCurrentUser();
        echo json_encode([
            'success' => true,
            'is_logged_in' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'is_logged_in' => false,
            'user' => null
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается.'
    ]);
}

