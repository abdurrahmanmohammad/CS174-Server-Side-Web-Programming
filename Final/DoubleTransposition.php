<?php
// Do not use '_' in input string
class DoubleTransposition {
	static function encrypt($plaintext, $key1, $key2) {
		return SingleTransposition::encrypt(SingleTransposition::encrypt($plaintext, $key1), $key2);
	}
	static function decrypt($plaintext, $key1, $key2) {
		return SingleTransposition::decrypt(SingleTransposition::decrypt($plaintext, $key2), $key1);
	}
}

/*
 * SingleTransposition:
 * To Encrypt:
 * Step 1: Make 2D array of width key1.length
 * Step 2: Number each char of key1 based on alphabetical value
 * Step 3: Divide plaintext into rows of length key1.length and put in array (fill additional spaces)
 * Step 4: CipherText = columns of matrix in one line (order of columns in step 2)
 * Step 5: Repeat steps 1 to 5
 */
class SingleTransposition {
	static private function alphabetSort($first, $second) {
		return strcmp($first["Value"], $second["Value"]); // Sort by alphabetical order
	}
	static private function shiftIndices($key) {
		// Example: $key = "potato"
		$output = array(); // The shift indices
		$sortedKey = array(); // The sorted key: list of (Key, Value)
		$keyLength = strlen($key); // Key length
		for($i = 0; $i < $keyLength; $i++)
			$sortedKey[] = Array("Key" => $i,"Value" => $key[$i]); // Convert to tuple: (Key, Value)
			                                                       // $sortedKey = (0, p), (1, o), (2, t), (3, a), (4, t), (5, o)
		usort($sortedKey, array('SingleTransposition','alphabetSort')); // Sort (Key, Value) by alphabetical order of Value
		                                                                // $sortedKey = (3, a), (1, o), (5, o), (0, p), (2, t), (4, t)
		for($i = 0; $i < $keyLength; $i++)
			$output[$sortedKey[$i]["Key"]] = $i;
		// $output = (3, 0), (1, 1), (5, 2), (0, 3), (2, 4), (4, 5) // Indices are mapped
		return $output; // Return the shift indices
	}
	static function encrypt($plaintext, $key) {
		$output = ""; // Stores the output to return
		$keyLength = strlen($key); // Key length
		$plaintextLength = strlen($plaintext); // The size of the initial plaintext
		$numOfCols = strlen($key); // Number of columns = key length
		$numOfRows = ceil($plaintextLength / $numOfCols); // Number of rows = ceiling [text length / number of columns]
		$charToRows = array(array());
		$rowToCols = array(array());
		$sortedCols = array(array());
		$shiftIndexes = self::shiftIndices($key);
		// If the plaintext does not fit into the matrix perfectly, create an additonal row and padded plaintext with '_' from right
		if ($plaintextLength % $keyLength != 0) $plaintext = str_pad($plaintext, $plaintextLength - ($plaintextLength % $keyLength) + $keyLength, '_');
		$plaintextLength = strlen($plaintext); // The size of the padded plaintext
		for($i = 0; $i < $plaintextLength; $i++) // Distribute chars in rows
			$charToRows[$i / $numOfCols][$i % $numOfCols] = $plaintext[$i]; // [row][column] = plaintext[$i]
		for($i = 0; $i < $numOfRows; $i++) // Create columns from the rows
			for($j = 0; $j < $numOfCols; $j++)
				$rowToCols[$j][$i] = $charToRows[$i][$j];
		for($i = 0; $i < $numOfCols; ++$i) // Sort the columns based on alphabetical order of chars in key
			for($j = 0; $j < $numOfRows; ++$j)
				$sortedCols[$shiftIndexes[$i]][$j] = $rowToCols[$i][$j];
		for($i = 0; $i < $plaintextLength; ++$i) // Condense columns into one line output
			$output .= $sortedCols[$i / $numOfRows][$i % $numOfRows]; // output .= sortedColChars[row][col]
		return str_replace('_', '', $output); // Remove padding and return output
	}
	static function decrypt($cipher, $key) {
		$output = ""; // Stores the output to return
		$cipherLength = strlen($cipher); // The size of plaintext
		$keyLength = strlen($key); // Key length
		$numOfRows = ceil($cipherLength / $keyLength); // Number of rows = ceiling [text length / number of columns]
		$numOfCols = $keyLength; // Number of rows = key length
		$shiftIndexes = self::shiftIndices($key); // Maps current indices to encryption indices
		$matrix = array(array()); // Matrix to store the transposition cipher

		$count = $numOfRows * $numOfCols - $cipherLength; // # of spaces to black out (to prevent array issues with null)
		for($i = $numOfRows - 1; $i > 0 && $count > 0; $i--) // Traverse rows
			for($j = $numOfCols - 1; $j > 0 && $count > 0; $j--, $count--) // Traverse columns
				$matrix[$i][$j] = "_"; // Start blacking out spaces from the end of the matrix (from last position)
		for($i = 0, $count = 0; $i < $numOfCols && $count < $cipherLength; $i++) { // Fill in matrix
			$colToFill = array_search($i, $shiftIndexes); // Column to fill (get key of shiftIndex[i]
			for($j = 0; $j < $numOfRows && $count < $cipherLength; $j++) // Fill in each column (by filling in rows in column)
				if (isset($matrix[$j][$colToFill]) == false) $matrix[$j][$colToFill] = $cipher[$count++]; // Fill if not '_'
		}
		for($i = 0; $i < $numOfRows; $i++) // Condense matrix
			for($j = 0; $j < $numOfCols; $j++)
				$output .= $matrix[$i][$j];
		return str_replace('_', '', $output); // Remove padding and return output;
	}
}
// Test code
/*
 $plaintext = "Spartans are coming hide your wife and kids";
 $key1 = "potato";
 $key2 = "panda";
 // DoubleTransposition
 $ciphertext = DoubleTransposition::encrypt($plaintext, $key1, $key2);
 echo "Ciphertext:".$ciphertext."<br>"; // "akynSwot ucaagafdisd  soimpiennhrire ed   r"
 $decrypt = DoubleTransposition::decrypt($ciphertext, $key1, $key2);
 echo "Decrypt:".$decrypt."<br>";
 // SingleTransposition
 $ciphertext1 = SingleTransposition::encrypt($plaintext, $key1);
 echo "Ciphertext1:".$ciphertext1."<br>"; // "ramiuekpsc yidaene adSn g wnsa ohof tridr i"
 $ciphertext2 = SingleTransposition::encrypt($ciphertext1, $key2);
 echo "Ciphertext2:".$ciphertext2."<br>"; // "akynSwot ucaagafdisd soimpiennhrire ed r"
 
 $decrypt1 = SingleTransposition::decrypt($ciphertext2, $key2);
 echo "Decrypt1:".$decrypt1."<br>"; // "ramiuekpsc yidaene adSn g wnsa ohof tridr i"
 $decrypt2 = SingleTransposition::decrypt($decrypt1, $key1);
 echo "Decrypt2:".$decrypt2."<br>"; // "Spartans are coming hide your wife and kids"
 */



