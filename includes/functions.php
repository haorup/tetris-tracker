<?php
// Get top players with achievements (Join Query)
function getTopPlayersWithAchievements($conn, $limit = 5) {
    $sql = "SELECT p.player_id, p.username, COUNT(pa.achievement_id) as total_achievements, 
            SUM(pg.highest_score) as total_score
            FROM Player p
            JOIN PlayerGame pg ON p.player_id = pg.player_id
            LEFT JOIN PlayerAchievement pa ON p.player_id = pa.player_id
            GROUP BY p.player_id, p.username
            ORDER BY total_score DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Get average playtime per player (Aggregation Query)
function getAveragePlaytimePerPlayer($conn, $dateCondition = "", $gameCondition = "") {
    $sql = "SELECT p.player_id, p.username, AVG(s.duration) as avg_playtime
            FROM Player p
            JOIN Session s ON p.player_id = s.player_id
            JOIN Game g ON s.game_id = g.game_id
            WHERE 1=1 $dateCondition $gameCondition
            GROUP BY p.player_id, p.username
            ORDER BY avg_playtime DESC";
    
    $result = $conn->query($sql);
    return $result;
}

// Get total playtime per week by player (Nested aggregation with Group-By)
function getTotalPlaytimePerWeekByPlayer($conn, $dateCondition = "", $gameCondition = "") {
    $sql = "SELECT p.player_id, p.username, 
            YEARWEEK(s.start_time) as week_number,
            SUM(s.duration) as total_weekly_playtime
            FROM Player p
            JOIN Session s ON p.player_id = s.player_id
            JOIN Game g ON s.game_id = g.game_id
            WHERE 1=1 $dateCondition $gameCondition
            GROUP BY p.player_id, p.username, YEARWEEK(s.start_time)
            ORDER BY p.player_id, week_number";
    
    $result = $conn->query($sql);
    return $result;
}

// Get top N players by score (Filtering and ranking query)
function getTopPlayersByScore($conn, $limit = 5) {
    $sql = "SELECT p.player_id, p.username, pg.highest_score, g.game_id, g.game_genre
            FROM Player p
            JOIN PlayerGame pg ON p.player_id = pg.player_id
            JOIN Game g ON pg.game_id = g.game_id
            ORDER BY pg.highest_score DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Update player profile (Update operation)
function updatePlayerProfile($conn, $player_id, $username, $email, $date_registration, $date_of_birth, $country, $subscription_type) {
    $sql = "UPDATE Player 
            SET username = ?, email_address = ?, date_registration = ?, date_of_birth = ?, country = ?, subscription_type = ?
            WHERE player_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $username, $email, $date_registration, $date_of_birth, $country, $subscription_type, $player_id);
    return $stmt->execute();
}

// Delete player and all related data (Cascade delete operation)
function deletePlayer($conn, $player_id) {
    // Start transaction to ensure data integrity
    $conn->begin_transaction();
    
    try {
        // Delete from PlayerAchievement table
        $sql1 = "DELETE FROM PlayerAchievement WHERE player_id = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $player_id);
        $stmt1->execute();
        
        // Delete from Session table
        $sql2 = "DELETE FROM Session WHERE player_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $player_id);
        $stmt2->execute();
        
        // Delete from PlayerGame table
        $sql3 = "DELETE FROM PlayerGame WHERE player_id = ?";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("i", $player_id);
        $stmt3->execute();
        
        // Delete from Player table
        $sql4 = "DELETE FROM Player WHERE player_id = ?";
        $stmt4 = $conn->prepare($sql4);
        $stmt4->bind_param("i", $player_id);
        $stmt4->execute();
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return false;
    }
}

// Calculate completion percentage
function calculateCompletionPercentage($current_points, $required_points) {
    if ($required_points <= 0) return 0;
    $percentage = ($current_points / $required_points) * 100;
    return min(100, max(0, $percentage)); // Ensure within 0-100 range
}

// Format time display (seconds to readable format)
function formatTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    if ($hours > 0) {
        return "$hours hours $minutes minutes";
    } else {
        return "$minutes minutes";
    }
}

// Get game items
function getGameItems($conn, $game_id) {
    $sql = "SELECT * FROM Item WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Get player achievements
function getPlayerAchievements($conn, $player_id) {
    $sql = "SELECT pa.*, a.achievement_type, a.description, a.required_points, a.required_playtime
            FROM PlayerAchievement pa
            JOIN Achievement a ON pa.achievement_id = a.achievement_id
            WHERE pa.player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Get player game statistics
function getPlayerGameStats($conn, $player_id) {
    $sql = "SELECT pg.*, g.game_genre, g.game_description
            FROM PlayerGame pg
            JOIN Game g ON pg.game_id = g.game_id
            WHERE pg.player_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Get game action configuration
function getGameAction($conn, $game_id) {
    $sql = "SELECT a.* 
            FROM Action a
            JOIN Game g ON a.action_id = g.action_id
            WHERE g.game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Safely get request parameter
function getParam($name, $default = null) {
    return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default);
}

// Display status message
function showAlert($message, $type = 'success') {
    return "<div class='alert $type'>$message</div>";
}

// Generate options for select dropdown
function generateOptions($items, $valueField, $textField, $selectedValue = null) {
    $html = '';
    foreach ($items as $item) {
        $selected = ($item[$valueField] == $selectedValue) ? 'selected' : '';
        $html .= "<option value='{$item[$valueField]}' $selected>{$item[$textField]}</option>";
    }
    return $html;
}

// Get top N players by achievement completion
function getTopAchieversByCompletion($conn, $limit = 5) {
    $sql = "SELECT p.player_id, p.username, 
            COUNT(CASE WHEN pa.is_completed = TRUE THEN 1 ELSE NULL END) as completed_count,
            COUNT(pa.achievement_id) as total_count,
            (COUNT(CASE WHEN pa.is_completed = TRUE THEN 1 ELSE NULL END) * 100 / COUNT(pa.achievement_id)) as completion_rate
            FROM Player p
            JOIN PlayerAchievement pa ON p.player_id = pa.player_id
            GROUP BY p.player_id, p.username
            HAVING COUNT(pa.achievement_id) > 0
            ORDER BY completion_rate DESC, completed_count DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result();
}
?>