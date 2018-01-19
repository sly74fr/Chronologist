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
 * FILE NAME   : group.inc.php
 * DESCRIPTION : Group related functions.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */

require_once("design.inc.php");

// Displays the common project form
function ShowGroupFields($GID = 0)
{
    // If no group was specified, show an empty form
    if ($GID == 0)
    {
        $Label      = "";
        $Administer = FALSE;

        echo "
                        <FORM ACTION='group.php?do=insert' METHOD='POST'>\n";
    }
    else // Show a form filled with the given group data
    {
        // Gets the data with the given ID
        $SQL = "SELECT  *
                FROM   `groups`, `user_groups`
                WHERE  `groups`.`gid` = '$GID'
                AND    `user_groups`.`gid` = `groups`.`gid`
               ";
        global $Connection;
        $Result = mysqli_query($Connection, $SQL)
        or die("Could not execute the '$SQL' request.");
        $Row = mysqli_fetch_array($Result);
        mysqli_free_result($Result);
        $Label      = $Row['label'];
        $Administer = $Row['administer'];

        echo "
                        <FORM ACTION='group.php?do=update' METHOD='POST'>\n";
    }

    echo "
                            <TABLE BORDER='0'>
                                <TR>
         ";

    // If the user belongs to the 'admin' or 'manager' group
    if ((ConnectedUserBelongsToAdminGroup() == TRUE) || (ConnectedUserBelongsToManagerGroup() == TRUE))
    {
        echo "
                                    <TD ALIGN='RIGHT'>
                                        <B> Group Label </B>
                                    </TD>
                                    <TD>
                                        <INPUT TYPE='TEXT' NAME='label' VALUE='".$Label."'>
                                    </TD>
                                </TR>";

        // If the connected user is an administrator
        if (ConnectedUserBelongsToAdminGroup() == TRUE)
        {
            echo "
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Administrator </B>
                                    </TD>
                                    <TD>
                                        <SELECT NAME='uid'>\n";

            $SQL = "SELECT *
                    FROM `users`
                   ";
            $Result = mysqli_query($Connection, $SQL)
            or die("Could not execute the '$SQL' request.");

            // Puts all the users in the pop-up
            while ($Row = mysqli_fetch_array($Result))
            {
                // Get the current user anme
                $UID      = $Row['uid'];
                $Name     = $Row['firstname']." ".$Row['lastname'];
                if ($Name == " ")
                {
                    $Name = $Row['email'];
                }

                echo "                                            	<OPTION VALUE='".$UID."'";
                if ($UID == $_SESSION['uid'])
                {
                    echo " SELECTED";
                }
                echo "> ".$Name."</OPTION>\n";
            }

            mysqli_free_result($Result);

            echo "
                                        </SELECT>
                                    </TD>";
        }
        else
        {
            echo "                                            <INPUT TYPE='HIDDEN' NAME='uid' VALUE='".$_SESSION['uid']."'>";

        }

        echo "
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Can Administer ? </B>
                                    </TD>
                                    <TD>
                                        <INPUT TYPE='CHECKBOX' NAME='administer'";
        if ($Administer == TRUE)
        {
            echo " CHECKED";
        }

        echo ">
                                    </TD>
                                </TR>
                                <TR>
                                    <TD ALIGN='CENTER' COLSPAN='2'>
                                        <INPUT TYPE='HIDDEN' NAME='gid' VALUE='".$GID."'>
                                        <BR> <INPUT TYPE='SUBMIT' VALUE='OK'>
                                    </TD>
             ";
    }
    else // If the user does not belong to the 'admin' group
    {
        echo "
                                    <TD ALIGN='CENTER' COLSPAN='2'>
                                        Group creation and modification is reserved to admin or manager level users. <BR>
                                        Please try again.
                                    </TD>
             ";
    }

    echo "
                            </TR>
                        </TABLE>
                    </FORM>
     ";
}
?>