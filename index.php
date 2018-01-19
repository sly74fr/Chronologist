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
 * FILE NAME   : index.php
 * DESCRIPTION : Contains the homepage.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */

require_once("design.inc.php");

// Retrieve the action to perform from the URL given 'do' parameter
$Do  = $_GET['do'];

switch($Do)
{
    case "login" :
        // If somebody was already connected
        if ($_SESSION['uid'] != "")
        {
	        // Destroy all the previous session variables
	        $_SESSION = array();
	        session_destroy();
		}

        // Get all the given login parameters
        $Email    = putslashes($_POST['email']);
        $Password = sha1(putslashes($_POST['password']));

        // Checks parameters validity
        if (($Email != "") && ($Password != ""))
        {
	        // Checks if the user exists in the database
	        $UserSQL = "SELECT *, UNIX_TIMESTAMP(expiration) as timestamp
	                    FROM   `users`
	                    WHERE  `email` = '$Email'
	                   ";
	        $UserResult = mysqli_query($Connection, $UserSQL)
	        or die("Could not execute the '$UserSQL' request.");

	        // If the user exists
	        if (mysqli_num_rows($UserResult) == 1)
	        {
				// Remember the user ID
				$UserArray  = mysqli_fetch_array($UserResult);
				$UID        = $UserArray['uid'];
				$Expiration = $UserArray['timestamp'];

				// If the user account has not expired yet (or will never expire)
				if (($Expiration > mktime()) || ($Expiration == 0))
				{
		            // Check if the given password is the same as the one in the database
		            $PasswordSQL = "SELECT `uid`
		                            FROM   `users`
		                            WHERE  `uid`      = '$UID'
		                            AND    `password` = '$Password'
		                           ";
		            $PasswordResult = mysqli_query($Connection, $PasswordSQL)
		            or die("Could not execute the '$PasswordSQL' request.");

		            // If the password is correct
		            if (mysqli_num_rows($PasswordResult) == 1)
		            {
						// Get all the user's groups from the database
						$GroupSQL = "SELECT *
		                             FROM   `user_groups`
		                             WHERE  `uid` = '$UID'
		                            ";
						$GroupResult = mysqli_query($Connection, $GroupSQL)
						or die("Could not execute the '$GroupSQL' request.");

						// Put all the groups of the current user in the array
						$Array = array();
						while ($GroupRow = mysqli_fetch_array($GroupResult))
						{
							// Put the current group ID in the array
							$Array[] = $GroupRow['gid'];
						}

				        mysqli_free_result($GroupResult);

						// Set session variables accordinally to enable authenticated mode
						$_SESSION['gid'] = $Array;
						$_SESSION['uid'] = $UID;
		            }
		            else // If the password is not correct
		            {
		                $_SESSION['message'] = "You just enter a wrong username or password. <BR> Please try again. <BR>";
		            }

			        mysqli_free_result($PasswordResult);
		        }
		        else // If the user account has expired
		        {
		            // TODO : Display THE user Chronologist manager is available, global administrator otherwise
		            // TODO : Must enforce administrotar/manager mail existance
	
			        // Checks if the 'admin' user exists in the database
			        $SQL = "SELECT `firstname`, `lastname`, `email`
			                FROM   `users`
			                WHERE  `uid` = '1'
			               ";
			        $Result = mysqli_query($Connection, $SQL)
			        or die("Could not execute the '$SQL' request.");

			        // If the 'admin' user exists
			        if (mysqli_num_rows($Result) == 1)
			        {
						// Remember the user ID
						$Array = mysqli_fetch_array($Result);
	
			            $_SESSION['message'] = "Your account has expired. <BR> Please contact <a href='mailto:".$Array['email']."'>".$Array['firstname']
			                                  ." ".$Array['lastname']."</a>.<BR>";
					}
					else
			        {
			            $_SESSION['message'] = "Your account has expired. <BR> Please contact your Chronologist administrator.<BR>";
					}

			        mysqli_free_result($Result);
		        }
	        }
	        else // If the user does not exist
	        {
	            $_SESSION['message'] = "You just enter a wrong username or password. <BR> Please try again. <BR>";
	        }

	        mysqli_free_result($UserResult);
	    }
	    else
	    {
			$_SESSION['message'] = "The email or password field is empty. <BR> Please try again. <BR>";
	    }

        // Redirect to the task creation page
        header("Location: task.php?do=add");
        // Redirect to the front page
        //header("Location: index.php");
        break;

    case "logout" :
        // Destroy all the session variables
        $_SESSION = array();
        session_destroy();

        // Redirect to the login page
        header("Location: index.php");
        break;

    default :
        ShowHeader("Homepage");

        // If somebody is already connected
        if ($_SESSION['uid'] != "")
	    {
	        // Display a welcome message
	        echo "
	                                <TABLE BORDER='0'>
	                                    <TR>
	                                        <TD ALIGN='CENTER' COLSPAN='2'>
	                                            Welcome on the Chronologist system
	                                        </TD>
	                                    </TR>
	                                </TABLE>
	             ";
	    }
	    else // If nobody is connected yet
        {
	        // Display the user login form
	        echo "
	                            <FORM ACTION='index.php?do=login' METHOD='POST' NAME='connection'>
	                                <TABLE BORDER='0'>
	                                    <TR>
	                                        <TD ALIGN='RIGHT'>
	                                            <B> E-Mail </B>
	                                        </TD>
	                                        <TD>
	                                            <INPUT TYPE='TEXT' NAME='email'>
	                                        </TD>
	                                    </TR>
	                                    <TR>
	                                        <TD ALIGN='RIGHT'>
	                                            <B> Password </B>
	                                        </TD>
	                                        <TD>
	                                            <INPUT TYPE='PASSWORD' NAME='password'>
	                                        </TD>
	                                    </TR>
	                                    <TR>
	                                        <TD ALIGN='CENTER' COLSPAN='2'>
	                                            <BR> <INPUT TYPE='SUBMIT' NAME='login' VALUE='Login'>
	                                        </TD>
	                                    </TR>
	                                </TABLE>
	                            </FORM>
	             ";
	    }

        ShowFooter();
}

?>