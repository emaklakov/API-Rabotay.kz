<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackAboutUser extends Model
{

	public function client()
	{
		return $this->belongsTo('App\Models\User', 'client_user_id', 'id');
	}

	public function performer()
	{
		return $this->belongsTo('App\Models\User', 'performer_user_id', 'id');
	}
}
