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
 * FILE NAME   : activity.inc.php
 * DESCRIPTION : Activities related functions.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


// TODO : Show only user and group activities

require_once("design.inc.php");

class Activity
{
    var $aid;           // Activity identifier
    var $paid;          // Parent activity identifier
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
        return $this->paid;
    }
    
    function setParent($parent)
    {
        $this->paid = $parent->paid;
    }

    // Return the complete label of one activity
    function getCompleteLabel()
    {
        $LocalAID = $this->aid;
        
        if ($LocalAID == 0)
        {
            return "None";
        }
        
        // Puts all the parent-activities labels in $CompleteLabel
        do
        {
            // Gets the current activity label from the database
            $SQL = "SELECT *
                    FROM   activities
                    WHERE  aid = '$LocalAID'";
            global $Connection;
            $Result = mysqli_query($Connection, $SQL)
                or die("Could not execute the '$SQL' request.");
            $Row = mysqli_fetch_array($Result);
            mysqli_free_result($Result);
            $Label = $Row['label'];
            
            // If its the first loop
            if ($LocalAID == $this->aid)
            {
                // Only append the activity name
                $CompleteLabel = $Label;
            }
            else
            {
                // Otherwise append " - " after the parent activity label
                $CompleteLabel = $Label." - ".$CompleteLabel;
            }
            
            // Loop on the parent activities of the current one
            $LocalAID = $Row['paid'];
        }
        while ($LocalAID != "0"); // Loop until it reaches the highest level activity
        
        return $CompleteLabel;
    }
    
    // Displays the common activity form
    function show($AID = 0)
    {
        // If no activity was specified, show an empty form
        if ($AID == 0)
        {
            $Parent = 0;
            $Label  = "";
            
            echo "<FORM ACTION='activity.php?do=insert' METHOD='POST'>\n";
        }
        else // Show a form filled with the given activity data
        {
            // Gets the data with the given ID
            $SQL = "SELECT  DISTINCT *
                    FROM   `activities`
                    WHERE  `aid` = '$AID'";
            global $Connection;
            $Result = mysqli_query($Connection, $SQL)
                or die("Could not execute the '$SQL' request.");
            $Row = mysqli_fetch_array($Result);
            mysqli_free_result($Result);
            
            $Parent = $Row['paid'];
            $Label  = $Row['label'];
            
            echo "<FORM ACTION='activity.php?do=update' METHOD='POST'>\n";
        }
        
        echo "  <TABLE BORDER='0'>
                    <TR>
                        <TD ALIGN='RIGHT'>
                            <B> Sub-activity of </B>
                        </TD>
                        <TD>
                            <SELECT NAME='paid'>
                                <OPTION VALUE='0'> None </OPTION>\n";

        $Array = array();
        $Array = GetSubActivities();
        /*
         if (ConnectedUserBelongsToAdminGroup() == TRUE)
         {
             $Array = GetSubActivities();
         }
         else if (ConnectedUserBelongsToManagerGroup() == TRUE)
         {
             foreach($_SESSION['gid'] as $Key => $GID)
         {
                 $Array = $Array + GetGroupSubActivities($GID);
         }
         }
         else
         {
             $Array = GetUserSubActivities($_SESSION['uid']);
         }
         */
        
        // Gets all the activities from the database
        foreach($Array as $AllAID => $AllLabel)
        {
            if ($AllAID != $AID)
            { 
                echo "                                            	<OPTION VALUE='".$AllAID."'";
                if ($AllAID == $Parent)
                {
                    echo " SELECTED";
                }
                echo "> ".$AllLabel."</OPTION>\n";
            }
        }
        
        echo "      </SELECT>
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
                <TD ALIGN='CENTER' COLSPAN='2'>
                    <INPUT TYPE='HIDDEN' NAME='aid' VALUE='".$AID."'>
                    <BR>
                    <INPUT TYPE='SUBMIT' NAME='ok' VALUE='OK'>
                </TD>
            </TR>
        </TABLE>
    </FORM>";
    }

    // Return all the activities
    function GetSubActivities($PAID = 0, $Label = "", $ShouldIncludeNone = true)
    {
        $Array = array();
        
        if (($ShouldIncludeNone == true) && ($PAID == 0))
        {
            $Array = $Array + array("-1" => "- None -");
        }
        
        // Gets all the sub-activities of the given activity from the database
        $SQL = "SELECT   aid, label
                FROM     activities
                WHERE    paid = '$PAID'
                ORDER BY label";
        global $Connection;
        $Result = mysqli_query($Connection, $SQL)
        or die("Could not execute the '$SQL' request.");
        
        // Puts all the sub-activities of the current sub-activity in the 'to-be-returned' array
        while ($Row = mysqli_fetch_array($Result))
        {
            // Get the current activity ID
            $AID      = $Row['aid'];
            $SubLabel = $Label;
            
            // If the current activity is not a top-level one
            if ($PAID != 0)
            {
                // Build its name (i.e "father name - ... - current activity name")
                $SubLabel .= " - ";
            }
            
            $SubLabel .= $Row['label'];
            
            // Puts the current activity ID and built label in the array
            $Array = $Array + array("$AID" => "$SubLabel");
            
            // Recursively do the same thing for all the sub-activities of the current activity
            $Array = $Array + GetSubActivities($AID, $SubLabel);
        }
        
        mysqli_free_result($Result);
        
        return $Array;
    }

    function update()
    {
        // TODO : Checks that the given activities label doesn't already exists in the database for the selected sub-activities
        $SQL = "UPDATE  `activitiess`
                SET     `label` = '$this->label',
                        `paid`  = '$this->paid',
                WHERE  `aid`   = '$this->aid'";
        global $Connection;
        $Result = mysqli_query($Connection, $SQL)
        or die("Could not execute the '$SQL' request.");
    }
    
    function insert()
    {
        // TODO : Checks that the given activities label doesn't already exists in the database for the selected sub-activities
        $SQL = "INSERT INTO `activities`
                VALUES (NULL,
                        '$this->paid',
                        '$this->label')";
        global $Connection;
        $Result = mysqli_query($Connection, $SQL)
        or die("Could not execute the '$SQL' request.");
        
        $this->aid = mysqli_insert_id($Connection);
    }
}

