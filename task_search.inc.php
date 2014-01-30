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
 * FILE NAME   : task_search.inc.php
 * DESCRIPTION : Task searching functions.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


// Displays the common task search form
function ShowTaskSearchFields()
{
    $TaskProject         = "";

    $TaskActivity  = 0;

    $TaskLabel           = "";

    $TaskBeginning       = $TimeStamp;
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

    $TaskBeginningYear   = 0;
    $TaskBeginningMonth  = 0;
    $TaskBeginningDay    = 0;
    $TaskBeginningHour   = 0;
    $TaskBeginningMinute = 0;

    echo "              <FORM ACTION='task_search.php?do=search' METHOD='POST' NAME='task'>
                            <TABLE BORDER='0'>
                                <TR>
                                    <TD ALIGN='RIGHT'>
                                        <B> Project :</B>
                                    </TD>
                                    <TD>
                                        <SELECT NAME='pid'>
             ";

        // Gets all the projects from the database
        $Array = GetSubProjects();
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
                                                    <INPUT TYPE='TEXT' NAME='beginningYear' SIZE='4' MAXLENGTH ='4' VALUE='".htmlentities(sprintf("%02d", $TaskBeginningYear))."'>
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
                                                    <INPUT TYPE='TEXT' NAME='beginningMinute' SIZE='2' MAXLENGTH ='2' VALUE='".sprintf("%02d", $TaskBeginningMinute)."'>
                                                </TD>
                                            </TR>
                                        </TABLE>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD ALIGN='CENTER' COLSPAN='2'>
                                        <BR> <INPUT TYPE='SUBMIT' NAME='search' VALUE='Search'>
                                    </TD>
                                </TR>
                            </TABLE>
                        </FORM>
         ";
}
?>