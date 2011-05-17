/*
	Auteur:  Stefan Meier
	Version: 2011.05.05
	
	Description: script de création de tables pour la base
				 calendar)
				 
	Détails: 	 l'ordre de création est à respecter (contraintes)

	Restauration d'un fichier: 
	mysql --user=USR --password=PWD --default-character-set=utf8  < /iafbm_personnes.sql

*/
DROP DATABASE IF EXISTS calendar;
CREATE DATABASE calendar DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE calendar;


DROP TABLE IF EXISTS buildings;
CREATE TABLE IF NOT EXISTS buildings (
  building_id INTEGER(9) NOT NULL AUTO_INCREMENT,
  name VARCHAR(30) NOT NULL,
  PRIMARY KEY (building_id)
) TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO buildings VALUES (7, 'BU 7');
INSERT INTO buildings VALUES (71, 'BU 7A');
INSERT INTO buildings VALUES (27, 'BU 27');

DROP TABLE IF EXISTS room_categories;
CREATE TABLE IF NOT EXISTS room_categories (
  room_category_id INTEGER(9) NOT NULL AUTO_INCREMENT,
  name VARCHAR(30) NOT NULL,
  PRIMARY KEY (room_category_id)
) TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO room_categories VALUES (1, 'Autre');
INSERT INTO room_categories VALUES (2, 'Appareils scientifiques');
INSERT INTO room_categories VALUES (3, 'Salles');

DROP TABLE IF EXISTS rooms;
CREATE TABLE IF NOT EXISTS rooms (
  room_id INTEGER(9) NOT NULL AUTO_INCREMENT,
  building_id INTEGER(9) NOT NULL,
  room_category_id INTEGER(9) NOT NULL,
  local VARCHAR(20) NOT NULL,
  name VARCHAR(100) NOT NULL,
  manager VARCHAR(100),
  description TEXT DEFAULT '',
  admins VARCHAR(200) NOT NULL DEFAULT 'fbm-admin-g',
  superAdmins VARCHAR(200) NOT NULL DEFAULT 'fbm-admin-g',
  acceptStudents INTEGER(1) NOT NULL DEFAULT 1,
  monitoring INTEGER(1) NOT NULL DEFAULT 0,
  maxEvents INTEGER(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (room_id),
  FOREIGN KEY (building_id) REFERENCES buildings(building_id),
  FOREIGN KEY (room_category_id) REFERENCES room_categories(room_category_id)
    /*ON DELETE SET DEFAULT*/
) TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO rooms VALUES
(1, 27, 1, '001', 'test 1', '', '', 'fbm-calendar-bu27-setups-g', 'fbm-admin-g', 1, 0, 1),
(2, 27, 2, '002', 'test 2', '', '', 'fbm-calendar-bu27-setups-g', 'fbm-admin-g', 1, 0, 1),
(3, 27, 2, '003', 'test 3', '', '', 'fbm-calendar-bu27-setups-g', 'fbm-admin-g', 1, 0, 1);

DROP TABLE IF EXISTS events;
CREATE TABLE IF NOT EXISTS events (
  event_id VARCHAR(255) NOT NULL,
  room_id INTEGER(9) NOT NULL,
  owner VARCHAR(50) NOT NULL DEFAULT '',
  title VARCHAR(50) NOT NULL DEFAULT '',
  description TEXT NOT NULL DEFAULT '',
  mode VARCHAR(2) NOT NULL,
  PRIMARY KEY (event_id),
  FOREIGN KEY (room_id) REFERENCES rooms(room_id)
    ON DELETE cascade,
  /*le check est ignoré par mysql et sert donc qu'à titre explicatif */
  CONSTRAINT c_mode CHECK (mode IN ('a', 'd', 'w', '2w', 'm', 'y'))
) TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS event_dates;
CREATE TABLE IF NOT EXISTS event_dates (
  event_date_id INTEGER(9) NOT NULL AUTO_INCREMENT,
  event_id VARCHAR(255) NOT NULL,
  begin DATETIME NOT NULL,
  end DATETIME NOT NULL,
  PRIMARY KEY (event_date_id),
  FOREIGN KEY (event_id) REFERENCES events(event_id)
    ON DELETE cascade
) TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS logs;
CREATE TABLE IF NOT EXISTS logs (
  log_id INTEGER(9) NOT NULL AUTO_INCREMENT,
  log_datetime DATETIME NOT NULL,
  log_uid VARCHAR(20) NOT NULL DEFAULT '',
  log_action VARCHAR(20) NOT NULL DEFAULT '',
  log_rooms_id INTEGER(9) NOT NULL,
  log_events_id INTEGER(9),
  log_event_name VARCHAR(50) NOT NULL DEFAULT '',
  log_event_date DATE NOT NULL,
  log_event_begin TIME NOT NULL,
  log_event_end TIME NOT NULL,
  log_event_description TEXT NOT NULL DEFAULT '',
  log_repeat_mode VARCHAR(5),
  log_repeat_end DATE, 
  PRIMARY KEY (log_id)
) TYPE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


/*
Example event

INSERT INTO events VALUES
(1, 13, 'smeier6', 'test1', '',  'w');
INSERT INTO events VALUES
(2, 13, 'smeier6', 'test2', '', 'n');

INSERT INTO event_dates VALUES
(1, 1, '2011-03-14 15:22', '2011-03-14 16:00'),
(2, 1, '2011-03-21 15:22', '2011-03-21 16:00'),
(3, 1, '2011-02-28 15:22', '2011-02-28 16:00'),
(4, 2, '2011-02-28 16:22', '2011-02-28 17:00');
*/