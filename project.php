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
 * FILE NAME   : project.php
 * DESCRIPTION : Project creation form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("project.inc.php");
require_once("user.inc.php");


// Retrieve the action to perform from the URL given 'do' parameter
$Do = $_GET['do'];


switch($Do)
{
    case "add" :
        ShowSecureHeader("Project Creation", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

        ShowProjectFields();

        ShowFooter();
		break;

    case "insert" :
        // Gets the given project label parameter
        $PPID  = putslashes($_POST['ppid']);
        $Label = putslashes($_POST['label']);
        $Closed = "0";
        if (putslashes($_POST['closed']) == TRUE)
        {
            $Closed = "1";
        }

        // Checks 'Project' parameter validity
        if ($PPID == "-1")
        {
            $_SESSION['message'] = "The parent project has not been set. <BR> Please try again. <BR>";
        }
        else if ($Label == "") // Checks 'Label' parameter validity
        {
            $_SESSION['message'] = "The label field is empty. <BR> Please try again. <BR>";
        }
        else
        {
            // TODO : Checks that the given project label doesn't already exists in the database for the selected sub-project

            // Insert the new project in the database
            $SQL =  "INSERT INTO `projects`
                     VALUES ('',
                             '$PPID',
                             '$Label',
                             '$Closed')
                    ";
            $Result = mysql_query($SQL)
            or die("Could not execute the '$SQL' request.");

            $PID = mysql_insert_id();
            if (ConnectedUserBelongsToAdminGroup() == TRUE)
            {
                // ... ??? !
            }
            else if (ConnectedUserBelongsToManagerGroup() == TRUE)
            {
                // TODO : Find in wich group to add the project !!!
            }
            else
            {
                // Insert the new project link with the user in the database
                $UID = $_SESSION['uid'];
                $SQL =  "INSERT INTO `user_projects`
                         VALUES ('$UID',
                                 '$PID')
                        ";
                $Result = mysql_query($SQL)
                or die("Could not execute the '$SQL' request.");
            }

            $_SESSION['message'] = "The project has been created succesfully. <BR>";
        }

        // Redirects to the project list
        header("Location: project.php");
        break;

    case "modify" :
        ShowSecureHeader("Modify Project", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

        ShowProjectFields(putslashes($_POST['pid']));

        ShowFooter();
		break;

    case "update" :
        // Gets the given project label
        $PID   = putslashes($_POST['pid']);
        $PPID  = putslashes($_POST['ppid']);
        $Label = putslashes($_POST['label']);
        $Closed = "0";
        if (putslashes($_POST['closed']) == TRUE)
        {
            $Closed = "1";
        }

        // Checks 'Project' parameter validity
        if ($PPID == "-1")
        {
            $_SESSION['message'] = "The parent project has not been set. <BR> Please try again. <BR>";
        }
        else if ($Label == "") // Checks 'Label' parameter validity
        {
            $_SESSION['message'] = "The label field is empty. <BR> Please try again. <BR>";
        }
        else
        {
            // TODO : Checks that the given project label doesn't already exists in the database for the selected sub-project

            // Change the given project label in the database
            $SQL = "UPDATE `projects`
                    SET    `label`  = '$Label',
                           `ppid`   = '$PPID',
                           `closed` = '$Closed'
                    WHERE  `pid`  = '$PID'
                   ";
            $Result = mysql_query($SQL)
            or die("Could not execute the '$SQL' request.");

            $_SESSION['message'] = "The project has been modifyed succesfully. <BR>";
        }

        // Redirects to the project list
        header("Location: project.php");
        break;

    default :
        ShowSecureHeader("Projects List", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

        echo "
              <TABLE BORDER='0'>
                  <TR ALIGN='CENTER'>
                      <TD>
                            <FORM ACTION='project.php?do=add' METHOD='POST'>
								<INPUT TYPE='SUBMIT' VALUE='New Project'>
							</FORM>
                      </TD>
                  </TR>
              </TABLE>
            <TABLE BORDER='1'>
                <TR ALIGN='CENTER'>
                    <TD>
                        <B> Project </B>
                    </TD>
                    <TD>
                        <B> Closed ? </B>
                    </TD>
                </TR>
             ";

			$Array = array();
			if (ConnectedUserBelongsToAdminGroup() == TRUE)
			{
				// Displays all the projects from the database
				$Array = GetSubProjects();
			}
			else if (ConnectedUserBelongsToManagerGroup() == TRUE)
			{
				foreach($_SESSION['gid'] as $Key => $GID)
				{
					// Displays all the group projects from the database
					$Array = $Array + GetGroupSubProjects($GID);
				}
			}
			else
			{
				// Displays all the user projects from the database
				$Array = GetUserSubProjects($_SESSION['uid']);
			}

			foreach($Array as $PID => $Label)
			{
				echo "
                    <TR>
                        <FORM ACTION='project.php?do=modify' METHOD='POST'>
                            <TD ALIGN='LEFT'>
                                ".htmlentities($Label)."
                            </TD>
                            <TD ALIGN='CENTER'>
                                <INPUT TYPE='CHECKBOX' NAME='closed'";

            // Gets the data with the given ID
            $SQL = "SELECT  DISTINCT *
                    FROM   `projects`
                    WHERE  `pid` = '$PID'
                   ";
            $Result = mysql_query($SQL)
            or die("Could not execute the '$SQL' request.");
            $Row = mysql_fetch_array($Result);
            mysql_free_result($Result);
    
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
                                <INPUT TYPE='HIDDEN' NAME='pid'   VALUE='".$PID."'>
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