<?php
define(TABLE, "TextFileData"); // Define the name of the table as a constant
require_once 'login.php'; // Load the user login
require_once 'databaseMethods.php'; // Load methods in other file
buildDB(new mysqli($hn, $un, $pw), $db); // Connect to MySQL and build the database if doesn't exist
$conn = new mysqli($hn, $un, $pw, $db); // Create a connection
buildTable($conn, TABLE); // Build the table if not have already done so
if ($conn->connect_error) // Check connection
    die(mysql_fatal_error($conn->connect_error));

if (isset($_POST['delete']) && isset($_POST['Name']))
    delete($conn, TABLE);

if (isset($_POST['Name']) && $_FILES['filename'])
    if ($_FILES['filename']['type'] == 'text/plain') { // Validate file (must be a text file)
        insert($conn, TABLE);
    } else
        die(mysql_fatal_error("Invalid file type!")); // If file does not exist in the system

echo <<<_END
<form action="assignment5.php" method="post" enctype='multipart/form-data'><pre>
Name <input type="text" name="Name">
Select File <input type='file' name='filename' size='10'>
<input type="submit" value="ADD RECORD">
</pre></form>
_END;

printQuery($conn, TABLE);
// $result->close();

$conn->close();





