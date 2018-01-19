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
 * FILE NAME   : user.inc.php
 * DESCRIPTION : User related functions.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


// TODO : make uneditable expiration date and checkbox for non admin users or other users than the one connected

require_once("design.inc.php");

// Return TRUE if the currently connected user belongs to the 'Administrator' group, FALSE otherwise
function ConnectedUserBelongsToAdminGroup()
{
    // For each group the connected user belongs to
	foreach($_SESSION['gid'] as $Index => $GID)
	{
		// If the current group is the 'admin' one
		if ($GID == 1)
		{
			// Returns TRUE
			return TRUE;
		}
	}

	// Returns FALSE otherwise
	return FALSE;
}

// Return TRUE if the currently connected user belongs to the 'Manager' group, FALSE otherwise
function ConnectedUserBelongsToManagerGroup()
{
    // For each group the connected user belongs to
	foreach($_SESSION['gid'] as $Index => $GID)
	{
		// If the current group is the 'admin' one
		if ($GID == 2)
		{
			// Returns TRUE
			return TRUE;
		}
	}

	// Returns FALSE otherwise
	return FALSE;
}

// Displays the common user form
function GetUserName($UID = 0)
{
    if ($UID != 0)
    {
        // Gets the current user complete name
        $SQL = "SELECT `firstname`, `lastname`, `email`
                 FROM   `users`
                 WHERE  `uid` = '$UID'
               ";
        global $Connection;
        $Result = mysqli_query($Connection, $SQL)
        or die("Could not execute the '$SQL' request.");
    
        // If the current user is not in the database anymore
        if (mysqli_num_rows($Result) <= 0)
        {
            // Sets the error message
            $_SESSION['message'] = "The current user does not exist. <BR> Please try again. <BR>";
    
            $Name = "";
        }
        else
        {
            // Otherwise computes its real name
            $Row  = mysqli_fetch_array($Result);
            $Name = $Row['firstname']." ".$Row['lastname'];
            if ($Name == " ")
            {
                $Name = $Row['email'];
            }
        }
    
        mysqli_free_result($Result);
    
        return $Name;
    }
    
    return NULL;
}

