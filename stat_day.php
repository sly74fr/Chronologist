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
 * FILE NAME   : stat_day.php
 * DESCRIPTION : Day to day statistics form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("task.inc.php");
require_once("project.inc.php");
require_once("stat.inc.php");


// Retrieve the the week, the year and the week part to stat
$UserFirstDay   = putslashes($_POST['first_day']);
$UserFirstMonth = putslashes($_POST['first_month']);
$UserFirstYear  = putslashes($_POST['first_year']);
$UserLastDay    = putslashes($_POST['last_day']);
$UserLastMonth  = putslashes($_POST['last_month']);
$UserLastYear   = putslashes($_POST['last_year']);

// Get the current day, month and year
$CurrentDay   = date("j");
$CurrentMonth = date("n");
$CurrentYear  = date("Y");

// If the user did not specified any input values
if (($UserFirstDay == NULL) || ($UserFirstMonth == NULL) || ($UserFirstYear == NULL) || ($UserLastDay == NULL) || ($UserLastMonth == NULL) || ($UserLastYear == NULL))
{
    // Use the current date instead
    $UserFirstDay   = $CurrentDay;
    $UserFirstMonth = $CurrentMonth;
    $UserFirstYear  = $CurrentYear;
    $UserLastDay    = $CurrentDay;
    $UserLastMonth  = $CurrentMonth;
    $UserLastYear   = $CurrentYear;
}

// TODO : Test parameters validity (first < last, ...)

// Get the given first and last days
$FirstDay = mktime( 0,  0,  1, $UserFirstMonth, $UserFirstDay, $UserFirstYear);
$LastDay  = mktime(23, 59, 59, $UserLastMonth,  $UserLastDay,  $UserLastYear);

ShowSecureHeader("Day to day Statistics", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

echo "
            <FORM ACTION='stat_day.php' METHOD='POST' NAME='stat_day'>
            <BR/><BR/>
                    <TABLE BORDER='0'>
                    <TR>
                        <TD ALIGN='RIGHT'>
                            <B> First Day :</B>
                        </TD>
                        <TD>
                            <TABLE>
                                <TR>
                                    <TD>
                                        <INPUT TYPE='TEXT' NAME='first_day' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $UserFirstDay))."'>
                                    </TD>
                                    <TD>
                                        /
                                    </TD>
                                    <TD>
                                        <INPUT TYPE='TEXT' NAME='first_month' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $UserFirstMonth))."'>
                                    </TD>
                                    <TD>
                                        /
                                    </TD>
                                    <TD>
                                        <INPUT TYPE='TEXT' NAME='first_year' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%04d", $UserFirstYear))."'>
                                    </TD>
                                </TR>
                            </TABLE>
                        </TD>
                    </TR>
                    <TR>
                        <TD ALIGN='RIGHT'>
                            <B> Last Day :</B>
                        </TD>
                        <TD>
                            <TABLE>
                                <TR>
                                    <TD>
                                        <INPUT TYPE='TEXT' NAME='last_day' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $UserLastDay))."'>
                                    </TD>
                                    <TD>
                                        /
                                    </TD>
                                    <TD>
                                        <INPUT TYPE='TEXT' NAME='last_month' SIZE='2' MAXLENGTH ='2' VALUE='".htmlentities(sprintf("%02d", $UserLastMonth))."'>
                                    </TD>
                                    <TD>
                                        /
                                    </TD>
                                    <TD>
                                        <INPUT TYPE='TEXT' NAME='last_year' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%04d", $UserLastYear))."'>
                                    </TD>
                                </TR>
                            </TABLE>
                        </TD>
                    </TR>
                    <TR>
                        <TD ALIGN='CENTER' COLSPAN='2'>
                            <INPUT TYPE='SUBMIT' NAME='stat' VALUE='Stat'>
                        </TD>
                    </TR>
                </TABLE>
            </FORM>
";

$TotalTime = GetSubProjectsCumulativeTime($_SESSION['uid'], 0, $FirstDay, $LastDay);

echo "The given period starts on ".GetFormattedDate($FirstDay)." and ends on ".GetFormattedDate($LastDay)."
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

//            $SQL = "SELECT pid, label, SUM(duration) as length
//                    FROM `tasks`
//                    GROUP BY `pid`
//                    ORDER BY label
//                   ";

        }
        else  // If a user is logged
        {
            // Gets the user projects duration from the database
            $SQL = "SELECT  SUM(duration) as length
                    FROM   `tasks`
                    WHERE   uid = '$_SESSION[uid]'
                    AND     pid = '$PID'
                    AND     beginning > FROM_UNIXTIME('$FirstDay')
                    AND     beginning < FROM_UNIXTIME('$LastDay')
                   ";
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