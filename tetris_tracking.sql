-- Create Tables
CREATE TABLE Player (
    player_id INT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email_address VARCHAR(100) NOT NULL UNIQUE,
    date_registration DATE NOT NULL,
    date_of_birth DATE,
    country VARCHAR(50),
    subscription_type ENUM('free','paid') NOT NULL DEFAULT 'free'
);

CREATE TABLE Action (
    action_id INT PRIMARY KEY,
    allowLeftArrow BOOLEAN DEFAULT TRUE,
    allowRightArrow BOOLEAN DEFAULT TRUE,
    allowUpArrow BOOLEAN DEFAULT TRUE,
    allowDownArrow BOOLEAN DEFAULT TRUE,
    allowSpace BOOLEAN DEFAULT TRUE,
    description VARCHAR(90)
);

CREATE TABLE DifficultyLevel (
    difficulty_level INT PRIMARY KEY,
    max_points INT NOT NULL
);

CREATE TABLE Game (
    game_id INT PRIMARY KEY,
    action_id INT NOT NULL,
    game_genre VARCHAR(50),
    ai_help_allowed BOOLEAN DEFAULT FALSE,
    difficulty_level INT,
    game_description VARCHAR(90),
    FOREIGN KEY (action_id) REFERENCES Action(action_id),
    FOREIGN KEY (difficulty_level) REFERENCES DifficultyLevel(difficulty_level)
);

CREATE TABLE Item (
    game_id INT,
    item_id VARCHAR(50),
    color VARCHAR(20),
    shape VARCHAR(20),
    position_on_board VARCHAR(20),
    time_to_appear INT,
    PRIMARY KEY (game_id, item_id),
    FOREIGN KEY (game_id) REFERENCES Game(game_id) ON DELETE CASCADE
);

CREATE TABLE PlayerGame (
    player_id INT,
    game_id INT,
    times_played INT NOT NULL DEFAULT 0,
    highest_score INT DEFAULT 0,
    total_playtime INT DEFAULT 0,
    last_played_date DATETIME,
    PRIMARY KEY (player_id, game_id),
    FOREIGN KEY (player_id) REFERENCES Player(player_id),
    FOREIGN KEY (game_id) REFERENCES Game(game_id)
);

CREATE TABLE Session (
    session_id INT PRIMARY KEY,
    player_id INT NOT NULL,
    game_id INT NOT NULL,
    start_time DATETIME,
    end_time DATETIME,
    duration INT NOT NULL DEFAULT 1,
    win_lose_status BOOLEAN,
    points_gained INT DEFAULT 0,
    FOREIGN KEY (player_id) REFERENCES Player(player_id),
    FOREIGN KEY (game_id) REFERENCES Game(game_id),
    FOREIGN KEY (player_id, game_id) REFERENCES PlayerGame(player_id, game_id)
);

CREATE TABLE Achievement (
    achievement_id INT PRIMARY KEY,
    achievement_type VARCHAR(50) NOT NULL,
    description VARCHAR(50),
    difficulty_level INT NOT NULL,
    required_points INT NOT NULL,
    required_playtime INT
);

CREATE TABLE PlayerAchievement (
    player_id INT,
    achievement_id INT,
    current_total_points INT DEFAULT 0,
    current_total_playtime INT DEFAULT 0,
    date_started DATETIME NOT NULL,
    date_completed DATETIME,
    is_completed BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (player_id, achievement_id),
    FOREIGN KEY (player_id) REFERENCES Player(player_id),
    FOREIGN KEY (achievement_id) REFERENCES Achievement(achievement_id)
);

