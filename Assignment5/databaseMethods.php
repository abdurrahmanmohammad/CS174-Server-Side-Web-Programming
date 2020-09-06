<?php

/**
 * Function to delete a record
 */
function delete($conn, $table)
{
    $name = get_post($conn, 'Name');
    $query = "DELETE FROM {$table} WHERE Name='$name'";
    $result = $conn->query($query);
    if (! $result)
        die(mysql_fatal_error("Failed to delete: $query<br>" . $conn->error . "<br><br>")); // Inform user of error before quitting
}

/**
 * Function to insert a record
 */
function insert($conn, $table)
{
    $name = get_post($conn, 'Name');
    $content = mysql_fix_string($conn, readNparse($_FILES['filename']['tmp_name'])); // Read the file and extract its contents
    $query = "INSERT INTO {$table} VALUES" . "('$name', '$content')";
    $result = $conn->query($query);
    if (! $result)
        die(mysql_fatal_error("Failed to insert: $query<br>" . $conn->error . "<br><br>"));
}

/**
 * Function to print a table in a MySQL database
 */
function printQuery($conn, $table)
{
    $query = "SELECT * FROM {$table}"; // Access the complete table stored in 'table'
    $result = $conn->query($query); // Extract contents from table
    if (! $result)
        die(mysql_fatal_error("Database access failed: " . $conn->error)); // If error occurs, inform user
    $rows = $result->num_rows; // Store the number of rows
    for ($j = 0; $j < $rows; ++ $j) { // Print out all entries in table
        $result->data_seek($j); // Get the jth row
        $row = $result->fetch_array(MYSQLI_NUM); // Convert it into an associative array
        echo <<<_END
        <pre>
        Name $row[0]
        Content $row[1]
        </pre>
        <form action="assignment5.php" method="post">
        <input type="hidden" name="delete" value="yes">
        <input type="hidden" name="Name" value="$row[0]">
        <input type="submit" value="DELETE RECORD"></form>
        _END;
    }
    $result->close();
}

// **************************************** File, Parsing, & Error Methods ****************************************
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
    //echo "<br>" . $error; // Print out the error ***(for debugging purposes only)***
}

/**
 * Funtion to read a file and return the stored text
 */
function readNparse($fname)
{
    $fh = fopen($fname, 'r') or die(mysql_fatal_error("Cannot open file!")); // Try to access the file. If not, quit.
    while (! feof($fh))
        $text .= fgets($fh); // While not the end of file, append $text with each line of input
    fclose($fh); // Close the file
    return $text; // Return extracted text
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
 * Fixes input string for MySQL
 */
function mysql_fix_string($conn, $string)
{
    if (get_magic_quotes_gpc())
        $string = stripslashes($string);

    return $conn->real_escape_string($string);
}

// **************************************** Database & Table Construction ****************************************
/**
 * Builds a MySQL table with Name (16 characters maximum) and Content fields if it doesn't already exist.
 * Name: VARCHAR is used because it is faster and its only 16 chars max. Name cannot be null or it will crach program.
 * Content: LONGTEXT is used because it can store more bytes than VARCHAR for very large files. We can have empty files.
 */
function buildTable($conn, $name)
{
    if ($conn->connect_error)
        die(mysql_fatal_error("Could not access DB when building table: " . $conn->error));
    $query = "CREATE TABLE IF NOT EXISTS {$name} (
		Name VARCHAR(16) NOT NULL,
        Content LONGTEXT
	)";
    if (! $conn->query($query))
        die(mysql_fatal_error("Could not build table: " . $conn->error));
}

/**
 * Builds a database if TextFileDatabase doesn't already exist
 */
function buildDB($conn, $name)
{
    if ($conn->connect_error)
        die(mysql_fatal_error("Could not access MySQLwhen building table: " . $conn->connect_error)); // Check connection. If cannot connect to MySQL, terminate program.
    $sql = "CREATE DATABASE IF NOT EXISTS TextFileDatabase"; // Build database if it does not already exist
    if ($conn->query($sql) === FALSE)
        echo mysql_fatal_error("Error creating database: " . $conn->error); // Check if database was created successfully
    $conn->close(); // Close the connection
}