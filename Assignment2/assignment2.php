<?php

/**
 * @author Abdurrahman Mohammad in collabortion with Henry Fan
 */

// Converts a roman numeral character to its corresponding int value
// After validating the input, convert the roman numeral char to decimal
function getRomanVal($romanChar)
{
    $mapRoman["I"] = 1;
    $mapRoman["V"] = 5;
    $mapRoman["X"] = 10;
    $mapRoman["L"] = 50;
    $mapRoman["C"] = 100;
    $mapRoman["D"] = 500;
    $mapRoman["M"] = 1000;
    return (is_string($romanChar) && preg_match("/^(?=[MDCLXVI])/", $romanChar)) ? $mapRoman[$romanChar] : NULL;
}

// Computes the modern Hindu–Arabic numeral of Roman numerals
// Regex src: https://www.oreilly.com/library/view/regular-expressions-cookbook/9780596802837/ch06s09.html
// Function Logic:
// Case 1: If current char is not last char (there is a next char): !($nextValue == -1) == true [-1 means current char is last char]
// --> Case 1.1: If next char is > than current char (ex. IV), subtract it from result. Next char will be added in next iteration.
// --> Case 1.2: If next char is <= than current char (ex. VI), add current char. Next char will be added in next iteration.
// Case 2: If there is no next char, current char is last char: !($nextValue == -1) == false. Add current char to result.
function convertRoman($romanNum)
{
    if (! (is_string($romanNum) && preg_match("/^(?=[MDCLXVI])M*(C[MD]|D?C*)(X[CL]|L?X*)(I[XV]|V?I*)$/", $romanNum)))
        return NULL; // Check to see if input is valid
    $sum = 0; // Initialize result to 0
    for ($i = 0; $i < strlen($romanNum); $i ++) { // Get the value of ith char (and (i+1)th char if needed (ex. IV))
        $char1 = getRomanVal($romanNum[$i]); // Get the value of the ith char
        $char2 = ($i + 1 >= strlen($romanNum)) ? - 1 : getRomanVal($romanNum[$i + 1]); // Get the val of (i+1)th if not end of string
        $sum += (! ($char2 == - 1) && $char2 > $char1) ? - $char1 : $char1; // Refer to function logic
    }
    return $sum; // Return the converted Roman numeral
}
// Tests convertRoman. First 3 test cases should return null (invalid input).
function testConvertRoman()
{
    $testCases = array(5, "Hello", "ICVDXM", "VI", "IV", "MCMXC", "IX", "", "I", "III", "V", "M", "C", "MCXX", "DXCIII");
    $expectedOutput = array(NULL, NULL, NULL, 6, 4, 1990, 9, 0, 1, 3, 5, 1000, 100, 1120, 593);
    for ($i = 0; $i < 15; $i ++)
        if (convertRoman($testCases[$i]) == $expectedOutput[$i])
            echo "Test " . ($i + 1) . ": [Passed] " . " => " . $testCases[$i] . " = " . $expectedOutput[$i] . "<br>";
}
testConvertRoman(); // Test the program
?>