-- Insert Data (25 rows per table)
-- Player
INSERT INTO Player (player_id, username, email_address, date_registration, date_of_birth, country, subscription_type)
VALUES
(1, 'user1', 'user1@example.com', '2025-03-01', '1990-01-01', 'USA', 'free'),
(2, 'user2', 'user2@example.com', '2025-03-02', '1990-02-02', 'Canada', 'paid'),
(3, 'user3', 'user3@example.com', '2025-03-03', '1990-03-03', 'UK', 'free'),
(4, 'user4', 'user4@example.com', '2025-03-04', '1990-04-04', 'Germany', 'paid'),
(5, 'user5', 'user5@example.com', '2025-03-05', '1990-05-05', 'France', 'free'),
(6, 'user6', 'user6@example.com', '2025-03-06', '1990-06-06', 'Japan', 'paid'),
(7, 'user7', 'user7@example.com', '2025-03-07', '1990-07-07', 'Australia', 'free'),
(8, 'user8', 'user8@example.com', '2025-03-08', '1990-08-08', 'Brazil', 'paid'),
(9, 'user9', 'user9@example.com', '2025-03-09', '1990-09-09', 'India', 'free'),
(10, 'user10', 'user10@example.com', '2025-03-10', '1990-10-10', 'China', 'paid'),
(11, 'user11', 'user11@example.com', '2025-03-11', '1991-01-11', 'South Korea', 'free'),
(12, 'user12', 'user12@example.com', '2025-03-12', '1991-02-12', 'Mexico', 'paid'),
(13, 'user13', 'user13@example.com', '2025-03-13', '1991-03-13', 'Italy', 'free'),
(14, 'user14', 'user14@example.com', '2025-03-14', '1991-04-14', 'Spain', 'paid'),
(15, 'user15', 'user15@example.com', '2025-03-15', '1991-05-15', 'Russia', 'free'),
(16, 'user16', 'user16@example.com', '2025-03-16', '1991-06-16', 'South Africa', 'paid'),
(17, 'user17', 'user17@example.com', '2025-03-17', '1991-07-17', 'Argentina', 'free'),
(18, 'user18', 'user18@example.com', '2025-03-18', '1991-08-18', 'Sweden', 'paid'),
(19, 'user19', 'user19@example.com', '2025-03-19', '1991-09-19', 'Netherlands', 'free'),
(20, 'user20', 'user20@example.com', '2025-03-20', '1991-10-20', 'Singapore', 'paid'),
(21, 'user21', 'user21@example.com', '2025-03-21', '1992-01-21', 'New Zealand', 'free'),
(22, 'user22', 'user22@example.com', '2025-03-22', '1992-02-22', 'Ireland', 'paid'),
(23, 'user23', 'user23@example.com', '2025-03-23', '1992-03-23', 'Poland', 'free'),
(24, 'user24', 'user24@example.com', '2025-03-24', '1992-04-24', 'Thailand', 'paid'),
(25, 'user25', 'user25@example.com', '2025-03-25', '1992-05-25', 'Egypt', 'free');

-- Action
INSERT INTO Action (action_id, allowLeftArrow, allowRightArrow, allowUpArrow, allowDownArrow, allowSpace, description)
VALUES
(1, TRUE, TRUE, TRUE, TRUE, TRUE, 'All actions enabled'),
(2, TRUE, TRUE, TRUE, TRUE, FALSE, 'All movement allowed, no dropping'),
(3, TRUE, TRUE, TRUE, FALSE, TRUE, 'No downward movement, can rotate and drop'),
(4, TRUE, TRUE, TRUE, FALSE, FALSE, 'Horizontal and rotation only'),
(5, TRUE, TRUE, FALSE, TRUE, TRUE, 'No rotation, all other moves allowed'),
(6, TRUE, TRUE, FALSE, TRUE, FALSE, 'Left/right and down movements only'),
(7, TRUE, TRUE, FALSE, FALSE, TRUE, 'Horizontal movement and instant drop only'),
(8, TRUE, TRUE, FALSE, FALSE, FALSE, 'Horizontal movement only'),
(9, TRUE, FALSE, TRUE, TRUE, TRUE, 'No right movement, all others allowed'),
(10, TRUE, FALSE, TRUE, TRUE, FALSE, 'Left, up and down movements only'),
(11, TRUE, FALSE, TRUE, FALSE, TRUE, 'Left movement, rotation and dropping'),
(12, TRUE, FALSE, TRUE, FALSE, FALSE, 'Left movement and rotation only'),
(13, TRUE, FALSE, FALSE, TRUE, TRUE, 'Left and down movements with dropping'),
(14, TRUE, FALSE, FALSE, TRUE, FALSE, 'Left and down movements only'),
(15, TRUE, FALSE, FALSE, FALSE, TRUE, 'Left movement and instant drop only'),
(16, TRUE, FALSE, FALSE, FALSE, FALSE, 'Left movement only'),
(17, FALSE, TRUE, TRUE, TRUE, TRUE, 'No left movement, all others allowed'),
(18, FALSE, TRUE, TRUE, TRUE, FALSE, 'Right, up and down movements only'),
(19, FALSE, TRUE, TRUE, FALSE, TRUE, 'Right movement, rotation and dropping'),
(20, FALSE, TRUE, TRUE, FALSE, FALSE, 'Right movement and rotation only'),
(21, FALSE, TRUE, FALSE, TRUE, TRUE, 'Right and down movements with dropping'),
(22, FALSE, TRUE, FALSE, TRUE, FALSE, 'Right and down movements only'),
(23, FALSE, TRUE, FALSE, FALSE, TRUE, 'Right movement and instant drop only'),
(24, FALSE, TRUE, FALSE, FALSE, FALSE, 'Right movement only'),
(25, FALSE, FALSE, TRUE, TRUE, TRUE, 'Rotation, down movement and dropping only');

