<?php
include 'includes/config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Добавление комментария
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postId = $_POST['post_id'] ?? null;
    $comment = trim($_POST['comment'] ?? '');

    if ($postId && !empty($comment)) {
        // Проверяем, существует ли пост
        $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        
        if ($stmt->rowCount() == 0) {
            // Пост не найден - перенаправляем с сообщением об ошибке
            header("Location: index.php?error=post_not_found");
            exit();
        }

        // Добавляем комментарий
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $_SESSION['user_id'], $comment]);
    }
}

// Успешное добавление комментария
header("Location: index.php?success=comment_added");
exit();