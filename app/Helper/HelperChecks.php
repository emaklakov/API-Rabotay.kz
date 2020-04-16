<?php

namespace App\Helpers;

class HelperChecks
{

	public static function isPhoneNumber($value)
	{
		if ((strncmp($value, '+77', 3) !== 0)) {
			return false;
		}

		if (strncmp($value, '+77', 3) == 0 && strlen($value) == 11) {
			return false;
		}

		if ((strpos($value, ' ') !== false) ||
			(strpos($value, '(') !== false) ||
			(strpos($value, ')') !== false) ||
			(strpos($value, '-') !== false)) {
			return false;
		}

		return true;
	}
}