// Displays the common user form
function ShowUserFields($UID = 0)
{
        global $Connection;
		// TODO : Add a javascript check box to specify weither the user has an expiration date or not

		// If no user was specified, show an empty form
		if ($UID == 0)
		{
            $Email                = "";

            $Password             = "";
            $ConfirmedNewPassword = "";

            $FirstName            = "";
            $LastName             = "";

            $GID                  = "";

            $Expiration           = getdate();
            $Expire               = FALSE;

	        echo "<FORM ACTION='user.php?do=user_insert' METHOD='POST'>";
        }
        else // Show a form filled with the given user data
        {
			// Gets the users with the given UID
			$SQL = "SELECT  DISTINCT *, UNIX_TIMESTAMP(expiration) as timestamp
                    FROM   `users`
                    WHERE  `uid` = '$UID'
                   ";
			$Result = mysqli_query($Connection, $SQL)
			or die("Could not execute the '$SQL' request.");

			// Displays all the user data
			$Row = mysqli_fetch_array($Result);
			mysqli_free_result($Result);

            $Email                = $Row['email'];

            $Password             = "";
            $ConfirmedNewPassword = "";

            $FirstName            = $Row['firstname'];
            $LastName             = $Row['lastname'];

            $GID                  = "";

            if ($Row['timestamp'] == 0)
            {
	            $Expire = FALSE;
	            $Expiration = getdate();
	        }
	        else
	        {
	            $Expire     = TRUE;
	            $Expiration = getdate($Row['timestamp']);
	        }

	        echo "<FORM ACTION='user.php?do=user_update' METHOD='POST'>";
        }

        $ExpirationYear       = $Expiration[year];
        $ExpirationMonth      = $Expiration[mon];
        $ExpirationDay        = $Expiration[mday];
        $ExpirationHour       = $Expiration[hours];
        $ExpirationMinute     = $Expiration[minutes];
        $Expiration           = $ExpirationYear."-".$ExpirationMonth."-".$ExpirationDay." "
                               .$ExpirationHour.":".$ExpirationMinute.":00";
        echo "
                                <TABLE BORDER='0'>
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <B> E-Mail </B>
                                        </TD>
                                        <TD>
                                            <INPUT TYPE='TEXT' NAME='email' VALUE='".$Email."'>
                                        </TD>
                                    </TR>
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <B> New Password </B>
                                        </TD>
                                        <TD>
                                            <INPUT TYPE='PASSWORD' NAME='password' VALUE='".$Password."'>
                                        </TD>
                                    </TR>
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <B> Confirme New Password </B>
                                        </TD>
                                        <TD>
                                            <INPUT TYPE='PASSWORD' NAME='confirmednewpassword' VALUE='".$Password."'>
                                        </TD>
                                    </TR>
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <I> First Name </I>
                                        </TD>
                                        <TD>
                                            <INPUT TYPE='TEXT' NAME='firstname' VALUE='".$FirstName."'>
                                        </TD>
                                    </TR>
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <I> Last Name </I>
                                        </TD>
                                        <TD>
                                            <INPUT TYPE='TEXT' NAME='lastname' VALUE='".$LastName."'>
                                        </TD>
                                    </TR>
             ";

        // If the user belongs to the 'admin' group
        if (ConnectedUserBelongsToAdminGroup() == TRUE)
        {
            echo "
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <B> Group </B>
                                        </TD>
                                        <TD>
                                            <SELECT NAME='gid'>
                 ";

			// Gets all the groups from the database
			$SQL = "SELECT  *
                    FROM   `groups`
                   ";
			$Result = mysqli_query($Connection, $SQL)
			or die("Could not execute the '$SQL' request.");

			// Puts all the groups in a pop-up list
			while ($Row = mysqli_fetch_array($Result))
			{
				echo "<OPTION VALUE='".$Row['gid']."'> ".$Row['label']."</OPTION>";
			}

			mysqli_free_result($Result);

            echo "
                                            </SELECT>
                                        </TD>
                                    </TR>
                 ";
        }
        else // If the user does not belong to the 'admin' group
        {
            // TODO : Only put the user groups if he is not the 'admin'

            // Makes the group information invisible so non-editable
			echo "
                                    <INPUT TYPE='HIDDEN' NAME='gid' VALUE='".$GID."'>
                 ";
        }

        echo "
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <B> Account Expiration :</B>
                                        </TD>
                                        <TD>
                                        	<TABLE>
                                        		<TR>
                                        			<TD>
			                                            <INPUT TYPE='CHECKBOX' NAME='administer'";

	        // TODO : Javascript to (un)hide expiration date on checkbox state

	        // If an expiration date is defined
	        if ($Expire == TRUE)
	        {
		        echo " CHECKED";
		    }

            // If the user doesn't belong to the 'admin' group
            if (ConnectedUserBelongsToAdminGroup() == FALSE)
            {
                echo " DISABLED";
            }

            echo ">
                                        			</TD>
                                        			<TD>
			                                            <INPUT TYPE='TEXT' NAME='expirationYear' SIZE='4' MAXLENGTH ='4' VALUE='".$ExpirationYear."'";

            // If the user doesn't belong to the 'admin' group
            if (ConnectedUserBelongsToAdminGroup() == FALSE)
            {
                echo " READONLY";
            }

            echo ">
                                        			</TD>
                                        			<TD>
                                        				/
                                        			</TD>
                                        			<TD>
			                                            <INPUT TYPE='TEXT' NAME='expirationMonth' SIZE='2' MAXLENGTH ='2' VALUE='".$ExpirationMonth."'";

            // If the user doesn't belong to the 'admin' group
            if (ConnectedUserBelongsToAdminGroup() == FALSE)
            {
                echo " READONLY";
            }

            echo ">
                                        			</TD>
                                        			<TD>
                                        				/
                                        			</TD>
                                        			<TD>
			                                            <INPUT TYPE='TEXT' NAME='expirationDay' SIZE='2' MAXLENGTH ='2' VALUE='".$ExpirationDay."'";

            // If the user doesn't belong to the 'admin' group
            if (ConnectedUserBelongsToAdminGroup() == FALSE)
            {
                echo " READONLY";
            }

            echo ">
                                        			</TD>
                                        			<TD>
                                        				-
                                        			</TD>
                                        			<TD>
			                                            <INPUT TYPE='TEXT' NAME='expirationHour' SIZE='2' MAXLENGTH ='2' VALUE='".$ExpirationHour."'";

            // If the user doesn't belong to the 'admin' group
            if (ConnectedUserBelongsToAdminGroup() == FALSE)
            {
                echo " READONLY";
            }

            echo ">
                                        			</TD>
                                        			<TD>
                                        				:
                                        			</TD>
                                        			<TD>
			                                            <INPUT TYPE='TEXT' NAME='expirationMinute' SIZE='2' MAXLENGTH ='2' VALUE='".$ExpirationMinute."'";

            // If the user doesn't belong to the 'admin' group
            if (ConnectedUserBelongsToAdminGroup() == FALSE)
            {
                echo " READONLY";
            }

            echo ">
                                        			</TD>
                                        		</TR>
                                        	</TABLE>
                                        </TD>
                                    </TR>
                                    <TR>
                                        <TD ALIGN='CENTER' COLSPAN='2'>
			                                     <INPUT TYPE='HIDDEN' NAME='uid' VALUE='".$UID."'>
                                            <BR> <INPUT TYPE='SUBMIT' VALUE='OK'>
                                        </TD>
                                    </TR>
                                </TABLE>
                            </FORM>
             ";
}
?>