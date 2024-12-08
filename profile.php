<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Project.html");
    exit;
}

$user_id = $_SESSION['username'];

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

$tableCreateSQL = "
    CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        readd INT NOT NULL,
        reciever_id VARCHAR(255) NOT NULL,
        sender_id VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        message_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
";

try {
    $pdo->exec($tableCreateSQL);
} catch (PDOException $e) {
    die("Table creation failed: " . $e->getMessage());
}


$userId = $_SESSION['username'];

$query = "
    SELECT COUNT(*) AS message_count
    FROM messages
    WHERE reciever_id = :userId
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
$stmt->execute();

$messageCount = $stmt->fetchColumn();

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

$senders = [];
foreach ($messages as $message) {
    $sender_id = $message['sender_id'];

    $senders[] = [
        'sender_name' => $sender_id,
        'message_count' => $message['message_count']
    ];
}


$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JR Deals</title>
    <link rel="stylesheet" href="css2/ProjectCSSV3.css" />
    <script src="js2/ProjectJSV6.js" defer></script>
</head>

<body>
<nav class="navbar">
    <div class="title">🚗 JR Deals</div>
    <div class="nav-links">
</div>
    <div class="auth-wrapper">
        <div class="sell-car-container">
            <button class="sell-car-btn" id="sellCar">Sell</button>
        </div>
        <div class="notification-container">
    <button class="notification-btn" onclick="toggleNotificationPanel()">
        🔔
        <span class="notification-badge" id="notification-badge">
            <?php echo $messageCount > 0 ? $messageCount : ''; ?>
        </span>
    </button>
</div>
</div>

<div id="notification-container" class="notification-container">
    <button class="close-btn" onclick="toggleNotificationPanel()">✖</button>
    <div class="notification-content">
    <h3>Notifications</h3>
    <?php if (!empty($senders)) : ?>
        <?php foreach ($senders as $sender) : ?>
            <div class="notification-item">
                <span class="sender-name"><?php echo htmlspecialchars($sender['sender_name']); ?></span>
                <span class="message-count"><?php echo $sender['message_count']; ?> messages</span>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No new messages.</p>
    <?php endif; ?>
</div>
<div id="chat-popup" class="chat-popup">
    <button id="close-chat-popup" class="close-btn">✖</button>
    <div class="chat-header">
        <span id="chat-with">Chat with: </span>
    </div>
    <div id="chat-messages" class="chat-messages"></div>
    <textarea id="chat-input" placeholder="Type a message..." rows="4"></textarea>
    <button id="send-message" class="btn btn-submit">Send</button>
