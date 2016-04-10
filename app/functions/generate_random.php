<?php
// Generate Random Characters
function generate_random($max)
{
    $i      = 0;
    $value  = '';
    while ($i < $max)
    {
        $value .= generate_random_var(rand(1,35));
        $i++;
    }

    // Just in case
    if (empty($value))
    {
        error('Dev error: $value is not generated for generate_random()');
    }

    // Return
    return $value;
}

// Sessions are currently simple
// Update this later with more complexity
function generate_random_var($number)
{
	switch ($number) {
		case 1: $r = 'a'; break;
		case 2: $r = 'b'; break;
		case 3: $r = 'c'; break;
		case 4: $r = 'd'; break;
		case 5: $r = 'e'; break;
		case 6: $r = 'f'; break;
		case 7: $r = 'g'; break;
		case 8: $r = 'h'; break;
		case 9: $r = 'i'; break;
		case 10: $r = 'j'; break;
		case 11: $r = 'k'; break;
		case 12: $r = 'l'; break;
		case 13: $r = 'm'; break;
		case 14: $r = 'n'; break;
		case 15: $r = 'o'; break;
		case 16: $r = 'p'; break;
		case 17: $r = 'q'; break;
		case 18: $r = 'r'; break;
		case 19: $r = 's'; break;
		case 20: $r = 't'; break;
		case 21: $r = 'u'; break;
		case 22: $r = 'v'; break;
		case 23: $r = 'w'; break;
		case 24: $r = 'x'; break;
		case 25: $r = 'y'; break;
		case 26: $r = 'z'; break;
		case 27: $r = '1'; break;
		case 28: $r = '2'; break;
		case 29: $r = '3'; break;
		case 30: $r = '4'; break;
		case 31: $r = '5'; break;
		case 32: $r = '6'; break;
		case 33: $r = '7'; break;
		case 34: $r = '8'; break;
		case 35: $r = '9'; break;

		default: $r = 'n'; break;
	}
	$t = rand(0, 1);
	if ($t == 1) {
		$r = strtoupper($r);
	}
	return $r;
}