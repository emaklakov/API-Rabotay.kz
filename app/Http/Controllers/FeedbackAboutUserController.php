<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Models\FeedbackAboutUser;
use App\Models\Notification;
use App\Models\RequestR;
use App\Models\User;
use Illuminate\Http\Request;

class FeedbackAboutUserController extends Controller
{
	public function index(Request $request, $user_id)
	{
		$user = User::where('id', $user_id)->firstOrFail();

		$per_page = 10;

		$data = $request->all();

		$sort = 'desc';

		$feedbacks = FeedbackAboutUser::where('performer_user_id', $user->id)
			->orderBy('created_at', $sort)
			->paginate($per_page);

		$lastPage = $feedbacks->lastPage();
		$currentPage = $feedbacks->currentPage();
		$nextPage = $currentPage + 1;

		$feedbacksTemp = [];

		foreach ($feedbacks as $feedback) {
			$feedbacksTemp[] = [
				'id' => $feedback->id,
				'user' => [
					'id' => $feedback->client->id,
					'first_last_middle_name' => HelperCommons::getFirstLastMiddleName($feedback->client->first_name, $feedback->client->last_name, $feedback->client->middle_name),
					'avatar_image' => HelperCommons::getUserAvatar($feedback->client),
					'created_at' => $feedback->client->created_at->format('d.m.Y'),
				],
				'client_user_id' => $feedback->client_user_id,
				'request_r_id' => $feedback->request_r_id,
				'message' => $feedback->message,
				'rating' => $feedback->rating,
				'rating_stars' => HelperCommons::getRatingStars($feedback->rating),
				'created_at' => HelperCommons::getValidFormatDate($feedback->created_at),
			];
		}

		$response = [
			'feedbacks' => $feedbacksTemp,
			'next_page' => $nextPage,
			'current_page' => $currentPage,
			'last_page' => $lastPage,
			'rating' => $user->rating->rating,
			'rating_stars' => HelperCommons::getRatingStars($user->rating->rating),
			'count_feedbacks' => $user->rating->count_feedbacks,
			'count_create_requests' => $user->rating->count_create_requests,
			'count_complet_requests' => $user->rating->count_complet_requests,
			'count_not_complet_requests' => $user->rating->count_not_complet_requests,
		];

		return response($response, 200);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request, $request_id)
	{
		$authUser = auth()->user();

		$data = $request->all();

		$requestR = RequestR::findOrFail($request_id);

		$response = null;

		if($authUser->id == $requestR->user_id) {
			$feedback = new FeedbackAboutUser();
			$feedback->client_user_id = $requestR->user_id;
			$feedback->performer_user_id = $requestR->performer_user_id;
			$feedback->request_r_id = $requestR->id;

			if (array_key_exists('message', $data) && !empty($data['message'])) {
				$feedback->message = $data['message'];
			}
			if (array_key_exists('rating', $data) && !empty($data['rating'])) {
				$feedback->rating = $data['rating'];
			}

			$feedback->save();

			$user = User::where('id', $feedback->performer_user_id)->firstOrFail();

			$feedbacksQuery = FeedbackAboutUser::query()->where('performer_user_id', $requestR->performer_user_id);
			$count_feedbacks = $feedbacksQuery->count();
			$rating = $count_feedbacks > 0 ? round($feedbacksQuery->sum('rating')/$count_feedbacks, 1) : 0;

			$user->rating->rating = $rating;
			$user->rating->count_feedbacks = $count_feedbacks;
			$user->rating->save();
			$user->save();

			Notification::createNotification($requestR->performer_user_id, 'Новая оценка или отзыв',
											 'За выполнение заявки №'.$requestR->id.' Вам поставили оценку или написали отзыв. Подробнее можете посмотреть на своей страницы рейтинга.',
											 '', 'Открыть мой рейтинг', '/user/rating-reviews/'.$user->id);

			$response = [
				'id' => $requestR->id
			];
		}

		return response($response, 200);
	}
}
