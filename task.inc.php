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
 * FILE NAME   : task.inc.php
 * DESCRIPTION : Task related functions.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */

require_once("design.inc.php");

function DurationHour($DurationSeconds)
{
	// Return the number of full hours in the given amount of seconds
	return floor($DurationSeconds / 3600);
}

function DurationMinute($DurationSeconds)
{
	// Return the number of minutes left (minus all the full hours) in the given amount of seconds
	return floor(($DurationSeconds % 3600) / 60);
}

function DurationLabel($DurationSeconds)
{
	// Return a string containing the number of hours and minutes in the given amount of seconds
	return sprintf("%dh%02d", DurationHour($DurationSeconds), DurationMinute($DurationSeconds));
}

function DurationFromHoursAndMinutes($Hours, $Minutes)
{
	// If the given $Hours value is negative
	if ($Hours < 0)
	{
		$_SESSION['message'] = "Hours duration must be positive. <BR> Please try again. <BR>";
		return "";
	}

	// If the given $Minutes value is negative
	if ($Minutes < 0)
	{
		$_SESSION['message'] = "Minutes duration must be positive. <BR> Please try again. <BR>";
		return "";
	}

	// Return the number of seconds in the guven hours and minutes
	return (60 * ($Minutes + (60 * $Hours)));
}

// Displays the common task form
function ShowTaskFields($TID = 0, $TimeStamp = 0)
{
    // If no user was specified, show an empty form
    if ($TID == 0)
    {
        $TaskProject   = "";

        $TaskActivity  = 0;

        $TaskLabel     = "";

        $TaskBeginning = $TimeStamp;
        if ($TaskBeginning == 0)
        {
            $TaskBeginning = getdate();
        }
        else
        {
            $TaskBeginning = getdate($TimeStamp);
        }

        $TaskDurationHour    = 0;
        $TaskDurationMinute  = 0;

        echo "
                        <FORM ACTION='task.php?do=insert' METHOD='POST' NAME='task'>\n";
    }
    else // Show a form filled with the given user data
    {
        // Gets the data with the given ID
        $SQL = "SELECT  DISTINCT *, UNIX_TIMESTAMP(beginning) as timestamp
                FROM   `tasks`
                WHERE  `tid` = '$TID'
               ";
        global $Connection;
        $Result = mysqli_query($Connection, $SQL)
        or die("Could not execute the '$SQL' request.");
        $Row = mysqli_fetch_array($Result);
        mysqli_free_result($Result);

        $TaskProject         = $Row['pid'];
        $TaskLabel           = $Row['label'];

        $TaskBeginning       = getdate($Row['timestamp']);

        $TaskDurationHour    = DurationHour($Row['duration']);
        $TaskDurationMinute  = DurationMinute($Row['duration']);

        $TaskActivity        = $Row['aid'];

        echo "
                        <FORM ACTION='task.php?do=update' METHOD='POST' NAME='task'>\n";
    }

    $TaskBeginningYear   = $TaskBeginning['year'];
    $TaskBeginningMonth  = $TaskBeginning['mon'];
    $TaskBeginningDay    = $TaskBeginning['mday'];
    $TaskBeginningHour   = $TaskBeginning['hours'];
    $TaskBeginningMinute = $TaskBeginning['minutes'];

    echo "
                            <TABLE BORDER='0'>
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Project :</B>
                                    </TD>
                                    <TD>
                                        <SELECT NAME='pid'>
             ";

        // Gets all the active projects from the database
        $Array = GetSubProjects(true);
        foreach($Array as $PID => $ProjectLabel)
        {
            echo "                                            	<OPTION VALUE='".$PID."'";
            if ($PID == $TaskProject)
            {
                echo " SELECTED";
            }
            echo "> ".htmlentities($ProjectLabel)." </OPTION>\n";
        }

        echo "
                                        </SELECT>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Activity :</B>
                                    </TD>
                                    <TD>
                                        <SELECT NAME='aid'>
             ";

        // Gets all the activities from the database
        $Array = GetSubActivities();
        foreach($Array as $AID => $ActivityLabel)
        {
            echo "                                            	<OPTION VALUE='".$AID."'";
            if ($AID == $TaskActivity)
            {
                echo " SELECTED";
            }
            echo "> ".htmlentities($ActivityLabel)." </OPTION>\n";
        }

        echo "
                                        </SELECT>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD ALIGN='RIGHT' VALIGN='TOP'>
                                        <I> Label :</I>
                                    </TD>
                                    <TD>
                                        <TEXTAREA COLS='40' ROWS='5' NAME='label'>".htmlentities($TaskLabel)."</TEXTAREA>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Duration :</B>
                                    </TD>
                                    <TD>
                                        <TABLE>
                                            <TR>
                                                <TD>
                                                    <INPUT TYPE='TEXT' NAME='durationHour' SIZE='2' VALUE='".htmlentities(sprintf("%02d", $TaskDurationHour))."'>
                                                </TD>
                                                <TD>
                                                    :
                                                </TD>
                                                <TD>
                                                    <INPUT TYPE='TEXT' NAME='durationMinute' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $TaskDurationMinute))."'>
                                                </TD>
                                            </TR>
                                        </TABLE>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Beginning Date :</B>
                                    </TD>
                                    <TD>
                                        <TABLE>
                                            <TR>
                                                <TD>
                                                    <INPUT TYPE='TEXT' NAME='beginningYear' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%04d", $TaskBeginningYear))."'>
                                                </TD>
                                                <TD>
                                                    /
                                                </TD>
                                                <TD>
                                                    <INPUT TYPE='TEXT' NAME='beginningMonth' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $TaskBeginningMonth))."'>
                                                </TD>
                                                <TD>
                                                    /
                                                </TD>
                                                <TD>
                                                    <INPUT TYPE='TEXT' NAME='beginningDay' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $TaskBeginningDay))."'>
                                                </TD>
                                                <TD>
                                                    -
                                                </TD>
                                                <TD>
                                                    <INPUT TYPE='TEXT' NAME='beginningHour' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $TaskBeginningHour))."'>
                                                </TD>
                                                <TD>
                                                    :
                                                </TD>
                                                <TD>
                                                    <INPUT TYPE='TEXT' NAME='beginningMinute' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $TaskBeginningMinute))."'>
                                                </TD>
                                            </TR>
                                        </TABLE>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD ALIGN='CENTER' COLSPAN='2'>
                                        <INPUT TYPE='HIDDEN' NAME='tid' VALUE='".$TID."'>
                                        <BR> <INPUT TYPE='SUBMIT' NAME='ok' VALUE='OK'>
                                    </TD>
                                </TR>
                            </TABLE>
                        </FORM>
         ";
}
?>