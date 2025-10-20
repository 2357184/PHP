// API базовый URL
const API_BASE_URL = '/blog-app/api';

// Инициализация приложения
document.addEventListener('DOMContentLoaded', function() {
    checkAuthStatus();
    setupEventListeners();
});

// Проверить статус авторизации
function checkAuthStatus() {
    fetch(`${API_BASE_URL}/auth_status.php`)
        .then(response => response.json())
        .then(data => {
            if (data.is_logged_in) {
                showBlogPage();
                loadPosts(1);
            } else {
                showLoginPage();
            }
        })
        .catch(error => {
            console.error('Error checking auth status:', error);
            showLoginPage();
        });
}

// Установить обработчики событий
function setupEventListeners() {
    // Форма входа
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleLogin();
        });
    }

    // Форма регистрации
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleRegister();
        });
    }

    // Форма восстановления пароля
    const recoverForm = document.getElementById('recover-form');
    if (recoverForm) {
        recoverForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleRecover();
        });
    }
}

// Обработка входа
function handleLogin() {
    const username = document.getElementById('login_username').value.trim();
    const password = document.getElementById('login_password').value.trim();
    const errorDiv = document.getElementById('login-error');

    if (!username || !password) {
        showError(errorDiv, 'Пожалуйста, заполните все поля.');
        return;
    }

    fetch(`${API_BASE_URL}/login.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            login: username,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('login-form').reset();
            showBlogPage();
            loadPosts(1);
        } else {
            showError(errorDiv, data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(errorDiv, 'Ошибка при входе. Попробуйте позже.');
    });
}


// Обработка регистрации
function handleRegister() {
    const username = document.getElementById('register_username').value.trim();
    const password = document.getElementById('register_password').value.trim();
    const errorDiv = document.getElementById('register-error');

    if (!username || !password) {
        showError(errorDiv, 'Пожалуйста, заполните все поля.');
        return;
    }

    fetch(`${API_BASE_URL}/register.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            login: username,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('register-form').reset();
            showLoginPage();
        } else {
            showError(errorDiv, data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(errorDiv, 'Ошибка при регистрации. Попробуйте позже.');
    });
}

