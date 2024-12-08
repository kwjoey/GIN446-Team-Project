<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'project';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_model = $_POST['car_model'] ?? '';
    $model_year = $_POST['Model-year'] ?? '';
    $fuel_type = $_POST['fuel_type'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $location = $_POST['location'] ?? '';
    $post_date = date('Y-m-d');
    $description = $_POST['description'] ?? '';
    $seller_id = $_POST['seller_id'] ?? '';

    if (empty($car_model) || empty($model_year) || empty($fuel_type) || empty($price) || empty($phone) || empty($location)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (isset($_FILES['car_picture']) && $_FILES['car_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['car_picture']['tmp_name'];
        $fileName = $_FILES['car_picture']['name'];
        $fileSize = $_FILES['car_picture']['size'];
        $fileType = $_FILES['car_picture']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $car_picture = $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'There was an error moving the uploaded file.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or there was an upload error.']);
        exit;
    }

    $insertSQL = "INSERT INTO car_details (car_model, model_year, fuel_type, category, price, phone, location, post_date, description, car_picture, seller_id) VALUES (:car_model, :model_year, :fuel_type, :category, :price, :phone, :location, :post_date, :description, :car_picture, :seller_id);";

    try {
        $stmt = $pdo->prepare($insertSQL);

        $stmt->bindParam(':car_model', $car_model);
        $stmt->bindParam(':model_year', $model_year);
        $stmt->bindParam(':fuel_type', $fuel_type);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':post_date', $post_date);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':car_picture', $car_picture);
        $stmt->bindParam(':seller_id', $seller_id);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Car details uploaded successfully.',
            'car_model' => $car_model,
            'model_year' => $model_year,
            'fuel_type' => $fuel_type,
            'price' => $price,
            'phone' => $phone,
            'location' => $location,
            'post_date' => $post_date,
            'description' => $description,
            'car_picture' => $car_picture
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>