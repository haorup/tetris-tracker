<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

// Get top 5 players for homepage display
$topPlayers = getTopPlayersByScore($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tetris Performance Tracking System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Tetris Performance Tracking System</h1>
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
        <section class="welcome">
            <h2>Welcome to Tetris Performance Tracking System</h2>
            <p>This system helps you track player performance, achievements, and statistics for Tetris games. Use the navigation above to access different modules. The system tracks players, games, game sessions, actions, and achievements.</p>
        </section>
        
        <section class="top-players">
            <h2>Top Players Leaderboard</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">Rank</th>
                        <th class="sortable-header">Player</th>
                        <th class="sortable-header">Game</th>
                        <th class="sortable-header">Highest Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $rank = 1;
                if($topPlayers && $topPlayers->num_rows > 0) {
                    while($player = $topPlayers->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$rank}</td>";
                        echo "<td>{$player['username']}</td>";
                        echo "<td>{$player['game_genre']}</td>";
                        echo "<td>{$player['highest_score']}</td>";
                        echo "<td><a href='player_details.php?id={$player['player_id']}' class='btn small'>View Details</a></td>";
                        echo "</tr>";
                        $rank++;
                    }
                } else {
                    echo "<tr><td colspan='5'>No player data found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </section>
        
        <section class="quick-stats">
            <h2>Quick Statistics</h2>
            <div class="stats-grid">
                <?php
                // Get total number of players
                $result = $conn->query("SELECT COUNT(*) as total FROM Player");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Total Players</h3>
                    <p class="stat-number"><?php echo $row['total']; ?></p>
                </div>
                
                <?php
                // Get total number of games played
                $result = $conn->query("SELECT COUNT(*) as total FROM Session");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Total Games Played</h3>
                    <p class="stat-number"><?php echo $row['total']; ?></p>
                </div>
                
                <?php
                // Get total number of game types
                $result = $conn->query("SELECT COUNT(DISTINCT game_genre) as total FROM Game");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Total Game Types</h3>
                    <p class="stat-number"><?php echo $row['total']; ?></p>
                </div>
                
                <?php
                // Get average game duration
                $result = $conn->query("SELECT AVG(duration) as avg_duration FROM Session");
                $row = $result->fetch_assoc();
                $avgMinutes = round($row['avg_duration'] / 60, 2);
                ?>
                <div class="stat-card">
                    <h3>Average Game Duration</h3>
                    <p class="stat-number"><?php echo $avgMinutes; ?> minutes</p>
                </div>
            </div>
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