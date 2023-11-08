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
 * FILE NAME   : project.inc.php
 * DESCRIPTION : Project related functions.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


// TODO : Show only user and group projects

require_once("design.inc.php");

class Project
{
    var $pid;           // Activity identifier
    var $ppid;          // Parent activity identifier
    var $label;         // Activity title
    var $parentLabel;   // Parent activities concatened titles
    
    function getLabel()
    {
        return $this->label;
    }
    
    function setLabel($label)
    {
        $this->label = $label;
    }
    
    function getParent()
    {
        return $this->ppid;
    }
    
    function setParent($parent)
    {
        $this->ppid = $parent->ppid;
    }

    // Return the complete label of one project
    function getCompleteLabel()
    {
        $LocalPID = $this->pid;
    
        if ($LocalPID == 0)
        {
            return "None";
        }
    
        // Puts all the parent-project labels in $CompleteLabel
        do
        {
            // Gets the current project label from the database
            $SQL = "SELECT *
                    FROM   projects
                    WHERE  pid = '$LocalPID'
                   ";
            global $Connection;
            $Result = mysqli_query($Connection, $SQL)
            or die("Could not execute the '$SQL' request.");
            $Row = mysqli_fetch_array($Result);
            mysqli_free_result($Result);
            $Label = $Row['label'];
    
            // If it's the first loop
            if ($LocalPID == $this->pid)
            {
                // Only append the project name
                $CompleteLabel = $Label;
            }
            else
            {
                // Otherwise append " - " after the parent project label
                $CompleteLabel = $Label." - ".$CompleteLabel;
            }
    
            // Loop on the parent project of the current one
            $LocalPID = $Row['ppid'];
        }
        while ($LocalPID != "0"); // Loop until it reaches the highest level project
    
        return $CompleteLabel;
    }

    // Displays the common project form
    function ShowProjectFields($PID = 0)
    {
        // If no project was specified, show an empty form
        if ($PID == 0)
        {
            $Parent = 0;
            $Label  = "";
            $Closed = false;
    
            echo "
                               <FORM ACTION='project.php?do=insert' METHOD='POST'>\n";
        }
        else // Show a form filled with the given project data
        {
            // Gets the data with the given ID
            $SQL = "SELECT  DISTINCT *
                    FROM   `projects`
                    WHERE  `pid` = '$PID'
                   ";
            global $Connection;
            $Result = mysqli_query($Connection, $SQL)
            or die("Could not execute the '$SQL' request.");
            $Row = mysqli_fetch_array($Result);
            mysqli_free_result($Result);
    
            $Parent = $Row['ppid'];
            $Label  = $Row['label'];
            $Closed = false;
            if ($Row['closed'] == 1)
            {
                $Closed = true;
            }
    
            echo "
                            <FORM ACTION='project.php?do=update' METHOD='POST'>\n";
        }
    
        echo "
                                <TABLE BORDER='0'>
                                    <TR>
                                        <TD ALIGN='RIGHT'>
                                            <B> Sub-Project of </B>
                                        </TD>
                                        <TD>
                                            <SELECT NAME='ppid'>
                                                <OPTION VALUE='0'> None </OPTION>\n";
    
        $Array = array();
        if (ConnectedUserBelongsToAdminGroup() == TRUE)
        {
            $Array = GetSubProjects();
        }
        else if (ConnectedUserBelongsToManagerGroup() == TRUE)
        {
            foreach($_SESSION['gid'] as $Key => $GID)
            {
                $Array = $Array + GetGroupSubProjects($GID);
            }
        }
        else
        {
            $Array = GetUserSubProjects($_SESSION['uid']);
        }
    
        // Gets all the projects from the database
        foreach($Array as $AllPID => $AllLabel)
        {
            if ($AllPID != $PID)
            { 
                echo "                                            	<OPTION VALUE='".$AllPID."'";
                if ($AllPID == $Parent)
                {
                    echo " SELECTED";
                }
                echo "> ".$AllLabel."</OPTION>\n";
            }
        }
    
        echo "
                                           </SELECT>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Label </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='TEXT' NAME='label' VALUE='".htmlentities($Label)."'>
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='RIGHT'>
                                           <B> Closed </B>
                                       </TD>
                                       <TD>
                                           <INPUT TYPE='CHECKBOX' NAME='closed'";
    
                // If the project is closed
                if ($Closed == true)
                {
                    echo " CHECKED";
                }
    
                echo ">
                                       </TD>
                                   </TR>
                                   <TR>
                                       <TD ALIGN='CENTER' COLSPAN='2'>
                                           <INPUT TYPE='HIDDEN' NAME='pid' VALUE='".$PID."'>
                                           <BR> <INPUT TYPE='SUBMIT' NAME='ok' VALUE='OK'>
                                       </TD>
                                    </TR>
                                </TABLE>
                            </FORM>
             ";
    }
}

