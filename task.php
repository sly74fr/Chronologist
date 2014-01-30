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
 * FILE NAME   : task.php
 * DESCRIPTION : Task creation form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("task.inc.php");
require_once("project.inc.php");
require_once("activity.inc.php");


// Retrieve the action to perform from the URL given 'do' parameter
$Do = $_GET['do'];


switch($Do)
{
    case "add" :
        ShowSecureHeader("Task Creation", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

        ShowTaskFields(0, putslashes($_POST['timestamp']));

        ShowFooter();
        break;

    case "add_next" :
        ShowSecureHeader("Task Creation", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

        ShowTaskFields(0, putslashes($_GET['timestamp']));

        ShowFooter();
        break;

    case "insert" :
		$SessionUID   = $_SESSION['uid'];

        // Gets the given task parameters
        $TaskProject  = putslashes($_POST['pid']);
        $TaskActivity = putslashes($_POST['aid']);
        $TaskLabel    = putslashes($_POST['label']);

        $TaskBeginning = mktime(putslashes($_POST['beginningHour']), putslashes($_POST['beginningMinute']), 0, putslashes($_POST['beginningMonth']), putslashes($_POST['beginningDay']), putslashes($_POST['beginningYear']));

        $TaskDuration = DurationFromHoursAndMinutes(putslashes($_POST['durationHour']), putslashes($_POST['durationMinute']));

        $TaskEnd = $TaskBeginning + $TaskDuration;

        // TODO : Checks beginning date vaildity with 'bool checkdate ( int month, int day, int year)'
        // TODO : Checks beginning time vaildity with '??????'

        // Checks parameters validity
        if ($TaskProject == "-1")
        {
			$_SESSION['message'] = "The 'project' task parameter has not been set. <BR> Please try again. <BR>";
        }
        else if ($TaskBeginning == "")
        {
        	$_SESSION['message'] .= "The 'beginning' task parameter field is empty. <BR> Please try again. <BR>";
        }
        else if ($TaskDuration == "")
        {
        	$_SESSION['message'] .= "The 'duration' task parameter field is empty. <BR> Please try again. <BR>";
        }
        else 
        {
            if ($TaskActivity == "-1")
            {
                $TaskActivity = 0;
            }

            // Insert the new task in the database
            $SQL =  "INSERT INTO `tasks`
                     VALUES ('',
                             '$SessionUID',
                             '$TaskProject',
                             '$TaskActivity',
                             '$TaskLabel',
                              FROM_UNIXTIME('$TaskBeginning'),
                             '$TaskDuration')
                    ";
            $Result = mysql_query($SQL)
            or die("Could not execute the '$SQL' request.");

            $_SESSION['message'] = "The task has been created succesfully. <BR>";
        }

        // Redirects to the task list
        header("Location: task.php?do=add_next&timestamp=$TaskEnd");
        break;

    case "modify" :
        ShowSecureHeader("Task Modification", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

        ShowTaskFields(putslashes($_POST['tid']), 0);

        ShowFooter();
        break;

    case "update" :
		$SessionUID = $_SESSION['uid'];

        // Gets the given task parameters
        $TID           = putslashes($_POST['tid']);
        $TaskProject   = putslashes($_POST['pid']);
        $TaskActivity  = putslashes($_POST['aid']);
        $TaskLabel     = putslashes($_POST['label']);

        $TaskBeginning = mktime(putslashes($_POST['beginningHour']), putslashes($_POST['beginningMinute']), 0, putslashes($_POST['beginningMonth']), putslashes($_POST['beginningDay']), putslashes($_POST['beginningYear']));

        $TaskDuration  = DurationFromHoursAndMinutes(putslashes($_POST['durationHour']), putslashes($_POST['durationMinute']));

        // TODO : Checks beginning date vaildity with 'bool checkdate ( int month, int day, int year)'
        // TODO : Checks beginning time vaildity with '??????'

        // Checks parameters validity
        if ($TaskProject == "-1")
        {
			$_SESSION['message'] = "The 'project' task parameter has not been set. <BR> Please try again. <BR>";
        }
        else if ($TaskBeginning == "")
        {
        	$_SESSION['message'] .= "The 'beginning' task parameter field is empty. <BR> Please try again. <BR>";
        }
        else if ($TaskDuration == "")
        {
        	$_SESSION['message'] .= "The 'duration' task parameter field is empty. <BR> Please try again. <BR>";
        }
        else 
        {
            if ($TaskActivity == "-1")
            {
                $TaskActivity = 0;
            }

            // Insert the new task in the database
	        $SQL = "UPDATE `tasks`
                    SET `pid`       = '$TaskProject',
                        `aid`       = '$TaskActivity',
                        `label`     = '$TaskLabel',
                        `beginning` =  FROM_UNIXTIME('$TaskBeginning'),
                        `duration`  =  '$TaskDuration'
                    WHERE `tid` = '$TID'
                    ";
            $Result = mysql_query($SQL)
            or die("Could not execute the '$SQL' request.");

            $_SESSION['message'] = "The task has been updated succesfully. <BR>";
        }

        // Redirects to the task list
        header("Location: task.php");
        break;

    case "remove" :
		$SessionUID = $_SESSION['uid'];
        $TID        = putslashes($_POST['tid']);

        $SQL = "SELECT *, UNIX_TIMESTAMP(beginning) as timestamp
                FROM  `tasks`
                WHERE `tid` = '$TID'
                AND   `uid` = '$SessionUID'
               ";
        $Result = mysql_query($SQL)
        or die("Could not execute the '$SQL' request.");
        $NumRows = mysql_num_rows($Result);
        $TaskRow = mysql_fetch_array($Result);

        ShowSecureHeader("Remove Task", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

		// If there is no tasks for the currently logged user
		if ($NumRows == 0)
		{
			// Append (to the hypothetic one from any previous) a warning message for the user
			$_SESSION['message'] .= "There is no task selected !";
		}
		else
		{
            echo "<TABLE BORDER='0'>
                      <TR ALIGN='CENTER'>
                          <TD COLSPAN='2'>
                              Are you sure you want to delete the selected task ? <BR>
                          </TD>
                      </TR>
                      <TR ALIGN='CENTER'>
                          <TD COLSPAN='2'>
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
                                  <TR>
                                      <TD ALIGN='LEFT'>
                                          ".htmlentities(GetProjectCompleteLabel($TaskRow['pid']))."
                                      </TD>
                                      <TD ALIGN='LEFT'>
                                          ".htmlentities($TaskRow['label'])."
                                      </TD>
                                      <TD ALIGN='CENTER'>
                                          ".htmlentities(GetFormattedDateAndTime($TaskRow['timestamp']))."
                                      </TD>
                                      <TD ALIGN='CENTER'>
                                          ".htmlentities(DurationLabel($TaskRow['duration']))."
                                      </TD>
                                  </TR>
                              </TABLE>
                              <BR>
                              <BR>
                          </TD>
                      </TR>
                      <TR ALIGN='CENTER'>
                          <TD>
                                <FORM ACTION='task.php?do=delete' METHOD='POST'>
                                    <INPUT TYPE='HIDDEN' NAME='tid' VALUE='".$TID."'>
                                    <INPUT TYPE='SUBMIT' NAME='delete' VALUE='OK'>
                                </FORM>
                          </TD>
                          <TD>
                                <FORM ACTION='task.php' METHOD='POST'>
                                    <INPUT TYPE='SUBMIT' NAME='cancel' VALUE='Cancel'>
                                </FORM>
                          </TD>
                      </TR>
                  </TABLE>";
        }

        ShowFooter();

		mysql_free_result($Result);
        break;

    case "delete" :
		$SessionUID = $_SESSION['uid'];

        // Gets the given task parameters
        $TID = putslashes($_POST['tid']);

        if ($TID == "")
		{
			// Append (to the hypothetic one from any previous) a warning message for the user
			$_SESSION['message'] .= "There is no task selected !";
		}
		else
        {
            // Delete the task from the database
	        $SQL = "DELETE FROM `tasks`
                    WHERE       `tid`   = '$TID'
                    ";
            $Result = mysql_query($SQL)
            or die("Could not execute the '$SQL' request.");

            $_SESSION['message'] = "The task has been deleted succesfully. <BR>";
        }

        // Redirects to the task list
        header("Location: task.php");
        break;

    default :
		$SessionUID = $_SESSION['uid'];

		// Gets the number of tasks for the currently logged user
		$SQL = "SELECT *
    			FROM  `tasks`
    			WHERE `uid` = '$SessionUID'
		       ";
		$Result = mysql_query($SQL)
		or die("Could not execute the '$SQL' request.");
        $NumRows = mysql_num_rows($Result);
		mysql_free_result($Result);

		// If there is no tasks for the currently logged user
		if (($NumRows == 0))
		{
			// Appends (to the hypothetic one from the 'apply' section for example) a warning message for the user
			$_SESSION['message'] .= "There is no tasks for the currently logged user !";
		}

        ShowSecureHeader("Task List", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

        // Retrieve the the month and the year to list
        $UserMonth = putslashes($_POST['month']);
        $UserYear  = putslashes($_POST['year']);

        $CurrentDate  = getdate();
        $CurrentMonth = $CurrentDate['mon'];
        $CurrentYear  = $CurrentDate['year'];
        
        // If the user specified a month and a year
        if (($UserMonth != NULL) && ($UserYear != NULL))
        {
            $CurrentMonth = $UserMonth;
            $CurrentYear  = $UserYear;
        }

        $PreviousMonth = ($CurrentMonth == 1 ? 12 : $CurrentMonth - 1);
        $PreviousYear = ($PreviousMonth > $CurrentMonth ? $CurrentYear - 1: $CurrentYear);
        $NextMonth = ($CurrentMonth == 12 ? 1 : $CurrentMonth + 1);
        $NextYear = ($NextMonth < $CurrentMonth ? $CurrentYear + 1: $CurrentYear);

        echo "
        <BR/><BR/>
            <TABLE BORDER='0'>
            <TR>
                <TD ALIGN='RIGHT'>
                    <B> Month :</B>
                </TD>
                <TD>
                    <TABLE>
                        <TR>
                            <FORM ACTION='task.php' METHOD='POST' NAME='previous_task'>
                            <TD>
                                <INPUT TYPE='HIDDEN' NAME='month' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $PreviousMonth))."'>
                                <INPUT TYPE='HIDDEN' NAME='year' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%04d", $PreviousYear))."'>
                                <INPUT TYPE='SUBMIT' NAME='show' VALUE='<'>
                            </TD>
                            </FORM>
                            <FORM ACTION='task.php' METHOD='POST' NAME='task'>
                            <TD>
                                <INPUT TYPE='TEXT' NAME='month' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $CurrentMonth))."'>
                            </TD>
                            <TD>
                                /
                            </TD>
                            <TD>
                                <INPUT TYPE='TEXT' NAME='year' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%04d", $CurrentYear))."'>
                            </TD>
                            <TD>
                                <INPUT TYPE='SUBMIT' NAME='show' VALUE='Show'>
                            </TD>
                            </FORM>
                            <FORM ACTION='task.php' METHOD='POST' NAME='next_task'>
                            <TD>
                                <INPUT TYPE='HIDDEN' NAME='month' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $NextMonth))."'>
                                <INPUT TYPE='HIDDEN' NAME='year' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%04d", $NextYear))."'>
                                <INPUT TYPE='SUBMIT' NAME='show' VALUE='>'>
                            </TD>
                            </FORM>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
        </TABLE>
        <TABLE BORDER='0'>
              <TR ALIGN='CENTER'>
                  <TD>
                        <FORM ACTION='task.php?do=add' METHOD='POST'>
                            <INPUT TYPE='SUBMIT' VALUE='New Task'>
                        </FORM>
                  </TD>
              </TR>
        </TABLE>
