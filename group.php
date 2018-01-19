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
 * FILE NAME   : group.php
 * DESCRIPTION : Group creation form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("group.inc.php");
require_once("user.inc.php");

// If the logged user is an administrator or a manager, handle group modification
if ((ConnectedUserBelongsToAdminGroup() == TRUE) || (ConnectedUserBelongsToManagerGroup() == TRUE))
{
	// Retrieve the action to perform from the URL given 'do' parameter
	$Do = $_GET['do'];

	switch($Do)
	{
	    case "add" :
	        ShowSecureHeader("New Group", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

	        ShowGroupFields();

	        ShowFooter();
			break;

	    case "insert" :
	        // Gets the given group label
	        $Label = putslashes($_POST['label']);

	        // Gets currently logged Id
		    $UID   = putslashes($_POST['uid']);

	        // Checks parameter validity
	        if ($Label == "")
		    {
				$_SESSION['message'] = "The label field is empty. <BR> Please try again. <BR>";
		    }
		    else
	        {
		        // Checks if the given group already exists in the database
		        $SQL =	"SELECT `gid`
		                 FROM   `groups`
		                 WHERE  `label` = '$Label'
		                ";
		        $Result = mysqli_query($Connection, $SQL)
		        or die("Could not execute the '$SQL' request.");
		        $RowNumber = mysqli_num_rows($Result);
				mysqli_free_result($Result);

		        // If the given group does not exist
		        if ($RowNumber == 0)
		        {
		            // Puts the given group in the database
		            $SQL =  "INSERT INTO `groups`
		                     VALUES (NULL,
		                             '$Label')
		                    ";
		            $Result = mysqli_query($Connection, $SQL)
		            or die("Could not execute the '$SQL' request.");

		            $GID = mysqli_insert_id();

		            // Puts the link between the newly created group and the logged user (as this group administrator) in the database
		            $SQL =  "INSERT INTO `user_groups`
		                     VALUES ('$UID',
		                             '$GID',
		                              1)
		                    ";
		            $Result = mysqli_query($Connection, $SQL)
		            or die("Could not execute the '$SQL' request.");

		            $_SESSION['message'] = "The group has been created succesfully. <BR>";
		        }
		        else // If the given group already exists
		        {
		            $_SESSION['message'] = "The group label '$Label' is already used. <BR> Please try again. <BR>";
		        }
		    }

	        // Redirects to the group list
	        header("Location: group.php");
	        break;

	    case "link" :
	        ShowSecureHeader("New User-Group Association", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

	        if (ConnectedUserBelongsToAdminGroup() == TRUE)
			{
		        echo "
		        			<FORM ACTION='group.php?do=user_group' METHOD='POST'>
                                <TABLE BORDER='0'>
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <B> User </B>
                                        </TD>
                                        <TD>
                                            <SELECT NAME='uid'>
                 ";

				// Gets all the users from the database
				$SQL = "SELECT  *
	                    FROM   `users`
	                   ";
				$Result = mysqli_query($Connection, $SQL)
				or die("Could not execute the '$SQL' request.");

				// Puts all the groups in a pop-up list
				while ($Row = mysqli_fetch_array($Result))
				{
					echo "<OPTION VALUE='".$Row['uid']."'> ".htmlentities(GetUserName($Row['uid']))." </OPTION>";
				}

				mysqli_free_result($Result);

	            echo "
	                                            </SELECT>
	                                        </TD>
	                                    </TR>
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
					echo "<OPTION VALUE='".$Row['gid']."'> ".htmlentities($Row['label'])." </OPTION>";
				}

				mysqli_free_result($Result);

	            echo "
	                                            </SELECT>
	                                        </TD>
	                                    </TR>
	                                    <TR>
	                                        <TD ALIGN='RIGHT'>
	                                            <B> Can Administer ? </B>
	                                        </TD>
	                                        <TD>
	                                            <INPUT TYPE='CHECKBOX' NAME='administer'>
	                                        </TD>
	                                    </TR>
	                                    <TR>
	                                        <TD ALIGN='CENTER' COLSPAN='2'>
	                                            <INPUT TYPE='SUBMIT' VALUE='OK'>
	                                        </TD>
	                                    </TR>
                                </TABLE>
		        			</FORM>
	                 ";
			}
			else // If the logged user is NOT an administrator, show an error message
			{
				$_SESSION['message'] = "User-Group association is reserved to administrator. <BR> Please try again. <BR>";
				
				// Redirects to the homepage
				header("Location: index.php");
			}

	        ShowFooter();
			break;

	    case "user_group" :
	        // Gets the given user Id
		    $UID = putslashes($_POST['uid']);

	        // Gets the given group Id
		    $GID = putslashes($_POST['gid']);

	        // Gets the given group administering status
	        $Administer = putslashes($_POST['administer']);

			// Check that the association does not already exist
			$SQL = "SELECT *
	                FROM   `user_groups`
	                WHERE  `uid` = '$UID'
	                AND    `gid` = '$GID'
	               ";
			$Result = mysqli_query($Connection, $SQL)
			or die("Could not execute the '$SQL' request.");
			$NumRows = mysqli_num_rows($Result);
			if ($NumRow != 0)
			{
				$_SESSION['message'] = "This User-Group association already exists. <BR> Please try again. <BR>";
			}
			else
			{
                // Checks parameter validity
                if (($UID == "") || ($GID == ""))
                {
                    $_SESSION['message'] = "Some user-group association parameters are missing. <BR> Please try again. <BR>";
                }
                else
                {
                    // Puts the link between the newly created group and the logged user (as this group administrator) in the database
                    $SQL =  "INSERT INTO `user_groups`
                             VALUES ('$UID',
                                     '$GID',
                                     '$Administer')
                            ";
                    $Result = mysqli_query($Connection, $SQL)
                    or die("Could not execute the '$SQL' request.");
    
                    $_SESSION['message'] = "The user-group association has been created succesfully. <BR>";
                }
            }

	        // Redirects to the group list
	        header("Location: group.php");
	        break;

	    case "modify" :
	        ShowSecureHeader("Modify Group", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

	        ShowGroupFields(putslashes($_POST['gid']));

	        ShowFooter();
			break;

	    case "update" :
	        // Gets the given group label
	        $Label      = putslashes($_POST['label']);

	        // Gets currently logged Id
	        $UID        = putslashes($_POST['uid']);

	        // Gets the given group Id
	        $GID        = putslashes($_POST['gid']);

	        // Gets the given group administering status
	        $Administer = putslashes($_POST['administer']);

	        // Checks parameter validity
	        if ($Label != "")
	        {
		        // Checks if the given group already exists in the database
		        $SQL =	"SELECT `label`
		                 FROM   `groups`
		                 WHERE  `gid`= '$GID'
		                ";
		        $Result = mysqli_query($Connection, $SQL)
		        or die("Could not execute the '$SQL' request.");

		        // If the given group does not exist
		        if (mysqli_num_rows($Result)== 0)
		        {
		            $_SESSION['message'] = "The group ID '$GID' does not exist. <BR> Please try again. <BR>";
		        }
		        else // If the given group exists
		        {
		            // Change the given group label in the database
		            $SQL =  "UPDATE `user_groups`
		                     SET    `administer` = '$Administer'
		                     WHERE  `gid`        = '$GID'
		                     AND    `uid`        = '$UID'
		                    ";
		            $Result = mysqli_query($Connection, $SQL)
		            or die("Could not execute the '$SQL' request.");

		            // Change the given group label in the database
		            $SQL =  "UPDATE `groups`
		                     SET    `label` = '$Label'
		                     WHERE  `gid`   = '$GID'
		                    ";
		            $Result = mysqli_query($Connection, $SQL)
		            or die("Could not execute the '$SQL' request.");

		            $_SESSION['message'] = "The group has been modified succesfully. <BR>";
		        }

				mysqli_free_result($Result);
		    }
		    else
		    {
				$_SESSION['message'] = "The label field is empty. <BR> Please try again. <BR>";
		    }

	        // Redirects to the group list
	        header("Location: group.php");
	        break;

	    default :
	        ShowSecureHeader("Groups List", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

	        echo "
	              <TABLE BORDER='0'>
	                  <TR ALIGN='CENTER'>
	                      <TD>
	                            <FORM ACTION='group.php?do=add' METHOD='POST'>
									<INPUT TYPE='SUBMIT' VALUE='New Group'>
								</FORM>
	                      </TD>
	                      <TD>
	                            <FORM ACTION='group.php?do=link' METHOD='POST'>
									<INPUT TYPE='SUBMIT' VALUE='New User-Group Association'>
								</FORM>
	                      </TD>
	                  </TR>
	              </TABLE>
                  <TABLE BORDER='1'>
                      <TR ALIGN='CENTER'>
                          <TD>
                              <B> Group </B>
                          </TD>
                      </TR>
	             ";

			// Gets all the groups from the database
			$SQL = "SELECT *
	                FROM   `groups`
	               ";
			$Result = mysqli_query($Connection, $SQL)
			or die("Could not execute the '$SQL' request.");

			// For each group
			while ($Row = mysqli_fetch_array($Result))
			{
				// Displays its label
				$Label = $Row['label'];
				$GID   = $Row['gid'];
				echo "
                        <TR>
                            <FORM ACTION='group.php?do=modify' METHOD='POST'>
                                <TD ALIGN='LEFT'>
                                    ".htmlentities($Label)."
                                </TD>
                                <TD>
                                    <INPUT TYPE='HIDDEN' NAME='gid'   VALUE='".$GID."'>
                                    <INPUT TYPE='HIDDEN' NAME='label' VALUE='".$Label."'>
                                    <INPUT TYPE='SUBMIT'              VALUE='Modify'>
                                </TD>
                            </FORM>
                        </TR>
					 ";
			}

			mysqli_free_result($Result);

	        echo "
                    </TABLE>
	             ";

	        ShowFooter();
	}
}
else // If the logged user is NOT an administrator NOR a manager, show an error message
{
	$_SESSION['message'] = "Group modification is reserved to administrator and manager users. <BR> Please try again. <BR>";

	// Redirects to the homepage
	header("Location: index.php");
}

?>