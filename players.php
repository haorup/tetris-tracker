<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

// Handle player deletion
if(isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $player_id = $_GET['delete_id'];
    $result = deletePlayer($conn, $player_id);
    if($result) {
        $success_message = "Player successfully deleted!";
    } else {
        $error_message = "Error deleting player.";
    }
}

// Get all players
$sql = "SELECT p.player_id, p.username, p.email_address, p.date_registration, p.country, 
        p.subscription_type, COUNT(pg.game_id) as games_played, 
        MAX(pg.highest_score) as max_score
        FROM Player p
        LEFT JOIN PlayerGame pg ON p.player_id = pg.player_id
        GROUP BY p.player_id, p.username, p.email_address, p.date_registration, p.country, p.subscription_type
        ORDER BY p.username";
$players = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Management - Tetris Performance Tracking System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Player Management</h1>
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
        <?php if(isset($success_message)): ?>
        <div class="alert success">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
        <div class="alert error">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <section class="player-actions">
            <h2>Player Operations</h2>
            <a href="player_form.php" class="btn">Add New Player</a>
        </section>
        
        <section class="player-list">
            <h2>Player List</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">ID</th>
                        <th class="sortable-header">Username</th>
                        <th class="sortable-header">Email</th>
                        <th class="sortable-header">Registration Date</th>
                        <th class="sortable-header">Country</th>
                        <th class="sortable-header">Subscription Type</th>
                        <th class="sortable-header">Games Played</th>
                        <th class="sortable-header">Highest Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($players && $players->num_rows > 0): ?>
                    <?php while($player = $players->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $player['player_id']; ?></td>
                        <td><?php echo $player['username']; ?></td>
                        <td><?php echo $player['email_address']; ?></td>
                        <td><?php echo $player['date_registration']; ?></td>
                        <td><?php echo $player['country']; ?></td>
                        <td><?php echo $player['subscription_type']; ?></td>
                        <td><?php echo $player['games_played']; ?></td>
                        <td><?php echo $player['max_score'] ? $player['max_score'] : 0; ?></td>
                        <td>
                            <a href="player_details.php?id=<?php echo $player['player_id']; ?>" class="btn small">View</a>
                            <a href="player_form.php?id=<?php echo $player['player_id']; ?>" class="btn small">Edit</a>
                            <a href="players.php?delete_id=<?php echo $player['player_id']; ?>" class="btn small delete-btn">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No player data found</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
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