-- DifficultyLevel
INSERT INTO DifficultyLevel (difficulty_level, max_points)
VALUES
(1, 100),
(2, 200),
(3, 300),
(4, 400),
(5, 500),
(6, 600),
(7, 700),
(8, 800),
(9, 900),
(10, 1000),
(11, 1100),
(12, 1200),
(13, 1300),
(14, 1400),
(15, 1500),
(16, 1600),
(17, 1700),
(18, 1800),
(19, 1900),
(20, 2000),
(21, 2100),
(22, 2200),
(23, 2300),
(24, 2400),
(25, 2500);

-- Game
INSERT INTO Game (game_id, action_id, game_genre, ai_help_allowed, difficulty_level, game_description)
VALUES
(1, 1, 'Classic', FALSE, 1, 'Traditional tetris gameplay with standard blocks and increasing speed'),
(2, 2, 'Space', TRUE, 2, 'Cosmic-themed blocks descend with zero-gravity physics and asteroid obstacles'),
(3, 3, 'Underwater', FALSE, 3, 'Ocean-themed blocks with water current effects and marine creature bonuses'),
(4, 4, 'Jungle', TRUE, 4, 'Rainforest-themed with vine-swinging mechanics and wild animal power-ups'),
(5, 5, 'Medieval', FALSE, 5, 'Castle-building blocks with knight and dragon special pieces'),
(6, 6, 'Futuristic', TRUE, 6, 'Neon blocks with holographic effects and time-manipulation mechanics'),
(7, 7, 'Arcade', FALSE, 7, 'Retro-styled with pixel graphics and classic arcade sound effects'),
(8, 8, 'Candy', TRUE, 8, 'Sweet-themed blocks with matching flavor bonuses and sugar rush events'),
(9, 9, 'Battle', FALSE, 9, 'Competitive mode with attack blocks that can be sent to opponents'),
(10, 10, 'Fantasy', TRUE, 10, 'Magical-themed with spell casting abilities and enchanted blocks'),
(11, 11, 'Steampunk', FALSE, 11, 'Industrial gear-shaped pieces with steam power mechanics'),
(12, 12, 'Horror', TRUE, 12, 'Spooky-themed with ghost blocks that phase through others randomly'),
(13, 13, 'Winter', FALSE, 13, 'Ice blocks that slide further and snow effects that slow falling'),
(14, 14, 'Desert', TRUE, 14, 'Sand-themed with sandstorm events that obscure the playing field'),
(15, 15, 'Racing', FALSE, 15, 'Speed-focused with car-shaped special pieces and lap bonus scoring'),
(16, 16, 'Cityscape', TRUE, 16, 'Building-themed where completed lines construct a growing skyline'),
(17, 17, 'Fairy Tale', FALSE, 17, 'Enchanted blocks with storybook characters and magical transformations'),
(18, 18, 'Dungeon', TRUE, 18, 'Labyrinth-themed with treasure blocks and monster encounters'),
(19, 19, 'Musical', FALSE, 19, 'Rhythm-based with note blocks that create melodies when cleared'),
(20, 20, 'Superhero', TRUE, 20, 'Comic-themed with power-up blocks that grant special abilities'),
(21, 21, 'Sci-Fi', FALSE, 21, 'Alien technology blocks with teleportation and laser clearing effects'),
(22, 22, 'Western', TRUE, 22, 'Wild West themed with sheriff badges and horseshoe special pieces'),
(23, 23, 'Monster', FALSE, 23, 'Creature-themed where blocks transform into collectible monsters'),
(24, 24, 'Prehistoric', TRUE, 24, 'Dinosaur and fossil-themed with earthquake hazards and meteor bonuses'),
(25, 25, 'Galactic', FALSE, 25, 'Space exploration with planet-shaped blocks and black hole challenges');

