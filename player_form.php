<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

$player = [
    'player_id' => '',
    'username' => '',
    'email_address' => '',
    'date_registration' => date('Y-m-d'),
    'date_of_birth' => '',
    'country' => '',
    'subscription_type' => 'free'
];

$isEditing = false;

// Check if editing mode
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $player_id = $_GET['id'];
    $isEditing = true;
    
    // Get player data
    $sql = "SELECT * FROM Player WHERE player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $player = $result->fetch_assoc();
    } else {
        $error_message = "Player not found!";
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $player = [
        'player_id' => $_POST['player_id'],
        'username' => $_POST['username'],
        'email_address' => $_POST['email_address'],
        'date_registration' => $_POST['date_registration'],
        'date_of_birth' => empty($_POST['date_of_birth']) ? NULL : $_POST['date_of_birth'],
        'country' => $_POST['country'],
        'subscription_type' => $_POST['subscription_type']
    ];
    
    // Validate form data
    $errors = [];
    
    if(empty($player['username'])) {
        $errors[] = "Username cannot be empty";
    }
    
    if(empty($player['email_address'])) {
        $errors[] = "Email cannot be empty";
    } elseif(!filter_var($player['email_address'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if(empty($player['date_registration'])) {
        $errors[] = "Registration date cannot be empty";
    }
    
    // If no errors, save data
    if(empty($errors)) {
        if($isEditing) {
            // Update existing player
            $result = updatePlayerProfile(
                $conn, 
                $player['player_id'], 
                $player['username'], 
                $player['email_address'], 
                $player['date_registration'],
                $player['date_of_birth'],
                $player['country'], 
                $player['subscription_type']
            );
            
            if($result) {
                header("Location: players.php");
                exit;
            } else {
                $error_message = "Error updating player!";
            }
        } else {
            // Create new player
            $sql = "INSERT INTO Player (player_id, username, email_address, date_registration, date_of_birth, country, subscription_type) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "issssss", 
                $player['player_id'], 
                $player['username'], 
                $player['email_address'], 
                $player['date_registration'], 
                $player['date_of_birth'], 
                $player['country'], 
                $player['subscription_type']
            );
            
            if($stmt->execute()) {
                header("Location: players.php");
                exit;
            } else {
                $error_message = "Error creating player!";
            }
        }
    }
}

// Get max player_id to auto-generate new ID
if(!$isEditing) {
    $result = $conn->query("SELECT MAX(player_id) as max_id FROM Player");
    $row = $result->fetch_assoc();
    $player['player_id'] = ($row['max_id'] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditing ? 'Edit' : 'Add'; ?> Player - Tetris Performance Tracking System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo $isEditing ? 'Edit' : 'Add'; ?> Player</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="players.php">Players</a></li>
                    <li><a href="games.php">Games</a></li>
                    <li><a href="achievements.php">Achievements</a></li>
                    <li><a href="sessions.php">Game Sessions</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <?php if(isset($error_message)): ?>
        <div class="alert error">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if(!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <section class="form-container">
            <h2><?php echo $isEditing ? 'Edit' : 'Add'; ?> Player</h2>
            <form action="" method="POST">
                <input type="hidden" name="player_id" value="<?php echo $player['player_id']; ?>">
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo $player['username']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email_address">Email:</label>
                    <input type="email" id="email_address" name="email_address" value="<?php echo $player['email_address']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="date_registration">Registration Date:</label>
                    <input type="date" id="date_registration" name="date_registration" value="<?php echo $player['date_registration']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $player['date_of_birth']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" id="country" name="country" value="<?php echo $player['country']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="subscription_type">Subscription Type:</label>
                    <select id="subscription_type" name="subscription_type">
                        <option value="free" <?php echo ($player['subscription_type'] == 'free') ? 'selected' : ''; ?>>Free</option>
                        <option value="paid" <?php echo ($player['subscription_type'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit"><?php echo $isEditing ? 'Update' : 'Add'; ?> Player</button>
                    <a href="players.php" class="btn">Cancel</a>
                </div>
            </form>
        </section>
    </main>
    
    <footer>
        <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Tetris Performance Tracking System</p>
        </div>
    </footer>
    
    <script src="js/main.js"></script>
</body>
</html>