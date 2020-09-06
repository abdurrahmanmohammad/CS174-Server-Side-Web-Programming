<?php

function uploadFile()
{
    echo <<<_END
    		<html><head><title>PHP Text File Upload</title></head><body>
    		<form method='post' action='assignment3.php' enctype='multipart/form-data'>
    			Select File: <input type='file' name='filename' size='10'>
    			<input type='submit' value='Upload'>
    		</form>
    _END;

    if ($_FILES) {
        $name = $_FILES['filename']['name']; // Get filename
        if ($_FILES['filename']['type'] == 'text/plain') { // Validate file
            $name = strtolower(preg_replace("[^A-Za-z0-9.]", "", $name)); // Santizie the name
            move_uploaded_file($_FILES['filename']['tmp_name'], $name); // Move uploaded file
            echo "Uploaded text file '$name' as '$name':<br>"; // Inform user of name change
        } else
            echo "'$name' is not an accepted text file!<br>"; // If file is not .txt
    } else
        echo "No file has been uploaded!" . "<br>"; // If no file has been uploaded
    echo "</body></html>";
    return $name; // Return the filename
}

function validateFile($fname)
{
    $fh = fopen($fname, 'r') or die("File does not exist or you lack permission to open it!"); // Try to access the file
    while (! feof($fh)) // While not the end of file
        $text .= fgets($fh); // Append $text with each line of input
    fclose($fh); // Close the file
    $text = preg_replace("/[^0-9]/", "", $text); // Santize text to just numbers. Remove all other chars.
    echo "Extracted text: " . $text . "<br>"; // Print extracted numbers
    echo "String Length:" . strlen($text) . "<br>"; // Print the input file length
    if (strlen($text) == 1000) // Make sure file has 1000 numbers
        return $text; // Return the file content if file has 1000 numbers
    else {
        echo "File is not formatted correctly"; // Print error message if not 100 numbers
        return - 1; // Return an error int
    }
}

function largestProduct($text)
{
    $text = strval($text); // Converts $text to string
    if (strlen($text) < 5 || ! preg_match("/^[0-9]*$/", $text))
        return - 1; // Check: Reject strings not purely numbers and whose length is less than 5
    $num1 = $text[0]; // 1st number
    $num2 = $text[1]; // 2nd number
    $num3 = $text[2]; // 3rd number
    $num4 = $text[3]; // 4th number
    $num5 = $text[4]; // 5th number
    $max = $num1 * $num2 * $num3 * $num4 * $num5;
    for ($i = 9; $i < strlen($text); $i ++) { // Start loop from index 9
        $temp = $text[$i] * $text[$i - 1] * $text[$i - 2] * $text[$i - 3] * $text[$i - 4];
        if ($max < $temp) {
            $num1 = $text[$i - 4];
            $num2 = $text[$i - 3];
            $num3 = $text[$i - 2];
            $num4 = $text[$i - 1];
            $num5 = $text[$i];
            $max = $temp;
        }
    }
    echo "Five adjacent numbers that multiplied together give the largest product: " . $num1 . $num2 . $num3 . $num4 . $num5 . "<br>";
    echo "The product is: " . $max . "<br>";
    return $max; // Return the product of these 5 numbers
}

function factorial($num)
{
    if ($num < 0 || ! preg_match("/^[0-9]*$/", $num))
        return - 1; // Check: reject negative integers and strings that contain chars other than numbers
    $factorial = 1; // Initial val of factorial is 1 (0! = 1)
    for ($i = 1; $i <= $num; $i ++)
        $factorial *= $i; // 1 * ... * ($i -1) * $i
    return $factorial; // Return the factorial of $num
}

function sumOfFactorials($num)
{
    $num = strval($num); // Converts $num to string
    if ($num < 0 || ! preg_match("/^[0-9]*$/", $num))
        return - 1; // Check: reject negative integers and strings that contain chars other than numbers
    $sum = 0; // Records the sum of the factorial of each digit
    for ($i = 0; $i < strlen($num); $i ++) // Traverse each number in string
        $sum += factorial($num[$i]); // Calculate the factorial of each number and add it to $sum
    echo "The sum of the factorial: " . $sum . "<br>";
    return $sum; // Return the sum of the factorial of each term of such product
}

function testerFunction() // Test all other function but upload
{
    echo "Test 1: <br>";
    $filename = "test.txt";
    $text = validateFile($filename);
    if ($text != - 1 && 40371 == sumOfFactorials(largestProduct($text)))
        echo "Test 1: Passed<br>";
    echo "<br>Test 2: <br>";
    $filename = "test2.txt";
    $text = validateFile($filename);
    // 7!+2!+5!+9!+0!+5! = 725905
    if ($text != - 1 && 725905 == sumOfFactorials(largestProduct($text)))
        echo "Test 2: Passed<br>";
    echo "<br>Test 3: <br>";
    $filename = "test3.txt";
    $text = validateFile($filename);
    // 5!+2!+4!+8!+8! = 80786
    if ($text != - 1 && 80786 == sumOfFactorials(largestProduct($text)))
        echo "Test 3: Passed<br><br><br>";
}
testerFunction();
// Test upload function as well
sumOfFactorials(largestProduct(validateFile(uploadFile())));

?>
