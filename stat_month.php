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
 * FILE NAME   : stat_month.php
 * DESCRIPTION : Monthly statistics form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("task.inc.php");
require_once("project.inc.php");
require_once("stat.inc.php");

// Retrieve the month and the year to stat
$UserMonth = putslashes($_POST['month']);
$UserYear  = putslashes($_POST['year']);


// Get the current week and year
$CurrentMonth = date("m");
$CurrentYear  = date("Y");

// If the user specified a month and a year
if (($UserMonth != NULL) && ($UserYear != NULL))
{
    $CurrentMonth = $UserMonth;
    $CurrentYear  = $UserYear;
}

// Test parameters validity
if ($CurrentYear == 0)
{
    $_SESSION['message'] = "The year parameter is missing. <BR> Please try again. <BR>";
    header("Location: stat.php");
    exit();
}
else if (($CurrentMonth < 1) || ($CurrentMonth > 12))
{
    $_SESSION['message'] = "The month parameter is missing or seems wrong. <BR> Please try again. <BR>";
    header("Location: stat.php");
    exit();
}

$PreviousMonth = ($CurrentMonth == 1 ? 12 : $CurrentMonth - 1);
$PreviousYear = ($PreviousMonth > $CurrentMonth ? $CurrentYear - 1: $CurrentYear);
$NextMonth = ($CurrentMonth == 12 ? 1 : $CurrentMonth + 1);
$NextYear = ($NextMonth < $CurrentMonth ? $CurrentYear + 1: $CurrentYear);

$Period   = GetMonthPeriod($CurrentMonth, $CurrentYear);
$FirstDay = $Period['start'];
$LastDay  = $Period['end'];


ShowSecureHeader("Monthly Statistics", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

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
                                    <FORM ACTION='stat_month.php' METHOD='POST' NAME='previous_stat_month'>
                                    <TD>
                                        <INPUT TYPE='HIDDEN' NAME='month' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $PreviousMonth))."'>
                                        <INPUT TYPE='HIDDEN' NAME='year' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%04d", $PreviousYear))."'>
                                        <INPUT TYPE='SUBMIT' NAME='stat' VALUE='<'>
                                    </TD>
                                    </FORM>
                                    <FORM ACTION='stat_month.php' METHOD='POST' NAME='stat_month'>
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
                                        <INPUT TYPE='SUBMIT' NAME='stat' VALUE='Stat'>
                                    </TD>
                                    </FORM>
                                    <FORM ACTION='stat_month.php' METHOD='POST' NAME='next_stat_month'>
                                    <TD>
                                        <INPUT TYPE='HIDDEN' NAME='month' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $NextMonth))."'>
                                        <INPUT TYPE='HIDDEN' NAME='year' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%04d", $NextYear))."'>
                                        <INPUT TYPE='SUBMIT' NAME='stat' VALUE='>'>
                                    </TD>
                                    </FORM>
                                </TR>
                            </TABLE>
                        </TD>
                    </TR>
                </TABLE>
";

$TotalTime = GetSubProjectsCumulativeTime($_SESSION['uid'], 0, $FirstDay, $LastDay);

echo "The given month starts on ".GetFormattedDate($FirstDay)." and ends on ".GetFormattedDate($LastDay)."
<BR/> <BR/> <B> Total : </B> ".htmlentities(DurationLabel($TotalTime))." <BR/> <BR/>";


if ($TotalTime != 0)
{
    echo "
                <TABLE BORDER='1'>
                    <TR ALIGN='CENTER'>
                        <TD>
                            <B> Project </B>
                        </TD>
                        <TD>
                            <B> Duration </B>
                        </TD>
                        <TD>
                            <B> Cumulative </B>
                        </TD>
                        <TD>
                            <B> Percentage </B>
                        </TD>
                    </TR>
    ";
    
    // Gets all the projects from the database
    // TODO : handle manager type user to return all their managed projects
    $Array = GetSubProjects();
    foreach($Array as $PID => $ProjectLabel)
    {
        if (ConnectedUserBelongsToAdminGroup() == TRUE) // If an administrator is logged
        {
            // Gets all the projects duration from the database
            $SQL = "SELECT  SUM(duration) as length
                    FROM   `tasks`
                    AND     pid = '$PID'
                   ";
        }
        else if (ConnectedUserBelongsToManagerGroup() == TRUE) // If a manager is logged
        {
            // TODO : Only show the managed project duration
            // Gets all the projects duration from the database
    /*
            $SQL = "SELECT pid, label, SUM(duration) as length
                    FROM `tasks`
                    GROUP BY `pid`
                    ORDER BY label
                   ";
    */
        }
        else  // If a user is logged
        {
            // Gets the user projects duration from the database
            $SQL = "SELECT  SUM(duration) as length
                    FROM   `tasks`
                    WHERE   uid = '$_SESSION[uid]'
                    AND     pid = '$PID'
                   ";
    
            if ($CurrentYear != 0)
            {
                $SQL .= "AND   YEAR(beginning) = '$CurrentYear'
                        ";
    
                if ((0 < $CurrentMonth) && ($CurrentMonth < 13))
                {
                    $SQL .= "AND   MONTH(beginning) = '$CurrentMonth'
                            ";
                }
                else
                {
                    $_SESSION['message'] = "The month parameter is missing or seems wrong. <BR> Please try again. <BR>";
                    header("Location: stat_month.php");
                    break;
                }
            }
            else
            {
                $_SESSION['message'] = "The year parameter is missing. <BR> Please try again. <BR>";
                header("Location: stat_month.php");
                break;
            }
        }
    
        $Result = mysqli_query($Connection, $SQL)
        or die("Could not execute the '$SQL' request.");
    
        // For each projects
        while ($Row = mysqli_fetch_array($Result))
        {
            // Displays its label and total duration
            $Duration   = $Row['length'];
            $Cumulative = $Duration + GetSubProjectsCumulativeTime($_SESSION['uid'], $PID, $FirstDay, $LastDay);

            if ($Cumulative != 0)
            {
                $Percentage = round((($Cumulative / $TotalTime) * 100), 2);

                echo "
                        <TR>
                            <TD ALIGN='LEFT'>
                                ".htmlentities($ProjectLabel)."
                            </TD>
                            <TD>
                                ".htmlentities(DurationLabel($Duration))."
                            </TD>
                            <TD>
                                ".htmlentities(DurationLabel($Cumulative))."
                            </TD>
                            <TD>
                                ".htmlentities($Percentage)."%
                            </TD>
                        </TR>
                ";
            }
        }

        mysqli_free_result($Result);
    }

    echo "
                </TABLE>
         ";
}

ShowFooter();

?>