// Return the complete label of one activity
function GetActivityCompleteLabel($AID = 0)
{
	$LocalAID = $AID;

	if ($LocalAID == 0)
	{
		return "None";
	}

	// Puts all the parent-activities labels in $CompleteLabel
	do
	{
		// Gets the current activity label from the database
		$SQL = "SELECT *
				FROM   activities
				WHERE  aid = '$LocalAID'
			   ";
        global $Connection;
		$Result = mysqli_query($Connection, $SQL)
		  or die("Could not execute the '$SQL' request.");
		$Row = mysqli_fetch_array($Result);
		mysqli_free_result($Result);
		$Label = $Row['label'];

		// If its the first loop
		if ($LocalAID == $AID)
		{
			// Only append the activity name
			$CompleteLabel = $Label;
		}
		else
		{
			// Otherwise append " - " after the parent activity label
			$CompleteLabel = $Label." - ".$CompleteLabel;
		}

		// Loop on the parent activities of the current one
		$LocalAID = $Row['paid'];
	}
	while ($LocalAID != "0"); // Loop until it reaches the highest level activity

	return $CompleteLabel;
}

// Return all the activities of a group
function GetGroupSubActivities($GID, $PAID = 0, $Label = "")
{
	$Array = array();

	// Gets all the sub-activities of the given activity from the database
	$SQL = "SELECT   activities.aid, label
			FROM     activities, group_activities
			WHERE    activities.paid       = '$PAID'
			AND      group_activities.aid  =  activities.aid
			AND      group_activities.gid  = '$GID'
			ORDER BY label
		   ";
    global $Connection;
	$Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");

	// Puts all the sub-activities of the current sub-activity in the 'to-be-returned' array
	while ($Row = mysqli_fetch_array($Result))
	{
		// Get the current activity ID
		$AID      = $Row['aid'];
		$SubLabel = $Label;

		// If the current activity is not a top-level one
		if ($PAID != 0)
		{
			// Build its name (i.e "father name - ... - current activity name")
			$SubLabel .= " - ";
		}

		$SubLabel .= $Row['label'];

		// Puts the current activities ID and built label in the array
		$Array = $Array + array("$AID" => "$SubLabel");

		// Recursively do the same thing for all the sub-activities of the current activity
		$Array = $Array + GetGroupSubActivities($GID, $AID, $SubLabel);
	}

	mysqli_free_result($Result);

	return $Array;
}

