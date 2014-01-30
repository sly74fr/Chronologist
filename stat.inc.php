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
 * FILE NAME   : stat.inc.php
 * DESCRIPTION : Statistics related functions.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


function MyWeek($week,$year)
{
    // Get the first day of the year
    $FirstDay = mktime(0, 0, 1, 1, 1, $year);
    
    // Get the numeric representation of the day of the week (0 for Sunday through 6 for Saturday)
    $Day = date("w", $FirstDay);
    
   	// If the first day is a monday
   	$FirstMonday = $FirstDay;

   	// If the first day is NOT a monday
   	if ($Day != 1)
   	{
	   	// If the first day is a sunday
   		if ($Day == 0)
	   	{
   			$FirstMonday = $FirstDay + (24 * 60 * 60);
	   	}
	   	else
	   	{
	   		$FirstMonday = $FirstDay + ((8 - $Day) * (24 * 60 * 60));
	   	}
	}

    // Get the ISO-8601 week number of the first day of the year (weeks starting on Monday)
    $FirstDayWeekNumber = date("W", $FirstDay);
    
	$NthMonday = $FirstMonday + (($week - 1 - $FirstDayWeekNumber) * ( 7 * (24 * 60 * 60)));
//	$NthMonday = $FirstMonday + (($week - 1) * ( 7 * (24 * 60 * 60)));
	$NthSunday = $NthMonday + (6 * (24 * 60 * 60));

    $Period = array();
	$Period['start'] = $NthMonday;
	$Period['end']   = $NthSunday;
	return $Period;
}


function get_lundi_dimanche_from_week($week,$year)
{
    $Period = array();

	if (strftime("%W" ,mktime(0, 0, 0, 01, 01, $year)) == 1)
	{
		$mon_mktime = mktime(0, 0, 0, 01, (01 + (($week - 1) * 7)), $year);
	}
	else
	{
		$mon_mktime = mktime(0, 0, 0, 01, (01 + ($week*7)), $year);
	}

	if (date("w", $mon_mktime) > 1)
	{
		$decalage = ((date("w", $mon_mktime) - 1) * 60 * 60 * 24);
	}

//	$lundi    = $mon_mktime - $decalage;
//	$dimanche = $lundi + (6 * 60 * 60 * 24);
	$Period['start'] = $mon_mktime - $decalage;
	$Period['end']   = $Period['start'] + (6 * 60 * 60 * 24);

//	return array(date("D - d/m/Y", $lundi) ,date("D - d/m/Y", $dimanche));
//	return array($lundi, $dimanche);
	return $Period;
}


/*
function get_weeks($year, $group_by_month = false)
{
    $firstday_ts = mktime(1,0,0,1,1,$year);
    $firstday = date("w", $firstday_ts);

    if($group_by_month === false)
    {
        $weeks[] = date('Y-m-d',$firstday_ts);
    }
    else
    {
        $m = 'Jan';  //start month
        $weeks[$m][] = date('Y-m-d',$firstday_ts);
    }

    $first = $firstday_ts;
    if($firstday > 0)
    {
        $x = 8 - $firstday;
        $first = mktime(1,0,0,1,$x,$year);
        if($group_by_month === false)
        {
            $weeks[] = date('Y-m-d',$first);
        }
        else
        {
            $weeks[$m][] = date("Y-m-d", $first);
        }
    }

    $first = strtotime("+7 day",$first);
    for($i = 0; $year == date("Y", $first); $i++, $first = strtotime("+7 day",$first))
    {
        if($group_by_month === false)
        {
            $weeks[] = date("Y-m-d", $first);
        }
        else
        {
            if($m != date("M",$first))
            {
                $m = date("M",$first);
            }
            $weeks[$m][] = date("Y-m-d", $first);
        }
    }
    return $weeks;
}
*/

/*
//function get_week_boundaries($int_time)
function get_week_boundaries($Week, $Year)
{
  // Get the first day of the year.
  $int_time = mktime(0, 0, 1, 1, 1, $Year);

  // first: find monday 0:00
  $weekdayid = date("w", $int_time);

  // sunday must have w=7, not 0. otherwise sunday will count to the next week *
  if ($weekdayid == 0) $weekdayid = 7;

  $dayid   = date("j", $int_time);
  $monthid = date("n", $int_time);
  $yearid  = date("Y", $int_time);
  $beginofday  = mktime(0, 0, 0, $monthid, $dayid, $yearid);
  $beginofweek = $beginofday - (($weekdayid-1) * 86400); //86400 == seconds of one day (24 hours)
  //now add the value of one week and call it the end of the week 
  //NOTE: End of week is Sunday, 23:59:59. I think you could also use Monday 00:00:00 but I though that'd suck
  $endofweek     = ($beginofweek + 7 * 86400) - 1;
  $week["begin"] = $beginofweek;
  $week["end"] = $endofweek;
  $week["pov"] = $int_time;
  return $week;
}
*/

