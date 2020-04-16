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

class QiwiController extends Controller
{
	public function checkPayment()
	{
		$startDate = new \DateTime('-2 days');
		$startDate = $startDate->format('Y-m-d\TH:i:s\Z');

		$endDate = new \DateTime();
		$endDate = $endDate->format('Y-m-d\TH:i:s\Z');

		$url = 'https://edge.qiwi.com/payment-history/v2/persons/77024472944/payments?rows=15&operation=IN&sources[0]=QW_KZT&startDate=' . $startDate . '&endDate=' . $endDate . '';

		$client = new Client();
		$resultValidate = $client->get($url, [
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer c09cd0f5746364e0753ef7246f9cd75f',
			],
		]);

		if($resultValidate->getStatusCode() == 200) {
			$response = json_decode($resultValidate->getBody(), true);
			$this->DataProcessing($response);
		} else {
			//TODO: Уведомить о проблеме с QIWI
			Log::error($resultValidate);
			throw new \Exception('Проблема при проверке платежей через QIWI.');
		}
	}

	private function DataProcessing($response)
	{
		Log::channel('qiwi')->info($response);

		if(isset($response['data'])) {
			$payments = $response['data'];

			foreach ($payments as $payment) {
				if ($payment['errorCode'] == 0 && $payment['status'] == 'SUCCESS' && !empty($payment['comment']) && mb_strlen($payment['comment']) <= 10) {
					$payment_check = Payment::where('transaction_id', $payment['txnId'])
						->where('transaction_id_client', $payment['trmTxnId'])
						->first();

					if(empty($payment_check)) {
						$comment = trim($payment['comment']);
						$comment = str_replace(" ", "", $comment);

						$user = User::where('id', $comment)->first();

						if (!empty($user)) {
							$total_amount = $payment['total']['amount'];

							$total_amount = round($total_amount);

							$description = 'Пополнение баланса | Прямой перевод на QIWI Кошелек +77024472944';

							// Добавляем 2% к платежам из терминалов QIWI
							if(strpos($payment['provider']['longName'], 'терминал') !== false){
								// Если сумма больше или равна 10 000, то процент не добавляем
								if($total_amount < 10000) {
									$total_amount = $total_amount + round($total_amount/100*2);
								}
								$description = 'Пополнение баланса | Пополнение QIWI Кошелька +77024472944 через терминал';
							}

							DB::beginTransaction();

							$payment_db = new Payment();
							$payment_db->user_id = $user->id;
							$payment_db->description = $description;
							$payment_db->payment_type = Payment::PAYMENT_TYPE_COMING;
							$payment_db->amount = $total_amount;
							$payment_db->source = Payment::SOURCE_TRANSFER_QIWI_WALLET;
							$payment_db->transaction_id = $payment['txnId'];
							$payment_db->transaction_id_client = $payment['trmTxnId'];
							$payment_db->save();

							$balance = HelperCommons::getValidFormatNumber($user->secret->balance);
							$balance = $balance + $total_amount;
							$user->secret->balance = $balance;

							if($balance >= 0) {
								$user->status = User::ACTIVE;
							}

							$user->secret->save();
							$user->save();

							Notification::createNotification($user->id, 'Пополнение баланса',
															 'Ваш баланс был пополнен на сумму '.$total_amount.' тг. Подробнее можете посмотреть на своей странице баланса.',
															 '', 'Открыть мой баланс', '/user/balance/detail');

							Notification::createTelegramNotification($user->id, 'Пополнение баланса', 'Баланс был пополнен на сумму '.$total_amount.' тг. Через QIWI.');

							DB::commit();
						}
					}
				}
			}
		}
	}
}