// Return all the activities of a user
function GetUserSubActivities($UID, $PAID = 0, $Label = "")
{
	$Array = array();

	// Gets all the sub-activities of the given activity from the database
	$SQL = "SELECT   activities.aid, label
			FROM     activities, user_activities
			WHERE    activities.paid      = '$PAID'
			AND      user_activities.aid  =  activities.aid
			AND      user_activities.uid  = '$UID'
			ORDER BY label
		   ";
    global $Connection;
	$Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");

	// Puts all the sub-activities of the current sub-activity in the 'to-be-returned' array
	while ($Row = mysqli_fetch_array($Result))
	{
		// Get the current activity ID
		$AID      = $Row['aid'];
		$SubLabel = $Label;

		// If the current activity is not a top-level one
		if ($PAID != 0)
		{
			// Build its name (i.e "father name - ... - current activity name")
			$SubLabel .= " - ";
		}

		$SubLabel .= $Row['label'];

		// Puts the current activity ID and built label in the array
		$Array = $Array + array("$AID" => "$SubLabel");

		// Recursively do the same thing for all the sub-activities of the current activity
		$Array = $Array + GetUserSubActivities($UID, $AID, $SubLabel);
	}

	mysqli_free_result($Result);

	return $Array;
}

// Return all the activities
function GetSubActivities($PAID = 0, $Label = "", $ShouldIncludeNone = true)
{
	$Array = array();

	if (($ShouldIncludeNone == true) && ($PAID == 0))
	{
		$Array = $Array + array("-1" => "- None -");
	}

	// Gets all the sub-activities of the given activity from the database
	$SQL =  "SELECT   aid, label
             FROM     activities
             WHERE    paid = '$PAID'
		     ORDER BY label
		    ";
    global $Connection;
	$Result = mysqli_query($Connection, $SQL)
	or die("Could not execute the '$SQL' request.");

	// Puts all the sub-activities of the current sub-activity in the 'to-be-returned' array
	while ($Row = mysqli_fetch_array($Result))
	{
		// Get the current activity ID
		$AID      = $Row['aid'];
		$SubLabel = $Label;

		// If the current activity is not a top-level one
		if ($PAID != 0)
		{
			// Build its name (i.e "father name - ... - current activity name")
			$SubLabel .= " - ";
		}

		$SubLabel .= $Row['label'];

		// Puts the current activity ID and built label in the array
		$Array = $Array + array("$AID" => "$SubLabel");

		// Recursively do the same thing for all the sub-activities of the current activity
		$Array = $Array + GetSubActivities($AID, $SubLabel);
	}

	mysqli_free_result($Result);

	return $Array;
}


// Displays the common activity form
function ShowActivityFields($AID = 0)
{
	// If no activity was specified, show an empty form
	if ($AID == 0)
	{
        $Parent = 0;
        $Label  = "";

        echo "
                           <FORM ACTION='activity.php?do=insert' METHOD='POST'>\n";
	}
	else // Show a form filled with the given activity data
	{
		// Gets the data with the given ID
		$SQL = "SELECT  DISTINCT *
				FROM   `activities`
				WHERE  `aid` = '$AID'
			   ";
        global $Connection;
		$Result = mysqli_query($Connection, $SQL)
    		or die("Could not execute the '$SQL' request.");
		$Row = mysqli_fetch_array($Result);
		mysqli_free_result($Result);

		$Parent = $Row['paid'];
		$Label  = $Row['label'];

		echo "
						<FORM ACTION='activity.php?do=update' METHOD='POST'>\n";
	}

	echo "
							<TABLE BORDER='0'>
								<TR>
									<TD ALIGN='RIGHT'>
										<B> Sub-activity of </B>
									</TD>
									<TD>
										<SELECT NAME='paid'>
											<OPTION VALUE='0'> None </OPTION>\n";

	$Array = array();
    $Array = GetSubActivities();
/*
	if (ConnectedUserBelongsToAdminGroup() == TRUE)
	{
		$Array = GetSubActivities();
	}
	else if (ConnectedUserBelongsToManagerGroup() == TRUE)
	{
		foreach($_SESSION['gid'] as $Key => $GID)
		{
			$Array = $Array + GetGroupSubActivities($GID);
		}
	}
	else
	{
		$Array = GetUserSubActivities($_SESSION['uid']);
	}
*/

	// Gets all the activities from the database
	foreach($Array as $AllAID => $AllLabel)
	{
		if ($AllAID != $AID)
		{ 
			echo "                                            	<OPTION VALUE='".$AllAID."'";
			if ($AllAID == $Parent)
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
                                   <TD ALIGN='CENTER' COLSPAN='2'>
                                       <INPUT TYPE='HIDDEN' NAME='aid' VALUE='".$AID."'>
                                       <BR> <INPUT TYPE='SUBMIT' NAME='ok' VALUE='OK'>
                                   </TD>
                                </TR>
                            </TABLE>
                        </FORM>
         ";
}
?>