/*
// Return any week beginning and ending full date
function GetWeekPeriod($Week, $Year)
{
  // Get the first day of the year.
  $int_time = mktime(0, 0, 1, 1, 1, $Year);

  // first: find monday 0:00
  $weekdayid = date("w", $int_time);

  // sunday must have w=7, not 0. otherwise sunday will count to the next week *
  if ($weekdayid == 0) $weekdayid = 7;

  $dayid   = date("j", $int_time);
  $monthid = date("n", $int_time);
  $yearid  = date("Y", $int_time);
  $beginofday  = mktime(0, 0, 0, $monthid, $dayid, $yearid);
  $beginofweek = $beginofday - (($weekdayid-1) * 86400); //86400 == seconds of one day (24 hours)
  //now add the value of one week and call it the end of the week 
  //NOTE: End of week is Sunday, 23:59:59. I think you could also use Monday 00:00:00 but I though that'd suck
  $endofweek     = ($beginofweek + 7 * 86400) - 1;
  $week["begin"] = $beginofweek;
  $week["end"] = $endofweek;
  $week["pov"] = $int_time;
  return $week;
    $Period = array();

    // If you are a Catholic you should use $x = 0 considering Sunday as the first day of the week.
    // If you are an Orthodox you should use $x = 1 considering Sunday as the seventh day of the week
    $x = 1;

//    $Period['start'] = mktime(0, 0, 0, 1, 1 + ($Week*7), $Year);
//    $Period['end']   = mktime(0, 0, 0, 1, (($Week+1)*7), $Year);
    $Period['start'] = mktime(0, 0, 0, 1, 1 + $Week*7 + $x - 6, $Year);
    $Period['end']   = mktime(0, 0, 0, 1, 1 + $Week*7 + $x    , $Year);

    return $Period;
}
*/

// Return any week beginning and ending full date
// Thanx to "leonardo at kreativ dot ro" from "http://fr.php.net/date"
function GetWeekPeriod($Week, $Year)
{
    // TODO: correct the bug that make it workng ONLY in 2005 !!!

    $Period = array();

    // If you are a Catholic you should use $x = 0 considering Sunday as the first day of the week.
    // If you are an Orthodox you should use $x = 1 considering Sunday as the seventh day of the week
    $x = 1;

//    $Period['start'] = mktime(0, 0, 0, 1, 1 + ($Week*7), $Year);
//    $Period['end']   = mktime(0, 0, 0, 1, (($Week+1)*7), $Year);
    $Period['start'] = mktime(0, 0, 0, 1, 1 + $Week*7 + $x - 6, $Year);
    $Period['end']   = mktime(0, 0, 0, 1, 1 + $Week*7 + $x    , $Year);

    return $Period;
}

// Return any month beginning and ending full date
function GetMonthPeriod($Month, $Year)
{
    $Period = array();

    $Period['start'] = mktime( 0,  0,  1, $Month, 1                          , $Year);
    $Period['end']   = mktime(23, 59, 59, $Month, date("t", $Period['start']), $Year);

    return $Period;
}


function GetProjectTime($UID, $PID, $AfterUnixTimeStamp = 0, $BeforeUnixTimeStamp = 0)
{
	$TotalTime = 0;

    // Gets the user projects duration from the database
    $SQL = "SELECT SUM(duration) as length
            FROM `tasks`
            WHERE pid = '$PID'
           ";

    // If the user is not administrator
    if ($UID != 0)
    {
        $SQL .= "AND uid = '$UID'
           ";
    }

    // Select all the tasks after this timestamp
    if ($AfterUnixTimeStamp != 0)
    {
        $SQL .= "AND   beginning > FROM_UNIXTIME('$AfterUnixTimeStamp')
                ";
    }

    // Select all the tasks before this timestamp
    if ($BeforeUnixTimeStamp != 0)
    {
        $SQL .= "AND   beginning < FROM_UNIXTIME('$BeforeUnixTimeStamp')
                ";
    }

    $Result = mysql_query($SQL)
    or die("Could not execute the '$SQL' request.");
    $Row       = mysql_fetch_array($Result);
    $TotalTime = $Row['length'];

	mysql_free_result($Result);

	return $TotalTime;
}