";

		// If there is no tasks for the currently logged user
		if ($NumRows > 0)
		{
		      echo"            <TABLE BORDER='1'>
                <TR ALIGN='CENTER'>
                    <TD>
                        <B> Project </B>
                    </TD>
                    <TD>
                        <B> Activity </B>
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

			// Gets all the tasks for the currently logged user and the current project
			$TaskSQL = "SELECT *, UNIX_TIMESTAMP(beginning) as timestamp
     					FROM  `tasks`
	    				WHERE `uid` = '$SessionUID'
	    				AND YEAR(beginning) = '$CurrentYear'
	    				AND MONTH(beginning) = '$CurrentMonth'
		     			ORDER BY timestamp DESC
			    	   ";

			$TaskResult = mysql_query($TaskSQL)
			or die("Could not execute the '$TaskSQL' request.");

			// Displays all the projects
			while ($TaskRow = mysql_fetch_array($TaskResult))
			{
				$TID       = $TaskRow['tid'];
				$TaskLabel = $TaskRow['label'];
				echo "
                    <TR>
                        <TD ALIGN='LEFT'>
                            ".htmlentities(GetProjectCompleteLabel($TaskRow['pid']))."
                        </TD>
                        <TD ALIGN='LEFT'>
                            ".htmlentities(GetActivityCompleteLabel($TaskRow['aid']))."
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
                        <FORM ACTION='task.php?do=modify' METHOD='POST'>
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
                        <FORM ACTION='task.php?do=remove' METHOD='POST'>
                            <TD ALIGN='LEFT'>
                                <INPUT TYPE='HIDDEN' NAME='tid' VALUE='".$TID."'>
                                <INPUT TYPE='SUBMIT' NAME='delete' VALUE='Delete'>
                            </TD>
                        </FORM>
                    </TR>
				 ";
			}

			mysql_free_result($TaskResult);

	        echo "
	                                </TABLE>
	             ";
	    }

        ShowFooter();
}

?>