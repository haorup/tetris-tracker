<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

// Get all achievements
$sql = "SELECT a.achievement_id, a.achievement_type, a.description, 
        a.difficulty_level, a.required_points, a.required_playtime,
        COUNT(pa.player_id) as unlocked_count
        FROM Achievement a
        LEFT JOIN PlayerAchievement pa ON a.achievement_id = pa.achievement_id
        GROUP BY a.achievement_id, a.achievement_type, a.description, a.difficulty_level, a.required_points, a.required_playtime
        ORDER BY a.difficulty_level, a.achievement_id";
$achievements = $conn->query($sql);

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
        
        <section class="achievement-list">
            <h2>All Achievements</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">ID</th>
                        <th class="sortable-header">Type</th>
                        <th class="sortable-header">Description</th>
                        <th class="sortable-header">Difficulty</th>
                        <th class="sortable-header">Required Points</th>
                        <th class="sortable-header">Required Playtime</th>
                        <th class="sortable-header">Unlocked By</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if($achievements && $achievements->num_rows > 0) {
                    while($achievement = $achievements->fetch_assoc()) {
                        $requiredTimeFormatted = formatTime($achievement['required_playtime']);
                        echo "<tr>";
                        echo "<td>{$achievement['achievement_id']}</td>";
                        echo "<td>{$achievement['achievement_type']}</td>";
                        echo "<td>{$achievement['description']}</td>";
                        echo "<td>{$achievement['difficulty_level']}</td>";
                        echo "<td>{$achievement['required_points']}</td>";
                        echo "<td>{$requiredTimeFormatted}</td>";
                        echo "<td>{$achievement['unlocked_count']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No achievement data found</td></tr>";
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
                    <h3>Participated Achievements</h3>
                    <p class="stat-number"><?php echo $row['total']; ?></p>
                </div>
                
                <?php
                // Get most difficult achievement to complete
                $result = $conn->query("SELECT a.description, COUNT(pa.player_id) as unlock_count, a.difficulty_level
                                      FROM Achievement a
                                      LEFT JOIN PlayerAchievement pa ON a.achievement_id = pa.achievement_id
                                      GROUP BY a.achievement_id, a.description, a.difficulty_level
                                      ORDER BY unlock_count ASC, a.difficulty_level DESC
                                      LIMIT 1");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Most Difficult Achievement</h3>
                    <p class="stat-number"><?php echo isset($row['description']) ? $row['description'] : 'N/A'; ?></p>
                    <p><?php echo isset($row['unlock_count']) ? $row['unlock_count'] . ' participants' : ''; ?></p>
                </div>
                
                <?php
                // Get average completion rate
                $result = $conn->query("SELECT AVG(
                                        CASE WHEN pa.is_completed = 1 THEN 100
                                        ELSE (pa.current_total_points * 100 / a.required_points) 
                                        END) as avg_completion
                                      FROM PlayerAchievement pa
                                      JOIN Achievement a ON pa.achievement_id = a.achievement_id");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Average Completion Rate</h3>
                    <p class="stat-number"><?php echo round($row['avg_completion'] ?? 0, 1); ?>%</p>
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