<?php
// Converted pseudocode code on CS 166 slides to PHP
class RC4 {
	static private function RC4Cipher($key, $plainText, $isEncrypt) {
		$s = array(); // S[] is permutation of 0,1,...,255
		for($i = 0; $i < 256; $i++) // S[] is permutation of 0,1,...,255
			$s[$i] = $i;
		$k = array(); // key[] contains N bytes of key
		for($i = 0; $i < 256; $i++) // ord — Convert the first byte of a string to a value between 0 and 255
			$k[$i] = ord($key[$i % strlen($key)]); // K[i] = key[i (mod N)]
		for($i = 0, $j = 0; $i < 256; $i++) {
			$j = ($j + $s[$i] + $k[$i]) % 256; // j = (j + S[i] + K[i]) mod 256
			$temp = $s[$i]; // swap(S[i], S[j])
			$s[$i] = $s[$j]; // swap(S[i], S[j])
			$s[$j] = $temp; // swap(S[i], S[j])
		}
		$output = ""; // Stores the output
		for($i = 0, $j = 0, $y = 0; $y < strlen($plainText); $y++) { // Generate key stream
			$i = ($i + 1) % 256; // i = (i + 1) mod 256
			$j = ($j + $s[$i]) % 256; // j = (j + S[i]) mod 256
			$temp = $s[$i]; // swap(S[i], S[j])
			$s[$i] = $s[$j]; // swap(S[i], S[j])
			$s[$j] = $temp; // swap(S[i], S[j])
			$t = ($s[$i] + $s[$j]) % 256; // t = (S[i] + S[j]) mod 256
			$output .= $plainText[$y] ^ chr($s[$t]); // keystreamByte = S[t]
		}
		return $output;
	}
	static function encrypt($key, $plainText) {
		return self::RC4Cipher($key, $plainText, true);
	}
	static function decrypt($key, $plainText) {
		return self::RC4Cipher($key, $plainText, false);
	}
}
?>