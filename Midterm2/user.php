<?php
// #################### Step 1: Import login.php and additional methods ####################
require_once 'login.php';
require_once 'databaseMethods.php'; // Load methods in other file
$conn = new mysqli($hn, $un, $pw, $db); // Create a connection to the database

// #################### Step 2: Print out webpagn ####################
echo <<<_END
<form action="user.php" method="post" enctype='multipart/form-data'><pre>
Select Putative Infected File <input type='file' name='filename' size='10'>
<input type="submit" value="CHECK">
</pre></form>
_END;
// #################### Step 3: Allow user to upload putative file ####################
if ($_FILES['filename']) // Check if file is uploaded
    checkMalware($conn, "Malwares"); // Check for malware in table "Malwares"

$conn->close();

// #################### Methods ####################
/**
 * Allows the user to submit a putative infected file and shows if it is infected or not
 * Searches files less than 20 chars also (malware exists only if malware is less than 20 chars)
 */
function checkMalware($conn, $name)
{
    // Read the file, extract its contents, and sanitize text
    $content = sanitizeMySQL($conn, getPutativeFile($_FILES['filename']['tmp_name']));
    $isMalware = false; // Flag for the loop
    for ($i = 0; $i < strlen($content) && $isMalware == false; $i ++)
        $isMalware = searchMalware($conn, $name, substr($content, $i, 20)); // Search 20 chars at a time
    if ($isMalware)
        echo "File infected with malware!<br>";
    else
        echo "No malware detected!<br>";
}

function getPutativeFile($fname)
{
    $fh = fopen($fname, 'r') or die(mysql_fatal_error("Cannot open file!")); // Try to access the file. If not, quit.
    while (! feof($fh))
        $text .= fgets($fh); // While not the end of file, append $text with each line of input
    fclose($fh); // Close the file
    return $text; // Return extracted text
}

function searchMalware($conn, $table, $string)
{
    $query = "SELECT * FROM {$table} WHERE Signature='$string'"; // Query command to execute
    $result = $conn->query($query); // Attempt to retrieve record by executing query command
    if (! $result)
        die($conn->error);
    elseif ($result->num_rows) {
        $row = $result->fetch_array(MYSQLI_NUM);
        $result->close();
        echo "Malware detected: " . $row[1] . "<br>"; // Print confirmation of malware and malware name
        return true;
    }
    return false;
}