<?php

namespace App\Http\Controllers\PaymentSystem;

use App\Helpers\HelperCommons;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AllController extends Controller
{
	public function addPayment(Request $request)
	{
		$data = $request->all();

		if(!empty($data)) {

			if(isset($data['apiKey']) && $data['apiKey'] == '9ccbf283-7c50-4a47-bdc8-17d24d58d3a0') {

				if(isset($data['user_id']) && !empty($data['user_id']) &&
					isset($data['amount']) && !empty($data['amount']) &&
					isset($data['payment_type']) && !empty($data['payment_type']) &&
					isset($data['source']) && !empty($data['source'])) {
					$message = isset($data['message']) && !empty($data['message']) ? ' | ' . $data['message'] : '';

					$total_amount = round($data['amount']);

					$sourceDescription = '';
					switch ($data['source']) {
						case Payment::SOURCE_TRANSFER_QIWI_WALLET:
							$sourceDescription = 'Пополнение QIWI Кошелька +77024472944';
							break;

						case Payment::SOURCE_SITE:
							$sourceDescription = 'Платежи на сайте';
							break;

						case Payment::SOURCE_PAYBOX:
							$sourceDescription = 'Оплата через PayBox';
							break;

						case Payment::SOURCE_STOCK:
							$sourceDescription = 'Акция';
							break;

						case Payment::SOURCE_TRANSFER_KASPI_GOLD:
							$sourceDescription = 'Перевод на Kaspi Gold';
							break;

						case Payment::SOURCE_CASH:
							$sourceDescription = 'Наличный платеж';
							break;

						case Payment::SOURCE_FOR_REQUEST:
							$sourceDescription = 'Списание за заявку';
							break;

						case Payment::SOURCE_RETURN:
							$sourceDescription = 'Возврат денег';
							break;
					}

					$description = 'Пополнение баланса | ' . $sourceDescription . $message;

					if($data['payment_type'] == '10') {
						$description = 'Списание с баланса | ' . $sourceDescription . $message;
					}

					DB::beginTransaction();

					$user = User::where('id', $data['user_id'])->first();

					$payment_db = new Payment();
					$payment_db->user_id = $user->id;
					$payment_db->description = $description;
					$payment_db->payment_type = $data['payment_type'] == '10' ? Payment::PAYMENT_TYPE_CONSUMPTION : Payment::PAYMENT_TYPE_COMING;
					$payment_db->amount = $total_amount;
					$payment_db->source = $data['source'];
					$payment_db->save();

					$balance = HelperCommons::getValidFormatNumber($user->secret->balance);

					if($data['payment_type'] == '10') {
						$balance = $balance - $total_amount;
					} else {
						$balance = $balance + $total_amount;
					}

					if($balance >= 0) {
						$user->status = User::ACTIVE;
					} else {
						$user->status = User::OWES;
					}

					$user->secret->balance = $balance;
					$user->secret->save();
					$user->save();

					if($data['payment_type'] == '10') {
						Notification::createNotification($user->id, 'Списание с баланса',
														 'С баланса было списано '.$total_amount.' тг. Подробнее можете посмотреть на своей странице баланса.',
														 '', 'Открыть мой баланс', '/user/balance/detail');
					} else {
						Notification::createNotification($user->id, 'Пополнение баланса',
														 'Ваш баланс был пополнен на сумму '.$total_amount.' тг. Подробнее можете посмотреть на своей странице баланса.',
														 '', 'Открыть мой баланс', '/user/balance/detail');

						Notification::createTelegramNotification($user->id, 'Пополнение баланса', 'Баланс был пополнен на сумму '.$total_amount.' тг. ' . $sourceDescription . $message);
					}

					DB::commit();

					return response(['result' => true], 200);
				}
			}
		}

		return response(['result' => false], 400);
	}
}
