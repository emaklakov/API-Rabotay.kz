<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Models\Category;
use App\Models\Notification;
use App\Models\RequestR;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformerController extends Controller
{
	public function index(Request $request)
	{
		$per_page = 10;

		$data = $request->all();

		//Log::error($data);

		$category_id = 1;
		if (array_key_exists('categoryId', $data) && !empty($data['categoryId'])) {
			$category_id = $data['categoryId'];
		}

		$location_id = 1;
		if (array_key_exists('locationId', $data) && !empty($data['locationId'])) {
			$location_id = $data['locationId'];
		}

		$sort = 'desc';
		if (array_key_exists('sort', $data) && !empty($data['sort'])) {
			$sort = $data['sort'];
		}

		$type_performer = 'all';
		if (array_key_exists('type_performer', $data) && !empty($data['type_performer'])) {
			$type_performer = $data['type_performer'];
		}

		$category = Category::where('is_disabled', false)
			->where('id', $category_id)->firstOrFail();
		$categories = $category->descendants()->pluck('id');
		$categories[] = $category->getKey();

		if($type_performer == 'favorites') {
			$favorites = [];
			if (array_key_exists('favorites', $data) && !empty($data['favorites'])) {
				$favorites = explode(',', $data['favorites']);
			}

			$performers = Subscription::where('location_id', $location_id)
				->whereIn('user_id', $favorites)
				->whereIn('category_id', $categories)
				->whereHas('user', function ($query) {
					$query->whereNotNull('first_name');
				})
				->orderBy('updated_at', $sort)
				->with('user')
				->with('user.secret')
				->with('user.rating')
				->with('category')
				->paginate($per_page);
		} else {
			$performers = Subscription::where('location_id', $location_id)
				->whereIn('category_id', $categories)
				->whereHas('user', function ($query) {
					$query->whereNotNull('first_name');
				})
				->orderBy('updated_at', $sort)
				->with('user')
				->with('user.secret')
				->with('user.rating')
				->with('category')
				->paginate($per_page);
		}

		$lastPage = $performers->lastPage();
		$currentPage = $performers->currentPage();
		$nextPage = $currentPage + 1;

		$performersTemp = [];

		foreach ($performers as $performer) {
			if(!empty($performer->user->secret->firebase_fcm_token)) {
				$categoryParent = $performer->category->parent;
				$category = $categoryParent->slug == 'all' ? $performer->category->name : $performer->category->name . ' - ' . $performer->category->parent->name;

				$performersTemp[] = [
					'id' => $performer->id,
					'user_id' => $performer->user->id,
					'first_last_middle_name' => HelperCommons::getFirstLastMiddleName($performer->user->first_name, $performer->user->last_name, $performer->user->middle_name),
					'avatar_image' => HelperCommons::getUserAvatar($performer->user),
					'created_at' => 'здесь с ' . $performer->user->created_at->format('d.m.Y') . ' г',
					'category' => $category,
					'is_identification' => !empty($performer->user->secret->iin),
					'rating' => [
						'rating_stars' => HelperCommons::getRatingStars($performer->user->rating->rating),
						'rating' => $performer->user->rating->rating,
						'count_feedbacks' => $performer->user->rating->count_feedbacks,
					],
				];
			}
		}

		$response = [
			'performers' => $performersTemp,
			'next_page' => $nextPage,
			'current_page' => $currentPage,
			'last_page' => $lastPage,
		];

		return response($response, 200);
	}

	public function offerRequest($request_id, $performer_id) {
		$requestR = RequestR::findOrFail($request_id);
		$performer = User::findOrFail($performer_id);

		Notification::createNotification($performer->id, 'Вам предложили заявку',
										 'Так как, Вы подписаны на определенные категории, у нас в системе вы числитесь как исполнитель. Заказчик предлагает Вам выполнить его заявку. Перейдя по ссылке ниже, Вы можете предложить свои услуги.<br><br>Управлять подписками на категории Вы можете в своих настройках.',
										 '', 'Открыть заявку', '/requests/detail/'.$requestR->id);

		$response = [
			'message' => 'Мы отправили вашу заявку исполнителю. Если он будет готов ее выполнить, он предложит Вам свои услуги.',
		];

		return response($response, 200);
	}
}
