# Инструкция по запуску проекта

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
### 4. Открыть в браузере
```
http://localhost/blog-app/public/
```

## Тестовые учетные данные

После инициализации БД используйте следующие учетные данные для входа:

- **Логин:** john | **Пароль:** pass123
- **Логин:** jane | **Пароль:** password456
- **Логин:** bob | **Пароль:** secret789

### Требования

- **XAMPP** (с запущенными модулями Apache и MySQL)
- **Git**
- **Composer** (установленный и добавленный в PATH)

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
# для Windows
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
3. Имеет ли PHP доступ к директории 
4. Правильно ли установлены права доступа на файлы

## Дополнительная информация

Для более подробной информации о проекте, см. [README.md](README.md)



