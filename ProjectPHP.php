<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$host = 'localhost';
$dbname = 'project';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

$tableCreateSQL = "
CREATE TABLE IF NOT EXISTS Accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);";

try {
    $pdo->exec($tableCreateSQL);
} catch (PDOException $e) {
    die("Table creation failed: " . $e->getMessage());
}

$tableCreateSQL = "
CREATE TABLE IF NOT EXISTS car_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_model VARCHAR(100) NOT NULL,
    model_year VARCHAR(4) NOT NULL,
    fuel_type VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(100) NOT NULL,
    post_date DATE NOT NULL,
    description TEXT,
    car_picture VARCHAR(255) NOT NULL,
    seller_id VARCHAR(100) NOT NULL,
    CONSTRAINT fk_seller_id FOREIGN KEY (seller_id) REFERENCES Accounts(username)
);";

try {
    $pdo->exec($tableCreateSQL);
} catch (PDOException $e) {
    die("Table creation failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['usernameSignUp']) ? trim($_POST['usernameSignUp']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['passwordSignUp']) ? trim($_POST['passwordSignUp']) : '';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $checkSQL = "SELECT COUNT(*) FROM Accounts WHERE username = :username OR email = :email";
    $stmt = $pdo->prepare($checkSQL);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $insertSQL = "INSERT INTO Accounts (username, email, password) VALUES (:username, :email, :password)";

    try {
        $stmt = $pdo->prepare($insertSQL);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);

        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'User registered successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>