# 🚀 Инструкция по запуску проекта

## Быстрый старт (за 5 минут)

### 1. Клонировать репозиторий
```bash
git clone https://github.com/2357184/blog-app.git
cd blog-app
```

### 2. Установить зависимости
```bash
composer install
```

### 3. Инициализировать базу данных
```bash
php config/init_db.php
```

Вы должны увидеть сообщение:
```
Database initialized successfully!
```

### 4. Запустить локальный сервер
```bash
cd public
php -S localhost:8000
```

### 5. Открыть в браузере
```
http://localhost:8000
```

## Тестовые учетные данные

После инициализации БД используйте следующие учетные данные для входа:

- **Логин:** john | **Пароль:** pass123
- **Логин:** jane | **Пароль:** password456
- **Логин:** bob | **Пароль:** secret789

## Требования к системе

- **PHP:** 7.4 или выше
- **MySQL:** 5.7 или выше (должен быть запущен)
- **Composer:** для управления зависимостями

## Проверка установки

### Проверить версию PHP
```bash
php -v
```

### Проверить подключение к MySQL
```bash
mysql -u root -e "SELECT VERSION();"
```

### Проверить Composer
```bash
composer --version
```

## Решение проблем

### Ошибка: "Connection Error: Access denied for user 'root'@'localhost'"

**Решение:** MySQL требует пароль или не запущен.

```bash
# Запустить MySQL (Linux)
sudo service mysql start

# Или для macOS
brew services start mysql

# Или для Windows
net start MySQL80
```

### Ошибка: "Composer command not found"

**Решение:** Установить Composer:

```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

### Ошибка: "Database already initialized"

**Решение:** Если вы хотите пересоздать БД, удалите существующую:

```bash
mysql -u root -e "DROP DATABASE IF EXISTS blog_db;"
php config/init_db.php
```

### Ошибка при входе: "Требуется авторизация"

**Решение:** Убедитесь, что сессии PHP работают правильно. Проверьте, что директория `/tmp` доступна для записи:

```bash
ls -la /tmp | grep php
```

## Структура URL

После запуска сервера на `http://localhost:8000`:

- **Главная страница:** `http://localhost:8000/`
- **API Регистрация:** `http://localhost:8000/../api/register.php`
- **API Авторизация:** `http://localhost:8000/../api/login.php`
- **API Выход:** `http://localhost:8000/../api/logout.php`
- **API Записи:** `http://localhost:8000/../api/posts.php?page=1&per_page=3`
- **API Статус:** `http://localhost:8000/../api/auth_status.php`
- **API Восстановление:** `http://localhost:8000/../api/recover_password.php`

## Использование с Apache

Если вы хотите использовать Apache вместо встроенного сервера PHP:

1. Скопируйте содержимое папки `public` в корень вашего веб-сервера (обычно `/var/www/html`)
2. Убедитесь, что модуль `mod_rewrite` включен:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
3. Файл `.htaccess` уже настроен для правильной маршрутизации

## Использование с Nginx

Если вы используете Nginx, добавьте эту конфигурацию:

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/blog-app/public;
    index index.html;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

## Дополнительные команды

### Просмотр логов MySQL
```bash
tail -f /var/log/mysql/error.log
```

### Просмотр данных в БД
```bash
mysql -u root blog_db
SELECT * FROM users;
SELECT * FROM posts;
```

### Очистка сессий
```bash
rm -rf /tmp/sess_*
```

## Поддержка

Если у вас возникли проблемы, проверьте:

1. Запущена ли MySQL
2. Установлены ли все зависимости Composer
3. Имеет ли PHP доступ к директории `/tmp`
4. Правильно ли установлены права доступа на файлы

## Дополнительная информация

Для более подробной информации о проекте, см. [README.md](README.md)