</div>
</div>
        <div class="profile-menu">
            <img src="img/PProfile.png" alt="Profile" class="profile-icon" />
            <div class="dropdown">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>
    <div id="form-container" class="form-container">
        <div id="form-wrapper" class="form-wrapper">

            <form id="car-form" class="sign-form" method="POST" action="sell_car.php" enctype="multipart/form-data"
                novalidate>
                <input type="hidden" name="seller_id" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">

                <button type="button" id="close-btn-car" class="close-btn">✖</button>
                <div class="form-title-container">
                    <h2 class="form-title" id="sign-in-title">Sell Your Car</h2>
                </div>

                <label for="car-model">Car Model</label>
                <input type="text" id="car-model" name="car_model" class="input-field" required>

                <label for="Model-year">Model Year</label>
                <input type="text" id="Model-year" name="Model-year" class="input-field" required>

                <label for="fuel-type">Fuel Type</label>
                <select id="fuel-type" name="fuel_type" required>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Electric">Electric</option>
                    <option value="Hybrid">Hybrid</option>
                </select>

                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="Car">Car</option>
                    <option value="Motorcycle">Motorcycle</option>
                    <option value="Truck">Truck</option>
                    <option value="Van">Van</option>
                </select>

                <label for="price">Price</label>
                <input type="number" id="price" name="price" class="input-field" required>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="input-field" required>

                <label for="location">Location</label>
                <input type="text" id="location" name="location" class="input-field" required>

                <label for="description">Car Description</label>
                <textarea id="description" name="description" rows="5" class="input-field"></textarea>

                <input type="file" id="car-picture" name="car_picture" class="input-field"
                    accept=".jpeg, .png, .jpg, .webp" required>

                <button type="submit" name="sign-car" class="btn btn-submit">Submit</button>
                <p class="form-warning" id="sign-car-warning" style="display: none;">All fields must be filled.</p>
            </form>
        </div>
    </div>
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM car_details");
        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching car details: " . $e->getMessage());
    }
    ?>
    <main>
    <div class="car-list">
    <?php foreach ($cars as $car): ?>
        <?php 
            $isOwnedByUser = ($user_id === $car['seller_id']); 
        ?>
        <div 
            class="car-item" 
            data-car='<?php echo json_encode($car); ?>' 
            style="<?php echo $isOwnedByUser ? 'border: 2px solid red;' : ''; ?>"
        >
            <div class="car-image" style="background-image: url('uploads/<?php echo htmlspecialchars($car['car_picture']); ?>');"></div>
            <div class="car-details">
                <p class="car-model-year">
                    <?php echo htmlspecialchars($car['car_model']); ?> <?php echo htmlspecialchars($car['model_year']); ?>
                </p>
                <p class="car-price">$<?php echo htmlspecialchars($car['price']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    </main>
    <div id="car-popup" class="car-popup">
        <button id="close-popup" class="close-btn">✖</button>
        <div id="popup-content" class="popup-content"></div>
    </div>
    <div id="backdrop" class="backdrop"></div>
    <footer id="footer">
        <p>© This website is created by <b>Rudy Azar</b> and <b>Joey Kourieh</b> for educational purposes only.</p>
        <p>Project for the <b>GIN446</b> USEK course.</p>
        <ul class="links">
            <li><a href="https://rudyazar.infinityfreeapp.com" target="_blank">Rudy Azar</a></li>
            <li><a href="https://joeykourieh.great-site.net/?i=1 " target="_blank">Joey Kourieh</a></li>
        </ul>
    </footer>
</body>
<script>
        const userId = <?php echo json_encode($user_id); ?>;
        window.userId = userId;

        function toggleNotificationPanel() {
  const panel = document.getElementById("notification-container");
  panel.classList.toggle("open");
}

document.addEventListener('DOMContentLoaded', () => {
    const messageCount = <?php echo $messageCount; ?>; 

    const notificationBadge = document.getElementById('notification-badge');
    
    if (messageCount > 0) {
        notificationBadge.textContent = messageCount; 
    } else {
        notificationBadge.style.display = 'none'; 
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const messageCount = <?php echo $messageCount; ?>;
    const notificationBadge = document.getElementById('notification-badge');
    
    if (messageCount > 0) {
        notificationBadge.textContent = messageCount;
    } else {
        notificationBadge.style.display = 'none';
    }

    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', (e) => {
            const senderName = e.currentTarget.querySelector('.sender-name').textContent;
            openChat(senderName);
        });
    });
});

function openChat(senderName) {
    const chatPopup = document.getElementById('chat-popup');
    const chatWith = document.getElementById('chat-with');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const sendMessageButton = document.getElementById('send-message');

    chatWith.textContent = `Chat with: ${senderName}`;

    chatMessages.innerHTML = '';

    fetch('fetch_messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            sender: senderName,
            receiver: window.userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.messages.forEach(message => {
                const messageElement = document.createElement('div');
                messageElement.classList.add('chat-message');
                messageElement.innerHTML = `
                    <span class="sender">${message.sender_id}</span>: ${message.message}<br>
                    <span class="message-time">${message.message_date}</span>
                `;
                chatMessages.appendChild(messageElement);
            });
        } else {
            chatMessages.innerHTML = '<p>No messages found.</p>';
        }
    })
    .catch(error => console.error('Error fetching messages:', error));

    chatPopup.classList.add('open');

    sendMessageButton.addEventListener('click', () => {
        const message = chatInput.value.trim();
        if (message) {
            sendMessage(senderName, message);
        }
    });
}

function sendMessage(receiver, message) {
    fetch('send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            sender: window.userId,
            receiver: receiver,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const chatMessages = document.getElementById('chat-messages');
            const messageElement = document.createElement('div');
            messageElement.classList.add('chat-message');
            messageElement.innerHTML = `
                <span class="sender">${window.userId}</span>: ${message}<br>
                <span class="message-time">${data.message_date}</span>
            `;
            chatMessages.appendChild(messageElement);
            document.getElementById('chat-input').value = '';
        } else {
            alert('Failed to send message');
        }
    })
    .catch(error => console.error('Error sending message:', error));
}
    </script>
</html>