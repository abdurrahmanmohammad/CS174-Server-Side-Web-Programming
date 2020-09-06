<?php
class SimpleSubstitution {
	static private function SimpleSubstitutionCipher($input, $alphabet, $isEncrypt) {
		if (strlen($alphabet) != 26) return null; // Alphabet length = 26 & doesn't have to be a-z
		if ($isEncrypt) {
			$oldAlphabet = "abcdefghijklmnopqrstuvwxyz";
			$newAlphabet = $alphabet;
		} else {
			$oldAlphabet = $alphabet;
			$newAlphabet = "abcdefghijklmnopqrstuvwxyz";
		}
		$match = Array(); // Array with the old alphanet mapped to the new alphabet
		for($i = 0; $i < 26; $i++)
			$match[strval($oldAlphabet[$i])] = strval($newAlphabet[$i]); // Match the alphabets
		$output = "";
		for($i = 0; $i < strlen($input); $i++)
			if (array_key_exists($input[$i], $match)) // Check if key (char in string) exists (in map)
			$output .= $match[$input[$i]]; // If $input [$i] is in array, map char to new alphabet
			else $output .= strval($input[$i]); // If $input[$i] is not in array, append it to output
		return $output;
	}
	static function encrypt($input, $cipherAlphabet) {
		return self::SimpleSubstitutionCipher($input, $cipherAlphabet, true);
	}
	static function decrypt($input, $cipherAlphabet) {
		return self::SimpleSubstitutionCipher($input, $cipherAlphabet, false);
	}
}

// Test code:
/*
 * $x = SimpleSubstitution::encrypt ( "fourscoreandsevenyearsago", "defghijklmnopqrstuvwxyzabc" ) . "<br>";
 * echo $x; // irxuvfruhdqgvhyhqbhduvdjr
 * echo SimpleSubstitution::decrypt ( $x, "defghijklmnopqrstuvwxyzabc" );
 */
?>