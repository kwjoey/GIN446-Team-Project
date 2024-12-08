<?php
session_start();
$userId = $_SESSION['userId'];

$query = "
    SELECT sender_id, COUNT(*) AS message_count
    FROM messages
    WHERE reciever_id = :userId
    GROUP BY sender_id
    ORDER BY message_count DESC
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['messages' => $messages]);
?>