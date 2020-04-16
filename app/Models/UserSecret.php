<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSecret extends Model
{
	public function getBalanceAttribute($value)
	{
		return $value;
		//return number_format($value, 0, ',', ' ');
	}
}
