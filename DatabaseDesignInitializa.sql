CREATE DATABASE databasehub;

USE databasehub;

CREATE TABLE `account` (
  `account_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `database_id` int(11) NOT NULL,
  `username` varchar(128) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `database_id` (`database_id`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `application` (
  `application_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL DEFAULT '',
  `description` varchar(1024) NOT NULL DEFAULT '',
  `database_id` int(11) NOT NULL,
  `sql` mediumtext NOT NULL,
  `type` varchar(32) NOT NULL DEFAULT '',
  `apply_user` int(11) NOT NULL,
  `approve_user` int(11) DEFAULT NULL,
  `create_time` datetime NOT NULL,
  `approve_time` datetime DEFAULT NULL,
  `edit_time` datetime DEFAULT NULL,
  `execute_time` datetime DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT '',
  `parallelable` varchar(8) NOT NULL DEFAULT 'NO' COMMENT 'YES NO',
  `duration` double DEFAULT NULL,
  PRIMARY KEY (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `database` (
  `database_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `database_name` varchar(128) NOT NULL DEFAULT '',
  `host` varchar(256) NOT NULL DEFAULT '',
  `port` int(11) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT '',
  `engine` varchar(32) NOT NULL DEFAULT '' COMMENT 'MYSQL',
  `default_account_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`database_id`),
  UNIQUE KEY `database_name` (`database_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `permission` (
  `permission_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `database_id` int(11) NOT NULL,
  `permission` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `user_id` (`user_id`,`database_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `quick_query` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `database_id` int(11) NOT NULL,
  `sql` mediumtext NOT NULL,
  `raw_sql` mediumtext NOT NULL,
  `apply_user` int(11) NOT NULL,
  `apply_time` datetime NOT NULL,
  `duration` double DEFAULT NULL,
  `remark` varchar(1024) DEFAULT NULL,
  `type` varchar(16) NOT NULL DEFAULT 'SYNC' COMMENT 'SYNC ASYNC',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `record` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT '',
  `act_user` int(11) NOT NULL,
  `action` varchar(128) NOT NULL DEFAULT '',
  `detail` text,
  `act_time` datetime NOT NULL,
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `session` (
  `session_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(200) NOT NULL DEFAULT '',
  `since` datetime NOT NULL,
  `expire` int(11) NOT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `realname` varchar(64) NOT NULL DEFAULT '',
  `email` varchar(200) DEFAULT NULL,
  `user_type` varchar(5) NOT NULL DEFAULT '' COMMENT 'ADMIN USER',
  `password` varchar(200) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL COMMENT 'NORMAL DISABLED',
  `user_org` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_org` (`user_org`,`username`),
  UNIQUE KEY `user_org_2` (`user_org`,`realname`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `user_permitted_approval`
AS SELECT
   `a`.`application_id` AS `application_id`,
   `a`.`title` AS `title`,
   `a`.`description` AS `description`,
   `a`.`database_id` AS `database_id`,
   `a`.`sql` AS `sql`,
   `a`.`type` AS `type`,
   `a`.`apply_user` AS `apply_user`,
   `a`.`approve_user` AS `approve_user`,
   `a`.`create_time` AS `create_time`,
   `a`.`approve_time` AS `approve_time`,
   `a`.`edit_time` AS `edit_time`,
   `a`.`execute_time` AS `execute_time`,
   `a`.`status` AS `status`,
   `a`.`parallelable` AS `parallelable`,
   `a`.`duration` AS `duration`,
   `p`.`user_id` AS `permitted_user`
FROM (`application` `a` join `permission` `p` on(((`a`.`database_id` = `p`.`database_id`) and (`a`.`type` = `p`.`permission`)))) where (`a`.`status` = 'APPLIED');