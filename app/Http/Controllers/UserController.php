<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function show($user_id)
	{
		$user = User::findOrFail($user_id);

		$response = [
			'id' => $user->id,
			'first_last_middle_name' => HelperCommons::getFirstLastMiddleName($user->first_name, $user->last_name, $user->middle_name),
			'avatar_image' => HelperCommons::getUserAvatar($user),
			'created_at' => $user->created_at->format('d.m.Y'),
			'about_me' => $user->info->about_me,
			'is_verification' => !empty($user->secret->iin),
			'rating' => [
				'rating_stars' => HelperCommons::getRatingStars($user->rating->rating),
				'rating' => $user->rating->rating,
				'count_feedbacks' => $user->rating->count_feedbacks,
				'count_create_requests' => $user->rating->count_create_requests,
				'count_complet_requests' => $user->rating->count_complet_requests,
				'count_not_complet_requests' => $user->rating->count_not_complet_requests,
			],
		];

		return response($response, 200);
	}

	public function data($user_id)
	{
		$response = HelperCommons::getUserData($user_id);

		return response($response, 200);
	}

	public function update(Request $request, $user_id)
	{
		$user = User::updateUser($request->all(), $user_id);

		$response = HelperCommons::getUserData($user_id);

		return response($response, 200);
	}
}
