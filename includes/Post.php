<?php

namespace App;

class Post
{
    private $conn;
    private $table = 'posts';
    private $posts_per_page = 3; // Переменная для количества записей на странице

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Установить количество записей на странице
     */
    public function setPostsPerPage($count)
    {
        $this->posts_per_page = (int)$count;
    }

    /**
     * Получить количество записей на странице
     */
    public function getPostsPerPage()
    {
        return $this->posts_per_page;
    }

    /**
     * Получить записи с пагинацией
     */
    public function getPosts($page = 1)
    {
        $page = (int)$page;
        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $this->posts_per_page;

        // Получить общее количество записей
        $count_sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $count_result = $this->conn->query($count_sql);
        $count_row = $count_result->fetch_assoc();
        $total_posts = $count_row['total'];

        // Вычислить общее количество страниц
        $total_pages = ceil($total_posts / $this->posts_per_page);

        // Получить записи для текущей страницы
        $sql = "SELECT p.id, p.user_id, p.title, p.content, p.created_at, u.login as author 
                FROM {$this->table} p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $this->posts_per_page, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        return [
            'success' => true,
            'posts' => $posts,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_posts' => $total_posts,
                'posts_per_page' => $this->posts_per_page
            ]
        ];
    }

    /**
     * Создать новую запись
     */
    public function createPost($user_id, $content, $title = 'Запись')
    {
        $sql = "INSERT INTO {$this->table} (user_id, title, content) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $title, $content);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Запись успешно создана.',
                'post_id' => $this->conn->insert_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при создании записи.'
            ];
        }
    }
}

