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
 * FILE NAME   : design.inc.php
 * DESCRIPTION : Contains the standard header and footer.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("user.inc.php");

$Connection = null;

// If the database configuration file does not exist
if (!file_exists("db.inc.php"))
{
	// Launch the database configuration script
	header("Location: install.php");
}
else
{
	// Get the database connection parameters
	require_once("db.inc.php");

    // Set default charset ti UTF8
    ini_set("default_charset", "utf8");

	// Connect to the database server
	global $Connection;
	$Connection = mysql_connect($Host, $User, $Password)
	or die("Could not connect to database server.");

    mysql_set_charset('utf8', $Connection);

	// Connect to the specified database
	$DB = mysql_select_db($Database, $Connection)
	or die("Could not select the database.");
}


// Session handling.
session_start();

// Set default timezone
date_default_timezone_set("Europe/Paris");


// Displays the common page header
function ShowHeader($Title, $URL = "index.php")
{
	// Aesthetic Attributes
	$BACKGROUNG_COLOR        = "WHITE";
	$HEADER_BACKGROUNG_COLOR = "GRAY";
	$HEADER_FONT_COLOR       = "WHITE";
	$TABS_BACKGROUNG_COLOR   = "YELLOW";
	$USERNAME_FONT_COLOR     = "RED";
	$MESSAGE_FONT_COLOR      = "RED";

    echo "
<HTML>
    <HEAD>
        <TITLE> ".$Title." </TITLE>
    </HEAD>

    <BODY BGCOLOR='".$BACKGROUNG_COLOR."' TOPMARGIN='0' LEFTMARGIN='0' MARGINHEIGHT='0' MARGINWIDTH='0'>
        <TABLE WIDTH='100%' HEIGHT='100%' BORDER='0' CELLSPACING='0' CELLPADDING='5'>
            <TR BGCOLOR='".$HEADER_BACKGROUNG_COLOR."' HEIGHT='1' ALIGN='CENTER' VALIGN='CENTER'>
                <TD WIDTH='1' ALIGN='LEFT'>
                    <FONT COLOR='".$HEADER_FONT_COLOR."' SIZE='+3'>
                        <B> Chronologist </B>
                    </FONT>
                </TD>
                <TD ALIGN='LEFT'>
                    <FONT COLOR='".$HEADER_FONT_COLOR."' SIZE='+2'>
                         - ".htmlentities($Title)."
                    </FONT>
                </TD>
                <TD WIDTH='1'>
                    <TABLE>
                        <TR>
          ";

    // If nobody is already connected
    $UID = $_SESSION['uid'];
    if ($UID == "")
    {
        // Displays the 'login' button
        echo "
                            <FORM ACTION='index.php?url=".$URL."' METHOD='POST'>
                                <INPUT TYPE='SUBMIT' NAME='Login' VALUE='Login'>
                            </FORM>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
             ";
    }
    else // If someone is already connected
    {
		$_SESSION['name'] = GetUserName($UID);
		if ($_SESSION['name'] == "")
		{
	        // Destroy all the session variables
	        $_SESSION = array();
	        session_destroy();
		}

        // Displays the 'logout' button and the menubar
        echo "
                            <FONT COLOR='".$HEADER_FONT_COLOR."'>
                                Welcome <BR>
                            </FONT>
                            <FONT COLOR='".$USERNAME_FONT_COLOR."'>".htmlentities($_SESSION['name'])." <BR>
                            </FONT>
                            <FORM ACTION='index.php?do=logout' METHOD='POST'>
                                <INPUT TYPE='SUBMIT' NAME='Logout' VALUE='Logout'>
                            </FORM>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
			<TR ALIGN='CENTER' VALIGN='TOP'>
				<TD COLSPAN='3' HEIGHT='1'>
					<TABLE BORDER='1' WIDTH='100%' BGCOLOR='".$TABS_BACKGROUNG_COLOR."' HEIGHT='1' ALIGN='CENTER' VALIGN='CENTER'>
						<TR>
							<TD ALIGN='CENTER'>
								<A HREF='user.php'> Users </A>
							</TD>";

		// If the logged user is an administrator or a manager
		if ((ConnectedUserBelongsToAdminGroup() == TRUE) || (ConnectedUserBelongsToManagerGroup() == TRUE))
		{
	        // Displays the group tab
	        echo "
							<TD ALIGN='CENTER'>
								<A HREF='group.php'> Groups </A>
							</TD>";
		}

        echo "
							<TD ALIGN='CENTER'>
								<A HREF='project.php'> Projects </A>
							</TD>
							<TD ALIGN='CENTER'>
								<A HREF='activity.php'> Activities </A>
							</TD>
							<TD ALIGN='CENTER'>
								<A HREF='task.php'> Current Tasks </A>
							</TD>
							<TD ALIGN='CENTER'>
								<A HREF='task_search.php'> Search Tasks </A>
							</TD>
							<TD ALIGN='CENTER'>
								<A HREF='task_all.php'> All Tasks </A>
							</TD>
							<TD ALIGN='CENTER'>
								<A HREF='stat_day.php'> Day to day Stats </A>
							</TD>
							<TD ALIGN='CENTER'>
								<A HREF='stat_month.php'> Monthly Stats </A>
							</TD>
							<TD ALIGN='CENTER'>
								<A HREF='stat.php'> All Stats </A>
							</TD>
							<TD ALIGN='CENTER'>
								<A HREF='stat_activity.php'> Activities Stats </A>
							</TD>
						</TR>
					</TABLE>
				</TD>
			</TR>
			 ";
    }

    echo "
            <TR ALIGN='CENTER' VALIGN='CENTER'>
                <TD COLSPAN='3'>
          ";

    // If an error message was set
    if ($_SESSION['message'] != "")
    {
        // Displays the error message
        echo "
                    <FONT SIZE='+1' COLOR='".$MESSAGE_FONT_COLOR."'>
                        <B> ".$_SESSION['message']." </B> <BR>
                    </FONT>
            ";

        // Reset the error message global variable
        $_SESSION['message'] = "";
    }
}


