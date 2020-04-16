<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
	const PAYMENT_TYPE_CONSUMPTION = 10; // Расход
	const PAYMENT_TYPE_COMING = 20; // Приход

	const SOURCE_SITE = 10; //Платежи на сайте
	const SOURCE_TRANSFER_QIWI_WALLET = 20; // Перевод на QIWI Кошелек
	const SOURCE_PAYBOX = 30; // Оплата через PayBox
	const SOURCE_STOCK = 40; // Акция
	const SOURCE_TRANSFER_KASPI_GOLD = 50; // Перевод на Kaspi Gold
	const SOURCE_CASH = 60; // Наличный платеж
	const SOURCE_FOR_REQUEST = 70; // Платежи за заявку
	const SOURCE_RETURN = 100; // Возврат денег

	public static function getPaymentTypeList() {
		return [
			'20' => 'Пополнение',
			'10' => 'Списание',
		];
	}

	public function getPaymentTypeName() {
		$list = self::getPaymentTypeList();
		return $list[$this->payment_type];
	}

	public static function getSourceList() {
		return [
			'50' => 'Перевод на Kaspi Gold',
			'20' => 'Перевод на QIWI Кошелек',
			'10' => 'Платежи на сайте',
			'30' => 'Оплата через PayBox',
			'40' => 'Акция',
			'60' => 'Наличный платеж',
			'70' => 'Платежи за заявку',
			'100' => 'Возврат денег',
		];
	}

	public function getSourceName() {
		$list = self::getSourceList();
		return $list[$this->source];
	}
}
