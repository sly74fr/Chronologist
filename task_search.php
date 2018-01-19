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
 * FILE NAME   : task_search.php
 * DESCRIPTION : Task searching form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("task_search.inc.php");
require_once("task.inc.php");
require_once("project.inc.php");
require_once("activity.inc.php");

// Retrieve the action to perform from the URL given 'do' parameter
$Do = $_GET['do'];
$GetPID = putslashes($_GET['pid']);


switch($Do)
{
    case "search" :
		$SessionUID = $_SESSION['uid'];

        // Gets the given task search parameters
        $TaskProject  = putslashes($_POST['pid']);
        if ($TaskProject == "")
        {
            $TaskProject = $GetPID;
        }
 
        $TaskActivity = putslashes($_POST['aid']);
        $TaskLabel    = putslashes($_POST['label']);
        $TaskMinute   = putslashes($_POST['beginningMinute']);
        $TaskHour     = putslashes($_POST['beginningHour']);
        $TaskDay      = putslashes($_POST['beginningDay']);
        $TaskMonth    = putslashes($_POST['beginningMonth']);
        $TaskYear     = putslashes($_POST['beginningYear']);

        $TaskMinTime   = -1;
        $TaskMaxTime   = -1;
        // If only a year was given
        if (($TaskMinute == 0) && ($TaskHour == 0) && ($TaskDay == 0) && ($TaskMonth == 0) && ($TaskYear != 0))
        {
            // Beginning date is the first day of the given year
            $TaskMinTime = mktime(0, 0, 0, 01, 01, $TaskYear);
            // End date is the last day of the given year
            $TaskMaxTime = mktime(0, 0, 0, 12, 31, $TaskYear);
        }
        // If only a year and a month were given
        if (($TaskMinute == 0) && ($TaskHour == 0) && ($TaskDay == 0) && ($TaskMonth != 0) && ($TaskYear != 0))
        {
            // Beginning date is the first day of the given month and year
            $TaskMinTime = mktime(0, 0, 0, $TaskMonth,     1, $TaskYear);
            // End date is the last day of the given month and year
            $TaskMaxTime = mktime(0, 0, 0, $TaskMonth + 1, 0, $TaskYear);
        }
        // If a year, a month and a day were given
        if (($TaskMinute == 0) && ($TaskHour == 0) && ($TaskDay != 0) && ($TaskMonth != 0) && ($TaskYear != 0))
        {
            // Beginning date is the first minute of the given day, month and year
            $TaskMinTime = mktime(0, 0, 0, $TaskMonth, $TaskDay,     $TaskYear);
            // End date is the last minute of the given day, month and year
            $TaskMaxTime = mktime(23, 59, 0, $TaskMonth, $TaskDay, $TaskYear);
        }
        // If a year, a month, a day and an hour were given
        if (($TaskMinute == 0) && ($TaskHour != 0) && ($TaskDay != 0) && ($TaskMonth != 0) && ($TaskYear != 0))
        {
            // Beginning date is the first minute of the given hour, day, month and year
            $TaskMinTime = mktime($TaskHour, 0, 0, $TaskMonth, $TaskDay,     $TaskYear);
            // End date is the last minute of the given hour, day, month and year
            $TaskMaxTime = mktime($TaskHour, 59, 0, $TaskMonth, $TaskDay, $TaskYear);
        }
        // If a year, a month, a day, an hour and a minute were given
        if (($TaskMinute != 0) && ($TaskHour != 0) && ($TaskDay != 0) && ($TaskMonth != 0) && ($TaskYear != 0))
        {
            // Beginning date is the first seconde of the given minute, hour, day, month and year
            $TaskMinTime = mktime($TaskHour, $TaskMinute, 0,  $TaskMonth, $TaskDay,     $TaskYear);
            // End date is the last seconde of the given minute, hour, day, month and year
            $TaskMaxTime = mktime($TaskHour, $TaskMinute, 59, $TaskMonth, $TaskDay, $TaskYear);
        }

        $TaskDuration = DurationFromHoursAndMinutes(putslashes($_POST['durationHour']), putslashes($_POST['durationMinute']));

        $TaskEnd = $TaskBeginning + $TaskDuration;

        // TODO : Checks beginning date vaildity with 'bool checkdate(int month, int day, int year)'
        // TODO : Checks beginning time validity with '??????'

        // SQL query header
		$TaskSQL = "SELECT *, UNIX_TIMESTAMP(beginning) as timestamp
    			    FROM  `tasks`
    			    WHERE `uid` = '$SessionUID'";

        // Compose the complete SQL query from each given parameter
        if ($TaskLabel != "")
        {
    		// TODO : search any of the given words in the whole label
    		$TaskSQL .= " AND `label` LIKE '$TaskLabel'";
        }
        if ($TaskProject != -1)
        {
    		$TaskSQL .= " AND `pid` = '$TaskProject'";
        }
        if (($TaskActivity != -1) && ($TaskActivity != ""))
        {
    		$TaskSQL .= " AND `aid` = '$TaskActivity'";
        }
        if ($TaskMinTime != -1)
        {
    		$TaskSQL .= " AND `beginning` >= FROM_UNIXTIME('$TaskMinTime')";
        }
        if ($TaskMaxTime != -1)
        {
    		$TaskSQL .= " AND `beginning` <= FROM_UNIXTIME('$TaskMaxTime')";
        }
        if ($TaskDuration != "")
        {
        	$TaskSQL .= " AND `duration` = '$TaskDuration'";
        }
        $TaskSQL .= " ORDER BY timestamp DESC";

		// Gets the tasks that correspond to the given search parameters
		global $Connection;
        $TaskResult = mysqli_query($Connection, $TaskSQL)
		or die("Could not execute the '$TaskSQL' request.");
        $TaskNumRows = mysqli_num_rows($TaskResult);

		// If there is no tasks for the currently logged user
		if (($TaskNumRows == 0))
		{
			// Appends (to the hypothetic one from the 'apply' section for example) a warning message for the user
			$_SESSION['message'] .= "There is no tasks that correspond to the given parameters for the currently logged user !</br>";
		}

        ShowSecureHeader("Task Search Result", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        echo "
              <TABLE BORDER='0'>
                  <TR ALIGN='CENTER'>
                      <TD>
                            <FORM ACTION='task_search.php' METHOD='POST'>
								<INPUT TYPE='SUBMIT' VALUE='New Search'>
							</FORM>
                      </TD>
                  </TR>
              </TABLE>
              ";

		// If there is no tasks for the currently logged user
		if ($TaskNumRows > 0)
		{
            echo "
            <TABLE BORDER='1'>
                <TR ALIGN='CENTER'>
                    <TD>
                        <B> Project </B>
                    </TD>
                    <TD>
                        <B> Label </B>
                    </TD>
                    <TD>
                        <B> Beginning </B>
                    </TD>
                    <TD>
                        <B> Duration </B>
                    </TD>
                </TR>
	             ";

			// TODO : Gets only the projects belonging to the user's groups

			$CurrentDate  = getdate();
			$CurrentYear  = $CurrentDate['year'];
			$CurrentMonth = $CurrentDate['mon'];

			// Displays all the projects
			while ($TaskRow = mysqli_fetch_array($TaskResult))
			{
				$TID           = $TaskRow['tid'];
				$TaskLabel     = $TaskRow['label'];
				echo "
                    <TR>
                        <FORM ACTION='task.php?do=modify' METHOD='POST'>
                            <TD ALIGN='LEFT'>
                                ".htmlentities(GetProjectCompleteLabel($TaskRow['pid']))."
                            </TD>
                            <TD ALIGN='LEFT'>
                                ".htmlentities($TaskLabel)."
                            </TD>
                            <TD ALIGN='CENTER'>
                                ".GetFormattedDateAndTime($TaskRow['timestamp'], '<BR>')."
                            </TD>
                            <TD ALIGN='CENTER'>
                                ".htmlentities(DurationLabel($TaskRow['duration']))."
                            </TD>
                            <TD ALIGN='LEFT'>
                                <INPUT TYPE='HIDDEN' NAME='tid' VALUE='".$TID."'>
                                <INPUT TYPE='SUBMIT' NAME='modify' VALUE='Modify'>
                            </TD>
                        </FORM>
                        <FORM ACTION='task.php?do=add' METHOD='POST'>
                            <TD ALIGN='LEFT'>
                                    <INPUT TYPE='HIDDEN' NAME='timestamp' VALUE='".($TaskRow['timestamp'] + $TaskRow['duration'])."'>
                                    <INPUT TYPE='SUBMIT' VALUE='Add Next Task'>
                            </TD>
                        </FORM>
                    </TR>
				 ";
			}

			mysqli_free_result($TaskResult);

	        echo "
                </TABLE>
	             ";
	    }

        ShowFooter();
        break;

    default :
        ShowSecureHeader("Task Search", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        ShowTaskSearchFields();

        ShowFooter();
}

?>