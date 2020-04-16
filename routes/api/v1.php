<?php

use App\Models\Category;
use App\Models\Notification;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| API Routes V1
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//Route::post('/registration', 'Auth\RegisterController@registration');
Route::post('/login', 'Auth\LoginController@login'); // Авторизация

Route::get('/dictionaries/locations', 'LocationController@index');
Route::get('/dictionaries/categories', 'CategoryController@index');
Route::get('/dictionaries/categories/top', 'CategoryController@top');
Route::get('/dictionaries/categories/{category_id}/children', 'CategoryController@children');
Route::get('/dictionaries/categories/{category_id}', 'CategoryController@show');

Route::get('/users/{user_id}', 'UserController@show'); // Информация о пользователе

Route::get('/requests', 'RequestRController@index'); // Все заявки
Route::get('/requests/{request_id}', 'RequestRController@show');

Route::post('/performers', 'PerformerController@index'); // Исполнители

Route::get('/notifications/{notification_id}/received', 'NotificationController@received');

Route::get('/users/{user_id}/rating', 'FeedbackAboutUserController@index');

Route::post('/page/sections/{page}', 'PageController@pageSections');
Route::get('/page/empty/{typepage}', 'PageController@pageEmpty');

Route::middleware('auth:api')->group(function() {
	Route::get('/users/{user_id}/data', 'UserController@data'); // Информация о пользователе
	Route::put('/users/{user_id}', 'UserController@update');
	Route::post('/users/{user_id}/avatar', 'UserInfoController@uploadAvatar');

	Route::post('/requests', 'RequestRController@store');
	Route::put('/requests/{request_id}', 'RequestRController@update');
	Route::get('/requests/{request_id}/complet', 'RequestRController@complet');
	Route::post('/requests/{request_id}/feedback', 'FeedbackAboutUserController@store');

	Route::get('/notifications', 'NotificationController@index'); // Все уведомления
	Route::get('/notifications/new-exists', 'NotificationController@newExists');
	Route::get('/notifications/{notification_id}', 'NotificationController@show');

	Route::post('/offers', 'OfferController@store');
	Route::get('/offers/{offer_id}', 'OfferController@show');
	Route::delete('/offers/{offer_id}', 'OfferController@destroy');
	Route::get('/offers/{offer_id}/accept', 'OfferController@accept');
	Route::get('/offers/{offer_id}/cancel', 'OfferController@destroy');
	Route::get('/offers/{offer_id}/refuse', 'OfferController@refuse');

	Route::post('/users/requests/my', 'RequestRController@my');

	Route::get('/payments', 'PaymentController@index');

	Route::post('/subscriptions', 'SubscriptionController@store');
	Route::get('/subscriptions/{subscription_id}', 'SubscriptionController@index');
	Route::delete('/subscriptions/{subscription_id}', 'SubscriptionController@destroy');

	Route::get('/requests/{request_id}/offer-request/{performer_id}', 'PerformerController@offerRequest');
});

Route::get('/commands/rating-categories', 'ServicesController@ratingCategories');

Route::get('/commands/payment-system/qiwi', 'PaymentSystem\QiwiController@checkPayment');

/*
 * Команды для кабинета
 */
Route::post('/commands/payment-system/payment/add', 'PaymentSystem\AllController@addPayment');
Route::post('/commands/requests/{request_id}/block', 'RequestRController@block');
Route::post('/commands/notifications/add', 'NotificationController@store');
Route::post('/commands/notifications/all', 'NotificationController@storeAll');

Route::get('/commands/queue/fcm', function() {
	$exitCode = Artisan::call('queue:work', [
		'--queue' => 'fcm',
		'--stop-when-empty' => true,
	]);

	return $exitCode;
});

