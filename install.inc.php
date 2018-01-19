<?PHP
/*
 *******************************************************************************
 * Chronologist - web-based time tracking database
 * Copyright (C) 2003 by Sylvain LAFRASSE.
 *******************************************************************************
 * LICENSE:
 * This file is part of Chronologist.
 * 
 * Chronologist is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Chronologist is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Chronologist; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *******************************************************************************
 * FILE NAME   : install.inc.php
 * DESCRIPTION : Contains the Installation related functions.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */

require_once("design.inc.php");

function CreateUserAndDatabase($MySqlServerAddress, $RootName, $RootPassword, $WebServerName , $DatabaseName, $UserName, $UserPassword)
{
	// Try to connect to the database server as 'root'
	$Connection = mysqli_connect($MySqlServerAddress, $RootName, $RootPassword);
	if ($Connection == FALSE)
	{
		$_SESSION['message'] = "Could not connect to the '$MySqlServerAddress' database server. <BR> Verify the database server host name, user name and password. <BR>";
		return FALSE;
	}

	// Try to connect to the 'mysql' database
	$DB = mysqli_select_db("mysql", $Connection);
	if ($DB == FALSE)
	{
		$_SESSION['message'] = "Could not connect to the 'mysql' database. <BR> Verify the database name. <BR>";
		return FALSE;
	}

	// TODO : Check if the desired database does NOT already exist

	// New database creation
	$SQL    = "CREATE DATABASE $DatabaseName";
	global $Connection;
    $Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");
	if ($Result = FALSE)
	{
		$_SESSION['message'] = "Could not create the '$DatabaseName' database. <BR> Please try again. <BR>";
		return FALSE;
	}

	// TODO : Check if the desired user does NOT already exist if necessary

	// New user creation
	$SQL    = "GRANT ALL PRIVILEGES ON $DatabaseName.*
	                 TO $UserName@$WebServerName
	                 IDENTIFIED BY '$UserPassword'
	                 WITH GRANT OPTION";
	$Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");
	if ($Result = FALSE)
	{
		$_SESSION['message'] = "Could not create the '$UserName' user for the '$DatabaseName' database from the '$WebServerName' web server. <BR> Please try again. <BR>";
		return FALSE;
	}

	// Flush the privileges to 'activate' the new user
	$SQL    = "FLUSH PRIVILEGES";
	$Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");
	if ($Result = FALSE)
	{
		$_SESSION['message'] = "Could not create the '$UserName' user for the '$DatabaseName' database from the '$WebServerName' web server. <BR> Please try again. <BR>";
		return FALSE;
	}

	mysqli_close();
	return TRUE;
}

function CreateConfigFile($MySqlServerAddress, $UserName, $UserPassword, $DatabaseName)
{
	// Try to connect to the database server as 'user'
	$Connection = mysqli_connect($MySqlServerAddress, $UserName, $UserPassword);
	if ($Connection == FALSE)
	{
		$_SESSION['message'] = "1 Could not connect to the '$MySqlServerAddress' database server. <BR> Verify the database server host name, user name and password. <BR>";
       	return FALSE;
	}

	// Try to connect to the new database
	$DB = mysqli_select_db($DatabaseName, $Connection);
	if ($DB == FALSE)
	{
		$_SESSION['message'] = "1 Could not connect to the '$DatabaseName' database. <BR> Verify the database name. <BR>";
       	return FALSE;
	}

	// Try to create the configuration file
	$FD = fopen("db.inc.php", 'x');
	if (empty($FD))
	{
		$_SESSION['message'] = "Could not create the configuration file. <BR>"
		                      ."Please verify that the web server process has sufficient file privileges to write in the Chronologist directory, <BR>"
		                      ."or that a file named 'db.inc.php' does NOT already exist in the TilmeTracker directory. <BR>";
		return FALSE;
	}

	// Generate the configuration file content from the received values
	$FileContent = "<?PHP\n"
	              ."\$Host     = '$MySqlServerAddress';\n"
	              ."\$User     = '$UserName';\n"
	              ."\$Password = '$UserPassword';\n"
	              ."\$Database = '$DatabaseName';\n"
	              ."?>\n";

	// Try to write the generated content in the configuration file
	if ((fwrite($FD, $FileContent)) == FALSE)
	{
		$_SESSION['message'] = "Could not write in the configuration file. <BR>"
		                      ."Please verify that the web server process has sufficient file privileges to write in the Chronologist directory. <BR>";
		return FALSE;
	}

	fclose($FD);
	mysqli_close();
	return TRUE;
}

