<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Console\Helper\Helper;

class HelperCommons
{

	/**
	 * Generate JSON Web Token.
	 */
	public static function createToken($user)
	{
		$tokenResult = $user->createToken('rabotay-personal-ac');

		$token = $tokenResult->token;

		//$token->expires_at = Carbon::now()->addWeeks(1);
		//$token->save();

		return $tokenResult->accessToken;
	}

	/**
	 * Get Array Count for PHP 7.2
	 */
	public static function getArrayCount($arr) {
		$arrayCount = 0;

		if(is_array($arr) ) {
			$arrayCount = count($arr);
		}

		return $arrayCount;
	}

	public static function getUserData($user_id) {
		$user = User::findOrFail($user_id);

		$userResult = [
			'id' => $user->id,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'middle_name' => $user->middle_name,
			'phone' => $user->phone,
			'info' => [
				'date_birth' => $user->info->date_birth,
				'location_id' => $user->info->location_id,
				'location_name' => $user->info->location ? $user->info->location->name : '',
				'avatar_image' => HelperCommons::getUserAvatar($user),
				'about_me' => $user->info->about_me,
				'gender' => $user->info->gender,
			],
			'rating' => [
				'rating_stars' => HelperCommons::getRatingStars($user->rating->rating),
				'rating' => $user->rating->rating,
				'count_feedbacks' => $user->rating->count_feedbacks,
				'count_create_requests' => $user->rating->count_create_requests,
				'count_complet_requests' => $user->rating->count_complet_requests,
				'count_not_complet_requests' => $user->rating->count_not_complet_requests,
			],
			'secret' => [
				'balance' => $user->secret->balance,
			],
			'status' => $user->status
		];

		return $userResult;
	}

	public static function getValidFormatDate($date) {
		$date = \Carbon\Carbon::parse($date);

		$year = $date->format('Y');
		$month = $date->format('n');
		$day = $date->format('j');
		$time = $date->format('H:i');

		$yearСurrent = date('Y');
		$monthСurrent = date('n');
		$dayСurrent = date('j');

		if($dayСurrent == $day && $monthСurrent == $month && $yearСurrent == $year) {
			$month = '';
			$day = 'сегодня';
		}

		if(($dayСurrent+1) == $day && $monthСurrent == $month && $yearСurrent == $year) {
			$month = '';
			$day = 'завтра';
		}

		$year = $year != $yearСurrent ? ' '.$year : '';

		switch ($month) {
			case 1:
				$month = ' января';
				break;
			case 2:
				$month = ' февраля';
				break;
			case 3:
				$month = ' марта';
				break;
			case 4:
				$month = ' апреля';
				break;
			case 5:
				$month = ' мая';
				break;
			case 6:
				$month = ' июня';
				break;
			case 7:
				$month = ' июля';
				break;
			case 8:
				$month = ' августа';
				break;
			case 9:
				$month = ' сентября';
				break;
			case 10:
				$month = ' октября';
				break;
			case 11:
				$month = ' ноября';
				break;
			case 12:
				$month = ' декабря';
				break;
		}

		$time = $time != '00:00' ? ', '.$time : '';

		$returnDate = $day.$month.$year.$time;

		return $returnDate;
	}

	public static function getFirstLastMiddleName($first_name, $last_name, $middle_name) {
		$first_last_name = 'Имя не указано';

		if(!empty($first_name)) {
			$last_name = !empty($last_name) ? ' '.(mb_strimwidth($last_name, 0, 2, ".")) : '';
			$first_last_name = $first_name.$last_name;
		}

		return $first_last_name;
	}

	public static function getUserAvatar($user) {
		$avatar_image = $user->info->avatar_image;
		$avatar_image = !empty($avatar_image) ? URL::to('/uploads/users/'.$user->id.'/'.$avatar_image) : '/assets/img/ava-empty.png?v2';

		return $avatar_image;
	}

	public static function getRatingStars($rating) {
		$stars = [];

		$count_add_star = 0;
		if($rating > 0) {
			$count_stars = $rating%5 > 0 ? $rating%5 : 5;

			for ($s = 0; $s < $count_stars; $s++) {
				$stars[] = 'star';
				$count_add_star++;
			}

			$count_stars_o = round((($rating - $count_stars) < 1 ? 0 : ($rating - $count_stars)), 0, PHP_ROUND_HALF_DOWN);

			if(($rating - $count_stars - $count_stars_o) > 0) {
				$stars[] = 'star-half';
				$count_add_star++;
			}

			for ($s = 0; $s < (5 - $count_add_star); $s++) {
				$stars[] = 'star-outline';
			}
		} else {
			$stars = ['star-outline', 'star-outline', 'star-outline', 'star-outline', 'star-outline'];
		}

		return $stars;
	}

	public static function getValidFormatNumber($data) {
		$data = str_replace(".", "", $data);
		$data = str_replace(",", "", $data);
		$data = str_replace(" ", "", $data);

		return $data;
	}

	/*
	 * Фильтрация текста
	 */
	public static function filterText($text) {
		$text = HelperCommons::replacePhone($text);

		return $text;
	}

	public static function ReplacePhone($text) {
		$text = preg_replace('#(\+)?(\(\d{2,3}\) ?\d|\d)(([ \-]?\d)|( ?\(\d{2,3}\) ?)){5,12}\d#', '(номер скрыт)', $text);
		return trim($text);
	}
}
