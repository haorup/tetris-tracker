<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

// Handle filters
$dateRange = isset($_GET['date-range']) ? $_GET['date-range'] : 'all';
$gameType = isset($_GET['game-type']) ? $_GET['game-type'] : 'all';

// Modify query based on filters
$dateCondition = "";
if($dateRange != 'all') {
    $days = (int)$dateRange;
    $dateCondition = " AND s.start_time >= DATE_SUB(NOW(), INTERVAL $days DAY)";
}

$gameCondition = "";
if($gameType != 'all') {
    $gameCondition = " AND g.game_genre = '$gameType'";
}

// Get data
$playerPlaytimeData = getAveragePlaytimePerPlayer($conn, $dateCondition, $gameCondition);
$weeklyPlaytimeData = getTotalPlaytimePerWeekByPlayer($conn, $dateCondition, $gameCondition);

// Get players with most achievements
$sql = "SELECT p.player_id, p.username, COUNT(pa.achievement_id) as total_achievements, 
        SUM(pg.highest_score) as total_score
        FROM Player p
        JOIN PlayerGame pg ON p.player_id = pg.player_id
        JOIN Game g ON pg.game_id = g.game_id
        LEFT JOIN PlayerAchievement pa ON p.player_id = pa.player_id
        WHERE 1=1 $gameCondition
        GROUP BY p.player_id, p.username
        ORDER BY total_achievements DESC, total_score DESC
        LIMIT 10";
$topAchievers = $conn->query($sql);

// Get statistical summary data
// Get total game time
$sql = "SELECT SUM(s.duration) as total 
        FROM Session s 
        JOIN Game g ON s.game_id = g.game_id 
        WHERE 1=1 $dateCondition $gameCondition";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalHours = round(($row['total'] ?? 0) / 3600, 2);

// Get average session duration
$sql = "SELECT AVG(s.duration) as avg_duration 
        FROM Session s 
        JOIN Game g ON s.game_id = g.game_id 
        WHERE 1=1 $dateCondition $gameCondition";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$avgMinutes = round(($row['avg_duration'] ?? 0) / 60, 2);

// Get highest score
$sql = "SELECT MAX(pg.highest_score) as max_score 
        FROM PlayerGame pg 
        JOIN Game g ON pg.game_id = g.game_id 
        WHERE 1=1 $gameCondition";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$maxScore = $row['max_score'] ?? 0;

// Get most active player
$sql = "SELECT p.username, COUNT(s.session_id) as session_count 
        FROM Player p
        JOIN Session s ON p.player_id = s.player_id
        JOIN Game g ON s.game_id = g.game_id
        WHERE 1=1 $dateCondition $gameCondition
        GROUP BY p.player_id, p.username
        ORDER BY session_count DESC
        LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$mostActivePlayer = $row['username'] ?? 'N/A';
$sessionCount = $row['session_count'] ?? 0;

