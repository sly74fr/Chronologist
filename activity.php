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
 * FILE NAME   : activity.php
 * DESCRIPTION : Activities creation form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("activity.inc.php");
require_once("user.inc.php");

// Retrieve the action to perform from the URL given 'do' parameter
$Do = $_GET['do'];


switch($Do)
{
    case "add" :
        ShowSecureHeader("Activities Creation", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        ShowActivityFields();

        ShowFooter();
		break;

    case "insert" :
        // Gets the given activities label parameter
        $PAID  = putslashes($_POST['paid']);
        $Label = putslashes($_POST['label']);

        // Checks 'activities' parameter validity
        if ($PAID == "-1")
        {
            $_SESSION['message'] = "The parent activities has not been set. <BR> Please try again. <BR>";
        }
        else if ($Label == "") // Checks 'Label' parameter validity
        {
            $_SESSION['message'] = "The label field is empty. <BR> Please try again. <BR>";
        }
        else
        {
            // TODO : Checks that the given activities label doesn't already exists in the database for the selected sub-activities

            // Insert the new activities in the database
            $SQL =  "INSERT INTO `activities`
                     VALUES (NULL,
                             '$PAID',
                             '$Label')
                    ";
            $Result = mysqli_query($Connection, $SQL)
            or die("Could not execute the '$SQL' request.");

            $AID = mysqli_insert_id($Connection);
            if (ConnectedUserBelongsToAdminGroup() == TRUE)
            {
                // ... ??? !
            }
            else if (ConnectedUserBelongsToManagerGroup() == TRUE)
            {
                // TODO : Find in wich group to add the activities !!!
            }
            else
            {
                // Insert the new activities link with the user in the database
/*
                $UID = $_SESSION['uid'];
                $SQL =  "INSERT INTO `user_activities`
                         VALUES ('$UID',
                                 '$AID')
                        ";
                $Result = mysqli_query($Connection, $SQL)
                or die("Could not execute the '$SQL' request.");
*/
            }

            $_SESSION['message'] = "The activities has been created succesfully. <BR>";
        }

        // Redirects to the activities list
        header("Location: activity.php");
        break;

    case "modify" :
        ShowSecureHeader("Modify Activities", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        ShowActivityFields(putslashes($_POST['aid']));

        ShowFooter();
		break;

    case "update" :
        // Gets the given activities label
        $AID   = putslashes($_POST['aid']);
        $PAID  = putslashes($_POST['paid']);
        $Label = putslashes($_POST['label']);

        // Checks 'activities' parameter validity
        if ($PAID == "-1")
        {
            $_SESSION['message'] = "The parent activities has not been set. <BR> Please try again. <BR>";
        }
        else if ($Label == "") // Checks 'Label' parameter validity
        {
            $_SESSION['message'] = "The label field is empty. <BR> Please try again. <BR>";
        }
        else
        {
            // TODO : Checks that the given activities label doesn't already exists in the database for the selected sub-activities

            // Change the given activities label in the database
            $SQL = "UPDATE `activities`
                    SET    `label` = '$Label',
                           `paid`  = '$PAID'
                    WHERE  `aid`   = '$AID'
                   ";
            $Result = mysqli_query($Connection, $SQL)
            or die("Could not execute the '$SQL' request.");

            $_SESSION['message'] = "The activities has been modifyed succesfully. <BR>";
        }

        // Redirects to the activities list
        header("Location: activity.php");
        break;

    default :
        ShowSecureHeader("Activities List", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        echo "
              <TABLE BORDER='0'>
                  <TR ALIGN='CENTER'>
                      <TD>
                            <FORM ACTION='activity.php?do=add' METHOD='POST'>
								<INPUT TYPE='SUBMIT' VALUE='New Activity'>
							</FORM>
                      </TD>
                  </TR>
              </TABLE>
            <TABLE BORDER='1'>
                <TR ALIGN='CENTER'>
                    <TD>
                        <B> Activity </B>
                    </TD>
                    <TD>
                        <B> Closed ? </B>
                    </TD>
                </TR>
             ";

			$Array = array();
			$Array = GetSubActivities();
/*
			if (ConnectedUserBelongsToAdminGroup() == TRUE)
			{
				// Displays all the activitiess from the database
				$Array = GetSubActivities();
			}
			else if (ConnectedUserBelongsToManagerGroup() == TRUE)
			{
				foreach($_SESSION['gid'] as $Key => $GID)
				{
					// Displays all the group activities from the database
					$Array = $Array + GetGroupSubActivities($GID);
				}
			}
			else
			{
				// Displays all the user activitiess from the database
				$Array = GetUserSubactivities($_SESSION['uid']);
			}
*/

			foreach($Array as $AID => $Label)
			{
				echo "
                    <TR>
                        <FORM ACTION='activity.php?do=modify' METHOD='POST'>
                            <TD ALIGN='LEFT'>
                                ".htmlentities($Label)."
                            </TD>
                            <TD ALIGN='CENTER'>
                                <INPUT TYPE='CHECKBOX' NAME='closed'";

            // Gets the data with the given ID
            $SQL = "SELECT  DISTINCT *
                    FROM   `activities`
                    WHERE  `aid` = '$AID'
                   ";
            $Result = mysqli_query($Connection, $SQL)
            or die("Could not execute the '$SQL' request.");
            $Row = mysqli_fetch_array($Result);
            mysqli_free_result($Result);
    
            $Closed = false;
            if ($Row['closed'] == 1)
            {
                $Closed = true;
            }
	        // If the project is closed
	        if ($Closed == true)
	        {
		        echo " CHECKED";
		    }

            echo " DISABLED>
                            </TD>
                            <TD>
                                <INPUT TYPE='HIDDEN' NAME='aid'   VALUE='".$AID."'>
                                <INPUT TYPE='SUBMIT'              VALUE='Modify'>
                            </TD>
                        </FORM>
                    </TR>
				 ";
			}

        echo "
            </TABLE>
             ";

        ShowFooter();
}

?>