/*
Route::get('/commands/test/fcm', function() {
	$user = User::where('id', 2010000)->first();

	if (!empty($user)) {
		$key = 'AAAA3uduEmE:APA91bFfUdEipwG4AaLt94cPD3_I_ZWZ5JKhHDVSvelrRjK8Ycnn7CyvfT5AzF0R_VDsGCtFX9U74BHrMSnYxp0Oe-rDgViwQ6ovIYJmzV1bu6srfXf4iMFXtpQW_6g4EQb5AXpUgqOi';

		$firebase_fcm_token = $user->secret->firebase_fcm_token;

		if (!empty($firebase_fcm_token)) {
			$urlSend = 'https://fcm.googleapis.com/fcm/send';

			$title = 'Title';
			$description_min = 'description_min';

			$client = new Client();

			$resultValidate = $client->post($urlSend, [
				'headers' => [
					'Authorization' => 'key=' . $key,
					'Content-Type' => 'application/json',
				],
				'body' => "{\n\t\"name\": \"data_notification\",\n\t\"notification\": {\n\t\t\"title\": \"".$title."\",\n\t\t\"body\": \"".$description_min."\",\n\t\t\"sound\": \"default\",\n\t\t\"icon\": \"fcm_push_icon\",\n\t\t\"color\": \"#3880ff\",\n\t\t\"badge\": 1,\n\t\t\"image\": \"\"\n\t},\n\t\"data\": {\n\t\t\"notification_title\": \"".$title."\",\n\t\t\"notification_body\": \"".$description_min."\",\n\t\t\"notification_foreground\": \"true\",\n\t\t\"notification_android_visibility\": \"1\",\n\t\t\"notification_android_color\": \"#3880ff\",\n\t\t\"notification_android_icon\": \"fcm_push_icon\",\n\t\t\"notification_android_vibrate\": \"500, 200, 500\",\n\t\t\"notification_android_lights\": \"#ffff0000, 250, 250\",\n\t\t\"notification_ios_badge\": \"1\",\n\t\t\"notification_id\": \"12345\",\n\t\t\"route\": \"/notifications/all\"\n\t},\n\t\"android\": {\n\t\t\"collapse_key\": \"rabotay\",\n\t\t\"priority\": \"high\",\n\t\t\"ttl\": \"3600s\",\n\t\t\"tag\": \"mrabotay\",\n\t\t\"notification\": {\n\t\t\t\"channel_id\": \"fcm_default_channel\"\n\t\t}\n\t},\n\t\"apns\": {\n\t\t\"payload\": {\n\t\t\t\"aps\": {\n\t\t\t\t\"category\": \"NEW_MESSAGE_CATEGORY\",\n\t\t\t\t\"badge\": 1,\n\t\t\t\t\"sound\": \"default\"\n\t\t\t}\n\t\t},\n\t\t\"fcm_options\": {\n\t\t\t\"image\": \"\"\n\t\t}\n\t},\n\t\"to\": \"".$firebase_fcm_token."\",\n\t\"priority\": \"high\"\n}",
			]);

			if($resultValidate->getStatusCode() == 200) {
				$response = json_decode($resultValidate->getBody(), true);
				//dd($response['results'][0]['error']);
				if(isset($response['success']) && $response['success'] == 1) {
					//$notification->is_send = true;
					//$notification->save();
				} else {
					$is_failed_jobs = true;

					Log::error('SendFCM', $response);

					if(isset($response['failure']) && $response['failure'] == 1) {

						if(isset($response['results']) &&
							isset($response['results'][0]) &&
							isset($response['results'][0]['error']) &&
							$response['results'][0]['error'] == 'NotRegistered') {
							$is_failed_jobs = false;

							$user->secret->firebase_fcm_token = '';
							$user->secret->save();
						}
					}

					if($is_failed_jobs) {
						throw new Exception('Error SendFCM:');
					}
				}
			}
		}

	}

	return 0;
});
*/

Route::get('/commands/queue/telegram', function() {
	$exitCode = Artisan::call('queue:work', [
		'--queue' => 'telegram',
		'--stop-when-empty' => true,
	]);

	return $exitCode;
});

Route::get('/commands/queue/subscriptions', function() {
	$exitCode = Artisan::call('queue:work', [
		'--queue' => 'subscriptions',
		'--stop-when-empty' => true,
	]);

	return $exitCode;
});

