<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

// Если пользователь уже авторизован, перенаправить на блог
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Вы уже авторизованы. Перейдите на страницу блога.'
    ]);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\User;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['login']) || !isset($data['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Логин и пароль обязательны.'
        ]);
        exit;
    }

    $login = trim($data['login']);
    $password = trim($data['password']);

    if (empty($login) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Логин и пароль не могут быть пустыми.'
        ]);
        exit;
    }

    if (strlen($login) < 3) {
        echo json_encode([
            'success' => false,
            'message' => 'Логин должен содержать минимум 3 символа.'
        ]);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Пароль должен содержать минимум 6 символов.'
        ]);
        exit;
    }

    $database = new Database();
    $db = $database->connect();
    $user = new User($db);

    $result = $user->register($login, $password);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается.'
    ]);
}

