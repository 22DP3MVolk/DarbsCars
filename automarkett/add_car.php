<?php
session_start();
header('Content-Type: application/json');

// Отключаем вывод ошибок PHP для отладки (временно)
error_reporting(0);
ini_set('display_errors', 0);

// Логирование
$logFile = 'add_car_log.txt';
file_put_contents($logFile, "Request received at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($logFile, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents($logFile, "FILES data: " . print_r($_FILES, true) . "\n", FILE_APPEND);
file_put_contents($logFile, "SESSION data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    $error = "Неавторизован: пожалуйста, войдите в систему";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

// Проверка входных данных
if (!isset($_POST['brand']) || !isset($_POST['model']) || !isset($_POST['year']) || 
    !isset($_POST['mileage']) || !isset($_POST['fuel']) || !isset($_POST['transmission']) || 
    !isset($_POST['price']) || !isset($_POST['condition']) || !isset($_FILES['image'])) {
    $error = "Не все обязательные поля заполнены";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

// Подключение к базе данных
$conn = new mysqli("localhost", "root", "", "automarkett");
if ($conn->connect_error) {
    $error = "Ошибка подключения к базе данных: " . $conn->connect_error;
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

// Получение и экранирование данных
$brand = $conn->real_escape_string($_POST['brand']);
$model = $conn->real_escape_string($_POST['model']);
$year = $conn->real_escape_string($_POST['year']);
$mileage = $conn->real_escape_string($_POST['mileage']);
$fuel = $conn->real_escape_string($_POST['fuel']);
$transmission = $conn->real_escape_string($_POST['transmission']);
$price = $conn->real_escape_string($_POST['price']);
$condition = $conn->real_escape_string($_POST['condition']);
$description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
$user_id = (int)$_ SESSION['user_id'];

// Валидация числовых полей
if (!is_numeric($year) || $year < 1900 || $year > date('Y')) {
    $error = "Некорректный год";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}
if (!is_numeric($mileage) || $mileage < 0) {
    $error = "Некорректный пробег";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}
if (!is_numeric($price) || $price < 0) {
    $error = "Некорректная цена";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

// Обработка изображения
$target_dir = "Uploads/";
if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0777, true)) {
        $error = "Не удалось создать папку Uploads";
        file_put_contents($logFile, $error . "\n", FILE_APPEND);
        echo json_encode(["error" => $error]);
        exit;
    }
}

// Проверка типа файла
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['image']['type'], $allowed_types)) {
    $error = "Недопустимый тип файла. Допустимы только JPEG, PNG, GIF";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

// Проверка размера файла
if ($_FILES['image']['size'] > 5000000) {
    $error = "Файл слишком большой. Максимальный размер: 5MB";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

$image = basename($_FILES["image"]["name"]);
$target_file = $target_dir . time() . "_" . $image;
if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    $error = "Ошибка загрузки изображения: " . $_FILES["image"]["error"];
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

// Вставка данных в базу
$sql = "INSERT INTO cars (brand, model, year, mileage, fuel, transmission, price, `condition`, description, image, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = "Ошибка подготовки запроса: " . $conn->error;
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
    exit;
}

$stmt->bind_param("ssiisssdssi", $brand, $model, $year, $mileage, $fuel, $transmission, $price, $condition, $description, $target_file, $user_id);

if ($stmt->execute()) {
    file_put_contents($logFile, "Машина успешно добавлена\n", FILE_APPEND);
    echo json_encode(["success" => true, "message" => "Машина добавлена!"]);
} else {
    $error = "Ошибка добавления машины: " . $stmt->error;
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error]);
}

$stmt->close();
$conn->close();
?>