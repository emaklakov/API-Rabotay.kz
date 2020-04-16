<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFCM implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 3;

	protected $notification_id;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($notification_id)
	{
		$this->notification_id = $notification_id;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$notification = Notification::where('id', $this->notification_id)
			->where('is_send', false)
			->first();

		if (!empty($notification)) {
			$user = User::where('id', $notification->user_id)->first();

			if (!empty($user)) {
				$key = 'AAAA3uduEmE:APA91bFfUdEipwG4AaLt94cPD3_I_ZWZ5JKhHDVSvelrRjK8Ycnn7CyvfT5AzF0R_VDsGCtFX9U74BHrMSnYxp0Oe-rDgViwQ6ovIYJmzV1bu6srfXf4iMFXtpQW_6g4EQb5AXpUgqOi';

				$firebase_fcm_token = $user->secret->firebase_fcm_token;

				if (!empty($firebase_fcm_token)) {
					$urlSend = 'https://fcm.googleapis.com/fcm/send';

					$title = $notification->title;
					$description_min = $notification->description_min;

					$client = new Client();

					$resultValidate = $client->post($urlSend, [
						'headers' => [
							'Authorization' => 'key=' . $key,
							'Content-Type' => 'application/json',
						],
						'body' => "{\n\t\"name\": \"data_notification\",\n\t\"notification\": {\n\t\t\"title\": \"".$title."\",\n\t\t\"body\": \"".$description_min."\",\n\t\t\"sound\": \"default\",\n\t\t\"icon\": \"fcm_push_icon\",\n\t\t\"color\": \"#3880ff\",\n\t\t\"badge\": 1,\n\t\t\"image\": \"https://rabotay.kz/img/fb.png\"\n\t},\n\t\"data\": {\n\t\t\"notification_title\": \"".$title."\",\n\t\t\"notification_body\": \"".$description_min."\",\n\t\t\"notification_foreground\": \"true\",\n\t\t\"notification_android_visibility\": \"1\",\n\t\t\"notification_android_color\": \"#3880ff\",\n\t\t\"notification_android_icon\": \"fcm_push_icon\",\n\t\t\"notification_android_vibrate\": \"500, 200, 500\",\n\t\t\"notification_android_lights\": \"#ffff0000, 250, 250\",\n\t\t\"notification_ios_badge\": \"1\",\n\t\t\"notification_id\": \"".$notification->id."\",\n\t\t\"route\": \"/notifications/all\"\n\t},\n\t\"android\": {\n\t\t\"collapse_key\": \"rabotay\",\n\t\t\"priority\": \"high\",\n\t\t\"ttl\": \"3600s\",\n\t\t\"tag\": \"mrabotay\",\n\t\t\"notification\": {\n\t\t\t\"channel_id\": \"fcm_default_channel\"\n\t\t}\n\t},\n\t\"apns\": {\n\t\t\"payload\": {\n\t\t\t\"aps\": {\n\t\t\t\t\"category\": \"NEW_MESSAGE_CATEGORY\",\n\t\t\t\t\"badge\": 1,\n\t\t\t\t\"sound\": \"default\"\n\t\t\t}\n\t\t},\n\t\t\"fcm_options\": {\n\t\t\t\"image\": \"https://rabotay.kz/img/fb.png\"\n\t\t}\n\t},\n\t\"to\": \"".$firebase_fcm_token."\",\n\t\"priority\": \"high\"\n}",
					]);

					if($resultValidate->getStatusCode() == 200) {
						$response = json_decode($resultValidate->getBody(), true);
						//Log::info('SendFCM:'.$this->notification_id, $response);
						if(isset($response['success']) && $response['success'] == 1) {
							$notification->is_send = true;
							$notification->save();
						} else {
							$is_failed_jobs = true;

							Log::error('SendFCM:'.$this->notification_id, $response);

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
								throw new Exception('Error SendFCM:'.$this->notification_id);
							}
						}
					}
				}

			}
		}
	}
}
