<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Jobs\NotifySubscribers;
use App\Jobs\SendFCM;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\RequestR;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class RequestRController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$per_page = 10;

		$data = $request->all();

		$category_id = 1;
		if (array_key_exists('category_id', $data) && !empty($data['category_id'])) {
			$category_id = $data['category_id'];
		}

		$location_id = 1;
		if (array_key_exists('location_id', $data) && !empty($data['location_id'])) {
			$location_id = $data['location_id'];
		}

		$sort = 'desc';
		if (array_key_exists('sort', $data) && !empty($data['sort'])) {
			$sort = $data['sort'];
		}

		$category = Category::where('is_disabled', false)
			->where('id', $category_id)->firstOrFail();
		$categories = $category->descendants()->pluck('id');
		$categories[] = $category->getKey();

		$requestsR = RequestR::where('status', RequestR::STATUS_NEW)
			->where('location_id', $location_id)
			->whereIn('category_id', $categories)
			->orderBy('updated_at', $sort)
			->paginate($per_page);
		//->get();

		$lastPage = $requestsR->lastPage();
		$currentPage = $requestsR->currentPage();
		$nextPage = $currentPage + 1;//$lastPage > $currentPage ? ($currentPage + 1) : $currentPage;

		$requestsRTemp = [];

		foreach ($requestsR as $requestR) {
			$category_icon = !empty($requestR->category->parent->icon) ? $requestR->category->parent->icon : 'other.png';

			$start = !empty($requestR->date_start) ? 'Начать: ' . HelperCommons::getValidFormatDate($requestR->date_start) . '; ' : '';
			$end = !empty($requestR->date_end) ? 'Закончить: ' . HelperCommons::getValidFormatDate($requestR->date_end) . ';' : '';

			$start_end = $start . $end;

			$requestsRTemp[] = [
				'id' => $requestR->id,
				'title' => $requestR->title,
				'category' => $requestR->category->parent->name,
				'category_icon' => URL::to('/images/categories/icons/' . $category_icon),
				'start_end' => $start_end,
				'price' => $requestR->price > 0 ? $requestR->price . ' тг' : 'Не указан',
				'created_at' => HelperCommons::getValidFormatDate($requestR->created_at),
			];
		}

		$response = [
			'requests' => $requestsRTemp,
			'next_page' => $nextPage,
			'current_page' => $currentPage,
			'last_page' => $lastPage,
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
	public function store(Request $request)
	{
		$requestR = RequestR::createRequest($request->all());

		// Уведомить подписчиков, что появилась новая заявка
		NotifySubscribers::dispatch($requestR->category->parent->id, $requestR->location_id)->onQueue('subscriptions');

		// Уведомить в телеграм, что появилась новая заявка. Для модерации
		Notification::createTelegramNotification(
			$requestR->user_id,
			'Новая заявка Id:'.$requestR->id,
			"Category:".$requestR->category->parent->name."\r\nLocation:".$requestR->location->name."\r\nTitle:".$requestR->title."\r\nDescription:".$requestR->description."\r\nDate:".HelperCommons::getValidFormatDate($requestR->created_at)
		);

		$response = [
			'id' => $requestR->id,
		];

		return response($response, 201);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param \App\Models\RequestR $requestR
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $requestR_id)
	{
		$requestR = RequestR::findOrFail($requestR_id);

		$category_icon = !empty($requestR->category->parent->icon) ? $requestR->category->parent->icon : 'other.png';

		$start = !empty($requestR->date_start) ? 'Начать: ' . HelperCommons::getValidFormatDate($requestR->date_start) . '; ' : '';
		$end = !empty($requestR->date_end) ? 'Закончить: ' . HelperCommons::getValidFormatDate($requestR->date_end) . ';' : '';

		$start_end = $start . $end;

		$authUser = $request->user('api');

		$offers = $requestR->offers;
		$is_add_offer = false;
		$offer_select = null;
		$add_offer_id = null;

		$offersTemp = [];

		foreach ($offers as $offer) {
			$is_add_offer = !empty($authUser) && $authUser->id == $offer->performer->id;
			if($is_add_offer) {
				$add_offer_id = $offer->id;
			}

			$offerTemp = [
				'id' => $offer->id,
				'first_last_middle_name' => HelperCommons::getFirstLastMiddleName($offer->performer->first_name, $offer->performer->last_name, $offer->performer->middle_name),
				'avatar_image' => HelperCommons::getUserAvatar($offer->performer),
				'created_at' => HelperCommons::getValidFormatDate($offer->created_at),
				'performer_id' => $offer->performer->id
			];

			$offersTemp[] = $offerTemp;

			if(!empty($requestR->performer) && $requestR->performer->id == $offer->performer->id) {
				$offer_select = $offerTemp;
			}
		}

		$contact = '';
		if(!empty($authUser) && $requestR->status == 10 & $requestR->performer_user_id == $authUser->id) {
			$contact = $requestR->user->phone;
		}

		//$whatsapp = 'https://wa.me/77024472944';
		$whatsapp = '';

		$is_owes = false;
		if(!empty($authUser) && $requestR->status == 0) {
			$is_owes = $authUser->status == User::OWES;
		}

		$custom_message = '';
		if($is_owes) {
			$custom_message = '<p class="ion-text-center mt-1 mb-1 pr-3 pl-3 text-red-600"><small>Вы превысили лимит по задолженности на балансе. Чтобы предлагать свои услуги Вам нужно пополнить баланс.</small></p>';
		}

		$response = [
			'id' => $requestR->id,
			'title' => $requestR->title,
			'category' => $requestR->category->parent->name,
			'category_icon' => URL::to('/images/categories/icons/' . $category_icon),
			'start_end' => $start_end,
			'price' => $requestR->price > 0 ? $requestR->price . ' тг' : 'Не указан',
			'created_at' => HelperCommons::getValidFormatDate($requestR->created_at),
			'description' => $requestR->description,
			'location_name' => $requestR->location->name,
			'address' => $requestR->address,
			'its_my' => !empty($authUser) ? ($requestR->user_id == $authUser->id) : false,
			'is_add_offer' => $is_add_offer,
			'add_offer_id' => $add_offer_id,
			'status' => $requestR->status,
			'is_owes' => $is_owes,
			'status_name' => $this->getStatusName($requestR->status),
			'contact' => $contact,
			'whatsapp' => $whatsapp,
			'user' => [
				'id' => $requestR->user->id,
				'first_last_middle_name' => HelperCommons::getFirstLastMiddleName($requestR->user->first_name, $requestR->user->last_name, $requestR->user->middle_name),
				'avatar_image' => HelperCommons::getUserAvatar($requestR->user),
				'created_at' => $requestR->user->created_at->format('d.m.Y'),
			],
			'offers' => $offersTemp,
			'offer_select' => $offer_select,
			'performer' => !empty($requestR->performer) ? [
				'id' => $requestR->performer->id,
				'first_last_middle_name' => HelperCommons::getFirstLastMiddleName($requestR->performer->first_name, $requestR->performer->last_name, $requestR->performer->middle_name),
				'avatar_image' => HelperCommons::getUserAvatar($requestR->performer),
				'created_at' => $requestR->user->created_at->format('d.m.Y'),
			] : null,
			'custom_message' => $custom_message,
		];

		return response($response, 200);
	}

	private function getStatusName($status)
	{
		$statusList = [
			'0' => 'Новая',
			'10' => 'Выполняется',
			'20' => 'Выполнена',
			'30' => 'Блокирована',
			'40' => 'Отменена',
			'50' => 'Удалена',
		];

		return $statusList[$status];
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Models\RequestR     $requestR
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $requestR_id)
	{
		$authUser = auth()->user();

		$requestR = RequestR::where('id', $requestR_id)
			->where('user_id', $authUser->id)
			->where('status', 0)
			->firstOrFail();

		$data = $request->all();

		if(!empty($data)) {
			if (array_key_exists('status', $data) && !empty($data['status'])) {
				$requestR->status = $data['status'];
			}

			if (array_key_exists('performer_user_id', $data) && !empty($data['performer_user_id'])) {
				$requestR->performer_user_id = $data['performer_user_id'];
			}

			if (array_key_exists('canceled_description', $data) && !empty($data['canceled_description'])) {
				$requestR->canceled_description = $data['canceled_description'];
			}

			$requestR->save();
		}

		$response = [
			'id' => $requestR->id,
		];

		return response($response, 200);
	}

	public function my(Request $request)
	{
		$per_page = 10;

		$data = $request->all();
		$authUser = auth()->user();

		$type_request = 'iclient';
		if (array_key_exists('type_request', $data) && !empty($data['type_request'])) {
			$type_request = $data['type_request'];
		}

		$sort = 'desc';
		if (array_key_exists('sort', $data) && !empty($data['sort'])) {
			$sort = $data['sort'];
		}

		if($type_request == 'iperformer') {
			$requestsR = RequestR::where('performer_user_id', $authUser->id)
				->whereIn('status', [RequestR::STATUS_NEW, RequestR::STATUS_ACCEPTED, RequestR::STATUS_COMPLETED, RequestR::STATUS_BLOCKED, RequestR::STATUS_CANCELED])
				->orderBy('created_at', $sort)
				->paginate($per_page);
		} elseif($type_request == 'offer-request') {
			$requestsR = RequestR::where('user_id', $authUser->id)
				->whereIn('status', [RequestR::STATUS_NEW])
				->orderBy('created_at', $sort)
				->paginate($per_page);
		} else {
			$requestsR = RequestR::where('user_id', $authUser->id)
				->whereIn('status', [RequestR::STATUS_NEW, RequestR::STATUS_ACCEPTED, RequestR::STATUS_COMPLETED, RequestR::STATUS_BLOCKED, RequestR::STATUS_CANCELED])
				->orderBy('created_at', $sort)
				->paginate($per_page);
		}

		$lastPage = $requestsR->lastPage();
		$currentPage = $requestsR->currentPage();
		$nextPage = $currentPage + 1;//$lastPage > $currentPage ? ($currentPage + 1) : $currentPage;

		$requestsRTemp = [];

		foreach ($requestsR as $requestR) {
			$category_icon = !empty($requestR->category->parent->icon) ? $requestR->category->parent->icon : 'other.png';

			$start = !empty($requestR->date_start) ? 'Начать: ' . HelperCommons::getValidFormatDate($requestR->date_start) . '; ' : '';
			$end = !empty($requestR->date_end) ? 'Закончить: ' . HelperCommons::getValidFormatDate($requestR->date_end) . ';' : '';

			$start_end = $start . $end;

			$requestsRTemp[] = [
				'id' => $requestR->id,
				'title' => $requestR->title,
				'category' => $requestR->category->parent->name,
				'category_icon' => URL::to('/images/categories/icons/' . $category_icon),
				'start_end' => $start_end,
				'price' => $requestR->price > 0 ? $requestR->price . ' тг' : 'Не указан',
				'status' => $requestR->status,
				'status_name' => $this->getStatusName($requestR->status),
				'created_at' => HelperCommons::getValidFormatDate($requestR->created_at),
			];
		}

		$response = [
			'requests' => $requestsRTemp,
			'next_page' => $nextPage,
			'current_page' => $currentPage,
			'last_page' => $lastPage,
		];

		return response($response, 200);
	}

	public function complet($requestR_id) {
		$authUser = auth()->user();


		$requestR = RequestR::findOrFail($requestR_id);
		$offer = Offer::where('request_r_id', $requestR->id)
			->where('performer_user_id', $requestR->performer_user_id)
			->firstOrFail();

		$user = User::where('id', $requestR->performer_user_id)->firstOrFail();

		$price = !empty($offer->price) && $offer->price > 0 ? $offer->price : $requestR->price;
		$remuneration = $this->getRemuneration($price);

		DB::beginTransaction();

		$payment = new Payment();
		$payment->user_id = $user->id;
		$payment->description = 'Списание за заявку №'.$requestR->id;
		$payment->payment_type = Payment::PAYMENT_TYPE_CONSUMPTION;
		$payment->amount = $remuneration;
		$payment->source = Payment::SOURCE_FOR_REQUEST;
		$payment->save();

		$balance = HelperCommons::getValidFormatNumber($user->secret->balance);
		$user->secret->balance = $balance - $remuneration;

		if($user->secret->balance <= -500) {
			$user->status = User::OWES;
		}

		$user->secret->save();

		$user->rating->count_complet_requests = $user->rating->count_complet_requests + 1;
		$user->rating->save();

		$user->save();

		$requestR->status = RequestR::STATUS_COMPLETED;
		$requestR->save();

		Notification::createNotification($requestR->performer_user_id, 'Списание с баланса',
										 'За выполнение заявки №'.$requestR->id.' с баланса было списано 5% (но не менее 50 тг) от суммы ('.$price.' тг) на которую Вы договорились с заказчиком - '.$user->secret->balance.' тг. Подробнее можете посмотреть на своей странице баланса.',
										 '', 'Открыть мой баланс', '/user/balance/detail');

		DB::commit();

		$response = [
			'id' => $requestR->id
		];

		return response($response, 200);
	}

	// Расчитать вознаграждение
	private function getRemuneration($price) {
		$result = 50;
		$price = HelperCommons::getValidFormatNumber($price);

		if($price > $result) {
			$remuneration = round(($price * 0.05));
			$result = $remuneration > $result ? $remuneration : $result;
		}

		return intval($result);
	}

	public function block(Request $request, $requestR_id) {
		$data = $request->all();

		if(!empty($data)) {
			if(isset($data['apiKey']) && $data['apiKey'] == '9ccbf283-7c50-4a47-bdc8-17d24d58d3a0') {
				$requestR = RequestR::where('id', $requestR_id)
				->where('status', RequestR::STATUS_NEW)
				->firstOrFail();

				if(isset($data['block_description']) && $data['block_description'] != '') {
					$requestR->block_description = $data['block_description'];
				}

				$requestR->status = RequestR::STATUS_BLOCKED;
				$requestR->save();

				Notification::createNotification($requestR->user_id, 'Заявка заблокирована',
												 'Заявки №'.$requestR->id.' была заблокирована за нарушение правил приложения. Подробнее можете узнать внутри заявки.',
												 '', 'Открыть заявку', '/requests/detail/'.$requestR->id);

				return response(['result' => true], 200);
			}
		}

		return response(['result' => false], 400);
	}
}
