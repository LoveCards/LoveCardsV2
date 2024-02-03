V2.2.X->V2.3.0数据库升级
ALTER TABLE admin DROP COLUMN uuid;

ALTER TABLE cards ADD COLUMN uid INT DEFAULT 0 AFTER id

ALTER TABLE cards_comments CHANGE COLUMN cid pid INT;
ALTER TABLE cards_comments ADD COLUMN aid INT AFTER id;
ALTER TABLE cards_comments ADD COLUMN uid INT AFTER pid;
RENAME TABLE cards_comments TO comments;

ALTER TABLE cards_tag ADD COLUMN aid INT AFTER id;
RENAME TABLE cards_tag TO tags;

ALTER TABLE img DROP COLUMN time;
ALTER TABLE img ADD COLUMN uid INT(11) AFTER pid;
ALTER TABLE img ADD COLUMN created_at DATETIME AFTER url;
ALTER TABLE img ADD COLUMN updated_at DATETIME DEFAULT NULL AFTER created_at;
ALTER TABLE img ADD COLUMN deleted_at DATETIME DEFAULT NULL AFTER updated_at;
RENAME TABLE img TO images;

ALTER TABLE cards_tag_map ADD COLUMN aid INT AFTER id;
ALTER TABLE cards_tag_map CHANGE COLUMN cid pid INT;
RENAME TABLE cards_tag_map TO tags_map;

DELETE FROM system WHERE name IN ('smtpUser', 'smtpHost', 'smtpPort', 'smtpPass', 'smtpName', 'smtpSecure');

ALTER TABLE good ADD COLUMN uid INT AFTER pid;

CREATE TABLE your_table_name (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    number VARCHAR(32) NOT NULL,
    avatar VARCHAR(255) NOT ,
    email VARCHAR(320) NOT NULL,
    phone VARCHAR(32) NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME DEFAULT NULL,
    status INT(11) NOT NULL
);



V2.0.0->V2.1.0数据库升级
RENAME TABLE `user` TO `admin`;

ALTER TABLE cards CHANGE COLUMN state status ENUM('0', '1');
ALTER TABLE cards_comments CHANGE COLUMN state status ENUM('0', '1');
ALTER TABLE cards_tag CHANGE COLUMN state status ENUM('0', '1');

ALTER TABLE cards MODIFY COLUMN top ENUM('0', '1');

ALTER TABLE admin ENGINE=InnoDB;
ALTER TABLE cards ENGINE=InnoDB;
ALTER TABLE cards_comments ENGINE=InnoDB;
ALTER TABLE cards_tag ENGINE=InnoDB;
ALTER TABLE cards_tag_map ENGINE=InnoDB;
ALTER TABLE good ENGINE=InnoDB;
ALTER TABLE img ENGINE=InnoDB;
ALTER TABLE system ENGINE=InnoDB;