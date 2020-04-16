<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

	public function category()
	{
		return $this->belongsTo('App\Models\Category');
	}

	public function location()
	{
		return $this->belongsTo('App\Models\Location');
	}
}
