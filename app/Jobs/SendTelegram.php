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
use Telegram\Bot\Laravel\Facades\Telegram;

class SendTelegram implements ShouldQueue
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

			$title = $notification->title;
			$description = $notification->description;

			$response = Telegram::sendMessage([
												  'chat_id' => '107988539',
												  'text' => "<b>".$title."</b>\r\nПользователь: ".$notification->user_id."\r\n".$description,
												  'parse_mode' => 'HTML'
											  ]);

			if(!empty($response) && $response->getMessageId()) {
				$notification->is_send = true;
				$notification->save();
			}
		}
	}
}
