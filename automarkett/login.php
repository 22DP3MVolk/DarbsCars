<?php
session_start();
header('Content-Type: application/json');

file_put_contents('login_log.txt', print_r($_POST, true) . "\n", FILE_APPEND);

$conn = new mysqli("localhost", "root", "", "automarkett");
if ($conn->connect_error) {
    $error = "Connection failed: " . $conn->connect_error;
    file_put_contents('login_log.txt', $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    $error = "Не указаны имя пользователя или пароль";
    file_put_contents('login_log.txt', $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password'];

$sql = "SELECT id, username, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = "Ошибка подготовки запроса: " . $conn->error;
    file_put_contents('login_log.txt', $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        file_put_contents('login_log.txt', "Вход успешен для пользователя: " . $user['username'] . " (ID: " . $user['id'] . ")\n", FILE_APPEND);
        echo json_encode(["success" => true, "user" => ["id" => $user['id'], "username" => $user['username']]]);
    } else {
        $error = "Неверный пароль";
        file_put_contents('login_log.txt', $error . "\n", FILE_APPEND);
        echo json_encode(["error" => $error]);
    }
} else {
    $error = "Пользователь не найден";
    file_put_contents('login_log.txt', $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
}

$stmt->close();
$conn->close();
?>