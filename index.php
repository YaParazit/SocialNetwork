<?php
include 'includes/config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Добавление нового поста
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $content]);
        $success = "Пост успешно опубликован!";
    }
}

// Получение постов с данными пользователей
$stmt = $pdo->prepare("SELECT posts.*, users.username, users.avatar 
                      FROM posts 
                      JOIN users ON posts.user_id = users.id 
                      ORDER BY posts.created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лента новостей</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Мини-Соцсеть</h1>
            <p>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <button class="theme-toggle" id="theme-toggle">Темная тема</button>
            <a href="logout.php" class="logout-button">Выйти</a>
        </header>
        
        <?php 
        // Отображение сообщений об ошибках и успехе
        if (isset($_GET['error']) && $_GET['error'] == 'post_not_found') {
            echo '<div class="error">Ошибка: пост не найден или был удален.</div>';
        }
        if (isset($_GET['success']) && $_GET['success'] == 'comment_added') {
            echo '<div class="success">Комментарий успешно добавлен!</div>';
        }
        if (isset($success)) {
            echo '<div class="success">' . $success . '</div>';
        }
        ?>
        
        <!-- Форма для нового поста -->
        <form method="POST">
            <div class="input-group">
                <textarea name="content" placeholder="Что у вас нового?" required></textarea>
            </div>
            <button type="submit">Опубликовать</button>
        </form>
        
        <!-- Список постов -->
        <?php if (empty($posts)): ?>
            <p>Пока нет ни одного поста. Будьте первым!</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <img src="<?php echo htmlspecialchars($post['avatar']); ?>" alt="Аватар" class="avatar">
                        <div>
                            <div class="post-user"><?php echo htmlspecialchars($post['username']); ?></div>
                            <div class="post-date"><?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                    
                    <!-- Комментарии к посту -->
                    <?php
                    $stmt = $pdo->prepare("SELECT comments.*, users.username, users.avatar 
                                          FROM comments 
                                          JOIN users ON comments.user_id = users.id 
                                          WHERE comments.post_id = ? 
                                          ORDER BY comments.created_at ASC");
                    $stmt->execute([$post['id']]);
                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    
                    <div class="comments">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <img src="<?php echo htmlspecialchars($comment['avatar']); ?>" alt="Аватар" class="comment-avatar">
                                <div class="comment-content">
                                    <span class="comment-user"><?php echo htmlspecialchars($comment['username']); ?></span>: 
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                    <span class="comment-date"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Форма добавления комментария -->
                    <form method="POST" action="add_comment.php" class="comment-form">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <div class="input-group">
                            <textarea name="comment" placeholder="Напишите комментарий..." required></textarea>
                        </div>
                        <button type="submit">Комментировать</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            
            // Проверяем, существует ли кнопка
            if (!themeToggle) {
                console.error('Кнопка переключения темы не найдена');
                return;
            }
            
            // Определяем текущую тему
            const currentTheme = localStorage.getItem('theme');
            
            // Устанавливаем тему при загрузке
            if (currentTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                themeToggle.textContent = 'Светлая тема';
            } else {
                document.documentElement.removeAttribute('data-theme');
                themeToggle.textContent = 'Темная тема';
            }
            
            // Переключение темы
            themeToggle.addEventListener('click', function() {
                if (document.documentElement.hasAttribute('data-theme')) {
                    // Сейчас темная тема, переключаем на светлую
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.removeItem('theme');
                    themeToggle.textContent = 'Темная тема';
                } else {
                    // Сейчас светлая тема, переключаем на темную
                    document.documentElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    themeToggle.textContent = 'Светлая тема';
                }
            });
        });
    </script>
</body>
</html>