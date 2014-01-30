--------------------------------------------------------------------------------
-- Chronologist - web-based time tracking database
-- Copyright (C) 2003 by Sylvain LAFRASSE.
--------------------------------------------------------------------------------
-- LICENSE:
-- This file is part of Chronologist.
-- 
-- Chronologist is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
-- 
-- Chronologist is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
-- 
-- You should have received a copy of the GNU General Public License
-- along with Chronologist; if not, write to the Free Software
-- Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
--------------------------------------------------------------------------------
-- FILE NAME   : populate.sql
-- DESCRIPTION : Default database populating schema.
-- AUTHORS     : Sylvain LAFRASSE.
--------------------------------------------------------------------------------


USE titi;


--
-- Table structure for table `versions`
--
DROP TABLE IF EXISTS `versions`;
CREATE TABLE `versions` (
    vid         integer         NOT NULL auto_increment,
    label       text            NOT NULL,
    PRIMARY KEY	(vid)
) TYPE=MyISAM;

--
-- Dumping data for table `versions`
--
INSERT INTO `versions` VALUES ('', 'v1.0.0');


--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    uid         integer         NOT NULL auto_increment,
    email		tinytext     	NOT NULL,
    password	tinytext     	NOT NULL,
    firstname	tinytext,
    lastname	tinytext,
    expiration  datetime        NOT NULL,
    PRIMARY KEY	(uid)
) TYPE=MyISAM;

--
-- Dumping data for table `users`
--
INSERT INTO `users` VALUES ('', 'admin', SHA1('admin'), 'Chronologist', 'Administrator', FROM_UNIXTIME(0));


--
-- Table structure for table `groups`
--
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
    gid         integer         NOT NULL auto_increment,
    label       text			NOT NULL,
    PRIMARY KEY	(gid)
) TYPE=MyISAM;

--
-- Dumping data for table `groups`
--
INSERT INTO `groups` VALUES ('', 'Administrator');
INSERT INTO `groups` VALUES ('', 'Manager');
INSERT INTO `groups` VALUES ('', 'Staff');


--
-- Table structure for table `user_groups`
--
DROP TABLE IF EXISTS user_groups;
CREATE TABLE user_groups (
    uid         integer         NOT NULL,
    gid         integer         NOT NULL,
    administer  bool            NOT NULL,
    PRIMARY KEY	(uid, gid)
) TYPE=MyISAM;

--
-- Dumping data for table `user_groups`
--
INSERT INTO `user_groups` VALUES (1, 1, 'true');
INSERT INTO `user_groups` VALUES (1, 2, 'true');
INSERT INTO `user_groups` VALUES (1, 3, 'true');


--
-- Table structure for table `projects`
--
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
    pid         integer         NOT NULL auto_increment,
    ppid		integer         NOT NULL,
    label		text			NOT NULL,
    closed      bool            NOT NULL,
    PRIMARY KEY	(pid)
) TYPE=MyISAM;


--
-- Table structure for table `user_projects`
--
DROP TABLE IF EXISTS `user_projects`;
CREATE TABLE `user_projects` (
    uid         integer         NOT NULL,
    pid         integer         NOT NULL,
    PRIMARY KEY	(uid, pid)
) TYPE=MyISAM;


--
-- Table structure for table `group_projects`
--
DROP TABLE IF EXISTS `group_projects`;
CREATE TABLE `group_projects` (
    gid         integer         NOT NULL,
    pid         integer         NOT NULL,
    PRIMARY KEY	(pid, gid)
) TYPE=MyISAM;


--
-- Table structure for table `tasks`
--
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
    tid         integer         NOT NULL auto_increment,
    uid         integer         NOT NULL,
    pid         integer         NOT NULL,
    aid         integer         NOT NULL,
    label		text            NOT NULL,
    beginning	datetime        NOT NULL,
    duration	integer         NOT NULL,
    PRIMARY KEY	(tid)
) TYPE=MyISAM;


--
-- Table structure for table `activities`
--
DROP TABLE IF EXISTS `activities`;
CREATE TABLE `activities` (
    aid         integer         NOT NULL auto_increment,
    paid		integer         NOT NULL,
    label		text			NOT NULL,
    PRIMARY KEY	(aid)
) TYPE=MyISAM;
