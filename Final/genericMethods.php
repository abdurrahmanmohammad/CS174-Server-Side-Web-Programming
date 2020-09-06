<?php
// ******************** Delete Session Method ********************
/* Function to destroy session */
function destroy_session_and_data() {
	$_SESSION = array();
	setcookie(session_name(), '', time() - 2592000, '/');
	session_destroy();
}
// ******************** Delete Method ********************
/**
 * Function to delete a record
 */
function delete($conn, $table) {
	$id = get_post($conn, 'IDs'); // Sanitize just in case
	$query = "DELETE FROM $table WHERE id='$id'"; // Make query to delete row with a specific ID
	                                              // $result = $conn->query($query); // Execute query and record result
	if (! $conn->query($query)) die(mysql_fatal_error("Failed to delete: $query<br>".$conn->error."<br><br>")); // Error not printed (for debugging)
}

// ******************** Print Method ********************
/**
 * Function to print table located in MySQL DB
 */
function printQuery($conn, $table, $username) {
	$query = "SELECT * FROM $table"; // Access the complete table stored in 'table'
	$result = $conn->query($query); // Extract contents from table
	if (! $result) die(mysql_fatal_error("Database access failed: ".$conn->error)); // Specific error not shown to user
	$rows = $result->num_rows; // Store the number of rows
	echo "<br><br><br>--------------------Saved Inputs--------------------<br>";
	for($j = 0; $j < $rows; ++$j) { // Check all entries in table
		$result->data_seek($j); // Get the jth row
		$row = $result->fetch_array(MYSQLI_NUM); // Convert it into an associative array
		                                         // Print rows that match username
		if ($row[1] == $username) echo <<<_END
		<pre>
		Username: $row[1]
		Cipher: $row[2]
		Input: $row[3]
		Output: $row[4]
		Key1: $row[5]
		Key2: $row[6]
		Timestamp: $row[7]</pre><form action="user.php" method="post"><input type="hidden" name="delete" value="yes"><input type="hidden" name="IDs" value="$row[0]">
		<input type="submit" value="DELETE RECORD"></form>
		
		_END;
	}
	$result->close(); // Close result after operation
}
// ******************** Print Webpage Method ********************
function printWebpage($username) {
	echo <<<_END
	<h1>Decryptoid</h1>
	<form action="user.php" method="post" enctype="multipart/form-data"><pre>
	Cipher <select name ="ciphers">
	<option value = "Simple Substitution">Simple Substitution</option>
	<option value = "Double Transposition">Double Transposition</option>
	<option value = "RC4">RC4</option>
	</select>
	Key1 <input type="text" name="Key1">
	Key2 <input type="text" name="Key2">
	Enter Text
	<textarea rows="6" cols="40" name="textbox"></textarea>
	<input type="submit" name ="TextboxEncrypt" value="Encrypt"/>
	<input type="submit" name ="TextboxDecrypt" value="Decrypt"/>
	
	Upload File <input type='file' name='filename' size='10'>
	<input type="hidden" name="Username" value="$username">
	<input type="submit" name ="FileEncrypt" value="Encrypt"/>
	<input type="submit" name ="FileDecrypt" value="Decrypt"/>
	</pre>
	</form>
	_END;
}

// ******************** Database Save Method ********************
function saveOutput($conn, $table, $username, $input, $output, $cipher, $key1, $key2) {
	// Sanitize all fields just in case (they are already sanitized from before)
	$username = sanitizeMySQL($conn, $username);
	$input = sanitizeMySQL($conn, $input);
	$output = sanitizeMySQL($conn, $output);
	$cipher = sanitizeMySQL($conn, $cipher);
	$key1 = sanitizeMySQL($conn, $key1);
	$key2 = sanitizeMySQL($conn, $key2);
	$query = "INSERT INTO $table VALUES(NULL, '$username', '$cipher','$input', '$output',  '$key1', '$key2', NULL)"; // Use MySQL's built in timestamp function
	                                                                                                                 // $result = $conn->query($query);
	if (! $conn->query($query)) die(mysql_fatal_error("Failed to insert: $query<br>".$conn->error."<br><br>")); // Error is not printed (for debugging)
	echo "<br>"."Saved!";
}

// ******************** Error Method ********************
/**
 * Function to notify the user when error occurs (Specific errors are NOT printed)
 */
function mysql_fatal_error($error) {
	echo <<<_END
	We are sorry, but it was not possible to complete the requested task.
	Please click the back button on your browser and try again.
	Thank you.
	_END;
	// echo "<br>" . $error; // Print out the error (for debugging purposes only)
}

// ******************** Sanitization Methods ********************

/**
 * Sanitizes input string for MySQL
 */
function sanitizeMySQL($connection, $var) {
	$var = $connection->real_escape_string($var);
	$var = sanitizeString($var);
	return $var;
}
/**
 * Helper method for sanitizeMySQL
 */
function sanitizeString($var) {
	$var = stripslashes($var);
	$var = strip_tags($var);
	$var = htmlentities($var);
	return $var;
}
function mysql_entities_fix_string($connection, $string) {
	return htmlentities(mysql_fix_string($connection, $string));
}
function mysql_fix_string($connection, $string) {
	if (get_magic_quotes_gpc()) $string = stripslashes($string);
	return $connection->real_escape_string($string);
}
/**
 * Passes each item it retrieves through the real_escape_string method of the connection object to strip
 * out any characters that a hacker may have inserted in order to break into or alter your database
 */
function get_post($conn, $var) {
	return $conn->real_escape_string($_POST[$var]);
}
// ******************** File Methods ********************
/**
 * Funtion to read a file and return the stored text (somewhat sanitized, fully sanitized later)
 */
function readNparse($fname) {
	$fh = fopen($fname, 'r') or die(mysql_fatal_error("Cannot open file!")); // Try to access the file. Error not printed.
	while(! feof($fh))
		$text .= sanitizeString(fgets($fh)); // While not the end of file, append $text with each line of input
	fclose($fh); // Close the file
	return $text; // Return extracted text
}