function PopulateDatabase($MySqlServerAddress, $UserName, $UserPassword, $DatabaseName, $AdminEmail, $AdminPassword, $AdminFirstName, $AdminLastName)
{
	// Try to connect to the database server as 'user'
	$Connection = mysqli_connect($MySqlServerAddress, $UserName, $UserPassword);
	if ($Connection == FALSE)
	{
		$_SESSION['message'] = "Could not connect to the '$MySqlServerAddress' database server. <BR> Verify the database server host name, user name and password. <BR>";
       	return FALSE;
	}

	// Try to connect to the new database
	$DB = mysqli_select_db($DatabaseName, $Connection);
	if ($DB == FALSE)
	{
		$_SESSION['message'] = "Could not connect to the '$DatabaseName' database. <BR> Verify the database name. <BR>";
       	return FALSE;
	}

	$Queries = array();

	// 'versions' table creation
	$Queries[] = "DROP   TABLE IF EXISTS versions";
	$Queries[] = "CREATE TABLE versions (vid   integer NOT NULL auto_increment,
	                                     label text    NOT NULL,
	                                     PRIMARY KEY (vid)) TYPE=MyISAM";
	$Queries[] = "INSERT INTO  versions VALUES (NULL, 'v0.5')";

	// 'users' table creation
	$Queries[] = "DROP   TABLE IF EXISTS users";
	$Queries[] = "CREATE TABLE users (uid        integer  NOT NULL auto_increment,
	                                  email      tinytext NOT NULL,
	                                  password   tinytext NOT NULL,
	                                  firstname  tinytext,
	                                  lastname   tinytext,
	                                  expiration datetime NOT NULL,
	                                  PRIMARY KEY (uid)) TYPE=MyISAM";
	$Queries[] = "INSERT INTO  users VALUES (NULL, '$AdminEmail', PASSWORD('$AdminPassword'), '$AdminFirstName', '$AdminLastName', FROM_UNIXTIME(0))";

	// 'user_groups' table creation
	$Queries[] = "DROP   TABLE IF EXISTS user_groups";
	$Queries[] = "CREATE TABLE user_groups (uid        integer NOT NULL,
	                                        gid        integer NOT NULL,
	                                        administer bool    NOT NULL,
	                                        PRIMARY KEY (uid, gid)) TYPE=MyISAM";
	$Queries[] = "INSERT INTO  user_groups VALUES (1, 1, 'true')";
	$Queries[] = "INSERT INTO  user_groups VALUES (1, 2, 'true')";
	$Queries[] = "INSERT INTO  user_groups VALUES (1, 3, 'true')";

	// 'user_projects' table creation
	$Queries[] = "DROP   TABLE IF EXISTS user_projects";
	$Queries[] = "CREATE TABLE `user_projects` (uid integer NOT NULL,
	                                            pid integer NOT NULL,
	                                            PRIMARY KEY (uid, pid)) TYPE=MyISAM";

	// 'groups' table creation
	$Queries[] = "DROP   TABLE IF EXISTS groups";
	$Queries[] = "CREATE TABLE groups (gid   integer NOT NULL auto_increment,
	                                   label text    NOT NULL,
	                                   PRIMARY KEY (gid)) TYPE=MyISAM";
	$Queries[] = "INSERT INTO  groups VALUES (NULL, 'Administrator')";
	$Queries[] = "INSERT INTO  groups VALUES (NULL, 'Manager')";
	$Queries[] = "INSERT INTO  groups VALUES (NULL, 'Staff')";

	// 'group_projects' table creation
	$Queries[] = "DROP   TABLE IF EXISTS group_projects";
	$Queries[] = "CREATE TABLE `group_projects` (gid integer NOT NULL,
	                                             pid integer NOT NULL,
	                                             PRIMARY KEY (pid, gid)) TYPE=MyISAM";

	// 'projects' table creation
	$Queries[] = "DROP   TABLE IF EXISTS projects";
	$Queries[] = "CREATE TABLE projects (pid   integer NOT NULL auto_increment,
	                                     ppid  integer NOT NULL,
	                                     label text    NOT NULL,
	                                     PRIMARY KEY (pid)) TYPE=MyISAM";
	$Queries[] = "INSERT INTO  projects VALUES (NULL, 0, 'Chronologist Administration')";

	// 'tasks' table creation
	$Queries[] = "DROP   TABLE IF EXISTS tasks";
	$Queries[] = "CREATE TABLE tasks (tid       integer  NOT NULL auto_increment,
	                                  uid       integer  NOT NULL,
	                                  pid       integer  NOT NULL,
	                                  label     text     NOT NULL,
	                                  beginning datetime NOT NULL,
	                                  duration	integer  NOT NULL,
	                                  PRIMARY KEY (tid)) TYPE=MyISAM";

	foreach ($Queries as $Query)
	{
		$Result = mysqli_query($Connection, $Query)
		or die("Could not execute the '".$Query."' request.");
	}

	mysqli_close();
	return FALSE;
	return TRUE;
}