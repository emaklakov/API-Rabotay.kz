<?php

namespace App\Models;

use App\Helpers\HelperCommons;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
	use HasApiTokens, Notifiable;

	const BLOCKED = 0;  // Заблокирован
	const ACTIVE  = 10; // Активный
	const OWES    = 20; // Должен

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'phone', 'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'firebase_user_uid', 'updated_at', 'deleted_at'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'created_at' => 'datetime',
	];

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param array $data
	 *
	 * @return \App\Models\User
	 */
	public static function createUser(array $data)
	{
		DB::beginTransaction();
		$user = new User();
		//$user->first_name = $data['first_name'];
		$user->phone = $data['phone'];
		$user->firebase_user_uid = $data['firebase_user_uid'];
		$user->save();

		$userInfo = new UserInfo();
		$userInfo->user_id = $user->id;
		$userInfo->save();

		$userRating = new UserRating();
		$userRating->user_id = $user->id;
		$userRating->save();

		$userSecret = new UserSecret();
		$userSecret->user_id = $user->id;
		if (array_key_exists('firebase_fcm_token', $data) && !empty($data['firebase_fcm_token'])) {
			$userSecret->firebase_fcm_token = $data['firebase_fcm_token'];
		}
		$userSecret->save();

		DB::commit();

		return $user;
	}

	public static function updateUser(array $data, $user_id)
	{
		$user = User::findOrFail($user_id);
		$authUser = auth()->user();

		if (!empty($data) && $user->id == $authUser->id) {

			if (array_key_exists('first_name', $data)) {
				$user->first_name = $data['first_name'];
			}
			if (array_key_exists('last_name', $data)) {
				$user->last_name = $data['last_name'];
			}
			if (array_key_exists('middle_name', $data)) {
				$user->middle_name = $data['middle_name'];
			}
			if (array_key_exists('about_me', $data)) {
				$user->info->about_me = HelperCommons::filterText($data['about_me']);
				$user->info->save();
			}
			if (array_key_exists('date_birth', $data)) {
				$date_birth = \Carbon\Carbon::parse($data['date_birth']);
				$user->info->date_birth = $date_birth->format('Y-m-d');
				$user->info->save();
			}
			if (array_key_exists('gender', $data)) {
				$user->info->gender = $data['gender'];
				$user->info->save();
			}
			if (isset($data['location_id'])) {
				$user->info->location_id = $data['location_id'];
				$user->info->save();
			}
			if (array_key_exists('firebase_fcm_token', $data)) {
				$user->secret->firebase_fcm_token = $data['firebase_fcm_token'];
				$user->secret->save();
			}

			$user->save();

		}

		return $user;
	}

	public function AauthAcessToken(){
		return $this->hasMany('\App\Models\OauthAccessToken');
	}

	public function info()
	{
		return $this->hasOne('App\Models\UserInfo');
	}

	public function rating()
	{
		return $this->hasOne('App\Models\UserRating');
	}

	public function secret()
	{
		return $this->hasOne('App\Models\UserSecret');
	}

	public function subscriptions()
	{
		return $this->hasOne('App\Models\Subscription');
	}
}