-- Item
INSERT INTO Item (game_id, item_id, color, shape, position_on_board, time_to_appear)
VALUES
(1, 'item1', 'red', 'square', 'top-left', 0),
(2, 'item2', 'blue', 'circle', 'center', 2),
(3, 'item3', 'green', 'triangle', 'bottom-right', 4),
(4, 'item4', 'yellow', 'diamond', 'top-right', 6),
(5, 'item5', 'purple', 'rectangle', 'bottom-left', 8),
(6, 'item6', 'orange', 'star', 'middle-right', 10),
(7, 'item7', 'cyan', 'hexagon', 'top-center', 12),
(8, 'item8', 'pink', 'L-shape', 'middle-left', 14),
(9, 'item9', 'brown', 'T-shape', 'bottom-center', 16),
(10, 'item10', 'gold', 'Z-shape', 'upper-left', 18),
(11, 'item11', 'silver', 'line', 'upper-right', 20),
(12, 'item12', 'black', 'cross', 'lower-left', 22),
(13, 'item13', 'white', 'crescent', 'lower-right', 24),
(14, 'item14', 'maroon', 'heart', 'top-border', 26),
(15, 'item15', 'navy', 'pentagon', 'right-border', 28),
(16, 'item16', 'olive', 'octagon', 'bottom-border', 30),
(17, 'item17', 'teal', 'cube', 'left-border', 32),
(18, 'item18', 'lime', 'sphere', 'top-quadrant', 34),
(19, 'item19', 'indigo', 'pyramid', 'right-quadrant', 36),
(20, 'item20', 'coral', 'cylinder', 'bottom-quadrant', 38),
(21, 'item21', 'salmon', 'arrow', 'left-quadrant', 40),
(22, 'item22', 'turquoise', 'teardrop', 'central-area', 42),
(23, 'item23', 'violet', 'hourglass', 'perimeter', 44),
(24, 'item24', 'crimson', 'spiral', 'random', 46),
(25, 'item25', 'emerald', 'tetromino', 'floating', 48);

-- Achievement
INSERT INTO Achievement (achievement_id, achievement_type, description, difficulty_level, required_points, required_playtime)
VALUES
(1, 'Speedster', 'Complete level fast', 3, 500, 3600),
(2, 'Collector', 'Collect all items', 5, 1000, 7200),
(3, 'Marathon', 'Play continuously for 2 hours', 2, 300, 7200),
(4, 'Precision', 'Clear 10 lines without errors', 6, 1500, 5400),
(5, 'First Steps', 'Complete your first game', 1, 100, 1800),
(6, 'Master', 'Reach level 10', 8, 2500, 10800),
(7, 'Perfectionist', 'Score 100% on any level', 7, 2000, 3600),
(8, 'Explorer', 'Play every game mode', 4, 800, 14400),
(9, 'Specialist', 'Win 5 games of the same type', 4, 700, 9000),
(10, 'Night Owl', 'Play for 3 hours between midnight and 6am', 3, 600, 10800),
(11, 'Comeback', 'Win after being near defeat', 5, 1200, 2700),
(12, 'Lightning', 'Clear 4 lines in under 10 seconds', 7, 1800, 1800),
(13, 'Dedicated', 'Play for 50 total hours', 6, 1500, 180000),
(14, 'Streak', 'Win 5 games in a row', 8, 2200, 9000),
(15, 'Diverse', 'Complete all achievements in 3 different games', 9, 3000, 21600),
(16, 'Early Bird', 'Play 10 games before 9am', 2, 300, 7200),
(17, 'Strategist', 'Win without using special abilities', 6, 1700, 3600),
(18, 'Champion', 'Rank #1 on any leaderboard', 9, 3500, 14400),
(19, 'Social', 'Invite 5 friends to play', 2, 250, 1800),
(20, 'Resourceful', 'Win with minimal moves', 7, 2100, 2700),
(21, 'Veteran', 'Play 100 total games', 5, 1300, 90000),
(22, 'Quick Learner', 'Complete tutorial in record time', 1, 150, 900),
(23, 'Challenge Seeker', 'Complete all achievements in hardest game', 10, 4000, 36000),
(24, 'Tetris God', 'Score over 5000 points in one game', 10, 5000, 5400),
(25, 'Legacy', 'Play every day for a month', 8, 2800, 43200);

