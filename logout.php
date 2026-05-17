<?php
include 'includes/config.php';

// Уничтожение сессии
session_unset();
session_destroy();

header("Location: login.php");
exit();