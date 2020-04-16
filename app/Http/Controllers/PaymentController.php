<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$authUser = auth()->user();
		$user = User::where('id', $authUser->id)->firstOrFail();

		$per_page = 10;

		$custom_message = '';

		$data = $request->all();

		$sort = 'desc';

		$payments = Payment::where('user_id', $user->id)
			->orderBy('created_at', $sort)
			->paginate($per_page);

		$lastPage = $payments->lastPage();
		$currentPage = $payments->currentPage();
		$nextPage = $currentPage + 1;

		$paymentsTemp = [];

		foreach ($payments as $payment) {
			$paymentsTemp[] = [
				'id' => $payment->id,
				'payment_type' => $payment->payment_type,
				'payment_type_name' => $payment->getPaymentTypeName(),
				'amount' => $payment->payment_type == Payment::PAYMENT_TYPE_CONSUMPTION ? '-'.$payment->amount : $payment->amount,
				'source_name' => $payment->getSourceName(),
				'description' => $payment->description,
				'created_at' => HelperCommons::getValidFormatDate($payment->created_at),
			];
		}

		$balance = $user->secret->balance;

		if($user->status == User::OWES) {
			$custom_message = '<p class="ion-text-wrap ion-text-center text-red-600">Вы превысили лимит по задолженности на балансе. Чтобы предлагать свои услуги Вам нужно пополнить баланс.</p>';
		}

		$response = [
			'payments' => $paymentsTemp,
			'next_page' => $nextPage,
			'current_page' => $currentPage,
			'last_page' => $lastPage,
			'balance' => $balance,
			'custom_message' => $custom_message,
			'user_id' => $user->id,
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
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param \App\Models\Payment $payment
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show(Payment $payment)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param \App\Models\Payment $payment
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Payment $payment)
	{
		//
	}
}