// Обработка восстановления пароля
function handleRecover() {
    const username = document.getElementById('recover_username').value.trim();
    const errorDiv = document.getElementById('recover-error');
    const successDiv = document.getElementById('recover-success');

    if (!username) {
        showError(errorDiv, 'Пожалуйста, введите логин.');
        return;
    }

    fetch(`${API_BASE_URL}/recover_password.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            login: username
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successDiv.style.display = 'block';
            errorDiv.style.display = 'none';
            successDiv.innerHTML = `<strong>Логин:</strong> ${data.login}<br><strong>Пароль:</strong> ${data.password}`;
        } else {
            showError(errorDiv, data.message);
            successDiv.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(errorDiv, 'Ошибка при восстановлении пароля. Попробуйте позже.');
    });
}

// Загрузить записи блога
function loadPosts(page = 1) {
    const postsPerPage = document.getElementById('posts_per_page').value || 3;

    fetch(`${API_BASE_URL}/posts.php?page=${page}&per_page=${postsPerPage}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPosts(data.posts, data.pagination);
            } else {
                document.getElementById('posts-container').innerHTML = `
                    <div class="error-message">${data.message}</div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('posts-container').innerHTML = `
                <div class="error-message">Ошибка при загрузке записей. Попробуйте позже.</div>
            `;
        });
}

// Отобразить записи
function displayPosts(posts, pagination) {
    const container = document.getElementById('posts-container');
    container.innerHTML = '';

    if (posts.length === 0) {
        container.innerHTML = '<div class="card"><div class="card-content"><p>Нет записей для отображения.</p></div></div>';
        document.getElementById('pagination-container').innerHTML = '';
        return;
    }

    posts.forEach(post => {
        const postDate = new Date(post.created_at).toLocaleDateString('ru-RU', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const postHTML = `
            <div class="post-card">
                <div class="post-header">
                    <span class="post-author">👤 ${post.author}</span>
                    <span class="post-date">${postDate}</span>
                </div>
                <div class="post-content">${escapeHtml(post.content)}</div>
            </div>
        `;
        container.innerHTML += postHTML;
    });

    // Отобразить пагинацию
    displayPagination(pagination);
}

// Отобразить пагинацию
function displayPagination(pagination) {
    const container = document.getElementById('pagination-container');
    container.innerHTML = '';

    if (pagination.total_pages <= 1) {
        return;
    }

    let paginationHTML = '<div class="pagination">';

    // Кнопка "Предыдущая"
    if (pagination.current_page > 1) {
        paginationHTML += `<a href="#" onclick="loadPosts(${pagination.current_page - 1}); return false;">← Предыдущая</a>`;
    } else {
        paginationHTML += '<span class="disabled">← Предыдущая</span>';
    }

    // Номера страниц
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === pagination.current_page) {
            paginationHTML += `<span class="active">${i}</span>`;
        } else {
            paginationHTML += `<a href="#" onclick="loadPosts(${i}); return false;">${i}</a>`;
        }
    }

    // Кнопка "Следующая"
    if (pagination.current_page < pagination.total_pages) {
        paginationHTML += `<a href="#" onclick="loadPosts(${pagination.current_page + 1}); return false;">Следующая →</a>`;
    } else {
        paginationHTML += '<span class="disabled">Следующая →</span>';
    }

    paginationHTML += '</div>';
    container.innerHTML = paginationHTML;
}

// Выход
function logout() {
    fetch(`${API_BASE_URL}/logout.php`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showLoginPage();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Показать страницу входа
function showLoginPage() {
    document.getElementById('login-page').style.display = 'block';
    document.getElementById('register-page').style.display = 'none';
    document.getElementById('recover-page').style.display = 'none';
    document.getElementById('blog-page').style.display = 'none';

    // Обновить навигацию
    document.getElementById('nav-login-link').style.display = 'block';
    document.getElementById('nav-register-link').style.display = 'block';
    document.getElementById('nav-blog-link').style.display = 'none';
    document.getElementById('nav-logout-link').style.display = 'none';
}

// Показать страницу регистрации
function showRegister() {
    document.getElementById('login-page').style.display = 'none';
    document.getElementById('register-page').style.display = 'block';
    document.getElementById('recover-page').style.display = 'none';
    document.getElementById('blog-page').style.display = 'none';

    // Обновить навигацию
    document.getElementById('nav-login-link').style.display = 'block';
    document.getElementById('nav-register-link').style.display = 'block';
    document.getElementById('nav-blog-link').style.display = 'none';
    document.getElementById('nav-logout-link').style.display = 'none';
}

// Показать страницу восстановления пароля
function showRecover() {
    document.getElementById('login-page').style.display = 'none';
    document.getElementById('register-page').style.display = 'none';
    document.getElementById('recover-page').style.display = 'block';
    document.getElementById('blog-page').style.display = 'none';

    // Обновить навигацию
    document.getElementById('nav-login-link').style.display = 'block';
    document.getElementById('nav-register-link').style.display = 'block';
    document.getElementById('nav-blog-link').style.display = 'none';
    document.getElementById('nav-logout-link').style.display = 'none';
}

// Показать страницу блога
function showBlogPage() {
    document.getElementById('login-page').style.display = 'none';
    document.getElementById('register-page').style.display = 'none';
    document.getElementById('recover-page').style.display = 'none';
    document.getElementById('blog-page').style.display = 'block';

    // Обновить навигацию
    document.getElementById('nav-login-link').style.display = 'none';
    document.getElementById('nav-register-link').style.display = 'none';
    document.getElementById('nav-blog-link').style.display = 'block';
    document.getElementById('nav-logout-link').style.display = 'block';
}

// Показать ошибку
function showError(element, message) {
    element.innerHTML = message;
    element.style.display = 'block';
}

// Экранировать HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

