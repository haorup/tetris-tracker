# Tetris Tracker

## Project Overview
Tetris Tracker is a web application that records and analyzes player performance across various Tetris game versions. The application tracks game sessions, player achievements, and maintains statistics for different game modes and difficulty levels.

## Database Structure

The database schema consists of the following tables:

1. **Player** - Stores basic player information such as name, username, and registration date.

2. **Game** - Contains information about different Tetris game versions including game name, release date, and description.

3. **Difficulty** - Records difficulty levels for each game and the corresponding high scores.

4. **Action** - Documents the allowed actions/controls in each game.

5. **Item** - Catalogs items available within games.

6. **Achievement** - Stores achievement requirements, including score thresholds and time requirements. Players must earn all possible achievements to complete a game.

7. **Session** - Records individual play sessions with unique IDs, including start/end times, scores achieved, player ID, and game ID.

8. **PlayerGame** - Maintains the relationship between players and games they've played, including highest score, play count, and cumulative play time for each game.

9. **PlayerAchievement** - Tracks achievements earned by players.

## Installation Instructions

### Prerequisites
- XAMPP installed on your system
- Web browser

### Setup Steps
1. Start XAMPP:
   - Launch XAMPP Control Panel
   - Start Apache and MySQL services

2. Create Database:
   - Click on "MySQL Admin" to open phpMyAdmin
   - Create a new database
   - Database credentials:
     - Username: root
     - Password: root

3. Application Installation:
   - Place the application folder in your XAMPP htdocs directory
   - Full path should be: `xampp\xam\htdocs\PhpLab\tetris-tracker`

4. Access the Application:
   - Open your web browser
   - Navigate to: http://localhost/PhpLab/tetris-tracker/

## Features
- Track player performance across multiple Tetris games
- Record individual game sessions with detailed metrics
- Monitor achievement progress
- View historical statistics and performance trends
- Compare player scores and achievements
