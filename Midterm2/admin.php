<?php
// Note: username "admin" and password "CS174Midterm2"
// #################### Step 1: Import login.php and additional methods ####################
require_once 'login.php'; // Import database credentials
require_once 'databaseMethods.php'; // Load methods in other file
$conn = new mysqli($hn, $un, $pw, $db); // Create a connection to the database

// #################### Step 2: Authenticate admin ####################
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    $username = sanitizeMySQL($conn, $_SERVER['PHP_AUTH_USER']); // Sanitize username
    $password = sanitizeMySQL($conn, $_SERVER['PHP_AUTH_PW']); // Sanitize password
    checkPassword($conn, $username, $password); // Kills the program for incorrect user and password
} else {
    header('WWW-Authenticate: Basic realm="Restricted Section"');
    header('HTTP/1.0 401 Unauthorized');
    die("Please enter your username and password");
}
// #################### Step 3: Print out webpage ####################
echo <<<_END
<form action="admin.php" method="post" enctype='multipart/form-data'><pre>
Name <input type="text" name="Name">
Select File <input type='file' name='filename' size='10'>
<input type="submit" value="ADD MALWARE">
</pre></form>
_END;
// #################### Step 4: Allow admin to upload malware file ####################
if (isset($_POST['Name']) && $_FILES['filename'])
    uploadMalware($conn, "Malwares");
$conn->close();

// #################### Methods ####################
/**
 * Authenticates user by checking if user exists and if the password is correct
 * Lets authenticate an Admin.
 */
function checkPassword($conn, $username, $password)
{
    $query = "SELECT * FROM users WHERE username='$username'"; // Attempt to retrieve user
    $result = $conn->query($query);
    if (! $result)
        die($conn->error);
    elseif ($result->num_rows) { // If user exists
        $row = $result->fetch_array(MYSQLI_NUM);
        $result->close();
        $salt = $row[2];
        $token = hash('sha256', "$salt$password");
        if ($token == $row[1]) // Check password
            echo "Welcome User: " . $username;
        else
            die("Invalid username/password combination"); // Incorrect password
    } else
        die("Invalid username/password combination"); // Incorrect username
}

/**
 * Allows admin to upload malware to malware DB
 * Allows admin to submit a Malware file, plus the name of the uploaded Malware
 */
function uploadMalware($conn, $table)
{
    $name = sanitizeMySQL($conn, get_post($conn, 'Name')); // Get malware name from user and sanitize name
    $signature = sanitizeMySQL($conn, getMalwareSignature($_FILES['filename']['tmp_name'])); // Read the file, extract its contents, and sanitize
    $stmt = $conn->prepare("INSERT INTO {$table} VALUES(?,?)"); // Placeholders for further sanitization
    $stmt->bind_param('ss', $name, $signature);
    $stmt->execute();
    if ($stmt->affected_rows == 1)
        echo "Malware uploaded!"; // Print confirmation message
    else
        die("Failed to upload!"); // Close the program if user misbehaves
    $stmt->close();
}

/**
 * Gets first 20 bytes/chars of malware file
 */
function getMalwareSignature($fname)
{
    $fh = fopen($fname, 'r') or die(mysql_fatal_error("Cannot open file!")); // Try to access the file. If not, quit.
    $text = fread($fh, 20); // Read 20 chars
    fclose($fh); // Close the file
    return $text; // Return extracted text
}
