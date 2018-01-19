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
 * FILE NAME   : stat.php
 * DESCRIPTION : Statistics form and script.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


require_once("design.inc.php");
require_once("task.inc.php");
require_once("project.inc.php");
require_once("stat.inc.php");


// Retrieve the action to perform from the URL given 'do' parameter
$Do = $_GET['do'];


$RootProject  = putslashes($_POST['pid']);
// If the root '- None -' project is selected (value = -1)
if ($RootProject == -1)
{
    // Use 0 as the true root PID
    $RootProject = 0;
}

switch($Do)
{
    case "excel" :
        header("Content-Type: application/vnd.ms-excel");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("content-disposition: attachment;filename=Stats.xls");
    
        echo "Project";
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
        if ($RootProject != 0)
        {
            $Label = GetProjectCompleteLabel($RootProject);
        }
        $TotalTime  = GetSubProjectsCumulativeTime($_SESSION['uid'], $RootProject);
        $Duration   = GetProjectTime($_SESSION['uid'], $RootProject);
        $Cumulative = $Duration + GetSubProjectsCumulativeTime($_SESSION['uid'], $RootProject);
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

        // Gets all the projects from the database
        // TODO : handle manager type user to return all their managed projects
        $Array = GetSubProjects(false, $RootProject, $Label);
        foreach($Array as $PID => $ProjectLabel)
        {
            $Duration   = GetProjectTime($_SESSION['uid'], $PID);
            $Cumulative = $Duration + GetSubProjectsCumulativeTime($_SESSION['uid'], $PID);
            $Percentage = round((($Duration / $TotalTime) * 100), 2);
            $CumulativePercentage = round((($Cumulative / $TotalTime) * 100), 2);

            echo $ProjectLabel;
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
        ShowSecureHeader("Statistics", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        echo "
                            <TABLE BORDER='0'>
                                <FORM ACTION='stat.php' METHOD='POST' NAME='stat'>
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Project :</B>
                                    </TD>
                                    <TD>
                                        <SELECT NAME='pid'>
             ";

        // Gets all the active projects from the database
        $Array = GetSubProjects();
        foreach($Array as $PID => $ProjectLabel)
        {
            echo "                                            	<OPTION VALUE='".$PID."'";
            // Keep the chosen project selected
            if ($PID == $RootProject)
            {
                echo " SELECTED";
            }
            echo "> ".htmlentities($ProjectLabel)." </OPTION>\n";
        }

        echo "
                                        </SELECT>
                                    </TD>
                                    <TD ALIGN='RIGHT'>
                                        <INPUT TYPE='SUBMIT' NAME='stat' VALUE='OK'>
                                    </TD>
                                </TR>
                                </FORM>
                                <FORM ACTION='stat.php?do=excel' METHOD='POST' NAME='excel'>
                                <TR>
                                    <TD COLSPAN='3' ALIGN='CENTER'>
                                        <INPUT TYPE='HIDDEN' NAME='pid' VALUE='".htmlentities($RootProject)."'>
                                        <INPUT TYPE='SUBMIT' NAME='excel' VALUE='Download Excel file'>
                                    </TD>
                                </TR>
                                </FORM>
                            </TABLE>
";

        $Label = "";
        if ($RootProject != 0)
        {
            $Label = GetProjectCompleteLabel($RootProject);
        }
        $TotalTime  = GetSubProjectsCumulativeTime($_SESSION['uid'], $RootProject);
        $Duration   = GetProjectTime($_SESSION['uid'], $RootProject);
        $Cumulative = $Duration + GetSubProjectsCumulativeTime($_SESSION['uid'], $RootProject);
        $Percentage = 100;
        if ($TotalTime != 0)
        {
            $Percentage = round((($Duration / $TotalTime) * 100), 2);
        }
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

        // Gets all the projects from the database
        // TODO : handle manager type user to return all their managed projects
        $Array = GetSubProjects(false, $RootProject, $Label);

        foreach($Array as $PID => $ProjectLabel)
        {
            $Duration   = GetProjectTime($_SESSION['uid'], $PID);
            $Cumulative = $Duration + GetSubProjectsCumulativeTime($_SESSION['uid'], $PID);
            $Percentage = round((($Duration / $TotalTime) * 100), 2);
            $CumulativePercentage = round((($Cumulative / $TotalTime) * 100), 2);

            echo "
                        <TR>
                            <FORM ACTION='project.php?do=modify' METHOD='POST'>
                                <TD ALIGN='LEFT'>
                                    <A href='task_search.php?do=search&pid=$PID'>".htmlentities($ProjectLabel)."</A>
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