function GetSubProjectsCumulativeTime($UID, $PPID, $AfterUnixTimeStamp = 0, $BeforeUnixTimeStamp = 0)
{
	$TotalTime = 0;

	// Gets all the subprojects of the given project from the database
	$SQL = "SELECT   pid
			FROM     projects
			WHERE    ppid = '$PPID'
		   ";
	$Result = mysql_query($SQL)
	or die("Could not execute the '$SQL' request.");

	// Puts all the sub-projects of the current sub-project in the 'to-be-returned' array
	while ($Row = mysql_fetch_array($Result))
	{
		// Get the current project ID
		$PID = $Row['pid'];

        // Gets the user projects duration from the database
        $SQL = "SELECT SUM(duration) as length
                FROM `tasks`
                WHERE pid = '$PID'
               ";

        // If the user is not administrator
        if ($UID != 0)
        {
            $SQL .= "AND uid = '$UID'
               ";
        }
    
        // Select all the tasks after this timestamp
        if ($AfterUnixTimeStamp != 0)
        {
            $SQL .= "AND   beginning > FROM_UNIXTIME('$AfterUnixTimeStamp')
                    ";
        }
    
        // Select all the tasks before this timestamp
        if ($BeforeUnixTimeStamp != 0)
        {
            $SQL .= "AND   beginning < FROM_UNIXTIME('$BeforeUnixTimeStamp')
                    ";
        }
    
        $TimeResult = mysql_query($SQL)
        or die("Could not execute the '$SQL' request.");
        $TimeRow    = mysql_fetch_array($TimeResult);
		$TotalTime += $TimeRow['length'];

		// Recursively do the same thing for all the sub-projects of the current project
		$TotalTime += GetSubProjectsCumulativeTime($UID, $PID, $AfterUnixTimeStamp, $BeforeUnixTimeStamp);
	}

	mysql_free_result($Result);

	return $TotalTime;
}

function GetActivityTime($UID, $AID, $AfterUnixTimeStamp = 0, $BeforeUnixTimeStamp = 0)
{
	$TotalTime = 0;

    // Gets the user activity duration from the database
    $SQL = "SELECT SUM(duration) as length
            FROM `tasks`
            WHERE aid = '$AID'
           ";

    // If the user is not administrator
    if ($UID != 0)
    {
        $SQL .= "AND uid = '$UID'
           ";
    }

    // Select all the tasks after this timestamp
    if ($AfterUnixTimeStamp != 0)
    {
        $SQL .= "AND   beginning > FROM_UNIXTIME('$AfterUnixTimeStamp')
                ";
    }

    // Select all the tasks before this timestamp
    if ($BeforeUnixTimeStamp != 0)
    {
        $SQL .= "AND   beginning < FROM_UNIXTIME('$BeforeUnixTimeStamp')
                ";
    }

    $Result = mysql_query($SQL)
    or die("Could not execute the '$SQL' request.");
    $Row       = mysql_fetch_array($Result);
    $TotalTime = $Row['length'];

	mysql_free_result($Result);

	return $TotalTime;
}


function GetSubActivitiesCumulativeTime($UID, $PAID, $AfterUnixTimeStamp = 0, $BeforeUnixTimeStamp = 0)
{
	$TotalTime = 0;

	// Gets all the sub-activites of the given activity from the database
	$SQL = "SELECT   aid
			FROM     activities
			WHERE    paid = '$PAID'
		   ";
	$Result = mysql_query($SQL)
	or die("Could not execute the '$SQL' request.");

	// Puts all the sub-activities of the current sub-activity in the 'to-be-returned' array
	while ($Row = mysql_fetch_array($Result))
	{
		// Get the current actitivty ID
		$AID = $Row['aid'];

        // Gets the user activitiess duration from the database
        $SQL = "SELECT SUM(duration) as length
                FROM `tasks`
                WHERE aid = '$AID'
               ";

        // If the user is not administrator
        if ($UID != 0)
        {
            $SQL .= "AND uid = '$UID'
               ";
        }
    
        // Select all the tasks after this timestamp
        if ($AfterUnixTimeStamp != 0)
        {
            $SQL .= "AND   beginning > FROM_UNIXTIME('$AfterUnixTimeStamp')
                    ";
        }
    
        // Select all the tasks before this timestamp
        if ($BeforeUnixTimeStamp != 0)
        {
            $SQL .= "AND   beginning < FROM_UNIXTIME('$BeforeUnixTimeStamp')
                    ";
        }

        $TimeResult = mysql_query($SQL)
        or die("Could not execute the '$SQL' request.");
        $TimeRow    = mysql_fetch_array($TimeResult);
		$TotalTime += $TimeRow['length'];

		// Recursively do the same thing for all the sub-activities of the current activity
		$TotalTime += GetSubActivitiesCumulativeTime($UID, $AID, $AfterUnixTimeStamp, $BeforeUnixTimeStamp);
	}

	mysql_free_result($Result);

	return $TotalTime;
}

?>