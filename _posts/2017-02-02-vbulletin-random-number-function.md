---
layout: post
title: "vBulletin random number function"
thumbnail: TODO-240.jpg
date: 2017-02-02
---


/**
* vBulletin's own random number generator
*
* @param	integer	Minimum desired value
* @param	integer	Maximum desired value
* @param	mixed Param is not used.
*/
function vbrand($min = 0, $max = 0, $seed = -1)
{
	mt_srand(crc32(microtime()));

	if ($max AND $max <= mt_getrandmax())
	{
		$number = mt_rand($min, $max);
	}
	else
	{
		$number = mt_rand();
	}
	// reseed so any calls outside this function don't get the second number
	mt_srand();

	return $number;
}

vbrand is used in fetch_random_password and in generateUserSecret


On Cygwin:
<?php
include('functions.php');
for ($i = 0; $i < 100; $i++) {
    echo fetch_random_password(32) . "\n";
    }
    
uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
uuuuuuuuuuuuuujjjjjjjjjjjjjj6jjj
jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
