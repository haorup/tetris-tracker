<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: games.php");
    exit;
}

$game_id = $_GET['id'];

// Get game information
$sql = "SELECT g.*, d.max_points
        FROM Game g
        LEFT JOIN DifficultyLevel d ON g.difficulty_level = d.difficulty_level
        WHERE g.game_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    header("Location: games.php");
    exit;
}

$game = $result->fetch_assoc();

// Get game action configuration
$action = getGameAction($conn, $game_id);

// Get game items
$items = getGameItems($conn, $game_id);

// Get game sessions
$sql = "SELECT s.*, p.username
        FROM Session s
        JOIN Player p ON s.player_id = p.player_id
        WHERE s.game_id = ?
        ORDER BY s.start_time DESC
        LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$sessions = $stmt->get_result();

// Get player game statistics
$sql = "SELECT pg.*, p.username
        FROM PlayerGame pg
        JOIN Player p ON pg.player_id = p.player_id
        WHERE pg.game_id = ?
        ORDER BY pg.highest_score DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$playerGames = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $game['game_genre']; ?> - Game Details - Tetris Performance Tracking System</title>
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
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Game Details: <?php echo $game['game_genre']; ?></h1>
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
        <section class="game-info">
            <h2>Basic Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>ID:</strong> <?php echo $game['game_id']; ?>
                </div>
                <div class="info-item">
                    <strong>Game Type:</strong> <?php echo $game['game_genre']; ?>
                </div>
                <div class="info-item">
                    <strong>AI Assistance:</strong> <?php echo $game['ai_help_allowed'] ? 'Enabled' : 'Disabled'; ?>
                </div>
                <div class="info-item">
                    <strong>Difficulty Level:</strong> <?php echo $game['difficulty_level']; ?>
                </div>
                <div class="info-item">
                    <strong>Max Points:</strong> <?php echo $game['max_points']; ?>
                </div>
                <div class="info-item">
                    <strong>Description:</strong> <?php echo $game['game_description']; ?>
                </div>
            </div>

        </section>

        <section class="game-controls">
            <h2>Game Control Configuration</h2>
            <p><strong>Action ID:</strong> <?php echo $action['action_id']; ?></p>
            <p><strong>Action Description:</strong> <?php echo $action['description']; ?></p>

            <div class="controls-grid">
                <div class="control-item <?php echo $action['allowLeftArrow'] ? 'control-enabled' : 'control-disabled'; ?>">
                    <h3>Left Arrow</h3>
                    <p><?php echo $action['allowLeftArrow'] ? 'Enabled' : 'Disabled'; ?></p>
                </div>
                <div class="control-item <?php echo $action['allowRightArrow'] ? 'control-enabled' : 'control-disabled'; ?>">
                    <h3>Right Arrow</h3>
                    <p><?php echo $action['allowRightArrow'] ? 'Enabled' : 'Disabled'; ?></p>
                </div>
                <div class="control-item <?php echo $action['allowUpArrow'] ? 'control-enabled' : 'control-disabled'; ?>">
                    <h3>Up Arrow</h3>
                    <p><?php echo $action['allowUpArrow'] ? 'Enabled' : 'Disabled'; ?></p>
                </div>
                <div class="control-item <?php echo $action['allowDownArrow'] ? 'control-enabled' : 'control-disabled'; ?>">
                    <h3>Down Arrow</h3>
                    <p><?php echo $action['allowDownArrow'] ? 'Enabled' : 'Disabled'; ?></p>
                </div>
                <div class="control-item <?php echo $action['allowSpace'] ? 'control-enabled' : 'control-disabled'; ?>">
                    <h3>Spacebar</h3>
                    <p><?php echo $action['allowSpace'] ? 'Enabled' : 'Disabled'; ?></p>
                </div>
            </div>
        </section>

        <section class="game-items">
            <h2>Game Items</h2>
            <?php if($items && $items->num_rows > 0): ?>
                <div class="items-grid">
                <?php while($item = $items->fetch_assoc()): ?>
                    <div class="item-card">
                        <h3>Item ID: <?php echo $item['item_id']; ?></h3>
                        <div class="color-sample" style="background-color: <?php echo $item['color']; ?>"></div>
                        <p><strong>Color:</strong> <?php echo $item['color']; ?></p>
                        <p><strong>Shape:</strong> <?php echo $item['shape']; ?></p>
                        <p><strong>Position:</strong> <?php echo $item['position_on_board']; ?></p>
                        <p><strong>Appearance Time:</strong> <?php echo $item['time_to_appear']; ?> seconds</p>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No items defined for this game yet</p>
            <?php endif; ?>
        </section>

        <section class="player-rankings">
            <h2>Player Rankings</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">Rank</th>
                        <th class="sortable-header">Player</th>
                        <th class="sortable-header">Highest Score</th>
                        <th class="sortable-header">Total Game Time</th>
                        <th class="sortable-header">Times Played</th>
                        <th class="sortable-header">Last Played Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $rank = 1;
                if($playerGames && $playerGames->num_rows > 0):
                    while($pg = $playerGames->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $rank++; ?></td>
                        <td><?php echo $pg['username']; ?></td>
                        <td><?php echo $pg['highest_score']; ?></td>
                        <td><?php echo formatTime($pg['total_playtime']); ?></td>
                        <td><?php echo $pg['times_played']; ?></td>
                        <td><?php echo $pg['last_played_date']; ?></td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="6">No players have played this game yet</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="recent-sessions">
            <h2>Recent Game Sessions</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">Session ID</th>
                        <th class="sortable-header">Player</th>
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
                        <td><?php echo $session['username']; ?></td>
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
                        <td colspan="7">No sessions for this game yet</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Tetris Performance Tracking System - CS 5200 Practicum 1</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>