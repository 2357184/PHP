<?php

namespace App;

class User
{
    private $conn;
    private $table = 'users';
    private $cipher = 'AES-128-CBC';
    private $key;

    public function __construct($db)
    {
        $this->conn = $db;
        // Генерируем ключ на основе секретного значения
        $this->key = hash('sha256', 'secret_key_for_encryption', true);
        $this->key = substr($this->key, 0, 16);
    }

    /**
     * Зарегистрировать нового пользователя
     */
    public function register($login, $password)
    {
        // Проверка на существование логина
        $check_sql = "SELECT id FROM {$this->table} WHERE login = ?";
        $stmt = $this->conn->prepare($check_sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return [
                'success' => false,
                'message' => 'Логин уже занят. Пожалуйста, выберите другой логин.'
            ];
        }

        // Шифрование пароля
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($password, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);
        $password_encrypted = base64_encode($iv . $encrypted);

        // Вставка пользователя в БД
        $sql = "INSERT INTO {$this->table} (login, password_encrypted) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $login, $password_encrypted);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Регистрация успешна! Теперь вы можете авторизоваться.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при регистрации. Попробуйте позже.'
            ];
        }
    }

    /**
     * Авторизовать пользователя
     */
    public function login($login, $password)
    {
        $sql = "SELECT id, login, password_encrypted FROM {$this->table} WHERE login = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return [
                'success' => false,
                'message' => 'Неверный логин или пароль. Пожалуйста, попробуйте еще раз.'
            ];
        }

        $user = $result->fetch_assoc();

        // Расшифровка пароля
        $encrypted_data = base64_decode($user['password_encrypted']);
        $iv = substr($encrypted_data, 0, 16);
        $encrypted = substr($encrypted_data, 16);
        $decrypted_password = openssl_decrypt($encrypted, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted_password === $password) {
            // Успешная авторизация
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $user['login'];

            return [
                'success' => true,
                'message' => 'Авторизация успешна!',
                'user_id' => $user['id'],
                'login' => $user['login']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Неверный логин или пароль. Пожалуйста, попробуйте еще раз.'
            ];
        }
    }

    /**
     * Выход (деавторизация)
     */
    public function logout()
    {
        session_destroy();
        return [
            'success' => true,
            'message' => 'Вы успешно вышли из системы.'
        ];
    }

    /**
     * Получить пароль по логину (для "задания со звездочкой")
     */
    public function getPasswordByLogin($login)
    {
        $sql = "SELECT password_encrypted FROM {$this->table} WHERE login = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return [
                'success' => false,
                'message' => 'Пользователь с таким логином не найден.'
            ];
        }

        $user = $result->fetch_assoc();

        // Расшифровка пароля
        $encrypted_data = base64_decode($user['password_encrypted']);
        $iv = substr($encrypted_data, 0, 16);
        $encrypted = substr($encrypted_data, 16);
        $decrypted_password = openssl_decrypt($encrypted, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);

        return [
            'success' => true,
            'login' => $login,
            'password' => $decrypted_password
        ];
    }

    /**
     * Проверить, авторизован ли пользователь
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['login']);
    }

    /**
     * Получить текущего пользователя
     */
    public static function getCurrentUser()
    {
        if (self::isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'login' => $_SESSION['login']
            ];
        }
        return null;
    }
}

