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
 * FILE NAME   : install.php
 * DESCRIPTION : Installation script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


// TODO : Cautious use of '<INPUT TYPE="FILE" NAME="hello">' for restoring a backup ???

require_once("design.inc.php");

// If the database configuration file already exists
if (file_exists("db.inc.php"))
{
	// Launch the login script
	header("Location: index.php");
}
else
{
	require_once("install.inc.php");

	// Session handling.
	session_start();

	// Retrieve the action to perform from the URL given 'do' parameter
	$Do  = $_GET['do'];
	switch($Do)
	{
	    case "configure" :
	    	$MySqlServerAddress      = putslashes($_POST['mysql_server_address']);
	    	$MySqlRootName           = putslashes($_POST['mysql_root_name']);
	    	$MySqlRootPassword       = putslashes($_POST['mysql_root_password']);

	    	$DatabaseName            = putslashes($_POST['database_name']);
	    	$WebServerAddress        = putslashes($_POST['web_server_address']);
	    	$UserName                = putslashes($_POST['user_name']);
	    	$UserPassword            = putslashes($_POST['user_password']);

	    	$AdminEmail              = putslashes($_POST['admin_email']);
	    	$AdminFirstName          = putslashes($_POST['admin_first_name']);
	    	$AdminLastName           = putslashes($_POST['admin_last_name']);
	    	$AdminPassword           = putslashes($_POST['admin_password']);
	    	$AdminPasswordConfirmed  = putslashes($_POST['admin_password_confirmed']);

	    	// Check 'mysql_server_address' parameter validity
	    	if ($MySqlServerAddress == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'MySql Server Address' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'mysql_root_name' parameter validity
	    	if ($MySqlRootName == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'MySQL Root Name' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'database_name' parameter validity
	    	if ($DatabaseName == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'Database Name' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'web_server_address' parameter validity
	    	if ($WebServerAddress == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'Web Server Address' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'user_name' parameter validity
	    	if ($UserName == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'Database User Name' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'user_password' parameter validity
	    	if ($UserPassword == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'Database User Password' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'admin_email' parameter validity
	    	if ($AdminEmail == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'Administrator E-Mail' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'admin_password' parameter validity
	    	if ($AdminPassword == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'Administrator User Password' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'admin_password_confirmed' parameter validity
	    	if ($AdminPasswordConfirmed == "")
	    	{
		       	$_SESSION['message'] = "Parameter 'Confirme Administrator User Password' is missing. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Check 'admin_password' parameter against 'admin_password_confirmed' parameter validity
	    	if ($AdminPassword != $AdminPasswordConfirmed)
	    	{
		       	$_SESSION['message'] = "'Admin Password' parameter is not the same as the 'Confirme Admin Password' parameter. <BR> Please try again. <BR>";
	        	header("Location: install.php");
	        	break;
	    	}

	    	// Try to create a new database with its associated user
	    	if (CreateUserAndDatabase($MySqlServerAddress, $MySqlRootName, $MySqlRootPassword, $WebServerAddress, $DatabaseName, $UserName,  $UserPassword)
	    	    == FALSE)
			{
	        	header("Location: install.php");
	        	break;
			}

			// Try to create a new configuration file containing the database access parameters
			if (CreateConfigFile($MySqlServerAddress, $UserName, $UserPassword, $DatabaseName) == FALSE)
			{
	        	header("Location: install.php");
	        	break;
			}

			// Try to create and populate all the database tables
	    	if (PopulateDatabase($MySqlServerAddress, $UserName, $UserPassword, $DatabaseName, $AdminEmail, $AdminPassword, $AdminFirstName, $AdminLastName)
	    	    == FALSE)
			{
	        	header("Location: install.php");
	        	break;
			}

	        // Redirect to the login page
	       	$_SESSION['message'] = "Configuration Done. <BR> Enjoy managing your time with Chronologist. <BR>";
	        header("Location: index.php");
	        break;

	    default :
			// Aesthetic Attributes
			$BACKGROUNG_COLOR        = "WHITE";
			$HEADER_BACKGROUNG_COLOR = "GRAY";
			$HEADER_FONT_COLOR       = "WHITE";
			$USERNAME_FONT_COLOR     = "RED";
			$MESSAGE_FONT_COLOR      = "RED";
			$FOOTER_BACKGROUNG_COLOR = "BLACK";
			$FOOTER_FONT_COLOR       = "WHITE";

		    echo "
<HTML>
    <HEAD>
        <TITLE> Welcome to Chronologist </TITLE>
    </HEAD>

    <BODY BGCOLOR='".$BACKGROUNG_COLOR."' TOPMARGIN='0' LEFTMARGIN='0' MARGINHEIGHT='0' MARGINWIDTH='0'>
        <TABLE WIDTH='100%' HEIGHT='100%' BORDER='0' CELLSPACING='0' CELLPADDING='5'>
            <TR BGCOLOR='".$HEADER_BACKGROUNG_COLOR."' HEIGHT='1' ALIGN='CENTER' VALIGN='CENTER'>
                <TD WIDTH='8%'>
                </TD>
                <TD>
                    <FONT COLOR='".$HEADER_FONT_COLOR."' SIZE='+10'>
                        <B> Welcome to Chronologist </B>
                    </FONT>
                </TD>
                <TD WIDTH='10%'>
                </TD>
            </TR>
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

	        // Display the user login form
	        echo "
                           <FORM ACTION='install.php?do=configure' METHOD='POST' NAME='configuration'>
                               <TABLE BORDER='0'>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> MySQL Server Host Name </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='mysql_server_address' VALUE='localhost'>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Database Root Name </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='mysql_root_name' VALUE='root'>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <I> Database Root Password </I>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='PASSWORD' NAME='mysql_root_password' VALUE=''>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='CENTER' COLSPAN='2'>
                                           <BR>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Web Server Host Name </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='web_server_address' VALUE='localhost'>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Database Base Name </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='database_name' VALUE='Chronologist'>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Database User Name </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='user_name' VALUE='Chronologist'>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Database User Password </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='PASSWORD' NAME='user_password' VALUE=''>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='CENTER' COLSPAN='2'>
                                           <BR>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Administrator E-Mail </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='admin_email' VALUE=''>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <I> Administrator First Name </I>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='admin_first_name' VALUE=''>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <I> Administrator Last Name </I>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='admin_last_name' VALUE=''>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Administrator User Password </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='PASSWORD' NAME='admin_password' VALUE=''>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Confirme Administrator User Password </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='PASSWORD' NAME='admin_password_confirmed' VALUE=''>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='CENTER' COLSPAN='2'>
                                           <BR> <INPUT TYPE='SUBMIT' NAME='configuration' VALUE='Configure'>
                                       </TD>
                                   </TR>
                               </TABLE>
                           </FORM>
                </TD>
            </TR>
            <TR BGCOLOR='$FOOTER_BACKGROUNG_COLOR' HEIGHT='1' VALIGN='CENTER'>
                <TD COLSPAN='3' ALIGN='CENTER'>
                    <FONT SIZE='-1' COLOR='$FOOTER_FONT_COLOR'>
                        Chronologist
                    </FONT>
                </TD>
            </TR>
        </TABLE>
    </BODY>
</HTML>";
			break;
	}
}

?>