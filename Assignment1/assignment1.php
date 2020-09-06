<?php

/** Given a numerical parameter in input, computes and prints all the prime numbers up to that value. */
function prime_function($number)
{
    $output = ""; // This is the output string
    if ($number < 0)
        $output = "Negative input!"; // Edge cases
    elseif ($number == 0 || $number == 1)
        $output = ""; // 0 and 1 are not prime. Return an empty line.
    elseif ($number > 1) { // Valid only if you pass in a number greater than 1
        for ($i = 2; $i <= $number; $i ++) { // Check all numbers below $number
            $isPrime = true; // Initially, set this flag to true
            for ($j = 2; $j < $i; $j ++)
                if ($i % $j == 0)
                    $isPrime = false;
            if ($isPrime == true)
                $output .= "$i "; // Print prime numbers
        }
    }
    echo "Printing prime numbers from 0 to $number: $output <br>";
    return $output;
}

function tester_function()
{
    // Test 1
    if (prime_function(0) == "")
        echo "Test 1: Passed <br><br>";
    else
        echo "Test 1: Failed <br><br>";
    // Test 2
    if (prime_function(1) == "")
        echo "Test 2: Passed <br><br>";
    else
        echo "Test 2: Failed <br><br>";
    // Test 3: prime_function(10) == "2 3 5 7 "
    if (prime_function(10) == "2 3 5 7 ")
        echo "Test 3: Passed <br><br>";
    else
        echo "Test 3: Failed <br><br>";
    // Test 4
    if (prime_function(50) == "2 3 5 7 11 13 17 19 23 29 31 37 41 43 47 ")
        echo "Test 4: Passed <br><br>";
    else
        echo "Test 4: Failed <br><br>";
    // Test 5
    if (prime_function(100) == "2 3 5 7 11 13 17 19 23 29 31 37 41 43 47 53 59 61 67 71 73 79 83 89 97 ")
        echo "Test 5: Passed <br><br>";
    else
        echo "Test 5: Failed <br><br>";
    // Test 6
    if (prime_function(200) == "2 3 5 7 11 13 17 19 23 29 31 37 41 43 47 53 59 61 67 71 73 79 83 89 97 101 103 107 109 113 127 131 137 139 149 151 157 163 167 173 179 181 191 193 197 199 ")
        echo "Test 6: Passed <br><br>";
    else
        echo "Test 6: Failed <br><br>";
    // Test 7: Empty string
    if (prime_function("") == "") // Empty string
        echo "Test 7: Passed <br><br>";
    else
        echo "Test 7: Failed <br><br>";
    // Test 8: String
    if (prime_function("Hello!") == "") // string
        echo "Test 8: Passed <br><br>";
    else
        echo "Test 8: Failed <br><br>";
    // Test 9: Negative input
    if (prime_function(- 15) == "Negative input!")
        echo "Test 9: Passed <br><br>";
    else
        echo "Test 9: Failed <br><br>";
    // Test 10: Character
    if (prime_function('a') == "") // char
        echo "Test 10: Passed <br><br>";
    else
        echo "Test 10: Failed <br><br>";
}
tester_function();
?>

