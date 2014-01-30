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
 * FILE NAME   : security.inc.php
 * DESCRIPTION : Security facilities.
 * AUTHORS     : Sylvain LAFRASSE.
 *******************************************************************************
 */


// Add slashes to the given string for secure SQL use if needed
function putslashes($String)
{
	if (! get_magic_quotes_gpc())
	{
		// Add slashes and return the resulting string
		return addslashes($String);
	}

	// Otherwise, return the given string without any modification
	return $String;
}

// Quote variable to make safe
function quote_smart($value)
{
    // Stripslashes
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    // Quote if not integer
    if (!is_numeric($value)) {
        $value = "'" . mysql_real_escape_string($value) . "'";
    }
    return $value;
}	// If PHP is not configured to add slashes by default

/*
$EmailID = "email";
$FirstNameID = "firstname";
$LastNameID = "lastname";
fieldName = {$EmailID => "E-Mail", $FirstNameID => "First Name", $LastNameID => "Last Name", };
*/
CheckStringFields($Fields, $Clean)
{
    foreach($Fields as $FieldID => $FieldName)
    {
        if ($_POST['$FieldID'] == strval($_POST['$FieldID']))
        {
            $clean['$FieldID'] = quote_smart($_POST['$FieldID']);
        }
        else
        {
        	$_SESSION['message'] .= "The '$FieldName' field is not a valid string. <BR> Please try again. <BR>";
            return FALSE;
        }
    }
    return TRUE;
}

CheckIntFields($Fields, $Clean)
{
    foreach($Fields as $FieldID => $FieldName)
    {
        if ($_POST['$FieldID'] == strval(intval($_POST['$FieldID'])))
        {
            $clean['$FieldID'] = quote_smart($_POST['$FieldID']);
        }
        else
        {
        	$_SESSION['message'] .= "The '$FieldName' field is not a valid integer. <BR> Please try again. <BR>";
            return FALSE;
        }
    }
    return TRUE;
}

CheckEmailFields($Fields, $Clean)
{
    foreach($Fields as $FieldID => $FieldName)
    {
        $email_pattern = '/^[^@\s<&>]+@([-a-z0-9]+\.)+[a-z]{2,}$/i'; 
        if (preg_match($email_pattern, $_POST['$FieldID']))
        {
            $clean['$FieldID'] = quote_smart($_POST['$FieldID']);
        }
        else
        {
        	$_SESSION['message'] .= "The '$FieldName' field is not a valid e-mail address. <BR> Please try again. <BR>";
            return FALSE;
        }
    }
    return TRUE;
}

?>