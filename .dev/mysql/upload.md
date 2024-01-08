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