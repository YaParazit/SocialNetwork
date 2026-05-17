<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Валидация данных
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Все поля обязательны для заполнения.";
    } elseif ($password !== $confirm_password) {
        $error = "Пароли не совпадают.";
    } else {
        // Проверка уникальности имени пользователя
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Пользователь с таким именем уже существует.";
        } else {
            // Обработка аватарки
            $avatarPath = 'uploads/default_avatar.png';
            
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png'];
                $fileType = mime_content_type($_FILES['avatar']['tmp_name']);
                
                if (in_array($fileType, $allowedTypes)) {
                    $fileName = 'avatar_' . time() . '_' . basename($_FILES['avatar']['name']);
                    $uploadDir = 'uploads/';
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filePath)) {
                        $avatarPath = $filePath;
                    } else {
                        $error = "Ошибка при загрузке аватарки.";
                    }
                } else {
                    $error = "Разрешены только JPEG и PNG изображения.";
                }
            }
            
            if (!isset($error)) {
                // Хеширование пароля
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Сохранение пользователя в БД
                $stmt = $pdo->prepare("INSERT INTO users (username, password, avatar) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $hashedPassword, $avatarPath])) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Ошибка регистрации. Попробуйте позже.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Мини-Соцсеть</h1>
            <h2>Регистрация нового пользователя</h2>
        </header>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="input-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="input-group">
                <label for="confirm_password">Подтвердите пароль</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="input-group">
                <label for="avatar">Аватар (опционально)</label>
                <input type="file" id="avatar" name="avatar" accept="image/jpeg, image/png">
            </div>
            
            <button type="submit">Зарегистрироваться</button>
        </form>
        
        <p>Уже есть аккаунт? <a href="login.php">Войдите</a></p>
    </div>
</body>
</html>