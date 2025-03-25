<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: players.php");
    exit;
}

$player_id = $_GET['id'];

// Get player details
$sql = "SELECT *
        FROM Player
        WHERE player_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0) {
    header("Location: players.php");
    exit;
}
$player = $result->fetch_assoc();

// Get player achievements
$sql = "SELECT pa.achievement_id, pa.current_total_points, pa.current_total_playtime,
        pa.date_started, pa.date_completed, pa.is_completed,
        a.achievement_type, a.description, a.required_points, a.required_playtime
        FROM PlayerAchievement pa
        JOIN Achievement a ON pa.achievement_id = a.achievement_id
        WHERE pa.player_id = ?
        ORDER BY pa.is_completed DESC, a.required_points ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$playerAchievements = $stmt->get_result();

// Get player rankings based on total points gained
$sql = "SELECT p.player_id, p.username,
        SUM(s.points_gained) as total_points,
        SUM(s.duration) as total_playtime,
        COUNT(s.session_id) as times_played,
        MAX(s.end_time) as last_played_date,
        (SELECT COUNT(*) + 1 FROM
            (SELECT p2.player_id, SUM(s2.points_gained) as total_points
             FROM Player p2
             JOIN Session s2 ON p2.player_id = s2.player_id
             GROUP BY p2.player_id
             HAVING SUM(s2.points_gained) > (
                SELECT SUM(s3.points_gained)
                FROM Session s3
                WHERE s3.player_id = ?
             )) as higher_ranked
        ) as player_rank,
        (SELECT COUNT(*) FROM Player p4 WHERE p4.player_id IN
            (SELECT DISTINCT s4.player_id FROM Session s4)
        ) as total_players
        FROM Player p
        JOIN Session s ON p.player_id = s.player_id
        WHERE p.player_id = ?
        GROUP BY p.player_id, p.username";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $player_id, $player_id);
$stmt->execute();
$playerRank = $stmt->get_result()->fetch_assoc();

// Get recent game sessions for this player
$sql = "SELECT s.session_id, s.player_id, s.game_id, s.start_time, s.end_time,
        s.duration, s.win_lose_status, s.points_gained, p.username, g.game_description
        FROM Session s
        JOIN Player p ON s.player_id = p.player_id
        JOIN Game g ON s.game_id = g.game_id
        WHERE s.player_id = ?
        ORDER BY s.end_time DESC
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$sessions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        .control-item {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .control-enabled {
            background-color: #d4edda;
            color: #155724;
        }
        .control-disabled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .item-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .color-sample {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin: 0 auto;
            border: 1px solid #ddd;
        }
        .achievement-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        .achievement-completed {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .achievement-progress {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }
        .achievement-bar {
            height: 100%;
            background-color: #007bff;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Player Details - Player ID: <?php echo $player['player_id']; ?></h1>
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
        <section class="player-details">
            <h2>Player Information</h2>
            <p><strong>Player ID:</strong> <?php echo $player['player_id']; ?></p>
            <p><strong>Username:</strong> <?php echo $player['username']; ?></p>
            <p><strong>Email Address:</strong> <?php echo $player['email_address']; ?></p>
            <p><strong>Registration Date:</strong> <?php echo $player['date_registration']; ?></p>
            <p><strong>Country:</strong> <?php echo $player['country']; ?></p>
            <p><strong>Subscription Type:</strong> <?php echo $player['subscription_type']; ?></p>
            <p><strong>Date of Birth:</strong> <?php echo $player['date_of_birth']; ?></p>
        </section>

        <section class="player-achievements">
            <h2>Unlocked Achievements</h2>
            <div class="items-grid">
            <?php
            if($playerAchievements && $playerAchievements->num_rows > 0):
                while($achievement = $playerAchievements->fetch_assoc()):
                    $pointsPercentage = min(100, ($achievement['current_total_points'] / $achievement['required_points']) * 100);
                    $timePercentage = min(100, ($achievement['current_total_playtime'] / $achievement['required_playtime']) * 100);
                    $overallPercentage = min(100, ($pointsPercentage + $timePercentage) / 2);
                    $cardClass = $achievement['is_completed'] ? 'achievement-card achievement-completed' : 'achievement-card';
            ?>
                <div class="<?php echo $cardClass; ?>">
                    <h3><?php echo $achievement['achievement_type']; ?></h3>
                    <p><?php echo $achievement['description']; ?></p>
                    <p><strong>Status:</strong> <?php echo $achievement['is_completed'] ? 'Completed' : 'In Progress'; ?></p>
                    <p><strong>Started:</strong> <?php echo $achievement['date_started']; ?></p>
                    <?php if($achievement['is_completed']): ?>
                    <p><strong>Completed:</strong> <?php echo $achievement['date_completed']; ?></p>
                    <?php else: ?>
                    <p><strong>Points Progress:</strong> <?php echo $achievement['current_total_points']; ?>/<?php echo $achievement['required_points']; ?></p>
                    <div class="achievement-progress">
                        <div class="achievement-bar" style="width: <?php echo $pointsPercentage; ?>%"></div>
                    </div>
                    <p><strong>Time Progress:</strong> <?php echo formatTime($achievement['current_total_playtime']); ?>/<?php echo formatTime($achievement['required_playtime']); ?></p>
                    <div class="achievement-progress">
                        <div class="achievement-bar" style="width: <?php echo $timePercentage; ?>%"></div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php
                endwhile;
            else:
            ?>
                <p>No achievements found for this player.</p>
            <?php endif; ?>
            </div>
        </section>

        <section class="player-rankings">
            <h2>Player Ranking</h2>
            <?php if($playerRank): ?>
            <div class="ranking-card">
                <h3>Current Ranking: <?php echo $playerRank['player_rank']; ?> of <?php echo $playerRank['total_players']; ?></h3>
                <p><strong>Total Points:</strong> <?php echo $playerRank['total_points']; ?></p>
                <p><strong>Total Game Time:</strong> <?php echo formatTime($playerRank['total_playtime']); ?></p>
                <p><strong>Games Played:</strong> <?php echo $playerRank['times_played']; ?></p>
                <p><strong>Last Played:</strong> <?php echo $playerRank['last_played_date']; ?></p>
            </div>
            <?php else: ?>
            <p>This player hasn't played any games yet.</p>
            <?php endif; ?>
        </section>

        <section class="recent-sessions">
            <h2>Recent Game Sessions</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">Session ID</th>
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
                if($sessions && $sessions->num_rows > 0):
                    while($session = $sessions->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $session['session_id']; ?></td>
                        <td><?php echo $session['game_description']; ?></td>
                        <td><?php echo $session['start_time']; ?></td>
                        <td><?php echo $session['end_time']; ?></td>
                        <td><?php echo formatTime($session['duration']); ?></td>
                        <td><?php echo $session['win_lose_status'] ? 'Victory' : 'Defeat'; ?></td>
                        <td><?php echo $session['points_gained']; ?></td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="7">No sessions for this player yet</td>
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
