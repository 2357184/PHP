<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

// Если пользователь авторизован, перенаправить на блог
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

    if (!isset($data['login'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Логин обязателен.'
        ]);
        exit;
    }

    $login = trim($data['login']);

    if (empty($login)) {
        echo json_encode([
            'success' => false,
            'message' => 'Логин не может быть пустым.'
        ]);
        exit;
    }

    $database = new Database();
    $db = $database->connect();
    $user = new User($db);

    $result = $user->getPasswordByLogin($login);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается.'
    ]);
}

