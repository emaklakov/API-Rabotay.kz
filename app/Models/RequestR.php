<?php

namespace App\Models;

use App\Helpers\HelperCommons;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RequestR extends Model
{
	const STATUS_NEW = 0; // Новая
	const STATUS_ACCEPTED = 10; // Принята
	const STATUS_COMPLETED = 20; // Выполнена
	const STATUS_BLOCKED = 30; // Блокирована
	const STATUS_CANCELED = 40; // Отменена
	const STATUS_DEL = 50; // Удалена

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'updated_at'
	];

	public function getPriceAttribute($value)
	{
		return $value;
		//return number_format($value, 0);
	}

	public static function createRequest(array $data)
	{
		$authUser = auth()->user();

		$requestR = new RequestR();
		$requestR->user_id = $authUser->id;
		$requestR->category_id = $data['category_id'];
		$requestR->location_id = $data['location_id'];
		if (array_key_exists('address', $data) && !empty($data['address'])) {
			$requestR->address = $data['address'];
		}
		$requestR->title = HelperCommons::filterText($data['title']);
		if (array_key_exists('description', $data) && !empty($data['description'])) {
			$requestR->description = HelperCommons::filterText($data['description']);
		}
		if (array_key_exists('price', $data)) {
			$requestR->price = $data['price'];
		}
		if (array_key_exists('date_start', $data) && !empty($data['date_start'])) {
			$date_start = \Carbon\Carbon::parse($data['date_start']);
			$requestR->date_start = $date_start->format('Y-m-d H:i');
		}
		if (array_key_exists('date_end', $data) && !empty($data['date_end'])) {
			$date_end = \Carbon\Carbon::parse($data['date_end']);
			$requestR->date_end = $date_end->format('Y-m-d H:i');
		}

		$requestR->save();

		//TODO: Добавить заказчиву в колличество созданных заявок

		return $requestR;
	}

	public function user()
	{
		return $this->belongsTo('App\Models\User', 'user_id', 'id');
	}

	public function performer()
	{
		return $this->belongsTo('App\Models\User', 'performer_user_id', 'id');
	}

	public function category()
	{
		return $this->belongsTo('App\Models\Category');
	}

	public function location()
	{
		return $this->belongsTo('App\Models\Location');
	}

	public function offers()
	{
		return $this->hasMany('\App\Models\Offer');
	}
}
