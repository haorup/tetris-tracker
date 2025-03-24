<?php
include 'includes/db_connection.php';
include 'includes/functions.php';

// Handle game deletion
if(isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $game_id = $_GET['delete_id'];

    // Start transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // Delete from Item table
        $stmt = $conn->prepare("DELETE FROM Item WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();

        // Delete from PlayerGame table
        $stmt = $conn->prepare("DELETE FROM PlayerGame WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();

        // Delete from Session table
        $stmt = $conn->prepare("DELETE FROM Session WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();

        // Delete from Game table
        $stmt = $conn->prepare("DELETE FROM Game WHERE game_id = ?");
        $stmt->bind_param("i", $game_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        $success_message = "Game successfully deleted!";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error_message = "Error deleting game: " . $e->getMessage();
    }
}

// Get all games
$sql = "SELECT g.game_id, g.game_genre, g.ai_help_allowed, d.max_points,
        g.difficulty_level, g.game_description, a.action_id,
        COUNT(DISTINCT i.item_id) as item_count,
        COUNT(DISTINCT pg.player_id) as player_count
        FROM Game g
        LEFT JOIN DifficultyLevel d ON g.difficulty_level = d.difficulty_level
        LEFT JOIN Action a ON g.action_id = a.action_id
        LEFT JOIN Item i ON g.game_id = i.game_id
        LEFT JOIN PlayerGame pg ON g.game_id = pg.game_id
        GROUP BY g.game_id, g.game_genre, g.ai_help_allowed, d.max_points, g.difficulty_level,
                g.game_description, a.action_id
        ORDER BY g.game_id";
$games = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Management - Tetris Performance Tracking System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Game Management</h1>
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

        <section class="game-list">
            <h2>Game List</h2>
            <table class="sortable">
                <thead>
                    <tr>
                        <th class="sortable-header">ID</th>
                        <th class="sortable-header">Game Type</th>
                        <th class="sortable-header">AI Assist</th>
                        <th class="sortable-header">Difficulty Level</th>
                        <th class="sortable-header">Max Points</th>
                        <th class="sortable-header">Game Items</th>
                        <th class="sortable-header">Players</th>
                        <th class="sortable-header">Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if($games && $games->num_rows > 0) {
                    while($game = $games->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $game['game_id']; ?></td>
                        <td><?php echo $game['game_genre']; ?></td>
                        <td><?php echo $game['ai_help_allowed'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $game['difficulty_level']; ?></td>
                        <td><?php echo $game['max_points']; ?></td>
                        <td><?php echo $game['item_count']; ?></td>
                        <td><?php echo $game['player_count']; ?></td>
                        <td><?php echo $game['game_description']; ?></td>
                        <td>
                            <a href="game_details.php?id=<?php echo $game['game_id']; ?>" class="btn small">View Details</a>

                        </td>
                    </tr>
                <?php
                    endwhile;
                } else {
                    echo "<tr><td colspan='9'>No games found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </section>

        <section class="game-stats">
            <h2>Game Statistics</h2>
            <div class="stats-grid">
                <?php
                // Get total game count
                $result = $conn->query("SELECT COUNT(*) as total FROM Game");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Total Games</h3>
                    <p class="stat-number"><?php echo $row['total']; ?></p>
                </div>

                <?php
                // Get most played game
                $result = $conn->query("SELECT g.game_genre, COUNT(DISTINCT s.player_id) as player_count
                                      FROM Game g
                                      JOIN Session s ON g.game_id = s.game_id
                                      GROUP BY g.game_id, g.game_genre
                                      ORDER BY player_count DESC
                                      LIMIT 1");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Most Popular Game</h3>
                    <p class="stat-number"><?php echo isset($row['game_genre']) ? $row['game_genre'] : 'N/A'; ?></p>
                    <p><?php echo isset($row['player_count']) ? $row['player_count'] . ' players' : ''; ?></p>
                </div>

                <?php
                // Get average difficulty level
                $result = $conn->query("SELECT AVG(difficulty_level) as avg_difficulty FROM Game");
                $row = $result->fetch_assoc();
                ?>
                <div class="stat-card">
                    <h3>Average Difficulty</h3>
                    <p class="stat-number"><?php echo round($row['avg_difficulty'], 1); ?></p>
                </div>

                <?php
                // Get AI-assisted game percentage
                $result = $conn->query("SELECT
                                       (SELECT COUNT(*) FROM Game WHERE ai_help_allowed = TRUE) as ai_games,
                                       COUNT(*) as total_games
                                       FROM Game");
                $row = $result->fetch_assoc();
                $aiPercentage = ($row['total_games'] > 0) ? round(($row['ai_games'] / $row['total_games']) * 100, 1) : 0;
                ?>
                <div class="stat-card">
                    <h3>AI-Assisted Games Ratio</h3>
                    <p class="stat-number"><?php echo $aiPercentage; ?>%</p>
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