// Get available game types
$gameTypes = $conn->query("SELECT DISTINCT game_genre FROM Game ORDER BY game_genre");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tetris Performance Tracking System</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <div class="container">
            <h1>Tetris Performance Dashboard</h1>
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
        <section class="dashboard-filters">
            <h2>Data Filters</h2>
            <form class="filter-form" action="" method="GET">
                <div class="form-group">
                    <label for="date-range">Time Range:</label>
                    <select name="date-range" id="date-range">
                        <option value="all" <?php echo ($dateRange == 'all') ? 'selected' : ''; ?>>All Time</option>
                        <option value="7" <?php echo ($dateRange == '7') ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30" <?php echo ($dateRange == '30') ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90" <?php echo ($dateRange == '90') ? 'selected' : ''; ?>>Last 90 Days</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="game-type">Game Type:</label>
                    <select name="game-type" id="game-type">
                        <option value="all" <?php echo ($gameType == 'all') ? 'selected' : ''; ?>>All Games</option>
                        <?php while($gameTypeRow = $gameTypes->fetch_assoc()): ?>
                        <option value="<?php echo $gameTypeRow['game_genre']; ?>" <?php echo ($gameType == $gameTypeRow['game_genre']) ? 'selected' : ''; ?>><?php echo $gameTypeRow['game_genre']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit">Apply Filters</button>
                <a href="dashboard.php" class="btn">Reset</a>
            </form>
        </section>
        
        <section class="stats-summary">
            <h2>Statistical Summary</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Game Time</h3>
                    <p class="stat-number"><?php echo $totalHours; ?> hours</p>
                </div>
                
                <div class="stat-card">
                    <h3>Average Session Duration</h3>
                    <p class="stat-number"><?php echo $avgMinutes; ?> minutes</p>
                </div>
                
                <div class="stat-card">
                    <h3>Highest Score</h3>
                    <p class="stat-number"><?php echo number_format($maxScore); ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Most Active Player</h3>
                    <p class="stat-number"><?php echo $mostActivePlayer; ?></p>
                    <p><?php echo $sessionCount; ?> games</p>
                </div>
            </div>
        </section>
        
        <section class="chart-container">
            <h2>Average Game Time per Player</h2>
            <canvas id="playtimeChart"></canvas>
        </section>
        
        <section class="chart-container">
            <h2>Weekly Game Time Trend</h2>
            <canvas id="weeklyChart"></canvas>
        </section>
        
        <section class="top-achievers">
            <h2>Players with Most Achievements</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">Rank</th>
                        <th class="sortable-header">Player</th>
                        <th class="sortable-header">Achievements</th>
                        <th class="sortable-header">Total Score</th>
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
                        echo "<td>{$achiever['total_achievements']}</td>";
                        echo "<td>{$achiever['total_score']}</td>";
                        echo "</tr>";
                        $rank++;
                    }
                } else {
                    echo "<tr><td colspan='4'>No matching data found</td></tr>";
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
    
    <script>
        // Chart data preparation
        document.addEventListener('DOMContentLoaded', function() {
            // Player average game time chart
            const playtimeCtx = document.getElementById('playtimeChart').getContext('2d');
            const playtimeData = {
                labels: [
                    <?php
                    $usernames = [];
                    $playtimes = [];
                    if($playerPlaytimeData && $playerPlaytimeData->num_rows > 0) {
                        while($row = $playerPlaytimeData->fetch_assoc()) {
                            $usernames[] = "'" . $row['username'] . "'";
                            $playtimes[] = round($row['avg_playtime'] / 60, 2); // Convert to minutes
                        }
                        echo implode(',', $usernames);
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Average Game Time (minutes)',
                    data: [<?php echo !empty($playtimes) ? implode(',', $playtimes) : ''; ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            };
            
            new Chart(playtimeCtx, {
                type: 'bar',
                data: playtimeData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Weekly game time trend chart
            const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
            const weeks = [];
            const playerData = {};
            
            <?php
            if($weeklyPlaytimeData && $weeklyPlaytimeData->num_rows > 0) {
                // Reset result set pointer
                $weeklyPlaytimeData->data_seek(0);
                while($row = $weeklyPlaytimeData->fetch_assoc()) {
                    echo "if(!weeks.includes('Week " . $row['week_number'] . "')) {";
                    echo "    weeks.push('Week " . $row['week_number'] . "');";
                    echo "}";
                    echo "if(!playerData['" . $row['username'] . "']) {";
                    echo "    playerData['" . $row['username'] . "'] = {};";
                    echo "}";
                    echo "playerData['" . $row['username'] . "']['Week " . $row['week_number'] . "'] = " . round($row['total_weekly_playtime'] / 3600, 2) . ";";
                }
            }
            ?>
            
            const datasets = [];
            const colors = [
                'rgba(255, 99, 132, 0.5)', 'rgba(54, 162, 235, 0.5)', 
                'rgba(255, 206, 86, 0.5)', 'rgba(75, 192, 192, 0.5)',
                'rgba(153, 102, 255, 0.5)', 'rgba(255, 159, 64, 0.5)'
            ];
            
            let colorIndex = 0;
            for(const player in playerData) {
                const dataPoints = [];
                for(const week of weeks) {
                    dataPoints.push(playerData[player][week] || 0);
                }
                
                datasets.push({
                    label: player,
                    data: dataPoints,
                    backgroundColor: colors[colorIndex % colors.length],
                    borderColor: colors[colorIndex % colors.length].replace('0.5', '1'),
                    borderWidth: 1
                });
                
                colorIndex++;
            }
            
            new Chart(weeklyCtx, {
                type: 'line',
                data: {
                    labels: weeks,
                    datasets: datasets
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Game Time (hours)'
                            }
                        }
                    }
                }
            });
        });
    </script>
    
    <script src="js/main.js"></script>
</body>
</html>