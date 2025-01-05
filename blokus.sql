DROP TABLE IF EXISTS `board`;
DROP TABLE IF EXISTS `player`;
DROP TABLE IF EXISTS `game_status`;
DROP TABLE IF EXISTS `blocks`;
DROP TABLE IF EXISTS `blocks_initial`;

CREATE TABLE `board`(
  `x` TINYINT(2) NOT NULL,
  `y` TINYINT (2) NOT NULL, 
  `piece_color` ENUM ('R', 'B', 'G', 'Y') DEFAULT NULL,
  `piece` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY(`x`, `y`)
);

DELIMITER $$
CREATE OR REPLACE PROCEDURE reset_board()		#R to reset red B to reset blue anything else to reset both
BEGIN
	UPDATE `board`  SET `piece_color` = NULL;
    UPDATE `board`  SET `piece` = NULL;
END ;
$$
DELIMITER ;
 

CREATE TABLE `player`(
`username` VARCHAR(20) DEFAULT NULL,
`piece_color` ENUM('R', 'B', 'G', 'Y') NOT NULL,
`last_action` TIMESTAMP NULL DEFAULT NULL,		#to check for inactivity
`player_token` VARCHAR(40),		#for authentication 
PRIMARY KEY(`piece_color`)
);


CREATE TABLE `game_status` (
    `status` ENUM('INACTIVE','INITIALIZED','STARTED','ENDED','ABORTED') NOT NULL DEFAULT 'INACTIVE',
    `player` ENUM('R','G','B','Y') DEFAULT NULL,
    `result` ENUM('R','G','B','Y','A') DEFAULT NULL,
    `last_change` TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE `blocks`(
    `color` ENUM('R', 'B', 'G', 'Y') NOT NULL,
    `piece` INT
);

CREATE TABLE `blocks_initial`(
    `color` ENUM('R', 'B', 'G', 'Y') NOT NULL,
    `piece` INT
);

#Initialise 1 game room
INSERT INTO `game_status` () VALUES ();

INSERT INTO `blocks_initial` (`color`, `piece`) VALUES
("R", 0),
("R", 1),
("R", 2),
("R", 3),
("R", 4),
("R", 5),
("R", 6),
("R", 7),
("R", 8),
("R", 9),
("R", 10),
("R", 11),
("R", 12),
("R", 13),
("R", 14),
("R", 15),
("R", 16),
("R", 17),
("R", 18),
("R", 19),
("R", 20),
("B", 0),
("B", 1),
("B", 2),
("B", 3),
("B", 4),
("B", 5),
("B", 6),
("B", 7),
("B", 8),
("B", 9),
("B", 10),
("B", 11),
("B", 12),
("B", 13),
("B", 14),
("B", 15),
("B", 16),
("B", 17),
("B", 18),
("B", 19),
("B", 20),
("G", 0),
("G", 1),
("G", 2),
("G", 3),
("G", 4),
("G", 5),
("G", 6),
("G", 7),
("G", 8),
("G", 9),
("G", 10),
("G", 11),
("G", 12),
("G", 13),
("G", 14),
("G", 15),
("G", 16),
("G", 17),
("G", 18),
("G", 19),
("G", 20),
("Y", 0),
("Y", 1),
("Y", 2),
("Y", 3),
("Y", 4),
("Y", 5),
("Y", 6),
("Y", 7),
("Y", 8),
("Y", 9),
("Y", 10),
("Y", 11),
("Y", 12),
("Y", 13),
("Y", 14),
("Y", 15),
("Y", 16),
("Y", 17),
("Y", 18),
("Y", 19),
("Y", 20);

INSERT INTO `board` (`x`, `y`, `piece_color`, `piece`) VALUES
(1, 1, NULL, NULL),
(1, 2, NULL, NULL),
(1, 3, NULL, NULL),
(1, 4, NULL, NULL),
(1, 5, NULL, NULL),
(1, 6, NULL, NULL),
(1, 7, NULL, NULL),
(1, 8, NULL, NULL),
(1, 9, NULL, NULL),
(1, 10, NULL, NULL),
(1, 11, NULL, NULL),
(1, 12, NULL, NULL),
(1, 13, NULL, NULL),
(1, 14, NULL, NULL),
(1, 15, NULL, NULL),
(1, 16, NULL, NULL),
(1, 17, NULL, NULL),
(1, 18, NULL, NULL),
(1, 19, NULL, NULL),
(1, 20, NULL, NULL),
(2, 1, NULL, NULL),
(2, 2, NULL, NULL),
(2, 3, NULL, NULL),
(2, 4, NULL, NULL),
(2, 5, NULL, NULL),
(2, 6, NULL, NULL),
(2, 7, NULL, NULL),
(2, 8, NULL, NULL),
(2, 9, NULL, NULL),
(2, 10, NULL, NULL),
(2, 11, NULL, NULL),
(2, 12, NULL, NULL),
(2, 13, NULL, NULL),
(2, 14, NULL, NULL),
(2, 15, NULL, NULL),
(2, 16, NULL, NULL),
(2, 17, NULL, NULL),
(2, 18, NULL, NULL),
(2, 19, NULL, NULL),
(2, 20, NULL, NULL),
(3, 1, NULL, NULL),
(3, 2, NULL, NULL),
(3, 3, NULL, NULL),
(3, 4, NULL, NULL),
(3, 5, NULL, NULL),
(3, 6, NULL, NULL),
(3, 7, NULL, NULL),
(3, 8, NULL, NULL),
(3, 9, NULL, NULL),
(3, 10, NULL, NULL),
(3, 11, NULL, NULL),
(3, 12, NULL, NULL),
(3, 13, NULL, NULL),
(3, 14, NULL, NULL),
(3, 15, NULL, NULL),
(3, 16, NULL, NULL),
(3, 17, NULL, NULL),
(3, 18, NULL, NULL),
(3, 19, NULL, NULL),
(3, 20, NULL, NULL),
(4, 1, NULL, NULL),
(4, 2, NULL, NULL),
(4, 3, NULL, NULL),
(4, 4, NULL, NULL),
(4, 5, NULL, NULL),
(4, 6, NULL, NULL),
(4, 7, NULL, NULL),
(4, 8, NULL, NULL),
(4, 9, NULL, NULL),
(4, 10, NULL, NULL),
(4, 11, NULL, NULL),
(4, 12, NULL, NULL),
(4, 13, NULL, NULL),
(4, 14, NULL, NULL),
(4, 15, NULL, NULL),
(4, 16, NULL, NULL),
(4, 17, NULL, NULL),
(4, 18, NULL, NULL),
(4, 19, NULL, NULL),
(4, 20, NULL, NULL),
(5, 1, NULL, NULL),
(5, 2, NULL, NULL),
(5, 3, NULL, NULL),
(5, 4, NULL, NULL),
(5, 5, NULL, NULL),
(5, 6, NULL, NULL),
(5, 7, NULL, NULL),
(5, 8, NULL, NULL),
(5, 9, NULL, NULL),
(5, 10, NULL, NULL),
(5, 11, NULL, NULL),
(5, 12, NULL, NULL),
(5, 13, NULL, NULL),
(5, 14, NULL, NULL),
(5, 15, NULL, NULL),
(5, 16, NULL, NULL),
(5, 17, NULL, NULL),
(5, 18, NULL, NULL),
(5, 19, NULL, NULL),
(5, 20, NULL, NULL),
(6, 1, NULL, NULL),
(6, 2, NULL, NULL),
(6, 3, NULL, NULL),
(6, 4, NULL, NULL),
(6, 5, NULL, NULL),
(6, 6, NULL, NULL),
(6, 7, NULL, NULL),
(6, 8, NULL, NULL),
(6, 9, NULL, NULL),
(6, 10, NULL, NULL),
(6, 11, NULL, NULL),
(6, 12, NULL, NULL),
(6, 13, NULL, NULL),
(6, 14, NULL, NULL),
(6, 15, NULL, NULL),
(6, 16, NULL, NULL),
(6, 17, NULL, NULL),
(6, 18, NULL, NULL),
(6, 19, NULL, NULL),
(6, 20, NULL, NULL),
(7, 1, NULL, NULL),
(7, 2, NULL, NULL),
(7, 3, NULL, NULL),
(7, 4, NULL, NULL),
(7, 5, NULL, NULL),
(7, 6, NULL, NULL),
(7, 7, NULL, NULL),
(7, 8, NULL, NULL),
(7, 9, NULL, NULL),
(7, 10, NULL, NULL),
(7, 11, NULL, NULL),
(7, 12, NULL, NULL),
(7, 13, NULL, NULL),
(7, 14, NULL, NULL),
(7, 15, NULL, NULL),
(7, 16, NULL, NULL),
(7, 17, NULL, NULL),
(7, 18, NULL, NULL),
(7, 19, NULL, NULL),
(7, 20, NULL, NULL),
(8, 1, NULL, NULL),
(8, 2, NULL, NULL),
(8, 3, NULL, NULL),
(8, 4, NULL, NULL),
(8, 5, NULL, NULL),
(8, 6, NULL, NULL),
(8, 7, NULL, NULL),
(8, 8, NULL, NULL),
(8, 9, NULL, NULL),
(8, 10, NULL, NULL),
(8, 11, NULL, NULL),
(8, 12, NULL, NULL),
(8, 13, NULL, NULL),
(8, 14, NULL, NULL),
(8, 15, NULL, NULL),
(8, 16, NULL, NULL),
(8, 17, NULL, NULL),
(8, 18, NULL, NULL),
(8, 19, NULL, NULL),
(8, 20, NULL, NULL),
(9, 1, NULL, NULL),
(9, 2, NULL, NULL),
(9, 3, NULL, NULL),
(9, 4, NULL, NULL),
(9, 5, NULL, NULL),
(9, 6, NULL, NULL),
(9, 7, NULL, NULL),
(9, 8, NULL, NULL),
(9, 9, NULL, NULL),
(9, 10, NULL, NULL),
(9, 11, NULL, NULL),
(9, 12, NULL, NULL),
(9, 13, NULL, NULL),
(9, 14, NULL, NULL),
(9, 15, NULL, NULL),
(9, 16, NULL, NULL),
(9, 17, NULL, NULL),
(9, 18, NULL, NULL),
(9, 19, NULL, NULL),
(9, 20, NULL, NULL),
(10, 1, NULL, NULL),
(10, 2, NULL, NULL),
(10, 3, NULL, NULL),
(10, 4, NULL, NULL),
(10, 5, NULL, NULL),
(10, 6, NULL, NULL),
(10, 7, NULL, NULL),
(10, 8, NULL, NULL),
(10, 9, NULL, NULL),
(10, 10, NULL, NULL),
(10, 11, NULL, NULL),
(10, 12, NULL, NULL),
(10, 13, NULL, NULL),
(10, 14, NULL, NULL),
(10, 15, NULL, NULL),
(10, 16, NULL, NULL),
(10, 17, NULL, NULL),
(10, 18, NULL, NULL),
(10, 19, NULL, NULL),
(10, 20, NULL, NULL),
(11, 1, NULL, NULL),
(11, 2, NULL, NULL),
(11, 3, NULL, NULL),
(11, 4, NULL, NULL),
(11, 5, NULL, NULL),
(11, 6, NULL, NULL),
(11, 7, NULL, NULL),
(11, 8, NULL, NULL),
(11, 9, NULL, NULL),
(11, 10, NULL, NULL),
(11, 11, NULL, NULL),
(11, 12, NULL, NULL),
(11, 13, NULL, NULL),
(11, 14, NULL, NULL),
(11, 15, NULL, NULL),
(11, 16, NULL, NULL),
(11, 17, NULL, NULL),
(11, 18, NULL, NULL),
(11, 19, NULL, NULL),
(11, 20, NULL, NULL),
(12, 1, NULL, NULL),
(12, 2, NULL, NULL),
(12, 3, NULL, NULL),
(12, 4, NULL, NULL),
(12, 5, NULL, NULL),
(12, 6, NULL, NULL),
(12, 7, NULL, NULL),
(12, 8, NULL, NULL),
(12, 9, NULL, NULL),
(12, 10, NULL, NULL),
(12, 11, NULL, NULL),
(12, 12, NULL, NULL),
(12, 13, NULL, NULL),
(12, 14, NULL, NULL),
(12, 15, NULL, NULL),
(12, 16, NULL, NULL),
(12, 17, NULL, NULL),
(12, 18, NULL, NULL),
(12, 19, NULL, NULL),
(12, 20, NULL, NULL),
(13, 1, NULL, NULL),
(13, 2, NULL, NULL),
(13, 3, NULL, NULL),
(13, 4, NULL, NULL),
(13, 5, NULL, NULL),
(13, 6, NULL, NULL),
(13, 7, NULL, NULL),
(13, 8, NULL, NULL),
(13, 9, NULL, NULL),
(13, 10, NULL, NULL),
(13, 11, NULL, NULL),
(13, 12, NULL, NULL),
(13, 13, NULL, NULL),
(13, 14, NULL, NULL),
(13, 15, NULL, NULL),
(13, 16, NULL, NULL),
(13, 17, NULL, NULL),
(13, 18, NULL, NULL),
(13, 19, NULL, NULL),
(13, 20, NULL, NULL),
(14, 1, NULL, NULL),
(14, 2, NULL, NULL),
(14, 3, NULL, NULL),
(14, 4, NULL, NULL),
(14, 5, NULL, NULL),
(14, 6, NULL, NULL),
(14, 7, NULL, NULL),
(14, 8, NULL, NULL),
(14, 9, NULL, NULL),
(14, 10, NULL, NULL),
(14, 11, NULL, NULL),
(14, 12, NULL, NULL),
(14, 13, NULL, NULL),
(14, 14, NULL, NULL),
(14, 15, NULL, NULL),
(14, 16, NULL, NULL),
(14, 17, NULL, NULL),
(14, 18, NULL, NULL),
(14, 19, NULL, NULL),
(14, 20, NULL, NULL),
(15, 1, NULL, NULL),
(15, 2, NULL, NULL),
(15, 3, NULL, NULL),
(15, 4, NULL, NULL),
(15, 5, NULL, NULL),
(15, 6, NULL, NULL),
(15, 7, NULL, NULL),
(15, 8, NULL, NULL),
(15, 9, NULL, NULL),
(15, 10, NULL, NULL),
(15, 11, NULL, NULL),
(15, 12, NULL, NULL),
(15, 13, NULL, NULL),
(15, 14, NULL, NULL),
(15, 15, NULL, NULL),
(15, 16, NULL, NULL),
(15, 17, NULL, NULL),
(15, 18, NULL, NULL),
(15, 19, NULL, NULL),
(15, 20, NULL, NULL),
(16, 1, NULL, NULL),
(16, 2, NULL, NULL),
(16, 3, NULL, NULL),
(16, 4, NULL, NULL),
(16, 5, NULL, NULL),
(16, 6, NULL, NULL),
(16, 7, NULL, NULL),
(16, 8, NULL, NULL),
(16, 9, NULL, NULL),
(16, 10, NULL, NULL),
(16, 11, NULL, NULL),
(16, 12, NULL, NULL),
(16, 13, NULL, NULL),
(16, 14, NULL, NULL),
(16, 15, NULL, NULL),
(16, 16, NULL, NULL),
(16, 17, NULL, NULL),
(16, 18, NULL, NULL),
(16, 19, NULL, NULL),
(16, 20, NULL, NULL),
(17, 1, NULL, NULL),
(17, 2, NULL, NULL),
(17, 3, NULL, NULL),
(17, 4, NULL, NULL),
(17, 5, NULL, NULL),
(17, 6, NULL, NULL),
(17, 7, NULL, NULL),
(17, 8, NULL, NULL),
(17, 9, NULL, NULL),
(17, 10, NULL, NULL),
(17, 11, NULL, NULL),
(17, 12, NULL, NULL),
(17, 13, NULL, NULL),
(17, 14, NULL, NULL),
(17, 15, NULL, NULL),
(17, 16, NULL, NULL),
(17, 17, NULL, NULL),
(17, 18, NULL, NULL),
(17, 19, NULL, NULL),
(17, 20, NULL, NULL),
(18, 1, NULL, NULL),
(18, 2, NULL, NULL),
(18, 3, NULL, NULL),
(18, 4, NULL, NULL),
(18, 5, NULL, NULL),
(18, 6, NULL, NULL),
(18, 7, NULL, NULL),
(18, 8, NULL, NULL),
(18, 9, NULL, NULL),
(18, 10, NULL, NULL),
(18, 11, NULL, NULL),
(18, 12, NULL, NULL),
(18, 13, NULL, NULL),
(18, 14, NULL, NULL),
(18, 15, NULL, NULL),
(18, 16, NULL, NULL),
(18, 17, NULL, NULL),
(18, 18, NULL, NULL),
(18, 19, NULL, NULL),
(18, 20, NULL, NULL),
(19, 1, NULL, NULL),
(19, 2, NULL, NULL),
(19, 3, NULL, NULL),
(19, 4, NULL, NULL),
(19, 5, NULL, NULL),
(19, 6, NULL, NULL),
(19, 7, NULL, NULL),
(19, 8, NULL, NULL),
(19, 9, NULL, NULL),
(19, 10, NULL, NULL),
(19, 11, NULL, NULL),
(19, 12, NULL, NULL),
(19, 13, NULL, NULL),
(19, 14, NULL, NULL),
(19, 15, NULL, NULL),
(19, 16, NULL, NULL),
(19, 17, NULL, NULL),
(19, 18, NULL, NULL),
(19, 19, NULL, NULL),
(19, 20, NULL, NULL),
(20, 1, NULL, NULL),
(20, 2, NULL, NULL),
(20, 3, NULL, NULL),
(20, 4, NULL, NULL),
(20, 5, NULL, NULL),
(20, 6, NULL, NULL),
(20, 7, NULL, NULL),
(20, 8, NULL, NULL),
(20, 9, NULL, NULL),
(20, 10, NULL, NULL),
(20, 11, NULL, NULL),
(20, 12, NULL, NULL),
(20, 13, NULL, NULL),
(20, 14, NULL, NULL),
(20, 15, NULL, NULL),
(20, 16, NULL, NULL),
(20, 17, NULL, NULL),
(20, 18, NULL, NULL),
(20, 19, NULL, NULL),
(20, 20, NULL, NULL);

DELIMITER $$
CREATE OR REPLACE TRIGGER game_status_update 
BEFORE UPDATE ON game_status
FOR EACH ROW
BEGIN
    SET NEW.last_change = NOW();
END$$
DELIMITER ;