-- PlayerGame
INSERT INTO PlayerGame (player_id, game_id, times_played, highest_score, total_playtime, last_played_date)
VALUES
(1, 1, 1, 95, 3600, '2025-03-01'),
(2, 2, 1, 185, 2400, '2025-03-02'),
(3, 3, 1, 270, 5200, '2025-03-03'),
(4, 4, 1, 380, 7800, '2025-03-04'),
(5, 5, 1, 420, 4100, '2025-03-05'),
(6, 6, 1, 570, 9600, '2025-03-06'),
(7, 7, 1, 580, 2800, '2025-03-07'),
(8, 8, 1, 710, 6300, '2025-03-08'),
(9, 9, 1, 820, 4800, '2025-03-09'),
(10, 10, 1, 950, 12000, '2025-03-10'),
(11, 11, 1, 980, 7400, '2025-03-11'),
(12, 12, 1, 1050, 3400, '2025-03-12'),
(13, 13, 1, 1180, 6100, '2025-03-13'),
(14, 14, 1, 1250, 9200, '2025-03-14'),
(15, 15, 1, 1320, 4300, '2025-03-15'),
(16, 16, 1, 1520, 11500, '2025-03-16'),
(17, 17, 1, 1480, 5000, '2025-03-17'),
(18, 18, 1, 1680, 7000, '2025-03-18'),
(19, 19, 1, 1750, 2200, '2025-03-19'),
(20, 20, 1, 1850, 8400, '2025-03-20'),
(21, 21, 1, 1920, 5500, '2025-03-21'),
(22, 22, 1, 2050, 10200, '2025-03-22'),
(23, 23, 1, 2120, 3500, '2025-03-23'),
(24, 24, 1, 2280, 8800, '2025-03-24'),
(25, 25, 1, 2350, 6200, '2025-03-25');


-- Session
INSERT INTO Session (session_id, player_id, game_id, start_time, end_time, duration, win_lose_status, points_gained)
VALUES
(1, 1, 1, '2025-03-01 11:00:00', '2025-03-01 12:00:00', 3600, TRUE, 95),
(2, 2, 2, '2025-03-02 11:20:00', '2025-03-02 12:00:00', 2400, TRUE, 185),
(3, 3, 3, '2025-03-03 10:33:20', '2025-03-03 12:00:00', 5200, FALSE, 270),
(4, 4, 4, '2025-03-04 09:50:00', '2025-03-04 12:00:00', 7800, TRUE, 380),
(5, 5, 5, '2025-03-05 10:51:40', '2025-03-05 12:00:00', 4100, FALSE, 420),
(6, 6, 6, '2025-03-06 09:20:00', '2025-03-06 12:00:00', 9600, TRUE, 570),
(7, 7, 7, '2025-03-07 11:13:20', '2025-03-07 12:00:00', 2800, FALSE, 580),
(8, 8, 8, '2025-03-08 10:15:00', '2025-03-08 12:00:00', 6300, TRUE, 710),
(9, 9, 9, '2025-03-09 10:40:00', '2025-03-09 12:00:00', 4800, FALSE, 820),
(10, 10, 10, '2025-03-10 08:40:00', '2025-03-10 12:00:00', 12000, TRUE, 950),
(11, 11, 11, '2025-03-11 09:56:40', '2025-03-11 12:00:00', 7400, TRUE, 980),
(12, 12, 12, '2025-03-12 11:03:20', '2025-03-12 12:00:00', 3400, FALSE, 1050),
(13, 13, 13, '2025-03-13 10:18:20', '2025-03-13 12:00:00', 6100, TRUE, 1180),
(14, 14, 14, '2025-03-14 09:26:40', '2025-03-14 12:00:00', 9200, FALSE, 1250),
(15, 15, 15, '2025-03-15 10:48:20', '2025-03-15 12:00:00', 4300, TRUE, 1320),
(16, 16, 16, '2025-03-16 08:48:20', '2025-03-16 12:00:00', 11500, TRUE, 1520),
(17, 17, 17, '2025-03-17 10:36:40', '2025-03-17 12:00:00', 5000, FALSE, 1480),
(18, 18, 18, '2025-03-18 10:03:20', '2025-03-18 12:00:00', 7000, TRUE, 1680),
(19, 19, 19, '2025-03-19 11:33:20', '2025-03-19 12:00:00', 2200, FALSE, 1750),
(20, 20, 20, '2025-03-20 09:40:00', '2025-03-20 12:00:00', 8400, TRUE, 1850),
(21, 21, 21, '2025-03-21 10:28:20', '2025-03-21 12:00:00', 5500, TRUE, 1920),
(22, 22, 22, '2025-03-22 09:10:00', '2025-03-22 12:00:00', 10200, FALSE, 2050),
(23, 23, 23, '2025-03-23 11:01:40', '2025-03-23 12:00:00', 3500, TRUE, 2120),
(24, 24, 24, '2025-03-24 09:33:20', '2025-03-24 12:00:00', 8800, TRUE, 2280),
(25, 25, 25, '2025-03-25 10:16:40', '2025-03-25 12:00:00', 6200, FALSE, 2350);

