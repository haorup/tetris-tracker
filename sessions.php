<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

// Handle filter conditions
$playerFilter = isset($_GET['player_id']) ? (int)$_GET['player_id'] : 0;
$gameFilter = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
$dateFilter = isset($_GET['date_range']) ? $_GET['date_range'] : 'all';

// Build query conditions
$conditions = [];
$params = [];
$types = "";

if($playerFilter > 0) {
    $conditions[] = "s.player_id = ?";
    $params[] = $playerFilter;
    $types .= "i";
}

if($gameFilter > 0) {
    $conditions[] = "s.game_id = ?";
    $params[] = $gameFilter;
    $types .= "i";
}

if($dateFilter != 'all') {
    $days = (int)$dateFilter;
    $conditions[] = "s.start_time >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params[] = $days;
    $types .= "i";
}

// Assemble WHERE clause
$where = "";
if(!empty($conditions)) {
    $where = "WHERE " . implode(" AND ", $conditions);
}

// Get game sessions
$sql = "SELECT s.session_id, p.username, g.game_genre,
        s.start_time, s.end_time, s.duration,
        s.win_lose_status, s.points_gained
        FROM Session s
        JOIN Player p ON s.player_id = p.player_id
        JOIN Game g ON s.game_id = g.game_id
        $where
        ORDER BY s.start_time DESC
        LIMIT 500";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$sessions = $stmt->get_result();

// Get player list (for filter)
$players = $conn->query("SELECT player_id, username FROM Player ORDER BY username");

// Get game list (for filter)
$games = $conn->query("SELECT game_id, game_genre FROM Game ORDER BY game_genre");

// Statistics queries
// Build WHERE clause for stats query
$statsWhere = $where;

// Get total sessions
$statsQuery = "SELECT COUNT(*) as total FROM Session s $statsWhere";
$statsStmt = $conn->prepare($statsQuery);
if(!empty($params)) {
    $statsStmt->bind_param($types, ...$params);
}
$statsStmt->execute();
$result = $statsStmt->get_result();
$totalSessions = $result->fetch_assoc()['total'] ?? 0;

// Get average playtime per player
$statsQuery = "SELECT AVG(player_total_time) as avg_player_time
              FROM (
                  SELECT s.player_id, SUM(s.duration) as player_total_time
                  FROM Session s
                  $statsWhere
                  GROUP BY s.player_id
              ) as player_times";
$statsStmt = $conn->prepare($statsQuery);
if(!empty($params)) {
    $statsStmt->bind_param($types, ...$params);
}
$statsStmt->execute();
$result = $statsStmt->get_result();
$avgMinutes = round(($result->fetch_assoc()['avg_player_time'] ?? 0) / 60, 1);

// Get win rate
$statsQuery = "SELECT
               SUM(CASE WHEN win_lose_status = TRUE THEN 1 ELSE 0 END) as wins,
               COUNT(*) as total
               FROM Session s $statsWhere";
$statsStmt = $conn->prepare($statsQuery);
if(!empty($params)) {
    $statsStmt->bind_param($types, ...$params);
}
$statsStmt->execute();
$result = $statsStmt->get_result();
$row = $result->fetch_assoc();
$winRate = ($row['total'] > 0) ? round(($row['wins'] / $row['total']) * 100, 1) : 0;

// Get average points
$statsQuery = "SELECT AVG(points_gained) as avg_points FROM Session s $statsWhere";
$statsStmt = $conn->prepare($statsQuery);
if(!empty($params)) {
    $statsStmt->bind_param($types, ...$params);
}
$statsStmt->execute();
$result = $statsStmt->get_result();
$avgPoints = round($result->fetch_assoc()['avg_points'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Sessions - Tetris Performance Tracking System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Game Sessions</h1>
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
        <section class="session-filters">
            <h2>Filter Sessions</h2>
            <form action="" method="GET" class="filter-form">
                <div class="form-group">
                    <label for="player_id">Player:</label>
                    <select name="player_id" id="player_id">
                        <option value="0">All Players</option>
                        <?php while($player = $players->fetch_assoc()): ?>
                        <option value="<?php echo $player['player_id']; ?>" <?php echo ($playerFilter == $player['player_id']) ? 'selected' : ''; ?>>
                            <?php echo $player['username']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="game_id">Game:</label>
                    <select name="game_id" id="game_id">
                        <option value="0">All Games</option>
                        <?php while($game = $games->fetch_assoc()): ?>
                        <option value="<?php echo $game['game_id']; ?>" <?php echo ($gameFilter == $game['game_id']) ? 'selected' : ''; ?>>
                            <?php echo $game['game_genre']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_range">Date Range:</label>
                    <select name="date_range" id="date_range">
                        <option value="all" <?php echo ($dateFilter == 'all') ? 'selected' : ''; ?>>All Time</option>
                        <option value="7" <?php echo ($dateFilter == '7') ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30" <?php echo ($dateFilter == '30') ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90" <?php echo ($dateFilter == '90') ? 'selected' : ''; ?>>Last 90 Days</option>
                    </select>
                </div>

                <button type="submit">Apply Filters</button>
                <a href="sessions.php" class="btn">Reset</a>
            </form>
        </section>

        <section class="session-stats">
            <h2>Session Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Sessions</h3>
                    <p class="stat-number"><?php echo $totalSessions; ?></p>
                </div>

                <div class="stat-card">
                    <h3>Average Playtime Per Player</h3>
                    <p class="stat-number"><?php echo $avgMinutes; ?> minutes</p>
                </div>

                <div class="stat-card">
                    <h3>Win Rate</h3>
                    <p class="stat-number"><?php echo $winRate; ?>%</p>
                </div>

                <div class="stat-card">
                    <h3>Average Score</h3>
                    <p class="stat-number"><?php echo $avgPoints; ?></p>
                </div>
            </div>
        </section>

        <section class="session-list">
            <h2>Game Session List</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">ID</th>
                        <th class="sortable-header">Player</th>
                        <th class="sortable-header">Game</th>
                        <th class="sortable-header">Start Time</th>
                        <th class="sortable-header">End Time</th>
                        <th class="sortable-header">Duration</th>
                        <th class="sortable-header">Result</th>
                        <th class="sortable-header">Points Gained</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if($sessions && $sessions->num_rows > 0) {
                    while($session = $sessions->fetch_assoc()) {
                        $duration = formatTime($session['duration']);
                        $result = $session['win_lose_status'] ? 'Victory' : 'Defeat';

                        echo "<tr>";
                        echo "<td>{$session['session_id']}</td>";
                        echo "<td>{$session['username']}</td>";
                        echo "<td>{$session['game_genre']}</td>";
                        echo "<td>{$session['start_time']}</td>";
                        echo "<td>{$session['end_time']}</td>";
                        echo "<td>{$duration}</td>";
                        echo "<td>{$result}</td>";
                        echo "<td>{$session['points_gained']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No matching sessions found</td></tr>";
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