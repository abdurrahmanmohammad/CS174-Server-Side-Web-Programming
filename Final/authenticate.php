<?php
/* Initial setup */
require_once 'login.php';
require_once 'genericMethods.php'; // Load methods in other file
$conn = new mysqli($hn, $un, $pw, $db);
$table = "Users";
/* ################################################## */
if ($conn->connect_error) die($conn->connect_error); // Test connection: Specific error not printed to user.
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
	$un_temp = mysql_entities_fix_string($conn, $_SERVER['PHP_AUTH_USER']);
	$pw_temp = mysql_entities_fix_string($conn, $_SERVER['PHP_AUTH_PW']);
	checkPassword($conn, $table, $un_temp, $pw_temp);
} else {
	header('WWW-Authenticate: Basic realm="Restricted Section"');
	header('HTTP/1.0 401 Unauthorized');
	die("Please enter your username and password");
}
$conn->close(); // Close connection
/* ################################################## */
function checkPassword($conn, $table, $un_temp, $pw_temp) {
	$query = "SELECT * FROM $table WHERE username='$un_temp'";
	$result = $conn->query($query);
	if (! $result) die($conn->error);
	elseif ($result->num_rows) { // If user exists
		$row = $result->fetch_array(MYSQLI_NUM);
		$result->close();
		if (password_verify($pw_temp, $row[1])) { // Check password
			session_start();
			ini_set('session.gc_maxlifetime', 60 * 60 * 24); // ##### Setting a Time-Out for cookie #####
			$_SESSION['username'] = $un_temp;
			$_SESSION['password'] = $pw_temp;
			$_SESSION['email'] = $row[2];
			/* Additional Checks: Preventing session hijacking */
			$_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
			echo "Hi $row[0], you are now logged in!";
			die("<p><a href=user.php>Click here to continue</a></p>");
		} else
			die("Invalid username/password combination"); // Incorrect password
	} else
		die("Invalid username/password combination"); // Incorrect username
}