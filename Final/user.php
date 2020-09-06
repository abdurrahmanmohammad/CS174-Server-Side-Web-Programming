<?php
// ########## Import login.php, additional methods, & test connection to DB ##########
require_once 'login.php'; // Import database credentials
require_once 'genericMethods.php'; // Load methods in other file
require_once 'SimpleSubstitution.php'; // Load SimpleSubstitution encypt decrypt methods
require_once 'DoubleTransposition.php'; // Load DoubleTransposition encypt decrypt methods
require_once 'RC4.php'; // Load RC4 encypt decrypt methods
$conn = new mysqli($hn, $un, $pw, $db); // Create a connection to the database
if ($conn->connect_error) die(mysql_fatal_error($conn->connect_error)); // Test connection: Error not printed (for debugging purposes only)
$UserTable = "Users"; // Name of table of users
$CipherTable = "Ciphers"; // Name of table to store input
$username = ""; // Unitilizalized (will be initialized in session)
/* Start Session / Authenticate user */
session_start(); // Start session
if (isset($_POST['LogOut'])) destroy_session_and_data(); // Logs out user (if click log out button)

/* ##### Preventing session fixation ##### */
if (! isset($_SESSION['initiated'])) {
	session_regenerate_id();
	$_SESSION['initiated'] = 1;
}
if (! isset($_SESSION['count'])) $_SESSION['count'] = 0;
else ++$_SESSION['count'];

/* Initialize session */
if (isset($_SESSION['username'])) {
	/* ##### Preventing session hijacking ##### */
	if ($_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) different_user();
	$username = $_SESSION['username']; /* Only need username for this session */
	// $password = $_SESSION['password']; // Don't need on this page
	// $email = $_SESSION['email']; // Don't need on this page
	// destroy_session_and_data(); // Page refreshes with every insert and deletion
	echo "Welcome back $username!"; // Welcome user
	/* Log out button */
	echo <<<_END
	<form action="user.php" method="post" enctype="multipart/form-data"><pre>
	<input type="submit" name="LogOut" value="Log Out">
	</form>
	_END;

	/* Print webpage */
	printWebpage($username);

	/* Retrieve and sanitize input & determine whether to encrypt or decrypt */
	$input = ""; // Input to encrypt/decrypt from file or textbox
	$isEncrypt = true; // Initialize flag to true
	if (isset($_POST['TextboxEncrypt']) && isset($_POST['textbox']) != 0 && isset($_POST['Key1'])) { // If textbox and key1 are set
		$isEncrypt = true; // We are encrypting
		$input = sanitizeMySQL($conn, $_POST['textbox']); // Retrieve, sanitize, & store input
	}
	if (isset($_POST['TextboxDecrypt']) && isset($_POST['textbox']) != 0 && isset($_POST['Key1'])) { // If textbox and key1 are set
		$isEncrypt = false; // We are decrypting
		$input = sanitizeMySQL($conn, $_POST['textbox']); // Retrieve, sanitize, & store input
	}
	// ########## Validate files, read files, sanitize content ##########
	if (isset($_POST['FileEncrypt']) && isset($_FILES['filename']) && isset($_POST['Key1'])) {
		$isEncrypt = true;
		// Validate file (must be a text file), read file, extract contents, sanitize
		if ($_FILES['filename']['type'] == 'text/plain') $input = sanitizeMySQL($conn, readNparse($_FILES['filename']['tmp_name']));
		else echo "Invalid file type (must by txt)"."<br>";
	}
	if (isset($_POST['FileDecrypt']) && $_FILES['filename'] && isset($_POST['Key1'])) {
		$isEncrypt = false;
		// Validate file (must be a text file), read file, extract contents, sanitize
		if ($_FILES['filename']['type'] == 'text/plain') $input = sanitizeMySQL($conn, readNparse($_FILES['filename']['tmp_name']));
		else echo "Invalid file type (must by txt)"."<br>";
	}
	// ########## Print results ##########
	echo "Input: $input"."<br>"; // Print input
	$key1 = sanitizeMySQL($conn, get_post($conn, 'Key1')); // Extract key1
	if ($key1 == '') echo "Key1: cannot be empty"."<br>";
	else echo "Key1: $key1"."<br>"; // Print key1
	                                // ########## Determine which cipher to use ##########
	$key2 = "";
	$cipher = "";
	$output = ""; // Output for users
	if ($_POST['ciphers'] == 'Simple Substitution' && ! ($key1 == '')) {
		if (strlen($key1) != 26) echo "Warning: Key must be 26 characters"."<br>"; // Returns null if not 26 chars
		echo "Output: ".$output = ($isEncrypt) ? SimpleSubstitution::encrypt($input, $key1) : SimpleSubstitution::decrypt($input, $key1);
		$cipher = "Simple Substitution";
		saveOutput($conn, $CipherTable, $username, $input, $output, $cipher, $key1, $key2); // Save output and other data
	}
	if ($_POST['ciphers'] == 'Double Transposition' && isset($_POST['Key2']) && ! ($key1 == '')) {
		$key2 = sanitizeMySQL($conn, get_post($conn, 'Key2'));
		if ($key2 == '') {
			echo "Key2: cannot be empty"."<br>";
			return; // Allow user to try again
		}
		echo "Key2: $key2"."<br>"; // Print key2
		echo "Output: ".$output = ($isEncrypt) ? DoubleTransposition::encrypt($input, $key1, $key2) : DoubleTransposition::decrypt($input, $key1, $key2);
		$cipher = "Double Transposition";
		saveOutput($conn, $CipherTable, $username, $input, $output, $cipher, $key1, $key2); // Save output and other data
	}
	if ($_POST['ciphers'] == 'RC4' && ! ($key1 == '')) {
		echo "Output: ".$output = ($isEncrypt) ? RC4::encrypt($input, $key1) : RC4::decrypt($input, $key1);
		$cipher = "RC4";
		saveOutput($conn, $CipherTable, $username, $input, $output, $cipher, $key1, $key2); // Save output and other data
	}
	if (isset($_POST['delete']) && isset($_POST['IDs'])) delete($conn, $CipherTable); // Delete entries if user wishes
	printQuery($conn, $CipherTable, $username); // Print out saved entries
	$conn->close(); // Close connection
} else {
	echo "Please <a href='authenticate.php'>click here</a> to log in.<br>";
	echo "Please <a href='main.php'>click here</a> to go back to the main page.";
}