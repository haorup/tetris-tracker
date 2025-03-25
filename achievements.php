<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

// Get players with highest achievement progress (includes all players with progress, not just completed ones)
$sql = "SELECT p.player_id, p.username,
        COUNT(pa.achievement_id) as achievement_count,
        (COUNT(pa.achievement_id) * 100 / (SELECT COUNT(*) FROM Achievement)) as completion_percentage
        FROM Player p
        JOIN PlayerAchievement pa ON p.player_id = pa.player_id
        GROUP BY p.player_id, p.username
        ORDER BY achievement_count DESC
        LIMIT 5";
$topAchievers = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievement System - Tetris Performance Tracking System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Achievement System</h1>
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
        <section class="top-achievers">
            <h2>Players with Most Achievements</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">Rank</th>
                        <th class="sortable-header">Player</th>
                        <th class="sortable-header">Unlocked Achievements</th>
                        <th class="sortable-header">Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $rank = 1;
                if($topAchievers && $topAchievers->num_rows > 0) {
                    while($achiever = $topAchievers->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$rank}</td>";
                        echo "<td>{$achiever['username']}</td>";
                        echo "<td>{$achiever['achievement_count']}</td>";
                        echo "<td>" . round($achiever['completion_percentage'], 1) . "%</td>";
                        echo "</tr>";
                        $rank++;
                    }
                } else {
                    echo "<tr><td colspan='4'>No achievement data found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </section>

        <section class="achievement-stats">
            <h2>Achievement Statistics</h2>
            <div class="stats-grid">
                <?php
                // Get total achievement count
                $result = $conn->query("SELECT COUNT(*) as total FROM Achievement");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Total Achievements</h3>
                    <p class="stat-number"><?php echo $row['total']; ?></p>
                </div>

                <?php
                // Get total participated achievements (doesn't require completion, just progress)
                $result = $conn->query("SELECT COUNT(*) as total FROM PlayerAchievement");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Gained Achievements</h3>
                    <p class="stat-number"><?php echo $row['total']; ?></p>
                </div>
            </div>
        </section>

        <section class="achievements-per-game">
            <h2>Achievements Per Game</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">Game ID</th>
                        <th class="sortable-header">Game Description</th>
                        <th class="sortable-header">Achievement Count</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Get achievements per game with more detailed metrics
                $gameAchievements = $conn->query("SELECT g.game_id, g.game_description,
                                        COUNT(DISTINCT pa.achievement_id) as achievement_count,
                                        COUNT(DISTINCT pa.player_id) as players_count,
                                        ROUND((SUM(CASE WHEN pa.is_completed = 1 THEN 1 ELSE 0 END) * 100.0 /
                                              NULLIF(COUNT(pa.achievement_id), 0)), 1) as completion_rate
                                      FROM Game g
                                      LEFT JOIN Session s ON g.game_id = s.game_id
                                      LEFT JOIN PlayerAchievement pa ON s.player_id = pa.player_id
                                      GROUP BY g.game_id, g.game_description
                                      ORDER BY achievement_count DESC, completion_rate DESC");

                if($gameAchievements && $gameAchievements->num_rows > 0) {
                    while($game = $gameAchievements->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$game['game_id']}</td>";
                        echo "<td>{$game['game_description']}</td>";
                        echo "<td>{$game['achievement_count']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No achievement data per game found</td></tr>";
                }
                ?>
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