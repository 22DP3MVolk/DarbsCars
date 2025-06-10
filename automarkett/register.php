<?php
header('Content-Type: application/json');

// Логирование для отладки
$logFile = 'register_log.txt';
file_put_contents($logFile, 'Request received at ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($logFile, 'POST data: ' . print_r($_POST, true) . "\n", FILE_APPEND);

// Подключение к базе данных
$conn = new mysqli("localhost", "root", "", "automarkett");
if ($conn->connect_error) {
    $error = "Connection failed: " . $conn->connect_error;
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

// Получение данных из формы
$first_name = $conn->real_escape_string($_POST['first_name']);
$last_name = $conn->real_escape_string($_POST['last_name']);
$username = $conn->real_escape_string($_POST['username']);
$email = $conn->real_escape_string($_POST['email']);
$password = $conn->real_escape_string($_POST['password']);
$confirm_password = $conn->real_escape_string($_POST['confirm_password']);

// Проверка совпадения паролей
if ($password !== $confirm_password) {
    $error = "Пароли не совпадают";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

// Хеширование пароля
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Вставка пользователя в базу
$sql = "INSERT INTO users (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = "Ошибка подготовки запроса: " . $conn->error;
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

$stmt->bind_param("sssss", $first_name, $last_name, $username, $email, $hashed_password);

if ($stmt->execute()) {
    file_put_contents($logFile, "Регистрация успешна для пользователя: " . $username . "\n", FILE_APPEND);
    echo json_encode(["success" => true, "message" => "Регистрация успешна!"]);
} else {
    $error = "Ошибка регистрации: " . $stmt->error;
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
}

$stmt->close();
$conn->close();
?>