<?php
/* Import login.php, additional methods, & test connection to DB */
require_once 'login.php'; // Import database credentials
require_once 'genericMethods.php'; // Load methods in other file
$conn = new mysqli($hn, $un, $pw, $db); // Create a connection to the database
if ($conn->connect_error) die(mysql_fatal_error($conn->connect_error)); // Test connection: $conn->connect_error will not be printed (for debugging purposes only)
$table = "Users"; // Name of table to store users

/* ################################################## */
/* Actual Program */
printCreateUser();
printResetPassword();
/* Define function for button clicks: create user & reset password */
if (isset($_POST['UsernameCreate']) && isset($_POST['PasswordCreate']) && isset($_POST['EmailCreate'])) createUser($conn, $table); // Create user
if (isset($_POST['UsernameReset']) && isset($_POST['PasswordReset']) && isset($_POST['NewPasswordReset']) && isset($_POST['EmailReset'])) resetPassword($conn, $table); // Reset password
echo <<<_END
<form action="main.php" method="post" enctype="multipart/form-data"><pre>
<input type="submit" name="MainPage" value="Main Page">
</form>
_END;
$conn->close(); // Close the connection before exiting
/* ################################################## */

/**
 * Creates a user
 */
function createUser($conn, $table) {
	/* Access fields and sanitize */
	$username = sanitizeMySQL($conn, get_post($conn, 'UsernameCreate')); // Get username & sanitize
	$password = sanitizeMySQL($conn, get_post($conn, 'PasswordCreate')); // Get password & sanitize
	$email = sanitizeMySQL($conn, get_post($conn, 'EmailCreate')); // Get email & sanitize
	/* Validate Input */
	if (checkUser($conn, $table, $username)) { // Check if username exists in DB
		echo "Username taken!"."<br>";
		return; // Return to main program. Allow user to try again
	}
	if (! validateInputs($username, $password, $email)) return; // Exit if input is invalid
	/* Encrypt password */
	$password = password_hash($password, PASSWORD_BCRYPT); // Salt the password with a random salt and hash (60 chars)
	/* Create account & store in DB */
	$stmt = $conn->prepare("INSERT INTO $table VALUES(?,?,?)"); // Placeholders for further sanitization
	$stmt->bind_param('sss', $username, $password, $email); // Prepare statement
	$stmt->execute(); // Execute statement
	if ($stmt->affected_rows == 1) echo "User created!"; // Print confirmation message
	else die(mysql_fatal_error("Failed to create user!")); // Close the program if error (from DB side). Error not printed (for debugging).
	$stmt->close(); // Close the statement
}
/**
 * Validate inpt fields
 */
function validateInputs($username, $password, $email) {
	if ($username == '' || $password == '' || $email == '') {
		echo "Blank fields!"."<br>";
		return false; // Return to main program. Allow user to try again
	}
	if (! preg_match("/^[a-zA-Z0-9-_]+$/", $username)) {
		// The username can contain English letters (capitalized or not), digits, and the characters '_' (underscore) and '-' (dash). Nothing else.
		echo "Invalid username format!"."<br>";
		return false; // Return to main program. Allow user to try again
	}
	if (strlen($username) > 20) { // Check username length
		echo "Username too long!"."<br>";
		return false; // Return to main program. Allow user to try again
	}
	if (strlen($email) > 60) { // Check email length
		echo "Email too long!"."<br>";
		return false; // Return to main program. Allow user to try again
	}
	if (! filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate email
		echo "Invalid email!"."<br>";
		return false; // Return to main program. Allow user to try again.
	}
	return true; // If all conditions are met
}

/**
 * Checks if a username exists in a table
 */
function checkUser($conn, $table, $username) {
	$query = "SELECT * FROM $table WHERE Username='$username'"; // Query command to execute
	$result = $conn->query($query); // Attempt to retrieve record by executing query command
	if (! $result) die(mysql_fatal_error("Database access failed: ".$conn->error));
	/* Execute below if no DB error. DB errors are not printed to the user. */
	$row = $result->fetch_array(MYSQLI_NUM); // Convert it into an associative array
	$result->close(); // Close result
	if ($row[0] == '') return false; // If query is empty, user does not exist
	else return true; // User exists
}
/**
 * Resets an account's password
 */
function resetPassword($conn, $table) {
	/* Access fields and sanitize */
	$username = sanitizeMySQL($conn, get_post($conn, 'UsernameReset')); // Get username & sanitize
	$password = sanitizeMySQL($conn, get_post($conn, 'PasswordReset')); // Get password & sanitize
	$email = sanitizeMySQL($conn, get_post($conn, 'EmailReset')); // Get email & sanitize
	/* Attempt to query user */
	$query = "SELECT * FROM $table WHERE Username='$username'"; // Query command to execute
	$result = $conn->query($query); // Attempt to retrieve record by executing query command
	if (! $result) die(mysql_fatal_error("Database access failed: ".$conn->error));
	/* Execute below if no DB error. DB errors are not printed to the user. */
	$row = $result->fetch_array(MYSQLI_NUM); // Convert it into an associative array
	$result->close(); // Close result
	/* Check if inputs match table data */
	if ($row[0] != $username || ! password_verify($password, $row[1]) || $row[2] != $email) {
		echo "Password cannot be reset!";
		return;
	}
	/* If username, passwod, & email match, reset password */
	$newPassword = sanitizeMySQL($conn, get_post($conn, 'NewPasswordReset')); // Get new password & sanitize
	$newPassword = password_hash($newPassword, PASSWORD_BCRYPT); // Salt the password with a random salt and hash (60 chars)
	$query = "Update $table SET Password='$newPassword' WHERE Username='$username'"; // Query command to execute
	if ($conn->query($query) === true) echo "Password reset!"; // Execute query and reset password
	else die(mysql_fatal_error("Error updating record: ".$conn->error)); // Error not printed to user
}

/**
 * Prints webpage to create user
 */
function printCreateUser() {
	echo <<<_END
	<form action="createUser.php" method="post" enctype='multipart/form-data'>
	<pre>
	Create User
	
	Username <input type="text" name="UsernameCreate">
	Password <input type="text" name="PasswordCreate">
	Email <input type="text" name="EmailCreate">
	<input type="submit" value="Create">
	
	
	</pre>
	</form>
	_END;
}
/**
 * Prints webpage to reset user password
 */
function printResetPassword() {
	echo <<<_END
	<form action="createUser.php" method="post" enctype='multipart/form-data'>
	<pre>
	Reset Password
	
	Username <input type="text" name="UsernameReset">
	Password <input type="text" name="PasswordReset">
	New Password <input type="text" name="NewPasswordReset">
	Email <input type="text" name="EmailReset">
	<input type="submit" value="Reset">
	</pre>
	</form>
	_END;
}