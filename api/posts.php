<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Post;
use App\User;

// Проверить авторизацию
if (!User::isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Требуется авторизация для просмотра записей.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $database = new Database();
    $db = $database->connect();
    $post = new Post($db);

    // Получить количество записей на странице из параметра запроса (по умолчанию 3)
    $posts_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 3;
    $posts_per_page = max(1, min($posts_per_page, 100)); // Ограничить от 1 до 100

    $post->setPostsPerPage($posts_per_page);

    // Получить номер страницы
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    $result = $post->getPosts($page);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается.'
    ]);
}

