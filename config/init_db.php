<?php

// Подключение к MySQL без выбора БД
$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    die('Connection Error: ' . $conn->connect_error);
}

// Создание базы данных
$sql = "CREATE DATABASE IF NOT EXISTS blog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql)) {
    die('Error creating database: ' . $conn->error);
}

// Выбор базы данных
$conn->select_db('blog_db');

// Создание таблицы users с обратимым шифрованием паролей
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(255) NOT NULL UNIQUE,
    password_encrypted VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($sql)) {
    die('Error creating users table: ' . $conn->error);
}

// Создание таблицы posts
$sql = "CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'Запись',
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($sql)) {
    die('Error creating posts table: ' . $conn->error);
}

// Вставка тестовых пользователей
$cipher = 'AES-128-CBC';
$key = hash('sha256', 'secret_key_for_encryption', true);
$key = substr($key, 0, 16);

// Пользователь 1: login=john, password=pass123
$iv1 = openssl_random_pseudo_bytes(16);
$encrypted1 = openssl_encrypt('pass123', $cipher, $key, OPENSSL_RAW_DATA, $iv1);
$password_encrypted1 = base64_encode($iv1 . $encrypted1);

// Пользователь 2: login=jane, password=password456
$iv2 = openssl_random_pseudo_bytes(16);
$encrypted2 = openssl_encrypt('password456', $cipher, $key, OPENSSL_RAW_DATA, $iv2);
$password_encrypted2 = base64_encode($iv2 . $encrypted2);

// Пользователь 3: login=bob, password=secret789
$iv3 = openssl_random_pseudo_bytes(16);
$encrypted3 = openssl_encrypt('secret789', $cipher, $key, OPENSSL_RAW_DATA, $iv3);
$password_encrypted3 = base64_encode($iv3 . $encrypted3);

// Проверка существования пользователей перед вставкой
$check_sql = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Вставка пользователей
    $sql = "INSERT INTO users (login, password_encrypted) VALUES 
            ('john', '$password_encrypted1'),
            ('jane', '$password_encrypted2'),
            ('bob', '$password_encrypted3')";
    
    if (!$conn->query($sql)) {
        die('Error inserting users: ' . $conn->error);
    }

    // Получение ID пользователей
    $result = $conn->query("SELECT id, login FROM users");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[$row['login']] = $row['id'];
    }

    // Вставка тестовых записей
    $posts_data = [
        ['user_id' => $users['john'], 'content' => 'Это моя первая запись в блоге. Я очень рад поделиться своими мыслями с вами!'],
        ['user_id' => $users['john'], 'content' => 'Вторая запись от John. Сегодня был прекрасный день для программирования.'],
        ['user_id' => $users['jane'], 'content' => 'Привет всем! Это моя первая запись. Надеюсь вам понравится мой блог.'],
        ['user_id' => $users['bob'], 'content' => 'Bob здесь. Делюсь интересными идеями о веб-разработке.'],
        ['user_id' => $users['jane'], 'content' => 'Вторая запись от Jane. Обновления о моих проектах.'],
        ['user_id' => $users['bob'], 'content' => 'Еще одна запись от Bob. Сегодня я узнал что-то новое!'],
    ];

    foreach ($posts_data as $post) {
        $sql = "INSERT INTO posts (user_id, content) VALUES ({$post['user_id']}, '{$conn->real_escape_string($post['content'])}')";
        if (!$conn->query($sql)) {
            die('Error inserting posts: ' . $conn->error);
        }
    }

    echo "Database initialized successfully!";
} else {
    echo "Database already initialized!";
}

$conn->close();

