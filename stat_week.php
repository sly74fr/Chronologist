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
 * FILE NAME   : stat_week.php
 * DESCRIPTION : Weekly statistics form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("task.inc.php");
require_once("project.inc.php");
require_once("stat.inc.php");


// Retrieve the the week, the year and the week part to stat
$UserWeek     = putslashes($_POST['week']);
$UserYear     = putslashes($_POST['year']);
$UserWeekPart = putslashes($_POST['week_part']);

// Get the current week and year
$CurrentWeek     = date("W");
$CurrentYear     = date("Y");
$CurrentWeekPart = "whole";

// If the user specified a week and a year
if (($UserWeek != NULL) && ($UserYear != NULL))
{
    // Use the given parameters instead of the default ones
    $CurrentWeek     = $UserWeek;
    $CurrentYear     = $UserYear;
    $CurrentWeekPart = $UserWeekPart;
}

// Test parameters validity
if ($CurrentYear == 0)
{
    $_SESSION['message'] = "The year parameter is missing. <BR> Please try again. <BR>";
    header("Location: stat.php");
    break;
}
else if (($CurrentWeek < 1) || ($CurrentWeek > 53))
{
    $_SESSION['message'] = "The week parameter is missing or seems wrong. <BR> Please try again. <BR>";
    header("Location: stat.php");
    break;
}
// TODO : check CurrentWeekPart in {beginning, whole, end}

// Get the given week start and end dates
//$WeekPeriod = GetWeekPeriod($CurrentWeek, $CurrentYear);
//$WeekPeriod = get_lundi_dimanche_from_week($CurrentWeek, $CurrentYear);
$WeekPeriod = MyWeek($CurrentWeek, $CurrentYear);

$FirstDay   = $WeekPeriod['start'];
$LastDay    = $WeekPeriod['end'];

// If the given week runs over 2 different monthes
$BeginningMonth = date("m", $WeekPeriod['start']);
$EndMonth       = date("m", $WeekPeriod['end']);
if ($BeginningMonth != $EndMonth)
{
    // Get the week's beginning month period to retrieve the month's last day date
    $BeginningMonthPeriod   = GetMonthPeriod($BeginningMonth, $CurrentYear);
    $BeginningMonthLastDay  = $BeginningMonthPeriod['end'];

    // Get the week's end month period to retrieve the month's first day date
    $EndMonthPeriod         = GetMonthPeriod($EndMonth, $CurrentYear);
    $EndMonthFirstDay       = $EndMonthPeriod['start'];

    // According to user choice
    switch ($CurrentWeekPart)
    {
        // week first part, running from given week first day to beginning month last day
        case "beginning":
            $LastDay = $BeginningMonthLastDay;
            break;

        // whole week, running from  given week first day to the given week last day
        case "whole":
            break;

        // week last part, running from the end month first day to the given week last day
        case "end":
            $FirstDay = $EndMonthFirstDay;
            break;

        default:
            $_SESSION['message'] = "Assertion failed: wrong week part selected. <BR> Please try again. <BR>";
            header("Location: stat.php");
            break;
    }
}


ShowSecureHeader("Weekly Statistics", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

echo "
            <FORM ACTION='stat_week.php' METHOD='POST' NAME='stat_week'>
            <BR/><BR/>
                    <TABLE BORDER='0'>
                    <TR>
                        <TD ALIGN='RIGHT'>
                            <B> Week :</B>
                        </TD>
                        <TD>
                            <TABLE>
                                <TR>
                                    <TD>
                                        <INPUT TYPE='TEXT' NAME='week' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $CurrentWeek))."'>
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
                                </TR>
                            </TABLE>
                        </TD>
                    </TR>
                    <TR>
                        <TD ALIGN='RIGHT'>
                            <B> Week beginning :</B>
                        </TD>
                        <TD>
                            <INPUT TYPE='RADIO' VALUE='beginning' NAME='week_part'";

// If the given week part is the 'beginnig' one
if ($CurrentWeekPart == "beginning")
{
    // Chect it
    echo " CHECKED";
}

echo ">
                        </TD>
                    </TR>
                    <TR>
                        <TD ALIGN='RIGHT'>
                            <B> Whole week :</B>
                        </TD>
                        <TD>
                            <INPUT TYPE='RADIO' VALUE='whole' NAME='week_part'";

// If the given week part is the 'whole' one
if ($CurrentWeekPart == "whole")
{
    // Chect it
    echo " CHECKED";
}

echo ">
                        </TD>
                    </TR>
                    <TR>
                        <TD ALIGN='RIGHT'>
                            <B> Week end :</B>
                        </TD>
                        <TD>
                            <INPUT TYPE='RADIO' VALUE='end' NAME='week_part'";

// If the given week part is the 'end' one
if ($CurrentWeekPart == "end")
{
    // Chect it
    echo " CHECKED";
}

echo ">
                        </TD>
                    </TR>
                </TABLE>
            </FORM>
";


$TotalTime = GetSubProjectsCumulativeTime($_SESSION['uid'], 0, $FirstDay, $LastDay);

echo "The given week starts on ".GetFormattedDate($FirstDay)." and ends on ".GetFormattedDate($LastDay)."
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
    
                if ((0 < $CurrentWeek) && ($CurrentWeek < 54))
                {
                    $SQL .= "AND   WEEK(beginning) = '$CurrentWeek'
                            ";
                }
                else
                {
                    $_SESSION['message'] = "The week parameter is missing or seems wrong. <BR> Please try again. <BR>";
                    header("Location: stat.php");
                    break;
                }
            }
            else
            {
                $_SESSION['message'] = "The year parameter is missing. <BR> Please try again. <BR>";
                header("Location: stat.php");
                break;
            }
        }
    
        $Result = mysql_query($SQL)
        or die("Could not execute the '$SQL' request.");
    
        // For each projects
        while ($Row = mysql_fetch_array($Result))
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

        mysql_free_result($Result);
    }

    echo "
                </TABLE>
         ";
}

ShowFooter();

?>