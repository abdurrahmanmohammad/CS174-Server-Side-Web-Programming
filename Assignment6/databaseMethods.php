<?php

/**
 * Function to search for a record
 * Implement one or more PHP functions that read the input from 
 * the SEARCH section and prepare the corresponding query for the Database
 */
function search($conn, $table)
{
    $advisor = sanitizeMySQL($conn, get_post($conn, 'SearchData')); // Get the student ID data stored in Search
    $query = "SELECT * FROM {$table} WHERE Advisor='$advisor'"; // Query command to execute
    $result = $conn->query($query); // Attempt to retrieve record by executing query command
    if (! $result) // If there is no result or the search is empty
        die(mysql_fatal_error("Database access failed: " . $conn->error)); // If error occurs, inform user
    else { // If record exists, print record
        $row = $result->fetch_array(MYSQLI_NUM); // Convert it into an associative array
        if ($row[0] == '' || $row[1] == '' || $row[2] == '' || $row[3] == '')
            echo "Record does not exist!"; // If any rows and columns are empty, print error message. Table fields cannot be null.
        else
            printRecord($row); // Print the record by passing in the array of data
    }
    $result->close();
}

/**
 * Implement a function that, when a search is made in the SEARCH section,
 * it shows on the webpage the result of the query
 */
function printRecord($row)
{
    echo <<<_END
    <pre>
    Advisor $row[0]
    Student $row[1]
    StudentID $row[2]
    ClassCode $row[3]
    </pre>
    _END;
}

/**
 * Function to insert a record
 * Placeholders and sanitizeString used to sanitize
 * Implement one or more PHP functions that read the inputs from the ADD section of
 * the Webpage and prepare the corresponding query to add the record in the Database
 */
function insert($conn, $table)
{
    $advisor = sanitizeMySQL($conn, get_post($conn, 'Advisor'));
    $student = sanitizeMySQL($conn, get_post($conn, 'Student'));
    $studentID = sanitizeMySQL($conn, get_post($conn, 'StudentID'));
    $classCode = sanitizeMySQL($conn, get_post($conn, 'ClassCode'));
    if ($advisor == '' || $student == '' || $studentID == '' || $classCode == '') // If any are empty
        echo "Failed to insert!<br>Blank Fields!";
    else { // We don't want empty rows in table and we don't want to sanitize empty data
        $stmt = $conn->prepare("INSERT INTO {$table} VALUES(?,?,?,?)");
        $stmt->bind_param('ssss', $advisor, $student, $studentID, $classCode);
        $stmt->execute();
        if ($stmt->affected_rows == 1)
            echo "Record successfully added!";
        else
            echo "Failed to insert!";
        $stmt->close();
    }
}

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

// **************************************** Database & Table Construction ****************************************
/**
 * Builds a MySQL table with Name (16 characters maximum) and Content fields if it doesn't already exist
 * Advisor, Student: VARCHAR is used because it is faster and restricts to 30 chars max
 * StudentID: VARCHAR is used because a student ID is generally 9 digits at SJSU and its faster
 * ClassCode: VARCHAR is used because a class code is generally 5 digits at SJSU and its faster
 */
function buildTable($conn, $name)
{
    if ($conn->connect_error)
        die(mysql_fatal_error("Could not access DB when building table: " . $conn->error));
    $query = "CREATE TABLE IF NOT EXISTS {$name} (
		Advisor VARCHAR(30) NOT NULL,
        Student VARCHAR(30) NOT NULL,
        StudentID VARCHAR(9) NOT NULL,
        ClassCode VARCHAR(5) NOT NULL
	)";
    if (! $conn->query($query))
        die(mysql_fatal_error("Could not build table: " . $conn->error));
}

/**
 * Builds a database if AdvisingDatabase doesn't already exist
 */
function buildDB($conn, $name)
{
    if ($conn->connect_error)
        die(mysql_fatal_error("Could not access MySQLwhen building table: " . $conn->connect_error)); // Check connection. If cannot connect to MySQL, terminate program.
    $sql = "CREATE DATABASE IF NOT EXISTS {$name}"; // Build database if it does not already exist
    if ($conn->query($sql) === FALSE)
        echo mysql_fatal_error("Error creating database: " . $conn->error); // Check if database was created successfully
    $conn->close(); // Close the connection
}