// Return the complete label of one project
function GetProjectCompleteLabel($PID = 0)
{
	$LocalPID = $PID;

	if ($LocalPID == 0)
	{
		return "None";
	}

	// Puts all the parent-project labels in $CompleteLabel
	do
	{
		// Gets the current project label from the database
		$SQL = "SELECT *
				FROM   projects
				WHERE  pid = '$LocalPID'
			   ";
		global $Connection;
        $Result = mysqli_query($Connection, $SQL)
		or die("Could not execute the '$SQL' request.");
		$Row = mysqli_fetch_array($Result);
		mysqli_free_result($Result);
		$Label = $Row['label'];

		// If its the first loop
		if ($LocalPID == $PID)
		{
			// Only append the project name
			$CompleteLabel = $Label;
		}
		else
		{
			// Otherwise append " - " after the parent project label
			$CompleteLabel = $Label." - ".$CompleteLabel;
		}

		// Loop on the parent project of the current one
		$LocalPID = $Row['ppid'];
	}
	while ($LocalPID != "0"); // Loop until it reaches the highest level project

	return $CompleteLabel;
}

// Return all the projects of a group
function GetGroupSubProjects($GID, $PPID = 0, $Label = "")
{
	$Array = array();

	// Gets all the subprojects of the given project from the database
	$SQL = "SELECT   projects.pid, label
			FROM     projects, group_projects
			WHERE    projects.ppid       = '$PPID'
			AND      group_projects.pid  =  projects.pid
			AND      group_projects.gid  = '$GID'
			ORDER BY label
		   ";
	global $Connection;
    $Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");

	// Puts all the sub-projects of the current sub-project in the 'to-be-returned' array
	while ($Row = mysqli_fetch_array($Result))
	{
		// Get the current project ID
		$PID      = $Row['pid'];
		$SubLabel = $Label;

		// If the current project is not a top-level one
		if ($PPID != 0)
		{
			// Build its name (i.e "father name - ... - current project name")
			$SubLabel .= " - ";
		}

		$SubLabel .= $Row['label'];

		// Puts the current project ID and built label in the array
		$Array = $Array + array("$PID" => "$SubLabel");

		// Recursively do the same thing for all the sub-projects of the current project
		$Array = $Array + GetGroupSubProjects($GID, $PID, $SubLabel);
	}

	mysqli_free_result($Result);

	return $Array;
}

// Return all the projects of a user
function GetUserSubProjects($UID, $PPID = 0, $Label = "")
{
	$Array = array();

	// Gets all the subprojects of the given project from the database
	$SQL = "SELECT   projects.pid, label
			FROM     projects, user_projects
			WHERE    projects.ppid      = '$PPID'
			AND      user_projects.pid  =  projects.pid
			AND      user_projects.uid  = '$UID'
			ORDER BY label
		   ";
	global $Connection;
    $Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");

	// Puts all the sub-projects of the current sub-project in the 'to-be-returned' array
	while ($Row = mysqli_fetch_array($Result))
	{
		// Get the current project ID
		$PID      = $Row['pid'];
		$SubLabel = $Label;

		// If the current project is not a top-level one
		if ($PPID != 0)
		{
			// Build its name (i.e "father name - ... - current project name")
			$SubLabel .= " - ";
		}

		$SubLabel .= $Row['label'];

		// Puts the current project ID and built label in the array
		$Array = $Array + array("$PID" => "$SubLabel");

		// Recursively do the same thing for all the sub-projects of the current project
		$Array = $Array + GetUserSubProjects($UID, $PID, $SubLabel);
	}

	mysqli_free_result($Result);

	return $Array;
}

