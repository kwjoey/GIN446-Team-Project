<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$dsn = 'mysql:host=localhost;dbname=project';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to the database: " . $e->getMessage());
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$readd = $_POST['readd'] ?? '';
$reciever_id = $_POST['reciever_id'] ?? '';
$sender_id = $_POST['sender_id'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($readd) || empty($reciever_id) || empty($sender_id) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

$insertSQL = "INSERT INTO messages (readd, reciever_id, sender_id, message, message_date) VALUES (:readd, :reciever_id, :sender_id, :message, NOW())";

try {
    $stmt = $pdo->prepare($insertSQL);

    $stmt->bindParam(':readd', $readd);
    $stmt->bindParam(':reciever_id', $reciever_id);
    $stmt->bindParam(':sender_id', $sender_id);
    $stmt->bindParam(':message', $message);

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
