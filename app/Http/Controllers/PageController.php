<?php

namespace App\Http\Controllers;

use App\Helpers\HelperCommons;
use App\Models\RequestR;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
	public function pageSections(Request $request, $page)
	{
		$data = $request->all();

		$appVersion = '';
		if(isset($data['appVersion']) && $data['appVersion'] != '') {
			$appVersion = $data['appVersion'];
		}

		$pageSections = [];

		switch ($page) {
			case 'settings':
				if(isset($data['userId']) && $data['userId'] != '') {
					$pageSections[] = $this->getPaymentsSection($data['userId']);
					$pageSections[] = $this->getIdentificationSection($data['userId']);
				}
				break;

			case 'balance':
				if(isset($data['userId']) && $data['userId'] != '') {
					$pageSections[] = $this->getBalanceSection($data['userId']);
				}
				break;

			case 'profile':
				if(isset($data['userId']) && $data['userId'] != '') {
					$pageSections[] = $this->getProfileSection($data['userId']);

					if(!empty($appVersion)) {
						$pageSections[] = $this->getPerformerSection($data['userId']);
					}

					$pageSections[] = $this->getUserIdSection($data['userId']);
				}
				break;

			case 'request':
				if(isset($data['requestId']) && $data['requestId'] != '') {
					$pageSections[] = $this->getRequestSection($data['requestId']);
				}
				break;
		}

		//$pageSections[] = $this->getTestSection(1);
		//$pageSections[] = $this->getTestSection(2);

		$response = [
			'pageSections' => $pageSections
		];

		return response($response, 200);
	}

	private function getTestSection($id) {
		$section = [
			//'htmlContent' => '<ion-list class="mt-3 mb-3">Здесь скоро будут очень выгодные для Вас предложения!</ion-list>',
			'htmlContentId' => 'htmlContentTest'.$id,
			//'htmlContent' => '<ion-list class="mt-3 mb-3" lines="none"><ion-button shape="block" onclick="Window.usell.openRoute(\'/tabs/tabHome/page-empty/view-test/'.$location.'\')" fill="outline">Показать на карте</ion-button></ion-list>',
			'htmlContent' => '<ion-list class="mt-3 mb-3" lines="none"><ion-button shape="block" onclick="Window.rabotaySettings.openUrl(\'https://rabotay.kz\', \'_system\', \'\')" fill="outline">Открыть</ion-button></ion-list>',
		];

		return $section;
	}

	private function getPaymentsSection($user_id) {
		$section = [
			'htmlContentId' => 'htmlPaymentsSection',
			'htmlContent' => '<ion-list class="mt-0 mb-3 pt-0 pb-0" lines="none"><ion-item onclick="Window.rabotaySettings.openUrl(\'https://rabotay.kz/services/payments/'.$user_id.'\')" lines="full" color="primary"><ion-label class="ion-text-center ion-text-wrap">ПОПОЛНИТЬ БАЛАНС</ion-label></ion-item></ion-list>',
		];

		return $section;
	}

	private function getIdentificationSection($user_id) {
		$user = User::findOrFail($user_id);
		$url = 'https://rabotay.kz/profile/'.$user->id.'/identification?identification_token='.$user->firebase_user_uid;

		if(empty($user->secret->iin)) {
			$section = [
				'htmlContentId' => 'htmlIdentificationSection',
				'htmlContent' => '<ion-card class="mt-0 mb-3" lines="none" color="white" mode="ios" onclick="Window.rabotaySettings.openUrl(\''.$url.'\', \'_system\', \'\')">'.
					'<ion-item lines="none" lines="full" color="primary" detail><ion-label class="ion-text-center ion-text-wrap"><ion-icon name="checkmark-circle" class="text-xl"></ion-icon> Стать проверенным пользователем через Digital ID</ion-label></ion-item>'.
					'<ion-text color="medium" class=" text-sm"><p class="pr-3 pl-3">Проверенные пользователи получают больше заявок, потому что заказчики им доверяют и больше предложений своих услуг от более профессиональных исполнителей. Проверку можно пройти только по удостоверению РК.</p></ion-text>'.
					'</ion-card>',
			];
		} else {
			$section = [
				'htmlContentId' => 'htmlIdentificationSection',
				'htmlContent' => '<ion-card class="mt-0 mb-3" lines="none" color="white" mode="ios">'.
					'<ion-item lines="none"><ion-text color="primary" class="ion-text-wrap text-lg"><ion-icon name="checkmark-circle" color="primary"></ion-icon> Проверенный пользователь</ion-text></ion-item>'.
					'<ion-card-content class="pt-0 pb-0"><ion-text color="primary" class="ion-text-wrap">Вы прошли проверку своих документов. Тем самым подтвердив что другие пользователи могут Вам доверять.</ion-text></ion-card-content>'.
					'<ion-item lines="none" class="ion-text-center"><ion-text color="medium" class="ion-text-wrap text-sm w-100">Проверено Digital ID</ion-text></ion-item>'.
					'</ion-card>',
			];
		}

		return $section;
	}

	private function getPerformerSection($user_id) {
		$user = User::findOrFail($user_id);

		$subscriptions = $user->subscriptions()
			->get();

		if($subscriptions->count() > 0) {
			$subscriptionsTemp = '';

			$categories = [];

			foreach ($subscriptions as $subscription) {
				if (!array_key_exists($subscription->category_id, $categories)) {
					$categoryParent = $subscription->category->parent;
					$category = $categoryParent->slug == 'all' ? $subscription->category->name : $subscription->category->name . ' - ' . $subscription->category->parent->name;

					$subscriptionsTemp .= '<ion-chip color="primary"><ion-label>'.$category.'</ion-label></ion-chip>';

					$categories[$subscription->category_id] = $subscription->category_id;
				}
			}

			$section = [
				'htmlContentId' => 'htmlPerformerSection',
				'htmlContent' =>
					'<ion-card class="mt-0 mb-3" lines="none" color="white" mode="ios">'.
					'<ion-card-header><ion-card-title class="ion-text-wrap">Исполнитель в категориях</ion-card-title></ion-card-header>'.
					'<ion-card-content>'.
					$subscriptionsTemp.
					'</ion-card-content>'.
					'<ion-item lines="none" onclick="Window.rabotayProfile.openRoute(\'/performers/offer-request/'.$user_id.'\')" lines="full" color="primary" detail><ion-label class="ion-text-center ion-text-wrap">Предложить заявку исполнителю</ion-label></ion-item>'.
					'</ion-card>',
			];
		} else {
			$section = [];
		}

		return $section;
	}

	private function getUserIdSection($user_id) {
		$section = [
			'htmlContentId' => 'htmlUserIdSection',
			'htmlContent' => '<ion-list class="mt-0 mb-0 pt-0 pb-0" lines="none"><ion-item lines="full"><ion-label class="ion-text-center ion-text-wrap text-medium">Номер пользователя: '.$user_id.'</ion-label></ion-item></ion-list>',
		];

		return $section;
	}

	private function getBalanceSection($user_id) {
		$section = [
			'htmlContentId' => 'htmlBalanceSection',
			'htmlContent' => '<ion-list class="mt-0 mb-0 pt-0 pb-0" lines="none"><ion-item onclick="Window.rabotayBalance.openUrl(\'https://rabotay.kz/services/payments/'.$user_id.'\')" lines="full" color="primary"><ion-label class="ion-text-center ion-text-wrap">ПОПОЛНИТЬ БАЛАНС</ion-label></ion-item></ion-list>',
		];

		return $section;
	}

	private function getProfileSection($user_id) {

		$user = User::where('id', $user_id)->firstOrFail();

		if(!empty($user->secret->iin)) {
			$section = [
				'htmlContentId' => 'htmlProfileSection',
				'htmlContent' => '<ion-card class="mt-0 mb-3" lines="none" color="white" mode="ios">'.
					'<ion-item lines="none"><ion-text color="primary" class="ion-text-wrap text-lg"><ion-icon name="checkmark-circle" color="primary"></ion-icon> Проверенный пользователь</ion-text></ion-item>'.
					'<ion-card-content class="pt-0 pb-0"><ion-text color="primary" class="ion-text-wrap">Пользователь прошел проверку своих документов. Тем самым подтвердив что Вы можете ему доверять.</ion-text></ion-card-content>'.
					'<ion-item lines="none" class="ion-text-center"><ion-text color="medium" class="ion-text-wrap text-sm w-100">Проверено Digital ID</ion-text></ion-item>'.
					'</ion-card>',
			];
		} else {
			$section = [];
		}

		return $section;
	}

	private function getRequestSection($requestId) {

		$requestR = RequestR::where('id', $requestId)->firstOrFail();

		$section = [];

		if($requestR->status == RequestR::STATUS_BLOCKED && !empty($requestR->block_description)) {
			$section = [
				'htmlContentId' => 'htmlBalanceSection',
				'htmlContent' => '<ion-list class="mt-0 mb-0 pt-0 pb-0" lines="none"><ion-item lines="full" color="danger"><ion-label class="ion-text-center ion-text-wrap">'.$requestR->block_description.'</ion-label></ion-item></ion-list>',
			];
		}

		return $section;
	}

	public function pageEmpty(Request $request, $typepage)
	{
		$title = '<span style="display: none;" id="page-title">Страница</span>';

		$content = $title . '<ion-list lines="none"><ion-item><ion-text color="dark">Пока нет информации.</ion-text></ion-item></ion-list>';

		switch ($typepage) {
			case 'test':
				//return '';
				break;
		}

		return $content;
	}
}
