<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Models\Offer;
use App\Models\Notification;
use App\Models\RequestR;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$data = $request->all();
		$authUser = auth()->user();

		$offer = Offer::where('request_r_id', $data['request_r_id'])
			->where('performer_user_id', $authUser->id)
			->first();

		if(empty($offer)) {
			$offer = Offer::createOffer($data);

			Notification::createNotification($offer->client_user_id, 'Новое предложение',
											 'В заявке №'.$offer->request_r_id.' Вам предложили услуги. Можете перейти в заявку и принять их. Выбирайте самое лучшее для Вас предложение, основываясь на рейтинге исполнителя. Не забывайте почитать, что пишут об исполнителе другие заказчики.',
											 '', 'Открыть заявку', '/requests/detail/'.$offer->request_r_id);
		}

		$response = [
			'id' => $offer->id,
		];

		return response($response, 201);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param \App\Models\Offer $offer
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show($offer_id)
	{
		$offer = Offer::findOrFail($offer_id);

		$authUser = auth()->user();

		$response = [
			'id' => $offer->id,
			'first_last_middle_name' => HelperCommons::getFirstLastMiddleName($offer->performer->first_name, $offer->performer->last_name, $offer->performer->middle_name),
			'avatar_image' => HelperCommons::getUserAvatar($offer->performer),
			'created_at' => HelperCommons::getValidFormatDate($offer->created_at),
			'performer_id' => $offer->performer_user_id,
			'request_r_id' => $offer->request_r_id,
			'request_status' => $offer->requestr->status,
			'description' => $offer->description,
			'price' => $offer->price,
			'is_client' => ($authUser->id == $offer->client_user_id),
			'its_my' => ($authUser->id == $offer->performer_user_id),
			'is_accept' => !empty($offer->requestr->performer_user_id) && $offer->requestr->performer_user_id == $offer->performer_user_id,
		];

		return response($response, 200);
	}

	public function accept($offer_id)
	{
		$offer = Offer::findOrFail($offer_id);

		$authUser = auth()->user();

		$requestR = RequestR::where('id', $offer->request_r_id)
			->where('user_id', $offer->client_user_id)
			->where('status', RequestR::STATUS_NEW)
			->firstOrFail();

		$requestR->performer_user_id = $offer->performer_user_id;
		$requestR->status = RequestR::STATUS_ACCEPTED;
		$requestR->save();

		Notification::createNotification($offer->performer_user_id, 'Ваши услуги приняты',
										 'В заявке №'.$offer->request_r_id.' Вы предложили свои услуги. Заказчик принял их и теперь Вам доступны его контакты. Можете перейти в заявку и связаться с заказчиком. Выполняя качественно свои услуги, Вы будите получать хорошие оценки и отзывы. Вас будут чаще выбирать среди других исполнителей.',
										 '', 'Открыть заявку', '/requests/detail/'.$offer->request_r_id);

		$response = [
			'id' => $requestR->id,
		];

		return response($response, 200);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param \App\Models\Offer $offer
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($offer_id)
	{
		$offer = Offer::findOrFail($offer_id);

		$authUser = auth()->user();

		$requestR = RequestR::where('id', $offer->request_r_id)
			->where('user_id', $offer->client_user_id)
			->first();

		DB::beginTransaction();

		$is_performer = false;

		if(!empty($requestR->performer_user_id) && $requestR->performer_user_id == $offer->performer_user_id) {
			$requestR->performer_user_id = null;
			$requestR->status = RequestR::STATUS_NEW;
			$requestR->save();

			$user = User::where('id', $offer->performer_user_id)->firstOrFail();
			$user->rating->count_not_complet_requests = $user->rating->count_not_complet_requests + 1;
			$user->rating->save();

			$is_performer = true;
		}

		$offer->delete();

		if($is_performer) {
			Notification::createNotification($requestR->user_id, 'Отменили предложение',
											 'В заявке №'.$requestR->id.' исполнитель отменил своё предложение. Он был исполнителем Вашей заявки. Теперь заявка перешла в статус новая и снова может принимать предложения.',
											 '', 'Открыть заявку', '/requests/detail/'.$requestR->id);
		} else {
			Notification::createNotification($requestR->user_id, 'Отменили предложение',
											 'В заявке №'.$requestR->id.' исполнитель отменил своё предложение. Он не был исполнителем Вашей заявки.',
											 '', 'Открыть заявку', '/requests/detail/'.$requestR->id);
		}

		DB::commit();

		$response = [
			'id' => $requestR->id,
		];

		return response($response, 200);
	}

	public function refuse($offer_id)
	{
		$offer = Offer::findOrFail($offer_id);

		$authUser = auth()->user();

		$requestR = RequestR::where('id', $offer->request_r_id)
			->where('user_id', $offer->client_user_id)
			->where('status', RequestR::STATUS_ACCEPTED)
			->firstOrFail();

		$user = User::where('id', $offer->performer_user_id)->firstOrFail();

		DB::beginTransaction();

		$requestR->performer_user_id = null;
		$requestR->status = RequestR::STATUS_NEW;
		$requestR->save();

		$user = User::where('id', $offer->performer_user_id)->firstOrFail();
		$user->rating->count_not_complet_requests = $user->rating->count_not_complet_requests + 1;
		$user->rating->save();

		Notification::createNotification($user->id, 'Отказались от предложения',
										 'В заявке №'.$requestR->id.' заказчик отказался от Вашего предложения. До этого Вы были исполнителем данной заявки.',
										 '', 'Открыть заявку', '/requests/detail/'.$requestR->id);

		DB::commit();

		$response = [
			'id' => $requestR->id,
		];

		return response($response, 200);
	}
}