// Displays the common page header, plus require user authentication
function ShowSecureHeader($Title, $URL)
{
    // If nobody is already connected
    if ($_SESSION['uid'] == "")
    {
        // Redirects to the login form
        header("Location: index.php?url=$URL");
        exit();
    }
    else // If someone is already connected
    {
        // Shows the standard header
        ShowHeader($Title, $URL);
    }
}


// Displays the common page footer
function ShowFooter()
{
	// Aesthetic Attributes
	$FOOTER_BACKGROUNG_COLOR = "BLACK";
	$FOOTER_FONT_COLOR       = "WHITE";

     // TODO : Change the URL in order to point on the Chronologist homepage.

	echo "
                </TD>
            </TR>
            <TR BGCOLOR='$FOOTER_BACKGROUNG_COLOR' HEIGHT='1' VALIGN='CENTER'>
                <TD COLSPAN='3' ALIGN='CENTER'>
                    <FONT SIZE='-1' COLOR='$FOOTER_FONT_COLOR'>
                        <A HREF='https://gna.org/projects/chronologist/'> Chronologist </A> - 
          ";

    // Gets the version from the database
    $SQL = "SELECT  `label`
			FROM	`versions`
			ORDER BY `vid`
			DESC
		   ";
	$Result = mysql_query($SQL)
	or die("Could not execute the '$SQL' request.");
	$Row = mysql_fetch_array($Result);

	// If a version number was in the database
	if ($Result > 0)
	{
		// Displays the version number
		echo htmlentities($Row['label']);
	}
	else // If no version number was in the database
	{
		echo "!!! Unknow Version !!!";
	}

	mysql_free_result($Result);

	echo "
                    </FONT>
                </TD>
            </TR>
        </TABLE>
    </BODY>
</HTML>
          ";

	// Database disconnection
	global $Connection;
	mysql_close($Connection) ;
}


// Give back a date as a formatted string built from the given time stamp
function GetFormattedDate($TimeStamp)
{
	if ($TimeStamp == 0)
	{
		return "None";
	}
	else
	{
		return strftime("%d/%m/%y", $TimeStamp);
	}
}


// Give back a time as a formatted string built from the given time stamp
function GetFormattedTime($TimeStamp)
{
	if ($TimeStamp == 0)
	{
		return "None";
	}
	else
	{
		return strftime("%Hh%M", $TimeStamp);
	}
}


// Give back a formatted string built from the given time stamp
function GetFormattedDateAndTime($TimeStamp, $Separator = "-")
{
	if ($TimeStamp == 0)
	{
		return "None";
	}
	else
	{
		$Date = GetFormattedDate($TimeStamp);
		$Time = GetFormattedTime($TimeStamp);
		return $Date.$Separator.$Time;
	}
}


// Add slashes to the given string for secure SQL use if needed
function putslashes($String)
{
	// If PHP is not configured to add slashes by default
	if (! get_magic_quotes_gpc())
	{
		// Add shlashes and return the resulting string
		return addslashes($String);
	}

	// Otherwise, return the given string without any modification
	return $String;
}

?>