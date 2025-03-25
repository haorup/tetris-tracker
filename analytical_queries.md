# Tetris Performance Tracking System: Analytical Queries

This document outlines the key analytical queries implemented in the Tetris Performance Tracking System, along with their descriptions and results.

## 1. Join Query: Top Players with Unlocked Achievements

**Plain English:** Display top players and their unlocked achievements by joining the Players, Achievements, and Sessions tables.

**SQL Query:**
```sql
SELECT p.player_id, p.username, COUNT(pa.achievement_id) as total_achievements,
       SUM(pg.highest_score) as total_score
FROM Player p
JOIN PlayerGame pg ON p.player_id = pg.player_id
LEFT JOIN PlayerAchievement pa ON p.player_id = pa.player_id
GROUP BY p.player_id, p.username
ORDER BY total_achievements DESC, total_score DESC
LIMIT 10;
```

**Result:** This query returns the top 10 players along with their total achievements and cumulative scores. It helps identify the most accomplished players in the system by counting achievements and summing scores across all games.

## 2. Aggregation Query: Average Playtime per Player

**Plain English:** Compute average playtime per player across all their game sessions.

**SQL Query:**
```sql
SELECT p.player_id, p.username, AVG(s.duration) as avg_playtime
FROM Player p
JOIN Session s ON p.player_id = s.player_id
GROUP BY p.player_id, p.username
ORDER BY avg_playtime DESC;
```

**Result:** Returns each player with their average playtime across all game sessions, helping to identify which players spend the most time per game session on average. This reveals player engagement patterns.

## 3. Aggregation Query: Total Achievements per Game

**Plain English:** Calculate the total number of achievements earned per game.

**SQL Query:**
```sql
SELECT g.game_id, g.game_description,
       COUNT(DISTINCT pa.achievement_id) as achievement_count,
       COUNT(DISTINCT pa.player_id) as players_count,
       ROUND((SUM(CASE WHEN pa.is_completed = 1 THEN 1 ELSE 0 END) * 100.0 /
             NULLIF(COUNT(pa.achievement_id), 0)), 1) as completion_rate
FROM Game g
LEFT JOIN Session s ON g.game_id = s.game_id
LEFT JOIN PlayerAchievement pa ON s.player_id = pa.player_id
GROUP BY g.game_id, g.game_description
ORDER BY achievement_count DESC, completion_rate DESC;
```

**Result:** This query shows how many achievements have been unlocked in each game, along with the number of players who have earned achievements and the overall completion rate. It helps identify which games have the most engaging achievement systems.

## 4. Nested Aggregation with Group-By: Total Playtime per Week by Player

**Plain English:** Find total playtime per week grouped by player.

**SQL Query:**
```sql
SELECT p.player_id, p.username,
       YEARWEEK(s.start_time) as week_number,
       SUM(s.duration) as total_weekly_playtime
FROM Player p
JOIN Session s ON p.player_id = s.player_id
GROUP BY p.player_id, p.username, YEARWEEK(s.start_time)
ORDER BY p.player_id, week_number;
```

**Result:** Shows how much time each player spends playing games on a weekly basis, allowing trend analysis of player engagement over time. This query is particularly useful for identifying patterns in play behavior and tracking changes in engagement.

## 5. Filtering & Ranking Query: Top 5 Players with Highest Scores

**Plain English:** Display the top 5 players with the highest scores dynamically.

**SQL Query:**
```sql
SELECT p.player_id, p.username, pg.highest_score, g.game_id, g.game_genre
FROM Player p
JOIN PlayerGame pg ON p.player_id = pg.player_id
JOIN Game g ON pg.game_id = g.game_id
ORDER BY pg.highest_score DESC
LIMIT 5;
```

**Result:** Returns the five players with the highest scores across all games, showing which game they achieved their highest score in. This query powers leaderboards and identifies top performers in the system.

## 6. Update Operation: Modify Player Profile

**Plain English:** Allow users to modify player profile details through a web form.

**SQL Query:**
```sql
UPDATE Player
SET username = ?, email_address = ?, date_registration = ?,
    date_of_birth = ?, country = ?, subscription_type = ?
WHERE player_id = ?;
```

**Result:** Updates a player's profile information in the database, allowing for maintenance of accurate player records. This operation ensures player data remains current and accurate.

## 7. Delete Operation with Cascade: Delete Player and Related Data