// Return all the projects
function GetSubProjects($ActiveOnly = false, $PPID = 0, $Label = "")
{
	$Array = array();

	if ($PPID == 0)
	{
		$Array = $Array + array("-1" => "- None -");
	}

	// Gets all the subprojects of the given project from the database
	$SQL =  "SELECT   pid, label
             FROM     projects
             WHERE    ppid = '$PPID'
		    ";

    if ($ActiveOnly == true)
    {
        $SQL .= "AND      closed = 0
                ";
    }

	$SQL .= "ORDER BY label
		    ";
	global $Connection;
    $Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");

	// Puts all the sub-projects of the current sub-project in the 'to-be-returned' array
	while ($Row = mysqli_fetch_array($Result))
	{
		// Get the current project ID
		$PID      = $Row['pid'];
		$SubLabel = $Label;

		// If the current project is not a top-level one
		if ($PPID != 0)
		{
			// Build its name (i.e "father name - ... - current project name")
			$SubLabel .= " - ";
		}

		$SubLabel .= $Row['label'];

		// Puts the current project ID and built label in the array
		$Array = $Array + array("$PID" => "$SubLabel");

		// Recursively do the same thing for all the sub-projects of the current project
		$Array = $Array + GetSubProjects($ActiveOnly, $PID, $SubLabel);
	}

	mysqli_free_result($Result);

	return $Array;
}


// Displays the common project form
function ShowProjectFields($PID = 0)
{
	// If no project was specified, show an empty form
	if ($PID == 0)
	{
        $Parent = 0;
        $Label  = "";
        $Closed = false;

        echo "
                           <FORM ACTION='project.php?do=insert' METHOD='POST'>\n";
	}
	else // Show a form filled with the given project data
	{
		// Gets the data with the given ID
		$SQL = "SELECT  DISTINCT *
				FROM   `projects`
				WHERE  `pid` = '$PID'
			   ";
		global $Connection;
        $Result = mysqli_query($Connection, $SQL)
		or die("Could not execute the '$SQL' request.");
		$Row = mysqli_fetch_array($Result);
		mysqli_free_result($Result);

		$Parent = $Row['ppid'];
		$Label  = $Row['label'];
        $Closed = false;
        if ($Row['closed'] == 1)
        {
            $Closed = true;
        }

		echo "
						<FORM ACTION='project.php?do=update' METHOD='POST'>\n";
	}

	echo "
							<TABLE BORDER='0'>
								<TR>
									<TD ALIGN='RIGHT'>
										<B> Sub-Project of </B>
									</TD>
									<TD>
										<SELECT NAME='ppid'>
											<OPTION VALUE='0'> None </OPTION>\n";

	$Array = array();
	if (ConnectedUserBelongsToAdminGroup() == TRUE)
	{
		$Array = GetSubProjects();
	}
	else if (ConnectedUserBelongsToManagerGroup() == TRUE)
	{
		foreach($_SESSION['gid'] as $Key => $GID)
		{
			$Array = $Array + GetGroupSubProjects($GID);
		}
	}
	else
	{
		$Array = GetUserSubProjects($_SESSION['uid']);
	}

	// Gets all the projects from the database
	foreach($Array as $AllPID => $AllLabel)
	{
		if ($AllPID != $PID)
		{ 
			echo "                                            	<OPTION VALUE='".$AllPID."'";
			if ($AllPID == $Parent)
			{
				echo " SELECTED";
			}
			echo "> ".$AllLabel."</OPTION>\n";
		}
	}

    echo "
                                       </SELECT>
                                   </TD>
                               </TR>
                               <TR>
                                   <TD ALIGN='RIGHT'>
                                       <B> Label </B>
                                   </TD>
                                   <TD>
                                       <INPUT TYPE='TEXT' NAME='label' VALUE='".htmlentities($Label)."'>
                                   </TD>
                               </TR>
                               <TR>
                                   <TD ALIGN='RIGHT'>
                                       <B> Closed </B>
                                   </TD>
                                   <TD>
                                       <INPUT TYPE='CHECKBOX' NAME='closed'";

	        // If the project is closed
	        if ($Closed == true)
	        {
		        echo " CHECKED";
		    }

            echo ">
                                   </TD>
                               </TR>
                               <TR>
                                   <TD ALIGN='CENTER' COLSPAN='2'>
                                       <INPUT TYPE='HIDDEN' NAME='pid' VALUE='".$PID."'>
                                       <BR> <INPUT TYPE='SUBMIT' NAME='ok' VALUE='OK'>
                                   </TD>
                                </TR>
                            </TABLE>
                        </FORM>
         ";
}
?>