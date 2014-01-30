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
 * FILE NAME   : stat_activity.php
 * DESCRIPTION : Activities statistics form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("task.inc.php");
require_once("activity.inc.php");
require_once("stat.inc.php");


// Retrieve the action to perform from the URL given 'do' parameter
$Do = $_GET['do'];


$RootActivity  = putslashes($_POST['aid']);
// If the root '- None -' activity is selected (value = -1)
if ($RootActivity == -1)
{
    // Use 0 as the true root AID
    $RootActivity = 0;
}

switch($Do)
{
    case "excel" :
        header("Content-Type: application/vnd.ms-excel");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("content-disposition: attachment;filename=Stats.xls");
    
        echo "Activity";
        echo "\t";
        echo "Duration";
        echo "\t";
        echo "Cumulative";
        echo "\t";
        echo "Percentage";
        echo "\t";
        echo "Cumulative";
        echo "\n";

        $Label = "";
        if ($RootActivity != 0)
        {
            $Label = GetActivityCompleteLabel($RootActivity);
        }
        $TotalTime  = GetSubActivitiesCumulativeTime($_SESSION['uid'], $RootActivity);
        $Duration   = GetActivityTime($_SESSION['uid'], $RootActivity);
        $Cumulative = $Duration + GetSubActivitiesCumulativeTime($_SESSION['uid'], $RootActivity);
        $Percentage = 100;
        if ($TotalTime != 0)
        {
            $Percentage = round((($Duration / $TotalTime) * 100), 2);
        }
        echo $Label;
        echo "\t";
        echo DurationLabel($Duration);
        echo "\t";
        echo DurationLabel($Cumulative);
        echo "\t";
        echo $Percentage;
        echo "\t";
        echo "100%";
        echo "\n";

        // Gets all the activities from the database
        // TODO : handle manager type user to return all their managed activities
        $Array = GetSubActivities($RootActivity, $Label, false);
        foreach($Array as $AID => $ActivityLabel)
        {
            $Duration   = GetActivityTime($_SESSION['uid'], $AID);
            $Cumulative = $Duration + GetSubActivitiesCumulativeTime($_SESSION['uid'], $AID);
            $Percentage = round((($Duration / $TotalTime) * 100), 2);
            $CumulativePercentage = round((($Cumulative / $TotalTime) * 100), 2);

            echo $ActivityLabel;
            echo "\t";
            echo DurationLabel($Duration);
            echo "\t";
            echo DurationLabel($Cumulative);
            echo "\t";
            echo $Percentage;
            echo "\t";
            echo $CumulativePercentage;
            echo "\n";
        }
        break;

    default :
        ShowSecureHeader("Activites Statistics", "http://"+$_SERVER['HTTP_HOST']+$_SERVER['REQUEST_URI']);

        echo "
                            <TABLE BORDER='0'>
                                <FORM ACTION='stat_activity.php' METHOD='POST' NAME='stat'>
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Activity :</B>
                                    </TD>
                                    <TD>
                                        <SELECT NAME='aid'>\n";

        // Gets all the active activities from the database
        $Array = GetSubActivities();
        foreach($Array as $AID => $ActivityLabel)
        {
            echo "                                            	<OPTION VALUE='".$AID."'";
            // Keep the chosen activity selected
            if ($AID == $RootActivity)
            {
                echo " SELECTED";
            }
            echo "> ".htmlentities($ActivityLabel)." </OPTION>\n";
        }

        echo "
                                        </SELECT>
                                    </TD>
                                    <TD ALIGN='LEFT'>
                                        <INPUT TYPE='SUBMIT' NAME='stat' VALUE='OK'>
                                    </TD>
                                </FORM>
                                <FORM ACTION='stat_activity.php?do=excel' METHOD='POST' NAME='excel'>
                                </TR>
                                    <TD COLSPAN='3' ALIGN='CENTER'>
                                        <INPUT TYPE='HIDDEN' NAME='aid' VALUE='".htmlentities($RootActivity)."'>
                                        <INPUT TYPE='SUBMIT' NAME='excel' VALUE='Download Excel file'>
                                    </TD>
                                </TR>
                                </FORM>
                            </TABLE>
";

        $Label = "";
        if ($RootActivity != 0)
        {
            $Label = GetActivityCompleteLabel($RootActivity);
        }
        $TotalTime  = GetSubActivitiesCumulativeTime($_SESSION['uid'], $RootActivity);
        $Duration   = GetActivityTime($_SESSION['uid'], $RootActivity);
        $Cumulative = $Duration + GetSubActivitiesCumulativeTime($_SESSION['uid'], $RootActivity);
        $Percentage = 100;
        if ($TotalTime != 0)
        {
            $Percentage = round((($Duration / $TotalTime) * 100), 2);
        }
        echo "
                <TABLE BORDER='1'>
                    <TR ALIGN='CENTER'>
                        <TD>
                            <B> Activity </B>
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
                        <TD>
                            <B> Cumulative </B>
                        </TD>
                    </TR>
                    <TR ALIGN='LEFT'>
                        <TD>
                            ".htmlentities($Label)."
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
                        <TD>
                            100%
                        </TD>
                    </TR>
             ";

        // Gets all the activities from the database
        // TODO : handle manager type user to return all their managed activities
        $Array = GetSubActivities($RootActivity, $Label, false);
        foreach($Array as $AID => $ActivityLabel)
        {
            $Duration   = GetActivityTime($_SESSION['uid'], $AID);
            $Cumulative = $Duration + GetSubActivitiesCumulativeTime($_SESSION['uid'], $AID);
            $Percentage = round((($Duration / $TotalTime) * 100), 2);
            $CumulativePercentage = round((($Cumulative / $TotalTime) * 100), 2);

            echo "
                        <TR>
                            <FORM ACTION='activity.php?do=modify' METHOD='POST'>
                                <TD ALIGN='LEFT'>
                                    ".htmlentities($ActivityLabel)."
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
                                <TD>
                                    ".htmlentities($CumulativePercentage)."%
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