**Plain English:** Ensure deleting a player removes related sessions and achievements automatically.

**SQL Query (Transaction):**
```sql
BEGIN TRANSACTION;

DELETE FROM PlayerAchievement WHERE player_id = ?;
DELETE FROM Session WHERE player_id = ?;
DELETE FROM PlayerGame WHERE player_id = ?;
DELETE FROM Player WHERE player_id = ?;

COMMIT;
```

**Result:** Removes a player and all their related records from the database, maintaining data integrity. This transaction ensures that no orphaned records remain after a player is deleted.

## 8. Time-Filtered Query: Session Statistics

**Plain English:** Get game session statistics filtered by a specific time period.

**SQL Query:**
```sql
SELECT COUNT(*) as total_sessions,
       AVG(s.duration) as avg_session_time,
       SUM(CASE WHEN win_lose_status = TRUE THEN 1 ELSE 0 END) as wins,
       COUNT(*) as total,
       (SUM(CASE WHEN win_lose_status = TRUE THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as win_rate,
       AVG(points_gained) as avg_points
FROM Session s
WHERE s.start_time >= DATE_SUB(NOW(), INTERVAL ? DAY);
```

**Result:** Returns comprehensive statistics about game sessions within a specified time period, including total sessions, average duration, win rate, and average points earned. This time-filtered analysis provides insights into recent gameplay trends.

## 9. Dashboard Query: Most Active Player

**Plain English:** Find the most active player based on the number of game sessions.

**SQL Query:**
```sql
SELECT p.username, COUNT(s.session_id) as session_count
FROM Player p
JOIN Session s ON p.player_id = s.player_id
JOIN Game g ON s.game_id = g.game_id
WHERE s.start_time >= DATE_SUB(NOW(), INTERVAL ? DAY)
GROUP BY p.player_id, p.username
ORDER BY session_count DESC
LIMIT 1;
```

**Result:** Identifies the player who has played the most game sessions within a given time period, which is a key metric displayed on the dashboard. This information highlights the most engaged player in the community.

## 10. Dashboard Query: Player Achievement Completion Rates

**Plain English:** Calculate achievement completion rates for players.

**SQL Query:**
```sql
SELECT p.player_id, p.username,
       COUNT(pa.achievement_id) as achievement_count,
       (COUNT(pa.achievement_id) * 100 / (SELECT COUNT(*) FROM Achievement)) as completion_percentage
FROM Player p
JOIN PlayerAchievement pa ON p.player_id = pa.player_id
GROUP BY p.player_id, p.username
ORDER BY achievement_count DESC
LIMIT 5;
```

**Result:** Shows how many achievements each player has completed and their overall completion percentage of all available achievements in the system. This is useful for identifying players who are most engaged with the achievement system.

## 11. Game Popularity Query

**Plain English:** Identify the most popular games based on the number of players.

**SQL Query:**
```sql
SELECT g.game_genre, COUNT(DISTINCT s.player_id) as player_count
FROM Game g
JOIN Session s ON g.game_id = s.game_id
GROUP BY g.game_id, g.game_genre
ORDER BY player_count DESC
LIMIT 1;
```

**Result:** Returns the most popular game genre based on the number of unique players who have played it. This helps identify which games are attracting the most players.

## 12. Player Statistics by Subscription Type

**Plain English:** Analyze player performance metrics grouped by subscription type (free vs paid).

**SQL Query:**
```sql
SELECT p.subscription_type,
       COUNT(DISTINCT p.player_id) as player_count,
       AVG(pg.highest_score) as avg_highest_score,
       MAX(pg.highest_score) as max_score,
       AVG(s.duration) as avg_playtime,
       COUNT(pa.achievement_id)/COUNT(DISTINCT p.player_id) as avg_achievements_per_player
FROM Player p
LEFT JOIN PlayerGame pg ON p.player_id = pg.player_id
LEFT JOIN Session s ON p.player_id = s.player_id
LEFT JOIN PlayerAchievement pa ON p.player_id = pa.player_id
GROUP BY p.subscription_type
ORDER BY avg_highest_score DESC;
```

**Result:** This query compares performance metrics between free and paid subscription users, showing player counts, average and maximum scores, average playtime, and achievements per player for each subscription type. It helps determine if paid subscribers demonstrate better performance metrics than free users.
