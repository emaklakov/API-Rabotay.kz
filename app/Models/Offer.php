<?php

namespace App\Models;

use App\Helpers\HelperCommons;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
	public static function createOffer(array $data)
	{
		$authUser = auth()->user();
		$requestR = RequestR::findOrFail($data['request_r_id']);

		$offer = new Offer();
		$offer->request_r_id = $requestR->id;
		$offer->client_user_id = $requestR->user_id;
		$offer->performer_user_id = $authUser->id;
		if (array_key_exists('description', $data) && !empty($data['description'])) {
			$offer->description = HelperCommons::filterText($data['description']);
		}
		if (array_key_exists('price', $data)) {
			$offer->price = $data['price'];
		}

		$offer->save();

		return $offer;
	}

	public function performer()
	{
		return $this->belongsTo('App\Models\User', 'performer_user_id', 'id');
	}

	public function requestr()
	{
		return $this->belongsTo('App\Models\RequestR', 'request_r_id', 'id');
	}
}
