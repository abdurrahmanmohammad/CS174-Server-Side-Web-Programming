<?php

// **************************************** Error & Sanitization Methods ****************************************
/**
 * Function to notify the user when error occurs
 */
function mysql_fatal_error($error)
{
    echo <<<_END
    We are sorry, but it was not possible to complete the requested task.
    Please click the back button on your browser and try again.
    Thank you.
    _END;
    // echo "<br>" . $error; // Print out the error ***(for debugging purposes only)***
}

/**
 * Passes each item it retrieves through the real_escape_string method of the connection object to strip
 * out any characters that a hacker may have inserted in order to break into or alter your database
 */
function get_post($conn, $var)
{
    return $conn->real_escape_string($_POST[$var]);
}

/**
 * Helper method for sanitizeMySQL
 */
function sanitizeString($var)
{
    $var = stripslashes($var);
    $var = strip_tags($var);
    $var = htmlentities($var);
    return $var;
}

/**
 * Sanitizes input string for MySQL
 */
function sanitizeMySQL($connection, $var)
{
    $var = $connection->real_escape_string($var);
    $var = sanitizeString($var);
    return $var;
}