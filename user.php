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
 * FILE NAME   : user.php
 * DESCRIPTION : User creation form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("user.inc.php");

global $Connection;

// TODO : Be able to manage multiple group for one user
// TODO : For newly created user, add a checkbox to specify wether its password must generated and sent by mail.

// Retrieve the action to perform from the URL given 'do' parameter
$Do = "";
if (!empty($_GET['do'])) {
    $Do  = $_GET['do'];
}

switch($Do)
{
    case "user_add" :
        ShowSecureHeader("New User", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

		// If the logged user is an administrator or a manager, handle user creation
		if ((ConnectedUserBelongsToAdminGroup() == TRUE) || (ConnectedUserBelongsToManagerGroup() == TRUE))
		{
	        ShowUserFields();
	    }
	    else
	    {
	        $_SESSION['message'] = "User creation is reserved to administrator and manager users. <BR> Please try again. <BR>";

	        // Redirects to the user list
    	    header("Location: user.php");
	    }

        ShowFooter();
		break;

   case "user_insert" :
		// If the logged user is not an administrator nor a manager
		if ((ConnectedUserBelongsToAdminGroup() == FALSE) || (ConnectedUserBelongsToManagerGroup() == FALSE))
		{
	        $_SESSION['message'] = "User creation is reserved to administrator and manager users. <BR> Please try again. <BR>";
	    }
	    else // Handle user creation
	    {
			// Gets all the user creation parameters
			$Email                = putslashes($_POST['email']);

	        $Password             = sha1(putslashes($_POST['password']));
	        $ConfirmedNewPassword = sha1(putslashes($_POST['confirmednewpassword']));

	        $FirstName            = ucwords(putslashes($_POST['firstname']));
	        $LastName             = strtoupper(putslashes($_POST['lastname']));

	        $GID                  = putslashes($_POST['gid']);

	        $Expiration           = "0";
	        if (putslashes($_POST['expire']) == TRUE)
	        {
	           // TODO: check all time values !!!
		        $Expiration = mktime(putslashes($_POST['expirationHour']), putslashes($_POST['expirationMinute']), 0, putslashes($_POST['expirationMonth']), putslashes($_POST['expirationDay']), putslashes($_POST['expirationYear']));
	        }

	        // Checks non-optionnal parameters validity
	        if (($Email != "") && ($GID != "") && ($Expiration != ""))
	        {
		        // Checks if there is already a user with the given e-mail address
		        $SQL =	"SELECT `uid`
		                 FROM   `users`
		                 WHERE  `email` = '$Email'
		                ";
		        $Result = mysqli_query($Connection, $SQL)
		        or die("Could not execute the '$SQL' request.");

		        // Test if the given e-mail address is alreadey used by another
		        if (mysqli_num_rows($Result) != 0)
		        {
		            $_SESSION['message'] = "The e-mail address '$Email' is allready used. <BR> Please try again. <BR>";
		        }
		        // Test if two given password are not the same
		        else if ($ConfirmedNewPassword != $Password)
                {
                    $_SESSION['message'] = "The two passwords are not the same. <BR> Please try again. <BR>";
                }
                else
                {
                    // Puts the new user in the database
                    $SQL =  "INSERT INTO `users`
                             VALUES (NULL,
                                     '$Email',
                                     '$Password',
                                     '$FirstName',
                                     '$LastName',
                                      FROM_UNIXTIME('$Expiration'))
                            ";
                    $Result = mysqli_query($Connection, $SQL)
                    or die("Could not execute the '$SQL' request.");

                    // Gets the new user UID
                    $SQL =	"SELECT `uid`
                             FROM   `users`
                             WHERE  `email` = '$Email'
                            ";
                    $Result = mysqli_query($Connection, $SQL)
                    or die("Could not execute the '$SQL' request.");
                    $Row = mysqli_fetch_array($Result);
                    mysqli_free_result($Result);
                    $UID = $Row['uid'];

                    // Puts the new UID-GID association in the database
                    $SQL =  "INSERT INTO `user_groups`
                             VALUES ('$UID', 
                                     '$GID',
                                     'false')
                            ";
                    $Result = mysqli_query($Connection, $SQL)
                    or die("Could not execute the '$SQL' request.");

                    $_SESSION['message'] = "The user has been created succesfully. <BR>";
                }

				mysqli_free_result($Result);
		    }
		    else
		    {
		        $_SESSION['message'] = "One or more non-optionnal parameters was not field. <BR> Please try again. <BR>";
		    }
	    }

        // Redirects to the user list
        header("Location: user.php");
        break;

    case "user_modify" :
        ShowSecureHeader("Modify User", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $UID = putslashes($_POST['uid']);

		// If the logged user is an administrator or a manager, or want to edit its own informations, handle user modification
		if ((ConnectedUserBelongsToAdminGroup() == TRUE) || (ConnectedUserBelongsToManagerGroup() == TRUE) || ($UID == $_SESSION['uid']))
		{
	        ShowUserFields($UID);
	    }
	    else
	    {
	        $_SESSION['message'] = "Other user modification is reserved to administrator and manager users. <BR> Please try again. <BR>";

	        // Redirects to the user list
    	    header("Location: user.php");
    	    exit;
	    }

        ShowFooter();
		break;

   case "user_update" :

        $UID = putslashes($_POST['uid']);

		// If the logged user is an administrator or a manager, or want to edit its own informations, handle user modification
		if ((ConnectedUserBelongsToAdminGroup() == TRUE) || (ConnectedUserBelongsToManagerGroup() == TRUE) || ($UID == $_SESSION['uid']))
		{
			// Gets all the user modification parameters
	        $Email                = putslashes($_POST['email']);

	        $Password             = sha1(putslashes($_POST['password']));
	        $ConfirmedNewPassword = sha1(putslashes($_POST['confirmednewpassword']));

	        $FirstName            = ucwords(putslashes($_POST['firstname']));
	        $LastName             = strtoupper(putslashes($_POST['lastname']));

	        $Expiration = mktime(putslashes($_POST['expirationHour']), putslashes($_POST['expirationMinute']), 0, putslashes($_POST['expirationMonth']), putslashes($_POST['expirationDay']), putslashes($_POST['expirationYear']));

	        // TODO : Checks all parameters validity
	        // TODO : Verify that we are not taking the email of someone else, but still allowing one to change his mail

	        $SQL =	"SELECT `email`
	                 FROM   `users`
	                 WHERE  `uid` = '$UID'
	                ";
	        $Result = mysqli_query($Connection, $SQL)
	        or die("Could not execute the '$SQL' request.");

	        // Check if the given user does not exist
	        if (mysqli_num_rows($Result) == 0)
	        {
	            $_SESSION['message'] = "The user ID '$UID' does not exist. <BR> Please try again. <BR>";
	        }
	        else // If the given user exists
	        {
	            // If the password must be modified
	            if (($Password != "") && ($ConfirmedPassword != ""))
	            {
	                // Test if two given password are really the same
	                if ($ConfirmedNewPassword == $Password)
	                {
	                    // Puts the new user in the database
	                    $SQL =  "UPDATE `users`
	                             SET    `email`      = '$Email',
	                                    `password`   = '$Password',
	                                    `firstname`  = '$FirstName',
	                                    `lastname`   = '$LastName',
	                                    `expiration` =  FROM_UNIXTIME('$Expiration')
	                             WHERE  `uid`        = '$UID'
	                            ";
	                    $Result = mysqli_query($Connection, $SQL)
	                    or die("Could not execute the '$SQL' request.");

	                    $_SESSION['message'] = "The user has been modified succesfully. <BR>";
	                }
	                else // If the two given password are not the same
	                {
	                    $_SESSION['message'] = "The two passwords are not the same. <BR> Please try again. <BR>";
	                }
	            }
	            else // If the password has not been modified
	            {
	                // Puts the new user in the database
	                $SQL =  "UPDATE `users`
	                         SET    `email`      = '$Email',
	                                `firstname`  = '$FirstName',
	                                `lastname`   = '$LastName',
	                                `expiration` = '$Expiration'
	                         WHERE  `uid`        = '$UID'
	                        ";
	                $Result = mysqli_query($Connection, $SQL)
	                or die("Could not execute the '$SQL' request.");
	
	                $_SESSION['message'] = "The user has been modified succesfully. <BR>";
	            }
	        }
	    }
	    else
	    {
	        $_SESSION['message'] = "User modification is reserved to administrator and manager users. <BR> Please try again. <BR>";
	    }

        mysqli_free_result($Result);

        // Redirects to the user list
        header("Location: user.php");
        break;

    case "password_form" :

        $UID = putslashes($_POST['uid']);

        ShowSecureHeader("Password Changing", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        echo "
                <FORM ACTION='user.php?do=password_update' METHOD='POST' NAME='password'>
                    <INPUT TYPE='HIDDEN' NAME='uid' VALUE='".$UID."'>
                    <TABLE BORDER='0'>
                        <TR>
                            <TD ALIGN='RIGHT'>
                                <B> New Password </B>
                            </TD>
                            <TD>
                                <INPUT TYPE='PASSWORD' NAME='newpassword'>
                            </TD>
                        </TR>
                        <TR>
                            <TD ALIGN='RIGHT'>
                                <B> Confirme New Password </B>
                            </TD>
                            <TD>
                                <INPUT TYPE='PASSWORD' NAME='confirmednewpassword'>
                            </TD>
                        </TR>
                        <TR>
                            <TD ALIGN='CENTER' COLSPAN='2'>
                                <BR> <INPUT TYPE='SUBMIT' NAME='ok' VALUE='OK'>
                            </TD>
                        </TR>
                    </TABLE>
                </FORM>
             ";

        ShowFooter();
        break;

    case "password_update" :
        // Gets the given password changing parameters
        $UID                  = putslashes($_POST['uid']);
        $NewPassword          = sha1(putslashes($_POST['newpassword']));
        $ConfirmedNewPassword = sha1(putslashes($_POST['confirmednewpassword']));

		// If the logged user is an administrator, or want to edit its own informations, handle user modification
		if ((ConnectedUserBelongsToAdminGroup() == TRUE) || ($UID == $_SESSION['uid']))
		{
            // TODO : Checks all parameters validity
            // TODO : If the currently logged user is not admin (or in the admin group ???), check the current password !!!
    
            // Checks if the user exists in the database
            $SQL = "SELECT `password`
                    FROM   `users`
                    WHERE  `uid` = '$UID'
                   ";
            $Result = mysqli_query($Connection, $SQL)
            or die("Could not execute the '$SQL' request.");
    
            // If the user exists
            if (mysqli_num_rows($Result) == 1)
            {
                // If the two given password are really the same
                if ($ConfirmedNewPassword == $NewPassword)
                {
                    // Update the password in the database
                    $SQL = "UPDATE `users`
                            SET    `password` = '$NewPassword'
                            WHERE  `uid`      = '$UID'
                           ";
                    $Result = mysqli_query($Connection, $SQL)
                    or die("Could not execute the '$SQL' request.");
                    
                    $_SESSION['message'] = "The password has been changed succesfully. <BR>";
                }
                else // If the two given password are not the same
                {
                    $_SESSION['message'] = "The two passwords are not the same. <BR> Please try again. <BR>";
                }
            }
            else // If the user does not exist
            {
                $_SESSION['message'] = "The requested user '".$UID."' does not exist. <BR> Please try again. <BR>";
            }
        }
        else
        {
            $_SESSION['message'] = "The requested modification is forbidden. <BR> Please try again. <BR>";
        }

        // Redirects to the password changing form
        header("Location: user.php");
        break;

    default :
        ShowSecureHeader("Users List", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        echo "
              <TABLE BORDER='0'>
                  <TR ALIGN='CENTER'>
                      <TD>
                            <FORM ACTION='user.php?do=password_form' METHOD='POST'>
								<INPUT TYPE='HIDDEN' VALUE='".$_SESSION['uid']."' NAME='uid'>
								<INPUT TYPE='SUBMIT' VALUE='Change your Password'>
							</FORM>
                      </TD>
                      <TD>
                            <FORM ACTION='user.php?do=user_modify' METHOD='POST'>
								<INPUT TYPE='HIDDEN' VALUE='".$_SESSION['uid']."' NAME='uid'>
								<INPUT TYPE='SUBMIT' VALUE='Modify your Data'>
							</FORM>
                      </TD>";

		// If the logged user is an administrator or a manager, handle user modification
		if ((ConnectedUserBelongsToAdminGroup() == TRUE) || (ConnectedUserBelongsToManagerGroup() == TRUE))
		{
	        echo "
						<TD>
							<FORM ACTION='user.php?do=user_add' METHOD='POST'>
								<INPUT TYPE='SUBMIT' VALUE='New User'>
							</FORM>
						</TD>
					</TR>
				</TABLE>
				<TABLE BORDER='1'>
					<TR ALIGN='CENTER'>
						<TD>
							<B> Last Name </B>
						</TD>
						<TD>
							<B> First Name </B>
						</TD>
						<TD>
							<B> E-Mail </B>
						</TD>
						<TD>
							<B> Account Expiration </B>
						</TD>
						<TD>
							<B> Groups </B>
						</TD>
					</TR>
	             ";

			// Get all the users from the database
			$UserSQL = "SELECT    *, UNIX_TIMESTAMP(expiration) as timestamp
	                    FROM      users
	                    ORDER BY  lastname, firstname
	                   ";
			$UserResult = mysqli_query($Connection, $UserSQL)
			or die("Could not execute the '$UserSQL' request.");

			// For each user
			while ($UserRow = mysqli_fetch_array($UserResult))
			{
				// Store the current user Id
				$UID = $UserRow['uid'];

				echo "
						<TR>
							<FORM ACTION='user.php?do=user_modify' METHOD='POST'>
								<TD ALIGN='LEFT'>
									".htmlentities($UserRow['lastname'])."
								</TD>
								<TD ALIGN='LEFT'>
									".htmlentities($UserRow['firstname'])."
								</TD>
								<TD ALIGN='LEFT'>
									".htmlentities($UserRow['email'])."
								</TD>
								<TD ALIGN='LEFT'>
									".htmlentities(GetFormattedDateAndTime($UserRow['timestamp']))."
								</TD>
								<TD>
					 ";

				// Gets all the groups the user belongs to
				$GroupSQL = "SELECT    label
	                         FROM      groups, user_groups
	                         WHERE     user_groups.uid = '$UID'
	                         AND       groups.gid      = user_groups.gid
	                         ORDER BY  label
	                        ";
				$GroupResult = mysqli_query($Connection, $GroupSQL)
				or die("Could not execute the '$GroupSQL' request.");

				// Displays all the found groups
				while ($GroupRow = mysqli_fetch_array($GroupResult))
				{
					echo $GroupRow['label']." </BR>";
				}

				mysqli_free_result($GroupResult);

				echo "
								</TD>
								<TD>
									<INPUT TYPE='HIDDEN' NAME='uid' VALUE='".$UID."'>
									<INPUT TYPE='SUBMIT'            VALUE='Modify'>
								</TD>
							</FORM>
						</TR>
					 ";
			}

			mysqli_free_result($UserResult);
	    }
	    else
	    {
	    	echo "</TR>";
	    }

        echo "
                     </TABLE>
             ";

        ShowFooter();
}

?>