Route::get('/commands/queue/errors', function() {
	$exitCode = Artisan::call('queue:work', [
		'--queue' => 'errors',
		'--stop-when-empty' => true,
	]);

	return $exitCode;
});

Route::get('/commands/queueretry', function() {
	$exitCode = Artisan::call('queue:retry', [
		'id' => ['all'],
	]);

	return $exitCode;
});

/*
Route::get('/commands/telegram', function() {
	$response = Telegram::sendMessage([
										  'chat_id' => '107988539',
										  'text' => "Hello\r\nWorld",
										  'parse_mode' => 'HTML'
									  ]);

	dd($response);

	return 0;
});
*/

Route::get('/commands/configcache', function () {
	$exitCode = Artisan::call('config:cache', [
		//'--force' => true,
	]);

	return $exitCode;
});

Route::get('/commands/cacheclear', function () {
	$exitCode = Artisan::call('cache:clear', [
		//'--force' => true,
	]);

	return $exitCode;
});

/*
Route::get('/commands/categories/add', function() {

	if(!Category::where('slug', 'bezopasnost-ohrana-detektivy')->exists()){
		$categoryRoot = Category::findOrFail(1);

		$categoryP = new Category([
									  'name' => 'Безопасность, охрана и детективы',
									  'slug' => 'bezopasnost-ohrana-detektivy',
									  'is_disabled' => false,
								  ]
		);
		if ($categoryP->save()) {
			$moved = $categoryP->hasMoved();
		}

		$categoryP->appendToNode($categoryRoot)->save();

		$categoryP->children()->create([
										   'name' => 'Другое',
										   'slug' => 'bezopasnost-ohrana-detektivy-other',
										   'is_disabled' => false,
									   ]
		);
	}

	if(!Category::where('slug', 'meditsinskiye-uslugi')->exists()){
		$categoryRoot = Category::findOrFail(1);

		$categoryP = new Category([
									  'name' => 'Медицинские услуги',
									  'slug' => 'meditsinskiye-uslugi',
									  'is_disabled' => false,
								  ]
		);
		if ($categoryP->save()) {
			$moved = $categoryP->hasMoved();
		}

		$categoryP->appendToNode($categoryRoot)->save();

		$categoryP->children()->create([
										   'name' => 'Другое',
										   'slug' => 'meditsinskiye-uslugi-other',
										   'is_disabled' => false,
									   ]
		);
	}

	if(!Category::where('slug', 'uslugi-dla-jivotnyh')->exists()){
		$categoryRoot = Category::findOrFail(1);

		$categoryP = new Category([
										 'name' => 'Услуги для животных',
										 'slug' => 'uslugi-dla-jivotnyh',
										 'is_disabled' => false,
									 ]
		);
		if ($categoryP->save()) {
			$moved = $categoryP->hasMoved();
		}

		$categoryP->appendToNode($categoryRoot)->save();

		$categoryP->children()->create([
										   'name' => 'Составление родословной',
										   'slug' => 'sostavlenie-rodoslovnoj',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Дрессировка',
										   'slug' => 'dressirovka',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Стрижка когтей',
										   'slug' => 'strizhka-kogtej',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Выгул',
										   'slug' => 'vyigul',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Передержка',
										   'slug' => 'perederzhka',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Мастер-классы',
										   'slug' => 'masterklassyi',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Перевозка',
										   'slug' => 'perevozka',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Стрижка животных',
										   'slug' => 'strizhka-zhivotnyih',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Обслуживание аквариума',
										   'slug' => 'obsluzhivanie-akvariuma',
										   'is_disabled' => false,
									   ]
		);

		$categoryP->children()->create([
										   'name' => 'Другое',
										   'slug' => 'uslugi-dla-jivotnyh-other',
										   'is_disabled' => false,
									   ]
		);
	}

	return 0;
});
*/
/*
Route::get('/commands/migrate', function () {
	$exitCode = Artisan::call('migrate', [
		'--force' => true,
	]);

	return $exitCode;
});
*/
/*
Route::get('/commands/seed', function () {
	$exitCode = Artisan::call('db:seed', [
		'--class' => 'LocationsTableSeeder',
	]);

	//
});
*/