-- PlayerAchievement
INSERT INTO PlayerAchievement (player_id, achievement_id, current_total_points, current_total_playtime, date_started, date_completed, is_completed)
VALUES
-- First Steps achievement (requires 100 points, 1800s)
(1, 5, 95, 3600, '2025-03-01', NULL, FALSE),
(2, 5, 185, 2400, '2025-03-02', NULL, FALSE),
(3, 5, 270, 5200, '2025-03-03', NULL, FALSE),
(4, 5, 380, 7800, '2025-03-04', NULL, FALSE),
(5, 5, 420, 4100, '2025-03-05', NULL, FALSE),

-- Social achievement (requires 250 points, 1800s)
(2, 19, 185, 2400, '2025-03-02', NULL, FALSE),
(3, 19, 270, 5200, '2025-03-03', NULL, FALSE),
(4, 19, 380, 7800, '2025-03-04', NULL, FALSE),
(5, 19, 420, 4100, '2025-03-05', NULL, FALSE),

-- Marathon achievement (requires 300 points, 7200s)
(4, 3, 380, 7800, '2025-03-04', NULL, FALSE),
(6, 3, 570, 9600, '2025-03-06', NULL, FALSE),
(10, 3, 950, 12000, '2025-03-10', NULL, FALSE),

-- Speedster achievement (requires 500 points, 3600s)
(6, 1, 570, 9600, '2025-03-06', NULL, FALSE),
(7, 1, 580, 2800, '2025-03-07', NULL, FALSE),
(10, 1, 950, 12000, '2025-03-10', NULL, FALSE),
(15, 1, 1320, 4300, '2025-03-15', NULL, FALSE),

-- Comeback achievement (requires 1200 points, 2700s)
(15, 11, 1320, 4300, '2025-03-15', NULL, FALSE),
(16, 11, 1520, 11500, '2025-03-16', NULL, FALSE),
(19, 11, 1750, 2200, '2025-03-19', NULL, FALSE),
(20, 11, 1850, 8400, '2025-03-20', NULL, FALSE),

-- Precision achievement (requires 1500 points, 5400s)
(16, 4, 1520, 11500, '2025-03-16', NULL, FALSE),
(19, 4, 1750, 2200, '2025-03-19', NULL, FALSE),
(20, 4, 1850, 8400, '2025-03-20', NULL, FALSE),

-- Strategist achievement (requires 1700 points, 3600s)
(18, 17, 1680, 7000, '2025-03-18', NULL, FALSE),
(19, 17, 1750, 2200, '2025-03-19', NULL, FALSE),
(20, 17, 1850, 8400, '2025-03-20', NULL, FALSE),
(21, 17, 1920, 5500, '2025-03-21', NULL, FALSE),

-- Collector achievement (requires 1000 points, 7200s)
(14, 2, 1250, 9200, '2025-03-14', NULL, FALSE),
(16, 2, 1520, 11500, '2025-03-16', NULL, FALSE),
(22, 2, 2050, 10200, '2025-03-22', NULL, FALSE),

-- Rank #1 achievement (for player 25 who has rank 1)
(25, 18, 2350, 6200, '2025-03-25', NULL, FALSE);